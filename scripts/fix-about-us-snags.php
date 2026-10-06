<?php

/**
 * Apply About Us snag list content fixes.
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/fix-about-us-snags.php
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
$ul = static function (array $items): string {
    $html = '<ul>';
    foreach ($items as $item) {
        $html .= '<li>' . $item . '</li>';
    }

    return $html . '</ul>';
};
$permalink = static function (string $path): string {
    $page = get_page_by_path($path);

    return $page instanceof WP_Post ? (string) get_permalink($page) : home_url('/' . trim($path, '/') . '/');
};
$save = static function (int $post_id, array $rows, string $label): void {
    update_field('flexible_content_blocks', $rows, $post_id);
    WP_CLI::success($label . ' #' . $post_id);
};

$careers_url = $permalink('about-us/careers');
$about_us_url = $permalink('about-us');
$team_url = $permalink('about-us/our-team');
$present_future_url = $permalink('about-us/our-present-and-future');
$recruitment_url = $permalink('about-us/careers/recruitment-and-useful-information');
$interview_url = $permalink('about-us/careers/attending-an-interview');
$wellbeing_url = $permalink('about-us/careers/recruitment-and-useful-information/staff-wellbeing');
$work_exp_url = $permalink('about-us/careers/recruitment-and-useful-information/how-to-get-work-experience');
$apply_url = $permalink('about-us/careers/recruitment-and-useful-information/how-to-apply-for-a-role');

// --- Careers: useful links as real links below the vacancies ---
$careers_id = 254;
$careers = get_field('flexible_content_blocks', $careers_id);
if (is_array($careers)) {
    foreach ($careers as $index => $row) {
        if (($row['acf_fc_layout'] ?? '') !== 'about_links_grid') {
            continue;
        }

        $careers[$index] = matrix_orlaith_useful_links_row([
            'Recruitment and Useful Information' => $recruitment_url,
            'Attending an interview' => $interview_url,
            'Staff Wellbeing' => $wellbeing_url,
            'How to get work experience' => $work_exp_url,
            'How to apply for a role' => $apply_url,
            'About SPMHS' => $about_us_url,
        ]);
        $careers[$index]['heading_tag'] = 'h2';
        break;
    }
    $save($careers_id, $careers, 'Careers useful links');
}

// --- Advocacy: the two named sections become accordions ---
$advocacy_id = 271;
$advocacy = get_field('flexible_content_blocks', $advocacy_id);
if (is_array($advocacy)) {
    $what_is = '';
    $what_work = '';
    $replace_from = null;
    $replace_count = 0;

    foreach ($advocacy as $index => $row) {
        if (($row['acf_fc_layout'] ?? '') !== 'content') {
            continue;
        }
        $heading = trim((string) ($row['heading'] ?? ''));
        if ($heading === 'What is advocacy?') {
            $what_is = (string) ($row['content'] ?? '');
            $replace_from = $replace_from ?? $index;
            $replace_count++;
        }
        if ($heading === 'What advocacy work do we do?') {
            $what_work = (string) ($row['content'] ?? '');
            $replace_from = $replace_from ?? $index;
            $replace_count++;
        }
    }

    if ($replace_from !== null && $replace_count > 0 && $what_is !== '' && $what_work !== '') {
        array_splice(
            $advocacy,
            $replace_from,
            $replace_count,
            [matrix_orlaith_accordion_row([
                'What is advocacy?' => $what_is,
                'What advocacy work do we do?' => $what_work,
            ])]
        );
        $save($advocacy_id, $advocacy, 'Advocacy accordions');
    } else {
        WP_CLI::warning('Advocacy accordion conversion skipped');
    }
}

// --- Our history: restore real timeline copy, drop FORM TEST ---
$history_id = 272;
$history = get_field('flexible_content_blocks', $history_id);
if (is_array($history)) {
    foreach ($history as $index => $row) {
        if (($row['acf_fc_layout'] ?? '') === 'timeline') {
            $history[$index]['heading'] = 'Our past, present, and future';
            $history[$index]['intro'] = $p('Our journey is defined by a consistent commitment to dignity, innovation, and the protection of the rights of those experiencing mental health difficulties.');
            $history[$index]['timeline_items'] = [
                [
                    'side' => 'left',
                    'event_date' => '',
                    'event_date_label' => '1745',
                    'image' => '',
                    'item_heading' => 'Jonathan Swift bequeaths £12,000',
                    'item_heading_tag' => 'h3',
                    'item_text' => $p('Jonathan Swift bequeaths £12,000 in his will to establish a hospital, marking the birth of the first psychiatric hospital in Ireland with its foundation a year later.'),
                    'cta_link' => '',
                ],
                [
                    'side' => 'right',
                    'event_date' => '',
                    'event_date_label' => '1757',
                    'image' => '',
                    'item_heading' => 'Hospital opens',
                    'item_heading_tag' => 'h3',
                    'item_text' => $p('St Patrick’s Hospital officially opens its doors to its first patients, providing a dedicated space for mental health treatment.'),
                    'cta_link' => '',
                ],
                [
                    'side' => 'left',
                    'event_date' => '',
                    'event_date_label' => '1899',
                    'image' => '',
                    'item_heading' => 'St Edmundsbury, Lucan',
                    'item_heading_tag' => 'h3',
                    'item_text' => $p('The service expands with the opening of St Edmundsbury in Lucan, reflecting a growing need for diverse therapeutic environments.'),
                    'cta_link' => '',
                ],
                [
                    'side' => 'right',
                    'event_date' => '',
                    'event_date_label' => '2009',
                    'image' => '',
                    'item_heading' => 'Rebrand to SPMHS',
                    'item_heading_tag' => 'h3',
                    'item_text' => $p('The organisation rebrands as “St Patrick’s Mental Health Services” to reflect a holistic, multidisciplinary approach to modern care.'),
                    'cta_link' => '',
                ],
            ];
        }

        if (($row['acf_fc_layout'] ?? '') === 'content' && trim((string) ($row['heading'] ?? '')) === 'Our present and future') {
            $history[$index]['content'] = $p('Here in St Patrick’s Mental Health Services (SPMHS), our organisational strategy guides our priorities today and our direction for the future.')
                . $p('<em>The Future in Mind</em> is our strategic plan for 2023 to 2027. It commits us to providing the highest quality mental healthcare, promoting mentally healthy living, and strengthening understanding of mental health difficulties.');
            $history[$index]['primary_button'] = matrix_orlaith_button('Our present and future', $present_future_url);
        }
    }
    $save($history_id, $history, 'Our history FORM TEST cleanup');
}

// --- Our present and future: accordion + Issuu embed ---
$present_id = 278;
$present = get_field('flexible_content_blocks', $present_id);
if (is_array($present)) {
    $objectives = [
        'Mental healthcare' => $p('To provide the highest quality support and treatment to people experiencing mental health difficulties.'),
        'Partnership with service users' => $p('To involve service users as equal partners in the planning, management and evaluation of the treatment and support they receive within SPMHS.'),
        'Research' => $p('To enhance our evidence-based understanding of mental health difficulties, and their treatment, through research.'),
        'Training' => $p('To support staff and organisations working in mental health to maintain and improve their skills and develop new competencies.'),
        'Human rights advocacy' => $p('To ensure mental healthcare, prevention strategies and promotion efforts for children and adults in Ireland are grounded in human rights and adhere to key human rights conventions.'),
        'Education' => $p('To educate and empower more people to live a mentally healthy life by promoting greater public understanding and awareness of mental health and reducing stigma.'),
    ];

    $issuu = '<iframe title="The Future in Mind: Strategy 2023-2027" allow="clipboard-write; autoplay; encrypted-media; fullscreen; picture-in-picture" sandbox="allow-top-navigation allow-top-navigation-by-user-activation allow-downloads allow-scripts allow-same-origin allow-popups allow-modals allow-popups-to-escape-sandbox allow-forms" allowfullscreen="true" style="position:absolute;border:none;width:100%;height:100%;left:0;right:0;top:0;bottom:0;" src="https://e.issuu.com/embed.html?d=spmhs_strategy_web_fa&amp;u=stpatricksmentalhealthservices"></iframe>';
    if (function_exists('matrix_normalize_absolute_embeds_in_html')) {
        $issuu = matrix_normalize_absolute_embeds_in_html($issuu);
    }

    $new_present = [];
    foreach ($present as $row) {
        if (($row['acf_fc_layout'] ?? '') === 'content' && trim((string) ($row['heading'] ?? '')) === 'Our strategy today') {
            $row['content'] = $p('<em>The Future in Mind</em> is our strategic plan for 2023 to 2027.')
                . $p('This strategy is underpinned by a human rights-based and recovery-focused ethos. It commits us to continuing to provide access to the highest quality mental healthcare to as many people as possible who experience mental health difficulties and require our services. We also commit to promoting mentally healthy living and to strengthening understanding and awareness of mental health difficulties.')
                . $p('The strategy was developed in consultation with our service users, staff, Board of Governors and other key stakeholders.');
            $new_present[] = $row;
            $new_present[] = matrix_orlaith_accordion_row($objectives, 'default', 'Our objectives for 2023 to 2027');
            continue;
        }

        if (($row['acf_fc_layout'] ?? '') === 'content' && trim((string) ($row['heading'] ?? '')) === 'Our future') {
            $row['content'] = $p('What gives us certainty in our plans and the resilience to keep moving forward? The possibility of recovery for all.')
                . $p('Our 2023 to 2027 strategy, <em>The Future in Mind</em>, reflects the many changes seen by society and the ever-changing landscape of mental health in Ireland, to ensure that we are prepared and equipped to respond appropriately to the diverse and dynamic mental health needs of a society that is constantly evolving.')
                . $p('As we continue to implement our strategy, we remain focused on delivering its ambitious aims and advancing our vision of a society where more people are empowered and given an opportunity to live mentally healthy lives.')
                . $p('<a href="https://www.stpatricks.ie/media/3720/the-future-in-mind-strategy-st-patricks.pdf"><em>The Future in Mind document</em></a> (PDF)')
                . $issuu;
            $new_present[] = $row;
            continue;
        }

        $new_present[] = $row;
    }
    $save($present_id, $new_present, 'Present and future accordion/embed');
}

// --- Recruitment: interview headings as accordions + fill empty applying section ---
$recruitment_id = 210;
$recruitment = get_field('flexible_content_blocks', $recruitment_id);
if (is_array($recruitment)) {
    $interview_accordion = [
        'Who will be on the interview panel?' => $p('All interviews are conducted by a panel, which may include:')
            . $ul([
                'A member of our Human Resources (HR) team',
                'Operational and/or senior management',
                'A service user representative',
                'External expert',
            ])
            . $p('You will be told in advance who will be on your panel.')
            . $p('Some roles may include task-based or skills assessment and may involve more than one interview stage.'),
        'How to prepare' => $p('We recommend that you:')
            . $ul([
                'Read the job description and person specification carefully',
                'Reread your CV',
                'Arrive in plenty of time, and be ready to begin your interview',
                'Dress appropriately for an interview',
            ])
            . $p('Please let us know in advance if you need any specific arrangements to support you at interview.')
            . $p('We also welcome any questions you may have about the role, our organisation, or your future career with us.'),
        'Where interviews take place' => $p('Interviews may take place online or in person.')
            . $p('Online interviews are held through Microsoft Teams. You will receive a link to join your scheduled interview. In-person interviews are usually held at St Patrick’s University Hospital (SPUH), Dublin 8. SPUH is a five-minute walk from Heuston Station and is well served by Dublin Bus and intercounty routes. Visitor parking is also available.')
            . $p('Directions to SPUH will be shared with you ahead of your interview.')
            . $p('If you are unable to attend, please contact the HR team in advance so we can rearrange your interview, where possible.'),
    ];

    $new_recruitment = [];
    foreach ($recruitment as $row) {
        if (($row['acf_fc_layout'] ?? '') === 'content' && trim((string) ($row['heading'] ?? '')) === 'Applying for a role') {
            $row['content'] = $p('You can view our current vacancies through our Careers section ' . $a($careers_url, 'here') . '.')
                . $p('If a role you are interested in is not currently advertised, you can email your cover letter and CV to ' . $a('mailto:hr@stpatricks.ie', 'hr@stpatricks.ie') . ' for consideration.')
                . $p('If you have any questions about the recruitment process or need support with your application, please contact our Human Resources team:')
                . $p('Email: ' . $a('mailto:hr@stpatricks.ie', 'hr@stpatricks.ie') . '<br>Phone: ' . $a('tel:012493435', '01 249 3435'))
                . $p('Please note that all applications must be submitted online, and paper applications cannot be accepted.');
            $row['primary_button'] = matrix_orlaith_button('See current vacancies', $careers_url);
            $new_recruitment[] = $row;
            continue;
        }

        if (($row['acf_fc_layout'] ?? '') === 'content' && trim((string) ($row['heading'] ?? '')) === 'Preparing for your interview') {
            $row['content'] = $p('If you are invited to interview at SPMHS, we will provide you with all the information you need in advance.');
            $new_recruitment[] = $row;
            $new_recruitment[] = matrix_orlaith_accordion_row($interview_accordion);
            continue;
        }

        $new_recruitment[] = $row;
    }
    $save($recruitment_id, $new_recruitment, 'Recruitment accordions');
}

// --- Staff wellbeing: restore Working with us copy from Drive content ---
$wellbeing_id = 228;
$wellbeing = get_field('flexible_content_blocks', $wellbeing_id);
if (is_array($wellbeing)) {
    foreach ($wellbeing as $index => $row) {
        if (($row['acf_fc_layout'] ?? '') !== 'content' || trim((string) ($row['heading'] ?? '')) !== 'Working with us') {
            continue;
        }
        $wellbeing[$index]['content'] = $p('We are always welcoming of applications from dedicated and motivated people who want to be part of an inclusive and progressive team.')
            . $p('Our roles span a wide range of clinical and non-clinical areas, from entry level through to senior leadership positions.')
            . $p('If you would like to work with us, you can view our latest vacancies through our Careers section ' . $a($careers_url, 'here') . '. If a role you are interested in is not currently advertised, you can email your CV and cover letter to ' . $a('mailto:hr@stpatricks.ie', 'hr@stpatricks.ie') . '.')
            . $p('For any queries or support with applications, you can contact our HR team:')
            . $p('Email: ' . $a('mailto:hr@stpatricks.ie', 'hr@stpatricks.ie') . '<br>Phone: ' . $a('tel:012493435', '01 249 3435'))
            . $p('Please note that all applications must be submitted online.');
        $wellbeing[$index]['primary_button'] = matrix_orlaith_button('See current vacancies', $careers_url);
    }
    $save($wellbeing_id, $wellbeing, 'Staff wellbeing Working with us');
}

// --- Work experience: nursing team button; drop extra CTAs except useful links ---
$work_id = 240;
$work = get_field('flexible_content_blocks', $work_id);
if (is_array($work)) {
    $new_work = [];
    foreach ($work as $row) {
        if (($row['acf_fc_layout'] ?? '') === 'content' && trim((string) ($row['heading'] ?? '')) === 'Are you a nursing student?') {
            $row['content'] = $p('If you’re interested to learn more, take a look at the different areas our nurses work in across SPMHS.');
            $row['primary_button'] = matrix_orlaith_button('Meet our nursing team', $team_url);
            $new_work[] = $row;
            continue;
        }

        if (($row['acf_fc_layout'] ?? '') === 'content_cta') {
            continue;
        }

        $new_work[] = $row;
    }
    $save($work_id, $new_work, 'Work experience cleanup');
}

// --- How to apply: heading markup + remove empty career development heading ---
$apply_id = 250;
$apply = get_field('flexible_content_blocks', $apply_id);
if (is_array($apply)) {
    foreach ($apply as $index => $row) {
        if (($row['acf_fc_layout'] ?? '') !== 'content') {
            continue;
        }

        $content = (string) ($row['content'] ?? '');
        $content = preg_replace('#<h3>\s*Career development may include\s*</h3>#i', '', $content) ?? $content;
        $content = preg_replace('#<p>\s*<strong>\s*<br\s*/?>\s*H3:\s*Pay and allowances\s*</strong>\s*</p>#i', '<h3>Pay and allowances</h3>', $content) ?? $content;
        $content = preg_replace('#<strong>\s*<br\s*/?>\s*H3:\s*Pay and allowances\s*</strong>#i', '</p><h3>Pay and allowances</h3><p>', $content) ?? $content;
        $content = str_replace('H3: Pay and allowances', '', $content);
        $content = preg_replace('#H2:\s*Next steps#i', '', $content) ?? $content;
        $content = preg_replace('#<p>\s*</strong>\s*<br\s*/?>\s*If you have applied#i', '<h3>Next steps</h3><p>If you have applied', $content) ?? $content;

        if (str_contains($content, 'If you have applied for a nursing role') && ! str_contains($content, '<h3>Next steps</h3>')) {
            $content = str_replace(
                'If you have applied for a nursing role',
                '</p><h3>Next steps</h3><p>If you have applied for a nursing role',
                $content
            );
        }

        $apply[$index]['content'] = $content;
    }
    $save($apply_id, $apply, 'How to apply headings');
}

WP_CLI::success('About Us snag content updates complete.');
