<?php

/**
 * Create /getting-help/ and organise Getting Help section pages under it.
 *
 * Sources:
 * - https://www.stpatricks.ie/getting-help
 * - Subpages already migrated; this script reparents them and seeds the landing page.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-getting-help.php
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

if (! function_exists('matrix_seed_getting_help_section_links')) {
    /**
     * @return array<int, array{link: array{title: string, url: string, target: string}}>
     */
    function matrix_seed_getting_help_section_links(string $base_url): array
    {
        $items = [
            ['Concerned about yourself or someone you know?', 'concerned-about-yourself-or-someone-you-know/'],
            ['Frequently asked questions', 'faqs/'],
            ['Information Centre', 'information-centre/'],
            ['Carers & supporters', 'carers-supporters/'],
            ['Insurance Information', 'insurance-information/'],
            ['Learning & Resource Hub', 'learning-resource-hub/'],
            ['Support Groups & Meetings', 'support-groups-meetings/'],
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

if (! function_exists('matrix_seed_getting_help_useful_links_row')) {
    /**
     * @return array<string, mixed>
     */
    function matrix_seed_getting_help_useful_links_row(string $base_url): array
    {
        return [
            'acf_fc_layout' => 'useful_links',
            'heading_tag' => 'h2',
            'heading' => 'In this section',
            'variant' => 'flexi',
            'links' => matrix_seed_getting_help_section_links($base_url),
            'background_color' => '#F1F8F9',
            'padding_settings' => [
                ['screen_size' => 'mob', 'padding_top' => '1.5', 'padding_bottom' => '1.5'],
                ['screen_size' => 'lg', 'padding_top' => '2', 'padding_bottom' => '2'],
            ],
        ];
    }
}

if (! function_exists('matrix_seed_getting_help_cta_rows')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function matrix_seed_getting_help_cta_rows(string $faqs_url, string $referrals_url): array
    {
        return [
            [
                'acf_fc_layout' => 'content_cta',
                'heading_tag' => 'h2',
                'heading' => 'Queries',
                'body' => '<p>For general queries, please call us. For more on mental health and our services, see our frequently asked questions (FAQs).</p><p><strong><a href="tel:012493200">01 249 3200</a></strong></p>',
                'button_link' => [
                    'title' => 'See our FAQs',
                    'url' => $faqs_url,
                    'target' => '',
                ],
                'background_type' => 'color',
                'background_color' => '#C6ECF4',
            ],
            [
                'acf_fc_layout' => 'content_cta',
                'heading_tag' => 'h2',
                'heading' => 'Referrals',
                'body' => '<p>Contact our Referral and Assessment Service for queries regarding referrals to our services.</p><p><strong><a href="tel:012493635">01 249 3635</a></strong></p>',
                'button_link' => [
                    'title' => 'See more from our referrals team',
                    'url' => $referrals_url,
                    'target' => '',
                ],
                'background_type' => 'color',
                'background_color' => '#CEF2EE',
            ],
        ];
    }
}

if (! function_exists('matrix_seed_update_getting_help_page_nav')) {
    function matrix_seed_update_getting_help_page_nav(int $post_id, string $getting_help_url, array $extra_breadcrumbs = []): void
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
                $rows[$index]['links'] = matrix_seed_getting_help_section_links($getting_help_url);
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
                            'title' => 'Getting Help',
                            'url' => $getting_help_url,
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

$home = home_url('/');
$getting_help_url = home_url('/getting-help/');
$faqs_url = $getting_help_url . 'faqs/';
$mental_health_url = home_url('/mental-health/');
$referrals_url = home_url('/referrals/');

$getting_help_id = matrix_seed_ensure_page('getting-help', 'Getting Help', 0, 'getting-help');

if ($getting_help_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not create getting-help page.');
    }

    exit(1);
}

$insurance_id = matrix_seed_ensure_page(
    'getting-help/insurance-information',
    'Insurance Information',
    $getting_help_id,
    'getting-help/insurance-information'
);

$learning_hub_id = matrix_seed_ensure_page(
    'getting-help/learning-resource-hub',
    'Learning & Resource Hub',
    $getting_help_id,
    'getting-help/learning-resource-hub'
);

$insurance_url = home_url('/getting-help/insurance-information/');
$learning_hub_url = home_url('/getting-help/learning-resource-hub/');

$direct_children = [
    1412 => 'getting-help/concerned-about-yourself-or-someone-you-know',
    1413 => 'getting-help/faqs',
    1414 => 'getting-help/information-centre',
    1410 => 'getting-help/carers-supporters',
    1428 => 'getting-help/support-groups-meetings',
    1429 => 'getting-help/support-information-service',
];

foreach ($direct_children as $page_id => $old_path) {
    wp_update_post([
        'ID' => $page_id,
        'post_parent' => $getting_help_id,
    ]);
    update_post_meta($page_id, '_matrix_migrate_old_path', $old_path);
}

if ($insurance_id > 0) {
    wp_update_post([
        'ID' => 1415,
        'post_parent' => $insurance_id,
    ]);
    update_post_meta(1415, '_matrix_migrate_old_path', 'getting-help/insurance-information/health-insurance-plans');
}

wp_update_post([
    'ID' => 1411,
    'post_parent' => 1410,
]);
update_post_meta(1411, '_matrix_migrate_old_path', 'getting-help/carers-supporters/family-mental-health-series');

$booklet_ids = [1417, 1419, 1420, 1421, 1424, 1426];

if ($learning_hub_id > 0) {
    foreach ($booklet_ids as $booklet_id) {
        wp_update_post([
            'ID' => $booklet_id,
            'post_parent' => $learning_hub_id,
        ]);
    }
}

if ($insurance_id > 0 && function_exists('matrix_migrate_restyle_page')) {
    delete_post_meta($insurance_id, '_matrix_migrate_restyled');
    matrix_migrate_restyle_page($insurance_id, true);
    matrix_seed_update_getting_help_page_nav($insurance_id, $getting_help_url);
}

$hero_image_id = (int) matrix_migrate_attachment_id_for_source_path('/images/default.jpg');
$pod_image_gp = (int) matrix_migrate_attachment_id_for_source_path('/media/1997/single-step-mental-health-support.jpg');
$pod_image_faq = (int) matrix_migrate_attachment_id_for_source_path('/media/1995/single-step-mental-health-info.jpg');
$pod_image_mh = (int) matrix_migrate_attachment_id_for_source_path('/media/1991/single-step-mental-health-awareness.jpg');

$section_padding = [
    ['screen_size' => 'mob', 'padding_top' => '3', 'padding_bottom' => '3'],
    ['screen_size' => 'lg', 'padding_top' => '6.25', 'padding_bottom' => '6.25'],
];

$intro_content = '<p>At St Patrick\'s Mental Health Services (SPMHS), we are committed to giving you the best opportunity to recover, while also providing wellbeing tools and resources to help you live a fulfilled life. We want to see a society where everyone is empowered to live mentally healthy lives.</p>';

$about_content = matrix_migrate_rewrite_html_urls(
    '<p>SPMHS is Ireland\'s largest independent not-for-profit mental health service provider. We have developed a strong reputation for providing the highest quality mental healthcare. Our services are <a href="/advocacy/human-rights-advocacy">grounded in human rights</a>, and we empower our service users to control and <a href="/get-involved/service-user-participation">guide their own treatment</a> and mental healthcare.</p>'
    . '<p>Our locations include:</p>'
    . '<ul>'
    . '<li><a href="/care-treatment/inpatient-hospital-care/st-patrick-s-university-hospital">St Patrick\'s University Hospital, Dublin</a></li>'
    . '<li><a href="/care-treatment/inpatient-hospital-care/st-patricks-lucan">St Patrick\'s Hospital Lucan</a></li>'
    . '<li><a href="/care-treatment/adolescent-mental-health-services/inpatient-adolescent-unit">Willow Grove Adolescent Unit, Dublin</a></li>'
    . '<li><a href="/care-treatment/outpatient-clinics/about-the-dean-clinics">Community Dean Clinics</a> in Dublin and around the country.</li>'
    . '</ul>'
    . '<p>We also provide <a href="/care-treatment/our-services/remote-services">a Homecare Service and remote access to our services</a> through phone, video or online channels.</p>'
    . '<p>As an independent organisation, we are not in receipt of government funding. We receive fees for our services from service users and/or <a href="/getting-help/insurance-information">their health insurance company</a>.</p>'
    . '<p>You can <a href="/care-treatment/our-services">find more information about our mental health services here</a>.</p>'
);

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
        'current_crumb_label' => 'Getting Help',
        'heading_tag' => 'h1',
        'heading' => 'A single step leads to the biggest change.',
        'content' => '<p>If you have concerns about your mental health, it can often feel overwhelming or isolating. But the biggest change can come from just a single step.</p>',
        'hero_image' => $hero_image_id,
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
    ],
    matrix_seed_getting_help_useful_links_row($getting_help_url),
    [
        'acf_fc_layout' => 'content',
        'heading' => 'If you have concerns about your mental health, it can often feel overwhelming or isolating.',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'content' => '<h3>But the biggest change can come from just a single step.</h3>' . $intro_content,
        'layout_style' => 'default',
        'background_type' => 'color',
        'background_color' => '#FFFFFF',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'related_cards',
        'heading_tag' => 'h2',
        'heading' => 'The road to recovery starts with a single step. Take it today.',
        'intro_text' => '',
        'cards' => [
            [
                'image' => $pod_image_gp,
                'title' => 'Talk to your GP',
                'description' => 'If you need support for your mental health, we recommend that you talk to your GP in the first instance. Your GP will know and understand your physical and mental health history, and, with their support, you can decide on a course of action that best suits your needs.',
                'link' => [
                    'title' => 'Learn more about getting support',
                    'url' => $getting_help_url . 'concerned-about-yourself-or-someone-you-know/',
                    'target' => '',
                ],
            ],
            [
                'image' => $pod_image_faq,
                'title' => 'Get helpful information',
                'description' => 'If you\'re worried about your mental health, talking to someone you trust can help you make sense of your feelings. Having practical information can also be a help. You can also see our frequently asked questions (FAQs) to find out more about our services and mental health supports.',
                'link' => [
                    'title' => 'See our FAQs',
                    'url' => $faqs_url,
                    'target' => '',
                ],
            ],
            [
                'image' => $pod_image_mh,
                'title' => 'Learn more about mental health',
                'description' => 'Understanding your mental health and knowing the facts can be important in your recovery. You can learn more about the different types of mental health difficulties, how to spot symptoms, and what kinds of supports or treatment are available here on our website.',
                'link' => [
                    'title' => 'Learn more about mental health',
                    'url' => $mental_health_url,
                    'target' => '',
                ],
            ],
        ],
        'background_color' => '#FBFAF7',
        'columns' => '3',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Everyone is entitled to good mental health, but deciding to take action to address the difficulties you are experiencing can be hard. Please know that we are here to support you every step of the way.',
        'heading_tag' => 'h3',
        'accent_position' => 'none',
        'content' => $about_content,
        'layout_style' => 'default',
        'background_type' => 'color',
        'background_color' => '#FFFFFF',
        'padding_settings' => $section_padding,
    ],
    ...matrix_seed_getting_help_cta_rows($faqs_url, $referrals_url),
];

