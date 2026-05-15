<?php

$defaults = matrix_get_blog_filter_archive_defaults();

$section_id = 'blog-filter-archive-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());
$heading = trim((string) get_sub_field('heading')) ?: $defaults['heading'];
$heading_tag = (string) get_sub_field('heading_tag');
$filter_label = trim((string) get_sub_field('filter_label')) ?: $defaults['filter_label'];
$search_placeholder = trim((string) get_sub_field('search_placeholder')) ?: $defaults['search_placeholder'];
$search_button_label = trim((string) get_sub_field('search_button_label')) ?: $defaults['search_button_label'];
$posts_per_page = (int) get_sub_field('posts_per_page');
$allowed_category_ids = array_values(array_filter(array_map('intval', (array) get_sub_field('allowed_categories'))));
$empty_state_message = trim((string) get_sub_field('empty_state_message')) ?: $defaults['empty_state_message'];
$background_color = (string) get_sub_field('background_color') ?: '#FFFFFF';
$filter_label_color = (string) get_sub_field('filter_label_color') ?: '#08284B';
$chip_text_color = (string) get_sub_field('chip_text_color') ?: '#08284B';
$chip_border_color = (string) get_sub_field('chip_border_color') ?: '#08284B';
$active_chip_background_color = (string) get_sub_field('active_chip_background_color') ?: '#80CCD9';
$active_chip_text_color = (string) get_sub_field('active_chip_text_color') ?: '#08284B';
$search_input_text_color = (string) get_sub_field('search_input_text_color') ?: '#08284B';
$search_input_border_color = (string) get_sub_field('search_input_border_color') ?: '#E2E8F0';
$search_button_background_color = (string) get_sub_field('search_button_background_color') ?: '#08284B';
$search_button_text_color = (string) get_sub_field('search_button_text_color') ?: '#FFFFFF';
$card_background_color = (string) get_sub_field('card_background_color') ?: '#F1F8F9';
$card_title_color = (string) get_sub_field('card_title_color') ?: '#1E244B';
$card_meta_color = (string) get_sub_field('card_meta_color') ?: '#1E244B';
$card_excerpt_color = (string) get_sub_field('card_excerpt_color') ?: '#1E244B';

if (! in_array($heading_tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p'], true)) {
    $heading_tag = 'h2';
}

if ($posts_per_page < 1) {
    $posts_per_page = (int) $defaults['posts_per_page'];
}

$wrapper_classes = ['flex', 'w-full', 'max-w-[1018px]', 'flex-col', 'items-center', 'mx-auto', 'pt-5', 'pb-5', 'max-xl:px-5'];
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

$terms_args = [
    'taxonomy' => 'category',
    'hide_empty' => true,
];

if ($allowed_category_ids !== []) {
    $terms_args['include'] = $allowed_category_ids;
}

$allowed_terms = get_terms($terms_args);
if (is_wp_error($allowed_terms) || ! is_array($allowed_terms)) {
    $allowed_terms = [];
}

$chips = [
    [
        'slug' => 'all',
        'label' => 'All',
    ],
];

$slug_to_id_map = [];
foreach ($allowed_terms as $term) {
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

$state = matrix_resolve_blog_filter_archive_state($_GET, array_keys($slug_to_id_map), $posts_per_page);
if ($state['category'] !== 'all' && ! isset($slug_to_id_map[$state['category']])) {
    $state['category'] = 'all';
}

$query = new WP_Query(matrix_build_blog_filter_archive_query_args($state, $slug_to_id_map));
$current_page_id = get_queried_object_id();
$base_url = $current_page_id ? get_permalink($current_page_id) : '';

if (! is_string($base_url) || $base_url === '') {
    $base_url = home_url('/');
}

$blog_filter_archive = [
    'section_id' => $section_id,
    'data_block' => str_replace('_', '-', get_row_layout()) . '-' . get_row_index(),
    'heading_tag' => $heading_tag,
    'heading' => $heading,
    'filter_label' => $filter_label,
    'search_placeholder' => $search_placeholder,
    'search_button_label' => $search_button_label,
    'empty_state_message' => $empty_state_message,
    'base_url' => $base_url,
    'state' => $state,
    'chips' => $chips,
    'query' => $query,
    'pagination' => [
        'current' => max(1, (int) $query->get('paged')),
        'total' => max(1, (int) $query->max_num_pages),
    ],
    'colors' => [
        'background' => $background_color,
        'filter_label' => $filter_label_color,
        'chip_text' => $chip_text_color,
        'chip_border' => $chip_border_color,
        'active_chip_background' => $active_chip_background_color,
        'active_chip_text' => $active_chip_text_color,
        'search_input_text' => $search_input_text_color,
        'search_input_border' => $search_input_border_color,
        'search_button_background' => $search_button_background_color,
        'search_button_text' => $search_button_text_color,
        'card_background' => $card_background_color,
        'card_title' => $card_title_color,
        'card_meta' => $card_meta_color,
        'card_excerpt' => $card_excerpt_color,
    ],
    'section_classes' => 'relative flex overflow-hidden',
    'section_style' => 'background-color: ' . $background_color . ';',
    'wrapper_classes' => implode(' ', array_unique($wrapper_classes)),
];

get_template_part('template-parts/blog/filter_archive', null, [
    'blog_filter_archive' => $blog_filter_archive,
]);
