<?php

/**
 * Seed Your Portal (page 256) to match Figma frame 2888:3250.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-your-portal.php
 */

$post_id = (int) (get_page_by_path('your-portal')?->ID ?? 0);

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at your-portal.');
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

if (! function_exists('matrix_seed_accordion_item')) {
    function matrix_seed_accordion_item(string $title, string $content, bool $starts_open = false): array
    {
        return [
            'title' => $title,
            'starts_open' => $starts_open ? 1 : 0,
            'content_rows' => [
                [
                    'icon_key' => '',
                    'icon' => '',
                    'content' => $content,
                ],
            ],
        ];
    }
}

$home = home_url('/');
$register_url = home_url('/register-for-your-portal/');

$figma = [
    'video' => 'https://www.figma.com/api/mcp/asset/ea3dcbe5-e8ed-4486-be48-1bc8644bc58a',
];

$video_poster_id = matrix_seed_resolve_image($figma['video'], 'your-portal-video-2888-3250', 'Your Portal video guide');

$hero_intro = 'Your Portal is a secure, online platform that you can access on your computer, smartphone or tablet to support your path to mental health recovery.';
$login_body = '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>';
$what_is_intro = '<p>Your Portal gives you and those you choose a central place to access key information about your care and treatment in St Patrick\'s Mental Health Services (SPMHS).</p>';
$what_is_list = '<ul>'
    . '<li>It will play a crucial role in exploring how best to deliver and improve mental health treatment and evidence-based practice.</li>'
    . '<li>Bullet point text - <strong>optimal 45-75 characters per line including spacing</strong></li>'
    . '<li>Quis nostrud exerci tation ullamcorper</li>'
    . '<li>Facilisis at vero eros et accumsanDuis autem vel eum iriure dolor in hendrerit</li>'
    . '<li>Iusto odio dignissim qui blandit</li>'
    . '<li>Quis nostrud exerci tation ullamcorper</li>'
    . '<li>Facilisis at vero eros et accumsanDuis autem vel eum iriure dolor in hendrerit</li>'
    . '<li>Iusto odio dignissim qui blandit</li>'
    . '<li>Quis nostrud exerci tation ullamcorper</li>'
    . '<li>Facilisis at vero eros et accumsan</li>'
    . '</ul>';
$register_body = '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris. Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>'
    . '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris. Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris. At vero eos et accusam et justo duo dolores et ea rebum.</p>';
$accordion_open_body = '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat m dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor inc <strong>consectetur adipisicing</strong>.</p>';

$video_slides = [];
foreach (range(1, 5) as $index) {
    $video_slides[] = [
        'poster_image' => matrix_seed_build_image_field($video_poster_id, 'Your Portal video slide ' . $index),
        'video_source_type' => 'embed_url',
        'video_embed_url' => 'https://www.youtube.com/watch?v=ysz5S6PUM-U',
        'caption' => '',
        'cta_link' => '',
    ];
}

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
        ],
        'current_crumb_label' => 'Your Portal',
        'heading_tag' => 'h1',
        'heading' => 'Your Portal',
        'content' => '<p>' . esc_html($hero_intro) . '</p>',
        'primary_button' => '',
        'hero_image' => '',
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
    ],
    [
        'acf_fc_layout' => 'content_cta',
        'heading_tag' => 'h2',
        'heading' => 'Login to your portal',
        'body' => $login_body,
        'button_link' => [
            'title' => 'Sign-in',
            'url' => $home . 'your-portal/',
            'target' => '_self',
        ],
        'background_type' => 'color',
        'background_color' => '#F1F3DE',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'What is Your Portal?',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'intro_text' => $what_is_intro,
        'content' => $what_is_list,
        'image' => '',
        'layout_style' => 'image_left',
        'background_type' => 'white',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'How can you register for Your Portal?',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'intro_text' => '',
        'content' => $register_body,
        'image' => '',
        'layout_style' => 'image_left',
        'background_type' => 'color',
        'background_color' => '#E9E2F7',
        'primary_button' => [
            'title' => 'Begin your registration',
            'url' => $register_url,
            'target' => '',
        ],
        'primary_button_variant' => 'outline',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'video_showcase',
        'heading_tag' => 'h2',
        'heading' => 'Video guide/ introduction to your portal',
        'intro' => '',
        'layout_style' => 'feature_slider',
        'slides' => $video_slides,
        'section_background' => 'linear-gradient(-76.43deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Are there different types of anxiety disorders? OR FAQs',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'intro_text' => '',
        'content' => '',
        'image' => '',
        'layout_style' => 'image_left',
        'background_type' => 'color',
        'background_color' => '#FBFAF7',
        'padding_settings' => [
            ['screen_size' => 'mob', 'padding_top' => '3', 'padding_bottom' => '1'],
            ['screen_size' => 'lg', 'padding_top' => '6.25', 'padding_bottom' => '1'],
        ],
    ],
    [
        'acf_fc_layout' => 'content_accordion',
        'layout_style' => 'default',
        'section_background' => '#FBFAF7',
        'panel_background' => '#FFFFFF',
        'open_panel_background' => 'linear-gradient(-42.77deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
        'items' => [
            matrix_seed_accordion_item('Lorem ipsum dolor sit amet lorem consectetur.', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>'),
            matrix_seed_accordion_item('Lorem ipsum dolor sit amet consectetur.', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>'),
            matrix_seed_accordion_item('Lorem ipsum sit amet consectetur.', $accordion_open_body, true),
            matrix_seed_accordion_item('Lorem ipsum dolor sit amet lorem consectetur.', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>'),
            matrix_seed_accordion_item('Sit amet lorem consectetur.', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>'),
            matrix_seed_accordion_item('Lorem ipsum dolor sit amet consectetur.', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>'),
        ],
        'padding_settings' => [
            ['screen_size' => 'mob', 'padding_top' => '1', 'padding_bottom' => '3'],
            ['screen_size' => 'lg', 'padding_top' => '1', 'padding_bottom' => '6.25'],
        ],
    ],
];

update_field('hero_content_blocks', [], $post_id);
update_field('flexible_content_blocks', $flexi_rows, $post_id);

$saved_rows = get_field('flexible_content_blocks', $post_id);
$saved_count = is_array($saved_rows) ? count($saved_rows) : 0;

if (class_exists('WP_CLI')) {
    if ($saved_count === count($flexi_rows)) {
        WP_CLI::success(sprintf(
            'Seeded Your Portal page (%d) with %d flexi blocks.',
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
