<?php

/**
 * Seed Location posts for SPUH and Lucan, scraped from stpatricks.ie.
 *
 * Sources:
 * - https://www.stpatricks.ie/care-treatment/inpatient-hospital-care/st-patrick-s-university-hospital
 * - https://www.stpatricks.ie/care-treatment/inpatient-hospital-care/st-patricks-lucan
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-locations.php
 */

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

$home = home_url('/');
$inpatient_care_url = home_url('/inpatient-care/');
$what_we_offer_url = home_url('/what-we-offer/');
$your_stay_url = home_url('/service-users-and-visitors/your-stay-in-hospital-as-an-adult/');
$faqs_url = home_url('/service-users-and-visitors/frequently-asked-questions-faqs/');
$referrals_url = home_url('/make-a-referral-cta/');
$our_team_url = home_url('/about-us/our-team/');

$hospital_term_id = matrix_seed_ensure_term('location_type', 'Hospital', 'hospital');

$section_padding = [
    ['screen_size' => 'mob', 'padding_top' => '3', 'padding_bottom' => '3'],
    ['screen_size' => 'lg', 'padding_top' => '6.25', 'padding_bottom' => '6.25'],
];

$spuh_card_image_id = matrix_seed_import_scraped_image(
    'https://www.stpatricks.ie/media/1869/st-patricks-mental-health-services-garden.jpg?width=610&height=332&mode=crop',
    'St Patricks University Hospital garden',
    'location-spuh-card'
);
$lucan_card_image_id = matrix_seed_import_scraped_image(
    'https://www.stpatricks.ie/media/1275/6472001461_38012a87c5_b.jpg?width=610&height=332&mode=crop',
    'St Patricks Hospital Lucan',
    'location-lucan-card'
);
$spuh_hero_image_id = matrix_seed_import_scraped_image(
    'https://www.stpatricks.ie/media/3434/steevens-lane-view-of-st-patricks-university-hospital.png',
    'Stevens Lane view of St Patricks University Hospital',
    'location-spuh-hero'
);

$spuh_intro = '<p>St Patrick\'s University Hospital is Ireland\'s largest independent, not-for-profit mental health hospital.</p>'
    . '<p>First founded in 1746, SPUH is the largest campus of St Patrick\'s Mental Health Services (SPMHS) and is located in Dublin 8.</p>'
    . '<p>We provide diverse mental health services, offering care and treatment for a wide range of mental health difficulties, including anxiety, depression, alcohol or chemical dependence, dual diagnosis, bipolar disorder and more.</p>'
    . '<p>There are 208 beds available in SPUH. Inpatient treatment and care are provided by multidisciplinary teams (MDTs), made up of psychiatrists, registrars, ward-based nurses, occupational therapists, social workers, psychologists, cognitive behavioural therapists and family therapists. Accommodation is mainly available in ensuite single rooms.</p>';

$spuh_location = '<p>Situated on Steeven\'s Lane in Dublin 8, between Heuston Station and St James\'s Hospital, SPUH is close to Dublin city centre. The hospital is well served by public transport, with bus, rail and LUAS services all within a five to 10-minute walk. Limited paid parking is also available for visitors travelling by car.</p>';

$spuh_visiting = '<p>We have visiting hours in place every weekday and at the weekends in SPUH. Throughout the ground floor and garden of SPUH, there are tables and seating available for visitors to spend time with their loved ones. We also have a dedicated space, the Wishing Well Family Room, for visitors and service users to use when children are visiting.</p>';

$lucan_intro = '<p>St Patrick\'s Hospital Lucan provides a wide range of mental healthcare services.</p>'
    . '<p>The hospital is one of our three approved inpatient services here in St Patrick\'s Mental Health Services (SPMHS). There are 52 beds available in St Patrick\'s Hospital Lucan, where we provide inpatient care to people experiencing diverse mental health difficulties. Accommodation for inpatient service users is mainly in single ensuite rooms.</p>'
    . '<p>A number of our day services are run through our hospital in Lucan. Outpatient mental health services are also provided through our Dean Clinic Lucan, which is based on the grounds of St Patrick\'s Hospital Lucan.</p>';

$lucan_location = '<p>St Patrick\'s Hospital Lucan is located close to Lucan village in County Dublin; it is a short drive from Dublin city, and is close to the M50 and M4 motorways. If you are travelling by car, there is limited free car parking available in the hospital grounds.</p>'
    . '<p>A number of buses pass close to the hospital also. Routes C3 and C4 from Dublin Bus travel between Maynooth and Dublin city centre, with both stopping close by the hospital grounds. It takes a five to 10-minute walk to reach the hospital building from the bus stops.</p>';

