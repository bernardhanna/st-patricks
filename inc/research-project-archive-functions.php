<?php

if (function_exists('add_action')) {
    add_action('init', function () {
        add_rewrite_tag('%research_archive_category%', '(current|past)');

        add_rewrite_rule(
            '^research-projects/(current|past)/page/?([0-9]{1,})/?$',
            'index.php?post_type=research_projects&research_archive_category=$matches[1]&paged=$matches[2]',
            'top'
        );

        add_rewrite_rule(
            '^research-projects/(current|past)/?$',
            'index.php?post_type=research_projects&research_archive_category=$matches[1]',
            'top'
        );
    });

    add_filter('query_vars', function ($vars) {
        $vars[] = 'research_archive_category';

        return $vars;
    });
}

function matrix_research_project_archive_sanitize_slug($value)
{
    if (function_exists('sanitize_title')) {
        return sanitize_title($value);
    }

    $value = strtolower(trim((string) $value));
    $value = preg_replace('/[^a-z0-9\s-]/', '', $value);
    $value = preg_replace('/[\s-]+/', '-', $value);

    return trim((string) $value, '-');
}

function matrix_research_project_archive_add_query_args($params, $base_url)
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

function matrix_get_research_project_archive_defaults()
{
    return [
        'heading' => 'Research Projects',
        'filter_label' => 'Filter by:',
        'researcher_filter_label' => 'Filter by profile',
        'researcher_filter_all_label' => 'All profiles',
        'search_placeholder' => 'Search research projects',
        'search_button_label' => 'Search',
        'posts_per_page' => 12,
        'empty_state_message' => 'No research projects matched your filters.',
    ];
}

function matrix_resolve_research_project_archive_base_url($preferred_base_url = '')
{
    $preferred_base_url = trim((string) $preferred_base_url);

    if ($preferred_base_url !== '') {
        return $preferred_base_url;
    }

    $archive_url = function_exists('get_post_type_archive_link') ? get_post_type_archive_link('research_projects') : '';
    if (is_string($archive_url) && $archive_url !== '') {
        return $archive_url;
    }

    return function_exists('home_url') ? home_url('/research-projects/') : '/research-projects/';
}

function matrix_research_project_archive_normalize_path($url)
{
    $path = (string) (function_exists('wp_parse_url') ? wp_parse_url((string) $url, PHP_URL_PATH) : parse_url((string) $url, PHP_URL_PATH));

    if (function_exists('untrailingslashit')) {
        return untrailingslashit($path);
    }

    return rtrim($path, '/');
}

function matrix_is_research_project_main_archive_url($url)
{
    return matrix_research_project_archive_normalize_path($url)
        === matrix_research_project_archive_normalize_path(matrix_resolve_research_project_archive_base_url());
}

function matrix_merge_research_project_archive_request_state($request_state = [])
{
    $request_state = is_array($request_state) ? $request_state : [];

    if (empty($request_state['research_category']) && function_exists('get_query_var')) {
        $path_category = matrix_research_project_archive_sanitize_slug((string) get_query_var('research_archive_category'));
        if ($path_category !== '') {
            $request_state['research_category'] = $path_category;
        }
    }

    return $request_state;
}

function matrix_build_research_project_archive_filter_params($state, $include_category_in_query = true)
{
    $params = [];
    $page = max(1, (int) ($state['paged'] ?? 1));

    if ($include_category_in_query) {
        $category = matrix_research_project_archive_sanitize_slug((string) ($state['category'] ?? 'all'));
        if ($category !== '' && $category !== 'all') {
            $params['research_category'] = $category;
        }
    }

    $researcher = matrix_research_project_archive_sanitize_slug((string) ($state['researcher'] ?? 'all'));
    if ($researcher !== '' && $researcher !== 'all') {
        $params['research_researcher'] = $researcher;
    }

    $search = trim((string) ($state['search'] ?? ''));
    if ($search !== '') {
        $params['research_search'] = $search;
    }

    if ($page > 1) {
        $params['research_page'] = $page;
    }

    return $params;
}

function matrix_build_research_project_archive_page_url($base_url, $state, $page)
{
    $page = max(1, (int) $page);
    $state_with_page = array_merge($state, ['paged' => $page]);
    $category = matrix_research_project_archive_sanitize_slug((string) ($state['category'] ?? 'all'));
    $use_path_category = $category !== 'all' && matrix_is_research_project_main_archive_url($base_url);

    if ($use_path_category) {
        $parts = function_exists('wp_parse_url') ? wp_parse_url($base_url) : parse_url($base_url);
        $origin = '';

        if (! empty($parts['scheme']) && ! empty($parts['host'])) {
            $origin = $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
        }

        $path_base = matrix_research_project_archive_normalize_path($base_url);
        $url = $origin . $path_base . '/' . $category . '/';

        if ($page > 1) {
            $url .= 'page/' . $page . '/';
        }

        $params = matrix_build_research_project_archive_filter_params($state_with_page, false);

        return $params === [] ? $url : matrix_research_project_archive_add_query_args($params, $url);
    }

    return matrix_research_project_archive_add_query_args(
        matrix_build_research_project_archive_filter_params($state_with_page, true),
        $base_url
    );
}

