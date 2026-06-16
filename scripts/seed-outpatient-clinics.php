<?php

/**
 * Seed Outpatient Clinics CPT posts from stpatricks.ie outpatient clinic pages.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-outpatient-clinics.php
 */

require_once __DIR__ . '/lib/page-seed-conventions.php';
require_once __DIR__ . '/lib/outpatient-clinics-seed.php';

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

$base_media = 'https://www.stpatricks.ie';
$hero_about_id = matrix_seed_import_scraped_image(
    $base_media . '/media/2265/st-patricks-dean-clinic-2018.jpg',
    'St Patricks Dean Clinic',
    'outpatient-clinic-hero-about'
);
$hero_default_id = matrix_seed_import_scraped_image(
    $base_media . '/media/3467/mental-health-services-and-information.png',
    'Mental health services and information',
    'outpatient-clinic-hero-default'
);

if ($hero_default_id <= 0) {
    $hero_default_id = $hero_about_id;
}

$locations_url = home_url('/about-us/our-locations/');
$about_dean_url = matrix_seed_outpatient_clinic_url('about-the-dean-clinics');
$getting_help_url = home_url('/service-users-and-visitors/');

if (! function_exists('matrix_seed_outpatient_about_rows')) {
    function matrix_seed_outpatient_about_rows(int $hero_image_id): array
    {
        $rows = [
            matrix_seed_outpatient_hero_block([
                'heading' => 'Learn more about our Dean Clinics',
                'crumb_label' => 'About the Dean Clinics',
                'intro' => '<p>Through our Dean Clinics, St Patrick\'s Mental Health Services (SPMHS) offers a network of community-based clinics aiming to make mental health services accessible to every area of Ireland.</p>',
                'hero_image_id' => $hero_image_id,
            ]),
            matrix_seed_outpatient_useful_links_block(),
            matrix_seed_outpatient_content_block(
                'Community-based care',
                '<p>The Dean Clinics provide community-based services in multidisciplinary settings which are designed to meet the holistic mental health needs of both the individual service user and the community.</p>'
                . '<p>Each clinic\'s multidisciplinary team (MDT) delivers care based on the recovery principles of hope, personal responsibility, education, self-advocacy and support, ensuring that the service user\'s experience is hopeful and empowering.</p>'
                . '<p>Care is delivered in-person or remotely, depending on what option best meets the needs of the service user.</p>'
            ),
            matrix_seed_outpatient_content_block(
                'Mental health treatment',
                '<p>We treat a wide range of mental health difficulties in the Dean Clinics.</p>'
                . '<p>For adult service users, we treat addiction and substance abuse; anxiety; mood disorders, such as bipolar disorder and low mood; dual diagnosis; eating disorders; memory difficulties; Obsessive-Compulsive Disorder (OCD); psychosis; stress-related disorders; and more.</p>'
                . '<p>For adolescent service users, we provide treatment for anxiety; low mood; eating disorders; OCD; psychosis; stress-related disorders, and more.</p>'
            ),
            matrix_seed_outpatient_content_block(
                'Adult services',
                '<p>Adult services are available through Dean Clinics in Cork, Dublin, and Galway.</p>'
                . '<p>For adult service users, we offer a comprehensive multidisciplinary mental health assessment to determine if we can offer a recovery-focused, personalised treatment plan, which is prepared in collaboration with the service user. Treatment options include mental health reviews with the medical and nursing team, Cognitive Behavioural Therapy (CBT), occupational therapy (OT) and psychological services.</p>'
                . '<p>Adult service users in the Dean Clinics can also be referred to recovery-based day programmes and/or specialist treatment programmes at SPMHS where appropriate. Inpatient care or Homecare services when required can also be arranged through the Dean Clinics.</p>'
            ),
            matrix_seed_outpatient_content_block(
                'Adolescent services',
                '<p>We provide an MDT assessment and care and treatment through the Adolescent Dean Clinic in Cork and the Adolescent Dean Clinic in Dublin.</p>'
                . '<p>Treatment involves a recovery-focused, individualised care plan, which is developed in collaboration with the young person and their parents. This may include attending medical reviews, individual therapy or group programmes, with treatment approaches including psychology, CBT and OT.</p>'
                . '<p>Adolescent service users in the Dean Clinics may also be referred to Willow Grove Adolescent Unit (WGAU) for inpatient care or Homecare.</p>',
                'cream'
            ),
            matrix_seed_outpatient_content_block(
                'Physical Health Monitoring service',
                '<p>The Physical Health Monitoring Service is designed to support Dean Clinic service users in maintaining and optimising their physical health while receiving psychiatric treatment. Certain medications prescribed by psychiatrists may have an impact on physical health. This service provides regular monitoring and guidance to help identify, manage, and reduce potential side effects, thereby promoting overall wellbeing.</p>'
                . '<p><strong>Referral Process:</strong> Referral to the Physical Health Monitoring Service is made by your treating team within SPMHS. Direct self-referral or referral from external services is not available.</p>'
                . '<p><strong>Location:</strong> St Patrick\'s University Hospital, Dublin 8. Dean Clinic Room 8.</p>'
                . '<p><strong>Service Limitations:</strong> The Physical Health Monitoring Service does not replace your General Practitioner (GP) and is not a primary care service.</p>',
                'white'
            ),
            [
                'acf_fc_layout' => 'content_accordion',
                'layout_style' => 'default',
                'section_background' => '#FBFAF7',
                'panel_background' => '#FFFFFF',
                'open_panel_background' => 'linear-gradient(-42.77deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
                'items' => [
                    matrix_seed_outpatient_accordion_item(
                        'Dean Clinics for adult services',
                        '<p>Adult services are available in the Dean Clinics below:</p><ul><li>Dean Clinic Cork | City Gate, Mahon, County Cork</li><li>Dean Clinic Galway | Merchant\'s Road, Galway</li><li>Dean Clinic Lucan | Lucan, County Dublin</li><li>Dean Clinic St Patrick\'s | James\' Street, Dublin 8</li></ul>',
                        true
                    ),
                    matrix_seed_outpatient_accordion_item(
                        'Dean Clinics for adolescent services',
                        '<p>You will find adolescent services at the Adolescent Dean Clinic (St Patrick\'s University Hospital campus, James\' Street, Dublin 8) and the Dean Clinic Cork (City Gate, Mahon, County Cork).</p>'
                    ),
                    matrix_seed_outpatient_accordion_item(
                        'Associate Dean Clinics',
                        '<p>In addition to our Dean Clinics, we also provide a community based psychiatric assessment service in the following locations:</p><ul><li>Cork, County Cork</li><li>Kilkenny, County Kilkenny</li><li>Naas, County Kildare</li></ul>'
                    ),
                ],
            ],
            [
                'acf_fc_layout' => 'content',
                'heading' => 'Frequently asked questions',
                'heading_tag' => matrix_page_seed_heading(2),
                'accent_position' => 'below_heading',
                'intro_text' => 'Click on the questions you\'re interested in below to see the answers.',
                'content' => '',
                'column_layout' => 'one_column',
                'background_type' => 'white',
                'text_width' => 'constrained',
            ],
            [
                'acf_fc_layout' => 'content_accordion',
                'layout_style' => 'default',
                'section_background' => '#FFFFFF',
                'panel_background' => '#FBFAF7',
                'open_panel_background' => 'linear-gradient(-42.77deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
                'items' => [
                    matrix_seed_outpatient_accordion_item(
                        'How do I get referred to the Dean Clinics?',
                        '<p>If you are not currently a service user in SPMHS, the best place to start in accessing the Dean Clinics is with your GP. They can assess if you would benefit from mental healthcare, and, if they find this would help, they may send a referral to our Referral and Assessment Service.</p><p>If you are currently in our inpatient or Homecare services, you might be referred to the Dean Clinics for follow-up care by a member of your MDT.</p>',
                        true
                    ),
                    matrix_seed_outpatient_accordion_item(
                        'What happens after I am referred?',
                        '<p>If you are referred to the Dean Clinic by your GP, you will receive a mental health assessment when your referral is confirmed with your GP. The Dean Clinic team will confirm a date for the assessment with you in advance.</p><p>If you are receiving care in another service in SPMHS and are referred by the team, you will not need an assessment.</p>'
                    ),
                    matrix_seed_outpatient_accordion_item(
                        'Can I choose which Dean Clinic I attend?',
                        '<p>The Dean Clinic you are referred to depends on the services that would best fit your needs. Your location is always taken into consideration and the Dean Clinic closest to you will be offered in the first instance, except if you need to attend a specialist clinic not offered in your location.</p>'
                    ),
                    matrix_seed_outpatient_accordion_item(
                        'Are Dean Clinic services remote or in-person?',
                        '<p>Your Dean Clinic appointments may take place in-person or remotely through video calls on Microsoft Teams (MS Teams) or phone conferencing, depending on your treatment plan.</p>'
                    ),
                    matrix_seed_outpatient_accordion_item(
                        'How do I attend appointments remotely?',
                        '<p>Remote appointments can take place by video on MS Teams or phone. If your appointment is taking place on MS Teams, the notification will include a link for you to connect with your clinician at your scheduled appointment time.</p>'
                    ),
                    matrix_seed_outpatient_accordion_item(
                        'What are the fees for the Dean Clinics?',
                        '<p>SPMHS is an independent, not-for-profit mental health service. Our services, including those for the Dean Clinics, are funded through health insurance or directly by the service user themselves.</p><p>We are independent from state-run, or public, services, which means that the public medical card does not cover the cost of treatment in the Dean Clinics.</p>'
                    ),
                    matrix_seed_outpatient_accordion_item(
                        'Is there a cancellation fee?',
                        '<p>A cancellation fee is charged if you do not attend your appointment or if you give less than 48 hours notice of cancellation. If you do not attend your review appointment, you will be charged a fee of €75.</p>'
                    ),
                ],
            ],
            matrix_seed_outpatient_content_block(
                'Get in touch with your Dean Clinic',
                '<p><strong>Adolescent Dean Clinic</strong> — 01 249 3590</p>'
                . '<p><strong>Dean Clinic Cork</strong> — 01 249 3502</p>'
                . '<p><strong>Dean Clinic Galway</strong> — 091 513 540</p>'
                . '<p><strong>Dean Clinic Lucan</strong> — 01 249 3590</p>'
                . '<p><strong>Dean Clinic St Patrick\'s</strong> — 01 249 3590</p>',
                'cream'
            ),
        ];

        return array_merge($rows, matrix_seed_outpatient_footer_cta_rows());
    }
}

