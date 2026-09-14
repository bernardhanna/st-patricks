#!/usr/bin/env python3
"""Build multi-sheet Excel workbook for Google Sheets upload (Items, Summary, Media folders, Instructions)."""

from __future__ import annotations

import csv
import zipfile
from collections import Counter
from datetime import date
from pathlib import Path
from xml.sax.saxutils import escape

BASE = Path(__file__).resolve().parent.parent / "old" / "content"
OUT = BASE / "CONTENT-MIGRATION-TRACKER-GoogleSheet-Template.xlsx"

CLIENT_EDITABLE = {
    "Client status",
    "Editorial notes",
    "Exclusions",
    "Owner",
    "Done?",
}


def col_letter(n: int) -> str:
    s = ""
    while n >= 0:
        s = chr(n % 26 + 65) + s
        n = n // 26 - 1
    return s


def sheet_xml(rows: list[list[str]]) -> str:
    parts = [
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>',
        '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">',
        "<sheetData>",
    ]
    for r_idx, row in enumerate(rows, start=1):
        parts.append(f'<row r="{r_idx}">')
        for c_idx, val in enumerate(row):
            ref = f"{col_letter(c_idx)}{r_idx}"
            text = escape(str(val) if val is not None else "")
            parts.append(f'<c r="{ref}" t="inlineStr"><is><t>{text}</t></is></c>')
        parts.append("</row>")
    parts += ["</sheetData>", "</worksheet>"]
    return "".join(parts)


