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
        ])
        ->addWysiwyg('career_job_description', [
            'label' => 'Job description',
            'instructions' => 'Full job detail shown on the vacancy page. Falls back to post content when empty.',
            'tabs' => 'all',
            'toolbar' => 'full',
            'media_upload' => 0,
        ])
        ->addTrueFalse('career_show_application_form', [
            'label' => 'Show application form',
            'instructions' => 'Display the careers application form beneath the job description.',
            'default_value' => 0,
            'ui' => 1,
        ])
        ->addEmail('career_application_email', [
            'label' => 'Application recipient email',
            'instructions' => 'Optional override for application notifications. Defaults to hr@stpatricks.ie.',
        ]);

    acf_add_local_field_group($career_fields->build());
}
