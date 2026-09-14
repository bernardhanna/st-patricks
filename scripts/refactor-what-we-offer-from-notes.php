<?php

/**
 * Apply What We Offer refactor notes from old/content/What We Offer refactor.md
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/refactor-what-we-offer-from-notes.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

$home = untrailingslashit(home_url('/'));
$call_tel = 'tel:012493200';
$portal_url = $home . '/about-your-portal/';
$suits_url = $home . '/service-user-it-support/';
$at_home_expect_url = $home . '/service-users-and-visitors/about-our-st-patricks-at-home-service/';
$adolescent_homecare_url = $home . '/service-users-and-visitors/your-care-with-willow-grove/your-time-in-homecare-as-an-adolescent/';
$su_faqs_url = $home . '/service-users-and-visitors/frequently-asked-questions-faqs/';

$tones = ['bg1', 'bg2', 'bg3', 'bg4'];

/**
 * @return array{title:string,description:string,link:array{title:string,url:string,target:string},card_tone:string,icon:string,image_url:string}
 */
$grid_card = static function (string $title, string $url, string $description = '', string $tone = 'bg1'): array {
    return [
        'icon' => '',
        'image_url' => '',
        'title' => $title,
        'description' => $description,
        'link' => [
            'title' => $title,
            'url' => $url,
            'target' => '',
        ],
        'card_tone' => $tone,
    ];
};

/**
 * Split HTML into intro (before first h3) and h3 => content map.
 *
 * @return array{0:string,1:array<string,string>}
 */
$split_h3 = static function (string $html): array {
    $intro = $html;
    $items = [];
    if (preg_match('#^(.*?)(?=<h3\b)#is', $html, $m)) {
        $intro = trim($m[1]);
    }
    if (preg_match_all('#<h3[^>]*>(.*?)</h3>(.*?)(?=<h3\b|$)#is', $html, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $title = trim(wp_strip_all_tags($match[1]));
            $body = trim($match[2]);
            if ($title !== '' && $body !== '') {
                $items[$title] = $body;
            }
        }
    }

    return [$intro, $items];
};

/**
 * Remove inline CTA paragraphs that become buttons.
 */
$strip_cta_links = static function (string $html): string {
    $patterns = [
        '#<p>\s*<a[^>]*>Learn more about Your Portal here</a>\.?\s*</p>#i',
        '#<p>\s*<a[^>]*>See more about SUITS here\.?</a>\s*</p>#i',
        '#,\s*or\s*<a[^>]*>learn more about what to expect from (?:adolescent )?homecare here</a>#i',
        '#\s*or\s*<a[^>]*>learn more about what to expect from (?:adolescent )?homecare here</a>\.?#i',
        '#<a[^>]*>learn more about what to expect from (?:adolescent )?homecare here</a>#i',
    ];
    $out = preg_replace($patterns, '', $html);

    return is_string($out) ? trim($out) : $html;
};

$permalink = static function (string $path) use ($home): string {
    $path = trim($path, '/');
    foreach (['page', 'mental_health', 'programmes_therapies', 'outpatient_clinics', 'locations'] as $type) {
        $found = get_posts([
            'name' => basename($path),
            'post_type' => $type,
            'post_status' => 'publish',
            'posts_per_page' => 1,
        ]);
        if ($found !== []) {
            return (string) get_permalink($found[0]);
        }
    }

    return $home . '/' . $path . '/';
};