def build_workbook(sheets: dict[str, list[list[str]]], path: Path) -> None:
    sheet_names = list(sheets.keys())
    workbook_xml = (
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
        'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
        "<sheets>"
    )
    rels = ['<?xml version="1.0" encoding="UTF-8" standalone="yes"?>']
    rels.append(
        '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    )
    rels.append(
        '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
    )
    rels.append("</Relationships>")

    wb_rels = [
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>',
        '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">',
    ]

    content_types = [
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>',
        '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">',
        '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>',
        '<Default Extension="xml" ContentType="application/xml"/>',
        '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>',
    ]

    for i, name in enumerate(sheet_names, start=1):
        safe = escape(name[:31])
        workbook_xml += f'<sheet name="{safe}" sheetId="{i}" r:id="rId{i}"/>'
        wb_rels.append(
            f'<Relationship Id="rId{i}" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet{i}.xml"/>'
        )
        content_types.append(
            f'<Override PartName="/xl/worksheets/sheet{i}.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
        )

    workbook_xml += "</sheets></workbook>"
    wb_rels.append("</Relationships>")

    with zipfile.ZipFile(path, "w", zipfile.ZIP_DEFLATED) as z:
        z.writestr("[Content_Types].xml", "".join(content_types) + "</Types>")
        z.writestr("_rels/.rels", "".join(rels))
        z.writestr("xl/workbook.xml", workbook_xml)
        z.writestr("xl/_rels/workbook.xml.rels", "".join(wb_rels))
        for i, name in enumerate(sheet_names, start=1):
            z.writestr(f"xl/worksheets/sheet{i}.xml", sheet_xml(sheets[name]))


def read_csv_rows(path: Path) -> list[list[str]]:
    with path.open(newline="", encoding="utf-8") as f:
        return list(csv.reader(f))


def build_summary_rows() -> list[list[str]]:
    items_path = BASE / "CONTENT-MIGRATION-TRACKER-v2.csv"
    with items_path.open(newline="", encoding="utf-8") as f:
        rows = list(csv.DictReader(f))
    rec = Counter(r.get("Reconciliation", "") for r in rows)
    act = Counter(r.get("Migration action", "") for r in rows)
    today = date.today().isoformat()
    out = [
        ["Content migration summary", ""],
        ["Generated", today],
        [""],
        ["Reconciliation", "Count"],
    ]
    for k, v in rec.most_common():
        out.append([k, str(v)])
    out += [["", ""], ["Migration action", "Count"]]
    for k, v in act.most_common():
        out.append([k, str(v)])
    out += [
        [""],
        ["WP migrated total (export)", ""],
    ]
    mig = BASE / "current-migrated-state-refreshed.csv"
    if mig.is_file():
        with mig.open(newline="", encoding="utf-8") as f:
            out.append(["Items with _matrix_migrate_old_path", str(sum(1 for _ in csv.DictReader(f)))])
    return out


def build_instructions() -> list[list[str]]:
    editable = ", ".join(sorted(CLIENT_EDITABLE))
    return [
        ["How to use this workbook in Google Sheets"],
        [""],
        ["1. Upload to Google Drive → Open with Google Sheets"],
        ["2. Share with client (Editor on Items tab columns: " + editable + ")"],
        ["3. All links use staging: https://st-patricks.s1.matrix-test.com/"],
        ["4. Matrix refreshes WP columns via export script — do not delete rows"],
        ["5. Sync news dates/flags: wp eval-file scripts/migrate-sync-news-events.php"],
        ["6. Sync clinician insights: wp eval-file scripts/migrate-sync-clinician-insights.php"],
        ["7. Sync sitemap pages: wp eval-file scripts/migrate-sync-sitemap-pages.php"],
        ["8. Regenerate: python3 scripts/build-content-review-artifacts.py"],
        ["9. Rebuild template: python3 scripts/build-google-sheet-template.py"],
        [""],
        ["Column ownership"],
        ["Client edits", editable],
        ["Matrix auto-updates", "WP post ID, WP status, Staging URL, Reconciliation, Flagged?"],
        [""],
        ["Safety"],
        ["Imports are insert-only — existing posts/pages are never deleted or overwritten"],
        ["New imports are always created as drafts"],
    ]


def main() -> None:
    surplus_path = BASE / "RECONCILIATION-SURPLUS.csv"
    surplus_rows: list[list[str]] = []
    if surplus_path.is_file():
        with surplus_path.open(newline="", encoding="utf-8") as f:
            reader = csv.DictReader(f)
            surplus_rows = [
                [
                    r.get("post_id", ""),
                    r.get("title", ""),
                    r.get("post_type", ""),
                    r.get("post_status", ""),
                    r.get("staging_url", ""),
                    r.get("old_url", ""),
                    r.get("note", ""),
                ]
                for r in reader
                if r.get("post_type") == "post" and r.get("post_status") == "publish"
            ]
        surplus_sheet = [["Post ID", "Title", "Type", "Status", "Staging URL", "Old URL", "Note"]] + surplus_rows
    else:
        surplus_sheet = [["Post ID", "Title", "Type", "Status", "Staging URL", "Old URL", "Note"]]

    editorial_rows: list[list[str]] = []
    items_path = BASE / "CONTENT-MIGRATION-TRACKER-v2.csv"
    if items_path.is_file():
        with items_path.open(newline="", encoding="utf-8") as f:
            for r in csv.DictReader(f):
                if r.get("Flagged?") == "Yes":
                    editorial_rows.append(
                        [
                            r.get("ID", ""),
                            r.get("Title", ""),
                            r.get("Menu / Section", ""),
                            r.get("Editorial action", ""),
                            r.get("Editorial notes", ""),
                            r.get("Staging URL", ""),
                        ]
                    )
    editorial_sheet = [
        ["ID", "Title", "Section", "Editorial action", "Editorial notes", "Staging URL"]
    ] + editorial_rows

    sheets = {
        "News and Events": read_csv_rows(BASE / "NEWS-AND-EVENTS-TRACKER.csv"),
        "Clinician Insights": read_csv_rows(BASE / "CLINICIAN-INSIGHTS-TRACKER.csv"),
        "Sitemap pages": read_csv_rows(BASE / "SITEMAP-PAGES-TRACKER.csv"),
        "Items": read_csv_rows(BASE / "CONTENT-MIGRATION-TRACKER-v2.csv"),
        "Editorial flags": editorial_sheet,
        "Surplus to draft": surplus_sheet,
        "Summary": build_summary_rows(),
        "Media folders": read_csv_rows(BASE / "MEDIA-FOLDER-TAXONOMY.csv"),
        "Instructions": build_instructions(),
    }
    build_workbook(sheets, OUT)
    print(f"Wrote {OUT} ({len(sheets)} sheets)")


if __name__ == "__main__":
    main()
