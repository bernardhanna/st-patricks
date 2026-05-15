# Webinars Archive Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a dedicated `webinars` CPT with taxonomy-driven filters, a real `/webinars/` archive, and a matching flexi block that renders the same searchable, paginated archive surface.

**Architecture:** Follow the same pattern used for the blog filter archive, but keep the webinars system dedicated rather than generic. Put CPT/taxonomy registration and webinar meta in the existing CPT/ACF locations, keep archive state/query logic in a helper file with focused Pest coverage, render cards through one shared webinars archive template, and feed that template from both `archive-webinars.php` and a thin flexi wrapper.

**Tech Stack:** WordPress PHP, ACF Builder, register-extended-post-type / register-extended-taxonomy patterns already used in `inc/cpts`, Pest/PHPUnit for helper coverage, Alpine for light archive UI state, local `wp eval` seeding, and browser verification on `/webinars/` and `/flexi/`.

---

## File Structure

- `inc/cpts/post-types/webinars.php`
  Registers the `webinars` CPT.
- `inc/cpts/taxonomies/webinar-type.php`
  Registers the `webinar_type` taxonomy.
- `acf-fields/partials/webinars.php`
  Adds webinar-specific ACF fields like date, time, and optional summary overrides.
- `inc/webinars-archive-functions.php`
  Keeps defaults, request-state resolution, query args, pagination URLs, and term/card-style helpers.
- `tests/Unit/WebinarsArchiveTest.php`
  Focused unit coverage for archive helper behavior.
- `template-parts/webinars/archive.php`
  Shared archive renderer used by both the real archive page and the flexi block.
- `acf-fields/partials/blocks/acf_webinars_archive.php`
  Thin block definition for the webinars archive surface.
- `template-parts/flexi/webinars_archive.php`
  Thin flexi wrapper that prepares arguments and includes the shared renderer.
- `archive-webinars.php`
  Real archive entry point for `/webinars/`, preparing archive args and including the shared renderer.
- `functions.php`
  Loads `inc/webinars-archive-functions.php`.

No generic “filterable archive” abstraction should be introduced in this plan.

### Task 1: Add helper coverage first

**Files:**
- Create: `inc/webinars-archive-functions.php`
- Modify: `functions.php`
- Create: `tests/Unit/WebinarsArchiveTest.php`

- [ ] **Step 1: Write the failing tests**

Create `tests/Unit/WebinarsArchiveTest.php` with:

```php
<?php

require_once dirname(__DIR__, 2) . '/inc/webinars-archive-functions.php';

test('webinars archive resolves request state against allowed webinar types', function () {
    expect(function_exists('matrix_resolve_webinars_archive_state'))->toBeTrue();

    $state = matrix_resolve_webinars_archive_state(
        [
            'webinar_type' => 'events',
            'webinar_search' => '  gp webinar  ',
            'webinar_page' => '3',
        ],
        ['events', 'webinars'],
        10
    );

    expect($state['type'])->toBe('events')
        ->and($state['search'])->toBe('gp webinar')
        ->and($state['paged'])->toBe(3)
        ->and($state['posts_per_page'])->toBe(10);
});

test('webinars archive falls back to all when the type is not allowed', function () {
    $state = matrix_resolve_webinars_archive_state(
        [
            'webinar_type' => 'other',
            'webinar_search' => '',
            'webinar_page' => '0',
        ],
        ['events', 'webinars'],
        10
    );

    expect($state['type'])->toBe('all')
        ->and($state['paged'])->toBe(1);
});

test('webinars archive builds query args for type search and paging', function () {
    expect(function_exists('matrix_build_webinars_archive_query_args'))->toBeTrue();

    $args = matrix_build_webinars_archive_query_args([
        'type' => 'webinars',
        'search' => 'mental health act',
        'paged' => 2,
        'posts_per_page' => 10,
    ], [
        'webinars' => 31,
        'events' => 32,
    ]);

    expect($args['post_type'])->toBe('webinars')
        ->and($args['post_status'])->toBe('publish')
        ->and($args['posts_per_page'])->toBe(10)
        ->and($args['paged'])->toBe(2)
        ->and($args['s'])->toBe('mental health act')
        ->and($args['tax_query'][0]['taxonomy'])->toBe('webinar_type')
        ->and($args['tax_query'][0]['terms'])->toBe([31]);
});

test('webinars archive preserves filters in pagination urls', function () {
    expect(function_exists('matrix_build_webinars_archive_page_url'))->toBeTrue();

    $url = matrix_build_webinars_archive_page_url(
        'http://localhost:10034/webinars/',
        [
            'type' => 'events',
            'search' => 'gp',
            'paged' => 1,
        ],
        4
    );

    expect($url)->toContain('webinar_type=events')
        ->and($url)->toContain('webinar_search=gp')
        ->and($url)->toContain('webinar_page=4');
});

test('webinars archive returns term-based card styles', function () {
    expect(function_exists('matrix_get_webinars_archive_card_theme'))->toBeTrue();

    expect(matrix_get_webinars_archive_card_theme('events')['card_background'])->toBe('#E4F4D6')
        ->and(matrix_get_webinars_archive_card_theme('webinars')['card_background'])->toBe('#E9E2F7')
        ->and(matrix_get_webinars_archive_card_theme('all')['card_background'])->toBe('#E9E2F7');
});
```

