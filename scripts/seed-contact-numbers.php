<?php

/**
 * Seed Healthcare Professionals > Contact Numbers (page 279) to match Figma frame 2780:4290.
 *
 * Contact data sourced from https://www.stpatricks.ie/contact
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-contact-numbers.php
 */

require_once __DIR__ . '/lib/page-seed-conventions.php';
require_once __DIR__ . '/lib/healthcare-faqs-seed.php';

$post_id = matrix_seed_resolve_page_id_by_path('healthcare-professionals/contact-numbers');

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at healthcare-professionals/contact-numbers.');
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

if (! function_exists('matrix_seed_contact_item')) {
    /**
     * @param array<int, string> $bullet_items
     * @return array<string, mixed>
     */
    function matrix_seed_contact_item(
        string $title,
        string $phone = '',
        string $email = '',
        array $bullet_items = [],
        bool $starts_open = false
    ): array {
        $item = [
            'title' => $title,
            'starts_open' => $starts_open ? 1 : 0,
            'bullet_items' => [],
            'phone' => $phone,
            'email' => $email,
        ];

        foreach ($bullet_items as $label) {
            $item['bullet_items'][] = ['label' => $label];
        }

        return $item;
    }
}

$home = home_url('/');
$healthcare_url = home_url('/healthcare-professionals/');
$faqs_url = home_url('/healthcare-professionals/frequently-asked-questions/');

$figma_hero = 'https://www.figma.com/api/mcp/asset/9f265cc3-6a87-409a-910a-33366708aa0c';
// Reuse the Healthcare Professionals hero asset (same Figma image for Contact Numbers).
$hero_image_id = matrix_seed_resolve_image(
    $figma_hero,
    'healthcare-professionals-hero-2780-4288',
    'Contact numbers hero'
);

$faq_ids = matrix_seed_hp_faq_landing_ids();

$hero_content = sprintf(
    '<p>Below, you can find some of the key contacts and visiting information for our campuses and services here in St Patrick\'s Mental Health Services.</p>'
    . '<p>If you have queries about our services or mental health supports, you may find it helpful to <a href="%1$s">visit our Frequently Asked Questions page</a>.</p>'
    . '<p>If you wish to refer a patient to us, or if you have a query about a referral made for you, please contact our Referral and Assessment Service by calling <a href="tel:012493635">01 249 3635</a>.</p>',
    esc_url($faqs_url)
);

$contact_column_1 = [
    matrix_seed_contact_item('General Enquiries', '01 249 3200'),
    matrix_seed_contact_item(
        'Clinical Governance Office (Complaints and feedback)',
        '',
        'clinicalgovernance@stpatricks.ie'
    ),
    matrix_seed_contact_item("St Patrick's University Hospital (Dublin 8)", '01 249 3200'),
    matrix_seed_contact_item('Dean Clinic Cork', '01 249 3502'),
    matrix_seed_contact_item("Dean Clinic St Patrick's", '01 249 3590'),
];

$contact_column_2 = [
    matrix_seed_contact_item('Referral and Assessment Service', '01 249 3635'),
    matrix_seed_contact_item('Human Resources', '', 'hr@stpatricks.ie'),
    matrix_seed_contact_item("St Patrick's, Lucan", '01 621 8200'),
    matrix_seed_contact_item('Dean Clinic Galway', '091 513 540'),
    matrix_seed_contact_item('Adolescent Dean Clinic', '01 249 3590'),
];

$contact_column_3 = [
    matrix_seed_contact_item('Pharmacy', '01 249 3256', 'pharmacy@stpatricks.ie'),
    matrix_seed_contact_item('Media queries', '01 249 3540', 'communications@stpatricks.ie'),
    matrix_seed_contact_item('Willow Grove Adolescent Unit', '01 249 3687'),
    matrix_seed_contact_item('Dean Clinic Lucan', '01 249 3590'),
    matrix_seed_contact_item("Dean Clinic St Patrick's", '01 249 3590'),
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
            [
                'breadcrumb_link' => [
                    'title' => 'Healthcare Professionals',
                    'url' => $healthcare_url,
                    'target' => '',
                ],
            ],
        ],
        'current_crumb_label' => 'Contact Numbers',
        'heading_tag' => 'h1',
        'heading' => 'Contact Numbers',
        'content' => $hero_content,
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
        'heading_tag' => matrix_page_seed_heading(2),
        'show_heading' => 1,
        'layout_style' => 'default',
        'source_mode' => $faq_ids !== [] ? 'selected' : 'category',
        'selected_faqs' => $faq_ids,
        'selected_faq_categories' => $faq_ids === []
            ? [matrix_seed_hp_faq_ensure_term('healthcare-professionals', 'Healthcare Professionals')]
            : [],
        'section_background' => '#FBFAF7',
        'heading_color' => '#1E244B',
        'underline_color' => '#6FC9C0',
        'item_background' => '#FFFFFF',
        'open_item_background' => 'linear-gradient(-42.77deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
        'question_color' => '#1E244B',
        'answer_color' => '#08284B',
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
            'Seeded Contact Numbers page (%d) with %d flexi blocks and %d FAQs.',
            $post_id,
            $saved_count,
            count($faq_ids)
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
