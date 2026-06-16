<?php

/**
 * Wire homepage flexi blocks to relevant internal URLs.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-homepage-links.php
 */

require_once __DIR__ . '/lib/page-seed-conventions.php';
require_once __DIR__ . '/lib/care-treatment-seed.php';

if (! function_exists('matrix_seed_home_resolve_url')) {
    function matrix_seed_home_resolve_url(string $path): string
    {
        $path = trim($path, '/');
        $post_id = url_to_postid(home_url('/' . $path . '/'));

        if ($post_id > 0) {
            return (string) get_permalink($post_id);
        }

        $page_id = matrix_seed_resolve_page_id_by_path($path);

        if ($page_id > 0) {
            return (string) get_permalink($page_id);
        }

        return home_url('/' . $path . '/');
    }
}

if (! function_exists('matrix_seed_home_link')) {
    /**
     * @return array{title: string, url: string, target: string}
     */
    function matrix_seed_home_link(string $title, string $path): array
    {
        return [
            'title' => $title,
            'url' => matrix_seed_home_resolve_url($path),
            'target' => '',
        ];
    }
}

$post_id = (int) get_option('page_on_front');

if ($post_id === 0) {
    $post_id = (int) (get_page_by_path('home')?->ID ?? 0);
}

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find the homepage.');
    }

    exit(1);
}

$rows = get_field('flexible_content_blocks', $post_id);

if (! is_array($rows) || $rows === []) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Homepage has no flexible content blocks.');
    }

    exit(1);
}

$what_we_offer_links = [
    'Inpatient Care' => matrix_seed_home_link('Inpatient Care', 'inpatient-hospital-care'),
    "St Patrick's at Home" => matrix_seed_home_link("St Patrick's at Home", 'care-treatment/homecare-service'),
    'Outpatient Care - Dean Clinics' => matrix_seed_home_link('Outpatient Care - Dean Clinics', 'outpatient-clinics/about-the-dean-clinics'),
    'Day Programmes' => matrix_seed_home_link('Day Programmes', 'programmes-therapies'),
];

$about_us_card_links = [
    1 => matrix_seed_home_link('Addiction & Dual Diagnosis', 'care-treatment/addiction-and-dual-diagnosis'),
    2 => matrix_seed_home_link('Anxiety', 'care-treatment/anxiety-disorders-programme'),
    3 => matrix_seed_home_link('Bipolar Disorder', 'care-treatment/bipolar-education-programme'),
    4 => matrix_seed_home_link('Eating Disorders', 'care-treatment/eating-disorders-programme'),
    5 => matrix_seed_home_link('Personality Disorders', 'mental-health'),
    6 => matrix_seed_home_link('Schizophrenia & Psychosis', 'care-treatment/psychosis-recovery-programme'),
];

$child_safeguarding_url = matrix_seed_home_resolve_url('about-us/policies-and-publications/child-safeguarding-statement');
if (! url_to_postid($child_safeguarding_url)) {
    $child_safeguarding_url = matrix_seed_home_resolve_url('about-us/policies-and-publications');
}

foreach ($rows as $index => &$row) {
    $layout = $row['acf_fc_layout'] ?? '';

    if ($layout === 'what_we_offer' && is_array($row['services'] ?? null)) {
        foreach ($row['services'] as &$service) {
            $title = (string) ($service['service_title'] ?? '');

            if (isset($what_we_offer_links[$title])) {
                $service['service_link'] = $what_we_offer_links[$title];
            }
        }
        unset($service);
    }

    if ($layout === 'about_us') {
        $row['view_more_link'] = matrix_seed_home_link('View more', 'service-users-and-visitors/about-mental-health');

        for ($card = 1; $card <= 6; $card++) {
            if (isset($about_us_card_links[$card])) {
                $row['card_' . $card . '_link'] = $about_us_card_links[$card];
            }
        }
    }

    if ($layout === 'content' && ($row['heading'] ?? '') === 'Child Safeguarding') {
        $row['primary_button'] = [
            'title' => 'Child Safeguarding Statement',
            'url' => $child_safeguarding_url,
            'target' => '',
        ];
    }

    if ($layout === 'content_two' && ($row['heading'] ?? '') === 'News and Events') {
        $row['button'] = matrix_seed_home_link('News and events', 'news-and-events');
    }
}
unset($row);

update_field('flexible_content_blocks', $rows, $post_id);
update_post_meta($post_id, '_matrix_seed_key', 'homepage-links');

if (class_exists('WP_CLI')) {
    WP_CLI::success(
        sprintf(
            'Updated homepage links on page %d (%s).',
            $post_id,
            get_permalink($post_id)
        )
    );
}
