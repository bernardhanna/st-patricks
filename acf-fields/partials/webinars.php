<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

if (function_exists('acf_add_local_field_group')) {
    $webinar_fields = new FieldsBuilder('webinar_fields', [
        'title' => 'Webinar Fields',
    ]);

    $webinar_fields
        ->setLocation('post_type', '==', 'webinars')
        ->addDatePicker('webinar_date', [
            'label' => 'Webinar Date',
            'instructions' => 'Date shown on webinar archive cards.',
            'display_format' => 'd/m/Y',
            'return_format' => 'Ymd',
            'required' => 1,
        ])
        ->addTimePicker('webinar_time', [
            'label' => 'Webinar Time',
            'instructions' => 'Time shown on webinar archive cards.',
            'display_format' => 'g:i a',
            'return_format' => 'H:i:s',
            'required' => 1,
        ])
        ->addTextarea('webinar_summary', [
            'label' => 'Webinar Summary',
            'instructions' => 'Short summary shown on webinar archive cards.',
            'new_lines' => 'br',
            'rows' => 4,
        ]);

    acf_add_local_field_group($webinar_fields->build());
}
