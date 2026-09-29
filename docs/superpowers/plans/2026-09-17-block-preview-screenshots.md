# Section Preview Screenshots Implementation Plan

**Goal:** All 39 picker cards show a preview — 8 from real pages, 5 from a private section-library page — and generation failures stop being silent.

**Spec:** `docs/superpowers/specs/2026-09-17-block-preview-screenshots-design.md`

**Architecture:** A new `Matrix_Preview_Library` owns the private library page and its sample content. Preview resolution changes only by appending that page to the end of the existing scan order. A new admin action computes the gaps and reuses the existing async capture job.

## Global Constraints

- Library page must never win over a real-site screenshot, and must never affect usage counts.
- Fixture overrides are nested ACF row shapes for `update_field()`, not flattened `parent__0__child` form keys.
- Reuse the existing async job, status JSON and `matrix_preview_progress` polling. No new capture code.
- Everything except the actual Playwright capture must be verifiable without `node_modules` installed.
- Do not commit unless asked. Do not run `npm install` without asking.

## Task 1 — `Matrix_Preview_Library` (page + fixtures)

**New file:** `includes/class-matrix-preview-library.php`, loaded from `matrix-content-export.php`.

- `OPTION = 'matrix_export_preview_library_page_id'`, `get_page_id()`, `page_exists()`.
- `rebuild()`: create-or-reuse a `private` page titled "Section Library (previews)", default template, then `update_field(FLEX_FIELD, $rows)` with one row per `discover_layouts()` slug. Idempotent — same post ID, equivalent rows on a second run.
- `build_row($slug)`: `get_default_row_values($slug)` merged with `get_fixtures()[$slug]` (fixture wins per top-level key).
- `get_fixtures()`: nested-ACF overrides for `about`, `cta_block`, `services` only. Two or three repeater rows of short text, `#` links, placeholder image ID.
- `get_placeholder_image_id()`: most recent image attachment, `0` if none; filterable.

**Verify:** rebuild twice — same page ID, 39 rows, every `acf_fc_layout` valid, status `private`; every fixture key exists in that layout's schema.

## Task 2 — Resolution + usage isolation

**File:** `includes/class-matrix-flexible-pages.php`

- `collect_posts_for_layout_scan()`: append the library page ID **last**, so real-site files win.
- `count_layout_usage_on_site()` and the hint-page list: exclude the library page ID.

**Verify:** with both a real-site and a library JPEG present the real URL wins; with only a library JPEG the library URL is returned; usage counts unchanged by the library page.

## Task 3 — Preflight

**File:** `includes/class-matrix-export.php`

- `check_preview_capture_prerequisites()`: returns `['ok' => bool, 'message' => string]` after checking the resolved node binary, `node_modules/playwright` in the plugin dir, and a Chromium build. Message names the exact `npm install` command.
- Call it at the top of `generate_block_previews_async()` and return the failure instead of spawning; surface it on the existing client-link buttons too.

**Verify:** with `node_modules` absent (current state) the check fails, returns the remediation text, and spawns no process.

## Task 4 — Gap-fill action + button

**Files:** `matrix-content-export.php`, `templates/admin-page.php`

- `Matrix_Export::get_layouts_missing_previews()`: layouts whose resolved preview URL is empty.
- `Matrix_Export::build_missing_preview_tasks()`: per missing layout, first real site instance if any, else the library instance.
- Admin POST handler `matrix_previews_action=fill_missing` under `manage_options` + its own nonce: preflight → `Matrix_Preview_Library::rebuild()` → build tasks → `generate_block_previews_async()`.
- Button "Generate missing section previews" with a count of what's missing, wired to the existing progress bar.

**Verify:** handler returns a job key and a task list matching the 13 missing layouts; preflight failure short-circuits with a visible notice.

## Task 5 — Tests

**New file:** `scripts/test-preview-library.php`, run via `wp eval-file`, cleaning up after itself.

Covers the seven spec assertions: rows-per-layout, idempotency, fixture field validity, resolution precedence, resolution fallback, usage-count isolation, preflight failure.

## Task 6 — Manual finish (needs `npm install`)

Ask before installing Playwright. Then run the button and confirm 39 of 39 layouts report a preview.

## Operational note: capture must run from a logged-in request

`get_capture_cookies_for_browser()` forwards the *current request's* cookies to Playwright, so previews must be generated from the admin screen while logged in. Two things depend on it here:

- The `password-protected` plugin redirects every front-end URL to a password wall for anonymous visitors, so an unauthenticated run captures nothing.
- The library page is `private`, so it only renders for a user who can read private pages.

A run triggered from WP-CLI has no cookies and silently produces zero images while still reporting every task "completed". When verifying from the command line, inject admin cookies first:

```php
$_COOKIE[AUTH_COOKIE] = wp_generate_auth_cookie(1, time() + DAY_IN_SECONDS, 'auth');
$_COOKIE[LOGGED_IN_COOKIE] = wp_generate_auth_cookie(1, time() + DAY_IN_SECONDS, 'logged_in');
```

The `stats` block in the job status file distinguishes the failure modes: `no_target_matches` means the page loaded but the anchor was absent (the signature of a password wall or a private page), while `capture_errors` means the screenshot itself failed.

Re-running the button rebuilds the library page's rows, so any manual edits to that page are replaced.
