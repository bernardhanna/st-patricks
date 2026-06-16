<?php

/**
 * Create /referrals/ landing page and Referrals CPT posts from old gps-referrals section.
 *
 * Sources:
 * - https://www.stpatricks.ie/gps-referrals
 * - https://www.stpatricks.ie/gps-referrals/referrals-admissions
 * - https://www.stpatricks.ie/gps-referrals/involuntary-admissions
 * - https://www.stpatricks.ie/gps-referrals/bed-vacancies
 * - https://www.stpatricks.ie/gps-referrals/online-gp-cpd
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-referrals.php
 */

require_once __DIR__ . '/lib/page-seed-conventions.php';
require_once get_template_directory() . '/inc/migrate-functions.php';
require_once get_template_directory() . '/inc/migrate-restyle-functions.php';

if (! function_exists('matrix_seed_ensure_page')) {
    function matrix_seed_ensure_page(string $path, string $title, int $parent_id = 0, string $old_path = ''): int
    {
        $page_id = matrix_seed_resolve_page_id_by_path($path);

        if ($page_id > 0) {
            wp_update_post([
                'ID' => $page_id,
                'post_title' => $title,
                'post_parent' => $parent_id,
                'post_status' => 'publish',
            ]);
        } else {
            $segments = array_values(array_filter(explode('/', trim($path, '/'))));
            $slug = (string) array_pop($segments);
            $inserted = wp_insert_post([
                'post_type' => 'page',
                'post_status' => 'publish',
                'post_parent' => $parent_id,
                'post_name' => $slug,
                'post_title' => $title,
            ], true);

            if (is_wp_error($inserted)) {
                return 0;
            }

            $page_id = (int) $inserted;
        }

        if ($old_path !== '') {
            update_post_meta($page_id, '_matrix_migrate_old_path', trim($old_path, '/'));
        }

        return $page_id;
    }
}

if (! function_exists('matrix_seed_ensure_referral_post')) {
    function matrix_seed_ensure_referral_post(string $slug, string $title, string $old_path): int
    {
        $existing = get_posts([
            'post_type' => 'referrals',
            'name' => $slug,
            'post_status' => ['publish', 'draft', 'private', 'pending'],
            'posts_per_page' => 1,
            'suppress_filters' => false,
        ]);

        if ($existing !== [] && $existing[0] instanceof WP_Post) {
            $post_id = (int) $existing[0]->ID;
            wp_update_post([
                'ID' => $post_id,
                'post_title' => $title,
                'post_status' => 'publish',
            ]);
        } else {
            $inserted = wp_insert_post([
                'post_type' => 'referrals',
                'post_status' => 'publish',
                'post_name' => $slug,
                'post_title' => $title,
            ], true);

            if (is_wp_error($inserted)) {
                return 0;
            }

            $post_id = (int) $inserted;
        }

        update_post_meta($post_id, '_matrix_migrate_old_path', trim($old_path, '/'));

        return $post_id;
    }
}

if (! function_exists('matrix_seed_referrals_section_links')) {
    /**
     * @return array<int, array{link: array{title: string, url: string, target: string}}>
     */
    function matrix_seed_referrals_section_links(string $base_url): array
    {
        $items = [
            ['Referrals & Admissions', 'referrals-admissions/'],
            ['Involuntary Admissions', 'involuntary-admissions/'],
            ['Bed Vacancies', 'bed-vacancies/'],
            ['Online GP CPD', 'online-gp-cpd/'],
        ];

        $links = [];

        foreach ($items as [$title, $slug]) {
            $links[] = [
                'link' => [
                    'title' => $title,
                    'url' => $base_url . $slug,
                    'target' => '',
                ],
            ];
        }

        return $links;
    }
}

if (! function_exists('matrix_seed_referrals_useful_links_row')) {
    /**
     * @return array<string, mixed>
     */
    function matrix_seed_referrals_useful_links_row(string $base_url): array
    {
        return [
            'acf_fc_layout' => 'useful_links',
            'heading_tag' => 'h2',
            'heading' => 'In this section',
            'variant' => 'flexi',
            'links' => matrix_seed_referrals_section_links($base_url),
            'background_color' => '#F1F8F9',
            'padding_settings' => [
                ['screen_size' => 'mob', 'padding_top' => '1.5', 'padding_bottom' => '1.5'],
                ['screen_size' => 'lg', 'padding_top' => '2', 'padding_bottom' => '2'],
            ],
        ];
    }
}