function matrix_resolve_research_project_archive_state($request, $allowed_category_slugs = [], $allowed_researcher_slugs = [], $posts_per_page = 12, $default_category = 'all')
{
    $allowed_category_slugs = array_values(array_filter(array_map('matrix_research_project_archive_sanitize_slug', (array) $allowed_category_slugs)));
    $allowed_researcher_slugs = array_values(array_filter(array_map('matrix_research_project_archive_sanitize_slug', (array) $allowed_researcher_slugs)));
    $default_category = matrix_research_project_archive_sanitize_slug((string) $default_category);

    $category = matrix_research_project_archive_sanitize_slug((string) ($request['research_category'] ?? ''));
    $researcher = matrix_research_project_archive_sanitize_slug((string) ($request['research_researcher'] ?? 'all'));
    $search = trim((string) ($request['research_search'] ?? ''));
    $paged = (int) ($request['research_page'] ?? 1);
    $posts_per_page = (int) $posts_per_page;

    if ($posts_per_page < 1) {
        $posts_per_page = 12;
    }

    if ($paged < 1) {
        $paged = 1;
    }

    if ($category === '') {
        $category = $default_category !== '' && $default_category !== 'all' ? $default_category : 'all';
    }

    if ($category === 'all') {
        $category = 'all';
    } elseif ($allowed_category_slugs !== [] && ! in_array($category, $allowed_category_slugs, true)) {
        $category = $default_category !== '' && $default_category !== 'all' ? $default_category : 'all';
    }

    if ($researcher === '' || $researcher === 'all') {
        $researcher = 'all';
    } elseif ($allowed_researcher_slugs !== [] && ! in_array($researcher, $allowed_researcher_slugs, true)) {
        $researcher = 'all';
    }

    return [
        'category' => $category,
        'researcher' => $researcher,
        'search' => $search,
        'paged' => $paged,
        'posts_per_page' => $posts_per_page,
    ];
}

