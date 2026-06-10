<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$inpatient_bed_vacancies = new FieldsBuilder('inpatient_bed_vacancies', [
    'label' => 'Inpatient Bed Vacancies',
]);

$inpatient_bed_vacancies
    ->addTab('Content', ['label' => 'Content'])
        ->addText('heading', [
            'label' => 'Heading',
            'default_value' => 'Current Inpatient Bed Vacancies',
        ])
        ->addSelect('heading_tag', [
            'label' => 'Heading Tag',
            'choices' => [
                'h1' => 'H1',
                'h2' => 'H2',
                'h3' => 'H3',
                'h4' => 'H4',
                'h5' => 'H5',
                'h6' => 'H6',
                'span' => 'Span',
                'p' => 'Paragraph',
            ],
            'default_value' => 'h2',
        ])
        ->addText('updated_text', [
            'label' => 'Updated Label',
            'instructions' => 'Shown beside the heading, e.g. Updated (30/02/2026).',
            'default_value' => 'Updated (30/02/2026)',
        ])
        ->addRepeater('vacancy_items', [
            'label' => 'Vacancy Rows',
            'instructions' => 'Add one row per inpatient location.',
            'button_label' => 'Add Vacancy Row',
            'layout' => 'row',
            'min' => 1,
        ])
            ->addNumber('bed_count', [
                'label' => 'Available Beds',
                'instructions' => 'Current bed count shown in the status box.',
                'min' => 0,
                'step' => 1,
                'default_value' => 0,
            ])
            ->addText('location_title', [
                'label' => 'Location Title',
                'default_value' => 'Adolescent Inpatient Bed Vacancies',
            ])
            ->addText('location_subtitle', [
                'label' => 'Location Subtitle',
                'instructions' => 'Shown in brackets after the title, e.g. Willow Grove.',
                'default_value' => 'Willow Grove',
            ])
            ->addTextarea('disclaimer', [
                'label' => 'Disclaimer',
                'instructions' => 'Supporting context shown on the right on desktop.',
                'rows' => 3,
                'new_lines' => '',
            ])
            ->addColorPicker('status_background_color', [
                'label' => 'Status Box Color',
                'default_value' => '#C3DBAE',
            ])
        ->endRepeater()

    ->addTab('Design', ['label' => 'Design'])
        ->addColorPicker('section_background_color', [
            'label' => 'Section Background Color',
            'default_value' => '#FBF8F3',
        ])
        ->addColorPicker('card_background_color', [
            'label' => 'Card Background Color',
            'default_value' => '#FFFFFF',
        ])
        ->addColorPicker('heading_color', [
            'label' => 'Heading Color',
            'default_value' => '#1E244B',
        ])
        ->addColorPicker('updated_color', [
            'label' => 'Updated Label Color',
            'default_value' => '#5F6478',
        ])
        ->addColorPicker('location_color', [
            'label' => 'Location Text Color',
            'default_value' => '#1E244B',
        ])
        ->addColorPicker('disclaimer_color', [
            'label' => 'Disclaimer Color',
            'default_value' => '#5F6478',
        ])
        ->addColorPicker('count_color', [
            'label' => 'Bed Count Color',
            'default_value' => '#1E244B',
        ])
        ->addColorPicker('beds_label_color', [
            'label' => 'Beds Label Color',
            'default_value' => '#1E244B',
        ])
        ->addColorPicker('underline_color', [
            'label' => 'Underline Color',
            'default_value' => '#6FC9C0',
        ])

    ->addTab('Layout', ['label' => 'Layout'])
        ->addRepeater('padding_settings', [
            'label' => 'Padding Settings',
            'instructions' => 'Customize padding for different screen sizes.',
            'button_label' => 'Add Screen Size Padding',
        ])
            ->addSelect('screen_size', [
                'label' => 'Screen Size',
                'choices' => [
                    'xxs' => 'xxs',
                    'xs' => 'xs',
                    'mob' => 'mob',
                    'sm' => 'sm',
                    'md' => 'md',
                    'lg' => 'lg',
                    'xl' => 'xl',
                    'xxl' => 'xxl',
                    'ultrawide' => 'ultrawide',
                ],
            ])
            ->addNumber('padding_top', [
                'label' => 'Padding Top',
                'min' => 0,
                'max' => 20,
                'step' => 0.01,
                'append' => 'rem',
            ])
            ->addNumber('padding_bottom', [
                'label' => 'Padding Bottom',
                'min' => 0,
                'max' => 20,
                'step' => 0.01,
                'append' => 'rem',
            ])
        ->endRepeater();

return $inpatient_bed_vacancies;