if (! function_exists('matrix_seed_referrals_cta_rows')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function matrix_seed_referrals_cta_rows(string $referrals_admissions_url): array
    {
        return [
            [
                'acf_fc_layout' => 'content_cta',
                'heading_tag' => 'h2',
                'heading' => 'Get information about referrals and services',
                'body' => '<p>Our Referrals and Assessment Service team can help GPs with queries about our services and referrals.</p><p><strong><a href="tel:012493635">01 249 3635</a></strong></p>',
                'button_link' => [
                    'title' => 'Contact our referrals team',
                    'url' => $referrals_admissions_url,
                    'target' => '',
                ],
                'background_type' => 'color',
                'background_color' => '#C6ECF4',
            ],
            [
                'acf_fc_layout' => 'content_cta',
                'heading_tag' => 'h2',
                'heading' => 'Sign up to our GP eNewsletter',
                'body' => '<p>Sign up to our GP eNewsletter for information about mental health, service updates, events and continuous professional development training.</p><p>Please be advised, this eNewsletter cannot be delivered to @healthmail email addresses; please provide an alternative email address.</p>',
                'button_link' => [
                    'title' => 'Subscribe to our GP eNewsletter',
                    'url' => home_url('/campaigns/subscribe-to-our-gp-enewsletter/'),
                    'target' => '',
                ],
                'background_type' => 'color',
                'background_color' => '#1E244B',
            ],
        ];
    }
}

if (! function_exists('matrix_seed_update_referrals_page_nav')) {
    function matrix_seed_update_referrals_page_nav(int $post_id, string $referrals_url, array $extra_breadcrumbs = []): void
    {
        $rows = get_field('flexible_content_blocks', $post_id);

        if (! is_array($rows) || $rows === []) {
            return;
        }

        $changed = false;

        foreach ($rows as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            if (($row['acf_fc_layout'] ?? '') === 'useful_links') {
                $rows[$index]['links'] = matrix_seed_referrals_section_links($referrals_url);
                $changed = true;
            }

            if (($row['acf_fc_layout'] ?? '') === 'hero_with_breadcrumbs') {
                $manual = [
                    [
                        'breadcrumb_link' => [
                            'title' => 'Home',
                            'url' => home_url('/'),
                            'target' => '',
                        ],
                    ],
                    [
                        'breadcrumb_link' => [
                            'title' => 'GPs & Referrals',
                            'url' => $referrals_url,
                            'target' => '',
                        ],
                    ],
                ];

                foreach ($extra_breadcrumbs as $crumb) {
                    if (! is_array($crumb) || ($crumb['title'] ?? '') === '' || ($crumb['url'] ?? '') === '') {
                        continue;
                    }

                    $manual[] = [
                        'breadcrumb_link' => [
                            'title' => (string) $crumb['title'],
                            'url' => (string) $crumb['url'],
                            'target' => (string) ($crumb['target'] ?? ''),
                        ],
                    ];
                }

                $rows[$index]['manual_breadcrumbs'] = $manual;
                $changed = true;
            }

            foreach (['content', 'wysiwyg', 'body', 'text_content', 'intro_text'] as $field) {
                if (! empty($row[$field]) && is_string($row[$field])) {
                    $rewritten = matrix_migrate_rewrite_html_urls($row[$field]);

                    if ($rewritten !== $row[$field]) {
                        $rows[$index][$field] = $rewritten;
                        $changed = true;
                    }
                }
            }

            if (($row['acf_fc_layout'] ?? '') === 'content_accordion' && ! empty($row['items']) && is_array($row['items'])) {
                foreach ($row['items'] as $item_index => $item) {
                    if (! is_array($item) || empty($item['content']) || ! is_string($item['content'])) {
                        continue;
                    }

                    $rewritten = matrix_migrate_rewrite_html_urls($item['content']);

                    if ($rewritten !== $item['content']) {
                        $rows[$index]['items'][$item_index]['content'] = $rewritten;
                        $changed = true;
                    }
                }
            }
        }

        if ($changed) {
            update_field('flexible_content_blocks', $rows, $post_id);
        }
    }
}

if (! function_exists('matrix_seed_referrals_inject_section_nav')) {
    function matrix_seed_referrals_inject_section_nav(int $post_id, string $referrals_url): void
    {
        $rows = get_field('flexible_content_blocks', $post_id);

        if (! is_array($rows)) {
            $rows = [];
        }

        $has_nav = false;

        foreach ($rows as $row) {
            if (is_array($row) && ($row['acf_fc_layout'] ?? '') === 'useful_links') {
                $has_nav = true;
                break;
            }
        }

        if ($has_nav) {
            return;
        }

        $hero_index = null;

        foreach ($rows as $index => $row) {
            if (is_array($row) && ($row['acf_fc_layout'] ?? '') === 'hero_with_breadcrumbs') {
                $hero_index = $index;
                break;
            }
        }

        $nav_row = matrix_seed_referrals_useful_links_row($referrals_url);

        if ($hero_index !== null) {
            array_splice($rows, $hero_index + 1, 0, [$nav_row]);
        } else {
            array_unshift($rows, $nav_row);
        }

        update_field('flexible_content_blocks', $rows, $post_id);
    }
}

