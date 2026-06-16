<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$defaults = matrix_get_research_cards_grid_defaults();

$research_cards_grid = new FieldsBuilder('research_cards_grid', [
    'label' => 'Research Cards Grid',
]);

$research_cards_grid
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
            'default_value' => $defaults['heading'],
        ])
        ->addWysiwyg('intro', [
            'label' => 'Intro Copy',
            'instructions' => 'Short supporting copy shown above the card grid.',
            'tabs' => 'all',
            'toolbar' => 'basic',
            'media_upload' => 0,
        ])
        ->addSelect('cards_source', [
            'label' => 'Cards Source',
            'choices' => [
                'manual' => 'Manual cards',
                'category' => 'By research category',
                'latest' => 'Latest project posts',
                'selected' => 'Select projects',
            ],
            'default_value' => 'manual',
        ])
        ->addNumber('posts_per_page', [
            'label' => 'Projects to Show',
            'instructions' => 'Used for latest, category, and selected project sources.',
            'default_value' => 4,
            'min' => 1,
            'max' => 12,
            'step' => 1,
            'conditional_logic' => [
                [
                    [
                        'field' => 'cards_source',
                        'operator' => '!=',
                        'value' => 'manual',
                    ],
                ],
            ],
        ])
        ->addTaxonomy('selected_research_categories', [
            'label' => 'Research Categories',
            'taxonomy' => 'research_project_category',
            'field_type' => 'multi_select',
            'return_format' => 'id',
            'allow_null' => 1,
            'multiple' => 1,
            'conditional_logic' => [
                [
                    [
                        'field' => 'cards_source',
                        'operator' => '==',
                        'value' => 'category',
                    ],
                ],
            ],
        ])
        ->addRelationship('selected_research_projects', [
            'label' => 'Selected Research Projects',
            'post_type' => ['research_projects'],
            'filters' => ['search'],
            'return_format' => 'object',
            'conditional_logic' => [
                [
                    [
                        'field' => 'cards_source',
                        'operator' => '==',
                        'value' => 'selected',
                    ],
                ],
            ],
        ])
        ->addRepeater('cards', [
            'label' => 'Cards',
            'instructions' => 'Add each research card in display order.',
            'button_label' => 'Add Card',
            'layout' => 'row',
            'min' => 1,
            'conditional_logic' => [
                [
                    [
                        'field' => 'cards_source',
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
            ->addWysiwyg('summary', [
                'label' => 'Summary',
                'tabs' => 'all',
                'toolbar' => 'basic',
                'media_upload' => 0,
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

    ->addTab('Design', ['label' => 'Design'])
        ->addColorPicker('background_color', [
            'label' => 'Background Color',
            'default_value' => $defaults['background_color'],
        ])
        ->addColorPicker('heading_color', [
            'label' => 'Heading Color',
            'default_value' => $defaults['heading_color'],
        ])
        ->addColorPicker('intro_color', [
            'label' => 'Intro Color',
            'default_value' => $defaults['intro_color'],
        ])
        ->addColorPicker('card_title_color', [
            'label' => 'Card Title Color',
            'default_value' => $defaults['card_title_color'],
        ])
        ->addColorPicker('card_body_color', [
            'label' => 'Card Body Color',
            'default_value' => $defaults['card_body_color'],
        ])
        ->addColorPicker('button_border_color', [
            'label' => 'Button Border Color',
            'default_value' => $defaults['button_border_color'],
        ])
        ->addColorPicker('button_text_color', [
            'label' => 'Button Text Color',
            'default_value' => $defaults['button_text_color'],
        ]);

return $research_cards_grid;