- [ ] **Step 2: Run the focused test to verify RED**

Run: `php vendor/bin/pest "tests/Unit/WebinarsArchiveTest.php"`

Expected: FAIL because the webinars archive helpers do not exist yet.

- [ ] **Step 3: Add the minimal helper implementation**

Create `inc/webinars-archive-functions.php` with:

```php
<?php

function matrix_webinars_archive_sanitize_slug($value)
{
    if (function_exists('sanitize_title')) {
        return sanitize_title($value);
    }

    $value = strtolower(trim((string) $value));
    $value = preg_replace('/[^a-z0-9\s-]/', '', $value);
    $value = preg_replace('/[\s-]+/', '-', $value);

    return trim((string) $value, '-');
}

function matrix_webinars_archive_add_query_args($params, $base_url)
{
    if (function_exists('add_query_arg')) {
        return add_query_arg($params, $base_url);
    }

    $parts = function_exists('wp_parse_url') ? wp_parse_url($base_url) : parse_url($base_url);
    $query = [];

    if (! empty($parts['query'])) {
        parse_str($parts['query'], $query);
    }

    $query = array_merge($query, $params);
    $query_string = http_build_query($query);

    $scheme = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
    $host = $parts['host'] ?? '';
    $port = isset($parts['port']) ? ':' . $parts['port'] : '';
    $path = $parts['path'] ?? '';

    return $scheme . $host . $port . $path . ($query_string !== '' ? '?' . $query_string : '');
}

function matrix_get_webinars_archive_defaults()
{
    return [
        'filter_label' => 'Filter by:',
        'search_placeholder' => 'Search webinars and events',
        'search_button_label' => 'Search',
        'posts_per_page' => 10,
        'empty_state_message' => 'No webinars matched your filters.',
    ];
}

function matrix_resolve_webinars_archive_state($request, $allowed_type_slugs = [], $posts_per_page = 10)
{
    $allowed_type_slugs = array_values(array_filter(array_map('matrix_webinars_archive_sanitize_slug', (array) $allowed_type_slugs)));
    $type = matrix_webinars_archive_sanitize_slug((string) ($request['webinar_type'] ?? 'all'));
    $search = trim((string) ($request['webinar_search'] ?? ''));
    $paged = (int) ($request['webinar_page'] ?? 1);
    $posts_per_page = (int) $posts_per_page;

    if ($posts_per_page < 1) {
        $posts_per_page = 10;
    }

    if ($paged < 1) {
        $paged = 1;
    }

    if ($type === '' || $type === 'all') {
        $type = 'all';
    } elseif ($allowed_type_slugs !== [] && ! in_array($type, $allowed_type_slugs, true)) {
        $type = 'all';
    }

    return [
        'type' => $type,
        'search' => $search,
        'paged' => $paged,
        'posts_per_page' => $posts_per_page,
    ];
}

function matrix_build_webinars_archive_query_args($state, $type_slug_to_id_map = [])
{
    $args = [
        'post_type' => 'webinars',
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
        'posts_per_page' => (int) ($state['posts_per_page'] ?? 10),
        'paged' => max(1, (int) ($state['paged'] ?? 1)),
    ];

    $search = trim((string) ($state['search'] ?? ''));
    if ($search !== '') {
        $args['s'] = $search;
    }

    $type = matrix_webinars_archive_sanitize_slug((string) ($state['type'] ?? 'all'));
    $type_id = (int) ($type_slug_to_id_map[$type] ?? 0);
    if ($type !== 'all' && $type_id > 0) {
        $args['tax_query'] = [[
            'taxonomy' => 'webinar_type',
            'field' => 'term_id',
            'terms' => [$type_id],
        ]];
    }

    return $args;
}

function matrix_build_webinars_archive_page_url($base_url, $state, $page)
{
    $page = max(1, (int) $page);
    $params = [
        'webinar_page' => $page,
    ];

    $type = matrix_webinars_archive_sanitize_slug((string) ($state['type'] ?? 'all'));
    if ($type !== '' && $type !== 'all') {
        $params['webinar_type'] = $type;
    }

    $search = trim((string) ($state['search'] ?? ''));
    if ($search !== '') {
        $params['webinar_search'] = $search;
    }

    return matrix_webinars_archive_add_query_args($params, $base_url);
}

function matrix_get_webinars_archive_card_theme($type_slug)
{
    $type_slug = matrix_webinars_archive_sanitize_slug($type_slug);

    if ($type_slug === 'events') {
        return [
            'badge_background' => '#C3DBAE',
            'card_background' => '#E4F4D6',
        ];
    }

    return [
        'badge_background' => '#B4A8CE',
        'card_background' => '#E9E2F7',
    ];
}
```

