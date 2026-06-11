<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$researchProjectsFields = new FieldsBuilder('research_projects_fields');

$researchProjectsFields
    ->addGroup('research_projects_settings', [
        'label' => 'Research Projects Archive',
    ])
    ->addImage('hero_background_image', [
        'label' => 'Hero Background Image',
        'instructions' => 'Upload a hero background for the research projects archive. Falls back to the blog hero image when blank.',
        'return_format' => 'array',
        'preview_size' => 'medium',
    ])
    ->addSelect('hero_heading_tag', [
        'label' => 'Hero Heading Tag',
        'choices' => [
            'h1' => '<h1>',
            'h2' => '<h2>',
            'h3' => '<h3>',
            'h4' => '<h4>',
            'h5' => '<h5>',
            'h6' => '<h6>',
            'span' => '<span>',
            'p' => '<p>',
        ],
        'default_value' => 'h1',
        'ui' => 1,
    ])
    ->addText('hero_heading_text', [
        'label' => 'Hero Heading Text',
        'default_value' => 'Research Projects',
    ])
    ->addTextarea('hero_subheading_text', [
        'label' => 'Hero Sub-heading Text',
        'rows' => 3,
        'default_value' => 'Explore current and past research projects from St Patrick\'s Mental Health Services.',
    ])
    ->addText('filter_section_title', [
        'label' => 'Filter Section Title',
        'default_value' => 'Filter by:',
    ])
    ->endGroup();

return $researchProjectsFields;
