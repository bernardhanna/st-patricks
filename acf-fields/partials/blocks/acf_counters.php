<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$counters = new FieldsBuilder('counters', [
    'label' => 'Counters (About Us)',
]);

$counters
    ->addTab('content_tab', ['label' => 'Content'])

    // Heading Section
    ->addSelect('heading_tag', [
        'label' => 'Heading Tag',
        'instructions' => 'Select the HTML tag for the heading.',
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
    ->addText('heading_text', [
        'label' => 'Heading Text',
        'instructions' => 'Enter the main heading text.',
        'default_value' => 'About us',
    ])
    ->addWysiwyg('description', [
        'label' => 'Description',
        'instructions' => 'Enter the description text below the heading.',
        'tabs' => 'visual',
        'media_upload' => 0,
        'delay' => 0,
        'default_value' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
    ])

    // Image
    ->addImage('image', [
        'label' => 'Main Image',
        'instructions' => 'Upload the main image for the section.',
        'return_format' => 'array',
        'preview_size' => 'medium',
    ])

    // CTA Buttons
    ->addLink('primary_cta', [
        'label' => 'Primary Button',
        'instructions' => 'Configure the primary call-to-action button.',
        'return_format' => 'array',
        'default_value' => [
            'url' => '#',
            'title' => 'Careers',
            'target' => '_self',
        ],
    ])
    ->addLink('secondary_cta', [
        'label' => 'Secondary Button',
        'instructions' => 'Configure the secondary call-to-action button.',
        'return_format' => 'array',
        'default_value' => [
            'url' => '#',
            'title' => 'About us',
            'target' => '_self',
        ],
    ])

    // Key Points (Counters)
    ->addRepeater('key_points', [
        'label' => 'Key Points (Counters)',
        'instructions' => 'Add animated counter statistics.',
        'button_label' => 'Add Key Point',
        'layout' => 'row',
        'min' => 1,
        'max' => 6,
    ])
        ->addNumber('value', [
            'label' => 'Counter Value',
            'instructions' => 'The target number to count up to.',
            'default_value' => 95,
            'min' => 0,
            'step' => 1,
        ])
        ->addText('suffix', [
            'label' => 'Suffix',
            'instructions' => 'Text to append after the number (e.g., %, k, +).',
            'default_value' => '%',
        ])
        ->addText('title', [
            'label' => 'Title',
            'instructions' => 'The title text below the counter.',
            'default_value' => 'Key point text',
        ])
        ->addTextarea('text', [
            'label' => 'Description',
            'instructions' => 'Short description text below the title.',
            'new_lines' => '',
            'maxlength' => 160,
            'default_value' => 'Lorem ipsum dolor sit ametsed do eiusmod tempor incididunt',
        ])
    ->endRepeater()

    ->addTab('design_tab', ['label' => 'Design'])

    // Color Controls
    ->addColorPicker('background_color', [
        'label' => 'Background Color',
        'instructions' => 'Set the background color for the section.',
        'default_value' => '#ffffff',
    ])
    ->addColorPicker('heading_color', [
        'label' => 'Heading Color',
        'instructions' => 'Set the color for the main heading.',
        'default_value' => '#1e1b4b',
    ])
    ->addColorPicker('desc_color', [
        'label' => 'Description Color',
        'instructions' => 'Set the color for the description text.',
        'default_value' => '#134e4a',
    ])
    ->addColorPicker('divider_color', [
        'label' => 'Divider Color',
        'instructions' => 'Set the color for dividers and underlines.',
        'default_value' => '#ef4444',
    ])
    ->addColorPicker('value_color', [
        'label' => 'Counter Value Color',
        'instructions' => 'Set the color for counter numbers.',
        'default_value' => '#0c4a6e',
    ])
    ->addColorPicker('title_color', [
        'label' => 'Key Point Title Color',
        'instructions' => 'Set the color for key point titles.',
        'default_value' => '#1e1b4b',
    ])
    ->addColorPicker('text_color', [
        'label' => 'Key Point Text Color',
        'instructions' => 'Set the color for key point descriptions.',
        'default_value' => '#134e4a',
    ])

    ->addTab('layout_tab', ['label' => 'Layout'])

    // Padding Settings
    ->addRepeater('padding_settings', [
        'label' => 'Padding Settings',
        'instructions' => 'Customize padding for different screen sizes.',
        'button_label' => 'Add Screen Size Padding',
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
        ])
        ->addNumber('padding_top', [
            'label' => 'Padding Top',
            'instructions' => 'Set the top padding in rem units.',
            'min' => 0,
            'max' => 20,
            'step' => 0.1,
            'append' => 'rem',
            'default_value' => 6,
        ])
        ->addNumber('padding_bottom', [
            'label' => 'Padding Bottom',
            'instructions' => 'Set the bottom padding in rem units.',
            'min' => 0,
            'max' => 20,
            'step' => 0.1,
            'append' => 'rem',
            'default_value' => 6,
        ])
    ->endRepeater();

return $counters;
