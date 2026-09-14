<?php

/**
 * Rebuild About our St Patrick's at Home Service page layout.
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/rebuild-about-st-patricks-at-home.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

$post_id = 266;
$page = get_post($post_id);
if (! $page instanceof WP_Post) {
    WP_CLI::error('Page 266 not found');
}

$home = untrailingslashit(home_url('/'));
$videos_url = 'https://www.youtube.com/playlist?list=PL9Qr7kXsp_qR0ALEUVQGqrh2Lydnp6ZVl';

$remote_practical = get_page_by_path('what-you-need-to-know-about-remote-care');
$remote_practical_url = $remote_practical instanceof WP_Post
    ? untrailingslashit((string) get_permalink($remote_practical))
    : $home . '/what-you-need-to-know-about-remote-care/';

$urls = [
    'appointments' => $remote_practical_url,
    'mdt' => $home . '/about-us/our-team/',
    'pharmacy' => $home . '/about-us/pharmacists/',
    'medication_choices' => $home . '/mental-health-medication-choices/',
    'medication' => $home . '/service-users-and-visitors/medication/',
    'dean' => $home . '/what-we-offer/outpatient-care-dean-clinics/',
    'info_centre' => $home . '/getting-help/information-centre/',
    'suits' => $home . '/service-user-it-support/',
    'remote_practical' => $remote_practical_url,
    'portal' => $home . '/about-your-portal/',
    'portal_register' => $home . '/register-for-your-portal/',
    'youtube' => 'https://www.youtube.com/@StPatricksMentalHealthServices',
    'portal_library' => $home . '/about-your-portal/',
];

$link = static function (string $label, string $url, bool $external = false) use ($home): string {
    $target = $external ? ' target="_blank" rel="noopener noreferrer"' : '';

    return '<a href="' . esc_url($url) . '"' . $target . '>' . esc_html($label) . '</a>';
};

$parse_h3_sections = static function (string $html): array {
    $intro = '';
    $items = [];
    if (! preg_match_all('/<h3>(.*?)<\/h3>(.*?)(?=<h3>|$)/is', $html, $matches, PREG_SET_ORDER)) {
        return ['intro' => trim($html), 'items' => []];
    }
    $first_pos = stripos($html, '<h3>');
    if ($first_pos !== false && $first_pos > 0) {
        $intro = trim(substr($html, 0, $first_pos));
    }
    foreach ($matches as $match) {
        $title = trim(wp_strip_all_tags($match[1]));
        $body = trim($match[2]);
        $body = preg_replace('/<p>\*videos about the homecare service and what to expect from it\*<\/p>/i', '', $body) ?? $body;
        if ($title === '' || $body === '') {
            continue;
        }
        $items[$title] = $body;
    }

    return ['intro' => $intro, 'items' => $items];
};

$rewrite_links = static function (string $html) use ($urls): string {
    // Longer paths first so /multidisciplinary-teams/pharmacy is not partially rewritten.
    $map = [
        'https://www.stpatricks.ie/about-us/multidisciplinary-teams/pharmacy' => $urls['pharmacy'],
        'https://www.stpatricks.ie/media-centre/news/2022/may/new-appointment-notifications' => $urls['appointments'],
        'https://www.stpatricks.ie/media-centre/blogs-articles/2023/may/mental-health-medication-choices' => $urls['medication_choices'],
        'https://www.stpatricks.ie/care-treatment/outpatient-clinics/about-the-dean-clinics' => $urls['dean'],
        'https://www.stpatricks.ie/care-treatment/your-portal/service-user-it-support' => $urls['suits'],
        'https://www.stpatricks.ie/care-treatment/our-services/remote-services/practical-information-remote-services' => $urls['remote_practical'],
        'https://www.stpatricks.ie/care-treatment/your-portal/about-your-portal' => $urls['portal'],
        'https://www.stpatricks.ie/care-treatment/your-portal/register' => $urls['portal_register'],
        'https://www.stpatricks.ie/care-treatment/your-portal/guides-to-your-portal' => $urls['portal'],
        'https://www.stpatricks.ie/care-treatment/medication' => $urls['medication'],
        'https://www.stpatricks.ie/getting-help/information-centre' => $urls['info_centre'],
        'https://www.stpatricks.ie/about-us/multidisciplinary-teams' => $urls['mdt'],
        'https://www.stpatricks.ie/media-centre/news/2022/may/new-appointment-notifications' => $urls['appointments'],
        'http://www.stpatricks.ie/media-centre/news/2022/may/new-appointment-notifications' => $urls['appointments'],
    ];

    foreach ($map as $from => $to) {
        $html = str_replace($from, $to, $html);
    }

    $html = preg_replace(
        '/href="https:\/\/www\.youtube\.com\/watch\?v=krG9n9xUJuo"/i',
        'href="' . esc_url($urls['portal_library']) . '"',
        $html
    ) ?? $html;

    return $html;
};

$existing = get_field('flexible_content_blocks', $post_id);
if (! is_array($existing) || $existing === []) {
    WP_CLI::error('No flexible content on page 266');
}

$hero = $existing[0];
$hero['layout_style'] = 'title_accent';
$hero['heading'] = "Your time in St Patrick’s at Home";
$hero['current_crumb_label'] = "Your time in St Patrick’s at Home";
$hero['content'] = '<p>Through our St Patrick’s at Home service, you can receive high-quality mental healthcare directly in your own home. Here, we go through some useful information about how admission to homecare works, what a typical day is like, and how you can make the most of your experience in homecare.</p>';
$hero['background_color'] = '#C6ECF4';
$hero['accent_color'] = '#6FC9C0';

$expect_html = '<p>St Patrick’s at Home, or homecare, offers comprehensive, multidisciplinary mental healthcare to you in the comfort of your own home. It combines the high level of support typically found in inpatient care with the convenience of remote access.</p>'
    . '<p>You can find out more below on what to expect from your time in homecare, from your referral and admission right through to your discharge.</p>';

$find_section = static function (array $rows, string $heading) use ($parse_h3_sections, $rewrite_links): array {
    $intro = '';
    $items = [];
    $count = count($rows);
    for ($i = 0; $i < $count; $i++) {
        $row = $rows[$i];
        if (($row['acf_fc_layout'] ?? '') !== 'content' || ($row['heading'] ?? '') !== $heading) {
            continue;
        }
        $intro = trim((string) ($row['content'] ?? ''));
        $next = $rows[$i + 1] ?? null;
        if (is_array($next) && ($next['acf_fc_layout'] ?? '') === 'content_accordion' && is_array($next['items'] ?? null)) {
            foreach ($next['items'] as $item) {
                $title = trim((string) ($item['title'] ?? ''));
                $body = '';
                foreach (($item['content_rows'] ?? []) as $cr) {
                    if (($cr['row_type'] ?? '') === 'text') {
                        $body .= (string) ($cr['content'] ?? '');
                    }
                }
                $body = trim($rewrite_links($body));
                if ($title !== '' && $body !== '') {
                    $items[$title] = $body;
                }
            }
        } else {
            $parsed = $parse_h3_sections($rewrite_links($intro));
            $intro = $parsed['intro'];
            $items = $parsed['items'];
        }
        break;
    }

    return ['intro' => $intro, 'items' => $items];
};

$started = $find_section($existing, 'Getting started in homecare');
$receiving = $find_section($existing, 'Receiving care');
$making = $find_section($existing, 'Making the most of homecare');

if ($started['intro'] !== '') {
    $started['intro'] = str_replace(
        'to see on what happens',
        'to see what happens',
        $started['intro']
    );
}

$playlist_slides = [];
$feed = wp_remote_get(
    'https://www.youtube.com/feeds/videos.xml?playlist_id=PL9Qr7kXsp_qR0ALEUVQGqrh2Lydnp6ZVl',
    ['timeout' => 30]
);
if (! is_wp_error($feed)) {
    $xml = simplexml_load_string((string) wp_remote_retrieve_body($feed));
    if ($xml instanceof SimpleXMLElement) {
        $xml->registerXPathNamespace('atom', 'http://www.w3.org/2005/Atom');
        $xml->registerXPathNamespace('yt', 'http://www.youtube.com/xml/schemas/2015');
        foreach ($xml->xpath('//atom:entry') ?: [] as $entry) {
            $entry->registerXPathNamespace('atom', 'http://www.w3.org/2005/Atom');
            $entry->registerXPathNamespace('yt', 'http://www.youtube.com/xml/schemas/2015');
            $ids = $entry->xpath('./yt:videoId');
            $titles = $entry->xpath('./atom:title');
            $vid = $ids ? (string) $ids[0] : '';
            $title = $titles ? (string) $titles[0] : '';
            if ($vid === '') {
                continue;
            }
            $playlist_slides[] = [
                'url' => 'https://www.youtube.com/watch?v=' . $vid,
                'title' => $title,
                'caption' => '',
            ];
        }
    }
}

$video_row = matrix_orlaith_video_row(
    'Homecare videos',
    '<p>Watch our adult Homecare videos below, or <a class="text-[#024B79] underline underline-offset-2 hover:no-underline font-medium" href="' . esc_url($videos_url) . '" target="_blank" rel="noopener noreferrer">open the full playlist on YouTube</a>.</p>',
    $playlist_slides
);
$video_row['layout_style'] = 'feature_slider';
$video_row['section_background'] = 'linear-gradient(-80.44deg, #F8F6F3 3.24%, #F5F6ED 90.88%)';

$rows = [
    $hero,
    matrix_orlaith_content_row(
        'What to expect from homecare',
        $expect_html,
        'white',
        0,
        'image_left',
        [
            'primary_button' => matrix_orlaith_button('Watch on YouTube', $videos_url, '_blank'),
            'primary_button_variant' => 'filled',
            'vertical_padding' => 'default',
        ]
    ),
    $video_row,
    matrix_orlaith_content_row(
        'Getting started in homecare',
        $started['intro'] !== '' ? $started['intro'] : '<p>Use the dropdown menu here to see what happens when you are referred and admitted to homecare with St Patrick’s Mental Health Services (SPMHS).</p>',
        'cream',
        0,
        'image_left',
        ['vertical_padding' => 'default']
    ),
];

$started_acc = matrix_orlaith_accordion_row($started['items'], 'default', '');
$started_acc['vertical_padding'] = 'bottom_only';
$started_acc['section_background'] = '#F8F6F3';
$rows[] = $started_acc;

$rows[] = matrix_orlaith_content_row(
    'Receiving care',
    $receiving['intro'] !== '' ? $receiving['intro'] : '<p>See more below about your care team and what to expect from your treatment.</p>',
    'white',
    0,
    'image_left',
    ['vertical_padding' => 'default']
);

$receiving_acc = matrix_orlaith_accordion_row($receiving['items'], 'default', '');
$receiving_acc['vertical_padding'] = 'bottom_only';
$rows[] = $receiving_acc;

$rows[] = matrix_orlaith_content_row(
    'Making the most of homecare',
    $making['intro'] !== '' ? $making['intro'] : '<p>While you are in homecare, you can choose from a diverse range of supports which can progress your recovery and help you to get the most from your care and treatment.</p>',
    'cream',
    0,
    'image_left',
    ['vertical_padding' => 'default']
);

$making_acc = matrix_orlaith_accordion_row($making['items'], 'default', '');
$making_acc['vertical_padding'] = 'bottom_only';
$making_acc['section_background'] = '#F8F6F3';
$rows[] = $making_acc;

update_field('flexible_content_blocks', $rows, $post_id);
update_post_meta($post_id, '_matrix_drive3_import', gmdate('c'));

if (function_exists('acf_get_store')) {
    acf_get_store('values')->reset();
}
clean_post_cache($post_id);

$verify = get_field('flexible_content_blocks', $post_id);
WP_CLI::log('Rebuilt page 266 with ' . count($verify) . ' blocks:');
foreach ($verify as $i => $row) {
    $layout = $row['acf_fc_layout'] ?? '?';
    $heading = $row['heading'] ?? '';
    $count = is_array($row['items'] ?? null) ? count($row['items']) : 0;
    $btn = is_array($row['primary_button'] ?? null) ? ($row['primary_button']['title'] ?? '') : '';
    WP_CLI::log("[$i] $layout heading={$heading} items={$count} button={$btn}");
}
WP_CLI::success('About St Patrick\'s at Home Service updated');
