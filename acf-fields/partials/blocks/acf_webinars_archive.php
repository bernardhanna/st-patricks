<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$defaults = matrix_get_webinars_archive_defaults();

$webinars_archive = new FieldsBuilder('webinars_archive', [
    'label' => 'Webinars Archive',
]);

$webinars_archive
    ->addTab('Content', ['label' => 'Content'])
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
        ->addTaxonomy('allowed_types', [
            'label' => 'Allowed Webinar Types',
            'instructions' => 'Optional. Leave empty to show all webinar types.',
            'taxonomy' => 'webinar_type',
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
            'instructions' => 'Customize padding for different screen sizes.',
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

return $webinars_archive;
