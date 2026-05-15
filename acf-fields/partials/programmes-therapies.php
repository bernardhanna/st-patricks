<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

if (function_exists('acf_add_local_field_group')) {
    $programmes_therapies_fields = new FieldsBuilder('programmes_therapies_fields', [
        'title' => 'Programme or Therapy Fields',
    ]);

    $programmes_therapies_fields
        ->setLocation('post_type', '==', 'programmes_therapies')
        ->addTextarea('listing_summary', [
            'label' => 'Listing Summary',
            'instructions' => 'Short summary shown on archive cards. Falls back to the excerpt when empty.',
            'new_lines' => 'br',
            'rows' => 4,
        ]);

    acf_add_local_field_group($programmes_therapies_fields->build());
}
