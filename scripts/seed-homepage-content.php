<?php

/**
 * Replace homepage lorem ipsum with relevant SPMHS placeholder copy.
 * Preserves layout and approximate word counts for each text field.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-homepage-content.php
 */

require_once __DIR__ . '/lib/page-seed-conventions.php';

if (! function_exists('matrix_seed_home_replace_lorem')) {
    function matrix_seed_home_replace_lorem(string $text, string $replacement): string
    {
        if (stripos($text, 'lorem') === false) {
            return $text;
        }

        return $replacement;
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

$hero_descriptions = [
    '<p>Welcome to St Patrick\'s Mental Health Services, Ireland\'s largest independent not-for-profit provider of specialist mental healthcare, research and education.</p>',
    '<p>We support people through inpatient hospital care, community Dean Clinics, programmes, therapies and homecare tailored to each person\'s recovery journey.</p>',
    '<p>Our teams work to promote mental wellbeing, reduce stigma and deliver compassionate care across our hospitals, clinics and community services nationwide.</p>',
    '<p>Whether you need information, support or a referral, we are here to help people access the right mental health care at the right time.</p>',
];

$what_we_offer_descriptions = [
    'Inpatient Care' => '<p>Specialist hospital care at St Patrick\'s University Hospital and Lucan for adults and adolescents who need intensive mental health treatment and support.</p>',
    "St Patrick's at Home" => '<p>Mental healthcare delivered in your home when clinically appropriate, with specialist teams supporting recovery in familiar surroundings close to family.</p>',
    'Outpatient Care - Dean Clinics' => '<p>Community Dean Clinics across Ireland offering assessments, therapy and ongoing outpatient mental healthcare close to where you live and work.</p>',
    'Day Programmes' => '<p>Structured day programmes and therapies for conditions including depression, anxiety, psychosis, bipolar disorder and eating disorders across our services.</p>',
];

$about_us_card_text = [
    1 => 'Specialist treatment for addiction and co-occurring mental health conditions across our services.',
    2 => 'Programmes and therapies supporting adults and young people living with anxiety and related disorders.',
    3 => 'Education and support programmes helping people understand and manage bipolar affective disorder.',
    4 => 'Dedicated eating disorders programme with specialist multidisciplinary care and family support.',
    5 => 'Information and treatment options for personality disorders within our mental health services.',
    6 => 'Psychosis recovery programme supporting people experiencing schizophrenia and related conditions.',
];

$counter_items = [
    [
        'value' => '95',
        'suffix' => '%',
        'title' => 'Service quality',
        'description' => 'Committed to high standards of clinical care across all our mental health services.',
    ],
    [
        'value' => '75',
        'suffix' => 'K',
        'title' => 'People supported',
        'description' => 'Supporting thousands of people and families through care, education and advocacy each year.',
    ],
    [
        'value' => '455',
        'suffix' => '',
        'title' => 'Expert staff',
        'description' => 'Multidisciplinary teams of psychiatrists, nurses, therapists and support staff nationwide.',
    ],
];

$about_us_body = '<p>Welcome to St Patrick\'s Mental Health Services, Ireland\'s largest independent not-for-profit mental health provider. We deliver high-quality care, promote mental wellbeing, and advance research and education to improve lives across our hospitals, clinics and community services.</p>';

$child_safeguarding_body = '<p>We are committed to safeguarding children and young people who interact with our services. Our Child Safeguarding Statement sets out how we protect welfare, respond to concerns, and support safe care across St Patrick\'s Mental Health Services.</p>';

$news_events_body = '<p>Stay up to date with the latest news, events, blogs and media from St Patrick\'s Mental Health Services, including research updates, campaigns and community initiatives across Ireland.</p>';

$hero_rows = get_field('hero_content_blocks', $post_id);

if (is_array($hero_rows)) {
    foreach ($hero_rows as &$hero_row) {
        if (($hero_row['acf_fc_layout'] ?? '') !== 'hero_slider' || ! is_array($hero_row['slides'] ?? null)) {
            continue;
        }

        foreach ($hero_row['slides'] as $slide_index => &$slide) {
            if (! isset($hero_descriptions[$slide_index])) {
                continue;
            }

            $slide['description'] = matrix_seed_home_replace_lorem(
                (string) ($slide['description'] ?? ''),
                $hero_descriptions[$slide_index]
            );
        }
        unset($slide);
    }
    unset($hero_row);

    update_field('hero_content_blocks', $hero_rows, $post_id);
}

$rows = get_field('flexible_content_blocks', $post_id);

if (! is_array($rows) || $rows === []) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Homepage has no flexible content blocks.');
    }

    exit(1);
}

foreach ($rows as $index => &$row) {
    $layout = $row['acf_fc_layout'] ?? '';

    if ($layout === 'what_we_offer' && is_array($row['services'] ?? null)) {
        foreach ($row['services'] as &$service) {
            $title = (string) ($service['service_title'] ?? '');

            if (! isset($what_we_offer_descriptions[$title])) {
                continue;
            }

            $service['service_description'] = matrix_seed_home_replace_lorem(
                (string) ($service['service_description'] ?? ''),
                $what_we_offer_descriptions[$title]
            );
        }
        unset($service);
    }

    if ($layout === 'about_us') {
        for ($card = 1; $card <= 6; $card++) {
            $field = 'card_' . $card . '_text';

            if (! isset($about_us_card_text[$card])) {
                continue;
            }

            $row[$field] = matrix_seed_home_replace_lorem(
                (string) ($row[$field] ?? ''),
                $about_us_card_text[$card]
            );
        }
    }

    if ($layout === 'counters') {
        $row['counter_items'] = $counter_items;
    }

    if ($layout === 'content' && ($row['heading'] ?? '') === 'About us') {
        $row['content'] = matrix_seed_home_replace_lorem((string) ($row['content'] ?? ''), $about_us_body);
    }

    if ($layout === 'content' && ($row['heading'] ?? '') === 'Child Safeguarding') {
        $row['content'] = matrix_seed_home_replace_lorem((string) ($row['content'] ?? ''), $child_safeguarding_body);
    }

    if ($layout === 'content_two' && ($row['heading'] ?? '') === 'News and Events') {
        $row['description'] = matrix_seed_home_replace_lorem((string) ($row['description'] ?? ''), $news_events_body);
    }
}
unset($row);

update_field('flexible_content_blocks', $rows, $post_id);
update_post_meta($post_id, '_matrix_seed_key', 'homepage-content');

if (class_exists('WP_CLI')) {
    WP_CLI::success(
        sprintf(
            'Updated homepage placeholder content on page %d (%s).',
            $post_id,
            get_permalink($post_id)
        )
    );
}
