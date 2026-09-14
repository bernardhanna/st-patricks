#!/usr/bin/env python3
"""Build content review deliverables from client Excel files + WP export. Read-only on source files."""

from __future__ import annotations

import csv
import re
import zipfile
from collections import Counter, defaultdict
from datetime import date
from pathlib import Path
from xml.etree import ElementTree as ET

NS = "{http://schemas.openxmlformats.org/spreadsheetml/2006/main}"
BASE = Path(__file__).resolve().parent.parent / "old" / "content"
STAGING_BASE = "https://st-patricks.s1.matrix-test.com"

MIGRATE_STATUSES = {
    "for migration",
    "for migrating",
    "migrating",
    "migrate",
    "for migrate",
}
DRAFTING_STATUSES = {"spmhs drafting", "not started", "in development"}
EDITORIAL_KEYWORDS = (
    "covid",
    "before publishing",
    "update link",
    "update podcast",
    "change link",
    "add content",
    "may need to be updated",
    "remove reference",
    "broadcast",
)


def col_to_idx(ref: str) -> int:
    m = re.match(r"([A-Z]+)(\d+)", ref)
    col = m.group(1)
    n = 0
    for c in col:
        n = n * 26 + (ord(c) - 64)
    return n - 1


def read_xlsx(path: Path) -> list[tuple[str, list[list[str]]]]:
    z = zipfile.ZipFile(path)
    shared: list[str] = []
    if "xl/sharedStrings.xml" in z.namelist():
        root = ET.fromstring(z.read("xl/sharedStrings.xml"))
        for si in root.findall(f"{NS}si"):
            shared.append("".join(t.text or "" for t in si.iter(f"{NS}t")))
    wb = ET.fromstring(z.read("xl/workbook.xml"))
    sheets_meta = [
        (
            s.get("name"),
            s.get("{http://schemas.openxmlformats.org/officeDocument/2006/relationships}id"),
        )
        for s in wb.iter(f"{NS}sheet")
    ]
    rels = ET.fromstring(z.read("xl/_rels/workbook.xml.rels"))
    rid = {r.get("Id"): r.get("Target") for r in rels}
    out: list[tuple[str, list[list[str]]]] = []
    for name, rid_val in sheets_meta:
        target = rid.get(rid_val)
        if not target:
            continue
        if not target.startswith("xl/"):
            target = "xl/" + target
        root = ET.fromstring(z.read(target))
        rows: list[list[str]] = []
        for row in root.iter(f"{NS}row"):
            cells: dict[int, str] = {}
            mx = 0
            for c in row.findall(f"{NS}c"):
                ref = c.get("r")
                if not ref:
                    continue
                ci = col_to_idx(ref)
                tp = c.get("t")
                v = c.find(f"{NS}v")
                isv = c.find(f"{NS}is")
                val = ""
                if tp == "s" and v is not None:
                    val = shared[int(v.text)]
                elif isv is not None:
                    val = "".join(x.text or "" for x in isv.iter(f"{NS}t"))
                elif v is not None:
                    val = v.text or ""
                val = (val or "").replace("\n", " ").strip()
                if val:
                    cells[ci] = val
                    mx = max(mx, ci)
            if cells:
                rows.append([cells.get(i, "") for i in range(mx + 1)])
        out.append((name, rows))
    return out


def norm_url(u: str) -> str:
    u = (u or "").strip().lower()
    u = re.sub(r"^https?://", "", u)
    u = re.sub(r"^www\.", "", u)
    return u.rstrip("/")


def url_to_path(u: str) -> str:
    n = norm_url(u)
    if "stpatricks.ie/" in n:
        return n.split("stpatricks.ie/", 1)[1]
    return n


def full_old_url(path: str) -> str:
    path = (path or "").strip().lstrip("/")
    return f"https://www.stpatricks.ie/{path}" if path else ""


def to_staging_url(permalink: str, post_id: str = "", old_path: str = "") -> str:
    p = (permalink or "").strip()
    if p and "?p=" not in p:
        return re.sub(r"^https?://[^/]+", STAGING_BASE.rstrip("/"), p, count=1)
    if old_path:
        return f"{STAGING_BASE}/{old_path.strip('/')}/"
    if post_id:
        return f"{STAGING_BASE}/?p={post_id}"
    return ""


