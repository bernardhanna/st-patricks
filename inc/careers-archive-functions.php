<?php

if (function_exists('add_action')) {
    add_action('init', function () {
        $careers_page = get_page_by_path('careers');

        if (! $careers_page instanceof WP_Post) {
            return;
        }

        $child_pages = get_pages([
            'parent' => (int) $careers_page->ID,
            'post_status' => 'publish',
            'number' => 50,
        ]);

        if (! is_array($child_pages)) {
            return;
        }

        foreach ($child_pages as $child_page) {
            if (! $child_page instanceof WP_Post || $child_page->post_name === '') {
                continue;
            }

            add_rewrite_rule(
                '^careers/' . preg_quote($child_page->post_name, '/') . '/?$',
                'index.php?page_id=' . (int) $child_page->ID,
                'top'
            );
        }
    }, 20);
}

function matrix_careers_archive_sanitize_slug($value)
{
    if (function_exists('sanitize_title')) {
        return sanitize_title($value);
    }

    $value = strtolower(trim((string) $value));
    $value = preg_replace('/[^a-z0-9\s-]/', '', $value);
    $value = preg_replace('/[\s-]+/', '-', $value);

    return trim((string) $value, '-');
}

function matrix_careers_archive_add_query_args($params, $base_url)
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

function matrix_get_careers_archive_defaults()
{
    return [
        'heading' => 'Current Vacancies',
        'filter_label' => 'Filter by:',
        'department_placeholder' => 'Department',
        'location_placeholder' => 'Location',
        'apply_filters_label' => 'Apply filters',
        'search_placeholder' => 'Search vacancies',
        'search_button_label' => 'Search',
        'view_detail_label' => 'View detail',
        'posts_per_page' => 10,
        'empty_state_message' => 'No vacancies matched your filters.',
    ];
}

function matrix_get_careers_archive_default_wrapper_classes()
{
    return 'mx-auto flex w-full max-w-[1018px] flex-col px-4 py-12 xl:px-0 xl:py-[100px]';
}

function matrix_get_careers_archive_table_header_style()
{
    return 'background-image: linear-gradient(-18.96deg, rgb(243, 234, 222) 3.24%, rgb(241, 243, 222) 90.88%);';
}

function matrix_get_careers_archive_mobile_table_header_style()
{
    return 'background-image: linear-gradient(-13.24deg, rgb(243, 234, 222) 3.24%, rgb(241, 243, 222) 90.88%);';
}

function matrix_get_careers_archive_filter_select_class_names()
{
    return implode(' ', [
        'w-full',
        'appearance-none',
        'rounded-[6px]',
        'border',
        'border-[#E2E8F0]',
        'bg-white',
        'bg-[length:16px_16px]',
        'bg-[right_12px_center]',
        'bg-no-repeat',
        'px-3',
        'py-2',
        'pr-10',
        'font-primary',
        'text-[16px]',
        'font-normal',
        'leading-[24px]',
        'text-[#08284B]',
        'focus:outline-none',
        'focus:ring-2',
        'focus:ring-offset-2',
        'focus:ring-[#024B79]',
    ]);
}

function matrix_get_careers_archive_apply_filters_button_class_names()
{
    return 'btn inline-flex h-[36px] w-fit shrink-0 items-center justify-center rounded-[6px] border border-transparent bg-[#08284B] px-3 text-[14px] font-medium leading-[24px] text-white hover:border-transparent hover:bg-[#024B79] hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79] lg:w-auto';
}

function matrix_get_careers_archive_view_detail_button_class_names()
{
    return 'btn inline-flex h-[36px] w-full min-w-[104px] items-center justify-center whitespace-nowrap rounded-[6px] border border-transparent bg-[#024B79] px-3 text-[14px] font-medium leading-[24px] text-white no-underline hover:border-transparent hover:bg-[#08284B] hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]';
}

function matrix_resolve_careers_archive_base_url($preferred_base_url = '')
{
    $preferred_base_url = trim((string) $preferred_base_url);

    if ($preferred_base_url !== '') {
        return $preferred_base_url;
    }

    $archive_url = function_exists('get_post_type_archive_link') ? get_post_type_archive_link('careers') : '';
    if (is_string($archive_url) && $archive_url !== '') {
        return $archive_url;
    }

    return function_exists('home_url') ? home_url('/careers/') : '/careers/';
}

