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
        ]);

matrix_acf_append_shared_vertical_padding_fields($programmes_therapies_archive, ['add_design_tab' => true]);

return $programmes_therapies_archive;
