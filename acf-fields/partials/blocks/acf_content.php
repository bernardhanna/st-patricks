<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$content_safeguarding = new FieldsBuilder('content', [
    'label' => 'Content Section',
]);

$content_safeguarding
    ->addTab('Content', [
        'label' => 'Content',
        'placement' => 'top',
    ])
        ->addText('heading', [
            'label' => 'Heading Text',
            'instructions' => 'Enter the main heading for this section.',
            'default_value' => 'Child Safeguarding',
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
        ])
        ->addSelect('accent_position', [
            'label' => 'Accent Line Position',
            'instructions' => 'Place the teal accent line above or below the heading.',
            'choices' => [
                'below_heading' => 'Below Heading',
                'above_heading' => 'Above Heading',
            ],
            'default_value' => 'below_heading',
            'ui' => 1,
        ])
        ->addWysiwyg('intro_text', [
            'label' => 'Intro Text',
            'instructions' => 'Optional bold lead paragraph shown below the heading.',
            'media_upload' => 0,
            'tabs' => 'all',
            'toolbar' => 'basic',
        ])
        ->addWysiwyg('content', [
            'label' => 'Content',
            'instructions' => 'Add the main content text for this section.',
            'default_value' => '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat incididunt ut laboret.</p>',
            'media_upload' => 0,
            'tabs' => 'all',
            'toolbar' => 'full',
        ])
        ->addLink('primary_button', [
            'label' => 'Primary Button (Optional)',
            'instructions' => 'Add a primary CTA button shown below the content.',
            'return_format' => 'array',
            'required' => 0,
        ])
        ->addSelect('primary_button_variant', [
            'label' => 'Primary Button Style',
            'choices' => [
                'filled' => 'Filled',
                'outline' => 'Outline',
            ],
            'default_value' => 'filled',
            'conditional_logic' => [
                [
                    [
                        'field' => 'primary_button',
                        'operator' => '!=empty',
                    ],
                ],
            ],
        ])
        ->addLink('document_link', [
            'label' => 'Document Link (Optional)',
            'instructions' => 'PDF or file link shown with document icon styling instead of a button.',
            'return_format' => 'array',
            'required' => 0,
        ])
        ->addLink('secondary_button', [
            'label' => 'Secondary Button (Optional)',
            'instructions' => 'Add a secondary CTA button shown next to the primary button.',
            'return_format' => 'array',
            'required' => 0,
        ])
        ->addSelect('secondary_button_variant', [
            'label' => 'Secondary Button Style',
            'choices' => [
                'filled' => 'Filled',
                'outline' => 'Outline',
            ],
            'default_value' => 'outline',
            'conditional_logic' => [
                [
                    [
                        'field' => 'secondary_button',
                        'operator' => '!=empty',
                    ],
                ],
            ],
        ])
        ->addImage('image', [
            'label' => 'Featured Image',
            'instructions' => 'Upload an image to display alongside the content. Recommended size: 450x346 pixels.',
            'return_format' => 'id',
            'preview_size' => 'medium',
            'library' => 'all',
        ])

    ->addTab('Design', [
        'label' => 'Design',
        'placement' => 'top',
    ])
        ->addSelect('background_type', [
            'label' => 'Background Type',
            'instructions' => 'Choose a preset background or use a custom color/gradient.',
            'choices' => [
                'gradient' => 'Default Gradient',
                'white' => 'White',
                'cream' => 'Cream',
                'light_blue' => 'Light Blue',
                'color' => 'Custom Color',
            ],
            'default_value' => 'gradient',
        ])
        ->addColorPicker('background_color', [
            'label' => 'Background Color',
            'instructions' => 'Select a custom background color (only used when Background Type is Custom Color).',
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
        ->addText('background_gradient', [
            'label' => 'Background Gradient',
            'instructions' => 'Paste a full CSS gradient value when using the default gradient preset.',
            'default_value' => 'linear-gradient(278deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
            'conditional_logic' => [
                [
                    [
                        'field' => 'background_type',
                        'operator' => '==',
                        'value' => 'gradient',
                    ],
                ],
            ],
        ])

    ->addTab('Layout', [
        'label' => 'Layout',
        'placement' => 'top',
    ])
        ->addSelect('layout_style', [
            'label' => 'Layout Style',
            'instructions' => 'Choose whether the image sits on the left or right.',
            'choices' => [
                'image_left' => 'Image Left',
                'image_right' => 'Image Right',
            ],
            'default_value' => 'image_left',
            'ui' => 1,
        ])
        ->addTrueFalse('reverse_layout', [
            'label' => 'Reverse Layout (Legacy)',
            'instructions' => 'Legacy toggle kept for older rows. Layout Style takes precedence when set.',
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
