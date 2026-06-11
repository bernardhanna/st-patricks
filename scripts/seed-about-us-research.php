<?php

/**
 * Seed About Us > Research (page 265) to match Figma frame 2780:3856.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-about-us-research.php
 */

$post_id = (int) (get_page_by_path('about-us/research')?->ID ?? 0);

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at about-us/research.');
    }

    exit(1);
}

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

if (! function_exists('matrix_seed_build_research_cards')) {
    function matrix_seed_build_research_cards(array $image_ids, string $default_link_url): array
    {
        $titles = [
            'Duis aute irure dolor in lorem ipsum',
            'Dolor in reprehendert',
            'Duis aute irure dolor in lorem ipsum',
            'Dolor in reprehendert',
        ];
        $summary = '<p>Lorem ipsum dolor sit amet, cons ectetur adipiscing elit.</p>';
        $cards = [];

        foreach ($titles as $index => $title) {
            $image_id = $image_ids[$index % count($image_ids)] ?? 0;
            $cards[] = [
                'title' => $title,
                'summary' => $summary,
                'image' => $image_id > 0 ? $image_id : '',
                'link' => [
                    'title' => $title,
                    'url' => $default_link_url,
                    'target' => '',
                ],
            ];
        }

        return $cards;
    }
}

$home = home_url('/');
$about_us_url = home_url('/about-us/');
$current_projects_url = home_url('/current-research-projects/');
$past_projects_url = home_url('/past-research-projects/');
$ethics_committee_url = home_url('/research-ethics-committee/');
$spire_url = home_url('/research-library-spire/');

$figma = [
    'hero' => 'https://www.figma.com/api/mcp/asset/7498d9fa-3124-4241-bbff-c5e72cb17aec',
    'card_a' => 'https://www.figma.com/api/mcp/asset/07e0c847-3ce6-4408-94b9-9a2a98347bb8',
    'card_b' => 'https://www.figma.com/api/mcp/asset/06212956-35f5-4e9c-a780-32b9a9586611',
    'card_c' => 'https://www.figma.com/api/mcp/asset/e16321aa-5447-4917-be04-e52b2c545bcd',
    'card_d' => 'https://www.figma.com/api/mcp/asset/f7cde890-22ea-4e3d-b3ce-7a6a646d2e52',
    'spire' => 'https://www.figma.com/api/mcp/asset/bfb3e585-a0c5-4f2a-9ff7-6df1eef9971b',
];

$hero_image_id = matrix_seed_resolve_image($figma['hero'], 'research-hero-2780-3856', 'Research hero');
$spire_image_id = matrix_seed_resolve_image($figma['spire'], 'research-spire-2780-3960', 'SPIRE research hub');

$card_image_ids = [
    matrix_seed_resolve_image($figma['card_a'], 'research-card-a-2780-3856', 'Research card image A'),
    matrix_seed_resolve_image($figma['card_b'], 'research-card-b-2780-3856', 'Research card image B'),
    matrix_seed_resolve_image($figma['card_c'], 'research-card-c-2780-3856', 'Research card image C'),
    matrix_seed_resolve_image($figma['card_d'], 'research-card-d-2780-3856', 'Research card image D'),
];

$lorem_hero = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad mini.';
$intro_current = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>';
$intro_past = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad mini incididunt ut labore et dolore magn.</p>';
$intro_ethics = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad mini incididunt ut lab, sed do eiusmod tempore et dolore magn.</p>';
$spire_body = '<p>About SPIRE Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat.</p>';

$hero_content = '<p>' . esc_html($lorem_hero) . '</p>';

$section_padding = [
    [
        'screen_size' => 'mob',
        'padding_top' => '3',
        'padding_bottom' => '3',
    ],
    [
        'screen_size' => 'lg',
        'padding_top' => '6.25',
        'padding_bottom' => '6.25',
    ],
];

$flexi_rows = [
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
            [
                'breadcrumb_link' => [
                    'title' => 'About Us',
                    'url' => $about_us_url,
                    'target' => '',
                ],
            ],
        ],
        'current_crumb_label' => 'Research',
        'heading_tag' => 'h1',
        'heading' => 'Research',
        'content' => $hero_content,
        'primary_button' => [
            'title' => 'Mental Health Research Hub',
            'url' => $spire_url,
            'target' => '',
        ],
        'hero_image' => $hero_image_id,
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
    ],
    [
        'acf_fc_layout' => 'research_cards_grid',
        'heading' => 'Current Research Projects',
        'heading_tag' => 'h2',
        'intro' => $intro_current,
        'cards' => matrix_seed_build_research_cards($card_image_ids, $current_projects_url),
        'footer_button_link' => [
            'title' => 'All Current Research Projects',
            'url' => $current_projects_url,
            'target' => '',
        ],
        'background_color' => '#FFFFFF',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'research_cards_grid',
        'heading' => 'Past Research Projects',
        'heading_tag' => 'h2',
        'intro' => $intro_past,
        'cards' => matrix_seed_build_research_cards($card_image_ids, $past_projects_url),
        'footer_button_link' => [
            'title' => 'All Past Research Projects',
            'url' => $past_projects_url,
            'target' => '',
        ],
        'background_color' => '#FBFAF7',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'research_cards_grid',
        'heading' => 'Our Research Ethics Committee',
        'heading_tag' => 'h2',
        'intro' => $intro_ethics,
        'cards' => matrix_seed_build_research_cards($card_image_ids, $ethics_committee_url),
        'footer_button_link' => [
            'title' => 'View Ethics Committee',
            'url' => $ethics_committee_url,
            'target' => '',
        ],
        'background_color' => '#FFFFFF',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Access our Central Hub for mental health research',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'content' => $spire_body,
        'primary_button' => [
            'title' => 'Visit SPIRE',
            'url' => $spire_url,
            'target' => '',
        ],
        'primary_button_variant' => 'filled',
        'layout_style' => 'image_right',
        'background_type' => 'gradient',
        'background_gradient' => 'linear-gradient(-70.72deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
        'image' => $spire_image_id,
        'padding_settings' => $section_padding,
    ],
];

update_field('hero_content_blocks', [], $post_id);
update_field('flexible_content_blocks', $flexi_rows, $post_id);

$saved_rows = get_field('flexible_content_blocks', $post_id);
$saved_count = is_array($saved_rows) ? count($saved_rows) : 0;

if (class_exists('WP_CLI')) {
    if ($saved_count === count($flexi_rows)) {
        WP_CLI::success(sprintf(
            'Seeded Research page (%d) with %d flexi blocks.',
            $post_id,
            $saved_count
        ));
    } else {
        WP_CLI::warning(sprintf(
            'Updated page %d but expected %d blocks, found %d.',
            $post_id,
            count($flexi_rows),
            $saved_count
        ));
    }
}
