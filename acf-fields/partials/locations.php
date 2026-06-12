<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

if (function_exists('acf_add_local_field_group')) {
    $locations_fields = new FieldsBuilder('locations_fields', [
        'title' => 'Location Fields',
    ]);

    $locations_fields
        ->setLocation('post_type', '==', 'locations')
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
        ]);

    acf_add_local_field_group($locations_fields->build());
}