def migration_action(client_status: str, old_url: str) -> str:
    s = (client_status or "").strip().lower()
    if s in {"n/a", "na"}:
        return "N/A"
    if "matrix" in s or "believe comes from matrix" in s:
        return "Matrix to provide"
    if s in DRAFTING_STATUSES or "drafting" in s or s in {"draft completed", "not started", "in development"}:
        if s == "draft completed":
            return "Client copy ready"
        return "Client drafting"
    if any(m in s for m in MIGRATE_STATUSES) or old_url.startswith("http"):
        return "Migrate"
    return "TBD"


def editorial_action(notes: str) -> tuple[str, str]:
    n = (notes or "").lower()
    if not notes.strip():
        return "None", ""
    if "covid" in n:
        return "Remove COVID refs", notes
    if "update" in n and ("link" in n or "podcast" in n):
        return "Update link", notes
    if "add content" in n or "ad content" in n:
        return "Merge content", notes
    if "broadcast" in n:
        return "Remove broadcast links", notes
    if "before publishing" in n or "may need" in n:
        return "Review before publish", notes
    return "Other", notes


def load_migrated(path: Path) -> dict[str, dict]:
    by_path: dict[str, dict] = {}
    if not path.is_file():
        return by_path
    with path.open(newline="", encoding="utf-8") as f:
        for row in csv.DictReader(f):
            p = norm_url(row.get("old_path", ""))
            if p:
                by_path[p] = row
    return by_path


def load_sitemap_canonical(path: Path) -> dict[str, dict]:
    by_path: dict[str, dict] = {}
    if not path.is_file():
        return by_path
    with path.open(newline="", encoding="utf-8") as f:
        for row in csv.DictReader(f):
            p = norm_url(row.get("old_path", ""))
            if p:
                by_path[p] = row
    return by_path


def reconcile(path: str, migrated: dict[str, dict], migration_action_val: str, editorial: str) -> str:
    if migration_action_val == "Matrix to provide":
        return "Matrix to provide"
    if migration_action_val == "Client drafting":
        return "Client drafting"
    if migration_action_val in ("N/A", "Client copy ready"):
        return migration_action_val
    if migration_action_val == "Data blocker" or not path:
        return "Data blocker"
    if editorial not in ("None", "", "Content exclusions"):
        return "Editorial hold"
    if path in migrated:
        st = migrated[path].get("post_status", "")
        if st == "publish":
            return "Published"
        return "Draft imported"
    if migration_action_val == "Migrate":
        return "Not in WP"
    return "TBD"


def next_id(prefix: str, n: int) -> str:
    return f"{prefix}-{n:03d}"


def parse_sitemap(items: list[dict], counters: dict[str, int]) -> None:
    path = BASE / "Content for new website to migrate - sitemap breakdown (1).xlsx"
    for sheet, rows in read_xlsx(path):
        menu = sheet.replace(" menu", "")
        is_footer = sheet == "Footer menu"
        for row in rows:
            if not row or row[0] in ("Page", "") or (len(row) > 1 and row[1] == "Status"):
                continue
            if is_footer:
                title = row[0]
                status = row[2] if len(row) > 2 else ""
                url_or_notes = row[3] if len(row) > 3 else ""
                url = url_or_notes if url_or_notes.startswith("http") else ""
                notes = url_or_notes if not url else ""
                if "matrix" in status.lower() or "matrix" in notes.lower():
                    notes = url_or_notes
                    url = ""
            else:
                title = row[0]
                status = row[1] if len(row) > 1 else ""
                url = row[2] if len(row) > 2 else ""
                notes = row[3] if len(row) > 3 else ""
            if "believe comes from matrix" in notes.lower():
                status = "Need Matrix input"
            act = migration_action(status, url)
            if "need matrix input" in status.lower():
                act = "Matrix to provide"
            ed_act, ed_notes = editorial_action(notes)
            if "not to be copied" in notes.lower() or "not to copy" in notes.lower():
                ed_act = "Content exclusions"
            counters["page"] += 1
            pid = next_id("PAGE", counters["page"])
            items.append(
                {
                    "ID": pid,
                    "Source file": path.name,
                    "Menu / Section": menu,
                    "Content type": "page",
                    "Title": title,
                    "Client status": status,
                    "Migration action": act,
                    "Old URL": url if url.startswith("http") else "",
                    "Exclusions": notes if ed_act == "Content exclusions" else "",
                    "Editorial action": ed_act,
                    "Editorial notes": ed_notes if ed_act not in ("None", "Content exclusions") else notes,
                    "Target": "",
                    "Publish date": "",
                }
            )


