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

$type_programmes = $ensure_term('programme_therapy_type', 'Programmes', 'programmes');
$type_therapies = $ensure_term('programme_therapy_type', 'Therapies', 'therapies');

$care_inpatient = $ensure_term('care_setting', 'Inpatient programme', 'inpatient-programme');
$care_day = $ensure_term('care_setting', 'Day patient programme', 'day-patient-programme');
$care_homecare = $ensure_term('care_setting', 'Homecare programme', 'homecare-programme');

$delivery_hybrid = $ensure_term('delivery_format', 'Hybrid', 'hybrid');
$delivery_online = $ensure_term('delivery_format', 'Online', 'online');
$delivery_in_person = $ensure_term('delivery_format', 'In person', 'in-person');

$entries = [
  ['title' => 'Acceptance & Commitment Therapy (ACT)', 'type' => $type_therapies, 'care' => $care_day, 'delivery' => $delivery_hybrid],
  ['title' => 'Cognitive Behavioural Therapy (CBT)', 'type' => $type_therapies, 'care' => $care_inpatient, 'delivery' => $delivery_in_person],
  ['title' => 'Dialectical Behaviour Therapy (DBT)', 'type' => $type_therapies, 'care' => $care_day, 'delivery' => $delivery_online],
  ['title' => 'Mindfulness-Based Stress Reduction', 'type' => $type_therapies, 'care' => $care_homecare, 'delivery' => $delivery_hybrid],
  ['title' => 'Adolescent Inpatient Programme', 'type' => $type_programmes, 'care' => $care_inpatient, 'delivery' => $delivery_in_person],
  ['title' => 'Adult Day Programme', 'type' => $type_programmes, 'care' => $care_day, 'delivery' => $delivery_hybrid],
  ['title' => 'Homecare Recovery Programme', 'type' => $type_programmes, 'care' => $care_homecare, 'delivery' => $delivery_online],
  ['title' => 'Eating Disorders Programme', 'type' => $type_programmes, 'care' => $care_inpatient, 'delivery' => $delivery_hybrid],
  ['title' => 'Addiction Recovery Programme', 'type' => $type_programmes, 'care' => $care_day, 'delivery' => $delivery_in_person],
  ['title' => 'Trauma-Focused Therapy', 'type' => $type_therapies, 'care' => $care_homecare, 'delivery' => $delivery_online],
  ['title' => 'Family Therapy Programme', 'type' => $type_programmes, 'care' => $care_day, 'delivery' => $delivery_hybrid],
  ['title' => 'Psychosis Recovery Programme', 'type' => $type_programmes, 'care' => $care_inpatient, 'delivery' => $delivery_online],
];

$summary = 'Our programme can help you to deal with thoughts and emotions by connecting with your values and learning to be present.';

foreach ($entries as $entry) {
    $existing = get_page_by_title($entry['title'], OBJECT, 'programmes_therapies');
    if ($existing instanceof WP_Post) {
        $post_id_item = (int) $existing->ID;
    } else {
        $post_id_item = wp_insert_post([
            'post_type' => 'programmes_therapies',
            'post_status' => 'publish',
            'post_title' => $entry['title'],
            'post_content' => '<p>' . esc_html($summary) . '</p>',
            'post_excerpt' => $summary,
        ], true);

        if (is_wp_error($post_id_item)) {
            WP_CLI::warning('Could not create post: ' . $entry['title']);

            continue;
        }
    }

    wp_set_object_terms($post_id_item, [(int) $entry['type']], 'programme_therapy_type', false);
    wp_set_object_terms($post_id_item, [(int) $entry['care']], 'care_setting', false);
    wp_set_object_terms($post_id_item, [(int) $entry['delivery']], 'delivery_format', false);
    update_field('listing_summary', $summary, $post_id_item);

    WP_CLI::log('Ensured programme/therapy: ' . $entry['title']);
}

$rows = get_field('flexible_content_blocks', $post_id);
if (! is_array($rows)) {
    $rows = [];
}

$rows[] = [
    'acf_fc_layout' => 'programmes_therapies_archive',
    'heading' => 'Select a programme or therapy',
    'heading_tag' => 'h2',
    'posts_per_page' => 10,
    'empty_state_message' => 'No programmes or therapies matched your filters.',
];

$updated = update_field('flexible_content_blocks', $rows, $post_id);

if (! $updated) {
    WP_CLI::error('Failed to update flexible content for page ' . $post_id);
}

flush_rewrite_rules(false);

WP_CLI::success('Seeded programmes_therapies CPT, terms, posts, and archive block on page ' . $post_id . '.');
