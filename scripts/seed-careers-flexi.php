<?php

$post_id = 329;

$ensure_term = static function ($taxonomy, $name, $slug) {
    $existing = get_term_by('slug', $slug, $taxonomy);
    if ($existing instanceof WP_Term) {
        return (int) $existing->term_id;
    }

    $created = wp_insert_term($name, $taxonomy, ['slug' => $slug]);
    if (is_wp_error($created)) {
        WP_CLI::warning('Could not create term ' . $taxonomy . ':' . $slug . ' - ' . $created->get_error_message());

        return 0;
    }

    return (int) ($created['term_id'] ?? 0);
};

$department_admin = $ensure_term('career_department', 'Administration', 'administration');
$department_clinical = $ensure_term('career_department', 'Clinical', 'clinical');
$location_dublin = $ensure_term('career_location', 'Dublin', 'dublin');
$location_lucan = $ensure_term('career_location', 'Lucan', 'lucan');

$vacancies = [
    ['title' => 'Receptionist / Admin Support', 'area' => 'Dean Clinic', 'department' => $department_admin, 'location' => $location_dublin],
    ['title' => 'Clinical Nurse Manager', 'area' => 'St Patrick' . chr(39) . 's University Hospital (SPUH)', 'department' => $department_clinical, 'location' => $location_dublin],
    ['title' => 'Occupational Therapist', 'area' => 'St Patrick' . chr(39) . 's Hospital Lucan', 'department' => $department_clinical, 'location' => $location_lucan],
    ['title' => 'Healthcare Assistant', 'area' => 'Willow Grove Adolescent Unit', 'department' => $department_clinical, 'location' => $location_dublin],
    ['title' => 'Psychologist', 'area' => 'Dean Clinic', 'department' => $department_clinical, 'location' => $location_dublin],
    ['title' => 'Medical Secretary', 'area' => 'Dean Clinic', 'department' => $department_admin, 'location' => $location_lucan],
    ['title' => 'Social Worker', 'area' => 'St Patrick' . chr(39) . 's University Hospital (SPUH)', 'department' => $department_clinical, 'location' => $location_dublin],
    ['title' => 'Pharmacy Technician', 'area' => 'St Patrick' . chr(39) . 's Hospital Lucan', 'department' => $department_clinical, 'location' => $location_lucan],
    ['title' => 'Facilities Coordinator', 'area' => 'Willow Grove Adolescent Unit', 'department' => $department_admin, 'location' => $location_dublin],
    ['title' => 'Speech and Language Therapist', 'area' => 'Dean Clinic', 'department' => $department_clinical, 'location' => $location_dublin],
    ['title' => 'HR Administrator', 'area' => 'St Patrick' . chr(39) . 's University Hospital (SPUH)', 'department' => $department_admin, 'location' => $location_dublin],
    ['title' => 'Dietitian', 'area' => 'St Patrick' . chr(39) . 's Hospital Lucan', 'department' => $department_clinical, 'location' => $location_lucan],
];

foreach ($vacancies as $index => $vacancy) {
    $slug = sanitize_title($vacancy['title'] . '-' . ($index + 1));
    $existing = get_page_by_path($slug, OBJECT, 'careers');
    if ($existing instanceof WP_Post) {
        continue;
    }

    $career_id = wp_insert_post([
        'post_type' => 'careers',
        'post_status' => 'publish',
        'post_title' => $vacancy['title'],
        'post_name' => $slug,
        'post_content' => '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>',
        'post_excerpt' => 'Join our team in a rewarding healthcare role.',
    ], true);

    if (is_wp_error($career_id)) {
        WP_CLI::warning('Could not create career post: ' . $career_id->get_error_message());
        continue;
    }

    update_field('career_area', $vacancy['area'], $career_id);

    if ($vacancy['department'] > 0) {
        wp_set_object_terms($career_id, [(int) $vacancy['department']], 'career_department', false);
    }

    if ($vacancy['location'] > 0) {
        wp_set_object_terms($career_id, [(int) $vacancy['location']], 'career_location', false);
    }
}

$rows = get_field('flexible_content_blocks', $post_id);
if (! is_array($rows)) {
    $rows = [];
}

$rows = array_values(array_filter($rows, static function ($row) {
    return ($row['acf_fc_layout'] ?? '') !== 'careers_archive';
}));

$rows[] = [
    'acf_fc_layout' => 'careers_archive',
    'heading' => 'Current Vacancies',
    'heading_tag' => 'h2',
    'filter_label' => 'Filter by:',
    'department_placeholder' => 'Department',
    'location_placeholder' => 'Location',
    'apply_filters_label' => 'Apply filters',
    'search_placeholder' => 'Search vacancies',
    'search_button_label' => 'Search',
    'view_detail_label' => 'View detail',
    'posts_per_page' => 10,
    'empty_state_message' => 'No vacancies matched your filters.',
];

$updated = update_field('flexible_content_blocks', $rows, $post_id);

if (! $updated) {
    WP_CLI::error('Failed to update flexible content for page ' . $post_id);
}

flush_rewrite_rules(false);

WP_CLI::success('Seeded careers CPT, terms, posts, and careers_archive block on page ' . $post_id . '.');
