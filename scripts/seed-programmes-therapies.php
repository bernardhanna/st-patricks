<?php

/**
 * Seed Programmes and Therapies pages (Figma 3279:19078).
 *
 * - /programmes-therapies/ (index)
 * - /what-we-offer/day-programmes/
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-programmes-therapies.php
 */

if (! function_exists('matrix_seed_import_remote_image')) {
    function matrix_seed_import_remote_image(string $url, string $title, string $cache_key): int
    {
        if ($url === '') {
            return 0;
        }

        $existing = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => '_matrix_seed_figma_key',
                    'value' => $cache_key,
                ],
            ],
        ]);

        if ($existing !== []) {
            return (int) $existing[0]->ID;
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $tmp = download_url($url, 30);

        if (is_wp_error($tmp)) {
            return 0;
        }

        $path = parse_url($url, PHP_URL_PATH);
        $filename = $path ? basename($path) : 'figma-asset.jpg';

        if (! preg_match('/\.(jpe?g|png|gif|webp|svg)$/i', $filename)) {
            $filename .= '.jpg';
        }

        $file_array = [
            'name' => sanitize_file_name($filename),
            'tmp_name' => $tmp,
        ];

        $attachment_id = media_handle_sideload($file_array, 0, $title);

        if (is_wp_error($attachment_id)) {
            @unlink($tmp);

            return 0;
        }

        update_post_meta($attachment_id, '_matrix_seed_figma_key', $cache_key);
        update_post_meta($attachment_id, '_matrix_seed_figma_url', $url);

        return (int) $attachment_id;
    }
}

if (! function_exists('matrix_seed_resolve_image')) {
    function matrix_seed_resolve_image(string $figma_url, string $cache_key, string $title): int
    {
        $id = matrix_seed_import_remote_image($figma_url, $title, $cache_key);

        if ($id > 0) {
            return $id;
        }

        $attachments = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'post_mime_type' => 'image',
            'orderby' => 'ID',
            'order' => 'DESC',
        ]);

        return $attachments !== [] ? (int) $attachments[0]->ID : 0;
    }
}

if (! function_exists('matrix_seed_ensure_programme_term')) {
    function matrix_seed_ensure_programme_term(string $taxonomy, string $name, string $slug): int
    {
        $existing = get_term_by('slug', $slug, $taxonomy);
        if ($existing instanceof WP_Term) {
            return (int) $existing->term_id;
        }

        $created = wp_insert_term($name, $taxonomy, ['slug' => $slug]);
        if (is_wp_error($created)) {
            if (class_exists('WP_CLI')) {
                WP_CLI::warning('Could not create term ' . $taxonomy . ':' . $slug . ' - ' . $created->get_error_message());
            }

            return 0;
        }

        return (int) ($created['term_id'] ?? 0);
    }
}

if (! function_exists('matrix_seed_programmes_therapies_posts')) {
    function matrix_seed_programmes_therapies_posts(): void
    {
        $type_programmes = matrix_seed_ensure_programme_term('programme_therapy_type', 'Programmes', 'programmes');
        $type_therapies = matrix_seed_ensure_programme_term('programme_therapy_type', 'Therapies', 'therapies');

        $care_inpatient = matrix_seed_ensure_programme_term('care_setting', 'Inpatient programme', 'inpatient-programme');
        $care_day = matrix_seed_ensure_programme_term('care_setting', 'Day patient programme', 'day-patient-programme');
        $care_homecare = matrix_seed_ensure_programme_term('care_setting', 'Homecare programme', 'homecare-programme');

        $delivery_hybrid = matrix_seed_ensure_programme_term('delivery_format', 'Hybrid', 'hybrid');
        $delivery_online = matrix_seed_ensure_programme_term('delivery_format', 'Online', 'online');
        $delivery_in_person = matrix_seed_ensure_programme_term('delivery_format', 'In person', 'in-person');

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
                    if (class_exists('WP_CLI')) {
                        WP_CLI::warning('Could not create programme/therapy: ' . $entry['title']);
                    }
                    continue;
                }
            }

            wp_set_object_terms($post_id_item, [(int) $entry['type']], 'programme_therapy_type', false);
            wp_set_object_terms($post_id_item, [(int) $entry['care']], 'care_setting', false);
            wp_set_object_terms($post_id_item, [(int) $entry['delivery']], 'delivery_format', false);
            update_field('listing_summary', $summary, $post_id_item);
        }
    }
}