// ---------------------------------------------------------------------------
// 1) St Patrick's at Home (#238)
// ---------------------------------------------------------------------------
$at_home_id = (int) (get_page_by_path('what-we-offer/st-patricks-at-home')?->ID ?? 0);
if ($at_home_id > 0) {
    $rows = get_field('flexible_content_blocks', $at_home_id) ?: [];
    $already_refactored = false;
    foreach ($rows as $row) {
        $heading = wp_strip_all_tags((string) ($row['heading'] ?? ''));
        if (($row['acf_fc_layout'] ?? '') === 'content' && $heading === 'Your Portal' && ! empty($row['primary_button']['url'])) {
            $already_refactored = true;
            break;
        }
    }
    if ($already_refactored) {
        WP_CLI::log('St Patrick\'s at Home already refactored — skipping.');
    } else {
    $new = [];

    foreach ($rows as $row) {
        $layout = (string) ($row['acf_fc_layout'] ?? '');
        $heading = wp_strip_all_tags((string) ($row['heading'] ?? ''));

        if ($layout === 'content' && stripos($heading, 'for adults') !== false) {
            [$intro, $items] = $split_h3((string) $row['content']);
            $intro = $strip_cta_links($intro);
            if (! str_contains(wp_strip_all_tags($intro), 'key aspects')) {
                $intro .= '<p>You can see some other key aspects of the homecare service below.</p>';
            }

            $portal_html = $items['Your Portal'] ?? '';
            $suits_html = $items['Technical support'] ?? '';
            $portal_html = $strip_cta_links($portal_html);
            $suits_html = $strip_cta_links($suits_html);

            $accordion_items = [];
            foreach (['Medication', 'Recovery-focused and social activities'] as $key) {
                if (! empty($items[$key])) {
                    $accordion_items[$key] = $items[$key];
                }
            }

            $new[] = matrix_orlaith_content_row($heading, $intro, (string) ($row['background_type'] ?? 'white'), 0, 'image_left', [
                'primary_button' => matrix_orlaith_button('Learn more about what to expect from homecare', $at_home_expect_url),
                'primary_button_variant' => 'filled',
                'secondary_button' => ['title' => '', 'url' => '', 'target' => ''],
            ]);

            if ($accordion_items !== []) {
                $new[] = matrix_orlaith_accordion_row($accordion_items, 'default', '');
            }

            if ($portal_html !== '') {
                $new[] = matrix_orlaith_content_row('Your Portal', $portal_html, 'cream', 0, 'image_left', [
                    'primary_button' => matrix_orlaith_button('Learn more about Your Portal', $portal_url),
                    'primary_button_variant' => 'filled',
                ]);
            }

            if ($suits_html !== '') {
                $new[] = matrix_orlaith_content_row('Technical support', $suits_html, 'white', 0, 'image_left', [
                    'primary_button' => matrix_orlaith_button('See more about SUITS', $suits_url),
                    'primary_button_variant' => 'filled',
                ]);
            }

            continue;
        }

        if ($layout === 'content' && stripos($heading, 'for adolescents') !== false) {
            [$intro, $items] = $split_h3((string) $row['content']);
            $intro = $strip_cta_links($intro);
            if (! str_contains(wp_strip_all_tags($intro), 'key aspects')) {
                $intro .= '<p>See some other key aspects of our adolescent homecare service below.</p>';
            }

            $accordion_items = [];
            foreach (['Child and adolescent team', 'Education', 'Medication', 'Technical support'] as $key) {
                if (empty($items[$key])) {
                    continue;
                }
                $body = $strip_cta_links($items[$key]);
                $accordion_items[$key] = $body;
            }

            $new[] = matrix_orlaith_content_row($heading, $intro, (string) ($row['background_type'] ?? 'cream'), 0, 'image_left', [
                'primary_button' => matrix_orlaith_button('Learn more about adolescent homecare', $adolescent_homecare_url),
                'primary_button_variant' => 'filled',
                'secondary_button' => matrix_orlaith_button('See more about SUITS', $suits_url),
                'secondary_button_variant' => 'outline',
            ]);

            if ($accordion_items !== []) {
                $new[] = matrix_orlaith_accordion_row($accordion_items, 'default', '');
            }

            continue;
        }

        if ($layout === 'content' && stripos($heading, 'Queries') !== false) {
            $row['content'] = '<p>For general queries, please call us. For more on mental health and our services, <a href="' . esc_url($su_faqs_url) . '">see our frequently asked questions (FAQs)</a>.</p>';
            $row['primary_button'] = matrix_orlaith_button('Call us', $call_tel);
            $row['primary_button_variant'] = 'filled';
            $row['secondary_button'] = ['title' => '', 'url' => '', 'target' => ''];
            $new[] = $row;
            continue;
        }

        $new[] = $row;
    }

    matrix_orlaith_save_page($at_home_id, $new, true);
    WP_CLI::success('St Patrick\'s at Home → ' . get_permalink($at_home_id));
    }
}

