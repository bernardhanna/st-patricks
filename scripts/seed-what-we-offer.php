<?php

/**
 * Seed What We Offer landing page (page 214) with real content.
 * Preserves the existing 4-block layout and only updates copy, links, and FAQs.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-what-we-offer.php
 */

require_once __DIR__ . '/lib/page-seed-conventions.php';

$post_id = (int) (get_page_by_path('what-we-offer')?->ID ?? 0);

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at what-we-offer.');
    }

    exit(1);
}

if (! function_exists('matrix_seed_wwo_url')) {
    function matrix_seed_wwo_url(string $path): string
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

if (! function_exists('matrix_seed_wwo_link')) {
    /**
     * @return array{title: string, url: string, target: string}
     */
    function matrix_seed_wwo_link(string $title, string $path): array
    {
        return [
            'title' => $title,
            'url' => matrix_seed_wwo_url($path),
            'target' => '',
        ];
    }
}

if (! function_exists('matrix_seed_wwo_html_link')) {
    function matrix_seed_wwo_html_link(string $path, string $label): string
    {
        return '<a href="' . esc_url(matrix_seed_wwo_url($path)) . '">' . esc_html($label) . '</a>';
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
$contact_url = matrix_seed_wwo_url('contact-us');
$getting_help_url = matrix_seed_wwo_url('getting-help');

$hero_intro = 'At St Patrick\'s Mental Health Services (SPMHS), we provide inpatient hospital care, home-based mental health services, community outpatient care through our Dean Clinics, and structured day programmes. Explore our services below to find the right support for you.';

$hero_content = sprintf(
    '<p>%s</p><p><a class="btn inline-flex min-h-[36px] items-center justify-center rounded-[6px] bg-[#024B79] px-3 py-2 text-[14px] font-medium leading-[24px] text-white no-underline" href="%s">Contact us</a></p>',
    esc_html($hero_intro),
    esc_url($contact_url)
);

$section_intro = 'At St Patrick\'s Mental Health Services (SPMHS), we provide community and outpatient care through our Dean Clinics and day patient services through our Wellness and Recovery Centre.';

$service_content = [
    'Inpatient Care' => [
        'description' => '<p>We offer a multidisciplinary inpatient service through our three approved centres: St Patrick\'s University Hospital, St Patrick\'s Hospital Lucan, and Willow Grove Adolescent Unit.</p>',
        'link' => matrix_seed_wwo_link('Inpatient Care', 'inpatient-care'),
    ],
    "St Patrick's at Home" => [
        'description' => '<p>We provide a Homecare Service and remote access to our services through phone, video or online channels, supporting people in their own homes where appropriate.</p>',
        'link' => matrix_seed_wwo_link("St Patrick's at Home", 'what-we-offer/st-patricks-at-home'),
    ],
    'Outpatient Care - Dean Clinics' => [
        'description' => '<p>We provide community and outpatient care through our Dean Clinics across Ireland, offering assessment, treatment and follow-up support.</p>',
        'link' => matrix_seed_wwo_link('Outpatient Care - Dean Clinics', 'what-we-offer/outpatient-care-dean-clinics'),
    ],
    'Day Programmes' => [
        'description' => '<p>Day patient services are delivered through our Wellness and Recovery Centre, with a wide range of structured programmes to support recovery.</p>',
        'link' => matrix_seed_wwo_link('Day Programmes', 'what-we-offer/day-programmes'),
    ],
];

$faq_items = [
    [
        'title' => 'How do I get referred for an adult inpatient stay?',
        'content' => '<p>To receive ' . matrix_seed_wwo_html_link('inpatient-hospital-care', 'inpatient care') . ' here in SPMHS, in most cases, you will need to arrange an appointment with your GP. If, following assessment, your GP finds that you would benefit from one of our services, they can refer you to SPMHS.</p>'
            . '<p>In some circumstances, if you are under the care of a psychiatrist outside of SPMHS, they may refer you to SPMHS. If you are already receiving care from a SPMHS psychiatrist, they may also refer you for an inpatient admission.</p>'
            . '<p>When we receive your referral, a member of our Referral and Assessment Service team will contact you to discuss your referral. Your referral will then be assessed by a consultant psychiatrist in SPMHS to identify the most appropriate service for you.</p>',
    ],
    [
        'title' => 'How do I get referred for the adult Homecare service?',
        'content' => '<p>Through our ' . matrix_seed_wwo_html_link('care-treatment/homecare-service', 'Homecare service') . ', we deliver high quality mental healthcare to service users in their own homes.</p>'
            . '<p>To be admitted to the adult Homecare service, in general, you would first need to be assessed by your GP who can then refer you to SPMHS if they feel you need care and treatment for your mental health.</p>'
            . '<p>A member of our Referral and Assessment Service team will contact you after we receive your referral to discuss this with you.</p>',
    ],
    [
        'title' => 'How do I get referred for a Dean Clinic (outpatient) assessment?',
        'content' => '<p>The ' . matrix_seed_wwo_html_link('outpatient-clinics/about-the-dean-clinics', 'Dean Clinics') . ' are our community or outpatient mental health clinics. The Dean Clinics offer mental health assessment, outpatient appointments, and follow-up care after inpatient or Homecare admission.</p>'
            . '<p>To access our Dean Clinics, you generally first need to visit your GP, who can refer you to SPMHS if their assessment finds that you would benefit from mental healthcare.</p>',
    ],
    [
        'title' => 'How do I get a referral for day programmes?',
        'content' => '<p>We have a wide range of day programmes; you can ' . matrix_seed_wwo_html_link('programmes-therapies', 'get information on our day programmes here') . '.</p>'
            . '<p>Most of our day programmes can be accessed if you are already receiving inpatient or Homecare services in SPMHS. We also accept direct referrals from GPs and other mental health services into some of our day programmes.</p>'
            . '<p>If you are not currently receiving care in SPMHS, you will need a referral from your GP. Please note that our day programmes are open to adults over 18 only.</p>',
    ],
    [
        'title' => 'What are the costs of treatment at SPMHS?',
        'content' => '<p>We are an independent, not-for-profit mental health service. Our services are funded through health insurance or directly funded by the service user.</p>'
            . '<p>If you have health insurance, we advise you to contact your insurance provider with your policy number to clarify your cover. Our service users can also self-fund care.</p>'
            . '<p>As we are independent from public services, the public medical card does not cover the cost of treatment in SPMHS. You can ' . matrix_seed_wwo_html_link('getting-help/insurance-information', 'see our funding information here') . '.</p>',
    ],
    [
        'title' => 'How can referrals for adolescent services be made?',
        'content' => '<p>We provide a range of mental health services for young people aged under 18. Our Willow Grove Adolescent Unit offers ' . matrix_seed_wwo_html_link('adolescent-mental-health-services', 'inpatient and Homecare services to young people') . ' between the ages of 12 and 17.</p>'
            . '<p>Through our ' . matrix_seed_wwo_html_link('outpatient-clinics/adolescent-dean-clinic', 'Adolescent Dean Clinic') . ' and our ' . matrix_seed_wwo_html_link('outpatient-clinics/dean-clinic-cork', 'Dean Clinic Cork') . ', we also offer assessment and therapy tailored to the young person\'s needs.</p>'
            . '<p>Referrals to our adolescent services can be made by GPs, CAMHS teams, paediatric or general hospitals, or by psychiatrists outside of SPMHS.</p>',
    ],
];

$faq_ids = [];

foreach ($faq_items as $index => $faq_item) {
    $faq_ids[] = matrix_seed_ensure_faq_post(
        $faq_item['title'],
        $faq_item['content'],
        'what-we-offer-faq-' . ($index + 1)
    );
}

$faq_ids = array_values(array_filter(array_map('intval', $faq_ids)));

$rows = get_field('flexible_content_blocks', $post_id);

if (! is_array($rows) || $rows === []) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('What We Offer page has no flexible content blocks to update.');
    }

    exit(1);
}

foreach ($rows as &$row) {
    $layout = $row['acf_fc_layout'] ?? '';

    if ($layout === 'hero_with_breadcrumbs') {
        $row['content'] = $hero_content;
    }

    if ($layout === 'what_we_offer') {
        $row['heading'] = 'What we offer';
        $row['intro_text'] = $section_intro;

        if (is_array($row['services'] ?? null)) {
            foreach ($row['services'] as &$service) {
                $title = (string) ($service['service_title'] ?? '');

                if (! isset($service_content[$title])) {
                    continue;
                }

                $service['service_description'] = $service_content[$title]['description'];
                $service['service_link'] = $service_content[$title]['link'];
            }
            unset($service);
        }
    }

    if ($layout === 'faqs' && $faq_ids !== []) {
        $row['selected_faqs'] = $faq_ids;
    }
}
unset($row);

update_field('flexible_content_blocks', $rows, $post_id);
update_post_meta($post_id, '_matrix_seed_key', 'what-we-offer-content');

if (class_exists('WP_CLI')) {
    WP_CLI::success(sprintf(
        'Updated What We Offer page (%d) content across %d flexi blocks.',
        $post_id,
        count($rows)
    ));
}
