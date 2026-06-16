<?php

function matrix_programmes_therapies_archive_sanitize_slug($value)
{
    if (function_exists('sanitize_title')) {
        return sanitize_title($value);
    }

    $value = strtolower(trim((string) $value));
    $value = preg_replace('/[^a-z0-9\s-]/', '', $value);
    $value = preg_replace('/[\s-]+/', '-', $value);

    return trim((string) $value, '-');
}

function matrix_programmes_therapies_archive_add_query_args($params, $base_url)
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

function matrix_get_programmes_therapies_archive_defaults()
{
    return [
        'heading' => 'Select a programme or therapy',
        'show_all_label' => 'Show all',
        'programmes_label' => 'Programmes',
        'therapies_label' => 'Therapies',
        'delivery_all_label' => 'All',
        'delivery_hybrid_label' => 'Hybrid',
        'delivery_online_label' => 'Online',
        'delivery_in_person_label' => 'In person',
        'posts_per_page' => 10,
        'empty_state_message' => 'No programmes or therapies matched your filters.',
    ];
}

function matrix_get_programmes_therapies_archive_default_wrapper_classes()
{
    return 'mx-auto flex w-full max-w-[1018px] flex-col px-5 py-12 xl:px-0 xl:py-[100px]';
}

function matrix_get_programmes_therapies_archive_delivery_options($defaults = null)
{
    $defaults = is_array($defaults) ? $defaults : matrix_get_programmes_therapies_archive_defaults();

    return [
        ['slug' => 'all', 'label' => (string) ($defaults['delivery_all_label'] ?? 'All')],
        ['slug' => 'hybrid', 'label' => (string) ($defaults['delivery_hybrid_label'] ?? 'Hybrid')],
        ['slug' => 'online', 'label' => (string) ($defaults['delivery_online_label'] ?? 'Online')],
        ['slug' => 'in-person', 'label' => (string) ($defaults['delivery_in_person_label'] ?? 'In person')],
    ];
}

function matrix_get_programmes_therapies_archive_type_options($defaults = null)
{
    $defaults = is_array($defaults) ? $defaults : matrix_get_programmes_therapies_archive_defaults();

    return [
        ['slug' => 'all', 'label' => (string) ($defaults['show_all_label'] ?? 'Show all')],
        ['slug' => 'programmes', 'label' => (string) ($defaults['programmes_label'] ?? 'Programmes')],
        ['slug' => 'therapies', 'label' => (string) ($defaults['therapies_label'] ?? 'Therapies')],
    ];
}

function matrix_resolve_programmes_therapies_archive_base_url($preferred_base_url = '')
{
    $preferred_base_url = trim((string) $preferred_base_url);

    if ($preferred_base_url !== '') {
        return $preferred_base_url;
    }

    $archive_url = function_exists('get_post_type_archive_link') ? get_post_type_archive_link('programmes_therapies') : '';
    if (is_string($archive_url) && $archive_url !== '') {
        return $archive_url;
    }

    return function_exists('home_url') ? home_url('/programmes-therapies/') : '/programmes-therapies/';
}

function matrix_resolve_programmes_therapies_archive_state($request, $allowed_type_slugs = [], $allowed_care_slugs = [], $allowed_delivery_slugs = [], $posts_per_page = 10)
{
    $allowed_type_slugs = array_values(array_filter(array_map('matrix_programmes_therapies_archive_sanitize_slug', (array) $allowed_type_slugs)));
    $allowed_care_slugs = array_values(array_filter(array_map('matrix_programmes_therapies_archive_sanitize_slug', (array) $allowed_care_slugs)));
    $allowed_delivery_slugs = array_values(array_filter(array_map('matrix_programmes_therapies_archive_sanitize_slug', (array) $allowed_delivery_slugs)));

    $type = matrix_programmes_therapies_archive_sanitize_slug((string) ($request['pt_type'] ?? 'all'));
    $care = matrix_programmes_therapies_archive_sanitize_slug((string) ($request['pt_care'] ?? 'all'));
    $delivery = matrix_programmes_therapies_archive_sanitize_slug((string) ($request['pt_delivery'] ?? 'all'));
    $paged = (int) ($request['pt_page'] ?? 1);
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

    if ($care === '' || $care === 'all') {
        $care = 'all';
    } elseif ($allowed_care_slugs !== [] && ! in_array($care, $allowed_care_slugs, true)) {
        $care = 'all';
    }

    if ($delivery === '' || $delivery === 'all') {
        $delivery = 'all';
    } elseif ($allowed_delivery_slugs !== [] && ! in_array($delivery, $allowed_delivery_slugs, true)) {
        $delivery = 'all';
    }

    return [
        'type' => $type,
        'care' => $care,
        'delivery' => $delivery,
        'paged' => $paged,
        'posts_per_page' => $posts_per_page,
    ];
}

