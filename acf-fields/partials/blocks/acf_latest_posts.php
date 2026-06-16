<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$latest_posts = new FieldsBuilder('latest_posts', [
    'label' => 'Latest Posts',
]);

$latest_posts
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
            'default_value' => 'Latest News, Events, and Expert advice from SPMHS',
        ])
        ->addTaxonomy('selected_categories', [
            'label' => 'Selected Blog Categories',
            'taxonomy' => 'category',
            'field_type' => 'multi_select',
            'return_format' => 'id',
            'allow_null' => 1,
            'multiple' => 1,
        ])
        ->addLink('header_button_link', [
            'label' => 'Header Button Link',
            'return_format' => 'array',
        ])
        ->addText('empty_state_message', [
            'label' => 'Empty State Message',
            'default_value' => 'No posts are available yet.',
        ])

    ->addTab('Design', ['label' => 'Design'])
        ->addColorPicker('background_color', [
            'label' => 'Background Color',
            'default_value' => '#FBFAF7',
        ])
        ->addColorPicker('heading_color', [
            'label' => 'Heading Color',
            'default_value' => '#1E244B',
        ])
        ->addColorPicker('card_title_color', [
            'label' => 'Card Title Color',
            'default_value' => '#1E244B',
        ]);

return $latest_posts;
