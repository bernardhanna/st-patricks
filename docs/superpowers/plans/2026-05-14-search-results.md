# Search Results Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a dedicated global search results page with multi-type filtering, sort controls, pagination, and a matching no-results state for native WordPress search requests.

**Architecture:** Stop routing `is_search()` through the blog archive and give search its own `search.php` plus a shared renderer. Keep request-state parsing, query args, URL building, and result normalization in a dedicated helper file with focused unit coverage, then feed one search-specific template part from `search.php`.

**Tech Stack:** WordPress PHP templates, `WP_Query`, theme helper functions, Pest/PHPUnit for focused helper tests, existing theme Tailwind utility conventions, local `curl`/browser verification, and `npm run build` for assets.

---

## File Structure

- `inc/search-results-functions.php`
  Keeps defaults, allowed type/sort maps, request-state resolution, query arg building, pagination URL building, heading copy, and result normalization helpers.
- `tests/Unit/SearchResultsTest.php`
  Focused unit coverage for pure search helper behavior.
- `functions.php`
  Loads `inc/search-results-functions.php`.
- `search.php`
  Native WordPress search entry point. Reads the search request, prepares the search view model, and includes the shared renderer.
- `template-parts/search/results.php`
  Shared renderer for both populated and no-results search states.
- `index.php`
  Stops routing `is_search()` through the blog archive fallback.

Do not extend the blog archive abstraction for this work.

### Task 1: Add helper coverage first

**Files:**
- Create: `inc/search-results-functions.php`
- Create: `tests/Unit/SearchResultsTest.php`
- Modify: `functions.php`

- [ ] **Step 1: Write the failing tests**

Create `tests/Unit/SearchResultsTest.php` with:

```php
<?php

require_once dirname(__DIR__, 2) . '/inc/search-results-functions.php';

test('search results resolve state from native and custom query vars', function () {
    expect(function_exists('matrix_resolve_search_results_state'))->toBeTrue();

    $state = matrix_resolve_search_results_state([
        's' => '  referral  ',
        'search_type' => 'webinars',
        'search_sort' => 'date',
        'paged' => '3',
    ]);

    expect($state)->toMatchArray([
        'query' => 'referral',
        'type' => 'webinars',
        'sort' => 'date',
        'paged' => 3,
    ]);
});

test('search results fall back to all and relevance for invalid values', function () {
    $state = matrix_resolve_search_results_state([
        's' => 'help',
        'search_type' => 'unknown',
        'search_sort' => 'popular',
        'paged' => '0',
    ]);

    expect($state)->toMatchArray([
        'query' => 'help',
        'type' => 'all',
        'sort' => 'relevance',
        'paged' => 1,
    ]);
});

test('search results build query args for a webinars date sort', function () {
    expect(function_exists('matrix_build_search_results_query_args'))->toBeTrue();

    $args = matrix_build_search_results_query_args([
        'query' => 'mental health',
        'type' => 'webinars',
        'sort' => 'date',
        'paged' => 2,
    ]);

    expect($args['s'])->toBe('mental health')
        ->and($args['post_type'])->toBe(['webinars'])
        ->and($args['paged'])->toBe(2)
        ->and($args['orderby'])->toBe('date')
        ->and($args['order'])->toBe('DESC');
});

test('search results pagination urls preserve search state', function () {
    expect(function_exists('matrix_build_search_results_page_url'))->toBeTrue();

    $url = matrix_build_search_results_page_url(
        'http://localhost:10034/?s=referral',
        [
            'query' => 'referral',
            'type' => 'page',
            'sort' => 'date',
            'paged' => 1,
        ],
        4
    );

    expect($url)->toContain('s=referral')
        ->and($url)->toContain('search_type=page')
        ->and($url)->toContain('search_sort=date')
        ->and($url)->toContain('paged=4');
});

test('search results expose heading copy for results and empty states', function () {
    expect(function_exists('matrix_get_search_results_heading_data'))->toBeTrue();

    $results_heading = matrix_get_search_results_heading_data('how to make a referral', true);
    $empty_heading = matrix_get_search_results_heading_data('prescriptions and medication', false);

    expect($results_heading)->toMatchArray([
        'prefix' => 'Search result for',
        'query' => 'how to make a referral',
    ]);

    expect($empty_heading)->toMatchArray([
        'prefix' => 'We couldn’t find a match for',
        'query' => 'prescriptions and medication',
    ]);
});

test('search results type labels map to display labels', function () {
    expect(function_exists('matrix_get_search_results_type_label'))->toBeTrue();

    expect(matrix_get_search_results_type_label('post'))->toBe('Blog')
        ->and(matrix_get_search_results_type_label('page'))->toBe('Page')
        ->and(matrix_get_search_results_type_label('webinars'))->toBe('Webinar');
});
```