if (! function_exists('matrix_seed_build_programmes_therapies_flexi_rows')) {
    /**
     * @param array{
     *     heading: string,
     *     current_crumb_label: string,
     *     manual_breadcrumbs: array<int, array{breadcrumb_link: array{title: string, url: string, target: string}}>,
     *     archive_anchor_url: string,
     *     programmes_image_id: int,
     *     therapies_image_id: int,
     * } $config
     * @return array<int, array<string, mixed>>
     */
    function matrix_seed_build_programmes_therapies_flexi_rows(array $config): array
    {
        $hero_intro = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad mini. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam voluptatem.';
        $programmes_intro = '<p><strong>We&rsquo;re proud to be Ireland\'s largest, independent, not-for-profit mental health service.</strong></p>';
        $programmes_body = '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat.</p>';
        $therapies_intro = '<p><strong>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</strong></p>';
        $therapies_body = '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat.</p>';

        $section_padding = [
            ['screen_size' => 'mob', 'padding_top' => '3', 'padding_bottom' => '3'],
            ['screen_size' => 'lg', 'padding_top' => '6.25', 'padding_bottom' => '6.25'],
        ];

        return [
            [
                'acf_fc_layout' => 'hero_with_breadcrumbs',
                'layout_style' => 'image_split',
                'show_breadcrumbs' => 1,
                'breadcrumb_source' => 'manual',
                'manual_breadcrumbs' => $config['manual_breadcrumbs'],
                'current_crumb_label' => $config['current_crumb_label'],
                'heading_tag' => 'h1',
                'heading' => $config['heading'],
                'content' => '<p>' . esc_html($hero_intro) . '</p>',
                'primary_button' => [
                    'title' => 'Select a programme or therapy',
                    'url' => $config['archive_anchor_url'],
                    'target' => '',
                ],
                'hero_image' => '',
                'background_color' => '#C6ECF4',
                'breadcrumb_background_color' => '#F1F8F9',
                'heading_color' => '#08284B',
                'text_color' => '#08284B',
            ],
            [
                'acf_fc_layout' => 'content',
                'heading' => 'What are programmes?',
                'heading_tag' => 'h2',
                'accent_position' => 'below_heading',
                'intro_text' => $programmes_intro,
                'content' => $programmes_body,
                'image' => (int) $config['programmes_image_id'],
                'layout_style' => 'image_left',
                'background_type' => 'color',
                'background_color' => '#FFFFFF',
                'padding_settings' => $section_padding,
            ],
            [
                'acf_fc_layout' => 'content',
                'heading' => 'About therapies',
                'heading_tag' => 'h2',
                'accent_position' => 'below_heading',
                'intro_text' => $therapies_intro,
                'content' => $therapies_body,
                'image' => (int) $config['therapies_image_id'],
                'layout_style' => 'image_right',
                'background_type' => 'color',
                'background_color' => '#FBFAF7',
                'padding_settings' => $section_padding,
            ],
            [
                'acf_fc_layout' => 'programmes_therapies_archive',
                'heading' => 'Select a programme or therapy',
                'heading_tag' => 'h2',
                'posts_per_page' => 10,
                'empty_state_message' => 'No programmes or therapies matched your filters.',
                'padding_settings' => $section_padding,
            ],
        ];
    }
}

if (! function_exists('matrix_seed_programmes_therapies_page')) {
    function matrix_seed_programmes_therapies_page(int $post_id, array $flexi_rows): bool
    {
        update_field('hero_content_blocks', [], $post_id);
        update_field('flexible_content_blocks', $flexi_rows, $post_id);

        $saved_rows = get_field('flexible_content_blocks', $post_id);

        return is_array($saved_rows) && count($saved_rows) === count($flexi_rows);
    }
}

