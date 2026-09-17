# Builder Section Fields Implementation Plan

**Goal:** Every flexi layout exposes all editable fields in the builder (including nested repeaters to depth 3), with choice fields rendering as readable dropdowns in both forms.

**Spec:** `docs/superpowers/specs/2026-09-17-builder-section-fields-design.md`

**Architecture:** One recursive PHP schema becomes the single source of truth for builder fields; choice labels come from ACF's own `choices`; the builder JS gains a complete type renderer plus a generic recursive repeater UI. The save pipeline is unchanged apart from a contiguous re-index safety net.

## Global Constraints

- Flattened key convention stays `parent__0__child` — `assign_nested_field_value()` already handles arbitrary depth.
- `color_picker` and design controls (`heading_tag`, padding, radii, overlay, breadcrumb) stay excluded.
- `taxonomy` / `relationship` / `post_object` inside builder sections render as read-only notes.
- Fixed-form boolean rendering must not change (existing Yes/No path keeps ownership of `true_false`).
- Do not commit unless the user asks.

## Task 1 — PHP: recursive schema + shared choice resolution

**File:** `includes/class-matrix-export.php`

- Add `get_flex_layout_definition($layout)`: returns the ACF layout definition by slug (shared by schema + resolver).
- Add `get_form_field_choices(array $acf_field, $current_value = '')`: curated map → ACF `choices` → `true_false` Yes/No → `[]`.
- Rewrite `get_flex_layout_form_schema($layout)` to delegate to a new recursive `build_form_field_schema(array $sub_fields, $sample_path)`:
  - `repeater` / `group`: recurse **without** `is_content_field()`; include container only if it yields ≥1 editable descendant; carry `min` / `max` / `sub_fields`.
  - Leaves: keep `is_content_field()`; attach `choices` and `optional`.
  - Skip `tab`, `message`, nested `flexible_content`.
  - Emitted `name` is **relative**; `$sample_path` (repeater index `0`) is used only for `is_content_field()` / label / optional decisions so `^items__\d+__title$` style rules still match.
- Remove the synthetic `links__0__link` fallback (the real `links` repeater is now emitted).

**Verify:** `wp eval` — all 39 layouts non-empty; all 21 repeater layouts expose nested leaves; `content_accordion` reaches depth 3; zero visible choice fields with empty `choices`.

## Task 2 — PHP: resolver wired into the fixed form

**Files:** `includes/class-matrix-export.php`, `templates/export-client-form.php`

- Add `resolve_acf_field_for_form_key($layout, $form_key)`: strip `__matrix_link_(url|title)` suffix and all numeric segments, then walk the layout tree.
- Add `get_form_choices_for_form_key($layout, $form_key, $current_value = '')`: resolve definition → `get_form_field_choices()`; **skip `true_false`** so the existing Yes/No path keeps ownership; fall back to `get_form_select_field_choices()`.
- In `export-client-form.php` (~line 843) swap `get_form_select_field_choices($field_name, $select_value_hint)` for `get_form_choices_for_form_key((string) $block_type, $field_name, $select_value_hint)`.

**Verify:** `content/image_height_mode` resolves to 3 labelled choices; `post_fields` keys still resolve to `null` without error.

## Task 3 — JS: complete field renderer

**File:** `assets/flexible-builder.js`

Extend `renderField()`: `select`/`radio`/`button_group` → `<select>`, or radios when exactly 2 choices (matches fixed form); `true_false` → Yes/No radios; `number`; `url`; `email`; `date_picker`; `file` → media picker; `taxonomy`/`relationship`/`post_object` → read-only note. Keep existing `link`, `image`, `wysiwyg`, `textarea`, `text`.

## Task 4 — JS: generic recursive repeater

**Files:** `assets/flexible-builder.js`, `assets/useful-links-add.js`

- `renderRepeater(rowIndex, field, values, path)`: rows in `.matrix-repeater-row` wrappers labelled "Item N" with Remove / ↑ / ↓, plus "+ Add {label}".
- Names: `matrix_field[{rowIndex}][{path}__{n}__{sub}]`; nested repeaters recurse with the composed path.
- `renumberRepeater(wrapper)`: prefix-replace `[{path}__{old}__` → `[{path}__{new}__` across every descendant input (covers all depths), then fix descendant `data-repeater-path` attributes and row labels.
- Respect `min` (refuse Remove below it, floor 1) and `max` (disable Add at it when > 0).
- Seed `min` rows (1 when `min` is 0) for newly added sections; re-init WYSIWYG/media via `refreshWysiwyg()`.
- Drop the `useful_links` special case from `renderSectionBlock()`; narrow `useful-links-add.js` to `.block[data-block-type="useful_links"]` only.

## Task 5 — PHP: contiguous re-index before save

**File:** `includes/class-matrix-import.php`

Recursively re-index arrays whose keys are all integers (`ksort` then `array_values`) before `update_field()`. Leave string-keyed arrays (ACF link/image shapes) untouched.

**Verify:** payload with `items__0__…` and `items__2__…` saves as rows 0 and 1.

## Task 6 — Tests

**File:** `scripts/test-flexible-builder-all-layouts.php`

Add the six spec assertions: schema coverage, repeater coverage, depth 3, choice labels, depth-3 round trip through `handle_form_submit()`, and gap re-index. Then manual check on a builder page.
