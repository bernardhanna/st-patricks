<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$counters = new FieldsBuilder('counters', [
    'label' => 'Counters Statistics',
]);

$counters
    ->addTab('content_tab', ['label' => 'Content'])

    // Counter Items Repeater
    ->addRepeater('counter_items', [
        'label' => 'Counter Items',
        'instructions' => 'Add animated counter statistics with values, titles, and descriptions.',
        'button_label' => 'Add Counter Item',
        'layout' => 'row',
        'min' => 1,
        'max' => 6,
        'default_value' => [
            [
                'value' => 95,
                'suffix' => '%',
                'title' => 'Key point text',
                'description' => 'Lorem ipsum dolor sit ametsed do eiusmod tempor incididunt'
            ],
            [
                'value' => 75,
                'suffix' => 'k',
                'title' => 'Key point text',
                'description' => 'Lorem ipsum dolor sit ametsed do eiusmod tempor incididunt'
            ],
            [
                'value' => 455,
                'suffix' => '',
                'title' => 'Key point text',
                'description' => 'Lorem ipsum dolor sit ametsed do eiusmod tempor incididunt'
            ]
        ]
    ])
        ->addNumber('value', [
            'label' => 'Counter Value',
            'instructions' => 'The target number to count up to during animation.',
            'default_value' => 95,
            'min' => 0,
            'step' => 1,
            'required' => 1,
        ])
        ->addText('suffix', [
            'label' => 'Suffix',
            'instructions' => 'Text to append after the number (e.g., %, k, +, M).',
            'default_value' => '%',
            'maxlength' => 10,
        ])
        ->addText('title', [
            'label' => 'Title',
            'instructions' => 'The title text displayed below the counter.',
            'default_value' => 'Key point text',
            'required' => 1,
            'maxlength' => 100,
        ])
        ->addTextarea('description', [
            'label' => 'Description',
            'instructions' => 'Short description text below the title.',
            'new_lines' => '',
            'maxlength' => 200,
            'rows' => 3,
            'default_value' => 'Lorem ipsum dolor sit ametsed do eiusmod tempor incididunt',
        ])
    ->endRepeater()

    ->addTab('design_tab', ['label' => 'Design'])

    // Background Color Control
    ->addColorPicker('background_color', [
        'label' => 'Background Color',
        'instructions' => 'Set the background color for the counters section.',
        'default_value' => '#0c4a6e',
    ])

    ->addTab('layout_tab', ['label' => 'Layout'])

    // Padding Settings
    ->addRepeater('padding_settings', [
        'label' => 'Padding Settings',
        'instructions' => 'Customize padding for different screen sizes.',
        'button_label' => 'Add Screen Size Padding',
        'layout' => 'table',
        'default_value' => [
            [
                'screen_size' => 'xxs',
                'padding_top' => 5,
                'padding_bottom' => 5,
            ],
            [
                'screen_size' => 'lg',
                'padding_top' => 8,
                'padding_bottom' => 8,
            ]
        ]
    ])
        ->addSelect('screen_size', [
            'label' => 'Screen Size',
            'instructions' => 'Select the screen size for this padding setting.',
            'choices' => [
                'xxs' => 'XXS (320px+)',
                'xs' => 'XS (480px+)',
                'mob' => 'Mobile (575px+)',
                'sm' => 'Small (640px+)',
                'md' => 'Medium (768px+)',
                'lg' => 'Large (1100px+)',
                'xl' => 'XL (1280px+)',
                'xxl' => 'XXL (1440px+)',
                'ultrawide' => 'Ultrawide (1920px+)',
            ],
            'required' => 1,
        ])
        ->addNumber('padding_top', [
            'label' => 'Padding Top',
            'instructions' => 'Set the top padding in rem units.',
            'min' => 0,
            'max' => 20,
            'step' => 0.1,
            'append' => 'rem',
            'default_value' => 5,
        ])
        ->addNumber('padding_bottom', [
            'label' => 'Padding Bottom',
            'instructions' => 'Set the bottom padding in rem units.',
            'min' => 0,
            'max' => 20,
            'step' => 0.1,
            'append' => 'rem',
            'default_value' => 5,
        ])
    ->endRepeater();

return $counters;