$lucan_visiting = '<p>Daily visiting times are in place every weekday and at the weekends in St Patrick\'s Hospital Lucan. There are a number of spaces around the hospital building and its grounds for visits to take place.</p>';

$spuh_flexi_rows = [
    [
        'acf_fc_layout' => 'hero_with_breadcrumbs',
        'layout_style' => 'image_split',
        'show_breadcrumbs' => 1,
        'breadcrumb_source' => 'manual',
        'manual_breadcrumbs' => [
            ['breadcrumb_link' => ['title' => 'Home', 'url' => $home, 'target' => '']],
            ['breadcrumb_link' => ['title' => 'What we offer', 'url' => $what_we_offer_url, 'target' => '']],
            ['breadcrumb_link' => ['title' => 'Inpatient hospital care', 'url' => $inpatient_care_url, 'target' => '']],
        ],
        'current_crumb_label' => 'St Patrick\'s University Hospital',
        'heading_tag' => 'h1',
        'heading' => 'St Patrick\'s University Hospital',
        'content' => '<p>St Patrick\'s University Hospital is Ireland\'s largest independent, not-for-profit mental health hospital.</p>',
        'hero_image' => $spuh_hero_image_id,
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'About SPUH',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'content' => $spuh_intro,
        'column_layout' => 'one_column',
        'background_type' => 'white',
        'text_width' => 'constrained',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Hospital location',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'content' => $spuh_location,
        'column_layout' => 'one_column',
        'background_type' => 'white',
        'text_width' => 'constrained',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content_accordion',
        'layout_style' => 'directions_page',
        'section_background' => '#FFFFFF',
        'panel_background' => '#FBFAF7',
        'open_panel_background' => 'linear-gradient(-42.77deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
        'items' => [
            matrix_seed_accordion_item(
                'Travelling by rail',
                '<p>SPUH is less than five minutes away by foot from Heuston Station, which provides a wide range of commuter rail and national rail services.</p>',
                true
            ),
            matrix_seed_accordion_item(
                'Travelling by bus',
                '<p>Dublin Bus operates a number of bus routes which stop within a three to five-minute walk from SPUH. Routes G1, G2, 13 and 123 stop along James\' Street, while the C1, C2, C3, C4, G1, 26, 52, or 145 routes stop at Heuston Station.</p>'
            ),
            matrix_seed_accordion_item(
                'Travelling by Luas',
                '<p>The Red Line Luas runs every five minutes and makes a quick link to SPUH; the journey takes between five and 10 minutes from Dublin city centre. The Heuston Station stop is a five-minute walk to SPUH.</p>'
            ),
        ],
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Visiting information',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'content' => $spuh_visiting,
        'column_layout' => 'one_column',
        'background_type' => 'cream',
        'text_width' => 'constrained',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content_accordion',
        'layout_style' => 'default',
        'section_background' => '#FBFAF7',
        'panel_background' => '#FFFFFF',
        'open_panel_background' => 'linear-gradient(-42.77deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
        'items' => [
            matrix_seed_accordion_item(
                'Wards',
                '<p>There are eight wards in SPUH. Three wards provide care for general admissions, with dedicated wards for care of the elderly, eating disorders, addictions, acute admissions, and a ward providing care exclusively for female service users.</p>',
                true
            ),
            matrix_seed_accordion_item(
                'Outdoor spaces',
                '<p>We have garden areas and green spaces in SPUH to provide rest, relaxation, and physical exercise opportunities. The Well Bean Café opens in the garden space in the summer months.</p>'
            ),
            matrix_seed_accordion_item(
                'Restaurant and shop',
                '<p>Ridgeways Restaurant is located to the rear of the main reception area in SPUH and is open to service users, visitors and staff throughout the day.</p>'
            ),
        ],
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content_cta',
        'heading_tag' => 'h2',
        'heading' => 'See visiting information for SPUH',
        'body' => '<p>Find out more about visiting times and arrangements at St Patrick\'s University Hospital.</p>',
        'button_link' => [
            'title' => 'Visiting information',
            'url' => $your_stay_url,
            'target' => '',
        ],
        'background_type' => 'color',
        'background_color' => '#CEF2EE',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content_cta',
        'heading_tag' => 'h2',
        'heading' => 'Learn more about our multidisciplinary teams',
        'body' => '<p>Read about the teams who provide inpatient care across our approved centres.</p>',
        'button_link' => [
            'title' => 'Multidisciplinary Teams',
            'url' => $our_team_url,
            'target' => '',
        ],
        'background_type' => 'color',
        'background_color' => '#E9E2F7',
        'padding_settings' => $section_padding,
    ],
];