// ---------------------------------------------------------------------------
// 2) Outpatient Care - Dean Clinics (#222)
// ---------------------------------------------------------------------------
$dean_id = (int) (get_page_by_path('what-we-offer/outpatient-care-dean-clinics')?->ID ?? 0);
if ($dean_id > 0) {
    $clinic_urls = [
        'cork' => $permalink('outpatient-clinics/dean-clinic-cork'),
        'galway' => $permalink('outpatient-clinics/dean-clinic-galway'),
        'lucan' => $permalink('outpatient-clinics/dean-clinic-lucan'),
        'st-patricks' => $permalink('outpatient-clinics/dean-clinic-st-patricks'),
        'adolescent' => $permalink('outpatient-clinics/adolescent-dean-clinic'),
    ];

    $adult_clinic_cards = [
        $grid_card('Dean Clinic Cork', $clinic_urls['cork'], 'City Gate, Mahon, County Cork', 'bg1'),
        $grid_card('Dean Clinic Galway', $clinic_urls['galway'], 'Merchant’s Road, Galway', 'bg2'),
        $grid_card('Dean Clinic Lucan', $clinic_urls['lucan'], 'Lucan, County Dublin', 'bg3'),
        $grid_card('Dean Clinic St Patrick’s', $clinic_urls['st-patricks'], 'James’ Street, Dublin 8', 'bg4'),
    ];

    $adolescent_clinic_cards = [
        $grid_card('Dean Clinic Cork', $clinic_urls['cork'], 'City Gate, Mahon, County Cork', 'bg1'),
        $grid_card('Dean Clinic St Patrick’s', $clinic_urls['st-patricks'], 'James’ Street, Dublin 8', 'bg2'),
    ];

    $mh = static function (string $slug) use ($home): string {
        $found = get_posts([
            'name' => $slug,
            'post_type' => 'mental_health',
            'post_status' => 'publish',
            'posts_per_page' => 1,
        ]);

        return $found !== [] ? (string) get_permalink($found[0]) : $home . '/mental-health/' . $slug . '/';
    };

    $prog = static function (string $slug) use ($home): string {
        $found = get_posts([
            'name' => $slug,
            'post_type' => 'programmes_therapies',
            'post_status' => 'publish',
            'posts_per_page' => 1,
        ]);

        return $found !== [] ? (string) get_permalink($found[0]) : $home . '/programmes-therapies/' . $slug . '/';
    };

    $adult_condition_cards = [];
    $adult_conditions = [
        ['Addiction and substance abuse', $mh('addiction-dual-diagnosis'), 'Support for addiction and dual diagnosis.'],
        ['Anxiety', $mh('anxiety'), 'Information and pathways for anxiety.'],
        ['Mood disorders', $mh('bipolar-disorder'), 'Including bipolar disorder and low mood.'],
        ['Dual diagnosis', $mh('addiction-dual-diagnosis'), 'Addiction alongside a mental health difficulty.'],
        ['Eating disorders', $mh('eating-disorders'), 'Specialist eating disorder support.'],
        ['Memory difficulties', $prog('living-well-with-mild-cognitive-impairment'), 'Support for mild cognitive impairment and memory.'],
        ['Obsessive-Compulsive Disorder (OCD)', $mh('anxiety'), 'Related anxiety and OCD pathways.'],
        ['Psychosis', $mh('schizophrenia-psychosis'), 'Psychosis and schizophrenia information.'],
        ['Stress-related disorders', $mh('anxiety'), 'Support for stress-related difficulties.'],
    ];
    foreach ($adult_conditions as $i => [$title, $url, $desc]) {
        $adult_condition_cards[] = $grid_card($title, $url, $desc, $tones[$i % 4]);
    }

    $adolescent_condition_cards = [];
    $adolescent_conditions = [
        ['Anxiety', $mh('anxiety'), 'Anxiety support for young people.'],
        ['Low mood', $mh('depression'), 'Support for low mood and depression.'],
        ['Eating disorders', $mh('eating-disorders'), 'Specialist eating disorder support.'],
        ['OCD', $mh('anxiety'), 'Related anxiety and OCD pathways.'],
        ['Psychosis', $mh('schizophrenia-psychosis'), 'Psychosis information and support.'],
        ['Stress-related disorders', $mh('anxiety'), 'Support for stress-related difficulties.'],
    ];
    foreach ($adolescent_conditions as $i => [$title, $url, $desc]) {
        $adolescent_condition_cards[] = $grid_card($title, $url, $desc, $tones[$i % 4]);
    }

    $refer_outpatient_url = $home . '/healthcare-professionals/refer-for-outpatient-care/';
    $attending_dean_url = $home . '/service-users-and-visitors/attending-a-dean-clinic/';

    $rows = get_field('flexible_content_blocks', $dean_id) ?: [];
    $new = [];

    foreach ($rows as $row) {
        $layout = (string) ($row['acf_fc_layout'] ?? '');
        $heading = wp_strip_all_tags((string) ($row['heading'] ?? ''));

        // Drop the leftover standalone remote-appointments paragraph.
        if (
            $layout === 'content'
            && $heading === ''
            && is_string($row['content'] ?? null)
            && str_contains((string) $row['content'], 'Appointments can also take place remotely')
        ) {
            continue;
        }

        // Skip grids regenerated from the section handlers below.
        if (
            $layout === 'about_links_grid'
            && (
                stripos($heading, 'Adult Dean Clinics') !== false
                || stripos($heading, 'Adolescent Dean Clinics') !== false
                || stripos($heading, 'For adult service users') !== false
                || stripos($heading, 'For adolescent service users') !== false
            )
        ) {
            continue;
        }

        if ($layout === 'content_accordion' && stripos($heading, 'FAQ') !== false) {
            $new[] = $row;
            continue;
        }

        if ($layout === 'content' && stripos($heading, 'Where you will receive care') !== false) {
            $new[] = matrix_orlaith_content_row(
                $heading,
                '<p>Adult and adolescent outpatient services are available across our Dean Clinics.</p>',
                (string) ($row['background_type'] ?? 'white')
            );
            $new[] = matrix_orlaith_about_links_grid_row(
                'Adult Dean Clinics',
                $adult_clinic_cards,
                [
                    'intro_text' => 'Adult services are available in the clinics below.',
                    'layout_style' => 'compact_row',
                    'columns' => '2',
                    'bg_color' => '#FBFAF7',
                ]
            );
            $new[] = matrix_orlaith_about_links_grid_row(
                'Adolescent Dean Clinics',
                $adolescent_clinic_cards,
                [
                    'intro_text' => 'Adolescent services are available in the clinics below.',
                    'layout_style' => 'compact_row',
                    'columns' => '2',
                    'bg_color' => '#F1F8F9',
                ]
            );
            continue;
        }

        if ($layout === 'content' && stripos($heading, 'Who we support') !== false) {
            $new[] = matrix_orlaith_content_row(
                $heading,
                '<p>We treat a wide range of mental health difficulties in the Dean Clinics. Appointments can also take place remotely by phone or video.</p>',
                (string) ($row['background_type'] ?? 'cream')
            );
            $new[] = matrix_orlaith_about_links_grid_row(
                'For adult service users',
                $adult_condition_cards,
                [
                    'intro_text' => 'Explore information on the difficulties we commonly treat for adults.',
                    'layout_style' => 'compact_row',
                    'columns' => '2',
                    'bg_color' => '#FBFAF7',
                ]
            );
            $new[] = matrix_orlaith_about_links_grid_row(
                'For adolescent service users',
                $adolescent_condition_cards,
                [
                    'intro_text' => 'Explore information on the difficulties we commonly treat for adolescents.',
                    'layout_style' => 'compact_row',
                    'columns' => '2',
                    'bg_color' => '#F1F8F9',
                ]
            );
            continue;
        }

        if ($layout === 'content' && stripos($heading, 'How to access') !== false) {
            $new[] = matrix_orlaith_content_row(
                $heading,
                '<p>If you are not already using our services, the best way to access a Dean Clinic is through your GP. Your GP can assess your needs and, if specialist support would help, they may send a referral to our Referral and Assessment Service. A consultant psychiatrist will review this referral and advise if care at a Dean Clinic is right for you.</p>'
                . '<p>Find out more about referrals to the Dean Clinic below.</p>',
                (string) ($row['background_type'] ?? 'white'),
                0,
                'image_left',
                [
                    'primary_button' => matrix_orlaith_button('Find out more', $refer_outpatient_url),
                    'primary_button_variant' => 'filled',
                ]
            );
            continue;
        }

        if ($layout === 'content' && (stripos($heading, 'FAQ') !== false || $heading === 'FAQs')) {
            $html = (string) ($row['content'] ?? '');
            $html = str_replace(
                [
                    'Find more detailed information on attending appointments, and what to expect on the day here.',
                    'https://www.stpatricks.ie/care-treatment/your-portal/service-user-it-support',
                ],
                [
                    'Find more detailed information on <a href="' . esc_url($attending_dean_url) . '">attending appointments and what to expect on the day</a>.',
                    $suits_url,
                ],
                $html
            );

            $faq_items = [];
            if (preg_match_all('#<p>\s*<strong>(.*?)</strong>\s*</p>\s*(.*?)(?=<p>\s*<strong>|$)#is', $html, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $q = trim(wp_strip_all_tags($match[1]));
                    $a = trim($match[2]);
                    if ($q !== '' && $a !== '') {
                        $faq_items[$q] = $a;
                    }
                }
            }

            if ($faq_items !== []) {
                $new[] = matrix_orlaith_accordion_row($faq_items, 'default', 'FAQs');
            } else {
                $new[] = $row;
            }
            continue;
        }

        // Replace live-site outpatient clinic links if any remain in other blocks.
        if (! empty($row['content']) && is_string($row['content'])) {
            $row['content'] = str_replace(
                [
                    'https://www.stpatricks.ie/care-treatment/outpatient-clinics/dean-clinic-cork',
                    'https://www.stpatricks.ie/care-treatment/outpatient-clinics/dean-clinic-galway',
                    'https://www.stpatricks.ie/care-treatment/outpatient-clinics/dean-clinic-lucan',
                    'https://www.stpatricks.ie/care-treatment/outpatient-clinics/dean-clinic-st-patrick-s',
                    'https://www.stpatricks.ie/care-treatment/your-portal/service-user-it-support',
                    'https://www.stpatricks.ie/care-treatment/your-portal',
                ],
                [
                    $clinic_urls['cork'],
                    $clinic_urls['galway'],
                    $clinic_urls['lucan'],
                    $clinic_urls['st-patricks'],
                    $suits_url,
                    $portal_url,
                ],
                $row['content']
            );
        }

        $new[] = $row;
    }

    matrix_orlaith_save_page($dean_id, $new, true);
    WP_CLI::success('Outpatient Care - Dean Clinics → ' . get_permalink($dean_id));
}

WP_CLI::success('What We Offer refactor complete.');