def slug_to_title(url: str) -> str:
    path = url_to_path(url).rstrip("/").split("/")[-1]
    return path.replace("-", " ").strip().title() or url


def parse_news(items: list[dict], counters: dict[str, int]) -> None:
    path = BASE / "News and Event section - Matrix info (1).xlsx"
    type_map = {
        "News posts": ("news", "NEWS", 3),
        "Blogs": ("blog", "BLOG", 4),
        "Podcasts": ("podcasts", "POD", 5),
        "Videos": ("videos", "VID", 4),
    }
    by_url: dict[str, dict] = {}

    for sheet, rows in read_xlsx(path):
        if sheet not in type_map:
            continue
        target_cat, prefix, _ = type_map[sheet]
        for row in rows:
            if not row:
                continue
            title = row[0].strip() if row[0] else ""
            if title.endswith("overview") or title in ("Blog overview", "Podcast overview", "Video overview"):
                continue

            url = next((c for c in row if isinstance(c, str) and c.startswith("http")), "")
            if not url:
                continue

            date_val = ""
            notes = ""
            if sheet == "News posts":
                if len(row) > 1 and re.match(r"^\d", row[1]):
                    date_val = row[1]
                if len(row) > 3 and row[3] and not row[3].startswith("http"):
                    notes = row[3]
            elif sheet == "Blogs":
                if len(row) > 1 and re.match(r"^\d", row[1]):
                    date_val = row[1]
                if len(row) > 2 and row[2].strip():
                    notes = row[2].strip()
            elif sheet == "Podcasts":
                if len(row) > 2 and re.match(r"^\d", row[2]):
                    date_val = row[2]
                if len(row) > 3 and row[3].strip():
                    notes = row[3].strip()
            else:
                if len(row) > 1 and re.match(r"^\d", row[1]):
                    date_val = row[1]

            if not title:
                title = slug_to_title(url)

            ed_act, ed_notes = editorial_action(notes)
            if url in by_url:
                existing = by_url[url]
                if notes and existing["Editorial action"] == "None":
                    existing["Editorial action"] = ed_act
                    existing["Editorial notes"] = ed_notes
                elif notes and ed_act != "None":
                    combined = f"{existing['Editorial notes']}; {ed_notes}".strip("; ")
                    existing["Editorial notes"] = combined.replace("; ; ", "; ")
                    existing["Editorial action"] = ed_act
                continue

            counters[prefix.lower()] = counters.get(prefix.lower(), 0) + 1
            item = {
                "ID": next_id(prefix, counters[prefix.lower()]),
                "Source file": path.name,
                "Menu / Section": sheet,
                "Content type": "post",
                "Title": title,
                "Client status": "For migration",
                "Migration action": "Migrate",
                "Old URL": url,
                "Exclusions": "",
                "Editorial action": ed_act,
                "Editorial notes": ed_notes,
                "Target": target_cat,
                "Publish date": date_val,
            }
            by_url[url] = item
            items.append(item)


