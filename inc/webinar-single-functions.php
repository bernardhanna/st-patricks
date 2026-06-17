<?php

function matrix_get_webinar_single_defaults()
{
    return [
        'back_label' => 'Back to webinars & events',
        'date_label' => 'Date:',
        'time_label' => 'Time:',
        'related_count' => 3,
        'webinar_type_taxonomy' => 'webinar_type',
    ];
}

function matrix_is_webinar_post($post_id = null)
{
    $post_id = (int) ($post_id ?: (function_exists('get_the_ID') ? get_the_ID() : 0));

    return $post_id > 0 && function_exists('get_post_type') && get_post_type($post_id) === 'webinars';
}

function matrix_uses_event_style_single_layout($post_id = null)
{
    $post_id = (int) ($post_id ?: (function_exists('get_the_ID') ? get_the_ID() : 0));

    if ($post_id < 1 || ! function_exists('get_post_type')) {
        return false;
    }

    $post_type = get_post_type($post_id);

    if ($post_type === 'webinars') {
        return true;
    }

    if ($post_type === 'post') {
        return matrix_is_event_post($post_id);
    }

    return false;
}

function matrix_get_webinars_archive_url()
{
    if (function_exists('get_post_type_archive_link')) {
        $url = get_post_type_archive_link('webinars');

        if (is_string($url) && $url !== '') {
            return $url;
        }
    }

    return function_exists('home_url') ? home_url('/webinars/') : '/webinars/';
}

function matrix_format_webinar_date_label($post_id = null)
{
    $post_id = (int) ($post_id ?: (function_exists('get_the_ID') ? get_the_ID() : 0));

    if ($post_id < 1) {
        return '';
    }

    $raw_date = function_exists('get_field') ? trim((string) get_field('webinar_date', $post_id)) : '';

    if ($raw_date === '') {
        return function_exists('matrix_format_blog_post_date')
            ? matrix_format_blog_post_date($post_id)
            : '';
    }

    if (preg_match('/^\d{8}$/', $raw_date) === 1) {
        $date = DateTime::createFromFormat('Ymd', $raw_date);

        if ($date instanceof DateTime) {
            return $date->format(matrix_get_post_date_display_format());
        }
    }

    $timestamp = strtotime($raw_date);

    if ($timestamp !== false) {
        return date_i18n(matrix_get_post_date_display_format(), $timestamp);
    }

    return '';
}

function matrix_format_webinar_time_label($post_id = null)
{
    $post_id = (int) ($post_id ?: (function_exists('get_the_ID') ? get_the_ID() : 0));

    if ($post_id < 1) {
        return '';
    }

    $raw_time = function_exists('get_field') ? trim((string) get_field('webinar_time', $post_id)) : '';

    if ($raw_time === '') {
        return '';
    }

    $timestamp = strtotime($raw_time);

    if ($timestamp !== false) {
        return strtolower(date_i18n('ga', $timestamp));
    }

    return '';
}

function matrix_get_webinar_single_placeholder_figma_key()
{
    return 'webinar-single-placeholder-3279-17604';
}

function matrix_should_force_webinar_single_placeholder($post_id = null)
{
    return matrix_is_webinar_post($post_id);
}

function matrix_get_webinar_single_placeholder_image_id()
{
    static $attachment_id = null;

    if ($attachment_id !== null) {
        return $attachment_id;
    }

    $attachment_id = 0;

    if (! function_exists('get_posts')) {
        return $attachment_id;
    }

    $existing = get_posts([
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => 1,
        'meta_query' => [
            [
                'key' => '_matrix_seed_figma_key',
                'value' => matrix_get_webinar_single_placeholder_figma_key(),
            ],
        ],
    ]);

    if ($existing !== [] && $existing[0] instanceof WP_Post) {
        $attachment_id = (int) $existing[0]->ID;
    }

    return $attachment_id;
}

function matrix_get_webinar_single_placeholder_image_url()
{
    $attachment_id = matrix_get_webinar_single_placeholder_image_id();

    if ($attachment_id > 0 && function_exists('wp_get_attachment_image_url')) {
        $url = wp_get_attachment_image_url($attachment_id, 'large');

        if (is_string($url) && $url !== '') {
            return $url;
        }
    }

    if (function_exists('get_template_directory_uri')) {
        return get_template_directory_uri() . '/assets/svg/st-patricks-logo-symbol.svg';
    }

    return '';
}

function matrix_get_webinar_single_placeholder_class_names($context = 'featured')
{
    if ($context === 'related') {
        return 'flex h-[186px] w-full items-center justify-center rounded-[4px] bg-[#E7EEF0] px-4';
    }

    return 'flex h-[240px] w-full items-center justify-center bg-[#E7EEF0] px-6 lg:h-[387px]';
}

function matrix_get_webinar_single_placeholder_logo_class_names($context = 'featured')
{
    if ($context === 'related') {
        return 'h-16 w-16 opacity-25';
    }

    return 'h-24 w-24 opacity-25 lg:h-32 lg:w-32';
}

