<?php

/**
 * Seed About Mental Health page (Figma 2780:4050).
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-about-mental-health.php
 */

$post_id = (int) (get_page_by_path('service-users-and-visitors/about-mental-health')?->ID ?? 0);

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at service-users-and-visitors/about-mental-health.');
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
$programmes_url = home_url('/programmes-therapies/');

$figma = [
    'hero' => 'https://www.figma.com/api/mcp/asset/930ef003-aa87-435d-ab1d-2cea7d7c0b7c',
    'anxiety_image' => 'https://www.figma.com/api/mcp/asset/8b68e6e2-0965-4ab5-9d74-d57cd3e2b713',
];

$hero_image_id = matrix_seed_resolve_image($figma['hero'], 'about-mental-health-hero-2780-4050', 'About Mental Health hero');
$anxiety_image_id = matrix_seed_resolve_image($figma['anxiety_image'], 'about-mental-health-anxiety-2780-4050', 'What is anxiety illustration');

$hero_intro = 'About Mental Health is a landing page that links users to all other subpages within the mental health section. Page context goes here. Max 4 lines of text. You have the ability to add additional pages to this section via sidenav.';

$what_is_anxiety_intro = '<p><strong>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut wisi enim ad minim veniam, quis nostrud exercitation ullamco laboris. At vero eos et accusam et justo duo dolores et ea rebum.</strong></p>';
$what_is_anxiety_body = '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris. Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris. At vero eos et accusam et justo duo dolores et ea rebum</p>';

$causes_intro = '<p><strong>Cognitive Behavioural Therapy (CBT) is highly effective in treating anxiety disorders. By learning about the vicious cycle of anxiety and challenging unhelpful beliefs and behaviours, you can gradually master your fears, grow your confidence and regain your functioning through CBT.</strong></p>';
$causes_body = '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris. Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris. At vero eos et accusam et justo duo dolores et ea rebum</p>'
    . '<h3>Paragraph title.</h3>'
    . '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris. Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris. At vero eos et accusam et justo duo dolores et ea rebum</p>';

$treatment_intro = '<p><strong>Our Anxiety Disorders Programme provides care in an outpatient, day patient or inpatient setting, according to your needs. Please note that PTSD is not part of the Anxiety Disorders Programme; however, our Anxiety Disorders Service will assess PTSD and treat it individually.</strong></p>';
$treatment_list = '<ul>'
    . '<li>It will play a crucial role in exploring how best to deliver and improve mental health treatment and evidence-based practice.</li>'
    . '<li>Bullet point text - <strong>optimal 45-75 characters per line including spacing</strong></li>'
    . '<li>Quis nostrud exerci tation ullamcorper</li>'
    . '<li>Facilisis at vero eros et accumsanDuis autem vel eum iriure dolor in hendrerit</li>'
    . '<li>Iusto odio dignissim qui blandit</li>'
    . '<li>Quis nostrud exerci tation ullamcorper</li>'
    . '<li>Facilisis at vero eros et accumsan</li>'
    . '</ul>'
    . '<h3>Paragraph title.</h3>'
    . '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris. Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris. At vero eos et accusam et justo duo dolores et ea rebum</p>';

$programme_body = '<p>The Anxiety Disorders Programme, based on Cognitive Behavioural Therapy (CBT) and compassion mindfulness-based approaches, focuses on addressing the physical, psychological and behavioural aspects of anxiety disorders using group psychotherapy.</p>'
    . '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris. Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris. At vero eos et accusam et justo duo dolores et ea rebum.</p>';

$accordion_open_body = '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. <strong>consectetur adipisicing</strong>.</p>';

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
            ['breadcrumb_link' => ['title' => 'Service users and visitors', 'url' => $service_users_url, 'target' => '']],
        ],
        'current_crumb_label' => 'About Mental Health',
        'heading_tag' => 'h1',
        'heading' => 'About Mental Health',
        'content' => '<p>' . esc_html($hero_intro) . '</p>',
        'primary_button' => [
            'title' => 'Treatment programme',
            'url' => $programmes_url,
            'target' => '',
        ],
        'hero_image' => $hero_image_id,
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'What is anxiety?',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'intro_text' => $what_is_anxiety_intro,
        'content' => $what_is_anxiety_body,
        'primary_button' => [
            'title' => 'Optional CTA can be removed',
            'url' => $programmes_url,
            'target' => '',
        ],
        'primary_button_variant' => 'outline',
        'image' => $anxiety_image_id,
        'layout_style' => 'image_left',
        'background_type' => 'color',
        'background_color' => '#FFFFFF',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'What causes anxiety disorders?',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'intro_text' => $causes_intro,
        'content' => $causes_body,
        'image' => '',
        'layout_style' => 'image_left',
        'background_type' => 'color',
        'background_color' => '#FBFAF7',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Is there treatment for anxiety?',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'intro_text' => $treatment_intro,
        'content' => $treatment_list,
        'image' => '',
        'layout_style' => 'image_left',
        'background_type' => 'color',
        'background_color' => '#FFFFFF',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Anxiety Disorders Programme / Treatment',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'intro_text' => '',
        'content' => $programme_body,
        'primary_button' => [
            'title' => 'Anxiety Disorders Programme',
            'url' => $programmes_url,
            'target' => '',
        ],
        'primary_button_variant' => 'outline',
        'image' => '',
        'layout_style' => 'image_left',
        'background_type' => 'color',
        'background_color' => '#E9E2F7',
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
            'Seeded About Mental Health page (%d) with %d flexi blocks.',
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
