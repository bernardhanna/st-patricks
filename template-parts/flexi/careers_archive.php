<?php

$queried_object = get_queried_object();
$is_careers_landing_page = $queried_object instanceof WP_Post
    && $queried_object->post_type === 'page'
    && $queried_object->post_name === 'careers';
$section_id = $is_careers_landing_page
    ? 'current-vacancies'
    : 'careers-archive-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());
$current_page_id = get_queried_object_id();
$base_url = $current_page_id ? get_permalink($current_page_id) : '';

$wrapper_classes = explode(' ', matrix_get_careers_archive_default_wrapper_classes());
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size = get_sub_field('screen_size');
        $padding_top = get_sub_field('padding_top');
        $padding_bottom = get_sub_field('padding_bottom');

        if ($screen_size !== '' && $padding_top !== '' && $padding_top !== null) {
            $wrapper_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
        }

        if ($screen_size !== '' && $padding_bottom !== '' && $padding_bottom !== null) {
            $wrapper_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
        }
    }
}

$careers_archive = matrix_prepare_careers_archive([
    'section_id' => $section_id,
    'data_block' => str_replace('_', '-', get_row_layout()) . '-' . get_row_index(),
    'heading' => get_sub_field('heading'),
    'heading_tag' => get_sub_field('heading_tag'),
    'filter_label' => get_sub_field('filter_label'),
    'department_placeholder' => get_sub_field('department_placeholder'),
    'location_placeholder' => get_sub_field('location_placeholder'),
    'apply_filters_label' => get_sub_field('apply_filters_label'),
    'search_placeholder' => get_sub_field('search_placeholder'),
    'search_button_label' => get_sub_field('search_button_label'),
    'view_detail_label' => get_sub_field('view_detail_label'),
    'posts_per_page' => get_sub_field('posts_per_page'),
    'allowed_departments' => get_sub_field('allowed_departments'),
    'allowed_locations' => get_sub_field('allowed_locations'),
    'empty_state_message' => get_sub_field('empty_state_message'),
    'request_state' => $_GET,
    'base_url' => $base_url,
    'wrapper_classes' => implode(' ', array_unique($wrapper_classes)),
]);

get_template_part('template-parts/careers/archive', null, [
    'careers_archive' => $careers_archive,
]);
