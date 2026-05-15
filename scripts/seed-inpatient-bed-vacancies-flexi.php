<?php

$post_id = 329;
$rows = get_field('flexible_content_blocks', $post_id);

if (! is_array($rows)) {
    $rows = [];
}

$rows[] = [
    'acf_fc_layout' => 'inpatient_bed_vacancies',
    'heading' => 'Current Inpatient Bed Vacancies',
    'heading_tag' => 'h2',
    'updated_text' => 'Updated (30/02/2026)',
    'vacancy_items' => [
        [
            'bed_count' => 0,
            'location_title' => 'Adolescent Inpatient Bed Vacancies',
            'location_subtitle' => 'Willow Grove',
            'disclaimer' => 'additional context would be required to clarify that available beds may not be immediately accessible.',
            'status_background_color' => '#C3DBAE',
        ],
    ],
    'section_background_color' => '#FBF8F3',
    'card_background_color' => '#FFFFFF',
    'heading_color' => '#1E244B',
    'updated_color' => '#5F6478',
    'location_color' => '#1E244B',
    'disclaimer_color' => '#5F6478',
    'count_color' => '#1E244B',
    'beds_label_color' => '#1E244B',
    'underline_color' => '#6FC9C0',
];

$updated = update_field('flexible_content_blocks', $rows, $post_id);

if (! $updated) {
    WP_CLI::error('Failed to update flexible content for page ' . $post_id);
}

WP_CLI::success('Added inpatient_bed_vacancies block to page ' . $post_id . '.');
