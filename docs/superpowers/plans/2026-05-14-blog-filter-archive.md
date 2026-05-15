# Blog Filter Archive Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a reusable `blog_filter_archive` surface that matches the approved Figma design, works as a flexi block on `/flexi/`, and provides the shared filter/search/card/pagination layer for the blog index and archive pages.

**Architecture:** Use a server-backed query model with Alpine.js handling only the UI state and form behavior. Put request-state parsing, query-arg building, and pagination URL logic into one helper file, render the archive UI through one shared blog partial, and let both the flexi block and the blog index template feed that shared renderer.

**Tech Stack:** WordPress theme PHP, ACF Builder, Alpine.js, Pest/PHPUnit, native `WP_Query`, local asset build via npm/webpack, `wp eval` or `wp post` seeding for demo data, and browser verification against the local WordPress site.

---

## File Structure

Planned file responsibilities:

- `inc/blog-filter-archive-functions.php`
  Central place for block defaults, request-state normalization, `WP_Query` args, and pagination URL helpers.
- `tests/Unit/BlogFilterArchiveTest.php`
  Focused helper coverage for state resolution, query building, and pagination preservation.
- `acf-fields/partials/blocks/acf_blog_filter_archive.php`
  Dedicated field model for the new block.
- `template-parts/blog/filter_archive.php`
  Shared renderer for controls, cards, empty state, and pagination.
- `template-parts/flexi/blog_filter_archive.php`
  Thin flexi wrapper that resolves fields, active request state, and shared-render arguments.
- `template-parts/blog/index.php`
  Existing archive template updated to call the shared archive renderer instead of maintaining its own divergent filter/search/card UI.

The concrete request/query model for this plan is:

- category query var: `blog_category`
- search query var: `blog_search`
- page query var: `blog_page`

This keeps the block working on `/flexi/` without forcing redirects to native category archive URLs, while still allowing the blog index/archive templates to consume the same state model.

### Task 1: Add helper coverage first

**Files:**
- Create: `inc/blog-filter-archive-functions.php`
- Modify: `functions.php`
- Create: `tests/Unit/BlogFilterArchiveTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/BlogFilterArchiveTest.php` with focused coverage for:

- sanitizing the active category/search/page state from request-like input
- restricting the active category to an allowed slug list
- building `WP_Query` args for category + search + pagination
- preserving category/search state when generating pagination URLs

Use this test content:

```php
<?php

require_once dirname(__DIR__, 2) . '/inc/blog-filter-archive-functions.php';

test('blog filter archive resolves request state against allowed categories', function () {
    expect(function_exists('matrix_resolve_blog_filter_archive_state'))->toBeTrue();

    $state = matrix_resolve_blog_filter_archive_state(
        [
            'blog_category' => 'events',
            'blog_search' => '  mental health  ',
            'blog_page' => '3',
        ],
        ['news', 'events'],
        12
    );

    expect($state['category'])->toBe('events')
        ->and($state['search'])->toBe('mental health')
        ->and($state['paged'])->toBe(3)
        ->and($state['posts_per_page'])->toBe(12);
});

test('blog filter archive falls back to all when category is not allowed', function () {
    $state = matrix_resolve_blog_filter_archive_state(
        [
            'blog_category' => 'projects',
            'blog_search' => '',
            'blog_page' => '0',
        ],
        ['news', 'events'],
        12
    );

    expect($state['category'])->toBe('all')
        ->and($state['search'])->toBe('')
        ->and($state['paged'])->toBe(1);
});

test('blog filter archive builds query args for category search and paging', function () {
    expect(function_exists('matrix_build_blog_filter_archive_query_args'))->toBeTrue();

    $args = matrix_build_blog_filter_archive_query_args([
        'category' => 'events',
        'search' => 'anxiety',
        'paged' => 2,
        'posts_per_page' => 12,
    ], [
        'events' => 17,
        'news' => 19,
    ]);

    expect($args['post_type'])->toBe('post')
        ->and($args['post_status'])->toBe('publish')
        ->and($args['posts_per_page'])->toBe(12)
        ->and($args['paged'])->toBe(2)
        ->and($args['s'])->toBe('anxiety')
        ->and($args['category__in'])->toBe([17]);
});

test('blog filter archive preserves filters in pagination urls', function () {
    expect(function_exists('matrix_build_blog_filter_archive_page_url'))->toBeTrue();

    $url = matrix_build_blog_filter_archive_page_url(
        'http://localhost:10034/flexi/',
        [
            'category' => 'news',
            'search' => 'service user',
            'paged' => 1,
        ],
        4
    );

    expect($url)->toContain('blog_category=news')
        ->and($url)->toContain('blog_search=service+user')
        ->and($url)->toContain('blog_page=4');
});
```