update_field('hero_content_blocks', [], $getting_help_id);
update_field('flexible_content_blocks', $landing_rows, $getting_help_id);

if ($learning_hub_id > 0) {
    $hub_cards = [];

    foreach ($booklet_ids as $booklet_id) {
        $booklet = get_post($booklet_id);

        if (! $booklet instanceof WP_Post) {
            continue;
        }

        $thumb_id = (int) get_post_thumbnail_id($booklet_id);
        $hub_cards[] = [
            'image' => $thumb_id,
            'title' => $booklet->post_title,
            'description' => '',
            'link' => [
                'title' => $booklet->post_title,
                'url' => (string) get_permalink($booklet_id),
                'target' => '',
            ],
        ];
    }

    $hub_rows = [
        [
            'acf_fc_layout' => 'hero_with_breadcrumbs',
            'layout_style' => 'image_split',
            'show_breadcrumbs' => 1,
            'breadcrumb_source' => 'manual',
            'manual_breadcrumbs' => [
                ['breadcrumb_link' => ['title' => 'Home', 'url' => $home, 'target' => '']],
                ['breadcrumb_link' => ['title' => 'Getting Help', 'url' => $getting_help_url, 'target' => '']],
            ],
            'current_crumb_label' => 'Learning & Resource Hub',
            'heading_tag' => 'h1',
            'heading' => 'Learning & Resource Hub',
            'content' => '<p>Browse our learning resources and information booklets on mental health topics.</p>',
            'hero_image' => $hero_image_id,
            'background_color' => '#C6ECF4',
            'breadcrumb_background_color' => '#F1F8F9',
            'heading_color' => '#08284B',
            'text_color' => '#08284B',
        ],
        matrix_seed_getting_help_useful_links_row($getting_help_url),
        [
            'acf_fc_layout' => 'related_cards',
            'heading_tag' => 'h2',
            'heading' => 'Resources',
            'intro_text' => '',
            'cards' => $hub_cards,
            'background_color' => '#FFFFFF',
            'columns' => '3',
            'padding_settings' => $section_padding,
        ],
    ];

    update_field('hero_content_blocks', [], $learning_hub_id);
    update_field('flexible_content_blocks', $hub_rows, $learning_hub_id);
}

