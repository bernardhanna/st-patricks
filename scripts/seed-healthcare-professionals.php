<?php

/**
 * Seed Healthcare Professionals landing page (page 232).
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-healthcare-professionals.php
 */

require_once __DIR__ . '/lib/page-seed-conventions.php';
require_once __DIR__ . '/lib/healthcare-faqs-seed.php';

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
        $filename = $path ? basename((string) $path) : 'figma-asset.jpg';

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

if (! function_exists('matrix_seed_hp_contact_item')) {
    /**
     * @param array<int, string> $bullet_items
     * @return array<string, mixed>
     */
    function matrix_seed_hp_contact_item(
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

if (! function_exists('matrix_seed_hp_url')) {
    function matrix_seed_hp_url(string $path): string
    {
        return esc_url(home_url('/' . trim($path, '/') . '/'));
    }
}

$home = home_url('/');
$healthcare_url = home_url('/healthcare-professionals/');
$webinars_url = home_url('/healthcare-professionals/webinars-events/');
$faqs_url = home_url('/healthcare-professionals/frequently-asked-questions/');
$referrals_url = home_url('/make-a-referral/');
$insurance_url = home_url('/getting-help/insurance-information/');
$programmes_url = home_url('/programmes-therapies/');

$figma = [
    'hero' => 'https://www.figma.com/api/mcp/asset/9f265cc3-6a87-409a-910a-33366708aa0c',
    'webinar' => 'https://www.figma.com/api/mcp/asset/1f79ece7-5c18-4c09-ad62-c902ceb592f3',
];

$hero_image_id = matrix_seed_resolve_image($figma['hero'], 'healthcare-professionals-hero-2780-4288', 'Healthcare Professionals hero');
$webinar_image_id = matrix_seed_resolve_image($figma['webinar'], 'healthcare-professionals-webinar-2780-4338', 'Healthcare Professionals webinar');

$parent_term_id = matrix_seed_hp_faq_ensure_term('healthcare-professionals', 'Healthcare Professionals');
$referrals_term_id = matrix_seed_hp_faq_ensure_term('hp-referrals', 'Referrals and admissions', $parent_term_id);
$services_term_id = matrix_seed_hp_faq_ensure_term('hp-services', 'Services and assessments', $parent_term_id);
$insurance_term_id = matrix_seed_hp_faq_ensure_term('hp-insurance', 'Insurance and funding', $parent_term_id);
$clinical_term_id = matrix_seed_hp_faq_ensure_term('hp-clinical', 'Clinical information and professional development', $parent_term_id);

$term_map = [
    'referrals' => $referrals_term_id,
    'services' => $services_term_id,
    'insurance' => $insurance_term_id,
    'clinical' => $clinical_term_id,
];

foreach (matrix_seed_hp_faq_sections() as $section_key => $section) {
    $term_id = (int) ($term_map[$section_key] ?? 0);

    foreach ($section['items'] as $index => $item) {
        matrix_seed_hp_faq_ensure_post(
            $item['title'],
            $item['content'],
            'hp-faq-' . $section_key . '-' . ($index + 1),
            [$term_id, $parent_term_id],
            $index + 1
        );
    }
}

$faq_ids = matrix_seed_hp_faq_landing_ids();

$hero_content = sprintf(
    '<p>At St Patrick\'s Mental Health Services (SPMHS), we support GPs and referrers with referral pathways, key contacts, and practical information about our mental health services.</p>'
    . '<p>Our Referral and Assessment Service team can help with queries about our services and referrals. Please call <a href="tel:012493635">01 249 3635</a> during office hours, or visit our <a href="%1$s">Frequently Asked Questions page</a> for more guidance.</p>',
    esc_url($faqs_url)
);

$contact_column_1 = [
    matrix_seed_hp_contact_item('General Enquiries', '01 249 3200'),
    matrix_seed_hp_contact_item(
        'Clinical Governance Office (Complaints and feedback)',
        '',
        'clinicalgovernance@stpatricks.ie'
    ),
    matrix_seed_hp_contact_item("St Patrick's University Hospital (Dublin 8)", '01 249 3200'),
    matrix_seed_hp_contact_item('Dean Clinic Cork', '01 249 3502'),
    matrix_seed_hp_contact_item("Dean Clinic St Patrick's", '01 249 3590'),
];

$contact_column_2 = [
    matrix_seed_hp_contact_item('Referral and Assessment Service', '01 249 3635'),
    matrix_seed_hp_contact_item('Human Resources', '', 'hr@stpatricks.ie'),
    matrix_seed_hp_contact_item("St Patrick's, Lucan", '01 621 8200'),
    matrix_seed_hp_contact_item('Dean Clinic Galway', '091 513 540'),
    matrix_seed_hp_contact_item('Adolescent Dean Clinic', '01 249 3590'),
];

$contact_column_3 = [
    matrix_seed_hp_contact_item('Pharmacy', '01 249 3256', 'pharmacy@stpatricks.ie'),
    matrix_seed_hp_contact_item('Media queries', '01 249 3540', 'communications@stpatricks.ie'),
    matrix_seed_hp_contact_item('Willow Grove Adolescent Unit', '01 249 3687'),
    matrix_seed_hp_contact_item('Dean Clinic Lucan', '01 249 3590'),
    matrix_seed_hp_contact_item("Dean Clinic St Patrick's", '01 249 3590'),
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
            [
                'breadcrumb_link' => [
                    'title' => 'Home',
                    'url' => $home,
                    'target' => '',
                ],
            ],
        ],
        'current_crumb_label' => 'Healthcare Professionals',
        'heading_tag' => matrix_page_seed_heading(1),
        'heading' => 'Key Contact Information',
        'content' => $hero_content,
        'primary_button' => [
            'title' => 'Make a referral',
            'url' => $referrals_url,
            'target' => '',
        ],
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
        'heading' => 'Webinars and events for healthcare professionals',
        'heading_tag' => matrix_page_seed_heading(2),
        'accent_position' => 'below_heading',
        'content' => '<p>Stay up to date with mental health developments, service updates, and continuous professional development opportunities through our webinars and events for GPs and referrers.</p>'
            . '<p>Sign up to our GP eNewsletter for information about mental health, service updates, events and training. Please note this eNewsletter cannot be delivered to @healthmail email addresses; please provide an alternative email address.</p>',
        'primary_button' => [
            'title' => 'See webinars and events',
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
            'Seeded Healthcare Professionals page (%d) with %d flexi blocks and %d FAQs.',
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

    WP_CLI::log('Page: ' . get_permalink($post_id));
}
