<?php

function matrix_blog_filter_archive_sanitize_slug($value)
{
    if (function_exists('sanitize_title')) {
        return sanitize_title($value);
    }

    $value = strtolower(trim((string) $value));
    $value = preg_replace('/[^a-z0-9\s-]/', '', $value);
    $value = preg_replace('/[\s-]+/', '-', $value);

    return trim((string) $value, '-');
}

function matrix_blog_filter_archive_add_query_args($params, $base_url)
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
    $allowed_category_slugs = array_values(array_filter(array_map('matrix_blog_filter_archive_sanitize_slug', (array) $allowed_category_slugs)));
    $category = matrix_blog_filter_archive_sanitize_slug((string) ($request['blog_category'] ?? 'all'));
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

    $category = matrix_blog_filter_archive_sanitize_slug((string) ($state['category'] ?? 'all'));
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

    $category = matrix_blog_filter_archive_sanitize_slug((string) ($state['category'] ?? 'all'));
    if ($category !== '' && $category !== 'all') {
        $params['blog_category'] = $category;
    }

    $search = trim((string) ($state['search'] ?? ''));
    if ($search !== '') {
        $params['blog_search'] = $search;
    }

    return matrix_blog_filter_archive_add_query_args($params, $base_url);
}

function matrix_get_blog_filter_archive_chip_button_class_names()
{
    return 'btn inline-flex h-[36px] shrink-0 whitespace-nowrap items-center justify-center rounded-full border px-6 text-[14px] font-medium leading-[24px] transition-colors';
}

function matrix_get_blog_filter_archive_horizontal_scroll_class_names()
{
    return 'min-w-0 flex-1 overflow-x-auto overscroll-x-contain scroll-smooth cursor-grab active:cursor-grabbing [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden';
}

function matrix_get_blog_filter_archive_horizontal_scroll_inner_class_names()
{
    return 'flex w-max min-w-full flex-nowrap gap-3';
}

function matrix_get_blog_filter_archive_pagination_class_names()
{
    return 'mt-10 flex w-full max-w-full flex-nowrap items-center justify-center gap-2 overflow-x-auto overscroll-x-contain scroll-smooth px-1 pb-1 [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden';
}

/**
 * @return array<int, array{type: string, page?: int}>
 */
function matrix_build_blog_filter_archive_pagination_items(int $current_page, int $total_pages, int $mid_size = 1, int $end_size = 1): array
{
    $current_page = max(1, $current_page);
    $total_pages = max(1, $total_pages);

    if ($total_pages <= 1) {
        return [];
    }

    $pages_to_show = [];

    for ($page = 1; $page <= min($end_size, $total_pages); $page++) {
        $pages_to_show[$page] = true;
    }

    for ($page = max(1, $total_pages - $end_size + 1); $page <= $total_pages; $page++) {
        $pages_to_show[$page] = true;
    }

    for ($page = max(1, $current_page - $mid_size); $page <= min($total_pages, $current_page + $mid_size); $page++) {
        $pages_to_show[$page] = true;
    }

    ksort($pages_to_show);

    $items = [];
    $previous_page = 0;

    foreach (array_keys($pages_to_show) as $page) {
        if ($previous_page > 0 && $page > $previous_page + 1) {
            $items[] = ['type' => 'ellipsis'];
        }

        $items[] = [
            'type' => 'page',
            'page' => $page,
        ];

        $previous_page = $page;
    }

    return $items;
}

function matrix_get_blog_post_category_badge_colors($category_name, $category_slug = '')
{
    $slug = $category_slug !== ''
        ? matrix_blog_filter_archive_sanitize_slug($category_slug)
        : matrix_blog_filter_archive_sanitize_slug($category_name);

    $background = '#80CCD9';

    if ($slug === 'events') {
        $background = '#C3DBAE';
    } elseif ($slug === 'news') {
        $background = '#F4978E';
    }

    return [
        'background' => $background,
        'text' => '#08284B',
    ];
}