if (! function_exists('matrix_seed_outpatient_adolescent_rows')) {
    function matrix_seed_outpatient_adolescent_rows(int $hero_image_id): array
    {
        return array_merge(
            [
                matrix_seed_outpatient_hero_block([
                    'heading' => 'Adolescent Dean Clinic at St Patrick\'s',
                    'crumb_label' => 'Adolescent Dean Clinic',
                    'intro' => '<p>The Adolescent Dean Clinic at St Patrick\'s University Hospital (SPUH) is a dedicated adolescent mental health clinic in Dublin 8.</p>',
                    'hero_image_id' => $hero_image_id,
                ]),
                matrix_seed_outpatient_useful_links_block(),
                matrix_seed_outpatient_content_block(
                    'Responding to young people\'s needs',
                    '<p>The Adolescent Dean Clinic is part of the community-based Dean Clinic network from St Patrick\'s Mental Health Services (SPMHS) and is located in SPUH.</p>'
                    . '<p>The Adolescent Dean Clinic SPUH treats young people aged 12 to 17, and services are delivered either in-person or remotely, depending on what best meets the young person\'s needs.</p>'
                    . '<p>Through the clinic, we offer assessment and therapy, tailored to the young person\'s needs, for a wide range of mental health difficulties, including anxiety, bipolar disorder, eating disorders, depression, OCD, psychosis and stress-related disorders.</p>'
                    . '<p>We provide individual and therapeutic group programmes, based on the young person\'s needs. These include Cognitive Behavioural Therapy, occupational therapy, psychology, dietetics and family therapy.</p>'
                    . '<p>Access to inpatient or Homecare services in Willow Grove Adolescent Unit is also available to young people in the Adolescent Dean Clinic SPUH, where appropriate.</p>'
                ),
                matrix_seed_outpatient_content_block(
                    'Where to find the Adolescent Dean Clinic SPUH',
                    '<p>The Adolescent Dean Clinic SPUH is based in the SPUH campus at Steeven\'s Lane, James\' Street, Dublin 8. After arriving at the SPUH campus, the Adolescent Dean Clinic can be found by entering the main SPUH building and following signs for the clinic.</p>'
                    . '<p>There is a paid car park available at the campus. Heuston Station is approximately five minutes away by foot. Dublin Bus routes G1, G2, 13 and 123 stop along James\' Street.</p>',
                    'cream'
                ),
                [
                    'acf_fc_layout' => 'content_cta',
                    'heading_tag' => matrix_page_seed_heading(2),
                    'heading' => 'See more on our Dean Clinics',
                    'body' => '<p>Learn more about our community-based Dean Clinic network.</p>',
                    'button_link' => [
                        'title' => 'About the Dean Clinics',
                        'url' => matrix_seed_outpatient_clinic_url('about-the-dean-clinics'),
                        'target' => '',
                    ],
                    'background_type' => 'color',
                    'background_color' => '#E9E2F7',
                ],
                matrix_seed_outpatient_contact_cta('Contact the clinic', '01 249 3590'),
            ],
            matrix_seed_outpatient_footer_cta_rows()
        );
    }
}

