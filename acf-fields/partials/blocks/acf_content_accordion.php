<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$content_accordion = new FieldsBuilder('content_accordion', [
    'label' => 'Content Accordion',
]);

$content_accordion
    ->addTab('Content', ['label' => 'Content'])
        ->addSelect('layout_style', [
            'label' => 'Accordion layout',
            'instructions' => 'Choose the layout that matches your content: general text sections, travel directions with icons, or policy pages with PDFs and download links.',
            'choices' => [
                'default' => 'Standard — text sections (optional icons)',
                'directions_page' => 'Travel & directions — icon rows (car, bus, train)',
                'policies_page' => 'Documents & links — PDF grids and link cards',
            ],
            'default_value' => 'default',
            'ui' => 1,
        ])
        ->addRepeater('items', [
            'label' => 'Accordion Items',
            'button_label' => 'Add Item',
            'layout' => 'block',
            'min' => 1,
        ])
            ->addText('title', [
                'label' => 'Title',
            ])
            ->addTrueFalse('starts_open', [
                'label' => 'Starts Open',
                'ui' => 1,
                'default_value' => 0,
            ])
            ->addRepeater('content_rows', [
                'label' => 'Content Rows',
                'button_label' => 'Add Content Row',
                'layout' => 'row',
                'min' => 1,
            ])
                ->addSelect('row_type', [
                    'label' => 'Row Type',
                    'instructions' => 'Only used with the “Documents & links” layout for PDF grids and link lists.',
                    'choices' => [
                        'text' => 'Text',
                        'pdf_grid' => 'PDF Grid',
                        'link_cards' => 'Link Cards',
                        'external_links' => 'External Links',
                    ],
                    'default_value' => 'text',
                    'ui' => 1,
                ])
                ->addSelect('icon_key', [
                    'label' => 'Icon',
                    'instructions' => 'Only used with the “Travel & directions” layout when no custom icon image is uploaded.',
                    'choices' => [
                        'car' => 'Car',
                        'map_pin' => 'Map pin',
                        'clock' => 'Clock',
                        'bus' => 'Bus',
                        'train' => 'Train',
                    ],
                    'allow_null' => 1,
                    'ui' => 1,
                ])
                ->addImage('icon', [
                    'label' => 'Custom Icon Image',
                    'instructions' => 'Optional override for the selected icon.',
                    'return_format' => 'array',
                    'preview_size' => 'thumbnail',
                ])
                ->addWysiwyg('content', [
                    'label' => 'Content',
                    'tabs' => 'all',
                    'toolbar' => 'basic',
                    'media_upload' => 0,
                    'conditional_logic' => [
                        [
                            [
                                'field' => 'row_type',
                                'operator' => '==',
                                'value' => 'text',
                            ],
                        ],
                        [
                            [
                                'field' => 'row_type',
                                'operator' => '==empty',
                            ],
                        ],
                    ],
                ])
                ->addRepeater('pdf_documents', [
                    'label' => 'PDF Documents',
                    'button_label' => 'Add PDF Document',
                    'layout' => 'row',
                    'conditional_logic' => [
                        [
                            [
                                'field' => 'row_type',
                                'operator' => '==',
                                'value' => 'pdf_grid',
                            ],
                        ],
                    ],
                ])
                    ->addText('title', [
                        'label' => 'Title',
                    ])
                    ->addLink('document_link', [
                        'label' => 'PDF Link',
                        'return_format' => 'array',
                    ])
                ->endRepeater()
                ->addRepeater('link_cards', [
                    'label' => 'Link Cards',
                    'button_label' => 'Add Link Card',
                    'layout' => 'row',
                    'conditional_logic' => [
                        [
                            [
                                'field' => 'row_type',
                                'operator' => '==',
                                'value' => 'link_cards',
                            ],
                        ],
                    ],
                ])
                    ->addText('title', [
                        'label' => 'Title',
                    ])
                    ->addLink('button_link', [
                        'label' => 'Button Link',
                        'return_format' => 'array',
                    ])
                ->endRepeater()
                ->addRepeater('external_links', [
                    'label' => 'External Links',
                    'button_label' => 'Add External Link',
                    'layout' => 'row',
                    'conditional_logic' => [
                        [
                            [
                                'field' => 'row_type',
                                'operator' => '==',
                                'value' => 'external_links',
                            ],
                        ],
                    ],
                ])
                    ->addText('title', [
                        'label' => 'Title',
                    ])
                    ->addLink('link', [
                        'label' => 'Link',
                        'return_format' => 'array',
                    ])
                ->endRepeater()
            ->endRepeater()
        ->endRepeater()

    ->addTab('Design', ['label' => 'Design'])
        ->addColorPicker('section_background', [
            'label' => 'Section Background Color',
            'default_value' => '#FFFFFF',
        ])
        ->addText('panel_background', [
            'label' => 'Panel Background / Gradient',
            'default_value' => 'linear-gradient(135deg, #F6EDE0 0%, #F5F0E0 48%, #F4F5DE 100%)',
        ])
        ->addText('open_panel_background', [
            'label' => 'Open Panel Background / Gradient',
            'default_value' => 'linear-gradient(135deg, #F6EDE0 0%, #F5F0E0 48%, #F4F5DE 100%)',
        ])
        ->addColorPicker('icon_tile_background_color', [
            'label' => 'Icon Tile Background Color',
            'default_value' => '#FFFFFF',
        ])
        ->addSelect('vertical_padding', [
            'label' => 'Vertical Padding',
            'instructions' => 'Use “Bottom only” when this block should sit flush against the section above on desktop.',
            'choices' => [
                'default' => 'Top & bottom',
                'bottom_only' => 'Bottom only (no top padding)',
                'small_top_large_bottom' => 'Small top / large bottom (2rem top, 100px bottom)',
            ],
            'default_value' => 'default',
            'ui' => 1,
        ]);

return $content_accordion;
