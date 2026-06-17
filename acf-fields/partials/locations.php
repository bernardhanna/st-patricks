<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

if (function_exists('acf_add_local_field_group')) {
    $locations_fields = new FieldsBuilder('locations_fields', [
        'title' => 'Location Fields',
    ]);

    $locations_fields
        ->setLocation('post_type', '==', 'locations')
        ->addTab('Listing', ['label' => 'Listing'])
        ->addImage('card_image', [
            'label' => 'Card Image',
            'instructions' => 'Image used in location grids. Falls back to the featured image when empty.',
            'return_format' => 'array',
            'preview_size' => 'medium',
        ])
        ->addTextarea('listing_summary', [
            'label' => 'Listing Summary',
            'instructions' => 'Short summary for cards and listings. Falls back to the excerpt when empty.',
            'new_lines' => 'br',
            'rows' => 3,
        ])
        ->addTab('Contact & Map', ['label' => 'Contact & Map'])
        ->addTextarea('address', [
            'label' => 'Address',
            'instructions' => 'Full postal address shown on contact and map panels.',
            'new_lines' => 'br',
            'rows' => 3,
        ])
        ->addText('phone', [
            'label' => 'Phone Number',
        ])
        ->addEmail('email', [
            'label' => 'Email Address',
        ])
        ->addNumber('latitude', [
            'label' => 'Latitude',
            'instructions' => 'Map marker latitude.',
            'step' => 0.000001,
        ])
        ->addNumber('longitude', [
            'label' => 'Longitude',
            'instructions' => 'Map marker longitude.',
            'step' => 0.000001,
        ])
        ->addTrueFalse('show_on_contact_map', [
            'label' => 'Show on Contact Map',
            'instructions' => 'Include this location on the Contact Us map section.',
            'default_value' => 1,
            'ui' => 1,
        ])
        ->addRepeater('opening_hours', [
            'label' => 'Opening Hours',
            'instructions' => 'Optional rows for days and hours (e.g. Mon - Fri / 09:00 - 17:00).',
            'button_label' => 'Add Hours Row',
            'layout' => 'table',
        ])
            ->addText('day_label', [
                'label' => 'Days',
            ])
            ->addText('hours', [
                'label' => 'Hours',
            ])
        ->endRepeater();

    acf_add_local_field_group($locations_fields->build());
}
