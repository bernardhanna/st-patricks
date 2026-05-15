<?php

/**
 * Seed Our History (page 272) to match Figma frame 3279:19822.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-our-history.php
 */

$post_id = (int) (get_page_by_path('about-us/our-history')?->ID ?? 0);

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at about-us/our-history.');
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

if (! function_exists('matrix_seed_build_image_field')) {
    function matrix_seed_build_image_field(int $attachment_id, string $alt): array
    {
        if ($attachment_id <= 0) {
            return [];
        }

        return [
            'ID' => $attachment_id,
            'url' => wp_get_attachment_url($attachment_id),
            'alt' => $alt,
            'title' => $alt,
        ];
    }
}

$home = home_url('/');
$about_us_url = home_url('/about-us/');
$present_future_url = home_url('/about-us/our-present-and-future/');

$figma = [
    'hero' => 'https://www.figma.com/api/mcp/asset/299f0024-f6de-439f-8f61-959b56c743f9',
    'video' => 'https://www.figma.com/api/mcp/asset/909e933f-86ba-42b3-bace-e364ef2fb5f6',
    'milestone' => 'https://www.figma.com/api/mcp/asset/909e933f-86ba-42b3-bace-e364ef2fb5f6',
];

$hero_image_id = matrix_seed_resolve_image($figma['hero'], 'our-history-hero-3279-19822', 'Our History hero');
$video_poster_id = matrix_seed_resolve_image($figma['video'], 'our-history-video-3279-19822', 'Our History video');
$milestone_image_id = matrix_seed_resolve_image($figma['milestone'], 'our-history-milestone-3279-19822', 'Our History milestone');

$hero_copy = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad mini.Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
$lorem = '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat.</p>';
$timeline_intro = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse varius enim in eros elementum tristique. Duis cursus, mi quis viverra ornare, eros dolor interdum nulla, ut commodo diam libero vitae erat.</p>';

$cta = [
    'title' => 'Supporting material CTA',
    'url' => '#',
    'target' => '_self',
];

$section_padding = [
    ['screen_size' => 'mob', 'padding_top' => '3', 'padding_bottom' => '3'],
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
            ['breadcrumb_link' => ['title' => 'Who we are', 'url' => $about_us_url, 'target' => '']],
        ],
        'current_crumb_label' => 'Our history and aims',
        'heading_tag' => 'h1',
        'heading' => 'Our History',
        'content' => '<p>' . esc_html($hero_copy) . '</p>',
        'primary_button' => '',
        'hero_image' => $hero_image_id,
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
    ],
    [
        'acf_fc_layout' => 'video_showcase',
        'heading_tag' => 'h2',
        'heading' => 'Video Title St. Patrick\'s Mental Health Services 1746-2016',
        'intro' => '',
        'layout_style' => 'feature_single',
        'slides' => [
            [
                'poster_image' => matrix_seed_build_image_field($video_poster_id, 'St Patrick\'s Mental Health Services 1746-2016'),
                'video_source_type' => 'embed_url',
                'video_embed_url' => 'https://www.youtube.com/watch?v=ysz5S6PUM-U',
                'caption' => '',
                'cta_link' => '',
            ],
        ],
        'section_background' => 'linear-gradient(-78.99deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'timeline',
        'heading_tag' => 'h2',
        'heading' => 'Our History, timeline title - medium length',
        'intro' => $timeline_intro,
        'timeline_items' => [
            [
                'side' => 'left',
                'event_date_label' => '2.8.1746',
                'item_heading' => 'Short heading Lorem ipsum dolor',
                'item_heading_tag' => 'h3',
                'item_text' => $lorem,
            ],
            [
                'side' => 'right',
                'event_date_label' => '2.8.1946',
                'image' => $milestone_image_id,
                'item_heading' => 'H3. Milestone',
                'item_heading_tag' => 'h3',
                'item_text' => $lorem,
                'cta_link' => $cta,
            ],
            [
                'side' => 'left',
                'event_date_label' => '2.8.2000',
                'item_heading' => 'H3. Milestone',
                'item_heading_tag' => 'h3',
                'item_text' => $lorem,
                'cta_link' => $cta,
            ],
            [
                'side' => 'right',
                'event_date_label' => '2.8.2024',
                'item_heading' => 'H3. Milestone',
                'item_heading_tag' => 'h3',
                'item_text' => $lorem,
                'cta_link' => $cta,
            ],
            [
                'side' => 'left',
                'event_date_label' => '2.8.2024',
                'image' => $milestone_image_id,
                'item_heading' => 'H3. Milestone',
                'item_heading_tag' => 'h3',
                'item_text' => $lorem,
                'cta_link' => $cta,
            ],
            [
                'side' => 'right',
                'event_date_label' => '2.8.2024',
                'item_heading' => 'H3. Milestone',
                'item_heading_tag' => 'h3',
                'item_text' => $lorem,
                'cta_link' => $cta,
            ],
        ],
        'footer_button_link' => [
            'title' => 'Our Present and Future',
            'url' => $present_future_url,
            'target' => '',
        ],
        'card_background_color' => '#E4F4D6',
        'timeline_accent_color' => '#6FC9C0',
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
            'Seeded Our History page (%d) with %d flexi blocks.',
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
