<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$defaults = matrix_get_research_project_archive_defaults();

$research_project_archive = new FieldsBuilder('research_project_archive', [
    'label' => 'Research Project Archive',
]);

$research_project_archive
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
            'label' => 'Category Filter Label',
            'default_value' => $defaults['filter_label'],
        ])
        ->addText('researcher_filter_label', [
            'label' => 'Researcher Filter Label',
            'default_value' => $defaults['researcher_filter_label'],
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
            'instructions' => 'Optional. Leave empty to show all research project categories.',
            'taxonomy' => 'research_project_category',
            'field_type' => 'multi_select',
            'return_format' => 'id',
            'allow_null' => 1,
            'multiple' => 1,
        ])
        ->addTaxonomy('allowed_researchers', [
            'label' => 'Allowed Researchers',
            'instructions' => 'Optional. Leave empty to show all researchers.',
            'taxonomy' => 'researcher',
            'field_type' => 'multi_select',
            'return_format' => 'id',
            'allow_null' => 1,
            'multiple' => 1,
        ])
        ->addTaxonomy('default_category', [
            'label' => 'Default Category',
            'instructions' => 'Optional. Pre-select a category such as Current or Past.',
            'taxonomy' => 'research_project_category',
            'field_type' => 'select',
            'return_format' => 'slug',
            'allow_null' => 1,
            'multiple' => 0,
        ])
        ->addTrueFalse('lock_category', [
            'label' => 'Lock Category Filter',
            'instructions' => 'Hide category chips and keep the default category fixed.',
            'ui' => 1,
            'default_value' => 0,
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
        ])

    ->addTab('Layout', ['label' => 'Layout'])
        ->addRepeater('padding_settings', [
            'label' => 'Padding Settings',
            'instructions' => 'Customize padding for different screen sizes.',
            'button_label' => 'Add Screen Size Padding',
        ])
            ->addSelect('screen_size', [
                'label' => 'Screen Size',
                'choices' => [
                    'xxs' => 'xxs',
                    'xs' => 'xs',
                    'mob' => 'mob',
                    'sm' => 'sm',
                    'md' => 'md',
                    'lg' => 'lg',
                    'xl' => 'xl',
                    'xxl' => 'xxl',
                    'ultrawide' => 'ultrawide',
                ],
            ])
            ->addNumber('padding_top', [
                'label' => 'Padding Top',
                'min' => 0,
                'max' => 20,
                'step' => 0.01,
                'append' => 'rem',
            ])
            ->addNumber('padding_bottom', [
                'label' => 'Padding Bottom',
                'min' => 0,
                'max' => 20,
                'step' => 0.01,
                'append' => 'rem',
            ])
        ->endRepeater();

return $research_project_archive;