if (! function_exists('matrix_seed_outpatient_cork_rows')) {
    function matrix_seed_outpatient_cork_rows(int $hero_image_id): array
    {
        return array_merge(
            [
                matrix_seed_outpatient_hero_block([
                    'heading' => 'Dean Clinic Cork',
                    'crumb_label' => 'Dean Clinic Cork',
                    'intro' => '<p>At the Dean Clinic Cork, we provide mental health assessments and individual and group therapy for adults and adolescents.</p>',
                    'hero_image_id' => $hero_image_id,
                ]),
                matrix_seed_outpatient_useful_links_block(),
                matrix_seed_outpatient_content_block(
                    'Adult services',
                    '<p>Through the clinic, we offer problem-focused, person-centred therapy for adults aged 18 and over, which caters to a range of mental health difficulties, such as anxiety, addiction and dual diagnosis, bipolar disorder, depression, OCD, psychosis and stress-related disorders.</p>'
                    . '<p>We provide individual and day care group programmes; educational and mental health awareness groups; and psycho-therapeutic groups on an individual needs basis.</p>'
                ),
                matrix_seed_outpatient_content_block(
                    'Adolescent services',
                    '<p>Adolescent service users in the Dean Clinic Cork may be referred to the Willow Grove Adolescent Unit in SPMHS for inpatient care or Homecare services, if needed. Young people can also be referred to day programmes or specialist therapies.</p>',
                    'cream'
                ),
                matrix_seed_outpatient_content_block(
                    'Where to find the Dean Clinic Cork',
                    '<p>The Dean Clinic Cork is located in Building 2000, City Gate, Mahon, County Cork.</p>'
                    . '<p>The 215 bus from the city centre is within walking distance of City Gate, Mahon. There is very limited free parking available – the maximum time allowed is three hours and clamping is in operation.</p>'
                ),
                matrix_seed_outpatient_contact_cta('Contact the clinic', '01 249 3502'),
            ],
            matrix_seed_outpatient_footer_cta_rows()
        );
    }
}

