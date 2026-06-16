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
$contact_url = home_url('/contact/');
$spuh_location_url = home_url('/locations/st-patricks-university-hospital/');
$lucan_location_url = home_url('/locations/st-patricks-hospital-lucan/');
$willow_grove_url = home_url('/locations/willow-grove-adolescent-unit/');
$carers_guide_url = home_url('/carers-supporters-information-guide/');
$family_series_url = home_url('/family-mental-health-series/');
$faqs_url = home_url('/service-users-and-visitors/frequently-asked-questions-faqs/');
$referrals_url = home_url('/make-a-referral/');
$our_team_url = home_url('/about-us/multidisciplinary-teams/');

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

$spuh_visiting = '<p>At St Patrick\'s Mental Health Services (SPMHS), we have visiting opportunities in place for our service users and their supporters.</p>'
    . '<p>Before coming to our campuses, we ask that you check our latest information and updates. <a href="' . esc_url($contact_url) . '">Our campus locations and contact details can be found here</a>.</p>';

$spuh_visiting_hours = '<p>Visiting is open in <a href="' . esc_url($spuh_location_url) . '">St Patrick\'s University Hospital</a> (SPUH) and <a href="' . esc_url($lucan_location_url) . '">St Patrick\'s Hospital Lucan</a>. Visiting times are currently from 2pm to 5pm and from 6pm to 8.30pm each day. On Saturday and Sunday, there is also currently an additional visiting time of between 10am and 12.30pm.</p>'
    . '<p>Service users can welcome a small number of visitors at a time. Visiting should not take place at service user\'s mealtimes or when they are due to attend programmes or therapies. Please note that, from time to time, there may need to be changes to visiting opportunities, depending on the service user\'s ward or other factors; if this arises, it will be communicated with service users. Special arrangements may be made for service users who are unable to leave their ward; in this instance, service users should speak with the nursing staff in charge of the ward.</p>'
    . '<p>Tables and seating are widely available on the ground floor and in the gardens of both hospitals for service users and visitors to meet and spend time together. Visitors will not be able to meet service users on their wards.</p>'
    . '<p>Please note that visiting for <a href="' . esc_url($willow_grove_url) . '">Willow Grove Adolescent Unit</a> is organised separately; the Willow Grove team will liaise with service users and their families to make arrangements.</p>';

$spuh_visiting_measures_intro = '<p>We follow public health guidance for visiting to acute hospitals. We make our visiting guidelines with everyone\'s health and safety and our service users\' recovery in mind.</p>'
    . '<p>All visitors must follow the measures outlined below.</p>';

$spuh_arrival_content = '<p>If you are visiting a service user, you should check in at the main reception of the hospital when you arrive.</p>'
    . '<p>Our reception staff will ask you to complete a standard COVID-19 screening survey and agree to follow our infection control measures, which you can see further below. Once this screening is passed, the visit can go ahead.</p>';

$spuh_children_visiting_content = '<p>Yes, children and young people aged 16 or under are allowed to visit. Children and young people will need to follow the visiting guidelines and infection control measures in place.</p>'
    . '<p>We have a child-friendly visiting space, The Wishing Well Family Room, in SPUH. This is available to all children visiting a service user, once they are accompanied by a responsible adult. A fob key is needed to access this family room; please go to the main reception in SPUH to ask for this.</p>';

$spuh_infection_control_content = '<p>If you are coming to one of our campuses, please come to the main reception desk when you arrive. Our reception staff will ask you to complete a questionnaire with the queries below:</p>'
    . '<ul>'
    . '<li>Have you recently experienced or are you experiencing any respiratory or cold/flu like symptoms including cough, fever, sore throat, headache, loss of smell, loss of taste or distortion of smell or taste?</li>'
    . '<li>Are you a close contact of a confirmed or suspected case of COVID-19?</li>'
    . '<li>Have you been advised that you are currently required to self-isolate/restrict your movements?</li>'
    . '</ul>'
    . '<p>If you answer “yes” to any of the questions, we cannot allow entry to the campus. If you pass this screening protocol, you will be asked to sign a document agreeing to:</p>'
    . '<ul>'
    . '<li>perform hand hygiene</li>'
    . '<li>practice cough and sneeze etiquette.</li>'
    . '</ul>'
    . '<p>Please follow the guidelines below when you are on campus.</p>'
    . '<ul>'
    . '<li>Wash your hands thoroughly before you arrive</li>'
    . '<li>Use our hand hygiene gels at the entrance and throughout the campus</li>'
    . '<li>Observe hand hygiene and coughing and sneezing etiquette closely, <a href="https://www2.hse.ie/conditions/coronavirus/protect-yourself.html" target="_blank" rel="noopener noreferrer">as promoted by the Health Service Executive</a>, throughout your time on the campus</li>'
    . '<li>cover your mouth and nose with a tissue when coughing or sneezing</li>'
    . '<li>dispose your used tissues in a bin</li>'
    . '<li>wash your hands thoroughly after coughing or sneezing</li>'
    . '<li>wash your hands thoroughly before leaving the hospital.</li>'
    . '</ul>';

$carers_card_image_id = matrix_seed_import_scraped_image(
    'https://www.stpatricks.ie/media/3320/carers-and-supporers.png?width=400&height=218&mode=crop',
    'Carers and Supporters Information Guide',
    'location-spuh-carers-card'
);
$family_series_card_image_id = matrix_seed_import_scraped_image(
    'https://www.stpatricks.ie/media/3337/website-launch-of-family-lecture-series.png?width=400&height=218&mode=crop',
    'Family Mental Health Information Series',
    'location-spuh-family-series-card'
);

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
        'acf_fc_layout' => 'content',
        'heading' => 'See our visiting hours',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'content' => $spuh_visiting_hours,
        'column_layout' => 'one_column',
        'background_type' => 'white',
        'text_width' => 'constrained',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Check our current visiting measures',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'content' => $spuh_visiting_measures_intro,
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
                'What should you do when you arrive for a visit?',
                $spuh_arrival_content,
                true
            ),
            matrix_seed_accordion_item(
                'Are children allowed to visit?',
                $spuh_children_visiting_content
            ),
            matrix_seed_accordion_item(
                'What infection control measures will I need to follow?',
                $spuh_infection_control_content
            ),
        ],
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'related_cards',
        'heading_tag' => 'h2',
        'heading' => 'More for families and supporters',
        'intro_text' => 'If you\'re supporting someone going through a mental health difficulty, we have a range of resources you might find helpful.',
        'cards' => [
            [
                'image' => $carers_card_image_id,
                'title' => 'Carers & Supporters Information Guide',
                'description' => '',
                'link' => [
                    'title' => 'Carers & Supporters Information Guide',
                    'url' => $carers_guide_url,
                    'target' => '',
                ],
            ],
            [
                'image' => $family_series_card_image_id,
                'title' => 'Family Mental Health Information Series',
                'description' => '',
                'link' => [
                    'title' => 'Family Mental Health Information Series',
                    'url' => $family_series_url,
                    'target' => '',
                ],
            ],
        ],
        'background_color' => '#FFFFFF',
        'columns' => '2',
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
