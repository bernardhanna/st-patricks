<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

if (function_exists('acf_add_local_field_group')) {
    $career_fields = new FieldsBuilder('career_fields', [
        'title' => 'Career Fields',
    ]);

    $career_fields
        ->setLocation('post_type', '==', 'careers')
        ->addText('career_area', [
            'label' => 'Area',
            'instructions' => 'Facility or service area shown in the vacancies table, e.g. Dean Clinic.',
            'required' => 1,
        ]);

    acf_add_local_field_group($career_fields->build());
}
