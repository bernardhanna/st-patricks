<?php

function matrix_search_results_sanitize_key($value)
{
    if (function_exists('sanitize_key')) {
        return sanitize_key($value);
    }

    $value = strtolower((string) $value);

    return preg_replace('/[^a-z0-9_\-]/', '', $value) ?? '';
}

function matrix_search_results_add_query_args($params, $base_url)
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

function matrix_get_search_results_type_options()
{
    return [
        [
            'value' => 'all',
            'label' => 'All',
        ],
        [
            'value' => 'blog',
            'label' => 'Blog',
        ],
        [
            'value' => 'page',
            'label' => 'Pages',
        ],
        [
            'value' => 'webinars',
            'label' => 'Webinars',
        ],
    ];
}

function matrix_get_search_results_sort_options()
{
    return [
        [
            'value' => 'relevance',
            'label' => 'Relevance',
        ],
        [
            'value' => 'date',
            'label' => 'Date',
        ],
    ];
}

function matrix_normalize_search_results_type($type)
{
    $type = matrix_search_results_sanitize_key((string) $type);

    if (! array_key_exists($type, matrix_get_search_results_allowed_types())) {
        return 'all';
    }

    return $type;
}

function matrix_normalize_search_results_sort($sort)
{
    $sort = matrix_search_results_sanitize_key((string) $sort);

    if (! in_array($sort, matrix_get_search_results_allowed_sorts(), true)) {
        return 'relevance';
    }

    return $sort;
}

function matrix_resolve_search_results_state($request)
{
    $query = trim((string) ($request['s'] ?? ''));
    $type = matrix_normalize_search_results_type($request['search_type'] ?? 'all');
    $sort = matrix_normalize_search_results_sort($request['search_sort'] ?? 'relevance');
    $paged = max(1, (int) ($request['paged'] ?? 1));

    return [
        'query' => $query,
        'type' => $type,
        'sort' => $sort,
        'paged' => $paged,
    ];
}

function matrix_build_search_results_query_args($state)
{
    $type = matrix_normalize_search_results_type($state['type'] ?? 'all');
    $sort = matrix_normalize_search_results_sort($state['sort'] ?? 'relevance');
    $query = trim((string) ($state['query'] ?? ''));

    $args = [
        'post_type' => matrix_get_search_results_allowed_types()[$type],
        'post_status' => 'publish',
        'paged' => max(1, (int) ($state['paged'] ?? 1)),
    ];

    if ($query === '') {
        $args['post__in'] = [0];

        return $args;
    }

    $args['s'] = $query;

    if ($sort === 'date') {
        $args['orderby'] = 'date';
        $args['order'] = 'DESC';
    }

    return $args;
}

function matrix_build_search_results_page_url($base_url, $state, $page)
{
    return matrix_search_results_add_query_args([
        's' => trim((string) ($state['query'] ?? '')),
        'search_type' => matrix_normalize_search_results_type($state['type'] ?? 'all'),
        'search_sort' => matrix_normalize_search_results_sort($state['sort'] ?? 'relevance'),
        'paged' => max(1, (int) $page),
    ], $base_url);
}

