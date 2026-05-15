<?php

/**
 * Seed Medication page (Figma 3279:19636).
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-medication.php
 */

$post_id = (int) (get_page_by_path('service-users-and-visitors/medication')?->ID ?? 0);

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at service-users-and-visitors/medication.');
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
                    'row_type' => 'text',
                    'icon_key' => '',
                    'icon' => '',
                    'content' => $content,
                ],
            ],
        ];
    }
}

$home = home_url('/');
$service_users_url = home_url('/service-users-and-visitors/');
$sample_pdf_url = home_url('/wp-content/uploads/sample.pdf');

$figma = [
    'video_poster' => 'https://www.figma.com/api/mcp/asset/d62b785b-08eb-4596-a2ce-320033009b27',
    'image_left' => 'https://www.figma.com/api/mcp/asset/be27514d-8a2e-426a-9991-9a803365b88b',
    'image_right' => 'https://www.figma.com/api/mcp/asset/d37a2333-262f-4977-a9e3-c1653b65e163',
];

$video_poster_id = matrix_seed_resolve_image($figma['video_poster'], 'medication-video-3279-19636', 'Pregnancy and Valproate video poster');
$image_left_id = matrix_seed_resolve_image($figma['image_left'], 'medication-image-left-3279-19636', 'Medication placeholder image');
$image_right_id = matrix_seed_resolve_image($figma['image_right'], 'medication-image-right-3279-19636', 'Medication placeholder image two');

$hero_copy = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad mini. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam voluptatem.';

$cravings_body = '<p>It can be difficult to control your weight, especially if you are on medication which can increase your appetite. Some medication can cause you to be thirsty or have a false appetite. These feelings are called cravings.</p>'
    . '<p>If you eat too many sugary or fatty foods, or drink too many sugary drinks to satisfy these cravings, you will gain weight. Instead of filling up on these foods and drinks, choose from the list below. Over time the cravings will disappear.</p>';

$long_text_intro = '<p><strong>Cognitive Behavioural Therapy (CBT) is highly effective in treating anxiety disorders. By learning about the vicious cycle of anxiety and challenging unhelpful beliefs and behaviours, you can gradually master your fears, grow your confidence and regain your functioning through CBT.</strong></p>';
$long_text_body = '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris. At vero eos et accusam et justo duo dolores et ea rebum.</p>'
    . '<h3>Paragraph title.</h3>'
    . '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris. Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris. At vero eos et accusam et justo duo dolores et ea rebum</p>';

$placeholder_intro = '<p><strong>Our vision is to see a society where all citizens are empowered and given the opportunity to live mentally healthy lives</strong></p>';
$placeholder_body = '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat.</p>';

$pdf_document_link = [
    'title' => 'PDF open in a new tab',
    'url' => $sample_pdf_url,
    'target' => '_blank',
];

$accordion_open_body = '<p>Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent.</p>';

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
            ['breadcrumb_link' => ['title' => 'Service Users and Visitors', 'url' => $service_users_url, 'target' => '']],
        ],
        'current_crumb_label' => 'Medication',
        'heading_tag' => 'h1',
        'heading' => 'Medication',
        'content' => '<p>' . esc_html($hero_copy) . '</p>',
        'primary_button' => '',
        'hero_image' => '',
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Medication & Cravings',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'intro_text' => '',
        'content' => $cravings_body,
        'document_link' => $pdf_document_link,
        'image' => '',
        'layout_style' => 'image_left',
        'background_type' => 'color',
        'background_color' => '#FFFFFF',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'video_showcase',
        'heading_tag' => 'h2',
        'heading' => 'Pregnancy and Valproate: Prevent programme',
        'intro' => '',
        'layout_style' => 'feature_single',
        'slides' => [
            [
                'poster_image' => matrix_seed_build_image_field($video_poster_id, 'Pregnancy and Valproate: Prevent programme'),
                'video_source_type' => 'embed_url',
                'video_embed_url' => 'https://www.youtube.com/watch?v=ysz5S6PUM-U',
                'caption' => '',
                'cta_link' => '',
            ],
        ],
        'section_background' => 'linear-gradient(-76.52deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Placeholder - longer text',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'intro_text' => $long_text_intro,
        'content' => $long_text_body,
        'image' => '',
        'layout_style' => 'image_left',
        'background_type' => 'color',
        'background_color' => '#FFFFFF',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Placeholder',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'intro_text' => $placeholder_intro,
        'content' => $placeholder_body,
        'document_link' => $pdf_document_link,
        'image' => $image_left_id,
        'layout_style' => 'image_left',
        'background_type' => 'color',
        'background_color' => '#FBF8F3',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Placeholder',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'intro_text' => $placeholder_intro,
        'content' => $placeholder_body,
        'document_link' => $pdf_document_link,
        'image' => $image_right_id,
        'layout_style' => 'image_right',
        'background_type' => 'color',
        'background_color' => '#FFFFFF',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Frequently Asked Questions',
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
            'Seeded Medication page (%d) with %d flexi blocks.',
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
