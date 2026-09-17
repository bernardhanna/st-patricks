# Builder Section Fields: Complete Schema and Rendering

**Date:** 2026-09-17
**Status:** Approved
**Scope:** `matrix-content-gathering` client form — flexible ("builder") pages at `/content-editing/`

## Problem

On builder pages (`matrix_client_flexible_page` = `1`), many flexi sections show incomplete or unusable fields:

1. **Missing fields.** 21 of the 39 registered `flexible_content_blocks` layouts contain a `repeater` or `group`. A section *added* through the builder exposes none of those nested fields, so cards, slides, accordion items, services, partners, testimonials, timeline entries and contact rows cannot be filled in.
2. **Raw code values.** 14 fields visible in the form render as plain text inputs showing the stored code rather than a readable choice — for example `content/image_height_mode` shows `match_text` and `about/buttons_style` shows `outline`.

### Measured baseline

| Metric | Value |
|---|---|
| Registered flexi layouts | 39 |
| Layouts containing a repeater or group | 21 |
| Maximum repeater nesting depth | 3 |
| Nested repeaters at depth ≥ 2 | 9 |
| `select` fields (all levels) | 97 |
| `true_false` fields (all levels) | 23 |
| Choice fields visible in the form with no readable labels | 14 (7 `select`, 7 `true_false`) |

Depth-3 examples: `contact_directory/columns/items/bullet_items`, `content_accordion/items/content_rows/pdf_documents`.

### Mechanisms

| Symptom | Cause |
|---|---|
| Nested fields absent from builder sections | `Matrix_Export::get_flex_layout_form_schema()` iterates only top-level `sub_fields` and `continue`s on `repeater`, `group`, `flexible_content`, `tab`, `accordion` |
| Repeater parents filtered out | `content_rows`, `link_cards`, `external_links`, `pdf_documents`, `links` are listed in `get_form_excluded_field_patterns()['exact']`, so `is_content_field()` rejects them |
| Codes instead of labels | No code path reads ACF's `choices`. `get_form_select_field_choices()` is a hard-coded map covering only `media_type`, `background_type`, `video_source_type`, `background_video_type` / `video_provider` |
| Booleans as free text in builder | `renderField()` in `assets/flexible-builder.js` handles `link`, `image`, `wysiwyg`, `textarea` only; everything else falls through to `<input type="text">` |

Existing sections appear partially populated only because `renderSectionBlock()` back-fills unknown draft keys with a guessed label and type. Newly added sections have no draft values, so they get nothing.

## Goals

- Every one of the 39 layouts exposes all of its editable content fields in the builder, including nested repeater rows to depth 3.
- Clients can add, remove and reorder repeater rows generically, for any repeater.
- Choice fields render as readable dropdowns or Yes/No radios in **both** the builder and the fixed form.

## Non-goals

- Exposing `color_picker` or other design controls (`heading_tag`, padding, radii, overlays, breadcrumbs). These stay excluded.
- Client-editable `relationship`, `post_object` or `taxonomy` pickers inside builder sections. These render as read-only notes.
- Changing the flattened key convention (`parent__0__child`) or the save pipeline's nesting logic.
- Replacing `accordion-faq-add.js`. It creates real `faqs` CPT posts, which is a separate concern from generic repeater rows.

## Design

### 1. Recursive field schema (PHP)

Replace the flat loop in `Matrix_Export::get_flex_layout_form_schema($layout)` with a recursive walk that returns a tree.

Leaf node shape:

```php
[
    'name'     => 'image_height_mode',
    'label'    => 'Image height mode',
    'type'     => 'select',
    'choices'  => [
        'match_text' => 'Match Text Height',
        'fixed_min'  => 'Fixed Minimum Height (19.5rem)',
        'contain'    => 'Contain beside text (202px)',
    ],
    'optional' => true,
]
```

Container node shape:

```php
[
    'name'       => 'items',
    'label'      => 'Items',
    'type'       => 'repeater',
    'min'        => 0,
    'max'        => 0,
    'sub_fields' => [ /* recursive leaf and container nodes */ ],
]
```

Filtering rules, which are the crux of the missing-field fix:

- For `repeater` and `group`: **recurse without applying `is_content_field()`**, then include the container only if the recursion produced at least one editable descendant. This admits `content_rows`, `link_cards`, `external_links`, `pdf_documents` and `links` as containers while keeping them out of the flat fixed form's field list.
- For leaves: keep the existing `is_content_field()` filter and `is_optional_content_field()` flag.
- `tab` fields hold no value and never carry `sub_fields` (verified: 77 tabs, 0 with sub-fields), so skip them outright.
- Nested `flexible_content` is out of scope; skip it.

The `useful_links` fallback that injects a synthetic `links__0__link` field is removed, because the recursive walk now emits the real `links` repeater.

### 2. Shared choice resolution (PHP)

New method:

```php
Matrix_Export::get_form_field_choices(array $acf_field, $current_value = ''): array
```

Resolution order:

1. `get_form_select_field_choices($name, $current_value)` — preserves the curated vocabularies where ACF choices are ambiguous or absent (`media_type`, `video_source_type` and its two competing sets, `background_video_type` / `video_provider`).
2. The ACF definition's own `choices` array — this automatically covers all 97 `select` fields plus the single `radio`.
3. `true_false` → `get_yes_no_choices()` (`['0' => 'No', '1' => 'Yes']`).
4. Otherwise an empty array, meaning "render as a normal input".

