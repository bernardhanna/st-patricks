<?php



$padding_settings = get_sub_field('padding_settings');
$padding_classes = function_exists('matrix_flexi_resolve_vertical_padding_classes')
    ? matrix_flexi_resolve_vertical_padding_classes(
        get_sub_field('vertical_padding'),
        is_array($padding_settings) ? $padding_settings : [],
        'py-12 lg:py-[100px]'
    )
    : 'py-12 lg:py-[100px]';
$wrapper_classes = trim(implode(' ', array_unique(array_merge(
    ['mx-auto', 'flex', 'w-full', 'max-w-[1018px]', 'flex-col', 'gap-8', 'px-4', 'lg:gap-8', 'xl:px-0'],
    preg_split('/\s+/', $padding_classes, -1, PREG_SPLIT_NO_EMPTY) ?: []
))));

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
        'cards' => matrix_exclude_page_hero_from_related_cards(
            matrix_normalize_related_cards(get_sub_field('cards'))
        ),
    ],
]);
