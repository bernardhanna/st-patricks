<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$useful_links = new FieldsBuilder('useful_links', [
    'label' => 'Useful Links',
]);

$useful_links
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
            'default_value' => 'Useful links',
        ])
        ->addSelect('variant', [
            'label' => 'Link Style',
            'choices' => [
                'flexi' => 'Bordered links',
                'search' => 'Grid with arrows',
            ],
            'default_value' => 'flexi',
            'ui' => 1,
        ])
        ->addRepeater('links', [
            'label' => 'Links',
            'button_label' => 'Add Link',
            'layout' => 'row',
            'min' => 1,
        ])
            ->addLink('link', [
                'label' => 'Link',
                'return_format' => 'array',
            ])
        ->endRepeater()

    ->addTab('Design', ['label' => 'Design'])
        ->addColorPicker('background_color', [
            'label' => 'Background Color',
            'default_value' => '#E9E2F7',
        ])
        ->addColorPicker('heading_color', [
            'label' => 'Heading Color',
            'default_value' => '#1E244B',
        ])
        ->addColorPicker('link_color', [
            'label' => 'Link Color',
            'default_value' => '#1E244B',
        ]);

return $useful_links;