function matrix_resolve_careers_archive_state($request, $allowed_department_slugs = [], $allowed_location_slugs = [], $posts_per_page = 10)
{
    $allowed_department_slugs = array_values(array_filter(array_map('matrix_careers_archive_sanitize_slug', (array) $allowed_department_slugs)));
    $allowed_location_slugs = array_values(array_filter(array_map('matrix_careers_archive_sanitize_slug', (array) $allowed_location_slugs)));
    $department = matrix_careers_archive_sanitize_slug((string) ($request['career_department'] ?? 'all'));
    $location = matrix_careers_archive_sanitize_slug((string) ($request['career_location'] ?? 'all'));
    $search = trim((string) ($request['career_search'] ?? ''));
    $paged = (int) ($request['career_page'] ?? 1);
    $posts_per_page = (int) $posts_per_page;

    if ($posts_per_page < 1) {
        $posts_per_page = 10;
    }

    if ($paged < 1) {
        $paged = 1;
    }

    if ($department === '' || $department === 'all') {
        $department = 'all';
    } elseif ($allowed_department_slugs !== [] && ! in_array($department, $allowed_department_slugs, true)) {
        $department = 'all';
    }

    if ($location === '' || $location === 'all') {
        $location = 'all';
    } elseif ($allowed_location_slugs !== [] && ! in_array($location, $allowed_location_slugs, true)) {
        $location = 'all';
    }

    return [
        'department' => $department,
        'location' => $location,
        'search' => $search,
        'paged' => $paged,
        'posts_per_page' => $posts_per_page,
    ];
}

function matrix_build_careers_archive_query_args($state, $department_slug_to_id_map = [], $location_slug_to_id_map = [])
{
    $args = [
        'post_type' => 'careers',
        'post_status' => 'publish',
        'orderby' => 'title',
        'order' => 'ASC',
        'posts_per_page' => (int) ($state['posts_per_page'] ?? 10),
        'paged' => max(1, (int) ($state['paged'] ?? 1)),
    ];

    $search = trim((string) ($state['search'] ?? ''));
    if ($search !== '') {
        $args['s'] = $search;
    }

    $tax_query = [];
    $department = matrix_careers_archive_sanitize_slug((string) ($state['department'] ?? 'all'));
    $department_id = (int) ($department_slug_to_id_map[$department] ?? 0);
    if ($department !== 'all' && $department_id > 0) {
        $tax_query[] = [
            'taxonomy' => 'career_department',
            'field' => 'term_id',
            'terms' => [$department_id],
        ];
    }

    $location = matrix_careers_archive_sanitize_slug((string) ($state['location'] ?? 'all'));
    $location_id = (int) ($location_slug_to_id_map[$location] ?? 0);
    if ($location !== 'all' && $location_id > 0) {
        $tax_query[] = [
            'taxonomy' => 'career_location',
            'field' => 'term_id',
            'terms' => [$location_id],
        ];
    }

    if ($tax_query !== []) {
        if (count($tax_query) > 1) {
            $args['tax_query'] = array_merge(['relation' => 'AND'], $tax_query);
        } else {
            $args['tax_query'] = $tax_query;
        }
    }

    return $args;
}

function matrix_get_careers_archive_filter_options($taxonomy, $allowed_term_ids = [])
{
    $terms_args = [
        'taxonomy' => $taxonomy,
        'hide_empty' => true,
        'orderby' => 'name',
        'order' => 'ASC',
    ];

    $allowed_term_ids = array_values(array_filter(array_map('intval', (array) $allowed_term_ids)));
    if ($allowed_term_ids !== []) {
        $terms_args['include'] = $allowed_term_ids;
        $terms_args['orderby'] = 'include';
    }

    $terms = function_exists('get_terms') ? get_terms($terms_args) : [];
    if (is_wp_error($terms) || ! is_array($terms)) {
        return [];
    }

    $options = [];
    $slug_to_id_map = [];

    foreach ($terms as $term) {
        if (! $term instanceof WP_Term) {
            continue;
        }

        $options[] = [
            'slug' => $term->slug,
            'label' => $term->name,
            'term_id' => (int) $term->term_id,
        ];
        $slug_to_id_map[$term->slug] = (int) $term->term_id;
    }

    return [
        'options' => $options,
        'slug_to_id_map' => $slug_to_id_map,
    ];
}

