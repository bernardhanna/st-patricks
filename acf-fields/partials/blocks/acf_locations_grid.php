<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$locations_grid = new FieldsBuilder('locations_grid', [
    'label' => 'Locations Grid',
]);

$locations_grid
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
            'default_value' => 'Our locations',
        ])
        ->addSelect('source_mode', [
            'label' => 'Card Source',
            'instructions' => 'Choose manual cards or pull cards from Location posts.',
            'choices' => [
                'manual' => 'Manual Cards',
                'locations' => 'Location Posts',
            ],
            'default_value' => 'manual',
            'ui' => 1,
        ])
        ->addRelationship('selected_locations', [
            'label' => 'Selected Locations',
            'instructions' => 'Choose Location posts in display order.',
            'post_type' => ['locations'],
            'filters' => ['search', 'taxonomy'],
            'return_format' => 'object',
            'min' => 1,
            'conditional_logic' => [
                [
                    [
                        'field' => 'source_mode',
                        'operator' => '==',
                        'value' => 'locations',
                    ],
                ],
            ],
        ])
        ->addRepeater('cards', [
            'label' => 'Location Cards',
            'instructions' => 'Add each location card in display order.',
            'button_label' => 'Add Location Card',
            'layout' => 'row',
            'min' => 1,
            'conditional_logic' => [
                [
                    [
                        'field' => 'source_mode',
                        'operator' => '==',
                        'value' => 'manual',
                    ],
                ],
            ],
        ])
            ->addImage('image', [
                'label' => 'Image',
                'return_format' => 'array',
                'preview_size' => 'medium',
            ])
            ->addText('title', [
                'label' => 'Title',
            ])
            ->addLink('link', [
                'label' => 'Card Link',
                'instructions' => 'Optional. When set, the whole card becomes clickable.',
                'return_format' => 'array',
            ])
        ->endRepeater()
        ->addLink('footer_button_link', [
            'label' => 'Footer Button Link',
            'instructions' => 'Optional section CTA shown below the grid.',
            'return_format' => 'array',
        ]);

return $locations_grid;
