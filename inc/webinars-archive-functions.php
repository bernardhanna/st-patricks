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

function matrix_get_webinars_archive_default_wrapper_classes()
{
    return 'mx-auto flex w-full max-w-[1018px] flex-col px-5 py-12 xl:px-0 xl:py-[100px]';
}

function matrix_get_webinars_archive_card_grid_class_names()
{
    return 'mt-8 grid grid-cols-1 gap-4 lg:mt-10 lg:grid-cols-2';
}

function matrix_get_webinars_archive_search_row_class_names()
{
    return 'flex w-full max-w-[384px] flex-col gap-3 min-[420px]:flex-row min-[420px]:items-center';
}

function matrix_get_webinars_archive_search_button_class_names()
{
    return 'btn inline-flex h-[40px] w-full items-center justify-center gap-2 rounded-[6px] bg-[#08284B] px-4 text-[14px] font-medium leading-[24px] text-white min-[420px]:w-auto';
}

function matrix_resolve_webinars_archive_base_url($preferred_base_url = '')
{
    $preferred_base_url = trim((string) $preferred_base_url);

    if ($preferred_base_url !== '') {
        return $preferred_base_url;
    }

    $archive_url = function_exists('get_post_type_archive_link') ? get_post_type_archive_link('webinars') : '';
    if (is_string($archive_url) && $archive_url !== '') {
        return $archive_url;
    }

    return function_exists('home_url') ? home_url('/webinars/') : '/webinars/';
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

function matrix_prepare_webinars_archive($args = [])
{
    $defaults = matrix_get_webinars_archive_defaults();
    $allowed_type_ids = array_values(array_filter(array_map('intval', (array) ($args['allowed_type_ids'] ?? []))));
    $request_state = is_array($args['request_state'] ?? null) ? $args['request_state'] : [];
    $posts_per_page = (int) ($args['posts_per_page'] ?? ($defaults['posts_per_page'] ?? 10));

    if ($posts_per_page < 1) {
        $posts_per_page = (int) ($defaults['posts_per_page'] ?? 10);
    }

    if (empty($request_state['webinar_page']) && function_exists('get_query_var')) {
        $request_state['webinar_page'] = max(1, (int) get_query_var('paged'));
    }

    $terms_args = [
        'taxonomy' => 'webinar_type',
        'hide_empty' => true,
        'orderby' => 'name',
        'order' => 'ASC',
    ];

    if ($allowed_type_ids !== []) {
        $terms_args['include'] = $allowed_type_ids;
        $terms_args['orderby'] = 'include';
    }

    $terms = get_terms($terms_args);
    if (is_wp_error($terms) || ! is_array($terms)) {
        $terms = [];
    }

    $chips = [
        [
            'slug' => 'all',
            'label' => 'All',
        ],
    ];
    $slug_to_id_map = [];

    foreach ($terms as $term) {
        if (! $term instanceof WP_Term) {
            continue;
        }

        $chips[] = [
            'slug' => $term->slug,
            'label' => $term->name,
            'term_id' => (int) $term->term_id,
        ];
        $slug_to_id_map[$term->slug] = (int) $term->term_id;
    }

    $state = matrix_resolve_webinars_archive_state($request_state, array_keys($slug_to_id_map), $posts_per_page);
    $query = new WP_Query(matrix_build_webinars_archive_query_args($state, $slug_to_id_map));

    $filter_label = trim((string) ($args['filter_label'] ?? ''));
    $search_placeholder = trim((string) ($args['search_placeholder'] ?? ''));
    $search_button_label = trim((string) ($args['search_button_label'] ?? ''));
    $empty_state_message = trim((string) ($args['empty_state_message'] ?? ''));
    $section_classes = trim((string) ($args['section_classes'] ?? ''));
    $wrapper_classes = trim((string) ($args['wrapper_classes'] ?? ''));

    return [
        'section_id' => trim((string) ($args['section_id'] ?? '')),
        'data_block' => trim((string) ($args['data_block'] ?? '')),
        'filter_label' => $filter_label !== '' ? $filter_label : (string) ($defaults['filter_label'] ?? 'Filter by:'),
        'search_placeholder' => $search_placeholder !== '' ? $search_placeholder : (string) ($defaults['search_placeholder'] ?? 'Search webinars and events'),
        'search_button_label' => $search_button_label !== '' ? $search_button_label : (string) ($defaults['search_button_label'] ?? 'Search'),
        'empty_state_message' => $empty_state_message !== '' ? $empty_state_message : (string) ($defaults['empty_state_message'] ?? 'No webinars matched your filters.'),
        'base_url' => matrix_resolve_webinars_archive_base_url($args['base_url'] ?? ''),
        'state' => $state,
        'chips' => $chips,
        'query' => $query,
        'pagination' => [
            'current' => max(1, (int) $query->get('paged')),
            'total' => max(1, (int) $query->max_num_pages),
        ],
        'section_classes' => $section_classes !== '' ? $section_classes : 'relative flex overflow-hidden w-full',
        'wrapper_classes' => $wrapper_classes !== '' ? $wrapper_classes : matrix_get_webinars_archive_default_wrapper_classes(),
    ];
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