Add this near the other helper includes in `functions.php`:

```php
require_once get_template_directory() . '/inc/webinars-archive-functions.php';
```

- [ ] **Step 4: Run the focused test to verify GREEN**

Run: `php vendor/bin/pest "tests/Unit/WebinarsArchiveTest.php"`

Expected: PASS.

### Task 2: Register the CPT, taxonomy, and webinar meta fields

**Files:**
- Create: `inc/cpts/post-types/webinars.php`
- Create: `inc/cpts/taxonomies/webinar-type.php`
- Create: `acf-fields/partials/webinars.php`

- [ ] **Step 1: Register the `webinars` CPT**

Create `inc/cpts/post-types/webinars.php` with:

```php
<?php

add_action('init', function () {
    register_extended_post_type('webinars', [
        'menu_icon' => 'dashicons-video-alt3',
        'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'page-attributes'],
        'has_archive' => true,
        'rewrite' => ['slug' => 'webinars'],
        'show_in_rest' => true,
    ], [
        'singular' => 'Webinar',
        'plural' => 'Webinars',
        'slug' => 'webinars',
    ]);
});
```

- [ ] **Step 2: Register the `webinar_type` taxonomy**

Create `inc/cpts/taxonomies/webinar-type.php` with:

```php
<?php

add_action('init', function () {
    register_extended_taxonomy('webinar_type', ['webinars'], [
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'webinar-type'],
    ], [
        'singular' => 'Webinar Type',
        'plural' => 'Webinar Types',
        'slug' => 'webinar-type',
    ]);
});
```

- [ ] **Step 3: Add webinar-specific ACF fields**

Create `acf-fields/partials/webinars.php` with:

