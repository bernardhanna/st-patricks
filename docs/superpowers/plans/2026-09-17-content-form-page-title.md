# Content Form Page Title Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let clients edit the WordPress page/post title from `/content-editing/` via a pinned field at the top of each page tab.

**Architecture:** Reuse existing `core_post_title` on the `post_fields` row and the existing import → `wp_update_post` path. Enable the field globally, render it once at the top of each tab, skip it in the core-fields loop to avoid duplicates, and ignore empty title submits.

**Tech Stack:** WordPress plugin `matrix-content-gathering` (PHP templates + `Matrix_Export` / `Matrix_Import`), option `matrix_export_disabled_form_fields`.

**Spec:** `docs/superpowers/specs/2026-09-17-content-form-page-title-design.md`

## Global Constraints

- Field key stays `core_post_title` (no new field name).
- Do not change `post_name` / slug on title update.
- Empty/whitespace title submit must keep the previous title.
- If `core_post_title` is in disabled form fields, hide the pinned control too.
- Do not commit unless the user explicitly asks.

## File map

| File | Responsibility |
|------|----------------|
| `wp-content/plugins/matrix-content-gathering/includes/class-matrix-import.php` | Skip empty titles in `decode_post_fields_update` |
| `wp-content/plugins/matrix-content-gathering/templates/export-client-form.php` | Pinned title UI; skip duplicate in core fields loop |
| `wp-content/plugins/matrix-content-gathering/matrix-content-export.php` (or one-shot option update) | Remove `core_post_title` from live disabled-fields option |
| `wp-content/plugins/matrix-content-gathering/scripts/test-core-post-title-field.php` | Eval-file smoke test for empty-title guard + enablement |

---

### Task 1: Empty-title guard on save

**Files:**
- Modify: `wp-content/plugins/matrix-content-gathering/includes/class-matrix-import.php` (`decode_post_fields_update`)
- Test: `wp-content/plugins/matrix-content-gathering/scripts/test-core-post-title-field.php`

**Interfaces:**
- Consumes: existing `decode_post_fields_update(array $field_data): array`
- Produces: same method; omits `post_title` key when trimmed title is `''`

- [ ] **Step 1: Add failing eval-file assertions for empty title**

Create `scripts/test-core-post-title-field.php`:

```php
<?php
/**
 * Smoke test: core_post_title empty guard + disabled-list enablement.
 *
 * Run:
 *   wp eval-file wp-content/plugins/matrix-content-gathering/scripts/test-core-post-title-field.php
 */

if (!class_exists('Matrix_Import') || !class_exists('Matrix_Export')) {
    fwrite(STDERR, "Matrix_Import / Matrix_Export not loaded.\n");
    exit(1);
}

$ref = new ReflectionClass('Matrix_Import');
$method = $ref->getMethod('decode_post_fields_update');
$method->setAccessible(true);

$decoded_empty = $method->invoke(null, ['core_post_title' => '   ']);
if (array_key_exists('post_title', $decoded_empty)) {
    fwrite(STDERR, "FAIL: empty title should not set post_title\n");
    exit(1);
}

$decoded_ok = $method->invoke(null, ['core_post_title' => ' New Title ']);
if (($decoded_ok['post_title'] ?? null) !== 'New Title') {
    fwrite(STDERR, "FAIL: expected sanitized New Title\n");
    exit(1);
}

echo "PASS decode empty/non-empty title\n";
```

- [ ] **Step 2: Run test — expect FAIL on empty title still being set**

```bash
wp --path="/Users/bernardhanna/Local Sites/st-patricks/app/public" eval-file \
  wp-content/plugins/matrix-content-gathering/scripts/test-core-post-title-field.php
```

Expected: `FAIL: empty title should not set post_title`

- [ ] **Step 3: Implement empty-title guard**

In `decode_post_fields_update`, replace the title branch with:

```php
if ($key === 'core_post_title' || $key === 'post_title') {
    $title = is_string($value) ? wp_unslash($value) : (string) $value;
    $title = sanitize_text_field($title);
    if ($title === '') {
        continue;
    }
    $out['post_title'] = $title;
    continue;
}
```

- [ ] **Step 4: Re-run test — expect PASS for decode assertions**

Same `wp eval-file` command. Expected: `PASS decode empty/non-empty title`

---

### Task 2: Enable `core_post_title` + pin it in the form UI

**Files:**
- Modify: `wp-content/plugins/matrix-content-gathering/templates/export-client-form.php`
- Modify: `wp-content/plugins/matrix-content-gathering/matrix-content-export.php` (one-time option cleanup on `admin_init` or plugin load)
- Extend: `scripts/test-core-post-title-field.php`

**Interfaces:**
- Consumes: `Matrix_Export::POST_FIELDS_SOURCE`, `Matrix_Export::get_disabled_form_fields()`, row `core_post_title` / `post_title`, draft `$draft_fields_by_index`
- Produces: pinned input `matrix_field[{i}][core_post_title]` when field is not disabled

- [ ] **Step 1: Extend test to assert disabled list no longer contains `core_post_title` after enable helper runs**

Append to the test file:

```php
$option = defined('MATRIX_EXPORT_DISABLED_FORM_FIELDS_OPTION')
    ? MATRIX_EXPORT_DISABLED_FORM_FIELDS_OPTION
    : 'matrix_export_disabled_form_fields';

if (function_exists('matrix_export_ensure_core_post_title_enabled')) {
    matrix_export_ensure_core_post_title_enabled();
}

$disabled = Matrix_Export::get_disabled_form_fields();
if (in_array('core_post_title', $disabled, true)) {
    fwrite(STDERR, "FAIL: core_post_title still disabled\n");
    exit(1);
}
echo "PASS core_post_title enabled\n";
```