- [ ] **Step 2: Run the test to verify RED**

Run: `php vendor/bin/pest "tests/Unit/BlogFilterArchiveTest.php"`

Expected: FAIL because the helper file or helper functions do not exist yet.

- [ ] **Step 3: Add the minimal helper and load it from `functions.php`**

Create `inc/blog-filter-archive-functions.php` with this initial helper implementation:

```php
<?php

function matrix_get_blog_filter_archive_defaults()
{
    return [
        'heading' => 'News and events',
        'filter_label' => 'Filter by:',
        'search_placeholder' => 'Search news and events',
        'search_button_label' => 'Search',
        'posts_per_page' => 12,
        'empty_state_message' => 'No posts matched your filters.',
    ];
}

function matrix_resolve_blog_filter_archive_state($request, $allowed_category_slugs = [], $posts_per_page = 12)
{
    $allowed_category_slugs = array_values(array_filter(array_map('sanitize_title', (array) $allowed_category_slugs)));
    $category = sanitize_title((string) ($request['blog_category'] ?? 'all'));
    $search = trim((string) ($request['blog_search'] ?? ''));
    $paged = (int) ($request['blog_page'] ?? 1);
    $posts_per_page = (int) $posts_per_page;

    if ($posts_per_page < 1) {
        $posts_per_page = 12;
    }

    if ($paged < 1) {
        $paged = 1;
    }

    if ($category === '' || $category === 'all') {
        $category = 'all';
    } elseif ($allowed_category_slugs !== [] && ! in_array($category, $allowed_category_slugs, true)) {
        $category = 'all';
    }

    return [
        'category' => $category,
        'search' => $search,
        'paged' => $paged,
        'posts_per_page' => $posts_per_page,
    ];
}

function matrix_build_blog_filter_archive_query_args($state, $category_slug_to_id_map = [])
{
    $args = [
        'post_type' => 'post',
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
        'posts_per_page' => (int) ($state['posts_per_page'] ?? 12),
        'paged' => max(1, (int) ($state['paged'] ?? 1)),
    ];

    $search = trim((string) ($state['search'] ?? ''));
    if ($search !== '') {
        $args['s'] = $search;
    }

    $category = sanitize_title((string) ($state['category'] ?? 'all'));
    $category_id = (int) ($category_slug_to_id_map[$category] ?? 0);
    if ($category !== 'all' && $category_id > 0) {
        $args['category__in'] = [$category_id];
    }

    return $args;
}

function matrix_build_blog_filter_archive_page_url($base_url, $state, $page)
{
    $page = max(1, (int) $page);
    $params = [
        'blog_page' => $page,
    ];

    $category = sanitize_title((string) ($state['category'] ?? 'all'));
    if ($category !== '' && $category !== 'all') {
        $params['blog_category'] = $category;
    }

    $search = trim((string) ($state['search'] ?? ''));
    if ($search !== '') {
        $params['blog_search'] = $search;
    }

    return add_query_arg($params, $base_url);
}
```

Add this require near the other helper includes in `functions.php`:

```php
require_once get_template_directory() . '/inc/blog-filter-archive-functions.php';
```

- [ ] **Step 4: Run the test to verify GREEN**

Run: `php vendor/bin/pest "tests/Unit/BlogFilterArchiveTest.php"`

Expected: PASS.

### Task 2: Create the shared archive surface and block wrapper

**Files:**
- Create: `acf-fields/partials/blocks/acf_blog_filter_archive.php`
- Create: `template-parts/blog/filter_archive.php`
- Create: `template-parts/flexi/blog_filter_archive.php`

- [ ] **Step 1: Create the ACF Builder definition**

Create `acf-fields/partials/blocks/acf_blog_filter_archive.php` with:

- `Content` tab:
  - `heading_tag`
  - `heading`
  - `filter_label`
  - `search_placeholder`
  - `search_button_label`
  - `posts_per_page`
  - `allowed_categories` using native `category`
  - `empty_state_message`