def resolve_clinician_url(title: str, url: str) -> tuple[str, str, str]:
    """Return (url, migration_action, editorial_notes)."""
    title_l = title.lower().strip()
    url = url.strip()

    if "bipolar" in title_l and not url.startswith("http"):
        return (
            "https://www.stpatricks.ie/st-patricks-mental-health-services-enewsletter/september-2021/early-diagnosis-of-bipolar-in-primary-care",
            "Migrate",
            "URL corrected — client sheet had title text instead of link",
        )

    if (
        "under-represented" in title_l
        and url.startswith("http")
        and "early-intervention-eating-disorders" in url
    ):
        return (
            "https://www.stpatricks.ie/st-patricks-mental-health-services-enewsletter/autumn-2024/under-represented-groups-eating-disorders",
            "Migrate",
            "URL corrected — client sheet duplicated the Summer 2024 eating disorders link",
        )

    if not url.startswith("http"):
        return ("", "Data blocker", "Missing URL")

    return (url, "Migrate", "")


def parse_clinician(items: list[dict], counters: dict[str, int]) -> None:
    path = BASE / "Clinician insights content to migrate (1).xlsx"
    for sheet, rows in read_xlsx(path):
        for row in rows:
            if not row or row[0] in ("Blog overview",) or row[0].startswith("Blog overview"):
                continue
            title = row[0]
            url = next((c for c in row if c.startswith("http")), "")
            date_val = row[-1] if len(row) > 2 and not row[-1].startswith("http") else ""
            url, act, ed_notes = resolve_clinician_url(title, url)
            ed = "None" if act != "Data blocker" else "Missing URL"
            counters["gp"] += 1
            items.append(
                {
                    "ID": next_id("GP", counters["gp"]),
                    "Source file": path.name,
                    "Menu / Section": "Clinician insights",
                    "Content type": "post",
                    "Title": title,
                    "Client status": "For migration",
                    "Migration action": act,
                    "Old URL": url,
                    "Exclusions": "",
                    "Editorial action": ed if ed != "None" else "None",
                    "Editorial notes": ed_notes,
                    "Target": "clinician-insights",
                    "Publish date": date_val,
                }
            )


def parse_media_folders() -> list[dict]:
    path = BASE / "Suggested structure for saving files and images on new site (1).xlsx"
    folders: list[dict] = []
    n = 0
    for sheet, rows in read_xlsx(path):
        kind = "document" if "Document" in sheet else "image"
        l1 = ""
        for row in rows[1:]:
            if not row:
                continue
            if row[0]:
                l1 = row[0]
            parts = [l1]
            if len(row) > 1 and row[1]:
                parts.append(row[1])
            if len(row) > 2 and row[2]:
                parts.append(row[2])
            if len(row) > 3 and row[3]:
                parts.append(row[3])
            notes = row[4] if len(row) > 4 else ""
            n += 1
            folders.append(
                {
                    "ID": f"MEDIA-{n:03d}",
                    "Content type": kind,
                    "Folder path": " › ".join(p for p in parts if p),
                    "Notes": notes,
                    "Populated": "N",
                }
            )
    return folders


def enrich_items(items: list[dict], migrated: dict[str, dict], sitemap_canonical: dict[str, dict] | None = None) -> None:
    sitemap_canonical = sitemap_canonical or {}
    for item in items:
        old_path = url_to_path(item["Old URL"]) if item["Old URL"] else ""
        canonical = sitemap_canonical.get(old_path, {})
        m = canonical if canonical else migrated.get(old_path, {})
        post_id = m.get("post_id", "")
        permalink = m.get("permalink", "")
        target_path = (canonical.get("target_path") or "").strip().strip("/")
        if target_path:
            item["Target"] = f"/{target_path}/"
        item["WP post ID"] = post_id
        item["WP status"] = m.get("post_status", "none") if m else "none"
        item["Staging URL"] = to_staging_url(permalink, post_id, target_path or old_path)
        lookup = dict(migrated)
        lookup.update(sitemap_canonical)
        item["Reconciliation"] = reconcile(
            old_path, lookup, item["Migration action"], item["Editorial action"]
        )
        item["Owner"] = "Client" if item["Migration action"] == "Client drafting" else "Matrix"
        item["Done?"] = "Y" if item["Reconciliation"] == "Published" else ""
        item["Flagged?"] = "Yes" if item["Editorial action"] not in ("None", "", "Content exclusions") else ""


