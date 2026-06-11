<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$research_project_fields = new FieldsBuilder('research_project_fields', [
    'title' => 'Research Project Fields',
]);

$research_project_fields
    ->setLocation('post_type', '==', 'research_projects')
    ->addText('post_author_name', [
        'label' => 'Author Name',
        'instructions' => 'Optional override for the published-by label on the single template.',
    ]);

acf_add_local_field_group($research_project_fields->build());
