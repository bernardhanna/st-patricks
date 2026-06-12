<?php

$wrapper_classes = 'mx-auto flex w-full max-w-[1018px] flex-col max-xl:px-5';

get_template_part('template-parts/useful-links/section', null, [
    'useful_links' => [
        'section_id' => 'useful-links-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid()),
        'data_block' => str_replace('_', '-', get_row_layout()) . '-' . get_row_index(),
        'heading' => get_sub_field('heading'),
        'heading_tag' => get_sub_field('heading_tag'),
        'links' => get_sub_field('links'),
        'background_color' => get_sub_field('background_color'),
        'heading_color' => get_sub_field('heading_color'),
        'link_color' => get_sub_field('link_color'),
        'variant' => in_array((string) get_sub_field('variant'), ['flexi', 'search'], true) ? (string) get_sub_field('variant') : 'flexi',
        'wrapper_classes' => $wrapper_classes,
    ],
]);