function matrix_get_search_results_heading_data($query, $has_results)
{
    $query = trim((string) $query);

    if ($query === '') {
        return [
            'prefix' => 'Search Results',
            'query' => '',
        ];
    }

    return [
        'prefix' => $has_results ? 'Search result for' : "We couldn\u{2019}t find a match for",
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

function matrix_get_search_results_base_url()
{
    if (function_exists('home_url')) {
        return home_url('/');
    }

    return '/';
}

function matrix_get_search_results_image_alt($image_id, $fallback = '')
{
    $image_id = (int) $image_id;

    if ($image_id <= 0 || ! function_exists('get_post_meta')) {
        return '';
    }

    $alt = trim((string) get_post_meta($image_id, '_wp_attachment_image_alt', true));

    if ($alt !== '') {
        return $alt;
    }

    return trim((string) $fallback);
}

function matrix_format_search_results_date_value($raw_date, $fallback_post = null)
{
    $raw_date = trim((string) $raw_date);

    if ($raw_date !== '' && preg_match('/^\d{8}$/', $raw_date) === 1) {
        $date = DateTime::createFromFormat('Ymd', $raw_date);

        if ($date instanceof DateTime) {
            return $date->format('d/m/y');
        }
    }

    if ($raw_date !== '') {
        $timestamp = strtotime($raw_date);

        if ($timestamp !== false) {
            if (function_exists('date_i18n')) {
                return date_i18n('d/m/y', $timestamp);
            }

            return date('d/m/y', $timestamp);
        }
    }

    if ($fallback_post !== null && function_exists('get_the_date')) {
        return (string) get_the_date('d/m/y', $fallback_post);
    }

    return '';
}

function matrix_get_search_results_date_label($post)
{
    if (! is_object($post) || ! function_exists('get_post_type')) {
        return '';
    }

    $post_type = (string) get_post_type($post);

    if ($post_type === 'webinars') {
        $webinar_date = function_exists('get_field') ? get_field('webinar_date', $post->ID) : '';

        return matrix_format_search_results_date_value($webinar_date, $post);
    }

    if ($post_type === 'post' && function_exists('get_the_date')) {
        return (string) get_the_date('d/m/y', $post);
    }

    return '';
}

function matrix_get_search_results_excerpt($post)
{
    if (! is_object($post)) {
        return '';
    }

    if (function_exists('get_the_excerpt')) {
        $excerpt = trim((string) get_the_excerpt($post));

        if ($excerpt !== '') {
            return $excerpt;
        }
    }

    $content = function_exists('get_post_field')
        ? (string) get_post_field('post_content', $post->ID)
        : (string) ($post->post_content ?? '');
    $content = trim(function_exists('wp_strip_all_tags') ? wp_strip_all_tags($content) : strip_tags($content));

    if ($content === '') {
        return '';
    }

    if (function_exists('wp_trim_words')) {
        return wp_trim_words($content, 24, '...');
    }

    $words = preg_split('/\s+/', $content) ?: [];

    if (count($words) <= 24) {
        return implode(' ', $words);
    }

    return implode(' ', array_slice($words, 0, 24)) . '...';
}

function matrix_prepare_search_result_item($post)
{
    if (! is_object($post) || empty($post->ID)) {
        return [];
    }

    $title = function_exists('get_the_title') ? trim((string) get_the_title($post)) : '';
    $type_key = function_exists('get_post_type') ? (string) get_post_type($post) : '';
    $image_id = function_exists('get_post_thumbnail_id') ? (int) get_post_thumbnail_id($post->ID) : 0;

    return [
        'title' => $title,
        'url' => function_exists('get_permalink') ? (string) get_permalink($post) : '',
        'image' => $image_id,
        'image_alt' => matrix_get_search_results_image_alt($image_id, $title),
        'type_key' => $type_key,
        'type_label' => matrix_get_search_results_type_label($type_key),
        'date_label' => matrix_get_search_results_date_label($post),
        'excerpt' => matrix_get_search_results_excerpt($post),
    ];
}

function matrix_prepare_search_results($request)
{
    $state = matrix_resolve_search_results_state((array) $request);
    $base_url = matrix_get_search_results_base_url();
    $type_options = matrix_get_search_results_type_options();
    $sort_options = matrix_get_search_results_sort_options();

    if (! class_exists('WP_Query')) {
        return [
            'state' => $state,
            'query' => null,
            'items' => [],
            'has_results' => false,
            'heading' => matrix_get_search_results_heading_data($state['query'], false),
            'type_options' => $type_options,
            'sort_options' => $sort_options,
            'pagination' => [
                'current' => max(1, (int) ($state['paged'] ?? 1)),
                'total' => 1,
            ],
            'base_url' => $base_url,
            'useful_links' => matrix_prepare_useful_links_section(),
        ];
    }

    $query_state = $state;
    $query = new WP_Query(matrix_build_search_results_query_args($query_state));
    $raw_total_pages = (int) ($query->max_num_pages ?? 0);

    if ($raw_total_pages > 0 && (int) ($query_state['paged'] ?? 1) > $raw_total_pages) {
        $query_state['paged'] = $raw_total_pages;
        $query = new WP_Query(matrix_build_search_results_query_args($query_state));
        $raw_total_pages = (int) ($query->max_num_pages ?? 0);
    }

    $items = [];

    foreach (($query->posts ?? []) as $post) {
        $item = matrix_prepare_search_result_item($post);

        if ($item !== []) {
            $items[] = $item;
        }
    }

    $has_results = $items !== [];
    $total_pages = max(1, $raw_total_pages);
    $current_page = method_exists($query, 'get')
        ? max(1, min((int) $query->get('paged'), $total_pages))
        : max(1, min((int) ($query_state['paged'] ?? 1), $total_pages));

    return [
        'state' => $query_state,
        'query' => $query,
        'items' => $items,
        'has_results' => $has_results,
        'heading' => matrix_get_search_results_heading_data($query_state['query'], $has_results),
        'type_options' => $type_options,
        'sort_options' => $sort_options,
        'pagination' => [
            'current' => $current_page,
            'total' => $total_pages,
        ],
        'base_url' => $base_url,
        'useful_links' => $has_results ? null : matrix_prepare_useful_links_section(),
    ];
}
