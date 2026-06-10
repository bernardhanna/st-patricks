<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$defaults = matrix_get_careers_archive_defaults();

$careers_archive = new FieldsBuilder('careers_archive', [
    'label' => 'Careers Archive',
]);

$careers_archive
    ->addTab('Content', ['label' => 'Content'])
        ->addText('heading', [
            'label' => 'Heading',
            'default_value' => $defaults['heading'],
        ])
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
        ->addText('filter_label', [
            'label' => 'Filter Label',
            'default_value' => $defaults['filter_label'],
        ])
        ->addText('department_placeholder', [
            'label' => 'Department Placeholder',
            'default_value' => $defaults['department_placeholder'],
        ])
        ->addText('location_placeholder', [
            'label' => 'Location Placeholder',
            'default_value' => $defaults['location_placeholder'],
        ])
        ->addText('apply_filters_label', [
            'label' => 'Apply Filters Label',
            'default_value' => $defaults['apply_filters_label'],
        ])
        ->addText('search_placeholder', [
            'label' => 'Search Placeholder',
            'default_value' => $defaults['search_placeholder'],
        ])
        ->addText('search_button_label', [
            'label' => 'Search Button Label',
            'default_value' => $defaults['search_button_label'],
        ])
        ->addText('view_detail_label', [
            'label' => 'View Detail Label',
            'default_value' => $defaults['view_detail_label'],
        ])
        ->addNumber('posts_per_page', [
            'label' => 'Posts Per Page',
            'default_value' => $defaults['posts_per_page'],
            'min' => 1,
            'max' => 50,
            'step' => 1,
        ])
        ->addTaxonomy('allowed_departments', [
            'label' => 'Allowed Departments',
            'instructions' => 'Optional. Leave empty to show all departments.',
            'taxonomy' => 'career_department',
            'field_type' => 'multi_select',
            'return_format' => 'id',
            'allow_null' => 1,
            'multiple' => 1,
        ])
        ->addTaxonomy('allowed_locations', [
            'label' => 'Allowed Locations',
            'instructions' => 'Optional. Leave empty to show all locations.',
            'taxonomy' => 'career_location',
            'field_type' => 'multi_select',
            'return_format' => 'id',
            'allow_null' => 1,
            'multiple' => 1,
        ])
        ->addText('empty_state_message', [
            'label' => 'Empty State Message',
            'default_value' => $defaults['empty_state_message'],
        ])

    ->addTab('Layout', ['label' => 'Layout'])
        ->addRepeater('padding_settings', [
            'label' => 'Padding Settings',
            'button_label' => 'Add Screen Size Padding',
            'layout' => 'table',
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

return $careers_archive;
