# Section Preview Screenshots For Every Block — Design

**Date:** 2026-09-17
**Status:** Approved

## Problem

The "Add a section" picker in the client content form shows a preview thumbnail per flexible-content layout. Only 26 of 39 layouts have one; the remaining 13 render as text-only cards, so clients pick sections blind.

Measured on the current site, the 13 split into two causes that need different fixes:

**Used on the site but never photographed (8).** A real instance exists on a published page, but screenshot generation only ever runs against the pages attached to a client link, and these pages were never in one.

| Layout | Site uses | First instance (post#index) |
|---|---|---|
| `contact_directory` | 1 | 273#1 |
| `contact_form` | 4 | 3933#3 |
| `locations_grid` | 5 | 201#6 |
| `locations_map` | 1 | 273#2 |
| `programmes_therapies_archive` | 2 | 646#3 |
| `related_cards` | 3 | 3620#5 |
| `timeline` | 1 | 272#2 |
| `wysiwyg` | 1 | 230#4 |

**Never used anywhere (5).** `about`, `blog_filter_archive`, `cta_block`, `research_project_archive`, `services`. There is nothing on the site to photograph, so re-running the generator can never produce an image for these.

A third problem sits underneath both: when Playwright is unavailable the generator **fails silently**. The plugin's `node_modules` is currently absent, so the existing buttons spawn a background job that produces nothing and reports no error.

## Goals

- All 39 picker cards show a preview: 8 from real pages, 5 from a library page.
- New ACF layouts get covered automatically, without hand-maintaining a list.
- Generation failures are visible instead of silent.

## Non-goals

- Re-shooting the 26 previews that already work.
- Screenshotting every block instance site-wide (hundreds of images; the picker needs one per layout).
- Replacing the per-block previews shown against existing form rows (`ensure_block_preview_urls()`), which stay as they are.
- A WP-CLI trigger. Playwright reuses the admin request's cookies to pass the site password gate, so the run has to originate from a logged-in admin browser request.

## Existing mechanism (unchanged parts)

| Concern | Location |
|---|---|
| Picker preview resolution | `Matrix_Flexible_Pages::get_layout_preview_urls()`, `collect_posts_for_layout_scan()` |
| File path / URL | `Matrix_Export::get_block_preview_filename()`, `get_block_preview_url_if_exists()` |
| Async job + status | `Matrix_Export::generate_block_previews_async()` |
| Capture | `scripts/capture-block-previews.mjs` (Playwright, 1440×2200, JPEG q72, 120 tasks/run) |
| Anchor targeting | `Matrix_Export::get_block_anchor_id()` → `{layout-with-dashes}-{index+1}`, matched to `data-matrix-block` in theme flexi templates |
| Storage | `wp-content/uploads/matrix-content-export-previews/{post_id}-{block_source}-{block_index}-{block_type}.jpg` (220 files today) |
| Admin page | `tools.php?page=matrix-content-export`, progress via `wp_ajax_matrix_preview_progress` |

## Architecture

Three additions, each independently testable:

1. **Section library page** — a private page holding one instance of every layout, giving the generator something to photograph for unused layouts.
2. **Gap-fill run** — an admin-triggered job that screenshots only the layouts currently missing a preview.
3. **Preflight** — a capability check that blocks a doomed run and explains how to fix it.

Resolution itself barely changes: the library page is appended to the **end** of the existing post scan order, so a real-site screenshot always wins and the library only fills gaps.

### 1. Section library page

A single page created on demand:

- `post_status = private`. Playwright already forwards the admin session cookie, so a private page renders for the screenshotter while staying out of menus, search, sitemaps, and public view.
- Default page template. `page.php` already calls `load_flexible_content_templates()`, so blocks render exactly as on a real page, including the `data-matrix-block` anchors the capture script targets.
- Title "Section Library (previews)"; ID stored in a new option `matrix_export_preview_library_page_id` so the page is stable across rebuilds.
- Flexible content holds **one row per layout, for all 39** — not only today's missing 13 — so a layout that later loses its real-site instance stays covered.
- Rebuild is idempotent: it replaces the flex rows in place on the same post ID. Re-running after a new ACF layout is added picks the new layout up. Running it twice produces the same page.

Row content is `Matrix_Flexible_Pages::get_default_row_values($slug)` merged with fixture overrides (below).

**Usage counting must exclude this page.** `count_layout_usage_on_site()` and the admin "Used N× on site" badges scan the same content; without an exclusion every layout would gain a phantom use. The library page ID is filtered out of both the usage scan and the hint-page list.

### 2. Sample content fixtures

ACF defaults are thin — mostly `heading = "Section heading"` and `description = "Your text here…"`, with empty repeaters, empty link fields and no images. A section rendered from defaults alone is recognisable for text-led layouts but empty for card-led ones.

Fixtures are therefore applied only where defaults leave the section visually empty. Inspecting the 5 unused layouts gives a concrete initial list:

| Layout | Fixture needed | What defaults leave empty |
|---|---|---|
| `about` | yes | `key_points` repeater (0 rows), `image_left` / `image_right` groups, `primary_cta` link |
| `cta_block` | yes | `button` link, `polaroid_image` |
| `services` | yes | `cards` repeater (0 rows), `view_more_button` link |
| `blog_filter_archive` | no | nothing — no repeaters, links or images; queries real posts |
| `research_project_archive` | no | nothing — same |

The fixture map lives in one file as `slug => [field overrides]`. Overrides are **nested ACF row shapes**, matching what `get_default_row_values()` returns and what `update_field()` expects — repeaters as lists of row arrays, links as `['url' => …, 'title' => …, 'target' => …]`, images as attachment IDs. They are *not* the flattened `parent__0__child` form keys the client builder uses; no flattening or decoding is involved in building the library page.

Each fixture supplies two or three repeater rows of short realistic text, a placeholder image, and a `#` link where a button would otherwise not render. Layouts absent from the map fall back to defaults, so the map only grows when a specific layout screenshots badly.

The placeholder image is resolved once per rebuild by a small filterable helper: it takes the most recent image attachment already in the media library, and if the library has none it leaves image fields empty rather than failing — the section still renders and still makes a usable thumbnail. Nothing new is uploaded, so the rebuild has no side effects outside the library page itself.

### 3. Gap-fill run

A "Generate missing section previews" button on the Content Gathering admin page. On submit:

1. Preflight (below). Abort with a message if it fails.
2. Ensure the library page exists and its rows are current.
3. Compute layouts with no preview, using the same resolution as the picker.
4. For each, build one task: the first real site instance if one exists, else the library instance.
5. Hand the tasks to the existing `generate_block_previews_async()` and reuse the existing progress bar and `matrix_preview_progress` polling.

This is ~13 tasks today, well under the 120-per-run cap, so no batching is needed. Reusing the existing job means no new capture, status or progress code.

Capability and nonce handling follow the existing client-link actions: `manage_options`, a dedicated nonce action.

### 4. Preflight

Before spawning a job, verify the node binary resolves, `node_modules/playwright` is present in the plugin directory, and a Chromium build is available. On failure, show the exact remediation (`npm install` inside the plugin directory) and do not spawn the job. This also applies to the existing client-link screenshot buttons, which share the same silent-failure mode.

## Error handling

- A layout whose capture fails keeps its text-only card. No broken image is ever rendered, because resolution requires the file to exist on disk.
- Per-layout failures are recorded in the job status so a single bad layout does not hide behind an overall "done".
- `wysiwyg` is a known anchor risk: the policy variant emits `wysiwyg-policy-{index}-{section}` rather than the `wysiwyg-{index+1}` the anchor helper expects, so its capture may fall through to the section-index fallback and photograph the wrong section. If the real-page capture looks wrong, the library instance is the fallback.

## Testing

| Area | Assertion |
|---|---|
| Library builder | Produces one row per discovered layout; every row's `acf_fc_layout` is a valid slug |
| Library builder | Idempotent — two consecutive rebuilds leave the same post ID and equivalent rows |
| Fixtures | Each fixture's field names exist in that layout's schema (guards against silent typos) |
| Resolution precedence | With both a real-site file and a library file present, the real-site URL wins |
| Resolution fallback | With only a library file present, the library URL is returned |
| Usage counting | Library page does not change `count_layout_usage_on_site()` results |
| Preflight | Returns a clear error, and spawns no job, when `node_modules` is absent |

These run as a `wp eval-file` script under `scripts/`, following the existing test convention (`test-flexible-builder-all-layouts.php` and friends) and cleaning up any page it creates.

Manual verification: run the button, watch progress complete, then confirm the picker reports 39 of 39 layouts with a preview and spot-check a library-sourced card against the real section.

## Prerequisite

`npm install` inside `wp-content/plugins/matrix-content-gathering` (Playwright plus its Chromium download). Nothing in this design can be verified end to end until that is done.
