<?php

/**
 * Seed Inpatient Care (page 212) with content scraped from stpatricks.ie.
 *
 * Source: https://www.stpatricks.ie/care-treatment/inpatient-hospital-care
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-inpatient-care.php
 */

$post_id = (int) (get_page_by_path('inpatient-care')?->ID ?? 0);

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at inpatient-care.');
    }

    exit(1);
}

if (! function_exists('matrix_seed_import_scraped_image')) {
    function matrix_seed_import_scraped_image(string $url, string $title, string $cache_key): int
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
                    'key' => '_matrix_seed_scraped_key',
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
        $filename = $path ? basename((string) $path) : 'scraped-image.jpg';

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

        update_post_meta($attachment_id, '_matrix_seed_scraped_key', $cache_key);
        update_post_meta($attachment_id, '_matrix_seed_scraped_url', $url);

        return (int) $attachment_id;
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
$what_we_offer_url = home_url('/what-we-offer/');
$locations_archive_url = home_url('/about-us/our-locations/');
$your_stay_adult_url = home_url('/service-users-and-visitors/your-stay-in-hospital-as-an-adult/');
$homecare_url = home_url('/what-we-offer/st-patricks-at-home/');
$faqs_url = home_url('/service-users-and-visitors/frequently-asked-questions-faqs/');
$referrals_url = home_url('/make-a-referral/');

$spuh_post = get_posts([
    'post_type' => 'locations',
    'name' => 'st-patricks-university-hospital',
    'post_status' => 'publish',
    'posts_per_page' => 1,
]);
$lucan_post = get_posts([
    'post_type' => 'locations',
    'name' => 'st-patricks-hospital-lucan',
    'post_status' => 'publish',
    'posts_per_page' => 1,
]);

$spuh_id = $spuh_post !== [] ? (int) $spuh_post[0]->ID : 0;
$lucan_id = $lucan_post !== [] ? (int) $lucan_post[0]->ID : 0;
$spuh_url = $spuh_id > 0 ? get_permalink($spuh_id) : $locations_archive_url;
$lucan_url = $lucan_id > 0 ? get_permalink($lucan_id) : $locations_archive_url;

$scraped_images = [
    'hero' => 'https://www.stpatricks.ie/media/3434/steevens-lane-view-of-st-patricks-university-hospital.png',
    'spuh' => 'https://www.stpatricks.ie/media/1869/st-patricks-mental-health-services-garden.jpg?width=610&height=332&mode=crop',
    'lucan' => 'https://www.stpatricks.ie/media/1275/6472001461_38012a87c5_b.jpg?width=610&height=332&mode=crop',
];

$hero_image_id = matrix_seed_import_scraped_image(
    $scraped_images['hero'],
    'St Patricks University Hospital - Steevens Lane',
    'inpatient-care-scraped-hero'
);
$spuh_image_id = matrix_seed_import_scraped_image(
    $scraped_images['spuh'],
    'St Patricks Mental Health Services garden',
    'inpatient-care-scraped-spuh'
);
$lucan_image_id = matrix_seed_import_scraped_image(
    $scraped_images['lucan'],
    'St Patricks Hospital Lucan',
    'inpatient-care-scraped-lucan'
);

$hero_intro = 'If you are experiencing a mental health difficulty, inpatient hospital care might be part of your recovery.';

$responding_body = '<p>If you are referred to our services and an assessment finds inpatient care is right for you, you may be staying in one of our three approved centres. Where you stay depends on your individual care plan and your treatment needs.</p>'
    . '<p>Our approved centres include <a href="' . esc_url($spuh_url) . '">St Patrick\'s University Hospital</a> with 208 inpatient beds and <a href="' . esc_url($lucan_url) . '">St Patrick\'s Hospital Lucan</a> with 52 inpatient beds, where we provide inpatient care for our adult service users. You can find out more about these hospitals in this section.</p>'
    . '<p>Inpatient care for adolescents is delivered in our Willow Grove Adolescent Unit with 14 inpatient beds. Please visit the Adolescent Mental Health Services section of our website to learn more about Willow Grove.</p>';

$selected_location_ids = array_values(array_filter([$spuh_id, $lucan_id]));

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
        'current_crumb_label' => 'Inpatient hospital care',
        'heading_tag' => 'h1',
        'heading' => 'Inpatient hospital care',
        'content' => '<p>' . esc_html($hero_intro) . '</p>',
        'hero_image' => $hero_image_id,
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Responding to your needs',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'intro_text' => '',
        'content' => $responding_body,
        'column_layout' => 'one_column',
        'background_type' => 'white',
        'text_width' => 'constrained',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'locations_grid',
        'heading_tag' => 'h2',
        'heading' => 'Our approved centres',
        'source_mode' => $selected_location_ids !== [] ? 'locations' : 'manual',
        'selected_locations' => $selected_location_ids,
        'cards' => [
            [
                'title' => "St Patrick's University Hospital",
                'image' => matrix_seed_build_image_field($spuh_image_id, "St Patrick's University Hospital"),
                'link' => [
                    'title' => 'Find out more about our Dublin 8 hospital',
                    'url' => $spuh_url,
                    'target' => '',
                ],
            ],
            [
                'title' => "St Patrick's Hospital Lucan",
                'image' => matrix_seed_build_image_field($lucan_image_id, "St Patrick's Hospital Lucan"),
                'link' => [
                    'title' => 'See our Lucan hospital',
                    'url' => $lucan_url,
                    'target' => '',
                ],
            ],
        ],
        'footer_button_link' => [
            'title' => 'View all locations',
            'url' => $locations_archive_url,
            'target' => '',
        ],
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'useful_links',
        'heading_tag' => 'h2',
        'heading' => 'In this section',
        'variant' => 'flexi',
        'links' => [
            ['link' => ['title' => "St Patrick's University Hospital", 'url' => $spuh_url, 'target' => '']],
            ['link' => ['title' => "St Patrick's, Lucan", 'url' => $lucan_url, 'target' => '']],
            ['link' => ['title' => 'Your Stay', 'url' => $your_stay_adult_url, 'target' => '']],
            ['link' => ['title' => 'Visiting information', 'url' => $your_stay_adult_url, 'target' => '']],
            ['link' => ['title' => 'How to Access', 'url' => $referrals_url, 'target' => '']],
        ],
        'background_color' => '#E9E2F7',
        'heading_color' => '#1E244B',
        'link_color' => '#1E244B',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content_cta',
        'heading_tag' => 'h2',
        'heading' => 'Continue to…',
        'body' => '<p>Learn about our homecare service for people who need support at home as part of their recovery.</p>',
        'button_link' => [
            'title' => 'Homecare service',
            'url' => $homecare_url,
            'target' => '',
        ],
        'background_type' => 'color',
        'background_color' => '#CEF2EE',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content_cta',
        'heading_tag' => 'h2',
        'heading' => 'Queries',
        'body' => '<p>For general queries, please call us. For more on mental health and our services, see our frequently asked questions (FAQs).</p><p><strong>01 249 3200</strong></p>',
        'button_link' => [
            'title' => 'See our FAQs',
            'url' => $faqs_url,
            'target' => '',
        ],
        'background_type' => 'color',
        'background_color' => '#C6ECF4',
        'padding_settings' => [
            ['screen_size' => 'mob', 'padding_top' => '3', 'padding_bottom' => '1.5'],
            ['screen_size' => 'lg', 'padding_top' => '6.25', 'padding_bottom' => '1.5'],
        ],
    ],
    [
        'acf_fc_layout' => 'content_cta',
        'heading_tag' => 'h2',
        'heading' => 'Referrals',
        'body' => '<p>Contact our Referral and Assessment Service for queries regarding referrals to our services.</p><p><strong>01 249 3635</strong></p>',
        'button_link' => [
            'title' => 'See more from our referrals team',
            'url' => $referrals_url,
            'target' => '',
        ],
        'background_type' => 'color',
        'background_color' => '#CEF2EE',
        'padding_settings' => [
            ['screen_size' => 'mob', 'padding_top' => '1.5', 'padding_bottom' => '3'],
            ['screen_size' => 'lg', 'padding_top' => '1.5', 'padding_bottom' => '6.25'],
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
            'Seeded Inpatient Care page (%d) with %d flexi blocks scraped from stpatricks.ie.',
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
