<?php

/**
 * Seed Outpatient Care - Dean Clinics (page 222) with content from
 * stpatricks.ie/care-treatment/outpatient-clinics/about-the-dean-clinics.
 * Preserves the existing 7-block layout and only updates copy, cards, and media.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-outpatient-care-dean-clinics.php
 */

require_once __DIR__ . '/lib/page-seed-conventions.php';
require_once __DIR__ . '/lib/outpatient-clinics-seed.php';

$post_id = (int) (get_page_by_path('what-we-offer/outpatient-care-dean-clinics')?->ID ?? 0);

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at what-we-offer/outpatient-care-dean-clinics.');
    }

    exit(1);
}

if (! function_exists('matrix_seed_dean_import_image')) {
    function matrix_seed_dean_import_image(string $url, string $title, string $cache_key): int
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
        $filename = $path ? basename((string) $path) : 'dean-clinic-image.jpg';

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

if (! function_exists('matrix_seed_dean_build_image_field')) {
    function matrix_seed_dean_build_image_field(int $attachment_id, string $alt): array
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

if (! function_exists('matrix_seed_dean_url')) {
    function matrix_seed_dean_url(string $path): string
    {
        $path = trim($path, '/');
        $post_id = url_to_postid(home_url('/' . $path . '/'));

        if ($post_id > 0) {
            return (string) get_permalink($post_id);
        }

        $page_id = matrix_seed_resolve_page_id_by_path($path);

        if ($page_id > 0) {
            return (string) get_permalink($page_id);
        }

        return home_url('/' . $path . '/');
    }
}

if (! function_exists('matrix_seed_dean_link')) {
    function matrix_seed_dean_link(string $label, string $path): string
    {
        return '<a href="' . esc_url(matrix_seed_dean_url($path)) . '">' . esc_html($label) . '</a>';
    }
}

if (! function_exists('matrix_seed_dean_location_card')) {
    /**
     * @return array<string, mixed>
     */
    function matrix_seed_dean_location_card(string $title, string $outpatient_slug, string $location_slug, int $fallback_image_id): array
    {
        $url = matrix_seed_dean_url('outpatient-clinics/' . $outpatient_slug);
        $location = get_posts([
            'post_type' => 'locations',
            'name' => $location_slug,
            'post_status' => 'publish',
            'posts_per_page' => 1,
        ]);
        $image_id = $fallback_image_id;

        if ($location !== []) {
            $thumb = (int) get_post_thumbnail_id((int) $location[0]->ID);

            if ($thumb > 0) {
                $image_id = $thumb;
            }
        }

        return [
            'title' => $title,
            'image' => matrix_seed_dean_build_image_field($image_id, $title),
            'link' => [
                'title' => $title,
                'url' => $url,
                'target' => '',
            ],
        ];
    }
}

$healthcare_url = matrix_seed_dean_url('healthcare-professionals');
$refer_outpatient_url = matrix_seed_dean_url('make-a-referral/refer-for-outpatient-care');
$locations_url = matrix_seed_dean_url('about-us/our-locations');
$attending_url = matrix_seed_dean_url('service-users-and-visitors/attending-a-dean-clinic');
$insurance_url = matrix_seed_dean_url('getting-help/insurance-information');

$hero_image_id = matrix_seed_dean_import_image(
    'https://www.stpatricks.ie/media/2265/st-patricks-dean-clinic-2018.jpg',
    'St Patricks Dean Clinic',
    'dean-clinics-landing-hero'
);
$video_poster_id = matrix_seed_dean_import_image(
    'https://www.stpatricks.ie/media/3467/mental-health-services-and-information.png',
    'Dean Clinics remote appointments',
    'dean-clinics-video-poster'
);

if ($video_poster_id <= 0) {
    $video_poster_id = $hero_image_id;
}

$hero_intro = 'Through our Dean Clinics, St Patrick\'s Mental Health Services (SPMHS) offers a network of community-based clinics aiming to make mental health services accessible to every area of Ireland.';

$community_care = '<p>The Dean Clinics provide community-based services in multidisciplinary settings which are designed to meet the holistic mental health needs of both the individual service user and the community.</p>'
    . '<p>Each clinic\'s multidisciplinary team (MDT) delivers care based on the recovery principles of hope, personal responsibility, education, self-advocacy and support, ensuring that the service user\'s experience is hopeful and empowering.</p>'
    . '<p>Care is delivered in-person or remotely, depending on what option best meets the needs of the service user.</p>';

$faq_intro = '<p>Click on the questions you\'re interested in below to see the answers.</p>'
    . $community_care;

$faq_items = [
  matrix_seed_outpatient_accordion_item(
      'How do I get referred to the Dean Clinics?',
      '<p>If you are not currently a service user in SPMHS, the best place to start in accessing the Dean Clinics is with your GP. They can assess if you would benefit from mental healthcare, and, if they find this would help, they may send a referral to our Referral and Assessment Service. One of our consultant psychiatrists will review this referral to see if we have a service that would suit your needs. This might include treatment at a Dean Clinic.</p>'
          . '<p>If you are currently in our inpatient or Homecare services, you might be referred to the Dean Clinics for follow-up care by a member of your MDT.</p>',
      true
  ),
  matrix_seed_outpatient_accordion_item(
      'What happens after I am referred?',
      '<p>If you are referred to the Dean Clinic by your GP, you will receive a mental health assessment when your referral is confirmed with your GP. The Dean Clinic team will confirm a date for the assessment with you in advance. Once your assessment is confirmed, you can reschedule the date for this if you need to; please give up to 72 hours notice for this to avoid a cancellation fee.</p>'
          . '<p>Please note that first assessments are for initial psychiatric assessment and not for the purpose of medico-legal reports. Medico-legal reports will not be provided.</p>'
          . '<p>If you are receiving care in another service in SPMHS and are referred by the team, you will not need an assessment. You will have previously had a mental health assessment on your admission, and the team referring you will be aware of your mental health needs.</p>'
  ),
  matrix_seed_outpatient_accordion_item(
      'Is there anything I should have ready for my assessment?',
      '<p>Your GP should ensure that they have sent full blood count (FBC), thyroid function test (TFT), renal and liver function test (LFT) blood results by post or fax ahead of the assessment, as these will be reviewed as part of the assessment.</p>'
          . '<p>We recommend that you allow up to three hours for the assessment, as you will be seen by different clinicians as part of this. If you are attending the assessment in-person, we recommend that you arrive 15 minutes ahead of the booking time to complete any necessary documentation.</p>'
          . '<p>If you have health insurance, please have your insurance details available (health insurer name, plan, and policy number) also.</p>'
  ),
  matrix_seed_outpatient_accordion_item(
      'Can I choose which Dean Clinic I attend?',
      '<p>The Dean Clinic you are referred to depends on the services that would best fit your needs; for example, if you need to attend a specialist service that is only delivered in one clinic, then you will be referred to that specific clinic. Your location is always taken into consideration and the Dean Clinic closest to you will be offered in the first instance, except if you need to attend a specialist clinic not offered in your location.</p>'
  ),
  matrix_seed_outpatient_accordion_item(
      'Are Dean Clinic services remote or in-person?',
      '<p>Your Dean Clinic appointments may take place in-person or remotely through video calls on Microsoft Teams (MS Teams) or phone conferencing, depending on your treatment plan.</p>'
  ),
  matrix_seed_outpatient_accordion_item(
      'How do I attend appointments remotely?',
      '<p>Remote appointments can take place by video on MS Teams or phone. If you do not have access to a computer or laptop, you can use a smartphone or tablet for your appointments.</p>'
          . '<p>You will receive a reminder of your appointment by text in advance of your appointment. You will also receive a reminder email if you are signed up to ' . matrix_seed_dean_link('Your Portal', 'about-your-portal') . ', our online patient platform.</p>'
          . '<p>If your appointment is taking place on MS Teams, the notification will include a link for you to connect with your clinician by video at your scheduled appointment time. You can also access the video link in Your Portal if you are registered to use this.</p>'
          . '<p>If your appointment is taking place by telephone, your clinician will contact you at your scheduled appointment time. Please allow 30 minutes before and after your appointment time to be contacted.</p>'
  ),
];

$location_cards = [
    matrix_seed_dean_location_card('Dean Clinic Cork', 'dean-clinic-cork', 'dean-clinic-cork', $hero_image_id),
    matrix_seed_dean_location_card('Dean Clinic Galway', 'dean-clinic-galway', 'dean-clinic-galway', $hero_image_id),
    matrix_seed_dean_location_card('Dean Clinic Lucan', 'dean-clinic-lucan', 'dean-clinic-lucan', $hero_image_id),
    matrix_seed_dean_location_card('Dean Clinic St Patrick\'s', 'dean-clinic-st-patricks', 'dean-clinic-st-patricks', $hero_image_id),
    matrix_seed_dean_location_card('Adolescent Dean Clinic', 'adolescent-dean-clinic', 'adolescent-dean-clinic', $hero_image_id),
];

$testimonial_items = [
    [
        'quote' => '<p>The Dean Clinic team helped me understand my treatment plan and feel supported throughout my recovery journey in the community.</p>',
        'author_name' => 'Service User',
        'author_title' => 'Dean Clinic',
        'card_tone' => 'lavender',
    ],
    [
        'quote' => '<p>Being able to attend some appointments remotely made it much easier to fit my care around family and work commitments.</p>',
        'author_name' => 'Service User',
        'author_title' => 'Dean Clinic',
        'card_tone' => 'mauve',
    ],
    [
        'quote' => '<p>The multidisciplinary team took time to explain each step of my assessment and made me feel welcome from my first visit.</p>',
        'author_name' => 'Service User',
        'author_title' => 'Dean Clinic',
        'card_tone' => 'lavender',
    ],
    [
        'quote' => '<p>Community-based care meant I could stay connected to my local area while receiving specialist mental health support.</p>',
        'author_name' => 'Service User',
        'author_title' => 'Dean Clinic',
        'card_tone' => 'mauve',
    ],
    [
        'quote' => '<p>My clinicians worked with me to build a recovery-focused plan that felt personal and achievable.</p>',
        'author_name' => 'Service User',
        'author_title' => 'Dean Clinic',
        'card_tone' => 'lavender',
    ],
];

$video_slides = [
    [
        'poster_image' => matrix_seed_dean_build_image_field($video_poster_id, 'Dean Clinics remote appointments'),
        'video_source_type' => 'embed_url',
        'video_embed_url' => 'https://www.youtube.com/watch?v=mN0Qyhix71E',
        'caption' => '<p>Dean Clinic appointments may take place in-person or remotely through video or phone, depending on your treatment plan. Visit our ' . matrix_seed_dean_link('attending a Dean Clinic', 'service-users-and-visitors/attending-a-dean-clinic') . ' page for practical guidance.</p>',
        'cta_link' => [
            'title' => 'About the Dean Clinics',
            'url' => matrix_seed_dean_url('outpatient-clinics/about-the-dean-clinics'),
            'target' => '',
        ],
    ],
];

$healthcare_cta_body = '<p>GPs and referrers can find information on referring patients to our Dean Clinics, including referral forms and assessment pathways.</p>'
    . '<p>You can also read our ' . matrix_seed_dean_link('insurance and funding information', 'getting-help/insurance-information') . ' for service users attending Dean Clinic appointments.</p>';

$rows = get_field('flexible_content_blocks', $post_id);

if (! is_array($rows) || $rows === []) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Outpatient Care - Dean Clinics page has no flexible content blocks to update.');
    }

    exit(1);
}

