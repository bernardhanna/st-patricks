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
        ])
        ->addSelect('vertical_padding', [
            'label' => 'Vertical Padding',
            'instructions' => 'Use “Bottom only” to sit flush under the previous section. “None”, “60px”, and “Custom” are optional extras — existing choices are unchanged.',
            'choices' => [
                'default' => 'Top & bottom (100px desktop)',
                'no_bottom' => 'Top only (no bottom on desktop)',
                'no_top' => 'Bottom only (no top on desktop)',
                'medium' => 'Top & bottom (60px desktop)',
                'none' => 'No padding',
                'custom' => 'Custom (per breakpoint)',
            ],
            'default_value' => 'default',
            'ui' => 1,
        ])
        ->addRepeater('padding_settings', [
            'label' => 'Custom Padding',
            'instructions' => 'Only used when Vertical Padding is “Custom”. Enter rem values per breakpoint (e.g. 2 = 32px).',
            'button_label' => 'Add Breakpoint',
            'layout' => 'table',
            'conditional_logic' => [[
                [
                    'field' => 'vertical_padding',
                    'operator' => '==',
                    'value' => 'custom',
                ],
            ]],
        ])
            ->addSelect('screen_size', [
                'label' => 'Breakpoint',
                'choices' => [
                    'mob' => 'Mobile (mob)',
                    'sm' => 'Small (sm)',
                    'md' => 'Medium (md)',
                    'lg' => 'Large (lg)',
                    'xl' => 'Extra large (xl)',
                ],
                'default_value' => 'lg',
                'ui' => 1,
            ])
            ->addNumber('padding_top', [
                'label' => 'Top (rem)',
                'min' => 0,
                'step' => 1,
            ])
            ->addNumber('padding_bottom', [
                'label' => 'Bottom (rem)',
                'min' => 0,
                'step' => 1,
            ])
        ->endRepeater();

return $locations_grid;
