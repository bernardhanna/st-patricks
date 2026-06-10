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
        ->addRepeater('cards', [
            'label' => 'Cards',
            'instructions' => 'Add each research card in display order.',
            'button_label' => 'Add Card',
            'layout' => 'row',
            'min' => 1,
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

return $research_cards_grid;
