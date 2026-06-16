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
    ]);

return $counters;
