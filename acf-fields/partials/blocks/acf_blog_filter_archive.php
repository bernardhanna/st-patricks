<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$defaults = matrix_get_blog_filter_archive_defaults();

$blog_filter_archive = new FieldsBuilder('blog_filter_archive', [
    'label' => 'Blog Filter Archive',
]);

$blog_filter_archive
    ->addTab('Content', ['label' => 'Content'])
        ->addSelect('heading_tag', [
            'label' => 'Heading Tag',
            'choices' => [
                'h1' => 'H1',
                'h2' => 'H2',
                'h3' => 'H3',
                'h4' => 'H4',
                'h5' => 'H5',
                'h6' => 'H6',
                'span' => 'Span',
                'p' => 'Paragraph',
            ],
            'default_value' => 'h2',
        ])
        ->addText('heading', [
            'label' => 'Heading',
            'default_value' => $defaults['heading'],
        ])
        ->addText('filter_label', [
            'label' => 'Filter Label',
            'default_value' => $defaults['filter_label'],
        ])
        ->addText('search_placeholder', [
            'label' => 'Search Placeholder',
            'default_value' => $defaults['search_placeholder'],
        ])
        ->addText('search_button_label', [
            'label' => 'Search Button Label',
            'default_value' => $defaults['search_button_label'],
        ])
        ->addNumber('posts_per_page', [
            'label' => 'Posts Per Page',
            'default_value' => $defaults['posts_per_page'],
            'min' => 1,
            'max' => 24,
            'step' => 1,
        ])
        ->addTaxonomy('allowed_categories', [
            'label' => 'Allowed Categories',
            'instructions' => 'Optional. Leave empty to show all post categories.',
            'taxonomy' => 'category',
            'field_type' => 'multi_select',
            'return_format' => 'id',
            'allow_null' => 1,
            'multiple' => 1,
        ])
        ->addText('empty_state_message', [
            'label' => 'Empty State Message',
            'default_value' => $defaults['empty_state_message'],
        ])

    ->addTab('Design', ['label' => 'Design'])
        ->addColorPicker('background_color', [
            'label' => 'Background Color',
            'default_value' => '#FFFFFF',
        ])
        ->addColorPicker('filter_label_color', [
            'label' => 'Filter Label Color',
            'default_value' => '#08284B',
        ])
        ->addColorPicker('chip_text_color', [
            'label' => 'Chip Text Color',
            'default_value' => '#08284B',
        ])
        ->addColorPicker('chip_border_color', [
            'label' => 'Chip Border Color',
            'default_value' => '#08284B',
        ])
        ->addColorPicker('active_chip_background_color', [
            'label' => 'Active Chip Background Color',
            'default_value' => '#80CCD9',
        ])
        ->addColorPicker('active_chip_text_color', [
            'label' => 'Active Chip Text Color',
            'default_value' => '#08284B',
        ])
        ->addColorPicker('search_input_text_color', [
            'label' => 'Search Input Text Color',
            'default_value' => '#08284B',
        ])
        ->addColorPicker('search_input_border_color', [
            'label' => 'Search Input Border Color',
            'default_value' => '#E2E8F0',
        ])
        ->addColorPicker('search_button_background_color', [
            'label' => 'Search Button Background Color',
            'default_value' => '#08284B',
        ])
        ->addColorPicker('search_button_text_color', [
            'label' => 'Search Button Text Color',
            'default_value' => '#FFFFFF',
        ])
        ->addColorPicker('card_background_color', [
            'label' => 'Card Background Color',
            'default_value' => '#F1F8F9',
        ])
        ->addColorPicker('card_title_color', [
            'label' => 'Card Title Color',
            'default_value' => '#1E244B',
        ])
        ->addColorPicker('card_meta_color', [
            'label' => 'Card Meta Color',
            'default_value' => '#1E244B',
        ])
        ->addColorPicker('card_excerpt_color', [
            'label' => 'Card Excerpt Color',
            'default_value' => '#1E244B',
        ]);

return $blog_filter_archive;