- [ ] **Step 2: Add enable helper in `matrix-content-export.php`**

Near other option helpers:

```php
/**
 * Ensure clients can edit Page / Post Title (core_post_title).
 * Idempotent: removes the key from the global disabled-fields list if present.
 */
function matrix_export_ensure_core_post_title_enabled() {
    $option = defined('MATRIX_EXPORT_DISABLED_FORM_FIELDS_OPTION')
        ? MATRIX_EXPORT_DISABLED_FORM_FIELDS_OPTION
        : '';
    if ($option === '') {
        return;
    }
    $disabled = get_option($option, []);
    if (!is_array($disabled)) {
        return;
    }
    $next = array_values(array_filter($disabled, static function ($key) {
        return sanitize_key((string) $key) !== 'core_post_title';
    }));
    if ($next !== array_values($disabled)) {
        update_option($option, $next, false);
    }
}
add_action('init', 'matrix_export_ensure_core_post_title_enabled', 20);
```

- [ ] **Step 3: Pin title UI + skip duplicate in `export-client-form.php`**

Inside each tab panel, **before** the `foreach ($page_other_rows as $row)` loop:

1. Find the `post_fields` row for `$pid`.
2. If found and `core_post_title` is not in `Matrix_Export::get_disabled_form_fields()`:
   - Resolve value from draft override, else `$row['core_post_title']`, else `$row['post_title']`.
   - Render a `.block.matrix-page-title-block` with label “Page / Post Title” and:

```php
<input
  type="text"
  id="matrix-field-<?php echo (int) $i; ?>-core_post_title"
  name="matrix_field[<?php echo (int) $i; ?>][core_post_title]"
  value="<?php echo esc_attr($title_value); ?>"
/>
```

Also output the standard hidden row metadata inputs for that `post_fields` row (`matrix_post_id[]`, `matrix_block_source[]`, etc.) **only if** this pinned block is the first place that row is rendered — otherwise keep the existing core-fields block for other fields and only add the title input once.

Preferred structure (avoid double row submit):

- Keep the existing `post_fields` block render.
- At the top, if enabled, render the title input using the **same** `$i` as that row.
- In the core-fields `foreach ($row_field_keys as $field_name)`, skip when `$field_name === 'core_post_title'`.

Concrete skip inside the field loop:

```php
if ($field_name === 'core_post_title') {
    continue;
}
```

Pinned block sketch (find `$post_fields_row` first):

```php
$disabled_form_fields = Matrix_Export::get_disabled_form_fields();
$show_pinned_title = !in_array('core_post_title', $disabled_form_fields, true);
$post_fields_row = null;
foreach ($page_other_rows as $candidate) {
    if (($candidate['block_source'] ?? '') === Matrix_Export::POST_FIELDS_SOURCE) {
        $post_fields_row = $candidate;
        break;
    }
}
if ($show_pinned_title && is_array($post_fields_row)) {
    $i = (int) $post_fields_row['_global_index'];
    $draft_row_fields = isset($draft_fields_by_index[$i]) && is_array($draft_fields_by_index[$i])
        ? $draft_fields_by_index[$i]
        : [];
    $title_value = '';
    if (array_key_exists('core_post_title', $draft_row_fields)) {
        $title_value = (string) $draft_row_fields['core_post_title'];
    } elseif (isset($post_fields_row['core_post_title'])) {
        $title_value = (string) $post_fields_row['core_post_title'];
    } elseif (isset($post_fields_row['post_title'])) {
        $title_value = (string) $post_fields_row['post_title'];
    }
    ?>
    <div class="block matrix-page-title-block" data-row-index="<?php echo (int) $i; ?>">
        <div class="field">
            <label class="field-label" for="matrix-field-<?php echo (int) $i; ?>-core_post_title">Page / Post Title</label>
            <input
                id="matrix-field-<?php echo (int) $i; ?>-core_post_title"
                type="text"
                name="matrix_field[<?php echo (int) $i; ?>][core_post_title]"
                value="<?php echo esc_attr($title_value); ?>"
            />
        </div>
    </div>
    <?php
}
```

Note: the existing block loop still emits `matrix_post_id[]` / source hiddens for the `post_fields` row — do **not** duplicate those in the pinned block.

- [ ] **Step 4: Run eval-file test**

```bash
wp --path="/Users/bernardhanna/Local Sites/st-patricks/app/public" eval-file \
  wp-content/plugins/matrix-content-gathering/scripts/test-core-post-title-field.php
```

Expected:

```
PASS decode empty/non-empty title
PASS core_post_title enabled
```

- [ ] **Step 5: Manual check on local**

1. Open a content-editing URL for a normal page and a titled CPT.
2. Confirm pinned “Page / Post Title” at top of the tab.
3. Confirm it is not repeated under Core post fields.
4. Change title → submit → title updates in WP; slug unchanged.
5. Clear title → submit → previous title retained.

---

## Spec coverage checklist

| Spec item | Task |
|-----------|------|
| Pinned field at top of tab | Task 2 |
| Bind `matrix_field[i][core_post_title]` | Task 2 |
| Save via existing import | unchanged (Task 1 guards empty) |
| Empty submit keeps previous | Task 1 |
| No slug change | no code changes needed (`wp_update_post` title-only) |
| No duplicate in core fields | Task 2 |
| Remove from disabled list; respect re-disable | Task 2 |
| Flexible pages still show pinned title | Task 2 (uses `page_other_rows` post_fields row) |