foreach ($rows as &$row) {
    $layout = $row['acf_fc_layout'] ?? '';

    if ($layout === 'hero_with_breadcrumbs') {
        $row['current_crumb_label'] = 'Outpatient Care - Dean Clinics';
        $row['heading'] = 'Outpatient Care - Dean Clinics';
        $row['content'] = '<p>' . esc_html($hero_intro) . '</p>';

        if ($hero_image_id > 0) {
            $row['hero_image'] = $hero_image_id;
        }

        $row['primary_button'] = [
            'title' => 'Refer for outpatient care',
            'url' => $refer_outpatient_url,
            'target' => '',
        ];
    }

    if ($layout === 'locations_grid') {
        $row['heading'] = 'Where you will receive care';
        $row['cards'] = $location_cards;
        $row['footer_button_link'] = [
            'title' => 'View all locations',
            'url' => $locations_url,
            'target' => '',
        ];
    }

    if ($layout === 'content' && ($row['heading'] ?? '') === 'Frequently Asked Questions') {
        $row['content'] = $faq_intro;
    }

    if ($layout === 'content_accordion') {
        $row['items'] = $faq_items;
    }

    if ($layout === 'testimonials') {
        $row['manual_items'] = $testimonial_items;
    }

    if ($layout === 'video_showcase') {
        $row['heading'] = 'Remote and in-person appointments';
        $row['intro'] = '<p>Dean Clinic care is delivered in-person or remotely, depending on what best meets your needs. Learn more about how our community clinics support recovery across Ireland.</p>';
        $row['slides'] = $video_slides;
    }

    if ($layout === 'content_cta') {
        $row['heading'] = 'Are you a healthcare professional?';
        $row['body'] = $healthcare_cta_body;
        $row['button_link'] = [
            'title' => 'Healthcare professionals',
            'url' => $healthcare_url,
            'target' => '',
        ];
    }
}
unset($row);

update_field('hero_content_blocks', [], $post_id);
update_field('flexible_content_blocks', $rows, $post_id);
update_post_meta($post_id, '_matrix_seed_key', 'outpatient-care-dean-clinics-content');

if (class_exists('WP_CLI')) {
    WP_CLI::success(sprintf(
        'Updated Outpatient Care - Dean Clinics page (%d) content across %d flexi blocks.',
        $post_id,
        count($rows)
    ));
}
