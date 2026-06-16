<?php

$queried_object = get_queried_object();
$is_programmes_landing_page = $queried_object instanceof WP_Post
    && $queried_object->post_type === 'page'
    && in_array($queried_object->post_name, ['programmes-therapies', 'day-programmes'], true);
$section_id = $is_programmes_landing_page
    ? 'select-programme-or-therapy'
    : 'programmes-therapies-archive-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());
$heading = get_sub_field('heading');
$heading_tag = get_sub_field('heading_tag');
$posts_per_page = get_sub_field('posts_per_page');
$empty_state_message = get_sub_field('empty_state_message');

$wrapper_classes = ['flex', 'w-full', 'max-w-[1018px]', 'flex-col', 'items-center', 'mx-auto', 'pt-5', 'pb-5', 'max-xl:px-5'];


$current_page_id = get_queried_object_id();
$base_url = $current_page_id ? get_permalink($current_page_id) : '';
$programmes_therapies_archive = matrix_prepare_programmes_therapies_archive([
    'section_id' => $section_id,
    'data_block' => str_replace('_', '-', get_row_layout()) . '-' . get_row_index(),
    'heading' => $heading,
    'heading_tag' => $heading_tag,
    'posts_per_page' => $posts_per_page,
    'empty_state_message' => $empty_state_message,
    'request_state' => $_GET,
    'base_url' => $base_url,
    'wrapper_classes' => implode(' ', array_unique($wrapper_classes)),
]);

get_template_part('template-parts/programmes-therapies/archive', null, [
    'programmes_therapies_archive' => $programmes_therapies_archive,
]);
