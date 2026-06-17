<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$what_we_offer = new FieldsBuilder('what_we_offer', [
    'label' => 'What We Offer Section',
]);

$what_we_offer
    ->addTab('Content', ['label' => 'Content'])
        ->addText('heading', [
            'label' => 'Section Heading',
            'instructions' => 'Enter the main heading for this section.',
            'default_value' => 'What we offer',
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
                'p'  => 'Paragraph',
                'span' => 'Span',
            ],
            'default_value' => 'h2',
        ])
        ->addLink('heading_link', [
            'label' => 'Heading Link (Optional)',
            'instructions' => 'If provided, the heading will become a clickable link.',
            'return_format' => 'array',
        ])
        ->addTrueFalse('show_heading_icon', [
            'label' => 'Show Heading Icon',
            'instructions' => 'Display a chevron icon next to the heading when it is a link.',
            'default_value' => 1,
            'conditional_logic' => [
                [
                    [
                        'field' => 'heading_link',
                        'operator' => '!=empty',
                    ],
                ],
            ],
        ])
        ->addSelect('layout_style', [
            'label' => 'Layout Style',
            'instructions' => 'Choose whether this section uses the image-led layout or the intro-led two-column layout.',
            'choices' => [
                'image_feature' => 'Image Feature',
                'intro_two_column' => 'Intro + Two Column',
            ],
            'default_value' => 'image_feature',
            'ui' => 1,
        ])
        ->addTextarea('intro_text', [
            'label' => 'Intro Text',
            'instructions' => 'Optional introductory copy shown above the services list in the Intro + Two Column layout.',
            'rows' => 3,
            'new_lines' => '',
            'conditional_logic' => [
                [
                    [
                        'field' => 'layout_style',
                        'operator' => '==',
                        'value' => 'intro_two_column',
                    ],
                ],
            ],
        ])
        ->addRepeater('services', [
            'label' => 'Services',
            'instructions' => 'Add the services or offerings to display.',
            'button_label' => 'Add Service',
            'min' => 1,
            'max' => 6,
            'layout' => 'block',
        ])
            ->addText('service_title', [
                'label' => 'Service Title',
                'instructions' => 'Enter the title for this service.',
            ])
            ->addWysiwyg('service_description', [
                'label' => 'Service Description',
                'instructions' => 'Enter the description for this service.',
                'media_upload' => 0,
                'tabs' => 'visual,text',
                'toolbar' => 'basic',
            ])
            ->addLink('service_link', [
                'label' => 'Service Link (Optional)',
                'instructions' => 'If provided, the service title will become a clickable link.',
                'return_format' => 'array',
            ])
            ->addColorPicker('accent_color', [
                'label' => 'Accent Color',
                'instructions' => 'Optional. Sets the icon rail accent; leave empty to rotate through the default palette.',
            ])
            ->addTrueFalse('show_service_icon', [
                'label' => 'Show Service Icon',
                'instructions' => 'Display a chevron icon next to the service title.',
                'default_value' => 1,
            ])
        ->endRepeater()
        ->addImage('main_image', [
            'label' => 'Main Image',
            'instructions' => 'Upload the main image to display alongside the services.',
            'return_format' => 'id',
            'preview_size' => 'medium',
            'conditional_logic' => [
                [
                    [
                        'field' => 'layout_style',
                        'operator' => '==',
                        'value' => 'image_feature',
                    ],
                ],
            ],
        ])

    ->addTab('Design', ['label' => 'Design'])
        ->addText('background_gradient', [
            'label' => 'Section Background Gradient',
            'instructions' => 'CSS background value. Leave empty to use the default gradient.',
            'default_value' => 'var(--StPatricks_Aux_DarkBG4, linear-gradient(278deg, #F6EDE0 3.24%, #F4F5DE 90.88%))',
        ])
        ->addSelect('vertical_padding', [
            'label' => 'Vertical Padding',
            'instructions' => 'Use “Standard” to drop extra desktop padding, or “Bottom only” when this block should sit flush against the section above.',
            'choices' => [
                'default' => 'Top & bottom (96px desktop)',
                'standard' => 'Standard (48px all breakpoints)',
                'bottom_only' => 'Bottom only (no top padding on desktop)',
            ],
            'default_value' => 'default',
            'ui' => 1,
        ]);

return $what_we_offer;