- `Design` tab:
  - `background_color`
  - `filter_label_color`
  - `chip_text_color`
  - `chip_border_color`
  - `active_chip_background_color`
  - `active_chip_text_color`
  - `search_input_text_color`
  - `search_input_border_color`
  - `search_button_background_color`
  - `search_button_text_color`
  - `card_background_color`
  - `card_title_color`
  - `card_meta_color`
  - `card_excerpt_color`
- `Layout` tab:
  - shared `padding_settings`

Use this defaults block in the builder:

```php
$defaults = matrix_get_blog_filter_archive_defaults();

->addText('heading', [
    'label' => 'Heading',
    'default_value' => $defaults['heading'],
])
->addText('filter_label', [
    'label' => 'Filter Label',
    'default_value' => $defaults['filter_label'],
])
->addText('search_placeholder', [
    'label' => 'Search Placeholder',
    'default_value' => $defaults['search_placeholder'],
])
->addText('search_button_label', [
    'label' => 'Search Button Label',
    'default_value' => $defaults['search_button_label'],
])
->addNumber('posts_per_page', [
    'label' => 'Posts Per Page',
    'default_value' => $defaults['posts_per_page'],
    'min' => 1,
    'max' => 24,
    'step' => 1,
])
```

- [ ] **Step 2: Create the shared renderer**

Create `template-parts/blog/filter_archive.php` as the one shared UI surface. It should expect a prepared array from `get_template_part(..., null, ['blog_filter_archive' => $blog_filter_archive])`, then normalize it at the top:

```php
<?php

$blog_filter_archive = is_array($args['blog_filter_archive'] ?? null) ? $args['blog_filter_archive'] : [];
if ($blog_filter_archive === []) {
    return;
}
```

The shared renderer should then use these keys:

- `heading_tag`
- `heading`
- `filter_label`
- `search_placeholder`
- `search_button_label`
- `empty_state_message`
- `base_url`
- `state`
- `chips`
- `query`
- `pagination`
- `colors`
- `section_classes`
- `section_style`

Render:

- top control row with label/chips on the left and search form on the right
- card grid with:
  - image
  - category badge
  - title
  - date
  - excerpt
- real pagination controls

Use Alpine on the outer wrapper:

```php
x-data="{
    category: '<?php echo esc_js($blog_filter_archive['state']['category']); ?>',
    search: <?php echo wp_json_encode($blog_filter_archive['state']['search']); ?>,
    submitCategory(slug) {
        this.category = slug;
        this.$refs.categoryInput.value = slug;
        this.$refs.pageInput.value = 1;
        this.$refs.form.submit();
    }
}"
```

Use this class structure for the controls and grid:

```php
$controls_classes = 'flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between';
$chip_group_classes = 'flex flex-wrap gap-3';
$grid_classes = 'mt-8 grid grid-cols-1 gap-6 md:grid-cols-2 xl:mt-10 xl:grid-cols-3 xl:gap-8';
$pagination_classes = 'mt-10 flex flex-wrap items-center justify-center gap-2';
```

- [ ] **Step 3: Create the flexi wrapper**

Create `template-parts/flexi/blog_filter_archive.php` as a thin adapter that:

1. reads ACF fields
2. resolves allowed categories
3. resolves request state from `$_GET`
4. builds category slug/id maps
5. runs `WP_Query`
6. prepares the `$blog_filter_archive` array
7. calls the shared partial with args

Use this preparation skeleton:

```php
$allowed_terms = get_terms([
    'taxonomy' => 'category',
    'hide_empty' => true,
    'include' => array_values(array_filter(array_map('intval', (array) get_sub_field('allowed_categories')))),
]);

$chips = [['slug' => 'all', 'label' => 'All']];
foreach ($allowed_terms as $term) {
    $chips[] = [
        'slug' => $term->slug,
        'label' => $term->name,
        'term_id' => (int) $term->term_id,
    ];
}

$slug_to_id_map = [];
foreach ($chips as $chip) {
    if (! empty($chip['term_id'])) {
        $slug_to_id_map[$chip['slug']] = (int) $chip['term_id'];
    }
}

get_template_part('template-parts/blog/filter_archive', null, [
    'blog_filter_archive' => $blog_filter_archive,
]);
```

