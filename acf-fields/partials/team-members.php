<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

if (function_exists('acf_add_local_field_group')) {
    $team_member_fields = new FieldsBuilder('team_member_fields', [
        'title' => 'Team Member Fields',
    ]);

    $team_member_fields
        ->setLocation('post_type', '==', 'team_members')
        ->addText('job_title', [
            'label' => 'Job Title',
            'instructions' => 'Short role label shown on team cards.',
        ])
        ->addWysiwyg('profile_teaser', [
            'label' => 'Profile Teaser',
            'instructions' => 'Short teaser used in the spokespeople card layout.',
            'tabs' => 'all',
            'toolbar' => 'basic',
            'media_upload' => 0,
        ]);

    acf_add_local_field_group($team_member_fields->build());
}