### 3. Fixed form uses the same resolution (PHP)

The fixed form renders from flattened values and only knows a field *name*, so it cannot see the ACF definition today. New resolver:

```php
Matrix_Export::resolve_acf_field_for_form_key($block_type, $form_key): ?array
```

It strips `__\d+__` index segments from the flattened key (`items__2__content_rows__0__title` → `items` → `content_rows` → `title`) and walks the layout's ACF tree to return the matching field definition.

In `templates/export-client-form.php`, the existing `get_form_select_field_choices($field_name, $select_value_hint)` call is replaced by a lookup that first resolves the ACF definition via `$block_type` and then calls `get_form_field_choices()`. When the definition cannot be resolved (for example `post_fields` rows), behaviour falls back to today's curated map, so nothing regresses.

Result: `image_height_mode` and `buttons_style` become dropdowns in the fixed form too, not only the builder.

### 4. Complete field renderer (JS)

`renderField()` in `assets/flexible-builder.js` gains handlers, driven by the schema's `type` and `choices`:

| Type | Rendering |
|---|---|
| `select`, `radio`, `button_group` | `<select>`; radio group when exactly 2 choices, matching the fixed form's `$use_radios` convention |
| `true_false` | Yes/No radio pair |
| `number` | `<input type="number">` |
| `url`, `email` | matching typed input |
| `date_picker` | `<input type="date">` |
| `file` | media picker reusing the image picker markup with a file media type |
| `taxonomy`, `relationship`, `post_object` | read-only help text explaining the value is managed in wp-admin |
| `link`, `image`, `wysiwyg`, `textarea`, `text` | unchanged |

### 5. Generic repeater UI (JS)

New recursive `renderRepeater(rowIndex, field, values, path)`:

- Each row renders in a labelled wrapper ("Item N") with **Remove**, **↑** and **↓** controls.
- A **"+ Add {label}"** button appends a row.
- Input names use the existing convention, so no save changes are needed: `matrix_field[{rowIndex}][{path}__{n}__{subName}]`.
- Nested repeaters recurse, carrying the parent path, supporting the measured depth of 3.
- Add, remove and reorder call a `renumberRepeaterRows()` helper that rewrites the numeric segment for that repeater **and the path prefix of every nested descendant input**.
- Row count respects `min` and `max`: Remove is refused below `min` (minimum 1 where the repeater has editable leaves), Add is disabled at `max` when `max` > 0.
- A newly added section seeds `min` rows, or 1 row when `min` is 0, so clients see an editable row immediately.
- WYSIWYG and media pickers are re-initialised for added rows via the existing `refreshWysiwyg()` path.

Because the generic repeater now covers `useful_links`, the `useful_links` special case is removed from `renderSectionBlock()`, and `assets/useful-links-add.js` is narrowed to fixed-form blocks only (`.block[data-block-type="useful_links"]`), dropping its `.matrix-flex-section-block[data-layout="useful_links"]` selector so the two UIs cannot both mount.

### 6. Save-path safety net (PHP)

`assign_nested_field_value()` already expands arbitrary depth, so saving needs no structural change. However, removing a row client-side can produce non-contiguous indices (`0, 2, 3`), which ACF does not expect for repeaters. Before `update_field()`, recursively re-index any array whose keys are all integers (`ksort`, then `array_values`). Arrays with string keys — ACF link and image shapes — are left untouched.

## Testing

Extend `scripts/test-flexible-builder-all-layouts.php` and add assertions:

1. **Schema coverage:** every one of the 39 layouts returns a non-empty schema.
2. **Repeater coverage:** each of the 21 repeater layouts exposes at least one nested leaf beneath its container.
3. **Depth:** `content_accordion` exposes a leaf at depth 3 under `items/content_rows/pdf_documents`.
4. **Choice labels:** no visible choice field (`select`, `radio`, `button_group`, `true_false`) resolves to an empty `choices` array. This assertion pins the 14 known offenders and prevents regressions.
5. **Round trip:** submit a depth-3 repeater payload through `Matrix_Import::handle_form_submit()` and read the values back with `get_field()`, confirming nesting and ordering survive.
6. **Re-index:** submit a payload with a gap (`items__0__…`, `items__2__…`) and assert the saved repeater has contiguous rows.

Manual verification on local: open a builder page, add one section of each repeater layout, confirm rows can be added, reordered and removed, confirm `image_height_mode` and `buttons_style` are dropdowns with readable labels, save, and confirm the front end renders the values.

## Files touched

| File | Change |
|---|---|
| `includes/class-matrix-export.php` | Recursive `get_flex_layout_form_schema()`; new `get_form_field_choices()`; new `resolve_acf_field_for_form_key()` |
| `includes/class-matrix-import.php` | Recursive integer-key re-index before `update_field()` |
| `templates/export-client-form.php` | Fixed-form choice lookup via the ACF resolver |
| `assets/flexible-builder.js` | Full type renderer; recursive repeater UI with add/remove/reorder; drop `useful_links` special case |
| `assets/useful-links-add.js` | Narrow to fixed-form blocks only |
| `scripts/test-flexible-builder-all-layouts.php` | The six assertions above |