- [ ] **Step 4: Run syntax checks**

Run:

```bash
php -l "acf-fields/partials/blocks/acf_blog_filter_archive.php"
php -l "template-parts/blog/filter_archive.php"
php -l "template-parts/flexi/blog_filter_archive.php"
```

Expected: `No syntax errors detected` for all files.

### Task 3: Adopt the shared renderer in the blog archive template

**Files:**
- Modify: `template-parts/blog/index.php`
- Optionally create: `template-parts/blog/archive-shell.php`

- [ ] **Step 1: Move the blog index to the shared state model**

Update `template-parts/blog/index.php` so its existing hero/breadcrumb shell remains intact, but the filter/search/cards/pagination section uses the same helper/state model as the flexi block.

Use the same query var names from Task 1:

```php
$defaults = matrix_get_blog_filter_archive_defaults();
$terms = get_terms([
    'taxonomy' => 'category',
    'hide_empty' => true,
]);

$chips = [['slug' => 'all', 'label' => 'All']];
$slug_to_id_map = [];
foreach ($terms as $term) {
    $chips[] = [
        'slug' => $term->slug,
        'label' => $term->name,
        'term_id' => (int) $term->term_id,
    ];
    $slug_to_id_map[$term->slug] = (int) $term->term_id;
}

$state = matrix_resolve_blog_filter_archive_state($_GET, array_keys($slug_to_id_map), 12);
$query = new WP_Query(matrix_build_blog_filter_archive_query_args($state, $slug_to_id_map));
```

- [ ] **Step 2: Hand the prepared archive data to the shared partial**

Prepare the same `$blog_filter_archive` structure and include:

```php
$blog_filter_archive = [
    'heading_tag' => 'h2',
    'heading' => 'What’s new at St Patrick’s',
    'filter_label' => $filter_title ?: $defaults['filter_label'],
    'search_placeholder' => $defaults['search_placeholder'],
    'search_button_label' => $defaults['search_button_label'],
    'empty_state_message' => $defaults['empty_state_message'],
    'base_url' => get_permalink(get_option('page_for_posts')) ?: home_url('/resources/'),
    'state' => $state,
    'chips' => $chips,
    'query' => $query,
    'pagination' => [
        'current' => max(1, (int) $query->get('paged')),
        'total' => max(1, (int) $query->max_num_pages),
    ],
    'colors' => [
        'background' => '#FFFFFF',
        'filter_label' => '#08284B',
        'chip_text' => '#08284B',
        'chip_border' => '#08284B',
        'active_chip_background' => '#80CCD9',
        'active_chip_text' => '#08284B',
        'search_input_text' => '#08284B',
        'search_input_border' => '#E2E8F0',
        'search_button_background' => '#08284B',
        'search_button_text' => '#FFFFFF',
        'card_background' => '#F1F8F9',
        'card_title' => '#1E244B',
        'card_meta' => '#1E244B',
        'card_excerpt' => '#1E244B',
    ],
    'section_classes' => 'w-full',
    'section_style' => '',
];

get_template_part('template-parts/blog/filter_archive', null, [
    'blog_filter_archive' => $blog_filter_archive,
]);
```

- [ ] **Step 3: Run syntax checks**

Run:

```bash
php -l "template-parts/blog/index.php"
php -l "template-parts/blog/filter_archive.php"
```

Expected: `No syntax errors detected` for both files.

### Task 4: Seed the flexi demo and ensure local archive data is usable

**Files:**
- Modify data only on page ID `329` (`/flexi/`)
- Optionally create demo posts in local WordPress data only

- [ ] **Step 1: Ensure enough local post data exists**

Check whether the local site has at least 13 published `post` posts spread across multiple categories:

```bash
wp post list --post_type=post --post_status=publish --format=count
wp term list category --format=csv
```

If the post count is below `13`, create enough local-only demo posts with category assignments so page 2 of the archive is testable:

```bash
NEWS_ID=$(wp term list category --slug=news --field=term_id)
EVENTS_ID=$(wp term list category --slug=events --field=term_id)

wp post create --post_type=post --post_status=publish \
  --post_title="Archive Demo News 01" \
  --post_content="Demo excerpt content for the archive block." \
  --post_category="$NEWS_ID"

wp post create --post_type=post --post_status=publish \
  --post_title="Archive Demo Event 01" \
  --post_content="Demo excerpt content for the archive block." \
  --post_category="$EVENTS_ID"
```

