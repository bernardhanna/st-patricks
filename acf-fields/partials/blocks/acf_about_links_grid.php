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
        ->addSelect('layout_style', [
            'label' => 'Layout Style',
            'instructions' => 'Image feature matches the About Us landing grid (Figma 2780:3457). Layout 2 uses a flush full-width image with a title-only footer (Figma 3279:18939, careers useful links). Compact row is a horizontal thumbnail alternative.',
            'choices' => [
                'image_feature' => 'Layout 1 (image feature)',
                'flush_image' => 'Layout 2 (flush image)',
                'compact_row' => 'Compact Row (thumbnail + title)',
            ],
            'default_value' => 'image_feature',
            'ui' => 1,
        ])
        ->addColorPicker('bg_color', [
            'label' => 'Background Color',
            'default_value' => '#FFFFFF',
        ])
        ->addColorPicker('heading_color', [
            'label' => 'Heading Color',
            'default_value' => '#1E244B',
        ])
        ->addColorPicker('intro_color', [
            'label' => 'Intro Text Color',
            'default_value' => '#08284B',
        ])
        ->addColorPicker('card_bg_color', [
            'label' => 'Card Background Color',
            'default_value' => '#F9FAFB',
        ])
        ->addColorPicker('card_title_color', [
            'label' => 'Card Title Color',
            'default_value' => '#1E244B',
        ])
        ->addColorPicker('card_desc_color', [
            'label' => 'Card Description Color',
            'default_value' => '#08284B',
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
        ]);

return $about_links_grid;