def find_surplus(migrated: dict[str, dict], client_paths: set[str]) -> list[dict]:
    surplus = []
    for path, row in sorted(migrated.items()):
        if path not in client_paths:
            surplus.append(
                {
                    "old_path": path,
                    "old_url": full_old_url(path),
                    "post_id": row.get("post_id", ""),
                    "post_type": row.get("post_type", ""),
                    "post_status": row.get("post_status", ""),
                    "staging_url": to_staging_url(
                        row.get("permalink", ""), row.get("post_id", ""), path
                    ),
                    "title": row.get("title", ""),
                    "note": "In WP from bulk crawl; not on client curated lists — draft candidate",
                }
            )
    return surplus


def find_blockers(items: list[dict]) -> list[dict]:
    blockers: list[dict] = []
    url_titles: dict[str, list[str]] = defaultdict(list)
    seen_ids: set[str] = set()

    for item in items:
        if item["Old URL"]:
            url_titles[item["Old URL"]].append(item["Title"])

    actionable = {
        "Data blocker",
        "Editorial hold",
        "Matrix to provide",
        "Not in WP",
    }

    for item in items:
        rec = item.get("Reconciliation", "")
        if rec not in actionable and item["Migration action"] not in ("Data blocker", "Matrix to provide"):
            continue
        if item["ID"] in seen_ids:
            continue
        seen_ids.add(item["ID"])
        blockers.append(dict(item))

    for url, titles in url_titles.items():
        if len(titles) <= 1:
            continue
        for item in items:
            if item["Old URL"] != url:
                continue
            item = dict(item)
            item["Editorial action"] = "Duplicate URL"
            item["Editorial notes"] = f"Same old URL as: {', '.join(t for t in titles if t != item['Title'])}"
            if item["ID"] not in seen_ids:
                seen_ids.add(item["ID"])
                blockers.append(item)

    return blockers


def build_client_actions(blockers: list[dict]) -> list[dict]:
    """Focused list for client/Matrix — data fixes, editorial holds, matrix-owned."""
    actions = []
    for item in blockers:
        rec = item.get("Reconciliation", "")
        if rec == "Client copy ready":
            continue
        issue = item["Editorial action"]
        if rec == "Data blocker":
            issue = "Missing or invalid URL"
        elif rec == "Matrix to provide":
            issue = "Matrix to provide"
        elif issue in ("None", ""):
            continue
        owner = "Client" if rec == "Data blocker" or issue == "Missing URL" else "Matrix"
        if rec == "Matrix to provide":
            owner = "Matrix"
        actions.append(
            {
                "Priority": "High"
                if rec in ("Data blocker", "Matrix to provide") or issue == "Missing URL"
                else "Medium",
                "ID": item["ID"],
                "Title": item["Title"],
                "Section": item["Menu / Section"],
                "Issue": issue,
                "Detail": item["Editorial notes"] or item["Exclusions"] or item["Client status"],
                "Old URL": item["Old URL"],
                "Staging URL": item["Staging URL"],
                "Owner": owner,
            }
        )
    return actions


TRACKER_COLS = [
    "ID",
    "Source file",
    "Menu / Section",
    "Content type",
    "Title",
    "Client status",
    "Migration action",
    "Old URL",
    "Exclusions",
    "Editorial action",
    "Editorial notes",
    "Flagged?",
    "Target",
    "Publish date",
    "WP post ID",
    "WP status",
    "Staging URL",
    "Reconciliation",
    "Owner",
    "Done?",
]

NEWS_EVENTS_COLS = [
    "ID",
    "Sheet",
    "Title",
    "Publish date",
    "Old URL",
    "Staging URL",
    "WP post ID",
    "WP status",
    "Editorial action",
    "Editorial notes",
    "Flagged?",
    "Reconciliation",
    "On client list?",
]

CLINICIAN_INSIGHTS_COLS = [
    "ID",
    "Title",
    "Publish date",
    "Target category",
    "Old URL",
    "Staging URL",
    "WP post ID",
    "WP status",
    "Migration action",
    "Editorial action",
    "Editorial notes",
    "Reconciliation",
]