if (! function_exists('matrix_seed_referrals_ensure_bed_vacancies_hero')) {
    function matrix_seed_referrals_ensure_bed_vacancies_hero(int $post_id, string $referrals_url): void
    {
        $rows = get_field('flexible_content_blocks', $post_id);

        if (! is_array($rows)) {
            $rows = [];
        }

        foreach ($rows as $row) {
            if (is_array($row) && ($row['acf_fc_layout'] ?? '') === 'hero_with_breadcrumbs') {
                return;
            }
        }

        $hero_image_id = (int) matrix_migrate_attachment_id_for_source_path('/media/1682/st-edmundsbury-hospital-bedroom-banner.jpg');

        $hero_row = [
            'acf_fc_layout' => 'hero_with_breadcrumbs',
            'layout_style' => 'image_split',
            'show_breadcrumbs' => 1,
            'breadcrumb_source' => 'manual',
            'manual_breadcrumbs' => [
                ['breadcrumb_link' => ['title' => 'Home', 'url' => home_url('/'), 'target' => '']],
                ['breadcrumb_link' => ['title' => 'GPs & Referrals', 'url' => $referrals_url, 'target' => '']],
            ],
            'current_crumb_label' => 'Bed Vacancies',
            'heading_tag' => 'h1',
            'heading' => 'Bed Vacancies',
            'content' => '',
            'hero_image' => $hero_image_id,
            'background_color' => '#1E244B',
            'breadcrumb_background_color' => '#F1F8F9',
            'heading_color' => '#FFFFFF',
            'text_color' => '#FFFFFF',
        ];

        array_unshift($rows, $hero_row);
        update_field('flexible_content_blocks', $rows, $post_id);
    }
}

if (! function_exists('matrix_seed_referrals_append_bed_vacancies_block')) {
    function matrix_seed_referrals_append_bed_vacancies_block(int $post_id): void
    {
        $rows = get_field('flexible_content_blocks', $post_id);

        if (! is_array($rows)) {
            $rows = [];
        }

        foreach ($rows as $row) {
            if (is_array($row) && ($row['acf_fc_layout'] ?? '') === 'inpatient_bed_vacancies') {
                return;
            }
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
                    'disclaimer' => 'Additional context would be required to clarify that available beds may not be immediately accessible.',
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

        update_field('flexible_content_blocks', $rows, $post_id);
    }
}

$home = home_url('/');
$referrals_url = home_url('/referrals/');
$referrals_admissions_url = $referrals_url . 'referrals-admissions/';
$bed_vacancies_url = $referrals_url . 'bed-vacancies/';
$staff_directory_url = home_url('/about-us/our-team/');

$referrals_landing_id = matrix_seed_ensure_page('referrals', 'GPs & Referrers', 0, 'gps-referrals');

if ($referrals_landing_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not create referrals landing page.');
    }

    exit(1);
}

$referral_posts_config = [
    ['slug' => 'referrals-admissions', 'title' => 'Referrals and Assessments', 'old_path' => 'gps-referrals/referrals-admissions'],
    ['slug' => 'involuntary-admissions', 'title' => 'Involuntary admissions', 'old_path' => 'gps-referrals/involuntary-admissions'],
    ['slug' => 'bed-vacancies', 'title' => 'Bed Vacancies', 'old_path' => 'gps-referrals/bed-vacancies'],
    ['slug' => 'online-gp-cpd', 'title' => 'Mental health education for GPs', 'old_path' => 'gps-referrals/online-gp-cpd'],
    ['slug' => 'ereferral-guides', 'title' => 'eReferral Guides for Socrates, Healthone and HPM', 'old_path' => 'gps-referrals/referrals-admissions/ereferral-guides-for-socrates-healthone-and-hpm'],
    ['slug' => 'service-information', 'title' => 'Service Information', 'old_path' => 'gps-referrals/service-information'],
];

$referral_post_ids = [];

foreach ($referral_posts_config as $config) {
    $post_id = matrix_seed_ensure_referral_post($config['slug'], $config['title'], $config['old_path']);

    if ($post_id > 0) {
        $referral_post_ids[$config['slug']] = $post_id;
    }
}

flush_rewrite_rules(false);

$hero_image_id = (int) matrix_migrate_attachment_id_for_source_path('/media/1530/st-patricks-mental-health-services-gp-referral-banner-min.jpg');

if ($hero_image_id === 0) {
    $hero_image_id = (int) matrix_migrate_attachment_id_for_source_path('/images/default.jpg');
}