- [ ] **Step 2: Run the focused test to verify RED**

Run: `php vendor/bin/pest "tests/Unit/SearchResultsTest.php"`

Expected: FAIL because the search results helpers do not exist yet.

- [ ] **Step 3: Add the minimal helper implementation**

Create `inc/search-results-functions.php` with:

```php
<?php

function matrix_get_search_results_allowed_types()
{
    return [
        'all' => ['post', 'page', 'webinars'],
        'blog' => ['post'],
        'page' => ['page'],
        'webinars' => ['webinars'],
    ];
}

function matrix_get_search_results_allowed_sorts()
{
    return ['relevance', 'date'];
}

function matrix_resolve_search_results_state($request)
{
    $query = trim((string) ($request['s'] ?? ''));
    $type = sanitize_key((string) ($request['search_type'] ?? 'all'));
    $sort = sanitize_key((string) ($request['search_sort'] ?? 'relevance'));
    $paged = max(1, (int) ($request['paged'] ?? 1));

    if (! array_key_exists($type, matrix_get_search_results_allowed_types())) {
        $type = 'all';
    }

    if (! in_array($sort, matrix_get_search_results_allowed_sorts(), true)) {
        $sort = 'relevance';
    }

    return [
        'query' => $query,
        'type' => $type,
        'sort' => $sort,
        'paged' => $paged,
    ];
}

function matrix_build_search_results_query_args($state)
{
    $args = [
        'post_type' => matrix_get_search_results_allowed_types()[$state['type'] ?? 'all'] ?? ['post', 'page', 'webinars'],
        'post_status' => 'publish',
        's' => (string) ($state['query'] ?? ''),
        'paged' => max(1, (int) ($state['paged'] ?? 1)),
    ];

    if (($state['sort'] ?? 'relevance') === 'date') {
        $args['orderby'] = 'date';
        $args['order'] = 'DESC';
    }

    return $args;
}

function matrix_build_search_results_page_url($base_url, $state, $page)
{
    return add_query_arg([
        's' => (string) ($state['query'] ?? ''),
        'search_type' => (string) ($state['type'] ?? 'all'),
        'search_sort' => (string) ($state['sort'] ?? 'relevance'),
        'paged' => max(1, (int) $page),
    ], $base_url);
}

function matrix_get_search_results_heading_data($query, $has_results)
{
    $query = trim((string) $query);

    return [
        'prefix' => $has_results ? 'Search result for' : 'We couldn’t find a match for',
        'query' => $query,
    ];
}

function matrix_get_search_results_type_label($post_type)
{
    if ($post_type === 'webinars') {
        return 'Webinar';
    }

    if ($post_type === 'page') {
        return 'Page';
    }

    return 'Blog';
}
```

Then load it in `functions.php` with:

```php
require_once get_template_directory() . '/inc/search-results-functions.php';
```

- [ ] **Step 4: Run the focused test to verify GREEN**

Run: `php vendor/bin/pest "tests/Unit/SearchResultsTest.php"`

Expected: PASS with all search helper tests green.

- [ ] **Step 5: Commit the helper slice**

```bash
git add "inc/search-results-functions.php" "tests/Unit/SearchResultsTest.php" "functions.php"
git commit -m "feat: add search results helpers"
```

### Task 2: Build the search-specific renderer

**Files:**
- Modify: `inc/search-results-functions.php`
- Create: `template-parts/search/results.php`

- [ ] **Step 1: Extend the helper with result preparation**

Add to `inc/search-results-functions.php`:

```php
function matrix_prepare_search_results($request)
{
    $state = matrix_resolve_search_results_state($request);
    $query = new WP_Query(matrix_build_search_results_query_args($state));
    $items = [];

    foreach ($query->posts as $post) {
        $post_type = get_post_type($post);
        $date_label = '';

        if ($post_type === 'webinars') {
            $webinar_date = (string) get_field('webinar_date', $post->ID);
            $date_label = $webinar_date !== '' ? $webinar_date : get_the_date('d/m/y', $post);
        } elseif ($post_type === 'post') {
            $date_label = get_the_date('d/m/y', $post);
        }

        $items[] = [
            'title' => get_the_title($post),
            'url' => get_permalink($post),
            'image_html' => get_the_post_thumbnail($post, 'medium_large', ['class' => 'h-full w-full object-cover']),
            'type_label' => matrix_get_search_results_type_label($post_type),
            'date_label' => $date_label,
            'excerpt' => has_excerpt($post) ? get_the_excerpt($post) : wp_trim_words(wp_strip_all_tags($post->post_content), 24),
        ];
    }

    return [
        'state' => $state,
        'query' => $query,
        'items' => $items,
        'has_results' => ! empty($items),
        'heading' => matrix_get_search_results_heading_data($state['query'], ! empty($items)),
        'base_url' => home_url('/'),
    ];
}
```

- [ ] **Step 2: Create the shared renderer**

Create `template-parts/search/results.php` with:

```php
<?php
$search_results = $args['search_results'] ?? [];
$state = $search_results['state'] ?? [];
$items = $search_results['items'] ?? [];
$heading = $search_results['heading'] ?? ['prefix' => 'Search Results', 'query' => ''];
$query = $search_results['query'] ?? null;
$has_results = ! empty($search_results['has_results']);
?>

<main class="mt-[0rem] w-full">
    <section class="w-full bg-[#C6ECF4]">
        <div class="mx-auto flex w-full max-w-[1280px] flex-col">
            <nav class="w-full bg-[#F1F8F9] px-5 py-3 lg:px-[70px]" aria-label="Breadcrumb">
                <ol class="flex flex-wrap gap-3 items-center">
                    <li><a href="<?php echo esc_url(home_url('/')); ?>" class="font-primary text-[14px] font-semibold leading-[20px] text-[#08284B]">Home</a></li>
                    <li class="font-primary text-[14px] font-semibold leading-[20px] text-[#08284B]">Search</li>
                    <li class="font-primary text-[14px] font-normal leading-[20px] text-[#08284B]" aria-current="page">Results</li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="bg-white">
        <div class="mx-auto flex w-full max-w-[1018px] flex-col gap-8 px-5 py-12 xl:px-0 xl:py-[100px]">
            <form action="<?php echo esc_url(home_url('/')); ?>" method="get" class="flex w-full max-w-[384px] gap-2">
                <label for="search-results-query" class="sr-only">Search site content</label>
                <input id="search-results-query" type="search" name="s" value="<?php echo esc_attr($state['query'] ?? ''); ?>" class="min-h-[40px] flex-1 rounded-md border border-[#E2E8F0] px-3 py-2 text-[16px] leading-[24px] text-[#08284B]" />
                <button type="submit" class="inline-flex h-[40px] items-center gap-2 rounded-md bg-[#08284B] px-3 text-[14px] font-medium leading-[24px] text-white">Search</button>
            </form>

            <h1 class="font-primary text-[36px] font-bold leading-[40px] tracking-[-0.432px] text-[#08284B] lg:text-[48px] lg:leading-[48px] lg:tracking-[-0.576px]">
                <?php echo esc_html($heading['prefix'] ?? 'Search Results'); ?>
                <?php if (! empty($heading['query'])) { ?>
                    <span class="font-normal italic"><?php echo esc_html(' ' . "'" . ($heading['query'] ?? '') . "'"); ?></span>
                <?php } ?>
            </h1>

            <?php if ($has_results) { ?>
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-4">
                        <label for="search-sort" class="text-[16px] font-medium leading-[28px] text-[#08284B]">Sort by:</label>
                        <select id="search-sort" name="search_sort" class="min-h-[40px] min-w-[213px] rounded-md border border-[#E2E8F0] px-3 py-2 text-[16px] leading-[24px] text-[#08284B]"></select>
                    </div>
                    <div class="flex items-center gap-4">
                        <label for="search-type" class="text-[16px] font-medium leading-[28px] text-[#08284B]">Filter by:</label>
                        <select id="search-type" name="search_type" class="min-h-[40px] min-w-[213px] rounded-md border border-[#E2E8F0] px-3 py-2 text-[16px] leading-[24px] text-[#08284B]"></select>
                    </div>
                </div>

                <div class="flex flex-col gap-4">
                    <?php foreach ($items as $item) { ?>
                        <article class="flex flex-col gap-6 rounded-[8px] bg-[#FBFAF7] p-6 shadow-sm lg:flex-row">
                            <a href="<?php echo esc_url($item['url']); ?>" class="block h-[186px] w-full overflow-hidden rounded-[4px] lg:w-[280px]">
                                <?php echo $item['image_html'] ?: ''; ?>
                            </a>
                            <div class="flex min-w-0 flex-1 flex-col gap-4">
                                <span class="inline-flex w-fit rounded-full bg-[#FADBD8] px-4 py-1 text-[14px] font-medium leading-[24px] text-[#08284B]"><?php echo esc_html($item['type_label']); ?></span>
                                <a href="<?php echo esc_url($item['url']); ?>" class="font-primary text-[20px] font-semibold leading-[24px] tracking-[-0.12px] text-[#1E244B]"><?php echo esc_html($item['title']); ?></a>
                                <?php if ($item['date_label'] !== '') { ?>
                                    <p class="text-[15px] font-semibold leading-[16px] tracking-[-0.09px] text-[#1E244B]">Date: <?php echo esc_html($item['date_label']); ?></p>
                                <?php } ?>
                                <p class="text-[14px] leading-[24px] text-[#1E244B]"><?php echo esc_html($item['excerpt']); ?></p>
                            </div>
                        </article>
                    <?php } ?>
                </div>
            <?php } else { ?>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="inline-flex w-fit items-center justify-center rounded-md bg-[#024B79] px-3 py-2 text-[14px] font-medium leading-[24px] text-white">Go back to Home page</a>
            <?php } ?>
        </div>
    </section>
</main>
```

