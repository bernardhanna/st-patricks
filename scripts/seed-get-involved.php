<?php

/**
 * Create /get-involved/ and organise Get Involved section pages under it.
 *
 * Sources:
 * - https://www.stpatricks.ie/get-involved
 * - Subpages (peer-support, fundraising, donations, etc.) already migrated; this reparents them.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-get-involved.php
 */

require_once __DIR__ . '/lib/page-seed-conventions.php';
require_once get_template_directory() . '/inc/migrate-functions.php';

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

if (! function_exists('matrix_seed_get_involved_section_links')) {
    /**
     * @return array<int, array{link: array{title: string, url: string, target: string}}>
     */
    function matrix_seed_get_involved_section_links(string $base_url): array
    {
        $items = [
            ['Service User Participation', 'service-user-participation/'],
            ['Peer Support', 'peer-support/'],
            ['Fundraising', 'fundraising/'],
            ['Donations', 'donations/'],
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

if (! function_exists('matrix_seed_get_involved_useful_links_row')) {
    /**
     * @return array<string, mixed>
     */
    function matrix_seed_get_involved_useful_links_row(string $base_url): array
    {
        return [
            'acf_fc_layout' => 'useful_links',
            'heading_tag' => 'h2',
            'heading' => 'In this section',
            'variant' => 'flexi',
            'links' => matrix_seed_get_involved_section_links($base_url),
            'background_color' => '#F1F8F9',
            'padding_settings' => [
                ['screen_size' => 'mob', 'padding_top' => '1.5', 'padding_bottom' => '1.5'],
                ['screen_size' => 'lg', 'padding_top' => '2', 'padding_bottom' => '2'],
            ],
        ];
    }
}

if (! function_exists('matrix_seed_update_get_involved_page_nav')) {
    function matrix_seed_update_get_involved_page_nav(int $post_id, string $get_involved_url, array $extra_breadcrumbs = []): void
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
                $rows[$index]['links'] = matrix_seed_get_involved_section_links($get_involved_url);
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
                            'title' => 'Get Involved',
                            'url' => $get_involved_url,
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

            if (($row['acf_fc_layout'] ?? '') === 'related_cards' && ! empty($row['cards']) && is_array($row['cards'])) {
                foreach ($row['cards'] as $card_index => $card) {
                    if (! is_array($card) || empty($card['link']) || ! is_array($card['link'])) {
                        continue;
                    }

                    $url = (string) ($card['link']['url'] ?? '');

                    if ($url === '') {
                        continue;
                    }

                    $resolved = matrix_migrate_resolve_migrated_url($url);

                    if ($resolved !== $url) {
                        $rows[$index]['cards'][$card_index]['link']['url'] = $resolved;
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
$get_involved_url = home_url('/get-involved/');
$service_user_participation_url = $get_involved_url . 'service-user-participation/';
$peer_support_url = $get_involved_url . 'peer-support/';
$fundraising_url = $get_involved_url . 'fundraising/';
$donations_url = $get_involved_url . 'donations/';
$learning_hub_url = home_url('/getting-help/learning-resource-hub/');

$get_involved_id = matrix_seed_ensure_page('get-involved', 'Get Involved', 0, 'get-involved');

if ($get_involved_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not create get-involved page.');
    }

    exit(1);
}

$direct_children = [
    1403 => 'get-involved/service-user-participation',
    1402 => 'get-involved/peer-support',
    1400 => 'get-involved/fundraising',
    1391 => 'get-involved/donations',
];

foreach ($direct_children as $page_id => $old_path) {
    wp_update_post([
        'ID' => $page_id,
        'post_parent' => $get_involved_id,
    ]);
    update_post_meta($page_id, '_matrix_migrate_old_path', $old_path);
}

$participation_children = [
    1404 => 'get-involved/service-user-participation/news-for-service-users',
    1405 => 'get-involved/service-user-participation/service-user-advisory-network-suan',
    1406 => 'get-involved/service-user-participation/service-user-and-supporters-council-suas',
    1408 => 'get-involved/service-user-participation/service-user-experience-survey',
];

foreach ($participation_children as $page_id => $old_path) {
    wp_update_post([
        'ID' => $page_id,
        'post_parent' => 1403,
    ]);
    update_post_meta($page_id, '_matrix_migrate_old_path', $old_path);
}

$hero_image_id = (int) matrix_migrate_attachment_id_for_source_path('/media/3121/get-involved-banner.png');
$promo_image_id = (int) matrix_migrate_attachment_id_for_source_path('/media/3321/peer-support-promo-banner.png');
$fundraising_image_id = (int) matrix_migrate_attachment_id_for_source_path('/media/3119/fundraising-page-featured-image.png');
$donations_image_id = (int) matrix_migrate_attachment_id_for_source_path('/media/3123/donations-banner.png');

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
        'current_crumb_label' => 'Get Involved',
        'heading_tag' => 'h1',
        'heading' => 'Get Involved',
        'content' => '<p>Find out more about service user engagement, fundraising and more at St Patrick\'s Mental Health Services.</p>',
        'hero_image' => $hero_image_id,
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
    ],
    matrix_seed_get_involved_useful_links_row($get_involved_url),
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Service user engagement',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'content' => '<p>We believe in listening to our service users and their supporters, and have a number of networks and forums for you to give feedback and help shape all that we do.</p>',
        'primary_button' => [
            'title' => 'See our service user groups here',
            'url' => $service_user_participation_url,
            'target' => '',
        ],
        'primary_button_variant' => 'filled',
        'layout_style' => 'image_right',
        'background_type' => 'color',
        'background_color' => '#FBFAF7',
        'image' => $promo_image_id,
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'related_cards',
        'heading_tag' => 'h2',
        'heading' => 'Support our work',
        'intro_text' => '',
        'cards' => [
            [
                'image' => $fundraising_image_id,
                'title' => 'Fundraising',
                'description' => 'Learn about our Capital Development Programme and the Jonathan Swift Institute of Mentally Healthy Living.',
                'link' => [
                    'title' => 'Fundraising',
                    'url' => $fundraising_url,
                    'target' => '',
                ],
            ],
            [
                'image' => $donations_image_id,
                'title' => 'Donations',
                'description' => 'Our aim is to provide the highest quality mental healthcare to as many people as possible in Ireland.',
                'link' => [
                    'title' => 'Donations',
                    'url' => $donations_url,
                    'target' => '',
                ],
            ],
        ],
        'background_color' => '#FFFFFF',
        'columns' => '2',
        'padding_settings' => $section_padding,
    ],
];

update_field('hero_content_blocks', [], $get_involved_id);
update_field('flexible_content_blocks', $landing_rows, $get_involved_id);

$section_page_ids = array_merge(
    array_keys($direct_children),
    array_keys($participation_children),
    [$get_involved_id]
);

$participation_url = $service_user_participation_url;

foreach ($section_page_ids as $page_id) {
    $extra = [];

    if (in_array($page_id, array_keys($participation_children), true)) {
        $extra[] = [
            'title' => 'Service User Participation',
            'url' => $participation_url,
            'target' => '',
        ];
    }

    matrix_seed_update_get_involved_page_nav($page_id, $get_involved_url, $extra);
}

if (class_exists('WP_CLI')) {
    WP_CLI::success(sprintf(
        'Seeded Get Involved section (parent %d) with %d pages updated.',
        $get_involved_id,
        count($section_page_ids)
    ));
    WP_CLI::log('Landing: ' . get_permalink($get_involved_id));
    WP_CLI::log('Peer Support: ' . get_permalink(1402));
    WP_CLI::log('Fundraising: ' . get_permalink(1400));
    WP_CLI::log('Donations: ' . get_permalink(1391));
}
