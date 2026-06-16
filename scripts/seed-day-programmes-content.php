<?php

/**
 * Seed Day Programmes page content and all programmes_therapies CPT entries
 * from scraped stpatricks.ie HTML.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-day-programmes-content.php
 */

require_once get_template_directory() . '/inc/migrate-functions.php';

$theme_dir = get_template_directory();
$html_dir = $theme_dir . '/old/html';

if (! function_exists('matrix_seed_programme_ensure_term')) {
    function matrix_seed_programme_ensure_term(string $taxonomy, string $name, string $slug): int
    {
        $existing = get_term_by('slug', $slug, $taxonomy);

        if ($existing instanceof WP_Term) {
            return (int) $existing->term_id;
        }

        $created = wp_insert_term($name, $taxonomy, ['slug' => $slug]);

        if (is_wp_error($created)) {
            if (class_exists('WP_CLI')) {
                WP_CLI::warning('Could not create term ' . $taxonomy . ':' . $slug);
            }

            return 0;
        }

        return (int) ($created['term_id'] ?? 0);
    }
}

if (! function_exists('matrix_seed_programme_html_file')) {
    function matrix_seed_programme_html_file(string $html_dir, string $slug): string
    {
        $candidates = [
            $html_dir . '/original_https_www.stpatricks.ie_care-treatment_programmes-therapies_our-programmes-and-therapies_' . $slug . '.html',
        ];

        foreach ($candidates as $candidate) {
            if (is_readable($candidate)) {
                return $candidate;
            }
        }

        return '';
    }
}

if (! function_exists('matrix_seed_programme_is_therapy')) {
    function matrix_seed_programme_is_therapy(string $slug): bool
    {
        $therapy_slugs = [
            'acceptance-commitment-therapy-act',
            'art-therapy',
            'cognitive-behavioural-therapy',
            'cognitive-behavioural-therapy-for-insomnia-cbt-i',
            'compassion-focused-therapy',
            'compassion-focused-therapy-eating-disorders',
            'compassion-focused-therapy-for-psychosis',
            'dialectical-behavioural-therapy',
            'electroconvulsive-therapy',
        ];

        return in_array($slug, $therapy_slugs, true);
    }
}

if (! function_exists('matrix_seed_programme_infer_delivery')) {
    function matrix_seed_programme_infer_delivery(string $text): string
    {
        $text = strtolower($text);
        $has_online = (bool) preg_match('/\b(online|microsoft teams|remotely|remote)\b/', $text);
        $has_in_person = (bool) preg_match('/\b(in[- ]person|onsite|on-site|on site)\b/', $text);
        $has_hybrid = (bool) preg_match('/\b(hybrid|combination of in[- ]person and online)\b/', $text);

        if ($has_hybrid || ($has_online && $has_in_person)) {
            return 'hybrid';
        }

        if ($has_online) {
            return 'online';
        }

        if ($has_in_person) {
            return 'in-person';
        }

        return 'hybrid';
    }
}

