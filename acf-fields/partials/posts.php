<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

if (function_exists('acf_add_local_field_group')) {
    $post_fields = new FieldsBuilder('post_fields', [
        'title' => 'Post Fields',
    ]);

    $post_fields
        ->setLocation('post_type', '==', 'post')
        ->addText('post_author_name', [
            'label' => 'Author Display Name',
            'instructions' => 'Optional override for the published-by label on single posts.',
        ])
        ->addTab('Event settings', [
            'label' => 'Event settings',
            'instructions' => 'Used when this post is in the Events category.',
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
            'label' => 'Link Thumbnail Directly To External URL',
            'instructions' => 'When enabled, the archive/index thumbnail links straight to the external event URL instead of this post.',
            'ui' => 1,
            'default_value' => 0,
        ]);

    acf_add_local_field_group($post_fields->build());
}