- [ ] **Step 3: Lint the helper and renderer**

Run:

```bash
php -l "inc/search-results-functions.php"
php -l "template-parts/search/results.php"
```

Expected: both files report `No syntax errors detected`.

- [ ] **Step 4: Commit the renderer slice**

```bash
git add "inc/search-results-functions.php" "template-parts/search/results.php"
git commit -m "feat: add search results renderer"
```

### Task 3: Wire native search into the dedicated template

**Files:**
- Create: `search.php`
- Modify: `index.php`

- [ ] **Step 1: Create the dedicated search template**

Create `search.php` with:

```php
<?php
get_header();

$search_results = matrix_prepare_search_results($_GET);

get_template_part('template-parts/search/results', null, [
    'search_results' => $search_results,
]);

get_footer();
```

- [ ] **Step 2: Stop routing search requests through the blog archive**

Update the `index.php` guard from:

```php
if (is_home() || is_category() || is_search()) {
```

to:

```php
if (is_home() || is_category()) {
```

- [ ] **Step 3: Lint the entry templates**

Run:

```bash
php -l "search.php"
php -l "index.php"
```

Expected: both files report `No syntax errors detected`.

- [ ] **Step 4: Commit the routing slice**

```bash
git add "search.php" "index.php"
git commit -m "feat: route native search to dedicated template"
```

### Task 4: Populate the controls and pagination

**Files:**
- Modify: `inc/search-results-functions.php`
- Modify: `template-parts/search/results.php`

- [ ] **Step 1: Add dropdown option helpers and pagination data**

Extend `matrix_prepare_search_results()` to return:

```php
'type_options' => [
    ['value' => 'all', 'label' => 'All'],
    ['value' => 'blog', 'label' => 'Blog'],
    ['value' => 'page', 'label' => 'Pages'],
    ['value' => 'webinars', 'label' => 'Webinars'],
],
'sort_options' => [
    ['value' => 'relevance', 'label' => 'Relevance'],
    ['value' => 'date', 'label' => 'Date'],
],
'pagination' => [
    'current' => max(1, (int) $query->get('paged')),
    'total' => max(1, (int) $query->max_num_pages),
],
```

- [ ] **Step 2: Bind the dropdowns to the current state**

Replace the placeholder selects in `template-parts/search/results.php` with:

```php
<select id="search-sort" name="search_sort" class="min-h-[40px] min-w-[213px] rounded-md border border-[#E2E8F0] px-3 py-2 text-[16px] leading-[24px] text-[#08284B]">
    <?php foreach (($search_results['sort_options'] ?? []) as $option) { ?>
        <option value="<?php echo esc_attr($option['value']); ?>" <?php selected(($state['sort'] ?? 'relevance'), $option['value']); ?>>
            <?php echo esc_html($option['label']); ?>
        </option>
    <?php } ?>
</select>

<select id="search-type" name="search_type" class="min-h-[40px] min-w-[213px] rounded-md border border-[#E2E8F0] px-3 py-2 text-[16px] leading-[24px] text-[#08284B]">
    <?php foreach (($search_results['type_options'] ?? []) as $option) { ?>
        <option value="<?php echo esc_attr($option['value']); ?>" <?php selected(($state['type'] ?? 'all'), $option['value']); ?>>
            <?php echo esc_html($option['label']); ?>
        </option>
    <?php } ?>
</select>
```

Wrap both dropdowns inside the same `<form>` as the search field so search/filter/sort submit together.

- [ ] **Step 3: Add pagination below results**

Add below the results list:

```php
<?php if (($search_results['pagination']['total'] ?? 1) > 1) { ?>
    <nav class="flex items-center justify-center gap-2" aria-label="Search results pagination">
        <?php for ($page = 1; $page <= (int) ($search_results['pagination']['total'] ?? 1); $page++) { ?>
            <a
                href="<?php echo esc_url(matrix_build_search_results_page_url(home_url('/'), $state, $page)); ?>"
                class="flex h-8 w-8 items-center justify-center rounded-full border border-[#C6ECF4] text-[14px] leading-[20px] text-[#08284B]"
                <?php if ($page === (int) ($search_results['pagination']['current'] ?? 1)) { ?>aria-current="page"<?php } ?>
            >
                <?php echo esc_html((string) $page); ?>
            </a>
        <?php } ?>
    </nav>
<?php } ?>
```

- [ ] **Step 4: Run syntax checks again**

Run:

```bash
php -l "inc/search-results-functions.php"
php -l "template-parts/search/results.php"
```

Expected: both files report `No syntax errors detected`.

- [ ] **Step 5: Commit the controls slice**

```bash
git add "inc/search-results-functions.php" "template-parts/search/results.php"
git commit -m "feat: add search filters sorting and pagination"
```

### Task 5: Build and verify the full search surface

**Files:**
- Modify as needed: `search.php`, `template-parts/search/results.php`, `inc/search-results-functions.php`

- [ ] **Step 1: Run focused helper coverage**

Run: `php vendor/bin/pest "tests/Unit/SearchResultsTest.php"`

Expected: PASS.

- [ ] **Step 2: Build theme assets**

Run: `npm run build`

Expected: build completes successfully; existing asset-size warnings are acceptable if no new errors appear.

- [ ] **Step 3: Verify populated search results via curl**

Run:

```bash
curl -s "http://localhost:10034/?s=referral" | rg -n "Search result for|Sort by:|Filter by:|Blog|Page|Webinar"
```

Expected: the response includes the search heading and both dropdown labels.

- [ ] **Step 4: Verify the no-results state via curl**

Run:

```bash
curl -s "http://localhost:10034/?s=zzzz-no-match-zzzz" | rg -n "We couldn’t find a match for|Go back to Home page"
```

Expected: the response includes the empty-state heading and the home CTA.

- [ ] **Step 5: Browser-check both Figma states**

Visit:

- `http://localhost:10034/?s=referral`
- `http://localhost:10034/?s=zzzz-no-match-zzzz`

Confirm:

- breadcrumb row matches the Figma treatment
- search form sizing and spacing are close to the design
- controls sit correctly on desktop and stack cleanly on smaller screens
- result cards use the intended stacked layout
- no-results state shows only the search form, heading, and home CTA

- [ ] **Step 6: Commit any final polish**

```bash
git add "search.php" "template-parts/search/results.php" "inc/search-results-functions.php" "tests/Unit/SearchResultsTest.php" "functions.php" "index.php"
git commit -m "feat: implement dedicated search results page"
```

## Self-Review

- Spec coverage: helper, dedicated renderer, `search.php`, populated/no-results states, sorting, filtering, pagination, and verification are all covered by Tasks 1-5.
- Placeholder scan: no `TODO`, `TBD`, or “similar to above” shortcuts remain.
- Type consistency: query vars remain `s`, `search_type`, `search_sort`, and `paged` throughout the plan.

## Execution Handoff

Plan complete and saved to `docs/superpowers/plans/2026-05-14-search-results.md`.

Two execution options:

1. Subagent-Driven (recommended) - dispatch a fresh subagent per task, review between tasks
2. Inline Execution - execute tasks in this session with checkpoints