SITEMAP_PAGES_COLS = [
    "ID",
    "Menu / Section",
    "Title",
    "Old URL",
    "Target path",
    "Staging URL",
    "WP post ID",
    "WP status",
    "Migration action",
    "Client status",
    "Reconciliation",
]


def write_csv(path: Path, rows: list[dict], fieldnames: list[str]) -> None:
    with path.open("w", newline="", encoding="utf-8") as f:
        w = csv.DictWriter(f, fieldnames=fieldnames, extrasaction="ignore")
        w.writeheader()
        w.writerows(rows)


def build_summary_md(items: list[dict], folders: list[dict], surplus: list[dict]) -> str:
    rec = Counter(i["Reconciliation"] for i in items)
    act = Counter(i["Migration action"] for i in items)
    by_section = Counter(i["Menu / Section"] for i in items)
    today = date.today().isoformat()
    lines = [
        "# Content migration — reconciliation summary",
        "",
        f"Generated: {today}",
        "",
        "## Totals",
        "",
        f"- **Tracker items (pages + posts):** {len(items)}",
        f"- **Media folder taxonomy rows:** {len(folders)}",
        f"- **WP surplus (in site, not on client lists):** {len(surplus)}",
        "",
        "## Reconciliation outcomes",
        "",
        "| Outcome | Count |",
        "|---|---|",
    ]
    for k, v in rec.most_common():
        lines.append(f"| {k} | {v} |")
    lines += [
        "",
        "## Migration action",
        "",
        "| Action | Count |",
        "|---|---|",
    ]
    for k, v in act.most_common():
        lines.append(f"| {k} | {v} |")
    lines += [
        "",
        "## By section (top 15)",
        "",
        "| Section | Count |",
        "|---|---|",
    ]
    for k, v in by_section.most_common(15):
        lines.append(f"| {k} | {v} |")
    return "\n".join(lines) + "\n"


