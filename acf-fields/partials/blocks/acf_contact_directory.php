<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$contact_directory = new FieldsBuilder('contact_directory', [
    'label' => 'Contact Directory',
]);

$contact_directory
    ->addTab('Content', ['label' => 'Content'])
    ->addText('heading', [
        'label' => 'Heading',
        'default_value' => 'Contact us',
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
        ],
        'default_value' => 'h2',
    ])
    ->addWysiwyg('intro_text', [
        'label' => 'Intro Text',
        'instructions' => 'Supporting copy shown beside the contact accordions on desktop.',
        'tabs' => 'visual',
        'toolbar' => 'basic',
        'media_upload' => 0,
    ])
    ->addSelect('auto_location_mode', [
        'label' => 'Auto Location Contacts',
        'instructions' => 'Optionally add location contacts automatically from the Locations post type.',
        'choices' => [
            'none' => 'None (manual items only)',
            'all' => 'All published locations',
            'selected' => 'Selected locations',
        ],
        'default_value' => 'none',
    ])
    ->addRelationship('auto_locations', [
        'label' => 'Auto Locations',
        'instructions' => 'Locations to include automatically in the first column.',
        'post_type' => ['locations'],
        'filters' => ['search'],
        'return_format' => 'object',
        'conditional_logic' => [
            [
                [
                    'field' => 'auto_location_mode',
                    'operator' => '==',
                    'value' => 'selected',
                ],
            ],
        ],
    ])
    ->addRepeater('columns', [
        'label' => 'Columns',
        'instructions' => 'Add up to two columns of contact accordions for the right-hand grid.',
        'button_label' => 'Add Column',
        'layout' => 'block',
        'min' => 1,
        'max' => 2,
    ])
        ->addRepeater('items', [
            'label' => 'Accordion Items',
            'button_label' => 'Add Item',
            'layout' => 'block',
            'min' => 1,
        ])
            ->addSelect('item_source', [
                'label' => 'Item Source',
                'choices' => [
                    'manual' => 'Manual entry',
                    'location' => 'Location post',
                ],
                'default_value' => 'manual',
            ])
            ->addPostObject('location', [
                'label' => 'Location',
                'instructions' => 'Pull phone, email, and opening hours from this location. Title and fields below can override.',
                'post_type' => ['locations'],
                'return_format' => 'object',
                'ui' => 1,
                'conditional_logic' => [
                    [
                        [
                            'field' => 'item_source',
                            'operator' => '==',
                            'value' => 'location',
                        ],
                    ],
                ],
            ])
            ->addText('title', [
                'label' => 'Title',
                'instructions' => 'Required for manual items. Optional override when using a location.',
            ])
            ->addTrueFalse('starts_open', [
                'label' => 'Starts Open',
                'ui' => 1,
                'default_value' => 0,
            ])
            ->addRepeater('bullet_items', [
                'label' => 'Bullet Items',
                'button_label' => 'Add Bullet',
                'layout' => 'table',
            ])
                ->addText('label', [
                    'label' => 'Label',
                ])
            ->endRepeater()
            ->addText('phone', [
                'label' => 'Phone Number',
                'instructions' => 'Leave empty when using a location to use the location phone number.',
            ])
            ->addText('email', [
                'label' => 'Email Address',
                'instructions' => 'Leave empty when using a location to use the location email.',
            ])
            ->addRepeater('opening_hours', [
                'label' => 'Opening Hours',
                'button_label' => 'Add Hours Row',
                'layout' => 'table',
                'instructions' => 'Leave empty when using a location to use the location opening hours.',
            ])
                ->addText('day_label', [
                    'label' => 'Days',
                ])
                ->addText('hours', [
                    'label' => 'Hours',
                ])
            ->endRepeater()
        ->endRepeater()
    ->endRepeater()
    ->addTab('Design', ['label' => 'Design'])
        ->addText('section_background', [
            'label' => 'Section Background / Gradient',
            'default_value' => '#FFFFFF',
        ])
        ->addText('closed_panel_background', [
            'label' => 'Closed Panel Background / Gradient',
            'default_value' => '#FBFAF7',
        ])
        ->addText('open_panel_background', [
            'label' => 'Open Panel Background / Gradient',
            'default_value' => 'linear-gradient(-79.46deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
        ]);

return $contact_directory;