$pod_referrals = (int) matrix_migrate_attachment_id_for_source_path('/media/1683/refer-and-admissions-st-patricks-mental-health-services.jpg');
$pod_involuntary = (int) matrix_migrate_attachment_id_for_source_path('/media/1677/involuntary-admissions-st-patricks-mental-health-services.jpg');
$pod_consultants = (int) matrix_migrate_attachment_id_for_source_path('/media/1537/st-patricks-mental-health-services-information-service.jpg');

$section_padding = [
    ['screen_size' => 'mob', 'padding_top' => '3', 'padding_bottom' => '3'],
    ['screen_size' => 'lg', 'padding_top' => '6.25', 'padding_bottom' => '6.25'],
];

$landing_rows = [
    [
        'acf_fc_layout' => 'hero_with_breadcrumbs',
        'layout_style' => 'image_split',
        'show_breadcrumbs' => 1,
        'breadcrumb_source' => 'manual',
        'manual_breadcrumbs' => [
            [
                'breadcrumb_link' => [
                    'title' => 'Home',
                    'url' => $home,
                    'target' => '',
                ],
            ],
        ],
        'current_crumb_label' => 'GPs & Referrers',
        'heading_tag' => 'h1',
        'heading' => 'GPs & Referrers',
        'content' => '',
        'primary_button' => [
            'title' => 'Make a Referral',
            'url' => $referrals_admissions_url,
            'target' => '',
        ],
        'hero_image' => $hero_image_id,
        'background_color' => '#1E244B',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#FFFFFF',
        'text_color' => '#FFFFFF',
    ],
    matrix_seed_referrals_useful_links_row($referrals_url),
    [
        'acf_fc_layout' => 'related_cards',
        'heading_tag' => 'h2',
        'heading' => '',
        'intro_text' => '',
        'cards' => [
            [
                'image' => $pod_referrals,
                'title' => 'Referrals and Admissions',
                'description' => 'We treat obsessive–compulsive disorder, anxiety disorders, addiction and dual diagnosis, depression, bipolar education, psychosis recovery, eating disorders, mood and anxiety and the mental health of older adults.',
                'link' => [
                    'title' => 'Learn More',
                    'url' => $referrals_admissions_url,
                    'target' => '',
                ],
            ],
            [
                'image' => $pod_involuntary,
                'title' => 'Involuntary admissions',
                'description' => 'St Patrick\'s University Hospital, as an MHC-approved centre, provides care and treatment for involuntary service users.',
                'link' => [
                    'title' => 'Learn more',
                    'url' => $referrals_url . 'involuntary-admissions/',
                    'target' => '',
                ],
            ],
            [
                'image' => $pod_consultants,
                'title' => 'List of Consultants',
                'description' => 'Please click here to find the list of consultants providing care in St Patrick\'s Mental Health Services.',
                'link' => [
                    'title' => 'Staff Directory',
                    'url' => $staff_directory_url,
                    'target' => '',
                ],
            ],
        ],
        'background_color' => '#FFFFFF',
        'columns' => '3',
        'padding_settings' => $section_padding,
    ],
    ...matrix_seed_referrals_cta_rows($referrals_admissions_url),
];

update_field('hero_content_blocks', [], $referrals_landing_id);
update_field('flexible_content_blocks', $landing_rows, $referrals_landing_id);

foreach ($referral_post_ids as $slug => $post_id) {
    delete_post_meta($post_id, '_matrix_migrate_restyled');
    matrix_migrate_restyle_page($post_id, true);
    matrix_seed_referrals_inject_section_nav($post_id, $referrals_url);

    if ($slug === 'bed-vacancies') {
        matrix_seed_referrals_ensure_bed_vacancies_hero($post_id, $referrals_url);
        matrix_seed_referrals_append_bed_vacancies_block($post_id);
    }

    $extra = [];

    if ($slug === 'ereferral-guides') {
        $extra[] = [
            'title' => 'Referrals & Admissions',
            'url' => $referrals_admissions_url,
            'target' => '',
        ];
    }

    matrix_seed_update_referrals_page_nav($post_id, $referrals_url, $extra);
}

matrix_seed_update_referrals_page_nav($referrals_landing_id, $referrals_url);

if (class_exists('WP_CLI')) {
    WP_CLI::success(sprintf(
        'Seeded Referrals section (landing page %d, %d CPT posts).',
        $referrals_landing_id,
        count($referral_post_ids)
    ));
    WP_CLI::log('Landing: ' . get_permalink($referrals_landing_id));

    foreach ($referral_post_ids as $slug => $post_id) {
        WP_CLI::log($slug . ': ' . get_permalink($post_id));
    }
}
