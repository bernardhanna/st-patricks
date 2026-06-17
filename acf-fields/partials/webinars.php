<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

if (function_exists('acf_add_local_field_group')) {
    $webinar_fields = new FieldsBuilder('webinar_fields', [
        'title' => 'Webinar Fields',
    ]);

    $webinar_fields
        ->setLocation('post_type', '==', 'webinars')
        ->addText('post_author_name', [
            'label' => 'Author Display Name',
            'instructions' => 'Optional override for the published-by label on single webinar pages.',
        ])
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
            'instructions' => 'Short summary shown on webinar archive cards and the single page hero.',
            'new_lines' => 'br',
            'rows' => 4,
        ])
        ->addTab('Event settings', [
            'label' => 'Event settings',
            'instructions' => 'Optional CTA box and external booking link on the single page.',
        ])
        ->addUrl('event_external_url', [
            'label' => 'External Event URL',
            'instructions' => 'Optional link to Eventbrite or another external booking page.',
        ])
        ->addText('event_external_button_label', [
            'label' => 'External Button Label',
            'instructions' => 'Label for the external event button on the single event layout.',
            'default_value' => 'Link to an Eventbrite',
        ])
        ->addWysiwyg('event_cta_summary', [
            'label' => 'Event CTA Summary',
            'instructions' => 'Short summary shown in the highlighted event box above the image. Supports links.',
            'tabs' => 'all',
            'toolbar' => 'basic',
            'media_upload' => 0,
        ])
        ->addTrueFalse('event_link_external_from_archive', [
            'label' => 'Link Archive Cards To External URL',
            'instructions' => 'When enabled, webinars archive cards open the external event URL directly instead of this single page.',
            'ui' => 1,
            'default_value' => 0,
        ]);

    acf_add_local_field_group($webinar_fields->build());
}
