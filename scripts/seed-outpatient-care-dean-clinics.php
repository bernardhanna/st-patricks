<?php

/**
 * Seed Outpatient Care - Dean Clinics (page 222) to match Figma frame 3279:18363.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-outpatient-care-dean-clinics.php
 */

$post_id = (int) (get_page_by_path('what-we-offer/outpatient-care-dean-clinics')?->ID ?? 0);

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at what-we-offer/outpatient-care-dean-clinics.');
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
$what_we_offer_url = home_url('/what-we-offer/');
$locations_url = home_url('/about-us/our-locations/');
$healthcare_url = home_url('/healthcare-professionals/');

$figma = [
    'hero' => 'https://www.figma.com/api/mcp/asset/a2f5d4b9-8318-4990-b824-05ee4ca5b3fc',
    'location_1' => 'https://www.figma.com/api/mcp/asset/8bdee9da-b82c-4bf4-b0fb-e595f8f8985e',
    'location_2' => 'https://www.figma.com/api/mcp/asset/23e84be8-8656-4074-add9-c88f221a779a',
    'location_3' => 'https://www.figma.com/api/mcp/asset/71a01689-a8a2-4260-937b-5c54d88c14b7',
    'location_4' => 'https://www.figma.com/api/mcp/asset/a2f5d4b9-8318-4990-b824-05ee4ca5b3fc',
    'testimonials_bg' => 'https://www.figma.com/api/mcp/asset/f47b4f05-2e5b-4ab0-9c45-5d664493cb9e',
    'video' => 'https://www.figma.com/api/mcp/asset/27dadf09-96e1-4248-afaf-0087e28a3832',
];

$hero_image_id = matrix_seed_resolve_image($figma['hero'], 'dean-clinics-hero-3279-18363', 'Outpatient Care Dean Clinics hero');
$testimonials_bg_id = matrix_seed_resolve_image($figma['testimonials_bg'], 'dean-clinics-testimonials-3279-18363', 'Dean Clinics testimonials background');
$video_poster_id = matrix_seed_resolve_image($figma['video'], 'dean-clinics-video-3279-18363', 'Dean Clinics video');
$location_image_ids = [
    matrix_seed_resolve_image($figma['location_1'], 'dean-clinics-location-willow-grove-3279-18363', 'Willow Grove Adolescent Unit'),
    matrix_seed_resolve_image($figma['location_2'], 'dean-clinics-location-spuh-3279-18363', 'St Patricks University Hospital'),
    matrix_seed_resolve_image($figma['location_3'], 'dean-clinics-location-lucan-3279-18363', 'St Patricks Hospital Lucan'),
    matrix_seed_resolve_image($figma['location_4'], 'dean-clinics-location-dean-3279-18363', 'Dean Clinic'),
];

$hero_copy = 'What we offer details page - is a landing page (per sitemap) that links users to all other subpages within the \'what we offer\' section. Page context goes here. Max 4 lines of text.';
$testimonial_quote = '<p>Through our Advocacy Committee, we respond to all relevant calls for submissions by the Dáil, Seanad and Government departments.</p>';
$video_intro = '<p>Videos and images section as requested. Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.</p>';
$accordion_open_body = '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat m dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor inc <strong>consectetur adipisicing</strong>.</p>';
$content_cta_body = '<p>Visit our dedicated healthcare content - Placeholder text</p><p>3-4x lines max. Videos and images section as requested. Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna.</p>';

$video_slides = [];
foreach (range(1, 5) as $index) {
    $video_slides[] = [
        'poster_image' => matrix_seed_build_image_field($video_poster_id, 'Dean Clinics video slide ' . $index),
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
            ['breadcrumb_link' => ['title' => 'What we offer', 'url' => $what_we_offer_url, 'target' => '']],
        ],
        'current_crumb_label' => 'What we offer details page (Outpatient care - dean clinics)',
        'heading_tag' => 'h1',
        'heading' => 'Outpatient Care - Dean Clinics',
        'content' => '<p>' . esc_html($hero_copy) . '</p>',
        'primary_button' => [
            'title' => 'Rferrals to Homecare for GPs',
            'url' => $healthcare_url,
            'target' => '',
        ],
        'hero_image' => $hero_image_id,
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
    ],
    [
        'acf_fc_layout' => 'locations_grid',
        'heading_tag' => 'h2',
        'heading' => 'Where you will receive care',
        'cards' => $location_cards,
        'footer_button_link' => [
            'title' => 'Locations Page',
            'url' => $locations_url,
            'target' => '',
        ],
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
    [
        'acf_fc_layout' => 'testimonials',
        'heading_tag' => 'h2',
        'heading_text' => 'Testimonials',
        'layout_style' => 'default',
        'source_mode' => 'manual',
        'manual_items' => [
            ['quote' => $testimonial_quote, 'author_name' => 'Tom', 'author_title' => 'Service User', 'card_tone' => 'lavender'],
            ['quote' => $testimonial_quote, 'author_name' => 'Tom', 'author_title' => 'Service User', 'card_tone' => 'mauve'],
            ['quote' => $testimonial_quote, 'author_name' => 'Tom', 'author_title' => 'Service User', 'card_tone' => 'mauve'],
            ['quote' => $testimonial_quote, 'author_name' => 'Tom', 'author_title' => 'Service User', 'card_tone' => 'lavender'],
            ['quote' => $testimonial_quote, 'author_name' => 'Tom', 'author_title' => 'Service User', 'card_tone' => 'mauve'],
        ],
        'footer_action_mode' => 'load_more',
        'load_more_button_text' => 'Load more testimonials',
        'background_image' => $testimonials_bg_id,
        'background_color' => '#F6EDE0',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'video_showcase',
        'heading_tag' => 'h2',
        'heading' => 'H2. Lorem ipsum dolor sit',
        'intro' => $video_intro,
        'layout_style' => 'feature_slider',
        'slides' => $video_slides,
        'section_background' => '#FFFFFF',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content_cta',
        'heading_tag' => 'h2',
        'heading' => 'Are you a healthcare professional?',
        'body' => $content_cta_body,
        'button_link' => [
            'title' => 'Healthcare professionals',
            'url' => $healthcare_url,
            'target' => '',
        ],
        'background_type' => 'color',
        'background_color' => '#E9E2F7',
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
            'Seeded Outpatient Care - Dean Clinics page (%d) with %d flexi blocks.',
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
