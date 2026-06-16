<?php



$wrapper_classes = trim(implode(' ', array_unique(
    ['mx-auto', 'flex', 'w-full', 'max-w-[1018px]', 'flex-col', 'gap-8', 'px-4', 'py-12', 'lg:gap-8', 'lg:py-[100px]', 'xl:px-0']
)));

get_template_part('template-parts/partials/related-cards-section', null, [
    'section' => [
        'section_id' => 'related-cards-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid()),
        'data_block' => str_replace('_', '-', get_row_layout()) . '-' . get_row_index(),
        'heading' => (string) get_sub_field('heading'),
        'heading_tag' => (string) get_sub_field('heading_tag'),
        'intro_text' => (string) get_sub_field('intro_text'),
        'background_color' => (string) (get_sub_field('background_color') ?: '#FFFFFF'),
        'columns' => (string) (get_sub_field('columns') ?: '3'),
        'wrapper_classes' => $wrapper_classes,
        'cards' => matrix_normalize_related_cards(get_sub_field('cards')),
    ],
]);
