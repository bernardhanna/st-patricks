<?php

/**
 * Seed Healthcare Professionals (page 232) to match Figma frame 2780:4288.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-healthcare-professionals.php
 */

$post_id = (int) (get_page_by_path('healthcare-professionals')?->ID ?? 0);

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at healthcare-professionals.');
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

if (! function_exists('matrix_seed_ensure_faq_post')) {
    function matrix_seed_ensure_faq_post(string $title, string $content, string $seed_key): int
    {
        $existing = get_posts([
            'post_type' => 'faqs',
            'post_status' => 'any',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => '_matrix_seed_key',
                    'value' => $seed_key,
                ],
            ],
        ]);

        if ($existing !== []) {
            $faq_id = (int) $existing[0]->ID;
            wp_update_post([
                'ID' => $faq_id,
                'post_title' => $title,
                'post_content' => $content,
                'post_status' => 'publish',
            ]);
        } else {
            $faq_id = wp_insert_post([
                'post_type' => 'faqs',
                'post_status' => 'publish',
                'post_title' => $title,
                'post_content' => $content,
            ]);

            if (is_wp_error($faq_id) || ! $faq_id) {
                return 0;
            }

            update_post_meta((int) $faq_id, '_matrix_seed_key', $seed_key);
        }

        return (int) $faq_id;
    }
}

$home = home_url('/');
$healthcare_url = home_url('/healthcare-professionals/');
$webinars_url = home_url('/healthcare-professionals/webinars-events/');

$figma = [
    'hero' => 'https://www.figma.com/api/mcp/asset/9f265cc3-6a87-409a-910a-33366708aa0c',
    'webinar' => 'https://www.figma.com/api/mcp/asset/1f79ece7-5c18-4c09-ad62-c902ceb592f3',
];

$hero_image_id = matrix_seed_resolve_image($figma['hero'], 'healthcare-professionals-hero-2780-4288', 'Healthcare Professionals hero');
$webinar_image_id = matrix_seed_resolve_image($figma['webinar'], 'healthcare-professionals-webinar-2780-4338', 'Healthcare Professionals webinar');

$lorem_hero = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad mini.';
$lorem_body = '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat.</p>';
$faq_answer = '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat m dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>';

$faq_ids = [];
$faq_titles = [
    'Lorem ipsum dolor sit amet lorem consectetur.',
    'Lorem ipsum dolor sit amet consectetur.',
    'Lorem ipsum sit amet consectetur.',
    'Lorem ipsum dolor sit amet lorem consectetur.',
    'Sit amet lorem consectetur.',
    'Lorem ipsum dolor sit amet consectetur.',
];

foreach ($faq_titles as $index => $faq_title) {
    $faq_ids[] = matrix_seed_ensure_faq_post(
        $faq_title,
        $faq_answer,
        'healthcare-professionals-faq-' . ($index + 1)
    );
}

$faq_ids = array_values(array_filter(array_map('intval', $faq_ids)));

$contact_column_1 = [
    [
        'title' => 'General Enquires',
        'starts_open' => 1,
        'bullet_items' => [
            ['label' => 'Inpatient care'],
            ['label' => 'Admissions'],
            ['label' => 'Pharmacy'],
        ],
        'phone' => '01 012 123 123',
        'email' => 'hello@StPatrick.ie',
    ],
    ['title' => 'Clinical Governance Office (Complaints and feedback)', 'starts_open' => 0, 'bullet_items' => [], 'phone' => '', 'email' => ''],
    ['title' => 'St Patrick\'s University Hospital (Dublin 8)', 'starts_open' => 0, 'bullet_items' => [], 'phone' => '', 'email' => ''],
    ['title' => 'Dean Clinic Cork', 'starts_open' => 0, 'bullet_items' => [], 'phone' => '', 'email' => ''],
    ['title' => 'Dean Clinic St Patrick\'s', 'starts_open' => 0, 'bullet_items' => [], 'phone' => '', 'email' => ''],
];

$contact_column_2 = [
    ['title' => 'Referral and Assessment Service', 'starts_open' => 0, 'bullet_items' => [], 'phone' => '', 'email' => ''],
    ['title' => 'Human Resources', 'starts_open' => 0, 'bullet_items' => [], 'phone' => '', 'email' => ''],
    ['title' => 'St Patrick\'s, Lucan', 'starts_open' => 0, 'bullet_items' => [], 'phone' => '', 'email' => ''],
    ['title' => 'Dean Clinic Galway', 'starts_open' => 0, 'bullet_items' => [], 'phone' => '', 'email' => ''],
    ['title' => 'Adolescent Dean Clinic', 'starts_open' => 0, 'bullet_items' => [], 'phone' => '', 'email' => ''],
];

$contact_column_3 = [
    ['title' => 'Pharmacy', 'starts_open' => 0, 'bullet_items' => [], 'phone' => '', 'email' => ''],
    ['title' => 'Placeholder', 'starts_open' => 0, 'bullet_items' => [], 'phone' => '', 'email' => ''],
    ['title' => 'Willow Grove Adolescent Unit', 'starts_open' => 0, 'bullet_items' => [], 'phone' => '', 'email' => ''],
    ['title' => 'Dean Clinic Lucan', 'starts_open' => 0, 'bullet_items' => [], 'phone' => '', 'email' => ''],
    ['title' => 'Dean Clinic St Patrick\'s', 'starts_open' => 0, 'bullet_items' => [], 'phone' => '', 'email' => ''],
];

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
        ],
        'current_crumb_label' => 'Healthcare Professionals',
        'heading_tag' => 'h1',
        'heading' => 'Key Contact Information',
        'content' => '<p>' . esc_html($lorem_hero) . '</p>',
        'hero_image' => $hero_image_id,
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
    ],
    [
        'acf_fc_layout' => 'key_contact_info',
        'columns' => [
            ['items' => $contact_column_1],
            ['items' => $contact_column_2],
            ['items' => $contact_column_3],
        ],
        'section_background' => '#FFFFFF',
        'closed_panel_background' => '#FBFAF7',
        'open_panel_background' => 'linear-gradient(-79.46deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'faqs',
        'heading' => 'Frequently Asked Questions',
        'heading_tag' => 'h2',
        'source_mode' => 'selected',
        'selected_faqs' => $faq_ids,
        'section_background' => '#FBFAF7',
        'heading_color' => '#1E244B',
        'underline_color' => '#6FC9C0',
        'item_background' => '#FFFFFF',
        'open_item_background' => 'linear-gradient(-42.77deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
        'question_color' => '#1E244B',
        'answer_color' => '#08284B',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Highlight an upcoming Webinar in this box',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'content' => $lorem_body,
        'primary_button' => [
            'title' => 'Sign-up to Webinar',
            'url' => $webinars_url,
            'target' => '',
        ],
        'primary_button_variant' => 'filled',
        'layout_style' => 'image_right',
        'background_type' => 'gradient',
        'background_gradient' => 'linear-gradient(-69.76deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
        'image' => $webinar_image_id,
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
            'Seeded Healthcare Professionals page (%d) with %d flexi blocks.',
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
