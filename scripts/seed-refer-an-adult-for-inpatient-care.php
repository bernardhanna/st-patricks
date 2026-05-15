<?php

/**
 * Seed Refer an Adult for Inpatient Care (page 201) to match Figma frame 2888:5019.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-refer-an-adult-for-inpatient-care.php
 */

$post_id = (int) (get_page_by_path('make-a-referral-cta/refer-an-adult-for-inpatient-care')?->ID ?? 0);

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at make-a-referral-cta/refer-an-adult-for-inpatient-care.');
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

if (! function_exists('matrix_seed_get_faq_category_id')) {
    function matrix_seed_get_faq_category_id(string $slug): int
    {
        $term = get_term_by('slug', $slug, 'faq_category');

        return $term instanceof WP_Term ? (int) $term->term_id : 0;
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
$healthcare_url = home_url('/healthcare-professionals/');
$locations_url = home_url('/about-us/our-locations/');

$figma = [
    'video' => 'https://www.figma.com/api/mcp/asset/bcc1f8bb-8c55-43f0-85bd-52db81e29cda',
    'testimonials_bg' => 'https://www.figma.com/api/mcp/asset/e6aa38fb-6d74-4cb6-8d05-0e62e412b66e',
    'location_1' => 'https://www.figma.com/api/mcp/asset/328749de-8648-4edb-a82f-c5a63736894b',
    'location_2' => 'https://www.figma.com/api/mcp/asset/b079693d-baf1-48a6-9aec-ecf1bebe3752',
    'location_3' => 'https://www.figma.com/api/mcp/asset/02247baf-10fa-439c-beab-2a3a66d70a3f',
    'location_4' => 'https://www.figma.com/api/mcp/asset/02247baf-10fa-439c-beab-2a3a66d70a3f',
];

$video_poster_id = matrix_seed_resolve_image($figma['video'], 'refer-adult-inpatient-video-2888-5019', 'Refer adult inpatient care video');
$testimonials_bg_id = matrix_seed_resolve_image($figma['testimonials_bg'], 'refer-adult-inpatient-testimonials-2888-5019', 'Refer adult inpatient care testimonials background');
$location_image_ids = [
    matrix_seed_resolve_image($figma['location_1'], 'refer-adult-inpatient-location-willow-grove-2888-5019', 'Willow Grove Adolescent Unit'),
    matrix_seed_resolve_image($figma['location_2'], 'refer-adult-inpatient-location-spuh-2888-5019', 'St Patricks University Hospital'),
    matrix_seed_resolve_image($figma['location_3'], 'refer-adult-inpatient-location-lucan-2888-5019', 'St Patricks Hospital Lucan'),
    matrix_seed_resolve_image($figma['location_4'], 'refer-adult-inpatient-location-dean-2888-5019', 'Dean Clinic'),
];

$hero_copy = 'What we offer - is a landing page (per sitemap) that links users to add other subpages within this section. Page context goes here. Max 4 lines of text. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad mini.';
$video_intro = '<p>Videos and images section as requested. Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.</p>';
$testimonial_quote = '<p>Through our Advocacy Committee, we respond to all relevant calls for submissions by the Dáil, Seanad and Government departments.</p>';
$testimonial_quote_long = '<p>Long testimonials example: Through our Advocacy Committee, we respond to all relevant calls for submissions by the Dáil, Seanad and Government departments. Through our Advocacy Committee, we respond to all relevant calls for submissions by the Dáil, Seanad and Government departments. Through our Advocacy Committee, we respond to all relevant calls for submissions by the Dáil, Seanad and Government departments. Through our Advocacy Committee, we respond to all relevant calls for submissions by the Dáil, Seanad and Government departments.</p>';

$video_slides = [];
foreach (range(1, 5) as $index) {
    $video_slides[] = [
        'poster_image' => matrix_seed_build_image_field($video_poster_id, 'Programme video slide ' . $index),
        'video_source_type' => 'embed_url',
        'video_embed_url' => 'https://www.youtube.com/watch?v=ysz5S6PUM-U',
        'caption' => '',
        'cta_link' => '',
    ];
}

$location_cards = [
    [
        'title' => 'Willow Grove Adolescent Unit',
        'image' => matrix_seed_build_image_field($location_image_ids[0], 'Willow Grove Adolescent Unit'),
        'link' => [
            'title' => 'Willow Grove Adolescent Unit',
            'url' => $locations_url,
            'target' => '',
        ],
    ],
    [
        'title' => "St Patrick's University Hospital (SPUH)",
        'image' => matrix_seed_build_image_field($location_image_ids[1], "St Patrick's University Hospital (SPUH)"),
        'link' => [
            'title' => "St Patrick's University Hospital (SPUH)",
            'url' => $locations_url,
            'target' => '',
        ],
    ],
    [
        'title' => "St Patrick's Hospital Lucan",
        'image' => matrix_seed_build_image_field($location_image_ids[2], "St Patrick's Hospital Lucan"),
        'link' => [
            'title' => "St Patrick's Hospital Lucan",
            'url' => $locations_url,
            'target' => '',
        ],
    ],
    [
        'title' => 'Dean Clinic Galway (optional)',
        'image' => matrix_seed_build_image_field($location_image_ids[3], 'Dean Clinic Galway'),
        'link' => [
            'title' => 'Dean Clinic Galway',
            'url' => $locations_url,
            'target' => '',
        ],
    ],
    [
        'title' => 'Dean Clinic Lucan (optional)',
        'image' => matrix_seed_build_image_field($location_image_ids[3], 'Dean Clinic Lucan'),
        'link' => [
            'title' => 'Dean Clinic Lucan',
            'url' => $locations_url,
            'target' => '',
        ],
    ],
    [
        'title' => 'Name of the hospital - no link, clinics are optional',
        'image' => matrix_seed_build_image_field($location_image_ids[3], 'Hospital location'),
        'link' => '',
    ],
];

$healthcare_faq_category_id = matrix_seed_get_faq_category_id('healthcare-professionals');

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
        'current_crumb_label' => 'Refer an Adult for Inpatient Care',
        'heading_tag' => 'h1',
        'heading' => 'Refer an Adult to Inpatient Care',
        'content' => '<p>' . esc_html($hero_copy) . '</p>',
        'primary_button' => '',
        'hero_image' => '',
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
    ],
    [
        'acf_fc_layout' => 'referral_action_cards',
        'left_title' => 'Make a Referral via Healthlink',
        'left_description' => '<p>The fastest and most efficient method is to send referrals via Healthlink or through your practice management system.</p>',
        'left_button' => [
            'title' => 'Go to Healthlink',
            'url' => 'https://www.healthlink.ie/',
            'target' => '_blank',
        ],
        'left_action_icon' => 'external',
        'right_title' => 'Download our Adult Referral Form',
        'right_description' => '<p>Complete our Adult Referral form and submit via Healthmail - referrals@stpatricks.ie</p>',
        'right_button' => [
            'title' => 'Download Adult Referral Form',
            'url' => '#',
            'target' => '',
        ],
        'right_action_icon' => 'download',
        'left_background_color' => '#CEF2EE',
        'right_background_color' => '#E4F4D6',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'inpatient_bed_vacancies',
        'heading' => 'Current Inpatient Bed Vacancies',
        'heading_tag' => 'h2',
        'updated_text' => 'Updated (30/02/2026)',
        'vacancy_items' => [
            [
                'bed_count' => 0,
                'location_title' => 'Adolescent Inpatient Bed Vacancies',
                'location_subtitle' => 'Willow Grove',
                'disclaimer' => 'additional context would be required to clarify that available beds may not be immediately accessible.',
                'status_background_color' => '#C3DBAE',
            ],
        ],
        'section_background_color' => '#FBF8F3',
        'card_background_color' => '#FFFFFF',
        'heading_color' => '#1E244B',
        'updated_color' => '#08284B',
        'location_color' => '#1E244B',
        'disclaimer_color' => '#08284B',
        'count_color' => '#08284B',
        'beds_label_color' => '#08284B',
        'underline_color' => '#6FC9C0',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'video_showcase',
        'heading_tag' => 'h2',
        'heading' => 'Title, slider',
        'intro' => $video_intro,
        'layout_style' => 'feature_slider',
        'slides' => $video_slides,
        'section_background' => '#FFFFFF',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'testimonials',
        'heading_tag' => 'h2',
        'heading_text' => 'Testimonials',
        'layout_style' => 'editorial_featured',
        'source_mode' => 'manual',
        'manual_items' => [
            [
                'quote' => $testimonial_quote,
                'author_name' => 'Tom',
                'author_title' => 'Service User',
                'card_tone' => 'lavender',
            ],
            [
                'quote' => $testimonial_quote,
                'author_name' => 'Tom',
                'author_title' => 'Service User',
                'card_tone' => 'lavender',
            ],
            [
                'quote' => $testimonial_quote_long,
                'author_name' => 'Tom',
                'author_title' => 'Service User',
                'card_tone' => 'mauve',
            ],
        ],
        'footer_action_mode' => 'none',
        'background_image' => $testimonials_bg_id,
        'background_color' => '#F6EDE0',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'locations_grid',
        'heading_tag' => 'h2',
        'heading' => 'Our locations',
        'cards' => $location_cards,
        'footer_button_link' => [
            'title' => 'Locations Page',
            'url' => $locations_url,
            'target' => '',
        ],
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'faqs',
        'heading' => 'Frequently Asked Questions',
        'heading_tag' => 'h2',
        'show_heading' => 1,
        'layout_style' => 'default',
        'source_mode' => $healthcare_faq_category_id > 0 ? 'category' : 'all',
        'selected_faq_categories' => $healthcare_faq_category_id > 0 ? [$healthcare_faq_category_id] : [],
        'section_background' => '#FBFAF7',
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
            'Seeded Refer an Adult for Inpatient Care page (%d) with %d flexi blocks.',
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