function matrix_get_webinar_post_intro($post_id = null)
{
    $post_id = (int) ($post_id ?: (function_exists('get_the_ID') ? get_the_ID() : 0));

    if ($post_id < 1) {
        return '';
    }

    $summary = function_exists('get_field') ? trim(wp_strip_all_tags((string) get_field('webinar_summary', $post_id))) : '';

    if ($summary !== '') {
        return $summary;
    }

    return matrix_get_blog_post_intro($post_id);
}

function matrix_get_webinar_adjacent_post_link($direction = 'next', $post_id = null)
{
    $post_id = (int) ($post_id ?: (function_exists('get_the_ID') ? get_the_ID() : 0));

    if ($post_id < 1 || ! function_exists('get_post')) {
        return null;
    }

    $post = get_post($post_id);

    if (! $post instanceof WP_Post || $post->post_type !== 'webinars') {
        return null;
    }

    $taxonomy = matrix_get_webinar_single_defaults()['webinar_type_taxonomy'] ?? 'webinar_type';
    $adjacent = $direction === 'previous'
        ? get_previous_post(true, '', $taxonomy)
        : get_next_post(true, '', $taxonomy);

    if (! $adjacent instanceof WP_Post) {
        return null;
    }

    return [
        'id' => (int) $adjacent->ID,
        'title' => get_the_title($adjacent),
        'permalink' => get_permalink($adjacent),
    ];
}

function matrix_get_webinar_related_posts($post_id = null, $count = 3)
{
    $post_id = (int) ($post_id ?: (function_exists('get_the_ID') ? get_the_ID() : 0));
    $count = max(1, (int) $count);
    $taxonomy = matrix_get_webinar_single_defaults()['webinar_type_taxonomy'] ?? 'webinar_type';
    $terms = wp_get_post_terms($post_id, $taxonomy, ['fields' => 'ids']);

    $args = [
        'post_type' => 'webinars',
        'post_status' => 'publish',
        'posts_per_page' => $count,
        'post__not_in' => [$post_id],
        'orderby' => 'date',
        'order' => 'DESC',
    ];

    if (! empty($terms) && ! is_wp_error($terms)) {
        $args['tax_query'] = [[
            'taxonomy' => $taxonomy,
            'field' => 'term_id',
            'terms' => $terms,
        ]];
    }

    $query = new WP_Query($args);

    if (! $query->have_posts()) {
        $query = new WP_Query([
            'post_type' => 'webinars',
            'post_status' => 'publish',
            'posts_per_page' => $count,
            'post__not_in' => [$post_id],
            'orderby' => 'date',
            'order' => 'DESC',
        ]);
    }

    return $query;
}

function matrix_map_webinar_related_post_card($post_id)
{
    $post_id = (int) $post_id;
    $terms = get_the_terms($post_id, matrix_get_webinar_single_defaults()['webinar_type_taxonomy'] ?? 'webinar_type');
    $display_term = (is_array($terms) && count($terms) === 1 && $terms[0] instanceof WP_Term) ? $terms[0] : null;
    $thumbnail_id = get_post_thumbnail_id($post_id);
    $permalink = get_permalink($post_id);
    $date_label = matrix_format_webinar_date_label($post_id);
    $time_label = matrix_format_webinar_time_label($post_id);
    $date_display = $date_label !== '' ? 'Date: ' . $date_label : '';

    if ($time_label !== '') {
        $date_display = trim($date_display . ($date_display !== '' ? ' ' : '') . 'Time: ' . $time_label);
    }

    $type_slug = $display_term instanceof WP_Term ? $display_term->slug : '';
    $theme = function_exists('matrix_get_webinars_archive_card_theme')
        ? matrix_get_webinars_archive_card_theme($type_slug)
        : ['badge_background' => '#F9E5F2', 'card_background' => '#FBFAF7'];

    return [
        'id' => $post_id,
        'title' => get_the_title($post_id),
        'permalink' => $permalink,
        'thumbnail_href' => $permalink,
        'thumbnail_target' => '_self',
        'thumbnail_rel' => '',
        'date_label' => $date_display,
        'category_name' => $display_term instanceof WP_Term ? $display_term->name : '',
        'category_slug' => $type_slug,
        'badge_background' => (string) ($theme['badge_background'] ?? '#F9E5F2'),
        'use_webinar_placeholder' => true,
        'image_id' => (int) $thumbnail_id,
        'image_url' => $thumbnail_id ? (string) wp_get_attachment_image_url($thumbnail_id, 'medium_large') : '',
        'image_alt' => $thumbnail_id ? trim((string) get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true)) : '',
    ];
}

function matrix_get_event_style_adjacent_post_link($direction = 'next', $post_id = null)
{
    $post_id = (int) ($post_id ?: (function_exists('get_the_ID') ? get_the_ID() : 0));

    if ($post_id < 1) {
        return null;
    }

    if (matrix_is_webinar_post($post_id)) {
        return matrix_get_webinar_adjacent_post_link($direction, $post_id);
    }

    return matrix_get_blog_adjacent_post_link($direction, $post_id);
}