if (! function_exists('matrix_seed_programme_parse_archive')) {
    /**
     * @return array<string, array{title: string, summary: string, care: string[], image: string}>
     */
    function matrix_seed_programme_parse_archive(string $html): array
    {
        $dom = matrix_migrate_parse_html_document($html);

        if (! $dom instanceof DOMDocument) {
            return [];
        }

        $xpath = new DOMXPath($dom);
        $pods = $xpath->query('//section[contains(@class,"day-programme-pods")]//div[contains(@class,"pod")]');
        $entries = [];

        if ($pods === false) {
            return [];
        }

        foreach ($pods as $pod) {
            if (! $pod instanceof DOMElement) {
                continue;
            }

            $link = $xpath->query('.//a[@href]', $pod)->item(0);

            if (! $link instanceof DOMElement) {
                continue;
            }

            $href = trim((string) $link->getAttribute('href'));
            $slug = basename(rtrim($href, '/'));

            if ($slug === '' || $slug === 'our-programmes-and-therapies') {
                continue;
            }

            $title = matrix_migrate_dom_text($xpath->query('.//h2', $pod)->item(0));
            $summary = matrix_migrate_dom_text($xpath->query('.//p', $pod)->item(0));
            $care = [];

            foreach ($xpath->query('.//span[contains(@class,"access")]', $pod) as $access_node) {
                $label = trim(preg_replace('/\s+/', ' ', matrix_migrate_dom_text($access_node)));

                if ($label !== '') {
                    $care[] = $label;
                }
            }

            $image = '';
            $img = $xpath->query('.//img/@src', $pod)->item(0);

            if ($img instanceof DOMNode) {
                $image = matrix_migrate_live_url((string) $img->nodeValue);
            }

            $entries[$slug] = [
                'title' => html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'summary' => html_entity_decode($summary, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'care' => $care,
                'image' => $image,
            ];
        }

        return $entries;
    }
}

if (! function_exists('matrix_seed_programme_care_term_ids')) {
    /**
     * @param string[] $care_labels
     * @return int[]
     */
    function matrix_seed_programme_care_term_ids(array $care_labels): array
    {
        $map = [
            'Inpatient programme' => 'inpatient-programme',
            'Day patient programme' => 'day-patient-programme',
            'Homecare programme' => 'homecare-programme',
        ];

        $ids = [];

        foreach ($care_labels as $label) {
            $slug = $map[$label] ?? '';

            if ($slug === '') {
                continue;
            }

            $term_id = matrix_seed_programme_ensure_term('care_setting', $label, $slug);

            if ($term_id > 0) {
                $ids[] = $term_id;
            }
        }

        return $ids;
    }
}

if (! function_exists('matrix_seed_programme_extract_supplements')) {
    function matrix_seed_programme_extract_supplements(string $html): string
    {
        $dom = matrix_migrate_parse_html_document($html);

        if (! $dom instanceof DOMDocument) {
            return '';
        }

        $xpath = new DOMXPath($dom);
        $parts = [];

        foreach ($xpath->query('//section[contains(@class,"pb-downloads")]') as $section) {
            $heading = matrix_migrate_dom_text($xpath->query('.//div[contains(@class,"section-head")]//h2', $section)->item(0));

            if ($heading !== '') {
                $parts[] = '<h2>' . esc_html($heading) . '</h2>';
            }

            foreach ($xpath->query('.//div[contains(@class,"download")]', $section) as $download) {
                $link = $xpath->query('.//a[@href]', $download)->item(0);
                $title = matrix_migrate_dom_text($xpath->query('.//h4', $download)->item(0));

                if ($link instanceof DOMElement && $title !== '') {
                    $href = matrix_migrate_rewrite_internal_url($link->getAttribute('href'));
                    $parts[] = '<p><a href="' . esc_url($href) . '" target="_blank" rel="noopener">' . esc_html($title) . '</a></p>';
                }
            }
        }

        foreach ($xpath->query('//section[contains(@class,"pb-page-picker")]') as $section) {
            $heading = matrix_migrate_dom_text($xpath->query('.//div[contains(@class,"section-head")]//h2', $section)->item(0));
            $programme_links = [];

            foreach ($xpath->query('.//a[contains(@href,"our-programmes-and-therapies/")]', $section) as $link) {
                if (! $link instanceof DOMElement) {
                    continue;
                }

                $label = trim(preg_replace('/\s+/', ' ', matrix_migrate_dom_text($link)));

                if ($label === '') {
                    $label = matrix_migrate_dom_text($xpath->query('.//h3', $link)->item(0));
                }

                $href = matrix_migrate_rewrite_internal_url($link->getAttribute('href'));

                if ($label !== '' && $href !== '' && $href !== '#') {
                    $programme_links[$href] = '<li><a href="' . esc_url($href) . '">' . esc_html($label) . '</a></li>';
                }
            }

            if ($programme_links !== []) {
                if ($heading !== '') {
                    $parts[] = '<h2>' . esc_html($heading) . '</h2>';
                }

                $parts[] = '<ul>' . implode('', array_values($programme_links)) . '</ul>';
            }
        }

        return trim(implode("\n", $parts));
    }
}

if (! function_exists('matrix_seed_programme_import_image')) {
    function matrix_seed_programme_import_image(string $url, string $title, string $cache_key): int
    {
        if ($url === '') {
            return 0;
        }

        return matrix_migrate_attachment_id_for_source_path($url);
    }
}

if (! function_exists('matrix_seed_programme_upsert')) {
    /**
     * @param array{slug: string, title: string, summary: string, body_html: string, care_ids: int[], type_id: int, delivery_id: int, image_url: string} $entry
     */
    function matrix_seed_programme_upsert(array $entry): int
    {
        $slug = (string) $entry['slug'];
        $existing = get_posts([
            'post_type' => 'programmes_therapies',
            'name' => $slug,
            'post_status' => 'any',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => '_matrix_seed_programme_slug',
                    'value' => $slug,
                ],
            ],
        ]);

        if ($existing === []) {
            $existing = get_posts([
                'post_type' => 'programmes_therapies',
                'name' => $slug,
                'post_status' => 'any',
                'posts_per_page' => 1,
            ]);
        }

        $postarr = [
            'post_type' => 'programmes_therapies',
            'post_status' => 'publish',
            'post_title' => (string) $entry['title'],
            'post_name' => $slug,
            'post_content' => (string) $entry['body_html'],
            'post_excerpt' => (string) $entry['summary'],
        ];

        if ($existing !== [] && $existing[0] instanceof WP_Post) {
            $postarr['ID'] = (int) $existing[0]->ID;
            $post_id = wp_update_post($postarr, true);
        } else {
            $post_id = wp_insert_post($postarr, true);
        }

        if (is_wp_error($post_id) || ! $post_id) {
            if (class_exists('WP_CLI')) {
                WP_CLI::warning('Failed programme: ' . $slug);
            }

            return 0;
        }

        $post_id = (int) $post_id;

        if (get_post_field('post_name', $post_id) !== $slug) {
            global $wpdb;
            $wpdb->update(
                $wpdb->posts,
                ['post_name' => $slug],
                ['ID' => $post_id],
                ['%s'],
                ['%d']
            );
            clean_post_cache($post_id);
        }

        update_post_meta($post_id, '_matrix_seed_programme_slug', $slug);

        wp_set_object_terms($post_id, [(int) $entry['type_id']], 'programme_therapy_type', false);

        if ($entry['care_ids'] !== []) {
            wp_set_object_terms($post_id, $entry['care_ids'], 'care_setting', false);
        }

        wp_set_object_terms($post_id, [(int) $entry['delivery_id']], 'delivery_format', false);
        update_field('listing_summary', (string) $entry['summary'], $post_id);

        if ($entry['image_url'] !== '') {
            $attachment_id = matrix_seed_programme_import_image(
                (string) $entry['image_url'],
                (string) $entry['title'],
                'programme-card-' . $slug
            );

            if ($attachment_id > 0) {
                set_post_thumbnail($post_id, $attachment_id);
            }
        }

        return $post_id;
    }
}

