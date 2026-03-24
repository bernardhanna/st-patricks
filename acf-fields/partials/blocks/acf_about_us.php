<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$about_us = new FieldsBuilder('about_us', [
    'label' => 'About Us / About Mental Health',
]);

$about_us
    ->addTab('Content', ['label' => 'Content'])
        ->addText('heading', [
            'label' => 'Section Heading',
            'instructions' => 'Main heading, e.g. "About Mental Health".',
            'default_value' => 'About Mental Health',
            'required' => 1,
        ])
        ->addSelect('heading_tag', [
            'label' => 'Heading Tag',
            'choices' => [
                'h1'  => 'H1',
                'h2'  => 'H2',
                'h3'  => 'H3',
                'h4'  => 'H4',
                'h5'  => 'H5',
                'h6'  => 'H6',
                'p'   => 'Paragraph',
                'span'=> 'Span',
            ],
            'default_value' => 'h2',
            'required' => 1,
        ])
        ->addImage('main_image', [
            'label' => 'Center Illustration Image',
            'instructions' => 'Image shown between the left and right card columns.',
            'return_format' => 'array',
            'preview_size' => 'medium',
        ])
        ->addLink('view_more_link', [
            'label' => '"View more" Button Link',
            'instructions' => 'Controls the call-to-action button below the cards.',
            'return_format' => 'array',
        ])

        // Cards 1–6 (no repeater, no icon fields)
        ->addText('card_1_title', [
            'label' => 'Card 1 Title',
            'default_value' => 'Addiction & Dual Diagnosis',
        ])
        ->addTextarea('card_1_text', [
            'label' => 'Card 1 Text',
            'new_lines' => 'br',
            'default_value' => 'Lorem ipsum dolor sit amet, cons etetur sadipscing elitr tempor.',
        ])
        ->addLink('card_1_link', [
            'label' => 'Card 1 Link',
            'instructions' => 'Whole card becomes clickable.',
            'return_format' => 'array',
        ])

        ->addText('card_2_title', [
            'label' => 'Card 2 Title',
            'default_value' => 'Anxiety',
        ])
        ->addTextarea('card_2_text', [
            'label' => 'Card 2 Text',
            'new_lines' => 'br',
            'default_value' => 'Lorem ipsum dolor sit amet, cons etetur sadipscing elitr tempor.',
        ])
        ->addLink('card_2_link', [
            'label' => 'Card 2 Link',
            'return_format' => 'array',
        ])

        ->addText('card_3_title', [
            'label' => 'Card 3 Title',
            'default_value' => 'Bipolar Disorder',
        ])
        ->addTextarea('card_3_text', [
            'label' => 'Card 3 Text',
            'new_lines' => 'br',
            'default_value' => 'Lorem ipsum dolor sit amet, cons etetur sadipscing elitr tempor.',
        ])
        ->addLink('card_3_link', [
            'label' => 'Card 3 Link',
            'return_format' => 'array',
        ])

        ->addText('card_4_title', [
            'label' => 'Card 4 Title',
            'default_value' => 'Eating Disorders',
        ])
        ->addTextarea('card_4_text', [
            'label' => 'Card 4 Text',
            'new_lines' => 'br',
            'default_value' => 'Lorem ipsum dolor sit amet, cons etetur sadipscing elitr tempor.',
        ])
        ->addLink('card_4_link', [
            'label' => 'Card 4 Link',
            'return_format' => 'array',
        ])

        ->addText('card_5_title', [
            'label' => 'Card 5 Title',
            'default_value' => 'Personality Disorders',
        ])
        ->addTextarea('card_5_text', [
            'label' => 'Card 5 Text',
            'new_lines' => 'br',
            'default_value' => 'Lorem ipsum dolor sit amet, cons etetur sadipscing elitr tempor.',
        ])
        ->addLink('card_5_link', [
            'label' => 'Card 5 Link',
            'return_format' => 'array',
        ])

        ->addText('card_6_title', [
            'label' => 'Card 6 Title',
            'default_value' => 'Schizophrenia & Psychosis',
        ])
        ->addTextarea('card_6_text', [
            'label' => 'Card 6 Text',
            'new_lines' => 'br',
            'default_value' => 'Lorem ipsum dolor sit amet, cons etetur sadipscing elitr tempor.',
        ])
        ->addLink('card_6_link', [
            'label' => 'Card 6 Link',
            'return_format' => 'array',
        ])

    ->addTab('Design', ['label' => 'Design'])
        ->addColorPicker('background_color', [
            'label' => 'Section Background Color',
            'instructions' => 'Background color for the section.',
            'default_value' => '#FFFFFF',
        ])

    ->addTab('Layout', ['label' => 'Layout'])
        ->addRepeater('padding_settings', [
            'label' => 'Padding Settings',
            'instructions' => 'Customize padding for different screen sizes.',
            'button_label' => 'Add Screen Size Padding',
            'layout' => 'table',
        ])
            ->addSelect('screen_size', [
                'label' => 'Screen Size',
                'instructions' => 'Select the screen size for this padding setting.',
                'choices' => [
                    'xxs'       => 'XXS (320px+)',
                    'xs'        => 'XS (480px+)',
                    'mob'       => 'Mobile (575px+)',
                    'sm'        => 'Small (640px+)',
                    'md'        => 'Medium (768px+)',
                    'lg'        => 'Large (1100px+)',
                    'xl'        => 'XL (1280px+)',
                    'xxl'       => 'XXL (1440px+)',
                    'ultrawide' => 'Ultrawide (1920px+)',
                ],
            ])
            ->addNumber('padding_top', [
                'label' => 'Padding Top',
                'instructions' => 'Set the top padding in rem.',
                'min' => 0,
                'max' => 20,
                'step' => 0.1,
                'append' => 'rem',
                'default_value' => 6,
            ])
            ->addNumber('padding_bottom', [
                'label' => 'Padding Bottom',
                'instructions' => 'Set the bottom padding in rem.',
                'min' => 0,
                'max' => 20,
                'step' => 0.1,
                'append' => 'rem',
                'default_value' => 6,
            ])
        ->endRepeater();

return $about_us;