if (! function_exists('matrix_seed_outpatient_galway_rows')) {
    function matrix_seed_outpatient_galway_rows(int $hero_image_id): array
    {
        return array_merge(
            [
                matrix_seed_outpatient_hero_block([
                    'heading' => 'Dean Clinic Galway',
                    'crumb_label' => 'Dean Clinic Galway',
                    'intro' => '<p>At the Dean Clinic Galway, we provide mental health assessments and individual and group therapy to adults.</p>',
                    'hero_image_id' => $hero_image_id,
                ]),
                matrix_seed_outpatient_useful_links_block(),
                matrix_seed_outpatient_content_block(
                    'Services and therapies',
                    '<p>The Dean Clinic Galway delivers care at an outpatient or community-based level to adults aged over 18. Care is delivered in-person or remotely, depending on what is best for the person.</p>'
                    . '<p>We offer person-centred, problem-focused therapy for anxiety, addiction and dual diagnosis, bipolar disorder, depression, OCD, psychosis and stress-related disorders.</p>'
                    . '<p>We provide individual and day care group programmes, educational and mental health awareness groups, and psychotherapeutic groups on an individual needs basis, including CBT, occupational therapy, psychology groups and recovery-based groups.</p>'
                ),
                matrix_seed_outpatient_content_block(
                    'Where to find the Dean Clinic Galway',
                    '<p>The Dean Clinic Galway is located at Merchant\'s Square, Merchant\'s Road in Galway City.</p>'
                    . '<p>There is a paid car park available near the clinic. All bus routes to Eyre Square are within a 15 minute walk from the clinic. Galway train station is also a 10 minute walk from the clinic.</p>',
                    'cream'
                ),
                matrix_seed_outpatient_contact_cta('Contact the clinic', '091 513 540'),
            ],
            matrix_seed_outpatient_footer_cta_rows()
        );
    }
}

