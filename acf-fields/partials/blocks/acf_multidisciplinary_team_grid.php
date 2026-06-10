<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$multidisciplinary_team_grid = new FieldsBuilder('multidisciplinary_team_grid', [
    'label' => 'Multidisciplinary Team Grid',
]);

$multidisciplinary_team_grid
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
            'instructions' => 'Section heading shown above the grid.',
            'default_value' => 'Our multidisciplinary team',
        ])
        ->addWysiwyg('intro', [
            'label' => 'Intro Copy',
            'instructions' => 'Short supporting copy shown between the heading and the card grid.',
            'tabs' => 'all',
            'media_upload' => 0,
            'toolbar' => 'basic',
            'default_value' => '<p>Our multidisciplinary team brings together specialist clinical expertise across psychiatry, psychology, nursing, social work, occupational therapy, and family support.</p>',
        ])
        ->addRepeater('cards', [
            'label' => 'Team Cards',
            'instructions' => 'Add each multidisciplinary team card in display order.',
            'button_label' => 'Add Team Card',
            'layout' => 'row',
            'min' => 1,
        ])
            ->addText('title', [
                'label' => 'Title',
                'default_value' => 'Psychiatry',
            ])
            ->addWysiwyg('description', [
                'label' => 'Description',
                'tabs' => 'all',
                'media_upload' => 0,
                'toolbar' => 'basic',
                'default_value' => '<p>Led by consultant psychiatrists and doctors who guide diagnosis, treatment planning, and ongoing medical care.</p>',
            ])
            ->addLink('link', [
                'label' => 'Card Link',
                'instructions' => 'Optional. When set, the whole card becomes clickable and shows the hover state.',
                'return_format' => 'array',
            ])
            ->addSelect('card_tone', [
                'label' => 'Card Tone',
                'choices' => [
                    'teal' => 'Teal',
                    'green' => 'Green',
                    'yellow' => 'Yellow',
                    'lavender' => 'Lavender',
                    'pink' => 'Pink',
                    'coral' => 'Coral',
                ],
                'default_value' => 'teal',
            ])
        ->endRepeater()

    ->addTab('Design', ['label' => 'Design'])
        ->addColorPicker('background_color', [
            'label' => 'Background Color',
            'default_value' => '#FFFFFF',
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

return $multidisciplinary_team_grid;