if (! function_exists('matrix_seed_day_programmes_page_content')) {
    function matrix_seed_day_programmes_page_content(int $page_id): bool
    {
        $rows = get_field('flexible_content_blocks', $page_id);

        if (! is_array($rows) || $rows === []) {
            return false;
        }

        $day_programmes_url = get_permalink($page_id);
        $archive_list_url = $day_programmes_url . '#select-programme-or-therapy';

        $hero_intro = 'At St Patrick&rsquo;s Mental Health Services (SPMHS), we provide a range of programmes to support people in their mental health recovery.';

        $programmes_intro = '<p><strong>Our programmes aim to help you develop skills and techniques to deal with the mental health concerns unique to you.</strong></p>';
        $programmes_body = matrix_migrate_rewrite_html_urls(
            '<p>Run through our Wellness and Recovery Centre and day services at both St Patrick&rsquo;s University Hospital (SPUH) and St Patrick&rsquo;s, Lucan, our programmes respond to a diverse range of mental health difficulties. Some programmes relate to a specific mental health diagnosis, while others focus on growing the tools and coping strategies to put in place when you are experiencing mental health difficulties. If you are living with addiction, we also offer a cognitive behavioural programme for your family members or significant others.</p>'
            . '<p>Programmes vary in length from one week to 16 weeks. They run through group sessions, with each session taking place over either a half day or full day. Some programmes begin while you are an inpatient in hospital or receiving our Homecare Service, while others are run as day patient programmes only. They can be accessed remotely through online channels.</p>'
            . '<p><a href="' . esc_url($archive_list_url) . '">Click here to see our full list of programmes.</a></p>'
        );

        $therapies_body = matrix_migrate_rewrite_html_urls(
            '<h2>How do I take part in a programme?</h2>'
            . '<p>If you are an inpatient, receiving our Homecare Service or attending our Dean Clinics, you can be referred to a programme or therapy by your SPMHS care team.</p>'
            . '<p>If you have been referred to SPMHS by your doctor or other mental health professional, some programmes can be accessed without the need to attend inpatient or homecare services and without referral from a SPMHS consultant psychiatrist. These programmes include:</p>'
            . '<ul>'
            . '<li><a href="' . esc_url(home_url('/programmes-therapies/acceptance-commitment-therapy-act/')) . '">Acceptance and Commitment Therapy</a></li>'
            . '<li><a href="' . esc_url(home_url('/programmes-therapies/bipolar-education-programme/')) . '">Bipolar Recovery Programme</a></li>'
            . '<li><a href="' . esc_url(home_url('/programmes-therapies/compassion-focused-therapy-eating-disorders/')) . '">Compassion Focused Therapy - Eating Disorders</a></li>'
            . '<li><a href="' . esc_url(home_url('/programmes-therapies/compassion-focused-therapy-for-psychosis/')) . '">Compassion-Focused Therapy for Psychosis</a></li>'
            . '<li><a href="' . esc_url(home_url('/programmes-therapies/depression-recovery-programme/')) . '">Depression Recovery Programme</a></li>'
            . '<li><a href="' . esc_url(home_url('/programmes-therapies/cognitive-behavioural-therapy/')) . '">Individual Cognitive Behavioural Therapy</a></li>'
            . '<li><a href="' . esc_url(home_url('/programmes-therapies/recovery-wrap-programme/')) . '">Recovery Programme (WRAP&reg;)</a></li>'
            . '</ul>'
            . '<p>We have a short leaflet on programmes that you can attend without already being under the care of an SPMHS consultant; <a href="' . esc_url(matrix_migrate_resolve_media_url('/media/3568/st-patricks-day-programmes.pdf')) . '" target="_blank" rel="noopener">read the leaflet here</a>.</p>'
            . '<h2>What happens when I am referred to a programme?</h2>'
            . '<p>When you are referred to a programme, you will receive a confirmation letter with information on who to contact with any queries. If you are an inpatient or receiving the Homecare Service at the time you are referred, this will happen after you are discharged.</p>'
            . '<p>Your insurance cover for the programme will also be confirmed; this can take up to three days. Most health insurers cover the costs of our programmes (apart from the ESB, Garda and Prison Officers&rsquo; Scheme). Some insurers, such as Irish Life, Laya and VHI, require an excess payment for attending programme sessions on some of their policies; we will let you know if this applies to your cover.</p>'
            . '<p>After this, our programme coordinator will contact you with an appointment for assessment or a start date.</p>'
            . '<p>Once you commence your programme, you will be invited to register with our day services administrator, either electronically or through a paper form which will be posted to you.</p>'
            . '<h2>Where can I get more information?</h2>'
            . '<p>If you would like more information about any of our programmes, talk to your key worker or any member of your multidisciplinary team.</p>'
            . '<p>You can also visit our Information Centre in the main reception area of SPUH.</p>'
        );

        foreach ($rows as $index => $row) {
            $layout = (string) ($row['acf_fc_layout'] ?? '');

            if ($layout === 'hero_with_breadcrumbs') {
                $rows[$index]['content'] = '<p>' . $hero_intro . '</p>';
                continue;
            }

            if ($layout === 'content' && (string) ($row['heading'] ?? '') === 'What are programmes?') {
                $rows[$index]['intro_text'] = $programmes_intro;
                $rows[$index]['content'] = $programmes_body;
                continue;
            }

            if ($layout === 'content' && (string) ($row['heading'] ?? '') === 'About therapies') {
                $rows[$index]['intro_text'] = '<p><strong>Find out how to access our programmes and what to expect when you are referred.</strong></p>';
                $rows[$index]['content'] = $therapies_body;
                continue;
            }

            if ($layout === 'programmes_therapies_archive') {
                $rows[$index]['posts_per_page'] = 50;
            }
        }

        update_field('flexible_content_blocks', $rows, $page_id);

        return true;
    }
}