def build_client_md(items: list[dict], folders: list[dict], blockers: list[dict]) -> str:
    rec = Counter(i["Reconciliation"] for i in items)
    migrate_items = [i for i in items if i["Migration action"] == "Migrate"]
    published = sum(1 for i in migrate_items if i["Reconciliation"] == "Published")
    draft = sum(1 for i in migrate_items if i["Reconciliation"] == "Draft imported")
    not_in = sum(1 for i in migrate_items if i["Reconciliation"] == "Not in WP")
    editorial = sum(1 for i in items if i["Reconciliation"] == "Editorial hold")
    client_drafting = rec.get("Client drafting", 0)
    today = date.today().isoformat()

    data_blockers = [i for i in blockers if i["Reconciliation"] == "Data blocker" or i["Migration action"] == "Data blocker"]
    matrix_owned = [i for i in items if i["Migration action"] == "Matrix to provide"]

    return f"""# St Patrick's — content migration review

**Prepared by Matrix · {today}**

This document reviews the four Excel files you shared, reconciles them against content already imported into the new WordPress site, and proposes a simpler way to track progress going forward.

**Important:** No content has been deleted from the new site as part of this review. Existing pages and posts remain in place.

---

## 1. What you sent us

| File | What it covers |
|---|---|
| **Content for new website to migrate — sitemap breakdown** | Full site map: ~144 pages across 9 menus, each with a status (drafting vs migrate) |
| **News and Event section — Matrix info** | Curated news, blogs, podcasts, and videos (~233 items) |
| **Clinician insights content to migrate** | 16 GP eNewsletter articles for Healthcare Professionals |
| **Suggested structure for saving files and images** | Proposed folder tree for documents (53 paths) and images (75 paths) |

---

## 2. Where we are today

We compared your lists against what is already in the new WordPress site (487 migrated items from the earlier crawl/import).

| Category | Count |
|---|---|
| Items on your lists marked **client drafting** (we will not auto-migrate) | {client_drafting} |
| Items you want **migrated from the old site** | {len(migrate_items)} |
| — Already **published** on the new site | {published} |
| — Imported as **draft** (needs review before publish) | {draft} |
| — **Not yet in WordPress** | {not_in} |
| Items with **editorial notes** (manual work before publish) | {editorial} |

Many news and blog articles from the original bulk import already exist as drafts. That does not mean they are ready to publish — items with editorial notes (COVID references, link updates, etc.) still need your review.

---

## 3. Media folder structure

WordPress stores uploads in a flat Media Library (files on disk use date-based folders, not named folders). Your spreadsheet describes a **taxonomy** — how editors should find assets — not a literal filesystem.

**Our recommendation:** Use a **media-folder plugin** (e.g. FileBird or Real Media Library) to recreate your proposed tree as **virtual folders** inside the Media Library. Editors get the folder experience you designed; WordPress keeps standard upload behaviour.

**Questions for you:**

1. Do PDF/document URLs need to reflect the folder path, or is admin findability enough?
2. Are yearly subfolders (e.g. Founders Day by year) essential, or can year be metadata?
3. Will Matrix populate the tree during migration, or will your team maintain it ongoing?

---

## 4. Data we need from you

Before we treat the clinician insights list as complete:

| Issue | Article | Action needed |
|---|---|---|
| ~~Missing URL~~ | Overcoming challenges to early diagnosis of bipolar affective disorder… | **Resolved** — URL corrected in tracker (`september-2021/early-diagnosis-of-bipolar-in-primary-care`) |
| ~~Duplicate URL~~ | Two articles pointed to the same `early-intervention-eating-disorders` link | **Resolved** — under-represented groups article URL corrected in tracker |
| Missing date | Guidance on diagnosing depression | Please confirm publish date |

**Editorial holds:** {editorial} items across news/blogs/podcasts have notes such as "remove COVID references before publishing" or "update podcast link". These are flagged in our tracker — we will not auto-publish them.

**Content exclusions (sitemap):**

- **Anxiety** — do not copy the "Find out more about anxiety" section/links
- **Depression** — do not copy the treatment-options section or #NoStigma campaign links

**Matrix-owned (from sitemap):**

- Footer **Sitemap** page
- Footer **Accessibility** page (accessibility standards copy)

---

## 5. A better way to track everything

Your four files use different column layouts, which makes progress hard to see. We suggest **one shared tracker** (Google Sheet or Excel) with a single **Items** tab:

- One row per page, post, or asset
- Columns for **client status**, **old URL**, **editorial notes**, **exclusions**
- Matrix-updated columns: **new URL**, **WP status**, **reconciliation** (not started / draft / published / editorial hold)
- A **Summary** tab with counts by section
- A **Media folders** tab from your structure file

We have prepared a draft: `CONTENT-MIGRATION-TRACKER-v2.csv` in the project `old/content/` folder for your review. Your original Excel files are unchanged.

---

## 6. Next steps

1. **You:** Confirm publish date for *Guidance on diagnosing depression* and editorial notes on flagged news items.
2. **You:** Confirm the unified tracker format works for your team (or suggest changes).
3. **Matrix:** Continue importing remaining items as **drafts only** — never overwriting or deleting existing content.
4. **Together:** Agree media folder approach (plugin + who populates).

---

## Appendix: reconciliation key

| Status | Meaning |
|---|---|
| **Client drafting** | You are writing new copy — we skip auto-migration |
| **Published** | Live on the new site |
| **Draft imported** | In WordPress as draft — needs review |
| **Not in WP** | On your list but not yet imported |
| **Editorial hold** | Imported or pending, but notes require manual edit first |
| **Data blocker** | Missing or broken URL in your sheet |
| **Matrix to provide** | Copy/assets Matrix will supply |
"""