function matrix_build_programmes_therapies_archive_query_args($state, $type_slug_to_id_map = [], $care_slug_to_id_map = [], $delivery_slug_to_id_map = [])
{
    $args = [
        'post_type' => 'programmes_therapies',
        'post_status' => 'publish',
        'orderby' => 'title',
        'order' => 'ASC',
        'posts_per_page' => (int) ($state['posts_per_page'] ?? 10),
        'paged' => max(1, (int) ($state['paged'] ?? 1)),
    ];

    $tax_query = [];
    $type = matrix_programmes_therapies_archive_sanitize_slug((string) ($state['type'] ?? 'all'));
    $type_id = (int) ($type_slug_to_id_map[$type] ?? 0);
    if ($type !== 'all' && $type_id > 0) {
        $tax_query[] = [
            'taxonomy' => 'programme_therapy_type',
            'field' => 'term_id',
            'terms' => [$type_id],
        ];
    }

    $care = matrix_programmes_therapies_archive_sanitize_slug((string) ($state['care'] ?? 'all'));
    $care_id = (int) ($care_slug_to_id_map[$care] ?? 0);
    if ($care !== 'all' && $care_id > 0) {
        $tax_query[] = [
            'taxonomy' => 'care_setting',
            'field' => 'term_id',
            'terms' => [$care_id],
        ];
    }

    $delivery = matrix_programmes_therapies_archive_sanitize_slug((string) ($state['delivery'] ?? 'all'));
    $delivery_id = (int) ($delivery_slug_to_id_map[$delivery] ?? 0);
    if ($delivery !== 'all' && $delivery_id > 0) {
        $tax_query[] = [
            'taxonomy' => 'delivery_format',
            'field' => 'term_id',
            'terms' => [$delivery_id],
        ];
    }

    if ($tax_query !== []) {
        $args['tax_query'] = array_merge(['relation' => 'AND'], $tax_query);
    }

    return $args;
}

function matrix_get_programmes_therapies_post_summary($post_id)
{
    $post_id = (int) $post_id;

    if ($post_id < 1) {
        return '';
    }

    if (function_exists('get_field')) {
        $summary = trim((string) get_field('listing_summary', $post_id));
        if ($summary !== '') {
            return $summary;
        }
    }

    $excerpt = trim((string) get_post_field('post_excerpt', $post_id));
    if ($excerpt !== '') {
        return $excerpt;
    }

    $content = (string) get_post_field('post_content', $post_id);

    return wp_trim_words(wp_strip_all_tags($content), 32, '...');
}

function matrix_map_programmes_therapies_post_card($post_id)
{
    $post_id = (int) $post_id;
    $tags = [];

    foreach (['programme_therapy_type', 'care_setting', 'delivery_format'] as $taxonomy) {
        $terms = function_exists('get_the_terms') ? get_the_terms($post_id, $taxonomy) : [];

        if (! is_array($terms)) {
            continue;
        }

        foreach ($terms as $term) {
            if (! $term instanceof WP_Term) {
                continue;
            }

            $tags[] = [
                'slug' => $term->slug,
                'label' => $term->name,
                'taxonomy' => $taxonomy,
            ];
        }
    }

    return [
        'id' => $post_id,
        'title' => function_exists('get_the_title') ? get_the_title($post_id) : '',
        'permalink' => function_exists('get_permalink') ? (string) get_permalink($post_id) : '',
        'summary' => matrix_get_programmes_therapies_post_summary($post_id),
        'tags' => $tags,
    ];
}

function matrix_build_programmes_therapies_archive_filter_groups($care_terms = [], $defaults = null)
{
    $defaults = is_array($defaults) ? $defaults : matrix_get_programmes_therapies_archive_defaults();
    $delivery_options = matrix_get_programmes_therapies_archive_delivery_options($defaults);
    $care_groups = [];

    foreach ((array) $care_terms as $term) {
        if (! $term instanceof WP_Term) {
            continue;
        }

        $care_groups[] = [
            'slug' => $term->slug,
            'label' => $term->name,
            'delivery_options' => $delivery_options,
        ];
    }

    return [
        'type_options' => matrix_get_programmes_therapies_archive_type_options($defaults),
        'care_groups' => $care_groups,
    ];
}