function matrix_map_career_post_row($post_id)
{
    $post_id = (int) $post_id;
    $area = function_exists('get_field') ? trim((string) get_field('career_area', $post_id)) : '';
    $location_terms = function_exists('get_the_terms') ? get_the_terms($post_id, 'career_location') : false;
    $location_label = '';

    if (is_array($location_terms)) {
        foreach ($location_terms as $term) {
            if ($term instanceof WP_Term) {
                $location_label = $term->name;
                break;
            }
        }
    }

    return [
        'id' => $post_id,
        'position' => function_exists('get_the_title') ? get_the_title($post_id) : '',
        'area' => $area,
        'location' => $location_label,
        'permalink' => function_exists('get_permalink') ? get_permalink($post_id) : '',
    ];
}

function matrix_prepare_careers_archive($args = [])
{
    $defaults = matrix_get_careers_archive_defaults();
    $allowed_department_ids = array_values(array_filter(array_map('intval', (array) ($args['allowed_departments'] ?? []))));
    $allowed_location_ids = array_values(array_filter(array_map('intval', (array) ($args['allowed_locations'] ?? []))));
    $request_state = is_array($args['request_state'] ?? null) ? $args['request_state'] : [];
    $posts_per_page = (int) ($args['posts_per_page'] ?? ($defaults['posts_per_page'] ?? 10));

    if ($posts_per_page < 1) {
        $posts_per_page = (int) ($defaults['posts_per_page'] ?? 10);
    }

    if (empty($request_state['career_page']) && function_exists('get_query_var')) {
        $request_state['career_page'] = max(1, (int) get_query_var('paged'));
    }

    $department_data = matrix_get_careers_archive_filter_options('career_department', $allowed_department_ids);
    $location_data = matrix_get_careers_archive_filter_options('career_location', $allowed_location_ids);
    $state = matrix_resolve_careers_archive_state(
        $request_state,
        array_keys($department_data['slug_to_id_map']),
        array_keys($location_data['slug_to_id_map']),
        $posts_per_page
    );
    $query = new WP_Query(matrix_build_careers_archive_query_args(
        $state,
        $department_data['slug_to_id_map'],
        $location_data['slug_to_id_map']
    ));

    $normalize_text = static function ($value, $fallback) {
        $value = trim((string) $value);

        return $value !== '' ? $value : $fallback;
    };

    return [
        'section_id' => trim((string) ($args['section_id'] ?? '')),
        'data_block' => trim((string) ($args['data_block'] ?? '')),
        'heading' => $normalize_text($args['heading'] ?? '', (string) ($defaults['heading'] ?? 'Current Vacancies')),
        'heading_tag' => trim((string) ($args['heading_tag'] ?? 'h2')),
        'filter_label' => $normalize_text($args['filter_label'] ?? '', (string) ($defaults['filter_label'] ?? 'Filter by:')),
        'department_placeholder' => $normalize_text($args['department_placeholder'] ?? '', (string) ($defaults['department_placeholder'] ?? 'Department')),
        'location_placeholder' => $normalize_text($args['location_placeholder'] ?? '', (string) ($defaults['location_placeholder'] ?? 'Location')),
        'apply_filters_label' => $normalize_text($args['apply_filters_label'] ?? '', (string) ($defaults['apply_filters_label'] ?? 'Apply filters')),
        'search_placeholder' => $normalize_text($args['search_placeholder'] ?? '', (string) ($defaults['search_placeholder'] ?? 'Search vacancies')),
        'search_button_label' => $normalize_text($args['search_button_label'] ?? '', (string) ($defaults['search_button_label'] ?? 'Search')),
        'view_detail_label' => $normalize_text($args['view_detail_label'] ?? '', (string) ($defaults['view_detail_label'] ?? 'View detail')),
        'empty_state_message' => $normalize_text($args['empty_state_message'] ?? '', (string) ($defaults['empty_state_message'] ?? 'No vacancies matched your filters.')),
        'base_url' => matrix_resolve_careers_archive_base_url($args['base_url'] ?? ''),
        'state' => $state,
        'department_options' => $department_data['options'],
        'location_options' => $location_data['options'],
        'query' => $query,
        'pagination' => [
            'current' => max(1, (int) $query->get('paged')),
            'total' => max(1, (int) $query->max_num_pages),
        ],
        'section_classes' => trim((string) ($args['section_classes'] ?? '')) !== '' ? trim((string) $args['section_classes']) : 'relative flex overflow-hidden w-full bg-white',
        'wrapper_classes' => trim((string) ($args['wrapper_classes'] ?? '')) !== '' ? trim((string) $args['wrapper_classes']) : matrix_get_careers_archive_default_wrapper_classes(),
        'allowed_department_ids' => $allowed_department_ids,
        'allowed_location_ids' => $allowed_location_ids,
    ];
}