$type_programmes = matrix_seed_programme_ensure_term('programme_therapy_type', 'Programmes', 'programmes');
$type_therapies = matrix_seed_programme_ensure_term('programme_therapy_type', 'Therapies', 'therapies');
matrix_seed_programme_ensure_term('care_setting', 'Inpatient programme', 'inpatient-programme');
matrix_seed_programme_ensure_term('care_setting', 'Day patient programme', 'day-patient-programme');
matrix_seed_programme_ensure_term('care_setting', 'Homecare programme', 'homecare-programme');
$delivery_hybrid = matrix_seed_programme_ensure_term('delivery_format', 'Hybrid', 'hybrid');
$delivery_online = matrix_seed_programme_ensure_term('delivery_format', 'Online', 'online');
$delivery_in_person = matrix_seed_programme_ensure_term('delivery_format', 'In person', 'in-person');

$expected_slugs = [
    'acceptance-commitment-therapy-act',
    'access-to-recovery-programme',
    'alcohol-chemical-step-down-programme',
    'anxiety-disorders-programme',
    'art-therapy',
    'binge-eating-disorder-programme',
    'bipolar-education-programme',
    'cognitive-behavioural-therapy',
    'cognitive-behavioural-therapy-for-insomnia-cbt-i',
    'compassion-focused-therapy',
    'compassion-focused-therapy-eating-disorders',
    'compassion-focused-therapy-for-psychosis',
    'depression-recovery-programme',
    'eating-disorder-recovery-workshops-for-families',
    'dialectical-behavioural-therapy',
    'eating-disorder-programme',
    'eating-disorders-treatment-information-programme',
    'electroconvulsive-therapy',
    'evergreen-programme',
    'focused-acceptance-and-commitment-therapy',
    'group-radical-openness',
    'group-schema-therapy',
    'healthy-self-esteem-programme',
    'older-adult-psychology-groups',
    'pathways-to-wellness',
    'psychology-skills-group-for-adolescents',
    'psychosis-recovery-programme',
    'recovery-wrap-programme',
    'temple-formulation-group',
    'trauma-programme',
    'young-adult-psychology-groups',
    'young-adult-programme',
];