if (! function_exists('matrix_seed_outpatient_lucan_rows')) {
    function matrix_seed_outpatient_lucan_rows(int $hero_image_id): array
    {
        return array_merge(
            [
                matrix_seed_outpatient_hero_block([
                    'heading' => 'Dean Clinic Lucan',
                    'crumb_label' => 'Dean Clinic Lucan',
                    'intro' => '<p>We provide mental health assessments and individual and group therapy to adults through our Dean Clinic Lucan.</p>',
                    'hero_image_id' => $hero_image_id,
                ]),
                matrix_seed_outpatient_useful_links_block(),
                matrix_seed_outpatient_content_block(
                    'What we offer at the Dean Clinic Lucan',
                    '<p>At the Dean Clinic Lucan, we offer problem-focused, person-centred mental health assessments and therapy for adults aged 18 and over. We deliver care at an outpatient or community-based level, either in-person or remotely.</p>'
                    . '<p>Our services cater to anxiety, bipolar disorder, depression, OCD, psychosis and stress-related disorders. We can also offer access to specialist treatment programmes, day care programmes, or inpatient and Homecare services in SPMHS, where appropriate.</p>'
                    . '<p>We provide individual and group programmes including CBT, occupational therapy, psychology and recovery-based groups.</p>'
                ),
                matrix_seed_outpatient_content_block(
                    'Where to find the Dean Clinic Lucan',
                    '<p>The Dean Clinic Lucan is based at the second entrance to St Patrick\'s Hospital Lucan in Lucan.</p>'
                    . '<p>There is a car park available at the clinic. Dublin Bus routes C3 and C4, and local bus routes L54 and P29 pass nearby.</p>',
                    'cream'
                ),
                matrix_seed_outpatient_contact_cta('Contact the clinic', '01 249 3590'),
            ],
            matrix_seed_outpatient_footer_cta_rows()
        );
    }
}

