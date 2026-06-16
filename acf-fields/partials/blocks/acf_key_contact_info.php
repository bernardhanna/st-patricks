<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$key_contact_info = new FieldsBuilder('key_contact_info', [
    'label' => 'Key Contact Information',
]);

$key_contact_info
    ->addTab('Content', ['label' => 'Content'])
        ->addRepeater('columns', [
            'label' => 'Columns',
            'instructions' => 'Add up to three columns of contact accordions.',
            'button_label' => 'Add Column',
            'layout' => 'block',
            'min' => 1,
            'max' => 3,
        ])
            ->addRepeater('items', [
                'label' => 'Accordion Items',
                'button_label' => 'Add Item',
                'layout' => 'block',
                'min' => 1,
            ])
                ->addText('title', [
                    'label' => 'Title',
                ])
                ->addTrueFalse('starts_open', [
                    'label' => 'Starts Open',
                    'ui' => 1,
                    'default_value' => 0,
                ])
                ->addRepeater('bullet_items', [
                    'label' => 'Bullet Items',
                    'button_label' => 'Add Bullet',
                    'layout' => 'table',
                ])
                    ->addText('label', [
                        'label' => 'Label',
                    ])
                ->endRepeater()
                ->addText('phone', [
                    'label' => 'Phone Number',
                ])
                ->addText('email', [
                    'label' => 'Email Address',
                ])
            ->endRepeater()
        ->endRepeater()

    ->addTab('Design', ['label' => 'Design'])
        ->addText('section_background', [
            'label' => 'Section Background / Gradient',
            'default_value' => '#FFFFFF',
        ])
        ->addText('closed_panel_background', [
            'label' => 'Closed Panel Background / Gradient',
            'default_value' => '#FBFAF7',
        ])
        ->addText('open_panel_background', [
            'label' => 'Open Panel Background / Gradient',
            'default_value' => 'linear-gradient(-79.46deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
        ]);

return $key_contact_info;