Repeat until the local site has enough published posts to exercise page `2`.

- [ ] **Step 2: Add the new block to `/flexi/`**

Use `wp eval` to remove any previous `blog_filter_archive` row and append one fresh block row with:

- heading: `News and events`
- filter label: `Filter by:`
- search placeholder: `Search news and events`
- search button label: `Search`
- posts per page: `12`
- empty state message: `No posts matched your filters.`

Use this row shape:

```php
[
    'acf_fc_layout' => 'blog_filter_archive',
    'heading_tag' => 'h2',
    'heading' => 'News and events',
    'filter_label' => 'Filter by:',
    'search_placeholder' => 'Search news and events',
    'search_button_label' => 'Search',
    'posts_per_page' => 12,
    'empty_state_message' => 'No posts matched your filters.',
    'background_color' => '#FFFFFF',
    'filter_label_color' => '#08284B',
    'chip_text_color' => '#08284B',
    'chip_border_color' => '#08284B',
    'active_chip_background_color' => '#80CCD9',
    'active_chip_text_color' => '#08284B',
    'search_input_text_color' => '#08284B',
    'search_input_border_color' => '#E2E8F0',
    'search_button_background_color' => '#08284B',
    'search_button_text_color' => '#FFFFFF',
    'card_background_color' => '#F1F8F9',
    'card_title_color' => '#1E244B',
    'card_meta_color' => '#1E244B',
    'card_excerpt_color' => '#1E244B',
]
```

- [ ] **Step 3: Verify the demo row exists**

Run:

```bash
curl -s "http://localhost:10034/flexi/" | rg -n "Filter by:|Search news and events|blog-filter-archive|No posts matched"
```

Expected: at least one match for the new control row or block marker.

### Task 5: Build and verify the full surface

**Files:**
- Review only unless scoped fixes are required

- [ ] **Step 1: Rebuild front-end assets**

Run: `npm run build`

Expected: exit code `0`. Existing webpack asset-size warnings are acceptable if no new build failure appears.

- [ ] **Step 2: Run focused verification**

Run:

```bash
php vendor/bin/pest "tests/Unit/BlogFilterArchiveTest.php"
php -l "inc/blog-filter-archive-functions.php"
php -l "acf-fields/partials/blocks/acf_blog_filter_archive.php"
php -l "template-parts/blog/filter_archive.php"
php -l "template-parts/flexi/blog_filter_archive.php"
php -l "template-parts/blog/index.php"
```

Expected: helper tests pass and all touched PHP files lint cleanly.

- [ ] **Step 3: Read lints for changed files**

Check IDE diagnostics for:

- `inc/blog-filter-archive-functions.php`
- `acf-fields/partials/blocks/acf_blog_filter_archive.php`
- `template-parts/blog/filter_archive.php`
- `template-parts/flexi/blog_filter_archive.php`
- `template-parts/blog/index.php`

Expected: no newly introduced diagnostics.

- [ ] **Step 4: Verify browser behavior on `/flexi/`**

Use browser automation to verify:

- filter chips render with one active state
- search control renders at the right side on desktop
- card grid renders three columns on desktop
- changing category updates the result set and resets to page `1`
- entering a search term updates the result set and keeps the search input value visible
- pagination controls preserve active category and search state

- [ ] **Step 5: Verify browser behavior on the blog index/archive**

Use browser automation to verify the same shared renderer on the real blog page:

- the filter/search/card UI matches the flexi block styling
- category + search state work together
- page navigation preserves both filters
- keyboard focus is visible on chips, search submit, cards, and pagination controls

### Task 6: Final review

**Files:**
- Review only

- [ ] **Step 1: Confirm the implementation still matches the approved scope**

Checklist:

- dedicated `blog_filter_archive` block
- not merged into `latest_posts`
- Alpine used for filter/search UI state
- server-backed results through `WP_Query`
- category chips use native `category`
- real pagination
- seeded onto `/flexi/`
- shared renderer used by both flexi and blog archive surfaces

- [ ] **Step 2: Summarize any local-data limitations**

If the local site required seeded demo posts or still did not have enough content to exercise deep pagination naturally, record that explicitly in the final handoff.