$section_page_ids = array_merge(
    array_keys($direct_children),
    [$getting_help_id, $insurance_id, $learning_hub_id, 1411, 1415],
    $booklet_ids
);

$section_page_ids = array_values(array_unique(array_filter(array_map('intval', $section_page_ids))));

foreach ($section_page_ids as $page_id) {
    $extra = [];

    if ($page_id === 1415 && $insurance_id > 0) {
        $extra[] = [
            'title' => 'Insurance Information',
            'url' => $insurance_url,
            'target' => '',
        ];
    }

    if (in_array($page_id, $booklet_ids, true) && $learning_hub_id > 0) {
        $extra[] = [
            'title' => 'Learning & Resource Hub',
            'url' => $learning_hub_url,
            'target' => '',
        ];
    }

    if ($page_id === 1411) {
        $extra[] = [
            'title' => 'Carers & supporters',
            'url' => home_url('/getting-help/carers-supporters/'),
            'target' => '',
        ];
    }

    matrix_seed_update_getting_help_page_nav($page_id, $getting_help_url, $extra);
}

if (class_exists('WP_CLI')) {
    WP_CLI::success(sprintf(
        'Seeded Getting Help section (parent %d) with %d child pages updated.',
        $getting_help_id,
        count($section_page_ids) - 1
    ));
    WP_CLI::log('Landing: ' . get_permalink($getting_help_id));
    WP_CLI::log('Concerned: ' . get_permalink(1412));
    WP_CLI::log('FAQs: ' . get_permalink(1413));
}
