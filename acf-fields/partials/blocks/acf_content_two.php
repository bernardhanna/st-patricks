<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$content_two = new FieldsBuilder('content_two', [
    'label' => 'Content Two',
]);

$content_two
    ->addTab('Content', ['label' => 'Content'])
    ->addText('heading', [
        'label' => 'Section Heading',
        'instructions' => 'Enter the main heading for the stories section.',
        'default_value' => 'Stories and Support',
        'required' => 1,
    ])
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
            'p' => 'Paragraph',
            'span' => 'Span',
        ],
        'default_value' => 'h2',
        'required' => 1,
    ])
    ->addWysiwyg('description', [
        'label' => 'Description',
        'instructions' => 'Enter the description text below the heading.',
        'default_value' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Lorem ipsum dolor sit amet, consectetur adipisicing elit.',
        'media_upload' => 0,
        'tabs' => 'all',
        'toolbar' => 'full',
    ])
    ->addImage('hero_image', [
        'label' => 'Hero Image',
        'instructions' => 'Upload the main image for the stories section.',
        'return_format' => 'id',
        'preview_size' => 'medium',
        'library' => 'all',
    ])
    ->addLink('button', [
        'label' => 'Call to Action Button',
        'instructions' => 'Configure the "See all stories" button.',
        'return_format' => 'array',
    ])

    ->addTab('Design', ['label' => 'Design'])
    ->addColorPicker('background_color', [
        'label' => 'Background Color',
        'instructions' => 'Choose the background color for the section.',
        'default_value' => '#ffffff',
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
            'instructions' => 'Select the screen size for this padding setting.',
            'choices' => [
                'xxs' => 'XXS (320px+)',
                'xs' => 'XS (480px+)',
                'mob' => 'Mobile (575px+)',
                'sm' => 'Small (640px+)',
                'md' => 'Medium (768px+)',
                'lg' => 'Large (1100px+)',
                'xl' => 'Extra Large (1280px+)',
                'xxl' => 'XXL (1440px+)',
                'ultrawide' => 'Ultra Wide (1920px+)',
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

return $content_two;
