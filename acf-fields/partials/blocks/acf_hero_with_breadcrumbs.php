<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$hero_with_breadcrumbs = new FieldsBuilder('hero_with_breadcrumbs', [
    'label' => 'Hero With Breadcrumbs',
]);

$hero_with_breadcrumbs
    ->addTab('Content', ['label' => 'Content'])
        ->addSelect('layout_style', [
            'label' => 'Layout Style',
            'instructions' => 'Choose the split image hero or the compact title hero with accent line.',
            'choices' => [
                'image_split' => 'Image Split',
                'title_accent' => 'Title + Accent',
                'register_intro' => 'Register Intro',
            ],
            'default_value' => 'image_split',
            'ui' => 1,
        ])
        ->addTrueFalse('show_breadcrumbs', [
            'label' => 'Show Breadcrumbs',
            'instructions' => 'Toggle the breadcrumb bar above the hero content.',
            'default_value' => 1,
            'ui' => 1,
        ])
        ->addSelect('breadcrumb_source', [
            'label' => 'Breadcrumb Source',
            'instructions' => 'Use the current WordPress page hierarchy or define breadcrumb items manually.',
            'choices' => [
                'auto' => 'Auto',
                'manual' => 'Manual',
            ],
            'default_value' => 'manual',
            'conditional_logic' => [
                [
                    [
                        'field' => 'show_breadcrumbs',
                        'operator' => '==',
                        'value' => '1',
                    ],
                ],
            ],
        ])
        ->addRepeater('manual_breadcrumbs', [
            'label' => 'Manual Breadcrumb Items',
            'instructions' => 'Add breadcrumb links in the order they should appear before the current page label.',
            'button_label' => 'Add Breadcrumb Item',
            'layout' => 'row',
            'conditional_logic' => [
                [
                    [
                        'field' => 'show_breadcrumbs',
                        'operator' => '==',
                        'value' => '1',
                    ],
                    [
                        'field' => 'breadcrumb_source',
                        'operator' => '==',
                        'value' => 'manual',
                    ],
                ],
            ],
        ])
            ->addLink('breadcrumb_link', [
                'label' => 'Breadcrumb Link',
                'instructions' => 'Use an ACF link so label, URL, and target stay together.',
                'return_format' => 'array',
            ])
        ->endRepeater()
        ->addText('current_crumb_label', [
            'label' => 'Current Crumb Label',
            'instructions' => 'Shown as the final breadcrumb item when using manual breadcrumb mode.',
            'default_value' => 'Healthcare professionals',
            'conditional_logic' => [
                [
                    [
                        'field' => 'show_breadcrumbs',
                        'operator' => '==',
                        'value' => '1',
                    ],
                    [
                        'field' => 'breadcrumb_source',
                        'operator' => '==',
                        'value' => 'manual',
                    ],
                ],
            ],
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
            'default_value' => 'h1',
        ])
        ->addText('heading', [
            'label' => 'Heading',
            'instructions' => 'Main hero heading.',
            'default_value' => 'About Us landing page title',
        ])
        ->addWysiwyg('content', [
            'label' => 'Content',
            'instructions' => 'Supporting copy shown below the heading.',
            'default_value' => '<p>Healthcare professionals - is a landing page (per sitemap) that links users to all other subpages within this section. Page context goes here. Max 4 lines of text. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt.</p>',
            'media_upload' => 0,
            'tabs' => 'all',
            'toolbar' => 'full',
        ])
        ->addText('aside_heading', [
            'label' => 'Aside Heading',
            'instructions' => 'Prompt shown above the aside button in the Register Intro layout.',
            'default_value' => 'Already registered?',
            'conditional_logic' => [
                [
                    [
                        'field' => 'layout_style',
                        'operator' => '==',
                        'value' => 'register_intro',
                    ],
                ],
            ],
        ])
        ->addLink('primary_button', [
            'label' => 'Primary Button',
            'instructions' => 'Optional CTA below hero copy (image split) or in the aside column (register intro).',
            'return_format' => 'array',
            'conditional_logic' => [
                [
                    [
                        'field' => 'layout_style',
                        'operator' => '==',
                        'value' => 'image_split',
                    ],
                ],
                [
                    [
                        'field' => 'layout_style',
                        'operator' => '==',
                        'value' => 'register_intro',
                    ],
                ],
            ],
        ])
        ->addImage('hero_image', [
            'label' => 'Hero Image',
            'instructions' => 'Main image shown on the right side of the hero.',
            'return_format' => 'id',
            'preview_size' => 'medium',
            'conditional_logic' => [
                [
                    [
                        'field' => 'layout_style',
                        'operator' => '==',
                        'value' => 'image_split',
                    ],
                ],
            ],
        ])

    ->addTab('Design', ['label' => 'Design'])
        ->addColorPicker('background_color', [
            'label' => 'Background Color',
            'instructions' => 'Use the light blue default for image split heroes. Title + Accent layouts often use cream (#FBF8F3).',
            'default_value' => '#C6ECF4',
        ])
        ->addColorPicker('accent_color', [
            'label' => 'Accent Line Color',
            'instructions' => 'Shown below the heading in the Title + Accent layout.',
            'default_value' => '#6FC9C0',
            'conditional_logic' => [
                [
                    [
                        'field' => 'layout_style',
                        'operator' => '==',
                        'value' => 'title_accent',
                    ],
                ],
            ],
        ])
        ->addColorPicker('breadcrumb_background_color', [
            'label' => 'Breadcrumb Background Color',
            'default_value' => '#F1F8F9',
        ])
        ->addColorPicker('heading_color', [
            'label' => 'Heading Color',
            'default_value' => '#08284B',
        ])
        ->addColorPicker('text_color', [
            'label' => 'Text Color',
            'default_value' => '#08284B',
        ])
        ->addSelect('text_max_width', [
            'label' => 'Heading & Text Max Width',
            'instructions' => 'Choose the maximum width for the hero heading and supporting copy.',
            'choices' => [
                'default' => 'Default (599px)',
                'wide' => 'Wide (50rem text, stacked)',
            ],
            'default_value' => 'default',
            'ui' => 1,
        ]);

return $hero_with_breadcrumbs;
