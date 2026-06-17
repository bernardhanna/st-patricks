<?php

/**
 * Seed all SPMHS locations from stpatricks.ie/contact and populate /about-us/our-locations/.
 *
 * Source: https://www.stpatricks.ie/contact
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-our-locations.php
 */

require_once __DIR__ . '/lib/page-seed-conventions.php';

$page_id = (int) (matrix_seed_resolve_page_id_by_path('about-us/our-locations') ?? 0);

if ($page_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at about-us/our-locations.');
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

if (! function_exists('matrix_seed_ensure_term')) {
    function matrix_seed_ensure_term(string $taxonomy, string $name, string $slug = ''): int
    {
        $slug = $slug !== '' ? $slug : sanitize_title($name);
        $existing = get_term_by('slug', $slug, $taxonomy);

        if ($existing instanceof WP_Term) {
            return (int) $existing->term_id;
        }

        $result = wp_insert_term($name, $taxonomy, ['slug' => $slug]);

        if (is_wp_error($result)) {
            return 0;
        }

        return (int) ($result['term_id'] ?? 0);
    }
}

if (! function_exists('matrix_seed_ensure_location')) {
    /**
     * @param array<string, mixed> $args
     */
    function matrix_seed_ensure_location(array $args): int
    {
        $slug = (string) ($args['slug'] ?? '');
        $title = (string) ($args['title'] ?? '');
        $seed_key = (string) ($args['seed_key'] ?? $slug);

        $existing = get_posts([
            'post_type' => 'locations',
            'post_status' => 'any',
            'posts_per_page' => 1,
            'name' => $slug,
            'meta_query' => [
                [
                    'key' => '_matrix_seed_key',
                    'value' => $seed_key,
                ],
            ],
        ]);

        if ($existing !== []) {
            $post_id = (int) $existing[0]->ID;
            wp_update_post([
                'ID' => $post_id,
                'post_title' => $title,
                'post_excerpt' => (string) ($args['excerpt'] ?? ''),
                'post_status' => 'publish',
            ]);
        } else {
            $post_id = (int) wp_insert_post([
                'post_type' => 'locations',
                'post_status' => 'publish',
                'post_title' => $title,
                'post_name' => $slug,
                'post_excerpt' => (string) ($args['excerpt'] ?? ''),
            ]);

            if ($post_id < 1) {
                return 0;
            }

            update_post_meta($post_id, '_matrix_seed_key', $seed_key);
        }

        if (! empty($args['term_id'])) {
            wp_set_object_terms($post_id, [(int) $args['term_id']], 'location_type');
        }

        if (! empty($args['card_image'])) {
            update_field('card_image', $args['card_image'], $post_id);
        }

        if (! empty($args['listing_summary'])) {
            update_field('listing_summary', (string) $args['listing_summary'], $post_id);
        }

        if (array_key_exists('address', $args)) {
            update_field('address', (string) ($args['address'] ?? ''), $post_id);
        }

        if (array_key_exists('phone', $args)) {
            update_field('phone', (string) ($args['phone'] ?? ''), $post_id);
        }

        if (array_key_exists('email', $args)) {
            update_field('email', (string) ($args['email'] ?? ''), $post_id);
        }

        if (array_key_exists('latitude', $args) && $args['latitude'] !== null && $args['latitude'] !== '') {
            update_field('latitude', (float) $args['latitude'], $post_id);
        }

        if (array_key_exists('longitude', $args) && $args['longitude'] !== null && $args['longitude'] !== '') {
            update_field('longitude', (float) $args['longitude'], $post_id);
        }

        if (array_key_exists('show_on_contact_map', $args)) {
            update_field('show_on_contact_map', ! empty($args['show_on_contact_map']) ? 1 : 0, $post_id);
        } else {
            update_field('show_on_contact_map', 1, $post_id);
        }

        if (! empty($args['opening_hours']) && is_array($args['opening_hours'])) {
            update_field('opening_hours', $args['opening_hours'], $post_id);
        }

        if (! empty($args['featured_image_id'])) {
            set_post_thumbnail($post_id, (int) $args['featured_image_id']);
        }

        if (! empty($args['flexi_rows']) && function_exists('update_field')) {
            update_field('hero_content_blocks', [], $post_id);
            update_field('flexible_content_blocks', $args['flexi_rows'], $post_id);
        }

        return $post_id;
    }
}

if (! function_exists('matrix_seed_build_location_detail_rows')) {
    /**
     * @param array<string, mixed> $location
     * @return array<int, array<string, mixed>>
     */
    function matrix_seed_build_location_detail_rows(array $location, string $home, string $about_us_url, string $our_locations_url): array
    {
        $phone = trim((string) ($location['phone'] ?? ''));
        $address = trim((string) ($location['address'] ?? ''));
        $visiting = trim((string) ($location['visiting'] ?? ''));
        $intro = trim((string) ($location['intro'] ?? ''));
        $hero_image_id = (int) ($location['hero_image_id'] ?? 0);
        $card_image_id = (int) ($location['card_image_id'] ?? $hero_image_id);

        $detail_parts = [];

        if ($address !== '') {
            $detail_parts[] = '<p><strong>Address:</strong> ' . esc_html($address) . '</p>';
        }

        if ($phone !== '') {
            $tel = preg_replace('/\s+/', '', $phone);
            $detail_parts[] = '<p><strong>Phone:</strong> <a href="tel:' . esc_attr($tel) . '">' . esc_html($phone) . '</a></p>';
        }

        if ($visiting !== '') {
            $detail_parts[] = '<p><strong>Visiting times:</strong> ' . esc_html($visiting) . '</p>';
        }

        $detail_body = implode('', $detail_parts);

        if ($intro !== '') {
            $detail_body = '<p>' . esc_html($intro) . '</p>' . $detail_body;
        }

        return [
            [
                'acf_fc_layout' => 'hero_with_breadcrumbs',
                'layout_style' => 'image_split',
                'show_breadcrumbs' => 1,
                'breadcrumb_source' => 'manual',
                'manual_breadcrumbs' => [
                    ['breadcrumb_link' => ['title' => 'Home', 'url' => $home, 'target' => '']],
                    ['breadcrumb_link' => ['title' => 'About us', 'url' => $about_us_url, 'target' => '']],
                    ['breadcrumb_link' => ['title' => 'Our locations', 'url' => $our_locations_url, 'target' => '']],
                ],
                'current_crumb_label' => (string) $location['title'],
                'heading_tag' => matrix_page_seed_heading(1),
                'heading' => (string) $location['title'],
                'content' => $intro !== '' ? '<p>' . esc_html($intro) . '</p>' : '',
                'hero_image' => $hero_image_id > 0 ? $hero_image_id : $card_image_id,
                'background_color' => '#C6ECF4',
                'breadcrumb_background_color' => '#F1F8F9',
                'heading_color' => '#08284B',
                'text_color' => '#08284B',
            ],
            [
                'acf_fc_layout' => 'content',
                'heading' => 'Contact and visiting information',
                'heading_tag' => matrix_page_seed_heading(2),
                'accent_position' => 'below_heading',
                'content' => $detail_body,
                'column_layout' => 'one_column',
                'background_type' => 'white',
                'text_width' => 'constrained',
            ],
        ];
    }
}

$home = home_url('/');
$about_us_url = home_url('/about-us/');
$our_locations_url = home_url('/about-us/our-locations/');
$faqs_url = home_url('/service-users-and-visitors/frequently-asked-questions-faqs/');
$referrals_url = home_url('/make-a-referral/');
$visiting_url = home_url('/service-users-and-visitors/your-stay-in-hospital-as-an-adult/');

$hospital_term_id = matrix_seed_ensure_term('location_type', 'Hospital', 'hospital');
$clinic_term_id = matrix_seed_ensure_term('location_type', 'Dean Clinic', 'dean-clinic');

$default_visiting_hours = [
    ['day_label' => 'Mon - Fri', 'hours' => '2pm - 5pm'],
    ['day_label' => 'Mon - Fri', 'hours' => '6pm - 8.30pm'],
];

$default_clinic_hours = [
    ['day_label' => 'Mon - Fri', 'hours' => '09:00 - 17:00'],
];

$base_media = 'https://www.stpatricks.ie';

$image_ids = [
    'spuh' => matrix_seed_import_scraped_image(
        $base_media . '/media/1140/banner12.jpg?width=610&height=332&mode=crop',
        'St Patricks University Hospital',
        'our-locations-spuh-card'
    ),
    'lucan' => matrix_seed_import_scraped_image(
        $base_media . '/media/1401/st-edmundsbury-hospital-entrance.jpg?width=610&height=332&mode=crop',
        'St Patricks Hospital Lucan entrance',
        'our-locations-lucan-card'
    ),
    'willow_grove' => matrix_seed_import_scraped_image(
        $base_media . '/media/1276/6472023305_baac09ce4f_o.jpg?width=610&height=332&mode=crop',
        'Willow Grove Adolescent Unit',
        'our-locations-willow-grove-card'
    ),
    'spuh_hero' => matrix_seed_import_scraped_image(
        $base_media . '/media/3434/steevens-lane-view-of-st-patricks-university-hospital.png',
        'Stevens Lane view of St Patricks University Hospital',
        'our-locations-spuh-hero'
    ),
    'dean_clinic' => matrix_seed_import_scraped_image(
        $base_media . '/media/1869/st-patricks-mental-health-services-garden.jpg?width=610&height=332&mode=crop',
        'Dean Clinic',
        'our-locations-dean-clinic-card'
    ),
];

$locations = [
    [
        'seed_key' => 'location-spuh',
        'slug' => 'st-patricks-university-hospital',
        'title' => 'St Patrick\'s University Hospital (SPUH)',
        'term_id' => $hospital_term_id,
        'excerpt' => 'St Patrick\'s University Hospital, James\' Street, Dublin 8, D08 K7YW, Ireland',
        'listing_summary' => 'Find out more about our Dublin 8 hospital',
        'address' => 'St Patrick\'s University Hospital, James\' Street, Dublin 8, D08 K7YW, Ireland',
        'phone' => '01 249 3200',
        'latitude' => 53.3439,
        'longitude' => -6.2940,
        'opening_hours' => $default_visiting_hours,
        'visiting' => '2pm to 5pm and 6pm to 8.30pm',
        'intro' => 'Our main campus on James\' Street, Dublin 8.',
        'card_image_id' => $image_ids['spuh'],
        'hero_image_id' => $image_ids['spuh_hero'],
        'preserve_existing_flexi' => true,
    ],
    [
        'seed_key' => 'location-lucan',
        'slug' => 'st-patricks-hospital-lucan',
        'title' => 'St Patrick\'s Hospital Lucan',
        'term_id' => $hospital_term_id,
        'excerpt' => 'St Patrick\'s Hospital Lucan (St Edmundsbury), Old Lucan Road, Lucan, County Dublin, Ireland',
        'listing_summary' => 'See our Lucan hospital',
        'address' => 'St Patrick\'s Hospital Lucan (St Edmundsbury), Old Lucan Road, Lucan, County Dublin, Ireland',
        'phone' => '01 621 8200',
        'latitude' => 53.3579,
        'longitude' => -6.4489,
        'opening_hours' => $default_visiting_hours,
        'visiting' => '2pm to 5pm and 6pm to 8.30pm',
        'intro' => 'Our Lucan campus provides inpatient care and day services.',
        'card_image_id' => $image_ids['lucan'],
        'hero_image_id' => $image_ids['lucan'],
        'preserve_existing_flexi' => true,
    ],
    [
        'seed_key' => 'location-willow-grove',
        'slug' => 'willow-grove-adolescent-unit',
        'title' => 'Willow Grove Adolescent Unit',
        'term_id' => $hospital_term_id,
        'excerpt' => 'St Patrick\'s University Hospital, James\' Street, Dublin 8, D08 K7YW, Ireland',
        'listing_summary' => 'Inpatient adolescent mental health services',
        'address' => 'St Patrick\'s University Hospital, James\' Street, Dublin 8, D08 K7YW, Ireland',
        'phone' => '01 249 3687',
        'latitude' => 53.3435,
        'longitude' => -6.2935,
        'opening_hours' => $default_visiting_hours,
        'visiting' => 'Contact Willow Grove for visiting',
        'intro' => 'Our adolescent unit on the Dublin 8 campus.',
        'card_image_id' => $image_ids['willow_grove'],
        'hero_image_id' => $image_ids['willow_grove'],
    ],
    [
        'seed_key' => 'location-dean-clinic-cork',
        'slug' => 'dean-clinic-cork',
        'title' => 'Dean Clinic Cork',
        'term_id' => $clinic_term_id,
        'excerpt' => 'Citygate, Mahon, Cork, Ireland',
        'listing_summary' => 'Outpatient care in Cork',
        'address' => 'Citygate, Mahon, Cork, Ireland',
        'phone' => '01 249 3502',
        'latitude' => 51.8970,
        'longitude' => -8.3980,
        'opening_hours' => $default_clinic_hours,
        'visiting' => '',
        'intro' => 'Community-based outpatient mental health services in Cork.',
        'card_image_id' => $image_ids['dean_clinic'],
        'hero_image_id' => $image_ids['dean_clinic'],
    ],
    [
        'seed_key' => 'location-dean-clinic-galway',
        'slug' => 'dean-clinic-galway',
        'title' => 'Dean Clinic Galway',
        'term_id' => $clinic_term_id,
        'excerpt' => 'Merchant\'s Road, Galway',
        'listing_summary' => 'Outpatient care in Galway',
        'address' => 'Merchant\'s Road, Galway',
        'phone' => '091 513 540',
        'latitude' => 53.2761,
        'longitude' => -9.0554,
        'opening_hours' => $default_clinic_hours,
        'visiting' => '',
        'intro' => 'Community-based outpatient mental health services in Galway.',
        'card_image_id' => $image_ids['dean_clinic'],
        'hero_image_id' => $image_ids['dean_clinic'],
    ],
    [
        'seed_key' => 'location-dean-clinic-lucan',
        'slug' => 'dean-clinic-lucan',
        'title' => 'Dean Clinic Lucan',
        'term_id' => $clinic_term_id,
        'excerpt' => 'Lucan, County Dublin',
        'listing_summary' => 'Outpatient care in Lucan',
        'address' => 'Lucan, County Dublin',
        'phone' => '01 249 3590',
        'latitude' => 53.3582,
        'longitude' => -6.4495,
        'opening_hours' => $default_clinic_hours,
        'visiting' => '',
        'intro' => 'Outpatient services on the grounds of St Patrick\'s Hospital Lucan.',
        'card_image_id' => $image_ids['dean_clinic'],
        'hero_image_id' => $image_ids['dean_clinic'],
    ],
    [
        'seed_key' => 'location-dean-clinic-st-patricks',
        'slug' => 'dean-clinic-st-patricks',
        'title' => 'Dean Clinic St Patrick\'s',
        'term_id' => $clinic_term_id,
        'excerpt' => 'James\' Street, Dublin 8',
        'listing_summary' => 'Outpatient care in Dublin 8',
        'address' => 'James\' Street, Dublin 8',
        'phone' => '01 249 3590',
        'latitude' => 53.3437,
        'longitude' => -6.2942,
        'opening_hours' => $default_clinic_hours,
        'visiting' => '',
        'intro' => 'Outpatient services on the Dublin 8 campus.',
        'card_image_id' => $image_ids['dean_clinic'],
        'hero_image_id' => $image_ids['dean_clinic'],
    ],
    [
        'seed_key' => 'location-adolescent-dean-clinic',
        'slug' => 'adolescent-dean-clinic',
        'title' => 'Adolescent Dean Clinic',
        'term_id' => $clinic_term_id,
        'excerpt' => 'St Patrick\'s University Hospital, Dublin 8',
        'listing_summary' => 'Outpatient adolescent mental health services',
        'address' => 'St Patrick\'s University Hospital, Dublin 8',
        'phone' => '01 249 3590',
        'latitude' => 53.3436,
        'longitude' => -6.2938,
        'opening_hours' => $default_clinic_hours,
        'visiting' => '',
        'intro' => 'Assessment and therapy for young people at an outpatient level.',
        'card_image_id' => $image_ids['dean_clinic'],
        'hero_image_id' => $image_ids['dean_clinic'],
    ],
];

$location_ids = [];

foreach ($locations as $location) {
    $card_image_id = (int) ($location['card_image_id'] ?? 0);
    $hero_image_id = (int) ($location['hero_image_id'] ?? $card_image_id);

    $args = [
        'seed_key' => $location['seed_key'],
        'slug' => $location['slug'],
        'title' => $location['title'],
        'excerpt' => $location['excerpt'],
        'listing_summary' => $location['listing_summary'],
        'term_id' => $location['term_id'],
        'card_image' => matrix_seed_build_image_field($card_image_id, $location['title']),
        'featured_image_id' => $hero_image_id,
        'address' => (string) ($location['address'] ?? ''),
        'phone' => (string) ($location['phone'] ?? ''),
        'latitude' => $location['latitude'] ?? null,
        'longitude' => $location['longitude'] ?? null,
        'opening_hours' => $location['opening_hours'] ?? [],
        'show_on_contact_map' => true,
    ];

    if (empty($location['preserve_existing_flexi'])) {
        $args['flexi_rows'] = matrix_seed_build_location_detail_rows(
            $location,
            $home,
            $about_us_url,
            $our_locations_url
        );
    }

    $location_id = matrix_seed_ensure_location($args);

    if ($location_id > 0) {
        $location_ids[] = $location_id;
    }
}

$hero_image_id = $image_ids['spuh_hero'] > 0 ? $image_ids['spuh_hero'] : $image_ids['spuh'];

$contact_intro = '<p>Below, you can find some of the key contacts and visiting information for our campuses and services here in St Patrick\'s Mental Health Services.</p>'
    . '<p>If you have queries about our services or mental health supports, you may find it helpful to <a href="' . esc_url($faqs_url) . '">visit our Frequently Asked Questions page</a>.</p>'
    . '<p>If you wish to refer a patient to us, or if you have a query about a referral made for you, please contact our Referral and Assessment Service by calling <a href="tel:012493635">01 249 3635</a>.</p>';

$getting_there = '<h3>St Patrick\'s University Hospital and Willow Grove Adolescent Unit</h3>'
    . '<p>You can <a href="https://maps.app.goo.gl/VBsyyXzA1aD2YnoH8" target="_blank" rel="noopener noreferrer">find a map to our Dublin 8 campus here</a>. There is a paid car park available at the campus entrance on Steeven\'s Lane.</p>'
    . '<p><strong>Rail or Luas:</strong> Heuston Station is less than a five minute walk from St Patrick\'s University Hospital.</p>'
    . '<p><strong>Dublin Bus:</strong> Take the G1, G2, 13 or 123 to James\' Street or the C1, C2, C3, C4, G1, 26, 52, or 145 to Heuston Station.</p>'
    . '<h3>St Patrick\'s Hospital Lucan</h3>'
    . '<p>You can <a href="https://maps.app.goo.gl/pBRGjtom1tRSbfNV9" target="_blank" rel="noopener noreferrer">find a map to our hospital in Lucan here</a>. Limited free car parking is available on site. Dublin Bus routes C3 and C4 stop close by the hospital grounds.</p>';

$flexi_rows = [
    [
        'acf_fc_layout' => 'hero_with_breadcrumbs',
        'layout_style' => 'image_split',
        'show_breadcrumbs' => 1,
        'breadcrumb_source' => 'manual',
        'manual_breadcrumbs' => [
            ['breadcrumb_link' => ['title' => 'Home', 'url' => $home, 'target' => '']],
            ['breadcrumb_link' => ['title' => 'About us', 'url' => $about_us_url, 'target' => '']],
        ],
        'current_crumb_label' => 'Our locations',
        'heading_tag' => matrix_page_seed_heading(1),
        'heading' => 'Our locations',
        'content' => '<p>Find contact details, visiting information and directions for our hospitals and Dean Clinics across Ireland.</p>',
        'hero_image' => $hero_image_id,
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
    ],
    [
        'acf_fc_layout' => 'content_cta',
        'heading_tag' => matrix_page_seed_heading(2),
        'heading' => 'Queries',
        'body' => '<p>For general queries, please call us. For more on mental health and our services, see our <a href="' . esc_url($faqs_url) . '">frequently asked questions (FAQs)</a>.</p>',
        'button_link' => [
            'title' => '01 249 3200',
            'url' => 'tel:012493200',
            'target' => '',
        ],
        'background_type' => 'color',
        'background_color' => '#CEF2EE',
    ],
    [
        'acf_fc_layout' => 'content_cta',
        'heading_tag' => matrix_page_seed_heading(2),
        'heading' => 'Referrals',
        'body' => '<p>Contact our Referral and Assessment Service for queries regarding referrals to our services. <a href="' . esc_url($referrals_url) . '">See more from our referrals team</a>.</p>',
        'button_link' => [
            'title' => '01 249 3635',
            'url' => 'tel:012493635',
            'target' => '',
        ],
        'background_type' => 'color',
        'background_color' => '#E9E2F7',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Contact numbers',
        'heading_tag' => matrix_page_seed_heading(2),
        'accent_position' => 'below_heading',
        'content' => $contact_intro,
        'column_layout' => 'one_column',
        'background_type' => 'white',
        'text_width' => 'constrained',
    ],
    [
        'acf_fc_layout' => 'locations_grid',
        'heading_tag' => matrix_page_seed_heading(2),
        'heading' => 'Find us',
        'source_mode' => 'locations',
        'selected_locations' => $location_ids,
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Getting to our hospitals',
        'heading_tag' => matrix_page_seed_heading(2),
        'accent_position' => 'below_heading',
        'content' => $getting_there,
        'column_layout' => 'one_column',
        'background_type' => 'cream',
        'text_width' => 'constrained',
    ],
    [
        'acf_fc_layout' => 'content_cta',
        'heading_tag' => matrix_page_seed_heading(2),
        'heading' => 'Visiting information',
        'body' => '<p>See our visiting information for inpatient hospital care.</p>',
        'button_link' => [
            'title' => 'See our visiting information here',
            'url' => $visiting_url,
            'target' => '',
        ],
        'background_type' => 'color',
        'background_color' => '#CEF2EE',
    ],
];

update_field('hero_content_blocks', [], $page_id);
update_field('flexible_content_blocks', $flexi_rows, $page_id);

$saved_rows = get_field('flexible_content_blocks', $page_id);
$saved_count = is_array($saved_rows) ? count($saved_rows) : 0;

if (class_exists('WP_CLI')) {
    if ($saved_count === count($flexi_rows) && count($location_ids) === count($locations)) {
        WP_CLI::success(sprintf(
            'Seeded Our Locations page (%d) with %d flexi blocks and %d location posts.',
            $page_id,
            $saved_count,
            count($location_ids)
        ));
    } else {
        WP_CLI::warning(sprintf(
            'Updated page %d with %d blocks (expected %d) and %d locations (expected %d).',
            $page_id,
            $saved_count,
            count($flexi_rows),
            count($location_ids),
            count($locations)
        ));
    }
}
