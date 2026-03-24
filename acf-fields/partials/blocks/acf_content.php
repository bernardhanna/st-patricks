<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$content_safeguarding = new FieldsBuilder('content', [
    'label' => 'Content Section',
]);

$content_safeguarding
    ->addTab('Content', [
        'label' => 'Content',
        'placement' => 'top'
    ])
    ->addText('heading', [
        'label' => 'Heading Text',
        'instructions' => 'Enter the main heading for this section.',
        'default_value' => 'Child Safeguarding',
        'required' => 1,
    ])
    ->addSelect('heading_tag', [
        'label' => 'Heading Tag',
        'instructions' => 'Select the appropriate HTML heading tag for SEO and accessibility.',
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
    ->addWysiwyg('content', [
        'label' => 'Content',
        'instructions' => 'Add the main content text for this section.',
        'default_value' => '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat incididunt ut laboret.</p>',
        'required' => 1,
        'media_upload' => 0,
        'tabs' => 'all',
        'toolbar' => 'full',
    ])
    ->addImage('image', [
        'label' => 'Featured Image',
        'instructions' => 'Upload an image to display alongside the content. Recommended size: 450x346 pixels.',
        'return_format' => 'id',
        'preview_size' => 'medium',
        'library' => 'all',
        'required' => 1,
    ])

    ->addTab('Design', [
        'label' => 'Design',
        'placement' => 'top'
    ])
    ->addSelect('background_type', [
        'label' => 'Background Type',
        'instructions' => 'Choose between a solid color or gradient background.',
        'choices' => [
            'gradient' => 'Default Gradient',
            'color' => 'Custom Color',
        ],
        'default_value' => 'gradient',
        'required' => 1,
    ])
    ->addColorPicker('background_color', [
        'label' => 'Background Color',
        'instructions' => 'Select a custom background color (only used when Background Type is set to Custom Color).',
        'default_value' => '#FFFFFF',
        'conditional_logic' => [
            [
                [
                    'field' => 'background_type',
                    'operator' => '==',
                    'value' => 'color',
                ],
            ],
        ],
    ])

    ->addTab('Layout', [
        'label' => 'Layout',
        'placement' => 'top'
    ])
    ->addTrueFalse('reverse_layout', [
        'label' => 'Reverse Layout',
        'instructions' => 'Toggle to switch the image and content positions (image on right, content on left).',
        'default_value' => 0,
        'ui' => 1,
        'ui_on_text' => 'Image Right',
        'ui_off_text' => 'Image Left',
    ])
    ->addRepeater('padding_settings', [
        'label' => 'Padding Settings',
        'instructions' => 'Customize padding for different screen sizes. Add multiple entries to control padding at different breakpoints.',
        'button_label' => 'Add Screen Size Padding',
        'layout' => 'table',
        'min' => 0,
        'max' => 9,
    ])
        ->addSelect('screen_size', [
            'label' => 'Screen Size',
            'instructions' => 'Select the screen size breakpoint.',
            'choices' => [
                'xxs' => 'Extra Extra Small (320px+)',
                'xs' => 'Extra Small (480px+)',
                'mob' => 'Mobile (575px+)',
                'sm' => 'Small (640px+)',
                'md' => 'Medium (768px+)',
                'lg' => 'Large (1100px+)',
                'xl' => 'Extra Large (1280px+)',
                'xxl' => 'Extra Extra Large (1440px+)',
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

return $content_safeguarding;
