<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$about_links_grid = new FieldsBuilder('about_links_grid', [
    'label' => 'About Links Grid',
]);

$about_links_grid
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
                'span' => 'span',
                'p' => 'p',
            ],
            'default_value' => 'h2',
        ])
        ->addText('heading_text', [
            'label' => 'Heading Text',
            'placeholder' => 'About us',
            'default_value' => 'About us',
        ])
        ->addTextarea('intro_text', [
            'label' => 'Intro Text',
            'instructions' => 'Optional short introduction shown above the card grid.',
            'rows' => 3,
            'new_lines' => 'br',
        ])
        ->addRepeater('links', [
            'label' => 'Link Cards',
            'instructions' => 'Add one card per destination link.',
            'button_label' => 'Add Link Card',
            'layout' => 'row',
            'min' => 1,
        ])
            ->addImage('icon', [
                'label' => 'Legacy Icon',
                'return_format' => 'array',
                'preview_size' => 'thumbnail',
                'instructions' => 'Optional fallback icon for older/simple card layouts.',
            ])
            ->addUrl('image_url', [
                'label' => 'Card Image URL',
                'instructions' => 'Used by the Figma-style card layout. Paste a full image URL.',
            ])
            ->addText('title', [
                'label' => 'Title',
                'placeholder' => 'Residential care',
                'default_value' => 'Link Title',
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
            ->addSelect('card_tone', [
                'label' => 'Card Tone',
                'choices' => [
                    'bg1' => 'Teal',
                    'bg2' => 'Green',
                    'bg3' => 'Lavender',
                    'bg4' => 'Pink',
                ],
                'default_value' => 'bg1',
            ])
        ->endRepeater()

    ->addTab('design_tab', ['label' => 'Design'])
        ->addColorPicker('bg_color', [
            'label' => 'Background Color',
            'default_value' => '#FFFFFF',
        ])
        ->addColorPicker('heading_color', [
            'label' => 'Heading Color',
            'default_value' => '#0B0B08',
        ])
        ->addColorPicker('intro_color', [
            'label' => 'Intro Text Color',
            'default_value' => '#4A4B37',
        ])
        ->addColorPicker('card_bg_color', [
            'label' => 'Card Background Color',
            'default_value' => '#F9FAFB',
        ])
        ->addColorPicker('card_title_color', [
            'label' => 'Card Title Color',
            'default_value' => '#0B0B08',
        ])
        ->addColorPicker('card_desc_color', [
            'label' => 'Card Description Color',
            'default_value' => '#4A4B37',
        ])
        ->addSelect('columns', [
            'label' => 'Grid Columns (Desktop)',
            'instructions' => 'Controls the maximum number of columns on larger screens.',
            'choices' => [
                '2' => '2 Columns',
                '3' => '3 Columns',
                '4' => '4 Columns',
            ],
            'default_value' => '3',
        ])

    ->addTab('layout_tab', ['label' => 'Layout'])
        ->addRepeater('padding_settings', [
            'label' => 'Padding Settings',
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

return $about_links_grid;
