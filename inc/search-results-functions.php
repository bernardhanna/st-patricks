<?php

if (function_exists('add_action')) {
    add_action('init', 'matrix_register_search_results_rewrites');

    add_action('after_switch_theme', function () {
        matrix_register_search_results_rewrites();

        if (function_exists('flush_rewrite_rules')) {
            flush_rewrite_rules();
        }
    });

    add_filter('query_vars', function ($vars) {
        $vars[] = 'matrix_search';

        return $vars;
    });

    add_filter('request', function ($query_vars) {
        if (! array_key_exists('matrix_search', $query_vars)) {
            return $query_vars;
        }

        $matrix_search = (string) ($query_vars['matrix_search'] ?? '');

        if ($matrix_search === '_landing') {
            $query_vars['s'] = '';

            return $query_vars;
        }

        if ($matrix_search !== '') {
            $query_vars['s'] = matrix_search_slug_to_query($matrix_search);
        }

        return $query_vars;
    });

    add_filter('template_include', function ($template) {
        if (! function_exists('get_query_var')) {
            return $template;
        }

        $matrix_search = get_query_var('matrix_search', null);

        if ($matrix_search === '_landing') {
            $search_template = function_exists('locate_template') ? locate_template('search.php') : '';

            return $search_template !== '' ? $search_template : $template;
        }

        if (is_string($matrix_search) && $matrix_search !== '') {
            $search_template = function_exists('locate_template') ? locate_template('search.php') : '';

            return $search_template !== '' ? $search_template : $template;
        }

        return $template;
    });

    add_action('template_redirect', 'matrix_maybe_redirect_legacy_search_url', 1);
}

function matrix_register_search_results_rewrites()
{
    if (! function_exists('add_rewrite_tag') || ! function_exists('add_rewrite_rule')) {
        return;
    }

    add_rewrite_tag('%matrix_search%', '([^/]+)');

    add_rewrite_rule(
        '^search/([^/]+)/page/?([0-9]{1,})/?$',
        'index.php?matrix_search=$matches[1]&paged=$matches[2]',
        'top'
    );

    add_rewrite_rule(
        '^search/([^/]+)/?$',
        'index.php?matrix_search=$matches[1]',
        'top'
    );

    add_rewrite_rule(
        '^search/?$',
        'index.php?matrix_search=_landing',
        'top'
    );
}

function matrix_search_query_to_slug($query)
{
    $query = trim((string) $query);

    if ($query === '') {
        return '';
    }

    if (function_exists('sanitize_title')) {
        return sanitize_title($query);
    }

    $slug = strtolower($query);
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug) ?? '';
    $slug = preg_replace('/[\s-]+/', '-', $slug) ?? '';

    return trim($slug, '-');
}

function matrix_search_slug_to_query($slug)
{
    $slug = trim((string) $slug);

    if ($slug === '') {
        return '';
    }

    return trim(str_replace('-', ' ', $slug));
}

