<?php

/**
 * Seed About Us > Overview (page 216) to match Figma frame 2780:3106.
 *
 * Run from WP root:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/seed-about-us-overview.php
 */

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

// Figma MCP asset URLs (frame 2780:3106).
$figma = [
    'hero' => 'https://www.figma.com/api/mcp/asset/8af8a20c-4d1a-49e0-b0a2-4a92eecbc1cf',
    'welcome' => 'https://www.figma.com/api/mcp/asset/2769e98f-0183-42e8-b774-54be5195d6b6',
    'vision' => 'https://www.figma.com/api/mcp/asset/98e0cc2f-095f-4fcb-8fd5-d5f989741981',
    'commitment' => 'https://www.figma.com/api/mcp/asset/7bf5818b-8bbe-43e1-a8ee-0ea0c7c117e9',
    'leader' => 'https://www.figma.com/api/mcp/asset/ebf445d0-7a52-437f-b381-505fa129ea36',
    'testimonials_bg' => 'https://www.figma.com/api/mcp/asset/fff21cc9-734d-411e-acb5-c7c0364f7ce5',
    'video_poster' => 'https://www.figma.com/api/mcp/asset/97df3fe2-1c5b-410e-a1d9-85b227f3e7ed',
];

$theme_fallback = 'assets/images/about-mental-health-grid/overview.png';

$post_id = 216;
$about_us_url = get_permalink(194) ?: home_url('/about-us/');

$hero_image_id = matrix_seed_resolve_image($figma['hero'], 'overview-hero', 'About Us Overview hero', $theme_fallback);
$welcome_image_id = matrix_seed_resolve_image($figma['welcome'], 'overview-welcome', 'About Us Overview welcome', $theme_fallback);
$vision_image_id = matrix_seed_resolve_image($figma['vision'], 'overview-vision', 'About Us Overview vision', $theme_fallback);
$commitment_image_id = matrix_seed_resolve_image($figma['commitment'], 'overview-commitment', 'About Us Overview commitment', $theme_fallback);
$leader_image_id = matrix_seed_resolve_image($figma['leader'], 'overview-leader', 'About Us Overview leader', $theme_fallback);
$testimonials_bg_id = matrix_seed_resolve_image($figma['testimonials_bg'], 'overview-testimonials-bg', 'About Us Overview testimonials background');
$video_poster_id = matrix_seed_resolve_image($figma['video_poster'], 'overview-video-poster', 'About Us Overview video poster', $theme_fallback);

$home_partners_row = null;
$home_flexi = get_field('flexible_content_blocks', (int) get_option('page_on_front'));

if (is_array($home_flexi)) {
    foreach ($home_flexi as $row) {
        if (($row['acf_fc_layout'] ?? '') === 'partners') {
            $home_partners_row = $row;
            break;
        }
    }
}

$partner_rows = [];

if (is_array($home_partners_row['partners'] ?? null)) {
    foreach ($home_partners_row['partners'] as $partner) {
        $logo = $partner['logo'] ?? 0;
        $logo_id = is_array($logo) ? (int) ($logo['ID'] ?? $logo['id'] ?? 0) : (int) $logo;

        if ($logo_id === 0) {
            continue;
        }

        $partner_rows[] = [
            'logo' => $logo_id,
            'link' => $partner['link'] ?? '',
        ];
    }
}

$lorem_short = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
$lorem_long = '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>';

$testimonial_quote = '<p>Through our Advocacy Committee, we respond to all relevant calls for submissions by the Dáil, Seanad and Government departments.</p>';

$testimonial_items = [];

for ($i = 0; $i < 4; $i++) {
    $testimonial_items[] = [
        'quote' => $testimonial_quote,
        'author_name' => 'Tom',
        'author_title' => 'Service User',
        'card_tone' => $i % 2 === 0 ? 'lavender' : 'mauve',
    ];
}

