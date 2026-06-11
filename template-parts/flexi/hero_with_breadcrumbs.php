<?php

$padding_classes = [];

if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size = get_sub_field('screen_size');
        $padding_top = get_sub_field('padding_top');
        $padding_bottom = get_sub_field('padding_bottom');

        if ($screen_size !== '' && $padding_top !== '' && $padding_top !== null) {
            $padding_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
        }

        if ($screen_size !== '' && $padding_bottom !== '' && $padding_bottom !== null) {
            $padding_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
        }
    }
}

matrix_render_hero_with_breadcrumbs([
    'section_id' => 'hero-with-breadcrumbs-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid()),
    'data_matrix_block' => str_replace('_', '-', get_row_layout()) . '-' . get_row_index(),
    'layout_style' => get_sub_field('layout_style'),
    'heading' => get_sub_field('heading'),
    'heading_tag' => get_sub_field('heading_tag'),
    'content' => get_sub_field('content'),
    'primary_button' => get_sub_field('primary_button'),
    'hero_image' => get_sub_field('hero_image'),
    'show_breadcrumbs' => (bool) get_sub_field('show_breadcrumbs'),
    'breadcrumb_source' => get_sub_field('breadcrumb_source'),
    'manual_breadcrumbs' => get_sub_field('manual_breadcrumbs'),
    'current_crumb_label' => get_sub_field('current_crumb_label'),
    'background_color' => get_sub_field('background_color'),
    'breadcrumb_background_color' => get_sub_field('breadcrumb_background_color'),
    'heading_color' => get_sub_field('heading_color'),
    'text_color' => get_sub_field('text_color'),
    'accent_color' => get_sub_field('accent_color'),
    'aside_heading' => get_sub_field('aside_heading'),
    'text_max_width' => get_sub_field('text_max_width'),
    'padding_classes' => $padding_classes,
], get_the_ID());