function matrix_maybe_redirect_legacy_search_url()
{
    if (! isset($_GET['s'])) {
        return;
    }

    $query = trim((string) (function_exists('wp_unslash') ? wp_unslash($_GET['s']) : $_GET['s']));

    if ($query === '') {
        return;
    }

    $matrix_search = function_exists('get_query_var') ? (string) get_query_var('matrix_search') : '';

    if ($matrix_search !== '' && $matrix_search !== '_landing') {
        return;
    }

    $request_uri = (string) ($_SERVER['REQUEST_URI'] ?? '');

    if (preg_match('#/search/[^/?]+/?$#', $request_uri) === 1) {
        return;
    }

    $state = matrix_resolve_search_results_state([
        's' => $query,
        'search_type' => $_GET['search_type'] ?? 'all',
        'search_sort' => $_GET['search_sort'] ?? 'relevance',
        'paged' => $_GET['paged'] ?? 1,
    ]);

    $target = matrix_build_search_results_page_url('', $state, (int) ($state['paged'] ?? 1));

    if (function_exists('wp_safe_redirect')) {
        wp_safe_redirect($target, 301);
        exit;
    }
}

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
    $matrix_search = (string) ($request['matrix_search'] ?? '');

    if ($matrix_search === '_landing') {
        $query = '';
    } elseif ($matrix_search !== '') {
        $query = matrix_search_slug_to_query($matrix_search);
    } else {
        $query = trim((string) ($request['s'] ?? ''));
    }

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
    $query = trim((string) ($state['query'] ?? ''));
    $page = max(1, (int) $page);
    $type = matrix_normalize_search_results_type($state['type'] ?? 'all');
    $sort = matrix_normalize_search_results_sort($state['sort'] ?? 'relevance');

    if ($query === '') {
        $url = matrix_get_search_results_base_url();
    } else {
        $slug = matrix_search_query_to_slug($query);

        if ($slug === '') {
            $url = matrix_get_search_results_base_url();
        } elseif ($page > 1) {
            $url = home_url('/search/' . $slug . '/page/' . $page . '/');
        } else {
            $url = home_url('/search/' . $slug . '/');
        }
    }

    $params = [];

    if ($type !== 'all') {
        $params['search_type'] = $type;
    }

    if ($sort !== 'relevance') {
        $params['search_sort'] = $sort;
    }

    if ($params === []) {
        return $url;
    }

    return matrix_search_results_add_query_args($params, $url);
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

function matrix_get_search_results_type_badge_colors($type_key)
{
    $type_key = matrix_search_results_sanitize_key((string) $type_key);
    $background = '#FADBD8';

    if ($type_key === 'page') {
        $background = '#C6ECF4';
    } elseif ($type_key === 'webinars') {
        $background = '#C3DBAE';
    }

    return [
        'background' => $background,
        'text' => '#08284B',
    ];
}

function matrix_get_search_results_cards_layout_class_names()
{
    return 'grid grid-cols-1 gap-4 mob:grid-cols-2 mob:gap-6 lg:flex lg:flex-col lg:gap-4';
}

function matrix_get_search_results_card_class_names()
{
    return 'flex h-full flex-col gap-4 rounded-[8px] bg-[#FBFAF7] p-6 shadow-sm lg:flex-row lg:items-start lg:gap-6';
}

function matrix_get_search_results_card_image_class_names()
{
    return 'block h-[186px] w-full overflow-hidden rounded-[6px] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79] lg:h-[186px] lg:w-[280px] lg:shrink-0';
}