$what_we_offer_services = [
    [
        'service_title' => 'Inpatient Care',
        'service_description' => '<p>' . $lorem_short . '</p>',
        'accent_color' => '#6FC9C0',
        'show_service_icon' => 0,
    ],
    [
        'service_title' => "St Patrick's at Home",
        'service_description' => '<p>' . $lorem_short . '</p>',
        'accent_color' => '#C3DBAE',
        'show_service_icon' => 0,
    ],
    [
        'service_title' => 'Outpatient Care - Dean Clinics',
        'service_description' => '<p>' . $lorem_short . '</p>',
        'accent_color' => '#B4A8CE',
        'show_service_icon' => 0,
    ],
    [
        'service_title' => 'Day Programmes',
        'service_description' => '<p>' . $lorem_short . '</p>',
        'accent_color' => '#E4B8D6',
        'show_service_icon' => 0,
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
                    'url' => home_url('/'),
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
        'current_crumb_label' => 'Overview',
        'heading_tag' => 'h1',
        'heading' => 'Overview - Short title',
        'content' => '<p>' . $lorem_short . '</p>',
        'hero_image' => $hero_image_id,
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => "Welcome to St Patrick's Mental Health Services",
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'intro_text' => '<p><strong>' . $lorem_short . '</strong></p>',
        'content' => $lorem_long,
        'layout_style' => 'image_left',
        'background_type' => 'white',
        'image' => $welcome_image_id,
    ],
    [
        'acf_fc_layout' => 'what_we_offer',
        'heading' => 'What we offer',
        'heading_tag' => 'h2',
        'layout_style' => 'intro_two_column',
        'intro_text' => $lorem_short,
        'services' => $what_we_offer_services,
        'background_gradient' => '#FBF8F3',
    ],
    [
        'acf_fc_layout' => 'testimonials',
        'heading_tag' => 'h2',
        'heading_text' => 'Testimonials',
        'layout_style' => 'grid_standard',
        'source_mode' => 'manual',
        'manual_items' => $testimonial_items,
        'footer_action_mode' => 'load_more',
        'load_more_button_text' => 'Load more testimonials',
        'background_image' => $testimonials_bg_id,
        'background_color' => '#F6EDE0',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Our Vision and Mission',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'intro_text' => '<p><strong>Our vision is to see a society where all citizens are empowered and given the opportunity to live mentally healthy lives</strong></p>',
        'content' => $lorem_long,
        'layout_style' => 'image_left',
        'background_type' => 'white',
        'image' => $vision_image_id,
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Our Commitment',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'intro_text' => '<p><strong>At St Patrick\'s Mental Health Services (SPMHS), we provide community and outpatient care through our Dean Clinics and day patient services through our Wellness and Recovery Centre.</strong></p>',
        'content' => $lorem_long,
        'primary_button' => [
            'title' => 'Support Us',
            'url' => home_url('/support-us/'),
            'target' => '',
        ],
        'primary_button_variant' => 'outline',
        'layout_style' => 'image_right',
        'background_type' => 'cream',
        'image' => $commitment_image_id,
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'A recognised leader in Mental Health',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'intro_text' => '<p><strong>Our vision is to see a society where all citizens are empowered and given the opportunity to live mentally healthy lives</strong></p>',
        'content' => $lorem_long,
        'primary_button' => [
            'title' => 'Our Future',
            'url' => home_url('/about-us/our-present-and-future/'),
            'target' => '',
        ],
        'primary_button_variant' => 'outline',
        'layout_style' => 'image_left',
        'background_type' => 'white',
        'image' => $leader_image_id,
    ],
    [
        'acf_fc_layout' => 'video_showcase',
        'heading_tag' => 'h2',
        'heading' => "Video Title St. Patrick's Mental Health Services 1746-2016",
        'intro' => '',
        'layout_style' => 'feature_single',
        'slides' => [
            [
                'poster_image' => $video_poster_id,
                'video_source_type' => 'embed_url',
                'video_embed_url' => 'https://www.youtube.com/watch?v=ysz5S6PUM-U',
                'caption' => '',
                'cta_link' => '',
            ],
        ],
        'section_background' => 'linear-gradient(279deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
        'padding_settings' => [
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
        ],
    ],
    [
        'acf_fc_layout' => 'partners',
        'heading_tag' => $home_partners_row['heading_tag'] ?? 'h2',
        'heading_text' => 'Committed to quality care, human rights, and innovation',
        'partners' => $partner_rows,
        'background_color' => $home_partners_row['background_color'] ?? '#FFFFFF',
        'heading_color' => $home_partners_row['heading_color'] ?? '#1e293b',
        'show_card_style' => $home_partners_row['show_card_style'] ?? false,
        'padding_settings' => $home_partners_row['padding_settings'] ?? false,
    ],
    [
        'acf_fc_layout' => 'callout_bar',
        'message' => 'SPMHS is a registered charity (Registered Charity Number (RCN): 20000370).',
    ],
];

update_field('hero_content_blocks', [], $post_id);
update_field('flexible_content_blocks', $flexi_rows, $post_id);

$saved_rows = get_field('flexible_content_blocks', $post_id);
$saved_count = is_array($saved_rows) ? count($saved_rows) : 0;

if ($saved_count !== count($flexi_rows)) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error(
            'Failed to update About Us Overview page ' . $post_id
            . ' (expected ' . count($flexi_rows) . ' blocks, found ' . $saved_count . ')'
        );
    }

    exit(1);
}

$block_count = count($flexi_rows);
$message = "Seeded About Us Overview (page {$post_id}) with {$block_count} flexi blocks matching Figma 2780:3106.";

if (class_exists('WP_CLI')) {
    WP_CLI::success($message);
}

echo $message . "\n";
