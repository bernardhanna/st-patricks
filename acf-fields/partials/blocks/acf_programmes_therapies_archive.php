<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$defaults = matrix_get_programmes_therapies_archive_defaults();

$programmes_therapies_archive = new FieldsBuilder('programmes_therapies_archive', [
    'label' => 'Programmes and Therapies Archive',
]);

$programmes_therapies_archive
    ->addTab('Content', ['label' => 'Content'])
        ->addText('heading', [
            'label' => 'Heading',
            'default_value' => $defaults['heading'],
        ])
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
        ->addNumber('posts_per_page', [
            'label' => 'Posts Per Page',
            'default_value' => $defaults['posts_per_page'],
            'min' => 1,
            'max' => 24,
            'step' => 1,
        ])
        ->addText('empty_state_message', [
            'label' => 'Empty State Message',
            'default_value' => $defaults['empty_state_message'],
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

return $programmes_therapies_archive;