matrix_seed_programmes_therapies_posts();

$home = home_url('/');
$what_we_offer_url = home_url('/what-we-offer/');

$figma = [
    'programmes' => 'https://www.figma.com/api/mcp/asset/3ca296a7-d982-46b3-b2b5-a07a6815c61f',
    'therapies' => 'https://www.figma.com/api/mcp/asset/824a3e76-34ee-4155-8724-4febf3f2c073',
];

$programmes_image_id = matrix_seed_resolve_image($figma['programmes'], 'programmes-therapies-programmes-3279-19078', 'What are programmes');
$therapies_image_id = matrix_seed_resolve_image($figma['therapies'], 'programmes-therapies-therapies-3279-19078', 'About therapies');

$index_post_id = (int) (get_page_by_path('programmes-therapies')?->ID ?? 0);
if ($index_post_id === 0) {
    $index_post_id = (int) wp_insert_post([
        'post_type' => 'page',
        'post_status' => 'publish',
        'post_title' => 'Programmes and Therapies',
        'post_name' => 'programmes-therapies',
        'post_parent' => 0,
    ], true);

    if (is_wp_error($index_post_id)) {
        if (class_exists('WP_CLI')) {
            WP_CLI::error('Could not create Programmes and Therapies page: ' . $index_post_id->get_error_message());
        }
        exit(1);
    }
} else {
    wp_update_post([
        'ID' => $index_post_id,
        'post_name' => 'programmes-therapies',
        'post_parent' => 0,
        'post_title' => 'Programmes and Therapies',
    ]);
}

$day_programmes_post_id = (int) (get_page_by_path('what-we-offer/day-programmes')?->ID ?? 0);
if ($day_programmes_post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at what-we-offer/day-programmes.');
    }
    exit(1);
}

$index_url = get_permalink($index_post_id);
$day_programmes_url = get_permalink($day_programmes_post_id);

$index_rows = matrix_seed_build_programmes_therapies_flexi_rows([
    'heading' => 'Programmes and Therapies',
    'current_crumb_label' => 'Programmes and Therapies',
    'manual_breadcrumbs' => [
        ['breadcrumb_link' => ['title' => 'Home', 'url' => $home, 'target' => '']],
    ],
    'archive_anchor_url' => $index_url . '#select-programme-or-therapy',
    'programmes_image_id' => $programmes_image_id,
    'therapies_image_id' => $therapies_image_id,
]);

$day_programmes_rows = matrix_seed_build_programmes_therapies_flexi_rows([
    'heading' => 'Day Programmes',
    'current_crumb_label' => 'Day Programmes',
    'manual_breadcrumbs' => [
        ['breadcrumb_link' => ['title' => 'Home', 'url' => $home, 'target' => '']],
        ['breadcrumb_link' => ['title' => 'What we offer', 'url' => $what_we_offer_url, 'target' => '']],
    ],
    'archive_anchor_url' => $day_programmes_url . '#select-programme-or-therapy',
    'programmes_image_id' => $programmes_image_id,
    'therapies_image_id' => $therapies_image_id,
]);

$index_ok = matrix_seed_programmes_therapies_page($index_post_id, $index_rows);
$day_ok = matrix_seed_programmes_therapies_page($day_programmes_post_id, $day_programmes_rows);

flush_rewrite_rules(false);

if (class_exists('WP_CLI')) {
    if ($index_ok && $day_ok) {
        WP_CLI::success(sprintf(
            'Seeded Programmes and Therapies pages: index (%d) at %s and day programmes (%d) at %s.',
            $index_post_id,
            $index_url,
            $day_programmes_post_id,
            $day_programmes_url
        ));
    } else {
        WP_CLI::warning(sprintf(
            'Seeded with warnings. Index page %d saved=%s, Day Programmes page %d saved=%s.',
            $index_post_id,
            $index_ok ? 'yes' : 'no',
            $day_programmes_post_id,
            $day_ok ? 'yes' : 'no'
        ));
    }
}