if (! function_exists('matrix_seed_outpatient_st_patricks_rows')) {
    function matrix_seed_outpatient_st_patricks_rows(int $hero_image_id): array
    {
        return array_merge(
            [
                matrix_seed_outpatient_hero_block([
                    'heading' => 'Dean Clinic St Patrick\'s',
                    'crumb_label' => 'Dean Clinic St Patrick\'s',
                    'intro' => '<p>We provide mental health assessments, along with individual and group therapy and treatment, at our Dean Clinic St Patrick\'s in Dublin.</p>',
                    'hero_image_id' => $hero_image_id,
                ]),
                matrix_seed_outpatient_useful_links_block(),
                matrix_seed_outpatient_content_block(
                    'What the Dean Clinic St Patrick\'s offers',
                    '<p>At the Dean Clinic St Patrick\'s, we offer problem-focused, person-centred therapy for adults aged 18 and over for anxiety, addiction and dual diagnosis, bipolar disorder, depression, eating disorders, memory difficulties, OCD, psychosis and stress-related disorders.</p>'
                    . '<p>We also run an Early Detection of Psychosis clinic for young adults (aged 18 to 25) who may be at high risk or in the early stages of developing psychosis.</p>'
                    . '<p>Care is delivered in-person or remotely. We can provide access to day programmes, specialist treatment programmes, or inpatient or Homecare services where appropriate.</p>'
                ),
                matrix_seed_outpatient_content_block(
                    'Therapies and groups',
                    '<p>We provide individual and group programmes including addiction counselling, CBT, dietetics, family therapy group, MANTRA, occupational therapy, psychology and recovery-based groups.</p>',
                    'cream'
                ),
                matrix_seed_outpatient_content_block(
                    'Where to find the Dean Clinic St Patrick\'s',
                    '<p>The Dean Clinic St Patrick\'s is based on the campus of St Patrick\'s University Hospital (SPUH) on Steeven\'s Lane, James\' Street, Dublin 8.</p>'
                    . '<p>When you arrive at the SPUH campus, the Dean Clinic St Patrick\'s is the green building at the back of the car park, facing the main entrance gates.</p>'
                ),
                matrix_seed_outpatient_contact_cta('Contact the clinic', '01 249 3590'),
            ],
            matrix_seed_outpatient_footer_cta_rows()
        );
    }
}

if (! function_exists('matrix_seed_outpatient_associate_rows')) {
    function matrix_seed_outpatient_associate_rows(int $hero_image_id): array
    {
        return array_merge(
            [
                matrix_seed_outpatient_hero_block([
                    'heading' => 'Associate Dean Clinics',
                    'crumb_label' => 'Associate Dean Clinics',
                    'intro' => '<p>Learn more about our Associate Dean Clinics.</p>',
                    'hero_image_id' => $hero_image_id,
                ]),
                matrix_seed_outpatient_useful_links_block(),
                matrix_seed_outpatient_content_block(
                    'Community-based assessments',
                    '<p>In addition to our five Dean Clinics, we offer community-based mental health assessments through our Associate Dean Clinics. Services offered through the Associate Dean Clinics are open to adults aged 18 and over.</p>'
                    . '<p>Our Associate Dean Clinics are based in Kilkenny City in County Kilkenny and Naas in County Kildare.</p>'
                    . '<p>Services provided through the Associate Dean Clinics include mental health assessments and referral to St Patrick\'s Mental Health Services (SPMHS) for admission to inpatient care, Homecare services, or one of the psychotherapy or recovery-based day programmes.</p>'
                    . '<p>All referrals to these services are made through our Referral and Admissions Service. For more information, please call 01 249 3635.</p>'
                ),
                [
                    'acf_fc_layout' => 'content_cta',
                    'heading_tag' => matrix_page_seed_heading(2),
                    'heading' => 'See more on the Dean Clinics',
                    'body' => '<p>Learn more about our Dean Clinic network and how to access services.</p>',
                    'button_link' => [
                        'title' => 'About the Dean Clinics',
                        'url' => matrix_seed_outpatient_clinic_url('about-the-dean-clinics'),
                        'target' => '',
                    ],
                    'background_type' => 'color',
                    'background_color' => '#E9E2F7',
                ],
            ],
            matrix_seed_outpatient_footer_cta_rows()
        );
    }
}

