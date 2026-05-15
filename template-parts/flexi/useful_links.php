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

$wrapper_classes = trim(implode(' ', array_unique(array_merge(
    ['mx-auto', 'flex', 'w-full', 'max-w-[1018px]', 'flex-col', 'max-xl:px-5', 'pt-5', 'pb-5'],
    $padding_classes
))));

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
