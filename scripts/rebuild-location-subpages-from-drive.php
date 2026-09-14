<?php

/**
 * Rebuild locations CPT sub-pages from Drive Library 3 Our locations docs.
 *
 * Sources: old/content/.../About Us/Our locations/Our locations/* sub page.docx
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/rebuild-location-subpages-from-drive.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

$p = static function (string $html): string {
    return '<p>' . $html . '</p>';
};

$ul = static function (array $items): string {
    $html = '<ul>';
    foreach ($items as $item) {
        $html .= '<li>' . $item . '</li>';
    }

    return $html . '</ul>';
};

$a = static function (string $url, string $label): string {
    return '<a href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
};

$home = untrailingslashit(home_url('/'));
$urls = [
    'directions' => $home . '/directions-and-parking/',
    'visiting' => $home . '/visiting-information/',
    'dean' => $home . '/what-we-offer/outpatient-care-dean-clinics/',
    'homecare' => $home . '/what-we-offer/st-patricks-at-home/',
    'day' => $home . '/what-we-offer/day-programmes/',
    'inpatient' => $home . '/inpatient-care/',
    'spuh' => $home . '/locations/st-patricks-university-hospital/',
    'lucan' => $home . '/locations/st-patricks-hospital-lucan/',
    'willow' => $home . '/locations/willow-grove-adolescent-unit/',
    'dean_spuh' => $home . '/locations/dean-clinic-st-patricks/',
    'dean_lucan' => $home . '/locations/dean-clinic-lucan/',
    'adolescent_dean' => $home . '/locations/adolescent-dean-clinic/',
];

$bg = static function (int $index): string {
    return ($index % 2 === 0) ? 'white' : 'cream';
};

$apply_page = static function (array $spec) use ($p, $a, $urls, $bg): void {
    $post_id = (int) $spec['id'];
    $post = get_post($post_id);
    if (! $post instanceof WP_Post || $post->post_type !== 'locations') {
        WP_CLI::warning("Skip missing location #{$post_id}");

        return;
    }

    $title = (string) $spec['title'];
    $hero_intro = (string) ($spec['hero_intro'] ?? '');
    $sections = $spec['sections'];
    $meta_title = (string) ($spec['meta_title'] ?? $title . ' | SPMHS');
    $meta_desc = (string) ($spec['meta_desc'] ?? '');
    $listing = (string) ($spec['listing_summary'] ?? '');

    $image_id = (int) get_post_thumbnail_id($post_id);
    if ($image_id <= 0) {
        $card = get_field('card_image', $post_id);
        $image_id = is_array($card) ? (int) ($card['ID'] ?? 0) : 0;
    }

    $flexi = [
        matrix_orlaith_hero_row($title, $hero_intro, $image_id),
    ];

    $i = 0;
    foreach ($sections as $section) {
        $heading = (string) ($section['heading'] ?? '');
        $html = (string) ($section['html'] ?? '');
        $extra = [];
        if (! empty($section['button'])) {
            $extra['primary_button'] = matrix_orlaith_button(
                (string) $section['button']['title'],
                (string) $section['button']['url']
            );
        }
        $flexi[] = matrix_orlaith_content_row($heading, $html, $bg($i), 0, 'image_left', $extra);
        $i++;
    }

    wp_update_post([
        'ID' => $post_id,
        'post_title' => $title,
    ]);

    if ($listing !== '') {
        update_field('listing_summary', $listing, $post_id);
    }

    update_post_meta($post_id, '_yoast_wpseo_title', $meta_title);
    if ($meta_desc !== '') {
        update_post_meta($post_id, '_yoast_wpseo_metadesc', $meta_desc);
    }

    update_field('flexible_content_blocks', $flexi, $post_id);

    WP_CLI::success(sprintf(
        'Rebuilt %s #%d → %s',
        $title,
        $post_id,
        get_permalink($post_id)
    ));

    foreach ((array) get_field('flexible_content_blocks', $post_id) as $idx => $row) {
        $layout = (string) ($row['acf_fc_layout'] ?? '?');
        $heading = wp_strip_all_tags((string) ($row['heading'] ?? ''));
        $btn = '';
        if (! empty($row['primary_button']['title'])) {
            $btn = ' | btn=' . $row['primary_button']['title'];
        }
        WP_CLI::log("  [{$idx}] {$layout} {$heading}{$btn}");
    }
};

$pages = [
    [
        'id' => 711,
        'title' => "St Patrick's University Hospital",
        'meta_title' => "St Patrick's University Hospital | SPMHS",
        'meta_desc' => "Learn more about St Patrick's University Hospital in Dublin, including inpatient care, facilities, visiting information and how to get here.",
        'listing_summary' => 'Find out more about our Dublin 8 hospital',
        'hero_intro' => $p("St Patrick's University Hospital (SPUH) is our main inpatient hospital in Dublin 8, providing specialist mental health care for adults."),
        'sections' => [
            [
                'heading' => 'About the hospital',
                'html' => $p('The hospital has 208 inpatient beds. Care is provided by multidisciplinary teams, with most service users staying in single rooms with ensuite facilities.')
                    . $p('We also provide outpatient care through our '
                        . $a($urls['dean_spuh'], "Dean Clinic St Patrick's")
                        . ' and '
                        . $a($urls['adolescent_dean'], 'Adolescent Dean Clinic')
                        . ', based in SPUH. '
                        . $a($urls['dean'], 'Find out more about the Dean Clinics here')
                        . '.'),
            ],
            [
                'heading' => 'Facilities and amenities',
                'html' => $p('SPUH has a range of facilities and spaces to support recovery and wellbeing, including:')
                    . $ul([
                        'Gardens, walking paths and outdoor exercise areas',
                        'A gym offering tailored exercise programmes',
                        'Art, craft, pottery and music rooms',
                        'A library and Information Centre',
                        'A range of beauty and hairdressing services',
                        'Recreational spaces, including a pool room',
                        'A restaurant and small shop',
                        'Complimentary Wi-Fi for service users and visitors',
                    ]),
            ],
            [
                'heading' => 'Visiting',
                'html' => $p('We have visiting hours in place every weekday and at the weekends in SPUH. '
                        . $a($urls['visiting'], 'See our visiting information')
                        . ' to learn more about visiting times and arrangements.')
                    . $p('Throughout the ground floor and garden of SPUH, there are tables and seating available for visitors to spend time with their loved ones. We also have a dedicated space, the Wishing Well Family Room, for visitors and service users to use when children are visiting, with a number of games and activities that everyone can enjoy together available here.'),
            ],
            [
                'heading' => 'Getting here',
                'html' => $p("SPUH is located on Steeven's Lane, Dublin 8. Heuston Station is less than a five-minute walk away, with Luas, train and bus services nearby."),
                'button' => [
                    'title' => 'Directions and parking',
                    'url' => $urls['directions'],
                ],
            ],
        ],
    ],
    [
        'id' => 712,
        'title' => "St Patrick's Hospital Lucan",
        'meta_title' => "St Patrick's Hospital Lucan | SPMHS",
        'meta_desc' => "Find information about St Patrick's Hospital Lucan, including inpatient care, facilities, visiting information and how to get here.",
        'listing_summary' => 'See our Lucan hospital',
        'hero_intro' => $p("St Patrick's Hospital Lucan provides inpatient mental health care for people experiencing diverse mental health difficulties. There are 52 inpatient beds."),
        'sections' => [
            [
                'heading' => 'About the hospital',
                'html' => $p('The hospital provides care through multidisciplinary teams, with accommodation mainly available in single rooms with ensuite facilities.')
                    . $p('Outpatient mental health services are also provided through our '
                        . $a($urls['dean_lucan'], 'Dean Clinic Lucan')
                        . ', which is based on the grounds of St Patrick\'s Hospital Lucan. '
                        . $a($urls['dean_lucan'], 'Find out more about the Dean Clinic Lucan here')
                        . '.'),
            ],
            [
                'heading' => 'Facilities and amenities',
                'html' => $p('The hospital is set within extensive grounds with gardens and outdoor spaces. Facilities include:')
                    . $ul([
                        'Gardens and outdoor exercise equipment',
                        'Physical activity supports',
                        'Recreational spaces',
                        'Dining facilities',
                        'Beauty services',
                        'Complimentary Wi-Fi',
                        'An oratory',
                    ])
                    . $p('Service users can also access additional occupational therapy and recreational facilities at '
                        . $a($urls['spuh'], "St Patrick's University Hospital (SPUH)")
                        . ' where appropriate.'),
            ],
            [
                'heading' => 'Visiting',
                'html' => $p("Daily visiting times are in place every weekday and at the weekends in St Patrick's Hospital Lucan.")
                    . $p('There are several spaces around the hospital building and its grounds for visits to take place. You can '
                        . $a($urls['visiting'], 'get detailed visiting information here')
                        . '.'),
            ],
            [
                'heading' => 'Getting here',
                'html' => $p("St Patrick's Hospital Lucan is located close to Lucan village, with easy access from the M50 and M4. Several bus routes stop close to the hospital, and free parking is available on the grounds."),
                'button' => [
                    'title' => 'Directions and parking',
                    'url' => $urls['directions'],
                ],
            ],
        ],
    ],
    [
        'id' => 2131,
        'title' => 'Willow Grove Adolescent Unit',
        'meta_title' => 'Willow Grove Adolescent Unit | SPMHS',
        'meta_desc' => 'Learn about Willow Grove Adolescent Unit, our specialist inpatient mental health service for young people aged 12 to 17.',
        'listing_summary' => 'Inpatient adolescent mental health services',
        'hero_intro' => $p('Willow Grove Adolescent Unit is our dedicated inpatient mental health service for young people aged 12 to 17.'),
        'sections' => [
            [
                'heading' => 'About Willow Grove',
                'html' => $p('The unit provides specialist care for young people experiencing a range of mental health difficulties, including anxiety, depression, eating disorders and early onset psychosis.')
                    . $p('Care is provided by a multidisciplinary team, with young people and their families supported throughout their treatment.')
                    . $p("Our inpatient centre is a 14-bed facility based in the campus of St Patrick's University Hospital (SPUH) in James' Street, Dublin 8. Young people receiving care as an inpatient have their own bedroom and ensuite bathroom."),
            ],
            [
                'heading' => 'Treatment and activities',
                'html' => $p('Young people take part in an individual programme based on their needs. This can include:')
                    . $ul([
                        'Individual therapy',
                        'Psychology and psychotherapy groups',
                        'Occupational therapy',
                        'Music, drama and activity groups',
                        'Gym and exercise activities',
                        'Education and support with returning to school',
                    ])
                    . $p('Young people receiving care through both inpatient and '
                        . $a($urls['homecare'], 'Homecare')
                        . ' services take part in the group programme. We also provide individual therapy and one-to-one support to young people in addition to the group programme.')
                    . $p('Education is also central to the programme; this is tailored for individual needs and includes plans for transition back to school.'),
            ],
            [
                'heading' => 'Facilities',
                'html' => $p('Willow Grove provides dedicated spaces for young people to take part in therapeutic, educational and recreational activities.'),
            ],
            [
                'heading' => 'Getting here',
                'html' => $p("Willow Grove is located on the grounds of St Patrick's University Hospital on James' Street, Dublin 8."),
                'button' => [
                    'title' => 'Directions and parking',
                    'url' => $urls['directions'],
                ],
            ],
        ],
    ],
    [
        'id' => 2135,
        'title' => "Dean Clinic St Patrick's",
        'meta_title' => "Dean Clinic St Patrick's | SPMHS",
        'meta_desc' => "Learn about Dean Clinic St Patrick's in Dublin, providing outpatient mental health assessment and treatment for adults aged 18 and over.",
        'listing_summary' => 'Outpatient care in Dublin 8',
        'hero_intro' => $p("Dean Clinic St Patrick's provides outpatient mental health assessment and treatment for adults aged 18 and over."),
        'sections' => [
            [
                'heading' => 'About the clinic',
                'html' => $p('The clinic supports people experiencing a range of mental health difficulties, including anxiety, depression, addiction, eating disorders, obsessive-compulsive disorder, psychosis and bipolar disorder.')
                    . $p('Appointments can take place in person or remotely, depending on individual needs.'),
            ],
            [
                'heading' => 'Treatment and support',
                'html' => $p('The clinic provides:')
                    . $ul([
                        'Individual therapy',
                        'Group programmes',
                        'Psychology',
                        'Cognitive Behavioural Therapy (CBT)',
                        'Occupational therapy',
                        'Family therapy',
                        'Addiction counselling',
                        'Dietetics',
                    ])
                    . $p('We can provide access to '
                        . $a($urls['day'], 'day programmes')
                        . ' and specialist treatment programmes, or '
                        . $a($urls['inpatient'], 'inpatient')
                        . ' or '
                        . $a($urls['homecare'], 'Homecare')
                        . ' services in St Patrick\'s Mental Health Services, where appropriate for the person.')
                    . $p('We also run an Early Detection of Psychosis clinic for young adults (aged 18 to 25) who may be at high risk or in the early stages of developing psychosis. The aim of the clinic is to help people at this early stage, when they have the best chance of recovery and potential to prevent further deterioration.'),
            ],
            [
                'heading' => 'Getting here',
                'html' => $p("Dean Clinic St Patrick's is located on the grounds of St Patrick's University Hospital in Dublin 8."),
                'button' => [
                    'title' => 'Directions and parking',
                    'url' => $urls['directions'],
                ],
            ],
        ],
    ],
    [
        'id' => 2134,
        'title' => 'Dean Clinic Lucan',
        'meta_title' => 'Dean Clinic Lucan | SPMHS',
        'meta_desc' => 'Learn about Dean Clinic Lucan, providing outpatient mental health assessment and treatment for adults aged 18 and over.',
        'listing_summary' => 'Outpatient care in Lucan',
        'hero_intro' => $p('Dean Clinic Lucan provides outpatient mental health assessment and treatment for adults aged 18 and over.'),
        'sections' => [
            [
                'heading' => 'About the clinic',
                'html' => $p('The clinic supports people experiencing a range of mental health difficulties, including anxiety, depression, bipolar disorder, obsessive-compulsive disorder, psychosis and stress-related difficulties.')
                    . $p('Appointments can take place in person or remotely, depending on individual needs.')
                    . $p("A GP referral is needed for people who are not currently receiving care in St Patrick's Mental Health Services (SPMHS), while service users in SPMHS' inpatient, Homecare or day care services can be referred by their multidisciplinary teams."),
            ],
            [
                'heading' => 'Treatment and support',
                'html' => $p('The clinic provides:')
                    . $ul([
                        'Individual therapy',
                        'Group programmes',
                        'Cognitive Behavioural Therapy (CBT)',
                        'Psychology',
                        'Occupational therapy',
                        'Recovery-based groups',
                    ])
                    . $p('In addition, we can offer access to specialist treatment programmes and '
                        . $a($urls['day'], 'day care programmes')
                        . ', or '
                        . $a($urls['inpatient'], 'inpatient')
                        . ' and '
                        . $a($urls['homecare'], 'Homecare')
                        . ' services in SPMHS, where this is right for the person.'),
            ],
            [
                'heading' => 'Getting here',
                'html' => $p("Dean Clinic Lucan is located at St Patrick's Hospital Lucan."),
                'button' => [
                    'title' => 'Directions and parking',
                    'url' => $urls['directions'],
                ],
            ],
        ],
    ],
    [
        'id' => 2132,
        'title' => 'Dean Clinic Cork',
        'meta_title' => 'Dean Clinic Cork | SPMHS',
        'meta_desc' => 'Learn about Dean Clinic Cork, providing outpatient mental health assessment and treatment for adults and young people.',
        'listing_summary' => 'Outpatient care in Cork',
        'hero_intro' => $p('Dean Clinic Cork provides outpatient mental health assessment and treatment for adults (aged 18 and over) and young people (aged 12 to 17).'),
        'sections' => [
            [
                'heading' => 'About the clinic',
                'html' => $p('The clinic provides support for a range of mental health difficulties, including anxiety, depression, addiction, bipolar disorder, obsessive-compulsive disorder and psychosis.')
                    . $p('Appointments can take place in person or remotely, depending on individual needs.')
                    . $p("Mental health assessments in the adult Dean Clinic Cork are mainly offered through a three-day assessment homecare admission (AHA) for a comprehensive assessment by the clinic's multidisciplinary team (MDT)."),
            ],
            [
                'heading' => 'Treatment and support',
                'html' => $p('The clinic provides:')
                    . $ul([
                        'Individual therapy',
                        'Group programmes',
                        'Cognitive Behavioural Therapy (CBT)',
                        'Psychology',
                        'Occupational therapy',
                        'Addiction counselling',
                        'Recovery-focused programmes',
                    ])
                    . $p('We can also provide access to '
                        . $a($urls['day'], 'day programmes')
                        . ', specialist treatment programmes, and '
                        . $a($urls['inpatient'], 'inpatient')
                        . ' or '
                        . $a($urls['homecare'], 'Homecare')
                        . " services in St Patrick's Mental Health Services (SPMHS), where appropriate for the person.")
                    . '<h3>Adolescent services</h3>'
                    . $p('Adolescent service users in the Dean Clinic Cork may be referred to the '
                        . $a($urls['willow'], 'Willow Grove Adolescent Unit')
                        . ' in SPMHS for inpatient care or Homecare services, if needed. Young people can also be referred to day programmes or specialist therapies, such as psychology groups and/or cognitive behavioural psychotherapy.'),
            ],
            [
                'heading' => 'Getting here',
                'html' => $p('Dean Clinic Cork is located at City Gate in Mahon, Cork.'),
                'button' => [
                    'title' => 'Directions and parking',
                    'url' => $urls['directions'],
                ],
            ],
        ],
    ],
    [
        'id' => 2133,
        'title' => 'Dean Clinic Galway',
        'meta_title' => 'Dean Clinic Galway | SPMHS',
        'meta_desc' => 'Learn about Dean Clinic Galway, providing outpatient mental health assessment and treatment for adults aged 18 and over.',
        'listing_summary' => 'Outpatient care in Galway',
        'hero_intro' => $p('Dean Clinic Galway provides outpatient mental health assessment and treatment for adults aged 18 and over.'),
        'sections' => [
            [
                'heading' => 'About the clinic',
                'html' => $p('The clinic supports people experiencing a range of mental health difficulties, including anxiety, depression, addiction, bipolar disorder, obsessive-compulsive disorder and psychosis.')
                    . $p('Appointments can take place in person or remotely, depending on individual needs.')
                    . $p("If you are not currently receiving care in St Patrick's Mental Health Services (SPMHS), you will need a GP referral to access the Dean Clinic Galway. If you are a service user in SPMHS' inpatient or Homecare services or day programmes, you may be referred to the Dean Clinic by your multidisciplinary team."),
            ],
            [
                'heading' => 'Treatment and support',
                'html' => $p('The clinic provides:')
                    . $ul([
                        'Individual therapy',
                        'Group programmes',
                        'Cognitive Behavioural Therapy (CBT)',
                        'Psychology',
                        'Occupational therapy',
                        'Recovery-focused programmes',
                    ])
                    . $p('We can also provide access to '
                        . $a($urls['inpatient'], 'inpatient')
                        . ' or '
                        . $a($urls['homecare'], 'Homecare')
                        . ' services, '
                        . $a($urls['day'], 'day programmes')
                        . ' and specialist treatment programmes in SPMHS where appropriate for the person.'),
            ],
            [
                'heading' => 'Getting here',
                'html' => $p("Dean Clinic Galway is located at Merchant's Square on Merchant's Road in Galway city."),
                'button' => [
                    'title' => 'Directions and parking',
                    'url' => $urls['directions'],
                ],
            ],
        ],
    ],
    [
        'id' => 2136,
        'title' => 'Adolescent Dean Clinic',
        'meta_title' => 'Adolescent Dean Clinic | SPMHS',
        'meta_desc' => 'Learn about the Adolescent Dean Clinic, providing outpatient mental health assessment and treatment for young people aged 12 to 17.',
        'listing_summary' => 'Outpatient care for young people aged 12 to 17',
        'hero_intro' => $p('The Adolescent Dean Clinic provides outpatient mental health assessment and treatment for young people aged 12 to 17.'),
        'sections' => [
            [
                'heading' => 'About the clinic',
                'html' => $p('The clinic supports young people experiencing a range of mental health difficulties, including anxiety, depression, eating disorders, obsessive-compulsive disorder, psychosis and bipolar disorder.')
                    . $p('Appointments can take place in person or remotely, depending on individual needs.')
                    . $p("A GP referral is needed for young people who are not currently receiving care in St Patrick's Mental Health Services (SPMHS). Young people in Willow Grove's inpatient or Homecare services may be referred to the Dean Clinic by their multidisciplinary team."),
            ],
            [
                'heading' => 'Treatment and support',
                'html' => $p("We provide individual and therapeutic group programmes, based on the young person's needs. These include:")
                    . $ul([
                        'Individual therapy',
                        'Therapeutic groups',
                        'Psychology',
                        'Cognitive Behavioural Therapy (CBT)',
                        'Occupational therapy',
                        'Dietetics',
                        'Family therapy',
                    ])
                    . $p('Where appropriate, young people can also access inpatient or Homecare services through '
                        . $a($urls['willow'], 'Willow Grove Adolescent Unit')
                        . '.'),
            ],
            [
                'heading' => 'Getting here',
                'html' => $p("The Adolescent Dean Clinic is located within the St Patrick's University Hospital campus in Dublin 8."),
                'button' => [
                    'title' => 'Directions and parking',
                    'url' => $urls['directions'],
                ],
            ],
        ],
    ],
];

foreach ($pages as $page) {
    $apply_page($page);
}
