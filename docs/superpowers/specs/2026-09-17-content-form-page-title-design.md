# Content Form: Editable Page / Post Title

**Date:** 2026-09-17  
**Status:** Approved  
**Scope:** `matrix-content-gathering` client form (`/content-editing/`)

## Problem

Clients need to edit the WordPress page/post title from the content-gathering form. The form already has a `core_post_title` field and save path, but it is globally disabled and buried inside “Core post fields”, so it is easy to miss.

## Goal

Enable title editing and pin a single clear title field at the top of each page tab.

## Non-goals

- Editing permalink / slug
- SEO / Rank Math titles
- Live-updating tab labels while typing (next load is enough)
- Changing FAQ question-as-title behavior beyond existing FAQ fields

## Approach

Reuse existing `core_post_title` export/import plumbing. Promote the field in the UI; do not invent a second title key.

## Behavior

1. **Pinned field:** At the top of each page tab (before flex blocks / section builder), show one text input labeled “Page / Post Title”.
2. **Binding:** Input name remains `matrix_field[{post_fields_row_index}][core_post_title]`, using the page’s existing `post_fields` row.
3. **Save:** Existing import continues to map `core_post_title` → `post_title` via `wp_update_post`.
4. **Empty submit:** If the submitted title is empty/whitespace, keep the previous title (do not blank titles).
5. **Slug:** Do not change `post_name` when the title changes.
6. **No duplicate:** When rendering the Core post fields block, skip `core_post_title` so the pinned control is the only title input.
7. **Admin visibility:** Remove `core_post_title` from the current global disabled-fields option so it appears. Keep the admin “Hidden form fields” checkbox so it can be re-hidden later. If an admin re-disables it, hide the pinned field as well.

## Files likely touched

- `wp-content/plugins/matrix-content-gathering/templates/export-client-form.php` — pinned title UI; skip duplicate in core fields loop
- `wp-content/plugins/matrix-content-gathering/includes/class-matrix-import.php` — empty-title guard if not already present
- `wp-content/plugins/matrix-content-gathering` option `matrix_export_disabled_form_fields` — remove `core_post_title` from the live disabled list (one-time / migration-style update or admin default)

## Testing

- Open `/content-editing/` for a page and a CPT that supports titles: pinned title shows current `post_title`.
- Change title, submit, confirm WP admin / front-end title updated and slug unchanged.
- Submit empty title: previous title retained.
- With `core_post_title` in disabled fields: pinned field hidden; no duplicate appears in core fields when enabled.
- Flexible builder pages still show the pinned title above the builder.
