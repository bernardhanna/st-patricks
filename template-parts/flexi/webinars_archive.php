<?php

$section_id = 'webinars-archive-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());
$filter_label = get_sub_field('filter_label');
$search_placeholder = get_sub_field('search_placeholder');
$search_button_label = get_sub_field('search_button_label');
$posts_per_page = get_sub_field('posts_per_page');
$allowed_type_ids = get_sub_field('allowed_types');
$empty_state_message = get_sub_field('empty_state_message');

$wrapper_classes = ['flex', 'w-full', 'max-w-[1018px]', 'flex-col', 'items-center', 'mx-auto', 'pt-5', 'pb-5', 'max-xl:px-5'];


$current_page_id = get_queried_object_id();
$base_url = $current_page_id ? get_permalink($current_page_id) : '';
$webinars_archive = matrix_prepare_webinars_archive([
    'section_id' => $section_id,
    'data_block' => str_replace('_', '-', get_row_layout()) . '-' . get_row_index(),
    'filter_label' => $filter_label,
    'search_placeholder' => $search_placeholder,
    'search_button_label' => $search_button_label,
    'posts_per_page' => $posts_per_page,
    'allowed_type_ids' => $allowed_type_ids,
    'empty_state_message' => $empty_state_message,
    'request_state' => $_GET,
    'base_url' => $base_url,
    'wrapper_classes' => implode(' ', array_unique($wrapper_classes)),
]);

get_template_part('template-parts/webinars/archive', null, [
    'webinars_archive' => $webinars_archive,
]);