$lucan_flexi_rows = [
    [
        'acf_fc_layout' => 'hero_with_breadcrumbs',
        'layout_style' => 'image_split',
        'show_breadcrumbs' => 1,
        'breadcrumb_source' => 'manual',
        'manual_breadcrumbs' => [
            ['breadcrumb_link' => ['title' => 'Home', 'url' => $home, 'target' => '']],
            ['breadcrumb_link' => ['title' => 'What we offer', 'url' => $what_we_offer_url, 'target' => '']],
            ['breadcrumb_link' => ['title' => 'Inpatient hospital care', 'url' => $inpatient_care_url, 'target' => '']],
        ],
        'current_crumb_label' => 'St Patrick\'s Hospital Lucan',
        'heading_tag' => 'h1',
        'heading' => 'St Patrick\'s Hospital Lucan',
        'content' => '<p>St Patrick\'s Hospital Lucan provides a wide range of mental healthcare services.</p>',
        'hero_image' => $lucan_card_image_id,
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'About our Lucan hospital',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'content' => $lucan_intro,
        'column_layout' => 'one_column',
        'background_type' => 'white',
        'text_width' => 'constrained',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Location of St Patrick\'s Hospital Lucan',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'content' => $lucan_location,
        'column_layout' => 'one_column',
        'background_type' => 'white',
        'text_width' => 'constrained',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Visiting information',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'content' => $lucan_visiting,
        'column_layout' => 'one_column',
        'background_type' => 'cream',
        'text_width' => 'constrained',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content_accordion',
        'layout_style' => 'default',
        'section_background' => '#FBFAF7',
        'panel_background' => '#FFFFFF',
        'open_panel_background' => 'linear-gradient(-42.77deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
        'items' => [
            matrix_seed_accordion_item(
                'Accommodation',
                '<p>There are 52 beds for inpatient service users in our Lucan hospital. There are 46 single rooms and three double bedrooms. Each bedroom has its own ensuite bathroom.</p>',
                true
            ),
            matrix_seed_accordion_item(
                'Grounds and gardens',
                '<p>Our Lucan campus includes a number of garden areas and a large grounds. Spending time in these natural environments can help to reduce stress and improve mood and cognitive performance.</p>'
            ),
            matrix_seed_accordion_item(
                'Technology supports',
                '<p>We offer free WiFi for service users and visitors in our Lucan hospital. Our Service User IT Support (SUITS) team runs a walk-in service from Monday to Friday.</p>'
            ),
        ],
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content_cta',
        'heading_tag' => 'h2',
        'heading' => 'Learn more about our multidisciplinary approach',
        'body' => '<p>Find out how our multidisciplinary teams support recovery across inpatient services.</p>',
        'button_link' => [
            'title' => 'Learn more about our MDTs',
            'url' => $our_team_url,
            'target' => '',
        ],
        'background_type' => 'color',
        'background_color' => '#E9E2F7',
        'padding_settings' => $section_padding,
    ],
];

$spuh_id = matrix_seed_ensure_location([
    'seed_key' => 'location-spuh',
    'slug' => 'st-patricks-university-hospital',
    'title' => 'St Patrick\'s University Hospital',
    'excerpt' => 'Our main campus is on James\' Street, Dublin 8, Ireland.',
    'listing_summary' => 'Find out more about our Dublin 8 hospital',
    'term_id' => $hospital_term_id,
    'card_image' => matrix_seed_build_image_field($spuh_card_image_id, 'St Patrick\'s University Hospital'),
    'featured_image_id' => $spuh_hero_image_id,
    'flexi_rows' => $spuh_flexi_rows,
]);

$lucan_id = matrix_seed_ensure_location([
    'seed_key' => 'location-lucan',
    'slug' => 'st-patricks-hospital-lucan',
    'title' => 'St Patrick\'s Hospital Lucan',
    'excerpt' => 'Our Lucan campus is based in St Edmundsbury, Lucan, County Dublin.',
    'listing_summary' => 'See our Lucan hospital',
    'term_id' => $hospital_term_id,
    'card_image' => matrix_seed_build_image_field($lucan_card_image_id, 'St Patrick\'s Hospital Lucan'),
    'featured_image_id' => $lucan_card_image_id,
    'flexi_rows' => $lucan_flexi_rows,
]);

if (class_exists('WP_CLI')) {
    if ($spuh_id > 0 && $lucan_id > 0) {
        WP_CLI::success(sprintf(
            'Seeded locations: SPUH (%d) %s, Lucan (%d) %s',
            $spuh_id,
            get_permalink($spuh_id),
            $lucan_id,
            get_permalink($lucan_id)
        ));
    } else {
        WP_CLI::error('Failed to seed one or more location posts.');
    }
}