function matrix_build_careers_archive_page_url($base_url, $state, $page)
{
    $page = max(1, (int) $page);
    $params = [
        'career_page' => $page,
    ];

    $department = matrix_careers_archive_sanitize_slug((string) ($state['department'] ?? 'all'));
    if ($department !== '' && $department !== 'all') {
        $params['career_department'] = $department;
    }

    $location = matrix_careers_archive_sanitize_slug((string) ($state['location'] ?? 'all'));
    if ($location !== '' && $location !== 'all') {
        $params['career_location'] = $location;
    }

    $search = trim((string) ($state['search'] ?? ''));
    if ($search !== '') {
        $params['career_search'] = $search;
    }

    return matrix_careers_archive_add_query_args($params, $base_url);
}

function matrix_render_careers_archive_results_html($archive)
{
    if (! is_array($archive) || $archive === []) {
        return '';
    }

    ob_start();
    get_template_part('template-parts/careers/archive-results', null, [
        'careers_archive' => $archive,
    ]);

    return (string) ob_get_clean();
}

function matrix_fetch_careers_archive_response(array $params)
{
    $posts_per_page = (int) ($params['posts_per_page'] ?? 0);
    $empty_state_message = trim((string) ($params['empty_state_message'] ?? ''));
    $view_detail_label = trim((string) ($params['view_detail_label'] ?? ''));
    $allowed_department_ids = array_values(array_filter(array_map('intval', explode(',', (string) ($params['allowed_departments'] ?? '')))));
    $allowed_location_ids = array_values(array_filter(array_map('intval', explode(',', (string) ($params['allowed_locations'] ?? '')))));

    $archive = matrix_prepare_careers_archive([
        'request_state' => [
            'career_department' => (string) ($params['career_department'] ?? 'all'),
            'career_location' => (string) ($params['career_location'] ?? 'all'),
            'career_search' => (string) ($params['career_search'] ?? ''),
            'career_page' => (string) ($params['career_page'] ?? '1'),
        ],
        'posts_per_page' => $posts_per_page > 0 ? $posts_per_page : null,
        'base_url' => (string) ($params['base_url'] ?? ''),
        'empty_state_message' => $empty_state_message !== '' ? $empty_state_message : null,
        'view_detail_label' => $view_detail_label !== '' ? $view_detail_label : null,
        'allowed_departments' => $allowed_department_ids,
        'allowed_locations' => $allowed_location_ids,
    ]);

    return [
        'html' => matrix_render_careers_archive_results_html($archive),
        'pagination' => $archive['pagination'] ?? [],
        'state' => $archive['state'] ?? [],
    ];
}

function matrix_ajax_careers_archive()
{
    wp_send_json_success(matrix_fetch_careers_archive_response($_REQUEST));
}

if (function_exists('add_action')) {
    add_action('wp_ajax_matrix_careers_archive', 'matrix_ajax_careers_archive');
    add_action('wp_ajax_nopriv_matrix_careers_archive', 'matrix_ajax_careers_archive');
}
