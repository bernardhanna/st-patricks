<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$related_cards = new FieldsBuilder('related_cards', [
    'label' => 'Related Cards',
]);

$related_cards
    ->addTab('content_tab', ['label' => 'Content'])
        ->addSelect('heading_tag', [
            'label' => 'Heading Tag',
            'choices' => [
                'h1' => 'h1',
                'h2' => 'h2',
                'h3' => 'h3',
                'h4' => 'h4',
                'h5' => 'h5',
                'h6' => 'h6',
            ],
            'default_value' => 'h2',
        ])
        ->addText('heading', [
            'label' => 'Heading',
            'default_value' => 'Related',
        ])
        ->addTextarea('intro_text', [
            'label' => 'Intro Text',
            'rows' => 3,
            'new_lines' => 'br',
        ])
        ->addRepeater('cards', [
            'label' => 'Cards',
            'button_label' => 'Add Card',
            'layout' => 'row',
            'min' => 1,
        ])
            ->addImage('image', [
                'label' => 'Card Image',
                'return_format' => 'id',
                'preview_size' => 'medium',
            ])
            ->addText('title', [
                'label' => 'Title',
            ])
            ->addTextarea('description', [
                'label' => 'Description',
                'rows' => 2,
                'new_lines' => '',
            ])
            ->addLink('link', [
                'label' => 'Link',
                'return_format' => 'array',
            ])
        ->endRepeater()

    ->addTab('design_tab', ['label' => 'Design'])
        ->addColorPicker('background_color', [
            'label' => 'Background Color',
            'default_value' => '#FFFFFF',
        ])
        ->addSelect('columns', [
            'label' => 'Grid Columns (Desktop)',
            'choices' => [
                '2' => '2 Columns',
                '3' => '3 Columns',
            ],
            'default_value' => '3',
            'ui' => 1,
        ])

    ->addTab('layout_tab', ['label' => 'Layout'])
        ->addRepeater('padding_settings', [
            'label' => 'Padding Settings',
            'button_label' => 'Add Screen Size Padding',
        ])
            ->addSelect('screen_size', [
                'label' => 'Screen Size',
                'choices' => [
                    'mob' => 'mob',
                    'lg' => 'lg',
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

return $related_cards;
