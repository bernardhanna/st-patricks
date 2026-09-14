#!/usr/bin/env python3
"""Generate to-migrate.csv from CONTENT-MIGRATION-TRACKER-v2.csv (insert-only queue)."""

from __future__ import annotations

import csv
import re
from pathlib import Path

BASE = Path(__file__).resolve().parent.parent / "old" / "content"
TRACKER = BASE / "CONTENT-MIGRATION-TRACKER-v2.csv"
OUT = BASE / "to-migrate.csv"
PENDING = BASE / "to-migrate-pending.csv"
MIGRATED = BASE / "current-migrated-state-refreshed.csv"


def norm_path(u: str) -> str:
    u = (u or "").strip().lower()
    u = re.sub(r"^https?://", "", u)
    u = re.sub(r"^www\.", "", u)
    if "stpatricks.ie/" in u:
        return u.split("stpatricks.ie/", 1)[1].rstrip("/")
    return u.rstrip("/")


def html_source(path: str) -> str:
    html_dir = BASE.parent / "html"
    if not html_dir.is_dir():
        return "fetch"
    safe = path.replace("/", "_")
    for name in html_dir.iterdir():
        if name.name.endswith(".html") and safe in name.name:
            return "local"
    return "fetch"


def main() -> None:
    migrated_paths: set[str] = set()
    if MIGRATED.is_file():
        with MIGRATED.open(newline="", encoding="utf-8") as f:
            for row in csv.DictReader(f):
                p = norm_path(row.get("old_path", ""))
                if p:
                    migrated_paths.add(p)

    rows_out: list[dict[str, str]] = []
    seen: set[str] = set()

    with TRACKER.open(newline="", encoding="utf-8") as f:
        for row in csv.DictReader(f):
            if row.get("Migration action") != "Migrate":
                continue
            url = (row.get("Old URL") or "").strip()
            if not url.startswith("http"):
                continue
            path = norm_path(url)
            if not path or path in seen:
                continue
            if path in migrated_paths or row.get("WP post ID", "").strip():
                continue
            seen.add(path)
            rows_out.append(
                {
                    "old_url": url,
                    "type": row.get("Content type", "page"),
                    "section": row.get("Menu / Section", ""),
                    "title": row.get("Title", ""),
                    "source": html_source(path),
                }
            )

    with OUT.open("w", newline="", encoding="utf-8") as f:
        w = csv.DictWriter(f, fieldnames=["old_url", "type", "section", "title", "source"])
        w.writeheader()
        w.writerows(rows_out)

    with PENDING.open("w", newline="", encoding="utf-8") as f:
        w = csv.DictWriter(f, fieldnames=["old_url", "type", "section", "title", "source"])
        w.writeheader()
        w.writerows(rows_out)

    print(f"Wrote {len(rows_out)} rows to {OUT} and {PENDING}")


if __name__ == "__main__":
    main()