if (! function_exists('matrix_seed_outpatient_how_to_access_rows')) {
    function matrix_seed_outpatient_how_to_access_rows(int $hero_image_id, string $locations_url): array
    {
        return array_merge(
            [
                matrix_seed_outpatient_hero_block([
                    'heading' => 'Accessing the Dean Clinics',
                    'crumb_label' => 'How to Access',
                    'intro' => '<p>The Dean Clinics provide mental health assessments and problem-focused, person-centred individual and group therapies for adults and adolescents.</p>',
                    'hero_image_id' => $hero_image_id,
                ]),
                matrix_seed_outpatient_useful_links_block(),
                matrix_seed_outpatient_content_block(
                    'Getting support',
                    '<p>If you are worried about your own mental health, your GP is a helpful first point of contact. Your GP will know and understand your physical and mental health history, so this is the first person we recommend you talk to.</p>'
                    . '<p>Together with your GP, you can decide on a course of action that best suits your needs.</p>'
                ),
                matrix_seed_outpatient_content_block(
                    'Accessing the Dean Clinics',
                    '<p>A GP referral is needed if you are not currently receiving care in SPMHS. Referrals are received by our Referral and Assessment Service and reviewed by a consultant psychiatrist to see if we have a service appropriate for your needs.</p>'
                    . '<p>If your referral suggests our Dean Clinic services may meet your needs, you may receive a Prompt Assessment of Needs (PAON), a free-of-charge assessment by an experienced mental health nurse through the Referral and Assessment Service.</p>'
                    . '<p>If you are receiving care through SPMHS\' inpatient, Homecare or day care services, you may be referred to the Dean Clinic for follow-up care by your multidisciplinary team.</p>'
                    . '<p>If you have any queries on accessing Dean Clinic services, please contact the Referral and Assessment Service on 01 249 3635, or you can contact our switchboard out of hours on 01 249 3200.</p>',
                    'cream'
                ),
                [
                    'acf_fc_layout' => 'content_cta',
                    'heading_tag' => matrix_page_seed_heading(2),
                    'heading' => 'Learn more about the Dean Clinics',
                    'body' => '<p>See our Dean Clinic locations and the different services available.</p>',
                    'button_link' => [
                        'title' => 'See more on the Dean Clinics',
                        'url' => matrix_seed_outpatient_clinic_url('about-the-dean-clinics'),
                        'target' => '',
                    ],
                    'background_type' => 'color',
                    'background_color' => '#CEF2EE',
                ],
                [
                    'acf_fc_layout' => 'content_cta',
                    'heading_tag' => matrix_page_seed_heading(2),
                    'heading' => 'Dean Clinic locations',
                    'body' => '<p>Find addresses, phone numbers and directions for all our hospitals and Dean Clinics.</p>',
                    'button_link' => [
                        'title' => 'Our locations',
                        'url' => $locations_url,
                        'target' => '',
                    ],
                    'background_type' => 'color',
                    'background_color' => '#E9E2F7',
                ],
            ],
            matrix_seed_outpatient_footer_cta_rows()
        );
    }
}