```php
<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

if (function_exists('acf_add_local_field_group')) {
    $webinar_fields = new FieldsBuilder('webinar_fields', [
        'title' => 'Webinar Fields',
    ]);

    $webinar_fields
        ->setLocation('post_type', '==', 'webinars')
        ->addDatePicker('webinar_date', [
            'label' => 'Webinar Date',
            'display_format' => 'd/m/Y',
            'return_format' => 'Ymd',
            'required' => 1,
        ])
        ->addTimePicker('webinar_time', [
            'label' => 'Webinar Time',
            'display_format' => 'g:i a',
            'return_format' => 'H:i:s',
            'required' => 1,
        ])
        ->addWysiwyg('webinar_summary', [
            'label' => 'Webinar Summary',
            'tabs' => 'all',
            'toolbar' => 'basic',
            'media_upload' => 0,
        ]);

    acf_add_local_field_group($webinar_fields->build());
}
```

- [ ] **Step 4: Lint the new registration/meta files**

Run:

```bash
php -l "inc/cpts/post-types/webinars.php"
php -l "inc/cpts/taxonomies/webinar-type.php"
php -l "acf-fields/partials/webinars.php"
```

Expected: all three report `No syntax errors detected`.

### Task 3: Build the shared webinars archive surface

**Files:**
- Create: `template-parts/webinars/archive.php`

- [ ] **Step 1: Build the shared surface contract**

The shared template should accept one array, e.g. `$webinars_archive`, containing:

- `filter_label`
- `search_placeholder`
- `search_button_label`
- `empty_state_message`
- `base_url`
- `state`
- `chips`
- `query`
- `pagination`
- optional wrapper/section classes

- [ ] **Step 2: Render the search and filter controls**

Render:

- search input
- submit button
- filter label
- chips sourced from `webinar_type`

Use query var names:

```php
<input type="hidden" name="webinar_type" x-ref="typeInput" :value="type" />
<input type="hidden" name="webinar_page" x-ref="pageInput" value="<?php echo esc_attr((string) $current_page); ?>" />
<input type="search" name="webinar_search" ... />
```

- [ ] **Step 3: Render the cards**

Each card should render:

```php
<article class="flex h-full flex-col rounded-[8px] p-6" style="background-color: <?php echo esc_attr($theme['card_background']); ?>;">
    <span class="inline-flex h-[30px] w-fit items-center justify-center rounded-full px-4 text-[14px] font-medium"
          style="background-color: <?php echo esc_attr($theme['badge_background']); ?>;">
        <?php echo esc_html($type_name); ?>
    </span>

    <h3 class="mt-4 font-primary text-[20px] font-semibold leading-[24px] tracking-[-0.12px] text-[#1E244B]">
        <a href="<?php echo esc_url($permalink); ?>">
            <?php echo esc_html($title); ?> →
        </a>
    </h3>

    <div class="mt-4 grid gap-1 text-[15px] font-semibold leading-[16px] tracking-[-0.09px] text-[#1E244B]">
        <p>Date: <?php echo esc_html($date_label); ?></p>
        <p>Time: <?php echo esc_html($time_label); ?></p>
    </div>

    <div class="mt-4 text-[14px] leading-[24px] text-[#1E244B]">
        <?php echo esc_html($summary); ?>
    </div>
</article>
```

- [ ] **Step 4: Render pagination**

Use the webinars page URL helper so active filter/search state persists across pagination links.

- [ ] **Step 5: Lint the shared template**

Run: `php -l "template-parts/webinars/archive.php"`

Expected: `No syntax errors detected`.

### Task 4: Wire the real archive and the flexi block

**Files:**
- Create: `acf-fields/partials/blocks/acf_webinars_archive.php`
- Create: `template-parts/flexi/webinars_archive.php`
- Create: `archive-webinars.php`

- [ ] **Step 1: Add the flexi block definition**

Create `acf-fields/partials/blocks/acf_webinars_archive.php` with a minimal, right-sized field set:

- `filter_label`
- `search_placeholder`
- `search_button_label`
- `posts_per_page`
- `allowed_types`
- `empty_state_message`
- `padding_settings`