def main() -> None:
    migrated_path = BASE / "current-migrated-state-refreshed.csv"
    if not migrated_path.is_file():
        migrated_path = BASE / "current-migrated-state.csv"
    migrated = load_migrated(migrated_path)
    sitemap_canonical = load_sitemap_canonical(BASE / "sitemap-canonical-state.csv")

    items: list[dict] = []
    counters: dict[str, int] = defaultdict(int)
    parse_sitemap(items, counters)
    parse_news(items, counters)
    parse_clinician(items, counters)
    enrich_items(items, migrated, sitemap_canonical)

    folders = parse_media_folders()
    client_paths = {url_to_path(i["Old URL"]) for i in items if i["Old URL"]}
    surplus = find_surplus(migrated, client_paths)
    blockers = find_blockers(items)
    client_actions = build_client_actions(blockers)

    write_csv(BASE / "CONTENT-MIGRATION-TRACKER-v2.csv", items, TRACKER_COLS)

    news_items = [
        {
            "ID": i["ID"],
            "Sheet": i["Menu / Section"],
            "Title": i["Title"],
            "Publish date": i["Publish date"],
            "Old URL": i["Old URL"],
            "Staging URL": i["Staging URL"],
            "WP post ID": i["WP post ID"],
            "WP status": i["WP status"],
            "Editorial action": i["Editorial action"],
            "Editorial notes": i["Editorial notes"],
            "Flagged?": i.get("Flagged?", ""),
            "Reconciliation": i["Reconciliation"],
            "On client list?": "Yes",
        }
        for i in items
        if "News and Event section" in i.get("Source file", "")
    ]
    write_csv(BASE / "NEWS-AND-EVENTS-TRACKER.csv", news_items, NEWS_EVENTS_COLS)

    clinician_items = [
        {
            "ID": i["ID"],
            "Title": i["Title"],
            "Publish date": i["Publish date"],
            "Target category": i["Target"],
            "Old URL": i["Old URL"],
            "Staging URL": i["Staging URL"],
            "WP post ID": i["WP post ID"],
            "WP status": i["WP status"],
            "Migration action": i["Migration action"],
            "Editorial action": i["Editorial action"],
            "Editorial notes": i["Editorial notes"],
            "Reconciliation": i["Reconciliation"],
        }
        for i in items
        if i.get("Menu / Section") == "Clinician insights"
    ]
    write_csv(BASE / "CLINICIAN-INSIGHTS-TRACKER.csv", clinician_items, CLINICIAN_INSIGHTS_COLS)

    sitemap_items = [
        {
            "ID": i["ID"],
            "Menu / Section": i["Menu / Section"],
            "Title": i["Title"],
            "Old URL": i["Old URL"],
            "Target path": i.get("Target", ""),
            "Staging URL": i["Staging URL"],
            "WP post ID": i["WP post ID"],
            "WP status": i["WP status"],
            "Migration action": i["Migration action"],
            "Client status": i["Client status"],
            "Reconciliation": i["Reconciliation"],
        }
        for i in items
        if "sitemap breakdown" in i.get("Source file", "")
        and i.get("Migration action") == "Migrate"
        and i.get("Old URL")
    ]
    write_csv(BASE / "SITEMAP-PAGES-TRACKER.csv", sitemap_items, SITEMAP_PAGES_COLS)
    write_csv(
        BASE / "MEDIA-FOLDER-TAXONOMY.csv",
        folders,
        ["ID", "Content type", "Folder path", "Notes", "Populated"],
    )
    write_csv(
        BASE / "RECONCILIATION-SURPLUS.csv",
        surplus,
        ["old_path", "old_url", "post_id", "post_type", "post_status", "staging_url", "title", "note"],
    )
    write_csv(BASE / "GAP-BLOCKER-LIST.csv", blockers, TRACKER_COLS)
    write_csv(
        BASE / "CLIENT-ACTION-ITEMS.csv",
        client_actions,
        ["Priority", "ID", "Title", "Section", "Issue", "Detail", "Old URL", "Staging URL", "Owner"],
    )

    (BASE / "RECONCILIATION-SUMMARY.md").write_text(
        build_summary_md(items, folders, surplus), encoding="utf-8"
    )
    (BASE / "CLIENT-CONTENT-REVIEW.md").write_text(
        build_client_md(items, folders, blockers), encoding="utf-8"
    )

    print(f"Items: {len(items)}")
    print(f"Media folders: {len(folders)}")
    print(f"Surplus: {len(surplus)}")
    print(f"Blockers: {len(blockers)}")
    print(f"Client actions: {len(client_actions)}")
    print(Counter(i["Reconciliation"] for i in items).most_common())


if __name__ == "__main__":
    main()
