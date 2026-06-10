<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$team_members = new FieldsBuilder('team_members', [
    'label' => 'Team Members',
]);

$team_members
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
            'default_value' => 'Our Senior Management Team',
        ])
        ->addWysiwyg('intro', [
            'label' => 'Intro Copy',
            'tabs' => 'all',
            'toolbar' => 'basic',
            'media_upload' => 0,
            'default_value' => '<p>We offer inpatient care in three approved centres:</p>',
        ])
        ->addSelect('layout_style', [
            'label' => 'Layout Style',
            'choices' => [
                'standard_grid' => 'Standard Grid',
                'spokespeople_grid' => 'Spokespeople Grid',
            ],
            'default_value' => 'standard_grid',
        ])
        ->addSelect('source_mode', [
            'label' => 'Source Mode',
            'choices' => [
                'all' => 'All Team Members',
                'selected' => 'Selected Team Members',
                'category' => 'By Team Category',
            ],
            'default_value' => 'selected',
        ])
        ->addRelationship('selected_team_members', [
            'label' => 'Selected Team Members',
            'post_type' => ['team_members'],
            'filters' => ['search'],
            'return_format' => 'object',
            'conditional_logic' => [
                [
                    [
                        'field' => 'source_mode',
                        'operator' => '==',
                        'value' => 'selected',
                    ],
                ],
            ],
        ])
        ->addTaxonomy('selected_team_categories', [
            'label' => 'Selected Team Categories',
            'taxonomy' => 'team_member_category',
            'field_type' => 'multi_select',
            'return_format' => 'id',
            'allow_null' => 1,
            'multiple' => 1,
            'conditional_logic' => [
                [
                    [
                        'field' => 'source_mode',
                        'operator' => '==',
                        'value' => 'category',
                    ],
                ],
            ],
        ])
        ->addNumber('posts_per_page', [
            'label' => 'Profiles to Show',
            'default_value' => 9,
            'min' => 1,
            'max' => 24,
        ])

    ->addTab('Design', ['label' => 'Design'])
        ->addText('section_background', [
            'label' => 'Section Background / Gradient',
            'instructions' => 'Use a CSS color or paste a full CSS gradient string.',
            'default_value' => '#FBFAF7',
        ])
        ->addColorPicker('card_background_color', [
            'label' => 'Standard Card Background',
            'default_value' => '#FFFFFF',
        ])
        ->addColorPicker('spokespeople_card_background_color', [
            'label' => 'Spokespeople Card Background',
            'default_value' => '#FBFAF7',
        ])

    ->addTab('Layout', ['label' => 'Layout'])
        ->addRepeater('padding_settings', [
            'label' => 'Padding Settings',
            'instructions' => 'Customize padding for different screen sizes.',
            'button_label' => 'Add Screen Size Padding',
        ])
            ->addSelect('screen_size', [
                'label' => 'Screen Size',
                'choices' => [
                    'xxs' => 'xxs',
                    'xs' => 'xs',
                    'mob' => 'mob',
                    'sm' => 'sm',
                    'md' => 'md',
                    'lg' => 'lg',
                    'xl' => 'xl',
                    'xxl' => 'xxl',
                    'ultrawide' => 'ultrawide',
                ],
            ])
            ->addNumber('padding_top', [
                'label' => 'Padding Top',
                'min' => 0,
                'max' => 20,
                'step' => 0.01,
                'append' => 'rem',
            ])
            ->addNumber('padding_bottom', [
                'label' => 'Padding Bottom',
                'min' => 0,
                'max' => 20,
                'step' => 0.01,
                'append' => 'rem',
            ])
        ->endRepeater();

return $team_members;
