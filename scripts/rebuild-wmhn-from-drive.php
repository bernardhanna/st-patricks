<?php

/**
 * Rebuild Women’s Mental Health Network (WMHN) from Drive Library 3.
 *
 * Source: WMHN page final.docx
 *
 * Page: women-s-mental-health-network (#1325), nested under About Us > Advocacy
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/rebuild-wmhn-from-drive.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

$post_id = (int) (get_page_by_path('women-s-mental-health-network')?->ID ?? 0);
if ($post_id === 0) {
    $post_id = (int) (get_page_by_path('about-us/advocacy/women-s-mental-health-network')?->ID ?? 0);
}
if ($post_id === 0) {
    WP_CLI::error('Could not find women-s-mental-health-network');
}

$advocacy_id = (int) (get_page_by_path('about-us/advocacy')?->ID ?? 0);
$home = untrailingslashit(home_url('/'));
$live = 'https://www.stpatricks.ie';

$p = static function (string $html): string {
    return '<p>' . $html . '</p>';
};
$a = static function (string $url, string $label, string $target = '_blank'): string {
    $attrs = ' href="' . esc_url($url) . '"';
    if ($target !== '') {
        $attrs .= ' target="' . esc_attr($target) . '" rel="noopener noreferrer"';
    }

    return '<a' . $attrs . '>' . esc_html($label) . '</a>';
};
$ul = static function (array $items): string {
    $html = '<ul>';
    foreach ($items as $item) {
        $html .= '<li>' . $item . '</li>';
    }

    return $html . '</ul>';
};

$hero_image = matrix_orlaith_find_image(3890, '324A2553');
if ($hero_image <= 0) {
    $hero_image = matrix_orlaith_find_image(1495, 'women-mental-health-network-launch-min');
}

$privacy = matrix_orlaith_permalink('data-protection-policy');
$getting_help = matrix_orlaith_permalink('getting-help');
$climate_news = get_permalink(3000) ?: ($live . '/media-centre/news/2025/may/womens-mental-health-and-climate-change');
$oram_pdf = wp_get_attachment_url(1644) ?: ($live . '/media/2358/orams_031218.pdf');
$out_of_silence = 'https://www.nwci.ie/images/uploads/Out_of_Silence_Report_-_NWCI_-_2018.pdf';
$covid_podcast = $live . '/media-centre/podcasts/2020/june/the-irish-times-women-s-podcast-covid-19-and-the-impact-on-womens-mental-health';
$silence_podcast = 'https://soundcloud.com/irishtimes-women/ep-256-womens-mental-health-out-of-silence-marian-keyes-at-the-safe-world-summit';

$yt = static function (string $id): string {
    return 'https://www.youtube.com/watch?v=' . $id;
};

$hero_intro = $p('The Women’s Mental Health Network (WMHN) is a network of people and organisations with a committed interest in women’s mental health issues, developed by St Patrick’s Mental Health Services (SPMHS) and the National Women’s Council (NWC).');

$advancing = $p('Gender and gender equality are key determinants of mental health, and understanding and responding to this is an important part of a human rights-based approach to mental health. Improving understanding and responses to women’s mental health issues, including interlinked issues such as gender-based violence, caring responsibilities, and unique needs across the lifespan, is essential to advance women’s right to the highest attainable standard of physical and mental health.')
    . $p('The WMHN has two aims:')
    . $ul([
        'To provide a forum for information-sharing and networking',
        'To advance interdisciplinary and multi-agency collaboration on women’s mental health issues.',
    ])
    . $p('Today, we have over 400 members from across Ireland. To achieve our goals, we share regular newsletters, and hold online and in-person education and networking events, hosted jointly by SPMHS and the NWC. We also explore and advocate for improved understanding and responses to women’s mental health issues in the media and through awareness-raising opportunities.');

$resources_intro = $p('Below, you’ll find a selection of event recordings, along with reports, podcasts and other resources that the WMHN has produced or contributed to. Please be aware that some sensitive topics are discussed in these resources, and know that help is available if you are in distress. You can find details of helplines and crisis supports '
        . $a($getting_help, 'here', '')
        . '.');

$resources = [
    'Neurodiversity and women’s mental health' => $p('We hosted a short, online learning session to explore the important connection between neurodiversity and women\'s mental health. During the session, we spoke to experts on neurodiversity and invited contributions from attendees. The aim of the session was to explore how women’s health experiences can intersect with neurodiversity, while challenging long-standing misconceptions, stigma, and historical taboos.')
        . $p($a($yt('plX0LUlKoXA'), 'Watch the learning session on neurodiversity here') . '.'),

    'Climate crisis and women’s mental health' => $p('The growing mental health impacts of the climate crisis were explored in one of our webinars, with a particular focus on how climate change affects women’s mental health. The event also gave an opportunity to highlight how wide-scale climate action can help protect mental health, and to urge policymakers to integrate climate change and mental health strategies. See more on the speakers and topics '
        . $a($climate_news, 'here', '')
        . '.')
        . $p($a($yt('Ukv1y5_22c8'), 'Watch a recording of the event on climate change here') . '.'),

    'Mental health in mid-life' => $p('We held a webinar to place a spotlight on women’s mental health in middle age. The webinar explored the changes in women\'s bodies and biology, along with sociocultural contexts and social determinants, which can impact women\'s mental health needs during menopause and middle age. The kinds of supports which should be in place for women in health, work and daily life as they enter this mid-life stage were also discussed. Speakers included Breeda Bermingham, founder of the MidLife Women Rock Project; Catherine O\'Keefe, founder of the Menopause Success Summit; Dr Caoimhe Hartely, GP and founder of Menopause Health; and Dr Cliona Loughnane, post-doctoral researcher on the CARE-VISION project in University College Cork.')
        . $p($a($yt('8t9yxZWudvA'), 'Watch the mental health in mid-life webinar here') . '.'),

    'Mental health needs of LGBTQ+ women' => $p('To mark Pride Month, we hosted a webinar on the different mental health needs of LGBTQ+ women in Ireland. The webinar also explored how mental health professionals and services can improve responses to LGBTQ+ women’s needs. This event included talks by:')
        . $ul([
            'Paula Fagan, Chief Executive Officer (CEO) of LGBT Ireland, who focused on older LGBTQ+ women and LGBT Ireland\'s Champions Programme',
            'Moninne Griffith, CEO of BeLonG To, who spoke about younger LGBTQ+ people',
            'Lilith Ferreyra-Carroll, National Community Development Officer for TENI, who offered guidance on how mental health services can be more responsive to trans people’s needs.',
        ])
        . $p($a($yt('VrBS9RbtBiI'), 'Watch the LGBTQ+ women’s mental health event here') . '.'),

    'Migration, mental health and resilience' => $p('We hosted a webinar on women’s experiences of migration and seeking asylum. This event explored how mental health services can become more inclusive and responsive to the mental health needs of migrant and asylum-seeking women. We were delighted to be joined by the following expert speakers:')
        . $ul([
            'Liliana Morales, Psychologist with the Health Service Executive (HSE) Psychology Service for Refugees and Asylum Seekers',
            'Dr Caroline Munyi, Migrant Women\'s Health Coordinator at AkiDwA',
            'Ber Grogan, Policy and Research Manager at Mental Health Reform',
            'Justyna Maslanka, volunteer with Cairde’s Mental Health and Wellbeing Project.',
        ])
        . $p($a($yt('gBBs8kT6QDc'), 'Watch the event on migration and women’s mental health here') . '.'),

    'Gendered impact of COVID-19' => $p('In June 2020, we hosted our first online event, which explored different perspectives on implications of the COVID-19 pandemic for women’s mental health. We were joined by guest speakers Lisa Marmion, Services Development Manager at Safe Ireland; Zoe Hughes, Policy and Research Officer at Care Alliance Ireland; Niamh Grennan, Coordinator of the Dublin Lesbian Line; and Liliana Morales, Psychologist with the Health Service Executive (HSE) Psychology Service for Refugees and Asylum Seekers.')
        . $p('You can ' . $a($yt('UQc8Ul3J2Og'), 'watch a recording of the webinar here') . '.')
        . $p('Our Advocacy Manager, Louise O’Leary, and Clíona Loughnane of the NWC also sat down with Róisín Ingle on The Irish Times Women’s Podcast about the gendered impact of the COVID-19 pandemic and how these affect women’s mental health. '
            . $a($covid_podcast, 'Listen to the podcast episode here')
            . '.'),

    'Strengthening Responses | Domestic violence and mental health' => $p('In 2018, the WMHN held Strengthening Responses, a seminar on domestic violence and women’s mental health. Dr Siân Oram, Lecturer in Women’s Mental Health at the Institute of Psychiatry, Psychology and Neuroscience in King’s College London was one of the speakers.')
        . $p($a($oram_pdf, 'Read Dr Siân Oram’s presentation (PDF)') . '.'),

    'Out of Silence | Women’s mental health report' => $p('The NWC produced Out of Silence, the first ever report to focus on women’s understanding of and needs around mental health. The report explores the things that keep women well, the social and community networks that support them, and the services that they turn to in times of difficulty. '
            . $a($out_of_silence, 'Read Out of Silence here')
            . '.')
        . $p('To mark the launch of the report, our Advocacy Manager, Louise O’Leary, and Orla O’Connor of the NWC joined the Irish Times Women’s Podcast to discuss its findings. '
            . $a($silence_podcast, 'Listen to the Irish Times Women’s Podcast here')
            . '.'),
];

$join_intro = $p('If you would like to learn more about becoming a member of the WMHN, please fill in the form below and we will be in touch with you. You can also withdraw your membership at any time by using the form.');

$rows = [
    matrix_orlaith_hero_row('Women’s Mental Health Network', $hero_intro, $hero_image),
    matrix_orlaith_content_row(
        'Advancing gender-responsive mental healthcare / Advocating for women’s rights to mental health',
        $advancing,
        'white'
    ),
    matrix_orlaith_content_row('Creating and sharing resources', $resources_intro, 'cream'),
    matrix_orlaith_accordion_row($resources, 'default', ''),
    matrix_orlaith_contact_form_row('Joining the network', $join_intro, [
        'form_name' => 'Women’s Mental Health Network registration',
        'email_subject' => 'WMHN – new membership request',
        'recipient_email' => 'sfitzharris@stpatricks.ie',
        'success_message' => 'Thanks! Your WMHN membership request has been sent.',
        'submit_label' => 'Submit',
        'background_type' => 'white',
        'privacy_policy_label' => 'privacy notice',
        'privacy_policy_link' => [
            'title' => 'Privacy Notice',
            'url' => $privacy,
            'target' => '_blank',
        ],
        'consent_items' => [
            [
                'title' => 'Sign up to the mailing list',
                'description' => 'If you would like to receive updates on events and developments at St Patrick’s Mental Health Services (SPMHS), subscribe to the SPMHS mailing list by ticking this box.',
                'required' => 0,
            ],
            [
                'title' => 'By submitting your application, you agree to the terms of service and',
                'description' => '',
                'required' => 1,
            ],
            [
                'title' => 'Consent to share with SPMHS and the NWC',
                'description' => 'The network is run in partnership by SPMHS and the National Women\'s Council (NWC). By signing up to the Women\'s Mental Health Network, you are consenting for your information to be shared with SPMHS and the NWC.',
                'required' => 1,
            ],
        ],
    ]),
];

wp_update_post([
    'ID' => $post_id,
    'post_title' => 'Women’s Mental Health Network',
    'post_name' => 'women-s-mental-health-network',
    'post_parent' => $advocacy_id > 0 ? $advocacy_id : 0,
    'post_status' => 'publish',
]);

matrix_orlaith_save_page($post_id, $rows, true, $hero_image);
matrix_orlaith_set_seo(
    $post_id,
    'Women’s Mental Health Network | St Patrick’s Mental Health Services',
    'Learn about the Women’s Mental Health Network from St Patrick’s Mental Health Services and the National Women’s Council.'
);

WP_CLI::success(sprintf(
    'Rebuilt WMHN #%d → %s (parent=%d, hero=%d)',
    $post_id,
    get_permalink($post_id),
    $advocacy_id,
    $hero_image
));

foreach ((array) get_field('flexible_content_blocks', $post_id) as $i => $row) {
    $layout = (string) ($row['acf_fc_layout'] ?? '?');
    $heading = wp_strip_all_tags((string) ($row['heading'] ?? $row['heading_text'] ?? ''));
    $items = is_array($row['items'] ?? null) ? count($row['items']) : 0;
    $extra = $items > 0 ? " | items={$items}" : '';
    WP_CLI::log("[{$i}] {$layout} {$heading}{$extra}");
}
