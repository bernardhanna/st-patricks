<?php

/**
 * Seed What We Offer landing page (page 214) to match Figma frame 2888:3476.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-what-we-offer.php
 */

$post_id = (int) (get_page_by_path('what-we-offer')?->ID ?? 0);

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at what-we-offer.');
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

$figma = [
    'hero' => 'https://www.figma.com/api/mcp/asset/6dc4af97-4af3-4bfc-901d-5e1b63919a7e',
    'partner_mhc' => 'https://www.figma.com/api/mcp/asset/e861c77e-50fa-4317-82a3-1fcf1a1468d0',
    'partner_tcd' => 'https://www.figma.com/api/mcp/asset/4c3808a1-6a20-42ba-8bc0-a1c2e25f80df',
    'partner_ucc' => 'https://www.figma.com/api/mcp/asset/b192edca-b3d0-4371-b04e-416b75c4b2fb',
    'partner_cpsychi' => 'https://www.figma.com/api/mcp/asset/613358ed-0b76-4060-9108-570b59f4e83d',
    'partner_inhed' => 'https://www.figma.com/api/mcp/asset/bad3c400-195e-42ad-bec8-b5129b9331b0',
];

$hero_image_id = matrix_seed_resolve_image($figma['hero'], 'what-we-offer-hero-2888-3476', 'What We Offer hero');

$partner_logo_ids = [
    matrix_seed_resolve_image($figma['partner_mhc'], 'what-we-offer-partner-mhc-2888-3476', 'Mental Health Commission logo'),
    matrix_seed_resolve_image($figma['partner_tcd'], 'what-we-offer-partner-tcd-2888-3476', 'Trinity College Dublin logo'),
    matrix_seed_resolve_image($figma['partner_ucc'], 'what-we-offer-partner-ucc-2888-3476', 'University College Cork logo'),
    matrix_seed_resolve_image($figma['partner_cpsychi'], 'what-we-offer-partner-cpsychi-2888-3476', 'College of Psychiatrists of Ireland logo'),
    matrix_seed_resolve_image($figma['partner_inhed'], 'what-we-offer-partner-inhed-2888-3476', 'INHED logo'),
];

$partner_rows = [];

foreach (array_filter($partner_logo_ids) as $logo_id) {
    $partner_rows[] = [
        'logo' => (int) $logo_id,
        'link' => '',
    ];
}

$hero_intro = 'What we offer - is a landing page (per sitemap) that links users to add other subpages within this section. Page context goes here. Max 4 lines of text. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
$lorem_service = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad mini.';
$lorem_card = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>';
$faq_answer = '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat m dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>';

$hero_content = sprintf(
    '<p>%s</p><p><a class="btn inline-flex min-h-[36px] items-center justify-center rounded-[6px] bg-[#024B79] px-3 py-2 text-[14px] font-medium leading-[24px] text-white no-underline" href="%s">Optional CTA</a></p>',
    esc_html($hero_intro),
    esc_url(home_url('/contact-us/'))
);

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
        'what-we-offer-faq-' . ($index + 1)
    );
}

$faq_ids = array_values(array_filter(array_map('intval', $faq_ids)));

$what_we_offer_services = [
    [
        'service_title' => 'Inpatient Care',
        'service_description' => $lorem_card,
        'service_link' => [
            'title' => 'Inpatient Care',
            'url' => home_url('/inpatient-care/'),
            'target' => '',
        ],
        'accent_color' => '#6FC9C0',
        'show_service_icon' => 1,
    ],
    [
        'service_title' => "St Patrick's at Home",
        'service_description' => $lorem_card,
        'service_link' => [
            'title' => "St Patrick's at Home",
            'url' => home_url('/what-we-offer/st-patricks-at-home/'),
            'target' => '',
        ],
        'accent_color' => '#C3DBAE',
        'show_service_icon' => 1,
    ],
    [
        'service_title' => 'Outpatient Care - Dean Clinics',
        'service_description' => $lorem_card,
        'service_link' => [
            'title' => 'Outpatient Care - Dean Clinics',
            'url' => home_url('/what-we-offer/outpatient-care-dean-clinics/'),
            'target' => '',
        ],
        'accent_color' => '#B4A8CE',
        'show_service_icon' => 1,
    ],
    [
        'service_title' => 'Day Programmes',
        'service_description' => $lorem_card,
        'service_link' => [
            'title' => 'Day Programmes',
            'url' => home_url('/what-we-offer/day-programmes/'),
            'target' => '',
        ],
        'accent_color' => '#E4B8D6',
        'show_service_icon' => 1,
    ],
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
        'current_crumb_label' => 'What we offer',
        'heading_tag' => 'h1',
        'heading' => 'What we offer',
        'content' => $hero_content,
        'hero_image' => $hero_image_id,
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
    ],
    [
        'acf_fc_layout' => 'what_we_offer',
        'heading' => 'Lorem ipsum- We offer services below:',
        'heading_tag' => 'h2',
        'layout_style' => 'intro_two_column',
        'intro_text' => $lorem_service,
        'services' => $what_we_offer_services,
        'background_gradient' => '#FFFFFF',
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
        'acf_fc_layout' => 'partners',
        'heading_tag' => 'h2',
        'heading_text' => 'Committed to quality care, human rights, and innovation',
        'partners' => $partner_rows,
        'background_color' => '#FFFFFF',
        'heading_color' => '#1E244B',
        'show_card_style' => false,
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
            'Seeded What We Offer page (%d) with %d flexi blocks.',
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