function matrix_get_search_results_base_url()
{
    if (function_exists('home_url')) {
        return home_url('/search/');
    }

    return '/search/';
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

function matrix_normalize_search_results_attachment_id($value)
{
    if (is_numeric($value)) {
        return max(0, (int) $value);
    }

    if (! is_array($value)) {
        return 0;
    }

    return max(0, (int) ($value['ID'] ?? $value['id'] ?? 0));
}

function matrix_extract_search_results_hero_image_from_rows(array $rows)
{
    foreach ($rows as $row) {
        if (! is_array($row)) {
            continue;
        }

        foreach (['hero_image', 'background_image'] as $field_key) {
            if (empty($row[$field_key])) {
                continue;
            }

            $image_id = matrix_normalize_search_results_attachment_id($row[$field_key]);

            if ($image_id > 0) {
                return $image_id;
            }
        }

        if (! empty($row['slides']) && is_array($row['slides'])) {
            foreach ($row['slides'] as $slide) {
                if (! is_array($slide)) {
                    continue;
                }

                $image_id = matrix_normalize_search_results_attachment_id($slide['hero_image'] ?? 0);

                if ($image_id > 0) {
                    return $image_id;
                }
            }
        }
    }

    return 0;
}

function matrix_get_search_results_page_hero_image_id($post_id)
{
    $post_id = (int) $post_id;

    if ($post_id <= 0 || ! function_exists('get_field')) {
        return 0;
    }

    foreach (['flexible_content_blocks', 'hero_content_blocks'] as $field_name) {
        $rows = get_field($field_name, $post_id);

        if (! is_array($rows) || $rows === []) {
            continue;
        }

        $image_id = matrix_extract_search_results_hero_image_from_rows($rows);

        if ($image_id > 0) {
            return $image_id;
        }
    }

    return 0;
}

function matrix_get_search_results_default_image_id()
{
    if (! function_exists('get_field')) {
        return 0;
    }

    $blog_settings = get_field('blog_settings', 'option') ?: [];
    $hero_bg = ! empty($blog_settings['hero_background_image']) && is_array($blog_settings['hero_background_image'])
        ? $blog_settings['hero_background_image']
        : null;

    if ($hero_bg === null) {
        return 0;
    }

    return matrix_normalize_search_results_attachment_id($hero_bg);
}

function matrix_resolve_search_results_image_id($post_id, $post_type)
{
    $post_id = (int) $post_id;
    $image_id = $post_id > 0 && function_exists('get_post_thumbnail_id')
        ? (int) get_post_thumbnail_id($post_id)
        : 0;

    if ($image_id > 0) {
        return $image_id;
    }

    if ($post_type === 'page') {
        $image_id = matrix_get_search_results_page_hero_image_id($post_id);

        if ($image_id > 0) {
            return $image_id;
        }
    }

    return matrix_get_search_results_default_image_id();
}

function matrix_strip_search_results_text($value)
{
    $value = trim((string) $value);

    if ($value === '') {
        return '';
    }

    return trim(function_exists('wp_strip_all_tags') ? wp_strip_all_tags($value) : strip_tags($value));
}

function matrix_trim_search_results_excerpt($text, $word_limit = 24)
{
    $text = matrix_strip_search_results_text($text);

    if ($text === '') {
        return '';
    }

    if (function_exists('wp_trim_words')) {
        return wp_trim_words($text, $word_limit, '...');
    }

    $words = preg_split('/\s+/', $text) ?: [];

    if (count($words) <= $word_limit) {
        return implode(' ', $words);
    }

    return implode(' ', array_slice($words, 0, $word_limit)) . '...';
}

function matrix_get_search_results_flexi_text_field_keys()
{
    return [
        'content',
        'text_content',
        'content_text',
        'intro_text',
        'intro',
        'description',
        'body',
        'item_text',
        'summary',
        'quote',
        'left_description',
        'right_description',
        'caption',
        'service_description',
        'profile_teaser',
        'event_cta_summary',
    ];
}

function matrix_extract_search_results_text_from_row(array $row)
{
    foreach (matrix_get_search_results_flexi_text_field_keys() as $field_key) {
        if (empty($row[$field_key])) {
            continue;
        }

        $text = matrix_strip_search_results_text($row[$field_key]);

        if ($text !== '') {
            return $text;
        }
    }

    foreach ($row as $value) {
        if (! is_array($value)) {
            continue;
        }

        if (array_is_list($value)) {
            foreach ($value as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $text = matrix_extract_search_results_text_from_row($item);

                if ($text !== '') {
                    return $text;
                }
            }
        }
    }

    return '';
}

function matrix_get_search_results_page_content_text($post_id)
{
    $post_id = (int) $post_id;

    if ($post_id <= 0 || ! function_exists('get_field')) {
        return '';
    }

    foreach (['flexible_content_blocks', 'hero_content_blocks'] as $field_name) {
        $rows = get_field($field_name, $post_id);

        if (! is_array($rows) || $rows === []) {
            continue;
        }

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $text = matrix_extract_search_results_text_from_row($row);

            if ($text !== '') {
                return $text;
            }
        }
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
    $content = matrix_strip_search_results_text($content);

    if ($content !== '') {
        return matrix_trim_search_results_excerpt($content);
    }

    $flexi_text = matrix_get_search_results_page_content_text((int) ($post->ID ?? 0));

    if ($flexi_text !== '') {
        return matrix_trim_search_results_excerpt($flexi_text);
    }

    return '';
}

function matrix_prepare_search_result_item($post)
{
    if (! is_object($post) || empty($post->ID)) {
        return [];
    }

    $title = function_exists('get_the_title') ? trim((string) get_the_title($post)) : '';
    $type_key = function_exists('get_post_type') ? (string) get_post_type($post) : '';
    $image_id = matrix_resolve_search_results_image_id((int) $post->ID, $type_key);

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