Do **not** add a broad design tab unless implementation proves it is necessary.

- [ ] **Step 2: Build the thin flexi wrapper**

In `template-parts/flexi/webinars_archive.php`:

- read the block fields
- load allowed `webinar_type` terms
- build a slug-to-id map
- resolve state from `$_GET`
- run `WP_Query`
- prepare the shared `$webinars_archive` array
- include `template-parts/webinars/archive.php`

- [ ] **Step 3: Build the real `/webinars/` archive**

Create `archive-webinars.php` that:

- loads all relevant `webinar_type` terms
- resolves state from query vars / `$_GET`
- runs the same query pattern as the flexi wrapper
- prepares `$webinars_archive`
- includes `template-parts/webinars/archive.php`

- [ ] **Step 4: Lint the new archive/block files**

Run:

```bash
php -l "acf-fields/partials/blocks/acf_webinars_archive.php"
php -l "template-parts/flexi/webinars_archive.php"
php -l "archive-webinars.php"
```

Expected: all pass.

### Task 5: Seed taxonomy terms, webinar posts, and `/flexi/`

**Files:**
- No repository files; WordPress data only

- [ ] **Step 1: Seed taxonomy terms**

Create local terms:

- `events`
- `webinars`

- [ ] **Step 2: Seed webinar demo posts**

Create enough local webinar posts to demonstrate:

- both taxonomy terms
- search hits
- pagination beyond one page

Each post should include:

- title
- summary
- `webinar_date`
- `webinar_time`
- one `webinar_type` term

- [ ] **Step 3: Add the block to `/flexi/`**

Add one `webinars_archive` row to page `329` with sensible defaults so the shared surface is visible on `/flexi/`.

- [ ] **Step 4: Verify seed results**

Run:

```bash
curl -s "http://localhost:10034/webinars/" | rg -n "Filter by:|Search|Date:|Time:"
curl -s "http://localhost:10034/flexi/" | rg -n "Filter by:|Search webinars and events"
```

Expected: both pages show the new webinars surface.

### Task 6: Build and verify

**Files:**
- Review-only unless focused fixes are needed

- [ ] **Step 1: Rebuild assets**

Run: `npm run build`

Expected: exit code `0`.

- [ ] **Step 2: Run focused tests and lints**

Run:

```bash
php vendor/bin/pest "tests/Unit/WebinarsArchiveTest.php"
php -l "inc/webinars-archive-functions.php"
php -l "inc/cpts/post-types/webinars.php"
php -l "inc/cpts/taxonomies/webinar-type.php"
php -l "acf-fields/partials/webinars.php"
php -l "acf-fields/partials/blocks/acf_webinars_archive.php"
php -l "template-parts/flexi/webinars_archive.php"
php -l "template-parts/webinars/archive.php"
php -l "archive-webinars.php"
```

Expected: all pass.

- [ ] **Step 3: Check IDE diagnostics**

Read diagnostics for all newly added/edited webinars files.

- [ ] **Step 4: Browser-check `/webinars/`**

Verify:

- filters change the card set
- search narrows results
- pagination preserves filter/search state
- `events` cards use the green treatment
- `webinars` cards use the lavender treatment
- cards collapse to one column on smaller screens

- [ ] **Step 5: Browser-check `/flexi/`**

Verify:

- the same archive surface appears inside the flexi page
- filter/search/pagination behavior matches the real archive

### Task 7: Final review

**Files:**
- Review only

- [ ] **Step 1: Confirm the feature stayed right-sized**

Checklist:

- dedicated `webinars` CPT created
- `webinar_type` taxonomy powers the chips
- one shared webinars renderer exists
- both archive and flexi block use that renderer
- no generic archive abstraction introduced

- [ ] **Step 2: Note any intentional simplifications**

If any pagination or filter-control details are slightly simplified relative to the Figma while preserving the structure and behavior, record that explicitly in the final handoff.