function matrix_build_research_project_archive_query_args($state, $category_slug_to_id_map = [], $researcher_slug_to_id_map = [])
{
    $args = [
        'post_type' => 'research_projects',
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

    $tax_query = [];
    $category = matrix_research_project_archive_sanitize_slug((string) ($state['category'] ?? 'all'));
    $category_id = (int) ($category_slug_to_id_map[$category] ?? 0);
    if ($category !== 'all' && $category_id > 0) {
        $tax_query[] = [
            'taxonomy' => 'research_project_category',
            'field' => 'term_id',
            'terms' => [$category_id],
        ];
    }

    $researcher = matrix_research_project_archive_sanitize_slug((string) ($state['researcher'] ?? 'all'));
    $researcher_id = (int) ($researcher_slug_to_id_map[$researcher] ?? 0);
    if ($researcher !== 'all' && $researcher_id > 0) {
        $tax_query[] = [
            'taxonomy' => 'researcher',
            'field' => 'term_id',
            'terms' => [$researcher_id],
        ];
    }

    if ($tax_query !== []) {
        if (count($tax_query) > 1) {
            $tax_query['relation'] = 'AND';
        }
        $args['tax_query'] = $tax_query;
    }

    return $args;
}

function matrix_get_research_project_primary_category_name($post_id)
{
    $terms = wp_get_post_terms((int) $post_id, 'research_project_category', ['orderby' => 'term_order', 'order' => 'ASC']);

    if (is_wp_error($terms) || $terms === []) {
        return '';
    }

    $term = $terms[0];

    return $term instanceof WP_Term ? $term->name : '';
}

function matrix_get_research_project_primary_researcher_name($post_id)
{
    $terms = wp_get_post_terms((int) $post_id, 'researcher', ['orderby' => 'name', 'order' => 'ASC']);

    if (is_wp_error($terms) || $terms === []) {
        return '';
    }

    $term = $terms[0];

    return $term instanceof WP_Term ? $term->name : '';
}

function matrix_prepare_research_project_archive($args = [])
{
    $defaults = matrix_get_research_project_archive_defaults();
    $allowed_category_ids = array_values(array_filter(array_map('intval', (array) ($args['allowed_category_ids'] ?? []))));
    $allowed_researcher_ids = array_values(array_filter(array_map('intval', (array) ($args['allowed_researcher_ids'] ?? []))));
    $request_state = matrix_merge_research_project_archive_request_state(
        is_array($args['request_state'] ?? null) ? $args['request_state'] : []
    );
    $posts_per_page = (int) ($args['posts_per_page'] ?? ($defaults['posts_per_page'] ?? 12));
    $default_category = matrix_research_project_archive_sanitize_slug((string) ($args['default_category'] ?? 'all'));
    $lock_category = (bool) ($args['lock_category'] ?? false);

    if ($posts_per_page < 1) {
        $posts_per_page = (int) ($defaults['posts_per_page'] ?? 12);
    }

    if (empty($request_state['research_page']) && function_exists('get_query_var')) {
        $request_state['research_page'] = max(1, (int) get_query_var('paged'));
    }

    $category_terms_args = [
        'taxonomy' => 'research_project_category',
        'hide_empty' => true,
        'orderby' => 'name',
        'order' => 'ASC',
    ];

    if ($allowed_category_ids !== []) {
        $category_terms_args['include'] = $allowed_category_ids;
        $category_terms_args['orderby'] = 'include';
    }

    $category_terms = get_terms($category_terms_args);
    if (is_wp_error($category_terms) || ! is_array($category_terms)) {
        $category_terms = [];
    }

    $chips = [
        [
            'slug' => 'all',
            'label' => 'All',
        ],
    ];
    $category_slug_to_id_map = [];

    foreach ($category_terms as $term) {
        if (! $term instanceof WP_Term) {
            continue;
        }

        $chips[] = [
            'slug' => $term->slug,
            'label' => $term->name,
            'term_id' => (int) $term->term_id,
        ];
        $category_slug_to_id_map[$term->slug] = (int) $term->term_id;
    }

    $researcher_terms_args = [
        'taxonomy' => 'researcher',
        'hide_empty' => true,
        'orderby' => 'name',
        'order' => 'ASC',
    ];

    if ($allowed_researcher_ids !== []) {
        $researcher_terms_args['include'] = $allowed_researcher_ids;
        $researcher_terms_args['orderby'] = 'include';
    }

    $researcher_terms = get_terms($researcher_terms_args);
    if (is_wp_error($researcher_terms) || ! is_array($researcher_terms)) {
        $researcher_terms = [];
    }

    $researcher_options = [
        [
            'slug' => 'all',
            'label' => (string) ($args['researcher_filter_all_label'] ?? $defaults['researcher_filter_all_label']),
        ],
    ];
    $researcher_slug_to_id_map = [];

    foreach ($researcher_terms as $term) {
        if (! $term instanceof WP_Term) {
            continue;
        }

        $researcher_options[] = [
            'slug' => $term->slug,
            'label' => $term->name,
            'term_id' => (int) $term->term_id,
        ];
        $researcher_slug_to_id_map[$term->slug] = (int) $term->term_id;
    }

    $state = matrix_resolve_research_project_archive_state(
        $request_state,
        array_keys($category_slug_to_id_map),
        array_keys($researcher_slug_to_id_map),
        $posts_per_page,
        $default_category
    );

    if ($lock_category && $default_category !== '' && $default_category !== 'all') {
        $state['category'] = $default_category;
    }

    $query = new WP_Query(matrix_build_research_project_archive_query_args($state, $category_slug_to_id_map, $researcher_slug_to_id_map));

    return [
        'section_id' => trim((string) ($args['section_id'] ?? '')),
        'data_block' => trim((string) ($args['data_block'] ?? '')),
        'heading_tag' => (string) ($args['heading_tag'] ?? 'h2'),
        'heading' => trim((string) ($args['heading'] ?? '')) ?: (string) ($defaults['heading'] ?? 'Research Projects'),
        'filter_label' => trim((string) ($args['filter_label'] ?? '')) ?: (string) ($defaults['filter_label'] ?? 'Filter by:'),
        'researcher_filter_label' => trim((string) ($args['researcher_filter_label'] ?? '')) ?: (string) ($defaults['researcher_filter_label'] ?? 'Filter by profile'),
        'search_placeholder' => trim((string) ($args['search_placeholder'] ?? '')) ?: (string) ($defaults['search_placeholder'] ?? 'Search research projects'),
        'search_button_label' => trim((string) ($args['search_button_label'] ?? '')) ?: (string) ($defaults['search_button_label'] ?? 'Search'),
        'empty_state_message' => trim((string) ($args['empty_state_message'] ?? '')) ?: (string) ($defaults['empty_state_message'] ?? 'No research projects matched your filters.'),
        'base_url' => matrix_resolve_research_project_archive_base_url($args['base_url'] ?? ''),
        'state' => $state,
        'chips' => $chips,
        'researcher_options' => $researcher_options,
        'lock_category' => $lock_category,
        'query' => $query,
        'pagination' => [
            'current' => max(1, (int) $query->get('paged')),
            'total' => max(1, (int) $query->max_num_pages),
        ],
        'colors' => is_array($args['colors'] ?? null) ? $args['colors'] : [],
        'section_classes' => trim((string) ($args['section_classes'] ?? 'w-full')),
        'section_style' => trim((string) ($args['section_style'] ?? '')),
        'wrapper_classes' => trim((string) ($args['wrapper_classes'] ?? 'flex w-full max-w-[1018px] flex-col items-center mx-auto pt-5 pb-5 max-xl:px-5')),
    ];
}