function matrix_prepare_programmes_therapies_archive($args = [])
{
    $defaults = matrix_get_programmes_therapies_archive_defaults();
    $request_state = is_array($args['request_state'] ?? null) ? $args['request_state'] : [];
    $posts_per_page = (int) ($args['posts_per_page'] ?? ($defaults['posts_per_page'] ?? 10));

    if ($posts_per_page < 1) {
        $posts_per_page = (int) ($defaults['posts_per_page'] ?? 10);
    }

    if (empty($request_state['pt_page']) && function_exists('get_query_var')) {
        $request_state['pt_page'] = max(1, (int) get_query_var('paged'));
    }

    $type_terms = function_exists('get_terms') ? get_terms([
        'taxonomy' => 'programme_therapy_type',
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC',
    ]) : [];

    if (is_wp_error($type_terms) || ! is_array($type_terms)) {
        $type_terms = [];
    }

    $care_terms = function_exists('get_terms') ? get_terms([
        'taxonomy' => 'care_setting',
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC',
    ]) : [];

    if (is_wp_error($care_terms) || ! is_array($care_terms)) {
        $care_terms = [];
    }

    $delivery_terms = function_exists('get_terms') ? get_terms([
        'taxonomy' => 'delivery_format',
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC',
    ]) : [];

    if (is_wp_error($delivery_terms) || ! is_array($delivery_terms)) {
        $delivery_terms = [];
    }

    $type_slug_to_id_map = [];
    foreach ($type_terms as $term) {
        if ($term instanceof WP_Term) {
            $type_slug_to_id_map[$term->slug] = (int) $term->term_id;
        }
    }

    $care_slug_to_id_map = [];
    foreach ($care_terms as $term) {
        if ($term instanceof WP_Term) {
            $care_slug_to_id_map[$term->slug] = (int) $term->term_id;
        }
    }

    $delivery_slug_to_id_map = [];
    foreach ($delivery_terms as $term) {
        if ($term instanceof WP_Term) {
            $delivery_slug_to_id_map[$term->slug] = (int) $term->term_id;
        }
    }

    $state = matrix_resolve_programmes_therapies_archive_state(
        $request_state,
        array_keys($type_slug_to_id_map),
        array_keys($care_slug_to_id_map),
        array_keys($delivery_slug_to_id_map),
        $posts_per_page
    );

    $query = class_exists('WP_Query')
        ? new WP_Query(matrix_build_programmes_therapies_archive_query_args($state, $type_slug_to_id_map, $care_slug_to_id_map, $delivery_slug_to_id_map))
        : null;

    $heading = trim((string) ($args['heading'] ?? ''));
    $heading_tag = trim((string) ($args['heading_tag'] ?? 'h2'));
    $empty_state_message = trim((string) ($args['empty_state_message'] ?? ''));
    $section_classes = trim((string) ($args['section_classes'] ?? ''));
    $wrapper_classes = trim((string) ($args['wrapper_classes'] ?? ''));

    if (! in_array($heading_tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p'], true)) {
        $heading_tag = 'h2';
    }

    return [
        'section_id' => trim((string) ($args['section_id'] ?? '')),
        'data_block' => trim((string) ($args['data_block'] ?? '')),
        'heading' => $heading !== '' ? $heading : (string) ($defaults['heading'] ?? 'Select a programme or therapy'),
        'heading_tag' => $heading_tag,
        'empty_state_message' => $empty_state_message !== '' ? $empty_state_message : (string) ($defaults['empty_state_message'] ?? 'No programmes or therapies matched your filters.'),
        'base_url' => matrix_resolve_programmes_therapies_archive_base_url($args['base_url'] ?? ''),
        'state' => $state,
        'filters' => matrix_build_programmes_therapies_archive_filter_groups($care_terms, $defaults),
        'query' => $query,
        'pagination' => [
            'current' => $query instanceof WP_Query ? max(1, (int) $query->get('paged')) : 1,
            'total' => $query instanceof WP_Query ? max(1, (int) $query->max_num_pages) : 1,
        ],
        'section_classes' => $section_classes !== '' ? $section_classes : 'relative flex overflow-hidden w-full',
        'wrapper_classes' => $wrapper_classes !== '' ? $wrapper_classes : matrix_get_programmes_therapies_archive_default_wrapper_classes(),
    ];
}

function matrix_build_programmes_therapies_archive_page_url($base_url, $state, $page)
{
    $page = max(1, (int) $page);
    $params = [
        'pt_page' => $page,
    ];

    $type = matrix_programmes_therapies_archive_sanitize_slug((string) ($state['type'] ?? 'all'));
    if ($type !== '' && $type !== 'all') {
        $params['pt_type'] = $type;
    }

    $care = matrix_programmes_therapies_archive_sanitize_slug((string) ($state['care'] ?? 'all'));
    if ($care !== '' && $care !== 'all') {
        $params['pt_care'] = $care;
    }

    $delivery = matrix_programmes_therapies_archive_sanitize_slug((string) ($state['delivery'] ?? 'all'));
    if ($delivery !== '' && $delivery !== 'all') {
        $params['pt_delivery'] = $delivery;
    }

    return matrix_programmes_therapies_archive_add_query_args($params, $base_url);
}

function matrix_render_programmes_therapies_archive_results_html($archive)
{
    if (! is_array($archive) || $archive === []) {
        return '';
    }

    ob_start();
    get_template_part('template-parts/programmes-therapies/archive-results', null, [
        'programmes_therapies_archive' => $archive,
    ]);

    return (string) ob_get_clean();
}

function matrix_fetch_programmes_therapies_archive_response(array $params)
{
    $posts_per_page = (int) ($params['posts_per_page'] ?? 0);
    $empty_state_message = trim((string) ($params['empty_state_message'] ?? ''));

    $archive = matrix_prepare_programmes_therapies_archive([
        'request_state' => [
            'pt_type' => (string) ($params['pt_type'] ?? 'all'),
            'pt_care' => (string) ($params['pt_care'] ?? 'all'),
            'pt_delivery' => (string) ($params['pt_delivery'] ?? 'all'),
            'pt_page' => (string) ($params['pt_page'] ?? '1'),
        ],
        'posts_per_page' => $posts_per_page > 0 ? $posts_per_page : null,
        'base_url' => (string) ($params['base_url'] ?? ''),
        'empty_state_message' => $empty_state_message !== '' ? $empty_state_message : null,
    ]);

    return [
        'html' => matrix_render_programmes_therapies_archive_results_html($archive),
        'pagination' => $archive['pagination'] ?? [],
        'state' => $archive['state'] ?? [],
    ];
}

function matrix_ajax_programmes_therapies_archive()
{
    wp_send_json_success(matrix_fetch_programmes_therapies_archive_response($_REQUEST));
}

function matrix_rest_programmes_therapies_archive(WP_REST_Request $request)
{
    return new WP_REST_Response(matrix_fetch_programmes_therapies_archive_response([
        'pt_type' => (string) $request->get_param('pt_type'),
        'pt_care' => (string) $request->get_param('pt_care'),
        'pt_delivery' => (string) $request->get_param('pt_delivery'),
        'pt_page' => (string) $request->get_param('pt_page'),
        'posts_per_page' => (int) $request->get_param('posts_per_page'),
        'base_url' => (string) $request->get_param('base_url'),
        'empty_state_message' => (string) $request->get_param('empty_state_message'),
    ]), 200);
}

function matrix_register_programmes_therapies_archive_rest_route()
{
    register_rest_route('matrix/v1', '/programmes-therapies-archive', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'matrix_rest_programmes_therapies_archive',
        'permission_callback' => '__return_true',
        'args' => [
            'pt_type' => [
                'type' => 'string',
                'default' => 'all',
                'sanitize_callback' => 'matrix_programmes_therapies_archive_sanitize_slug',
            ],
            'pt_care' => [
                'type' => 'string',
                'default' => 'all',
                'sanitize_callback' => 'matrix_programmes_therapies_archive_sanitize_slug',
            ],
            'pt_delivery' => [
                'type' => 'string',
                'default' => 'all',
                'sanitize_callback' => 'matrix_programmes_therapies_archive_sanitize_slug',
            ],
            'pt_page' => [
                'type' => 'integer',
                'default' => 1,
                'minimum' => 1,
            ],
            'posts_per_page' => [
                'type' => 'integer',
                'default' => 10,
                'minimum' => 1,
            ],
            'base_url' => [
                'type' => 'string',
                'required' => true,
                'sanitize_callback' => 'esc_url_raw',
            ],
            'empty_state_message' => [
                'type' => 'string',
                'default' => '',
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ],
    ]);
}

if (function_exists('add_action')) {
    add_action('wp_ajax_matrix_programmes_therapies_archive', 'matrix_ajax_programmes_therapies_archive');
    add_action('wp_ajax_nopriv_matrix_programmes_therapies_archive', 'matrix_ajax_programmes_therapies_archive');
    add_action('rest_api_init', 'matrix_register_programmes_therapies_archive_rest_route');
}
