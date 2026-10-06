<?php

/**
 * Apply leftover About Us items plus next-batch snags.
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/fix-next-batch-snags.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

$p = static function (string $html): string {
    return '<p>' . $html . '</p>';
};
$a = static function (string $url, string $label): string {
    return '<a href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
};
$permalink = static function (string $path): string {
    $page = get_page_by_path($path);
    if ($page instanceof WP_Post) {
        return (string) get_permalink($page);
    }

    return matrix_orlaith_permalink($path);
};
$save = static function (int $post_id, array $rows, string $label): void {
    update_field('flexible_content_blocks', $rows, $post_id);
    WP_CLI::success($label . ' #' . $post_id);
};
$hero_image_id = static function (array $hero): int {
    $image = $hero['hero_image'] ?? '';
    if (is_array($image)) {
        return (int) ($image['ID'] ?? $image['id'] ?? 0);
    }

    return (int) $image;
};

$careers_url = $permalink('about-us/careers');
$refer_inpatient_url = $permalink('healthcare-professionals/refer-an-adult-for-inpatient-care');
$stay_adult_url = $permalink('service-users-and-visitors/your-stay-in-hospital-as-an-adult');
$stay_adolescent_url = $permalink('service-users-and-visitors/your-care-with-willow-grove/your-stay-in-hospital-as-an-adolescent');
$directions_url = $permalink('directions-and-parking');
$faqs_url = $permalink('service-users-and-visitors/frequently-asked-questions-faqs');
$referrals_url = $permalink('healthcare-professionals');
$homecare_url = $permalink('what-we-offer/st-patricks-at-home');
$inpatient_url = $permalink('inpatient-care');
$day_url = $permalink('what-we-offer/day-programmes');
$dean_url = $permalink('what-we-offer/outpatient-care-dean-clinics');
$lucan_url = $permalink('locations/st-patricks-hospital-lucan');
$advocacy_url = $permalink('advocacy-centre');
$academic_url = $permalink('academic-institute');
$training_url = $permalink('healthcare-professionals/training-centre');

// --- Part A: How to apply title + nursing careers CTA ---
$apply_id = 250;
wp_update_post([
    'ID' => $apply_id,
    'post_title' => 'How to apply',
]);

$apply = get_field('flexible_content_blocks', $apply_id);
if (is_array($apply)) {
    foreach ($apply as $index => $row) {
        if (($row['acf_fc_layout'] ?? '') === 'hero_with_breadcrumbs') {
            $apply[$index]['heading'] = 'How to apply';
            $apply[$index]['current_crumb_label'] = 'How to apply';
        }

        if (($row['acf_fc_layout'] ?? '') !== 'content') {
            continue;
        }

        $content = (string) ($row['content'] ?? '');
        if (! str_contains($content, 'Suggested CTA') && ! str_contains($content, 'Explore current vacancies')) {
            continue;
        }

        $content = preg_replace(
            '#<p>\s*<strong>\s*Suggested CTA.*?</p>\s*<p>.*?St Patrick’s Mental Health Services\.</p>#is',
            '',
            $content
        ) ?? $content;
        $content = preg_replace('#Suggested CTA#i', '', $content) ?? $content;
        $content = preg_replace('#<p>\s*<strong>\s*</strong>\s*</p>#i', '', $content) ?? $content;
        $apply[$index]['content'] = $content;
        $apply[$index]['primary_button'] = matrix_orlaith_button('See current vacancies', $careers_url);
        $apply[$index]['primary_button_variant'] = 'filled';
    }
    $save($apply_id, $apply, 'How to apply title and CTA');
}

// --- New hospital: hero intro + body section ---
$hospital_id = 229;
$hospital = get_field('flexible_content_blocks', $hospital_id);
if (is_array($hospital)) {
    $hero = $hospital[0] ?? [];
    $img_id = $hero_image_id($hero);
    $hero_intro = $p('St Patrick’s Mental Health Services is committed to ensuring our facilities are of the highest quality possible for our service users.');
    $body = $p('By continuing to modernise our inpatient facilities, we can create a purposefully designed and modern space where service users feel safe, respected and in control, and enjoy a sense of connection, community and dignity.')
        . $p('We will also explore the viability of building a world-class mental healthcare facility, leveraging our existing land at '
            . $a($lucan_url, 'St Patrick’s Hospital Lucan')
            . ', to provide:')
        . '<ul>'
        . '<li>adolescent and adult inpatient services</li>'
        . '<li>acute inpatient services</li>'
        . '<li>an addiction unit</li>'
        . '<li>an eating disorders unit</li>'
        . '<li>age-specific care units</li>'
        . '<li>day service and outpatient facilities</li>'
        . '<li>a full range of therapeutic facilities, including a gym, music therapy rooms, and an arts and crafts studio.</li>'
        . '</ul>';

    $hero_row = matrix_orlaith_hero_row('Modernising our facilities', $hero_intro, $img_id);
    $hero_row['layout_style'] = $img_id > 0 ? 'image_split' : 'title_accent';
    $hero_row['current_crumb_label'] = 'Modernising our facilities';
    $hero_row['background_color'] = '#C6ECF4';
    $hero_row['accent_color'] = '#6FC9C0';

    $rest = array_values(array_filter(
        array_slice($hospital, 1),
        static fn($row): bool => ($row['acf_fc_layout'] ?? '') !== 'content'
    ));

    $save($hospital_id, array_merge([
        $hero_row,
        matrix_orlaith_content_row('', $body, 'white'),
    ], $rest), 'New hospital split');
}

// --- Extending services: embed strategy video ---
$extending_id = 264;
$extending = get_field('flexible_content_blocks', $extending_id);
if (is_array($extending)) {
    foreach ($extending as $index => $row) {
        if (($row['acf_fc_layout'] ?? '') !== 'content') {
            continue;
        }
        $content = (string) ($row['content'] ?? '');
        $content = preg_replace(
            '#<p>\s*<em>Embed</em>\s*<a[^>]*>.*?</a>\s*</p>#is',
            '',
            $content
        ) ?? $content;
        $content = str_replace(
            'https://www.stpatricks.ie/care-treatment/programmes-therapies/our-programmes-and-therapies',
            $day_url,
            $content
        );
        $content = str_replace(
            'https://www.stpatricks.ie/care-treatment/outpatient-clinics/about-the-dean-clinics',
            $dean_url,
            $content
        );
        $content = str_replace(
            'for adults and adolescents that as many service users as possible',
            'for adults and adolescents so that as many service users as possible',
            $content
        );
        $extending[$index]['content'] = $content;
    }

    $has_video = false;
    foreach ($extending as $row) {
        if (($row['acf_fc_layout'] ?? '') === 'video_showcase') {
            $has_video = true;
            break;
        }
    }
    if (! $has_video) {
        $insert_at = count($extending);
        foreach ($extending as $index => $row) {
            if (($row['acf_fc_layout'] ?? '') === 'useful_links') {
                $insert_at = $index;
                break;
            }
        }
        array_splice($extending, $insert_at, 0, [matrix_orlaith_video_row('', '', [[
            'url' => 'https://www.youtube.com/watch?v=ZkX7DqQE_60',
            'title' => 'Service delivery strategy',
        ]])]);
    }
    $save($extending_id, $extending, 'Extending services video');
}

// --- National centre: hero + body + education section ---
$national_id = 211;
$national = get_field('flexible_content_blocks', $national_id);
if (is_array($national)) {
    $hero = $national[0] ?? [];
    $img_id = $hero_image_id($hero);
    $hero_intro = $p('St Patrick’s Mental Health Services is committed to developing a national centre for mentally healthy living.');
    $body = $p('The national centre for mentally healthy living will provide mental health and wellbeing initiatives for the general population, as well as a range of services for people experiencing mental health difficulties.')
        . $p('Planning permission to proceed with phase one of this development was granted from Dublin City Council. This includes a programme of conservation work to repair the fabric of the Historic Building of St Patrick’s University Hospital in Dublin 8 and to transform its ground floor, which will be home to:')
        . '<ul>'
        . '<li>an Interactive Education Centre</li>'
        . '<li>an ' . $a($advocacy_url, 'Advocacy Centre') . '</li>'
        . '<li>an ' . $a($academic_url, 'Academic Institute') . '</li>'
        . '<li>a ' . $a($training_url, 'Training Centre') . '</li>'
        . '<li>clinical rooms and spaces for service delivery.</li>'
        . '</ul>';
    $education = $p('The Interactive Education Centre will tackle misinformation about mental health; challenge stigma; and educate people, particularly young people, about the practical tools we can all use throughout our lives to support our wellbeing and mental health. It will also immerse them in the history of our founder, Jonathan Swift, and the hospital building. The education centre will be free of charge to the public, and will open up compassionate conversations around mental health and equip visitors with the knowledge and tools to better understand and support their mental health.');

    $hero_row = matrix_orlaith_hero_row('National Centre', $hero_intro, $img_id);
    $hero_row['layout_style'] = $img_id > 0 ? 'image_split' : 'title_accent';
    $hero_row['current_crumb_label'] = 'National Centre';
    $hero_row['background_color'] = '#C6ECF4';
    $hero_row['accent_color'] = '#6FC9C0';

    $rest = [];
    foreach (array_slice($national, 1) as $row) {
        $layout = $row['acf_fc_layout'] ?? '';
        $heading = trim((string) ($row['heading'] ?? ''));
        if ($layout === 'content' && ($heading === '' || $heading === 'Interactive Education Centre')) {
            continue;
        }
        $rest[] = $row;
    }

    $save($national_id, array_merge([
        $hero_row,
        matrix_orlaith_content_row('', $body, 'white'),
        matrix_orlaith_content_row('Interactive Education Centre', $education, 'cream'),
    ], $rest), 'National centre split');
}

// --- Inpatient care padding, Who we support h2, referral link ---
$inpatient_id = 212;
$inpatient = get_field('flexible_content_blocks', $inpatient_id);
if (is_array($inpatient)) {
    $new_rows = [];
    foreach ($inpatient as $row) {
        if (($row['acf_fc_layout'] ?? '') === 'content' && trim((string) ($row['heading'] ?? '')) === 'Where is inpatient care available?') {
            $row['vertical_padding'] = 'default';
        }

        if (($row['acf_fc_layout'] ?? '') === 'content' && trim((string) ($row['heading'] ?? '')) === 'What to expect') {
            $content = (string) ($row['content'] ?? '');
            $parts = preg_split('/<p>\s*(?:<strong>)?\s*H2:\s*Who we support\s*(?:<\/strong>)?\s*<\/p>/i', $content, 2);
            $expect = $parts[0] ?? $content;
            $expect = str_replace(
                '<p>Your Stay as an Adult</p>',
                '<p>' . $a($stay_adult_url, 'Your Stay as an Adult') . '</p>',
                $expect
            );
            $expect = preg_replace(
                '#<p>Your Stay as an Adolescent.*?</p>#is',
                '<p>' . $a($stay_adolescent_url, 'Your Stay as an Adolescent') . '</p>',
                $expect
            ) ?? $expect;
            $expect = preg_replace('#<strong></p>#', '</p>', $expect) ?? $expect;
            $row['content'] = $expect;
            $new_rows[] = $row;

            $support = $parts[1] ?? '';
            $support = preg_replace('#^</strong>#', '', $support) ?? $support;
            $support = preg_replace('#^<p></strong>#', '<p>', $support) ?? $support;
            $support = str_replace('</strong>', '', $support);
            if ($support !== '') {
                $support_row = matrix_orlaith_content_row('Who we support', $support, 'cream');
                $support_row['heading_tag'] = 'h2';
                $new_rows[] = $support_row;
            }
            continue;
        }

        if (($row['acf_fc_layout'] ?? '') === 'content' && trim((string) ($row['heading'] ?? '')) === 'How to access inpatient care') {
            $content = (string) ($row['content'] ?? '');
            if (str_contains($content, 'making a referral here') && ! str_contains($content, 'making a referral here</a>')) {
                $content = str_replace(
                    'You can find out more about making a referral here.',
                    'You can find out more about making a referral ' . $a($refer_inpatient_url, 'here') . '.',
                    $content
                );
            }
            $row['content'] = $content;
        }

        $new_rows[] = $row;
    }
    $save($inpatient_id, $new_rows, 'Inpatient care');
}

// --- Outpatient: How to access padding + location images ---
$clinic_images = [
    2132 => 380,  // Cork — clinic exterior
    2133 => 617,  // Galway — dean clinic photo
    2134 => 2127, // Lucan — hospital campus
    2135 => 3131, // St Patrick's Dean Clinic
    2136 => 2128, // Adolescent — Willow Grove
];
foreach ($clinic_images as $location_id => $image_id) {
    if (get_post_type($image_id) !== 'attachment') {
        WP_CLI::warning('Missing image ' . $image_id . ' for location ' . $location_id);
        continue;
    }
    set_post_thumbnail($location_id, $image_id);
    update_field('card_image', $image_id, $location_id);
}

$outpatient_id = 222;
$outpatient = get_field('flexible_content_blocks', $outpatient_id);
if (is_array($outpatient)) {
    foreach ($outpatient as $index => $row) {
        if (($row['acf_fc_layout'] ?? '') === 'content' && trim((string) ($row['heading'] ?? '')) === 'How to access') {
            $outpatient[$index]['vertical_padding'] = 'default';
        }

        if (($row['acf_fc_layout'] ?? '') !== 'locations_grid') {
            continue;
        }

        $cards = $row['cards'] ?? [];
        if (! is_array($cards)) {
            continue;
        }
        foreach ($cards as $card_index => $card) {
            $title = strtolower(trim((string) ($card['title'] ?? '')));
            $image_id = 0;
            if (str_contains($title, 'cork')) {
                $image_id = 380;
            } elseif (str_contains($title, 'galway')) {
                $image_id = 617;
            } elseif (str_contains($title, 'lucan')) {
                $image_id = 2127;
            } elseif (str_contains($title, 'adolescent')) {
                $image_id = 2128;
            } elseif (str_contains($title, 'patrick')) {
                $image_id = 3131;
            }
            if ($image_id > 0) {
                $outpatient[$index]['cards'][$card_index]['image'] = $image_id;
            }
        }
        $outpatient[$index]['heading'] = 'Our locations';
    }
    $save($outpatient_id, $outpatient, 'Outpatient care');
}

// --- Our locations layout ---
$locations_id = 282;
$locations = get_field('flexible_content_blocks', $locations_id);
if (is_array($locations)) {
    $hero = $locations[0] ?? [];
    $grid = null;
    $hospitals = '';
    $outpatient_copy = '';
    foreach ($locations as $row) {
        $layout = $row['acf_fc_layout'] ?? '';
        $heading = trim((string) ($row['heading'] ?? ''));
        if ($layout === 'locations_grid') {
            $grid = $row;
            $grid['heading'] = 'Find us';
        }
        if ($layout === 'content' && $heading === 'Our hospitals and inpatient services') {
            $hospitals = (string) ($row['content'] ?? '');
        }
        if ($layout === 'content' && ($heading === 'Our outpatient clinics' || $heading === 'Our outpatient services')) {
            $outpatient_copy = (string) ($row['content'] ?? '');
        }
    }

    $hero_intro = $p('Find contact details, visiting information and directions for our hospitals and Dean Clinics across Ireland.');
    $overview = $p('St Patrick’s Mental Health Services (SPMHS) provides care across a number of locations in Ireland, including our hospitals in Dublin, Willow Grove Adolescent Unit and our network of Dean Clinics. If you are referred to our services and an assessment finds that inpatient care is the most suitable option for you, your care team will discuss where you will receive care based on your individual needs and treatment plan.');

    $hero_row = matrix_orlaith_hero_row('Our locations', $hero_intro, 0);
    $hero_row['layout_style'] = 'title_accent';
    $hero_row['current_crumb_label'] = 'Our locations';
    $hero_row['background_color'] = '#C6ECF4';
    $hero_row['accent_color'] = '#6FC9C0';

    if ($grid === null) {
        $grid = [
            'acf_fc_layout' => 'locations_grid',
            'heading_tag' => 'h2',
            'heading' => 'Find us',
            'source_mode' => 'locations',
            'selected_locations' => [711, 712, 2131, 2132, 2133, 2134, 2135, 2136],
            'cards' => '',
            'footer_button_link' => '',
        ];
    }

    $access_intro = $p('You can ' . $a($directions_url, 'find directions to our locations here') . '.')
        . $p('You will find overviews of the accessibility supports available in our locations below.');

    $access_items = [
        'St Patrick’s University Hospital' => $p('Accessibility supports available at St Patrick’s University Hospital include:')
            . '<ul>'
            . '<li>accessible parking spaces near the main entrance</li>'
            . '<li>accessible toilets and a Changing Places facility</li>'
            . '<li>lift access throughout the hospital</li>'
            . '<li>wheelchair assistance, where required</li>'
            . '<li>hearing loops in selected locations</li>'
            . '<li>sign language interpreters, where possible and arranged in advance</li>'
            . '<li>closed captions for online appointments and groups</li>'
            . '<li>access for trained service dogs.</li>'
            . '</ul>',
        'St Patrick’s Hospital Lucan' => $p('Accessibility supports available at St Patrick’s Hospital Lucan include:')
            . '<ul>'
            . '<li>accessible parking</li>'
            . '<li>ramp access to the main entrance</li>'
            . '<li>wheelchair-accessible bedrooms</li>'
            . '<li>accessible visitor toilets</li>'
            . '<li>lift access to upper floors</li>'
            . '<li>sign language interpreters, where possible and arranged in advance</li>'
            . '<li>closed captions for online appointments and groups</li>'
            . '<li>access for trained service dogs.</li>'
            . '</ul>',
        'Willow Grove Adolescent Unit' => $p('Accessibility supports available at Willow Grove include:')
            . '<ul>'
            . '<li>ground-floor access throughout the unit</li>'
            . '<li>a wheelchair-accessible bedroom</li>'
            . '<li>wheelchair access to the gym and basketball court with a wheelchair lift</li>'
            . '<li>sign language interpreters, where possible and arranged in advance</li>'
            . '<li>closed captions for online appointments and groups</li>'
            . '<li>access for trained service dogs.</li>'
            . '</ul>'
            . $p('A member of the team will discuss any support needs or accommodations with the young person and their family before admission.'),
        'Dean Clinics' => $p('Accessibility supports vary by clinic location and may include:')
            . '<ul>'
            . '<li>accessible entrances and facilities</li>'
            . '<li>sign language interpretation, where possible and arranged in advance</li>'
            . '<li>closed captions for online appointments</li>'
            . '<li>access for trained service dogs.</li>'
            . '</ul>'
            . $p('If you require support when attending a Dean Clinic appointment, we encourage you to contact the clinic in advance so that arrangements can be made where possible.'),
    ];

    $queries = $p('For general queries, please call us. For more on mental health and our services, '
        . $a($faqs_url, 'see our Frequently Asked Questions (FAQs)')
        . '.');
    $referrals = $p('GPs and healthcare professionals can contact our Referrals and Assessments Team for queries on referrals to our services. '
        . $a($referrals_url, 'See more from our referrals team')
        . '.');
    $directions = $p('Find details of how to travel to and park at our locations.');

    $flexi = [
        $hero_row,
        matrix_orlaith_content_row('', $overview, 'white'),
        $grid,
        matrix_orlaith_content_row('Our hospitals and inpatient services', $hospitals !== '' ? $hospitals : $p('We provide inpatient care for adults at St Patrick’s University Hospital in Dublin 8 and St Patrick’s Hospital Lucan in County Dublin.') . $p('Adolescent inpatient care is provided in Willow Grove Adolescent Unit, located on the grounds of St Patrick’s University Hospital in Dublin 8.'), 'cream'),
        matrix_orlaith_content_row('Our outpatient clinics', $outpatient_copy !== '' ? $outpatient_copy : $p('Our Dean Clinics are a network of community-based mental health clinics located in Dublin, Cork, Galway and Lucan.'), 'white'),
        matrix_orlaith_content_row('Accessibility', $access_intro, 'cream'),
        matrix_orlaith_accordion_row($access_items),
        matrix_orlaith_content_row('Queries', $queries, 'white', 0, 'image_left', [
            'primary_button' => matrix_orlaith_button('Call us', 'tel:012493200'),
            'primary_button_variant' => 'filled',
        ]),
        matrix_orlaith_content_row('Referrals', $referrals, 'cream', 0, 'image_left', [
            'primary_button' => matrix_orlaith_button('Call our referrals team', 'tel:012493635'),
            'primary_button_variant' => 'filled',
        ]),
        matrix_orlaith_content_row('Directions and parking', $directions, 'white', 0, 'image_left', [
            'primary_button' => matrix_orlaith_button('Get directions here', $directions_url),
            'primary_button_variant' => 'filled',
        ]),
    ];

    $save($locations_id, $flexi, 'Our locations');
}

WP_CLI::success('Next-batch snag updates complete.');
