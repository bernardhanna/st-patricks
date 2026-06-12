<?php

/**
 * Seed About Us landing page (Figma 2780:3447).
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-about-us.php
 */

$post_id = (int) (get_page_by_path('about-us')?->ID ?? 0);

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at about-us.');
    }

    exit(1);
}

if (! function_exists('matrix_seed_import_theme_image')) {
    function matrix_seed_import_theme_image(string $relative_path, string $title): int
    {
        $file = get_template_directory() . '/' . ltrim($relative_path, '/');

        if (! is_readable($file)) {
            return 0;
        }

        $filename = basename($file);
        $existing = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => '_matrix_seed_source',
                    'value' => $relative_path,
                ],
            ],
        ]);

        if ($existing !== []) {
            return (int) $existing[0]->ID;
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $upload = wp_upload_bits($filename, null, (string) file_get_contents($file));

        if (! empty($upload['error'])) {
            return 0;
        }

        $filetype = wp_check_filetype($filename, null);
        $attachment_id = wp_insert_attachment([
            'post_mime_type' => $filetype['type'] ?: 'image/png',
            'post_title' => $title,
            'post_content' => '',
            'post_status' => 'inherit',
        ], $upload['file']);

        if (is_wp_error($attachment_id) || ! $attachment_id) {
            return 0;
        }

        $metadata = wp_generate_attachment_metadata($attachment_id, $upload['file']);
        wp_update_attachment_metadata($attachment_id, $metadata);
        update_post_meta($attachment_id, '_matrix_seed_source', $relative_path);

        return (int) $attachment_id;
    }
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
    function matrix_seed_resolve_image(string $figma_url, string $cache_key, string $title, string $theme_fallback = ''): int
    {
        $id = matrix_seed_import_remote_image($figma_url, $title, $cache_key);

        if ($id > 0) {
            return $id;
        }

        if ($theme_fallback !== '') {
            $id = matrix_seed_import_theme_image($theme_fallback, $title);

            if ($id > 0) {
                return $id;
            }
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

if (! function_exists('matrix_seed_attachment_url')) {
    function matrix_seed_attachment_url(int $attachment_id): string
    {
        if ($attachment_id <= 0) {
            return '';
        }

        $url = wp_get_attachment_url($attachment_id);

        return is_string($url) ? $url : '';
    }
}

$figma = [
    'hero' => 'https://www.figma.com/api/mcp/asset/9b95640a-3098-4fe6-866c-b0519cdb8e84',
    'overview' => 'https://www.figma.com/api/mcp/asset/691d2e09-63b9-4c37-8082-9344ba5b4730',
    'policies' => 'https://www.figma.com/api/mcp/asset/e5106661-6864-4bb2-91fa-5d1641e63a78',
    'research' => 'https://www.figma.com/api/mcp/asset/bcf83b9a-fef0-4774-96bf-d2f2072cc9cc',
    'present_future' => 'https://www.figma.com/api/mcp/asset/c7fa6391-da11-409f-ab47-76ed2e31cce4',
    'our_team' => 'https://www.figma.com/api/mcp/asset/f8b01341-2895-40aa-b8fc-7244320387bc',
    'careers' => 'https://www.figma.com/api/mcp/asset/9b39a382-3e47-4820-a6cd-7cce650e7647',
    'advocacy' => 'https://www.figma.com/api/mcp/asset/61137e34-2370-477d-9f8a-31b0053b4485',
    'locations' => 'https://www.figma.com/api/mcp/asset/813fd211-f203-48b2-82bf-174e7c246752',
    'support_us' => 'https://www.figma.com/api/mcp/asset/51f630cf-2d21-4237-b5c3-ddbc3ec0ba3e',
    'media_queries' => 'https://www.figma.com/api/mcp/asset/cbd8314e-1a7d-4733-a681-c5a4df736dab',
    'our_history' => 'https://www.figma.com/api/mcp/asset/0ca08f1d-25db-4c5c-a86e-69358525a97d',
];

$theme_grid = 'assets/images/about-mental-health-grid/';
$home = home_url('/');

$hero_image_id = matrix_seed_resolve_image(
    $figma['hero'],
    'about-us-landing-hero',
    'About Us landing hero',
    $theme_grid . 'overview.png'
);

$card_definitions = [
    [
        'title' => 'Overview',
        'url' => $home . 'about-us/overview/',
        'tone' => 'bg1',
        'figma' => $figma['overview'],
        'cache' => 'about-us-grid-overview',
        'fallback' => $theme_grid . 'overview.png',
    ],
    [
        'title' => 'Our team',
        'url' => $home . 'about-us/our-team/',
        'tone' => 'bg1',
        'figma' => $figma['our_team'],
        'cache' => 'about-us-grid-our-team',
        'fallback' => $theme_grid . 'our-team.png',
    ],
    [
        'title' => 'Support Us',
        'url' => $home . 'about-us/support-us/',
        'tone' => 'bg1',
        'figma' => $figma['support_us'],
        'cache' => 'about-us-grid-support-us',
        'fallback' => $theme_grid . 'support-us.png',
    ],
    [
        'title' => 'Policies and Publications',
        'url' => $home . 'about-us/policies-and-publications/',
        'tone' => 'bg2',
        'figma' => $figma['policies'],
        'cache' => 'about-us-grid-policies',
        'fallback' => $theme_grid . 'policies-and-publications.png',
    ],
    [
        'title' => 'Careers',
        'url' => $home . 'about-us/careers/',
        'tone' => 'bg2',
        'figma' => $figma['careers'],
        'cache' => 'about-us-grid-careers',
        'fallback' => $theme_grid . 'careers.png',
    ],
    [
        'title' => 'Media Queries',
        'url' => $home . 'about-us/media-queries/',
        'tone' => 'bg2',
        'figma' => $figma['media_queries'],
        'cache' => 'about-us-grid-media-queries',
        'fallback' => $theme_grid . 'media-queries.png',
    ],
    [
        'title' => 'Research',
        'url' => $home . 'about-us/research/',
        'tone' => 'bg3',
        'figma' => $figma['research'],
        'cache' => 'about-us-grid-research',
        'fallback' => $theme_grid . 'research.png',
    ],
    [
        'title' => 'Advocacy',
        'url' => $home . 'about-us/advocacy/',
        'tone' => 'bg3',
        'figma' => $figma['advocacy'],
        'cache' => 'about-us-grid-advocacy',
        'fallback' => $theme_grid . 'advocacy.png',
    ],
    [
        'title' => 'Our History',
        'url' => $home . 'about-us/our-history/',
        'tone' => 'bg3',
        'figma' => $figma['our_history'],
        'cache' => 'about-us-grid-our-history',
        'fallback' => $theme_grid . 'our-history.png',
    ],
    [
        'title' => 'Our present and future',
        'url' => $home . 'about-us/our-present-and-future/',
        'tone' => 'bg4',
        'figma' => $figma['present_future'],
        'cache' => 'about-us-grid-present-future',
        'fallback' => $theme_grid . 'our-present-and-future.png',
    ],
    [
        'title' => 'Our Locations',
        'url' => $home . 'about-us/our-locations/',
        'tone' => 'bg4',
        'figma' => $figma['locations'],
        'cache' => 'about-us-grid-locations',
        'fallback' => $theme_grid . 'our-locations.png',
    ],
];

$about_links = [];

foreach ($card_definitions as $card) {
    $image_id = matrix_seed_resolve_image(
        $card['figma'],
        $card['cache'],
        'About Us grid – ' . $card['title'],
        $card['fallback']
    );

    $about_links[] = [
        'icon' => '',
        'image_url' => matrix_seed_attachment_url($image_id),
        'title' => $card['title'],
        'description' => '',
        'link' => [
            'title' => $card['title'],
            'url' => $card['url'],
            'target' => '',
        ],
        'card_tone' => $card['tone'],
    ];
}

$hero_intro = "Welcome to St Patrick's Mental Health Services, Ireland's largest independent, not-for-profit mental health service. Explore our teams, locations, research, advocacy, and the communities we support across Ireland.";

$section_padding = [
    ['screen_size' => 'mob', 'padding_top' => '3', 'padding_bottom' => '3'],
    ['screen_size' => 'lg', 'padding_top' => '6.25', 'padding_bottom' => '6.25'],
];

$grid_padding = [
    ['screen_size' => 'mob', 'padding_top' => '3', 'padding_bottom' => '6.25'],
    ['screen_size' => 'lg', 'padding_top' => '6.25', 'padding_bottom' => '6.25'],
];

$flexi_rows = [
    [
        'acf_fc_layout' => 'hero_with_breadcrumbs',
        'layout_style' => 'image_split',
        'show_breadcrumbs' => 1,
        'breadcrumb_source' => 'manual',
        'manual_breadcrumbs' => [
            ['breadcrumb_link' => ['title' => 'Home', 'url' => $home, 'target' => '']],
        ],
        'current_crumb_label' => 'About Us',
        'heading_tag' => 'h1',
        'heading' => 'About Us',
        'content' => '<p>' . esc_html($hero_intro) . '</p>',
        'primary_button' => '',
        'hero_image' => $hero_image_id,
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
    ],
    [
        'acf_fc_layout' => 'about_links_grid',
        'heading_tag' => 'h2',
        'heading_text' => 'About us',
        'intro_text' => '',
        'links' => $about_links,
        'bg_color' => '#F1F8F9',
        'heading_color' => '#0B0B08',
        'intro_color' => '#4A4B37',
        'columns' => '3',
        'padding_settings' => $grid_padding,
    ],
];

update_field('hero_content_blocks', [], $post_id);
update_field('flexible_content_blocks', $flexi_rows, $post_id);

$saved_rows = get_field('flexible_content_blocks', $post_id);
$saved_count = is_array($saved_rows) ? count($saved_rows) : 0;

if (class_exists('WP_CLI')) {
    if ($saved_count === count($flexi_rows)) {
        WP_CLI::success(sprintf(
            'Seeded About Us landing page (%d) with %d flexi blocks and %d link cards.',
            $post_id,
            $saved_count,
            count($about_links)
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