$archive_file = $html_dir . '/original_https_www.stpatricks.ie_care-treatment_programmes-therapies_our-programmes-and-therapies.html';
$archive_html = is_readable($archive_file) ? (string) file_get_contents($archive_file) : '';
$archive_entries = $archive_html !== '' ? matrix_seed_programme_parse_archive($archive_html) : [];

foreach (get_posts([
    'post_type' => 'programmes_therapies',
    'post_status' => 'any',
    'posts_per_page' => -1,
    'fields' => 'ids',
]) as $existing_id) {
    wp_delete_post((int) $existing_id, true);
}

$seeded_ids = [];
$failed = [];

foreach ($expected_slugs as $slug) {
    $meta = $archive_entries[$slug] ?? [
        'title' => '',
        'summary' => '',
        'care' => [],
        'image' => '',
    ];

    $html_file = matrix_seed_programme_html_file($html_dir, $slug);
    $old_path = 'care-treatment/programmes-therapies/our-programmes-and-therapies/' . $slug;
    $parsed = null;

    if ($html_file !== '') {
        $parsed = matrix_migrate_extract_parsed_page((string) file_get_contents($html_file), $old_path);
    }

    $title = (string) ($parsed['title'] ?? $meta['title']);

    if ($title === '') {
        $failed[] = $slug;
        continue;
    }

    $summary = trim((string) ($parsed['intro'] ?? ''));

    if ($summary === '') {
        $summary = (string) ($meta['summary'] ?? '');
    }

    $body_html = (string) ($parsed['body_html'] ?? '');

    if ($html_file !== '') {
        $supplements = matrix_seed_programme_extract_supplements((string) file_get_contents($html_file));

        if ($supplements !== '') {
            $body_html = trim($body_html . "\n" . $supplements);
        }
    }

    if ($body_html === '' && $summary !== '') {
        $body_html = '<p>' . esc_html($summary) . '</p>';
    }

    $delivery_slug = matrix_seed_programme_infer_delivery($body_html . ' ' . $summary);
    $delivery_id = match ($delivery_slug) {
        'online' => $delivery_online,
        'in-person' => $delivery_in_person,
        default => $delivery_hybrid,
    };

    $post_id = matrix_seed_programme_upsert([
        'slug' => $slug,
        'title' => $title,
        'summary' => $summary,
        'body_html' => $body_html,
        'care_ids' => matrix_seed_programme_care_term_ids($meta['care']),
        'type_id' => matrix_seed_programme_is_therapy($slug) ? $type_therapies : $type_programmes,
        'delivery_id' => $delivery_id,
        'image_url' => (string) ($meta['image'] ?? ''),
    ]);

    if ($post_id > 0) {
        $seeded_ids[] = $post_id;

        if (class_exists('WP_CLI')) {
            WP_CLI::log('Seeded: ' . $title . ' (' . $slug . ')');
        }
    } else {
        $failed[] = $slug;
    }
}

foreach ($seeded_ids as $seeded_id) {
    $content = (string) get_post_field('post_content', $seeded_id);
    $rewritten = matrix_migrate_rewrite_html_urls($content);

    if ($rewritten !== $content) {
        wp_update_post([
            'ID' => $seeded_id,
            'post_content' => $rewritten,
        ]);
    }
}

$day_programmes_id = (int) (get_page_by_path('what-we-offer/day-programmes')?->ID ?? 0);
$page_ok = $day_programmes_id > 0 && matrix_seed_day_programmes_page_content($day_programmes_id);

flush_rewrite_rules(false);

if (class_exists('WP_CLI')) {
    if ($failed !== []) {
        WP_CLI::warning('Failed slugs: ' . implode(', ', $failed));
    }

    if ($page_ok) {
        WP_CLI::success(sprintf(
            'Seeded %d programmes and updated Day Programmes page (ID %d).',
            count($seeded_ids),
            $day_programmes_id
        ));
    } else {
        WP_CLI::warning(sprintf(
            'Seeded %d programmes but could not update Day Programmes page.',
            count($seeded_ids)
        ));
    }
}