$pages = [
    [
        'seed_key' => 'outpatient-about-dean-clinics',
        'slug' => 'about-the-dean-clinics',
        'title' => 'About the Dean Clinics',
        'excerpt' => 'Through our Dean Clinics, SPMHS offers a network of community-based clinics across Ireland.',
        'featured_image_id' => $hero_about_id,
        'flexi_rows' => matrix_seed_outpatient_about_rows($hero_about_id),
    ],
    [
        'seed_key' => 'outpatient-adolescent-dean-clinic',
        'slug' => 'adolescent-dean-clinic',
        'title' => 'Adolescent Dean Clinic',
        'excerpt' => 'Dedicated adolescent mental health clinic at St Patrick\'s University Hospital, Dublin 8.',
        'featured_image_id' => $hero_default_id,
        'flexi_rows' => matrix_seed_outpatient_adolescent_rows($hero_default_id),
    ],
    [
        'seed_key' => 'outpatient-dean-clinic-cork',
        'slug' => 'dean-clinic-cork',
        'title' => 'Dean Clinic Cork',
        'excerpt' => 'Mental health assessments and therapy for adults and adolescents in Cork.',
        'featured_image_id' => $hero_default_id,
        'flexi_rows' => matrix_seed_outpatient_cork_rows($hero_default_id),
    ],
    [
        'seed_key' => 'outpatient-dean-clinic-galway',
        'slug' => 'dean-clinic-galway',
        'title' => 'Dean Clinic Galway',
        'excerpt' => 'Outpatient mental health assessments and therapy for adults in Galway.',
        'featured_image_id' => $hero_default_id,
        'flexi_rows' => matrix_seed_outpatient_galway_rows($hero_default_id),
    ],
    [
        'seed_key' => 'outpatient-dean-clinic-lucan',
        'slug' => 'dean-clinic-lucan',
        'title' => 'Dean Clinic Lucan',
        'excerpt' => 'Mental health assessments and therapy for adults in Lucan.',
        'featured_image_id' => $hero_default_id,
        'flexi_rows' => matrix_seed_outpatient_lucan_rows($hero_default_id),
    ],
    [
        'seed_key' => 'outpatient-dean-clinic-st-patricks',
        'slug' => 'dean-clinic-st-patricks',
        'title' => 'Dean Clinic St Patrick\'s',
        'excerpt' => 'Mental health assessments and therapy at our Dublin 8 Dean Clinic.',
        'featured_image_id' => $hero_default_id,
        'flexi_rows' => matrix_seed_outpatient_st_patricks_rows($hero_default_id),
    ],
    [
        'seed_key' => 'outpatient-associate-dean-clinics',
        'slug' => 'associate-dean-clinics',
        'title' => 'Associate Dean Clinics',
        'excerpt' => 'Community-based mental health assessments in Kilkenny and Naas.',
        'featured_image_id' => $hero_default_id,
        'flexi_rows' => matrix_seed_outpatient_associate_rows($hero_default_id),
    ],
    [
        'seed_key' => 'outpatient-how-to-access',
        'slug' => 'how-to-access',
        'title' => 'How to Access',
        'excerpt' => 'How to access Dean Clinic services through your GP or SPMHS care team.',
        'featured_image_id' => $hero_default_id,
        'flexi_rows' => matrix_seed_outpatient_how_to_access_rows($hero_default_id, $locations_url),
    ],
];

$seeded_ids = [];

foreach ($pages as $page) {
    $post_id = matrix_seed_ensure_outpatient_clinic($page);

    if ($post_id > 0) {
        $seeded_ids[] = $post_id;
    }
}

flush_rewrite_rules(false);

if (class_exists('WP_CLI')) {
    if (count($seeded_ids) === count($pages)) {
        WP_CLI::success(sprintf(
            'Seeded %d Outpatient Clinics posts. Example: %s',
            count($seeded_ids),
            get_permalink($seeded_ids[0])
        ));
    } else {
        WP_CLI::warning(sprintf(
            'Seeded %d of %d Outpatient Clinics posts.',
            count($seeded_ids),
            count($pages)
        ));
    }
}
