<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$content_cta = new FieldsBuilder('content_cta', [
    'label' => 'Content CTA',
]);

$content_cta
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
            'default_value' => 'Are you a healthcare professional?',
        ])
        ->addWysiwyg('body', [
            'label' => 'Body Copy',
            'instructions' => 'Supporting copy shown beside the CTA button.',
            'tabs' => 'all',
            'toolbar' => 'basic',
            'media_upload' => 0,
        ])
        ->addLink('button_link', [
            'label' => 'CTA Button',
            'return_format' => 'array',
        ])

    ->addTab('Design', ['label' => 'Design'])
        ->addSelect('layout_style', [
            'label' => 'Layout',
            'instructions' => 'Layout 2 uses a full-width background image with a colour tint beneath it.',
            'choices' => [
                'default' => 'Layout 1 (solid / gradient)',
                'image_background' => 'Layout 2 (image background)',
            ],
            'default_value' => 'default',
            'ui' => 1,
        ])
        ->addSelect('background_type', [
            'label' => 'Background Type',
            'choices' => [
                'color' => 'Solid Color',
                'gradient' => 'Gradient',
            ],
            'default_value' => 'color',
            'conditional_logic' => [
                [
                    [
                        'field' => 'layout_style',
                        'operator' => '==',
                        'value' => 'default',
                    ],
                ],
            ],
        ])
        ->addColorPicker('background_color', [
            'label' => 'Background Color',
            'instructions' => 'Used when Background Type is Solid Color.',
            'default_value' => '#E9E2F7',
            'conditional_logic' => [
                [
                    [
                        'field' => 'layout_style',
                        'operator' => '==',
                        'value' => 'default',
                    ],
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
            'instructions' => 'Paste a full CSS gradient value, e.g. linear-gradient(135deg, #E9E2F7 0%, #CEF2EE 100%).',
            'default_value' => 'linear-gradient(278deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
            'conditional_logic' => [
                [
                    [
                        'field' => 'layout_style',
                        'operator' => '==',
                        'value' => 'default',
                    ],
                    [
                        'field' => 'background_type',
                        'operator' => '==',
                        'value' => 'gradient',
                    ],
                ],
            ],
        ])
        ->addImage('background_image', [
            'label' => 'Background Image',
            'instructions' => 'Full-width background image for Layout 2.',
            'return_format' => 'id',
            'preview_size' => 'medium',
            'library' => 'all',
            'conditional_logic' => [
                [
                    [
                        'field' => 'layout_style',
                        'operator' => '==',
                        'value' => 'image_background',
                    ],
                ],
            ],
        ])
        ->addColorPicker('background_tint_color', [
            'label' => 'Background Tint',
            'instructions' => 'Solid colour shown beneath the background image.',
            'default_value' => '#F1F3DE',
            'conditional_logic' => [
                [
                    [
                        'field' => 'layout_style',
                        'operator' => '==',
                        'value' => 'image_background',
                    ],
                ],
            ],
        ])
        ->addSelect('background_image_opacity', [
            'label' => 'Background Image Opacity',
            'instructions' => 'How visible the background image appears over the tint.',
            'choices' => [
                '0' => 'None',
                '25' => '25%',
                '50' => '50%',
                '75' => '75%',
                '100' => '100%',
            ],
            'default_value' => '50',
            'conditional_logic' => [
                [
                    [
                        'field' => 'layout_style',
                        'operator' => '==',
                        'value' => 'image_background',
                    ],
                ],
            ],
        ])
        ->addColorPicker('background_image_overlay_color', [
            'label' => 'Background Image Overlay',
            'instructions' => 'Optional extra colour tint over the image to improve text contrast.',
            'conditional_logic' => [
                [
                    [
                        'field' => 'layout_style',
                        'operator' => '==',
                        'value' => 'image_background',
                    ],
                ],
            ],
        ])
        ->addSelect('background_image_overlay_opacity', [
            'label' => 'Background Image Overlay Opacity',
            'instructions' => 'How strong the overlay tint appears over the image.',
            'choices' => [
                '0' => 'None',
                '25' => '25%',
                '50' => '50%',
                '75' => '75%',
            ],
            'default_value' => '50',
            'conditional_logic' => [
                [
                    [
                        'field' => 'layout_style',
                        'operator' => '==',
                        'value' => 'image_background',
                    ],
                    [
                        'field' => 'background_image_overlay_color',
                        'operator' => '!=empty',
                    ],
                ],
            ],
        ])
        ->addSelect('color_scheme', [
            'label' => 'Text & Button Style',
            'instructions' => 'Use Inverse on dark backgrounds so headings, body copy, and buttons stay readable.',
            'choices' => [
                'default' => 'Default (dark text)',
                'inverse' => 'Inverse (light text)',
            ],
            'default_value' => 'default',
            'ui' => 1,
        ]);

return $content_cta;
