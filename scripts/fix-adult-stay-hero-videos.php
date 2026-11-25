<?php
/**
 * Adult stay: move long hero copy into sections + embed inpatient video slider.
 *
 * - Slim hero to a short intro
 * - Add intro content section (locations / what this page covers)
 * - Add video_showcase slider from the adult inpatient YouTube playlist
 * - Add Willow Grove note as its own content section
 *
 * HTTP (staging/local):
 *   /wp-content/themes/matrix-starter/scripts/fix-adult-stay-hero-videos.php?key=fix-adult-stay-hero-videos
 *
 * CLI:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/fix-adult-stay-hero-videos.php
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-adult-stay-hero-videos') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

$playlist_id = 'PL9Qr7kXsp_qQ81T9NDmg3q4IS3yvCDiHO';
$playlist_url = 'https://www.youtube.com/playlist?list=' . $playlist_id;

$post = get_page_by_path('service-users-and-visitors/your-stay-in-hospital-as-an-adult');
if (! $post instanceof WP_Post) {
    echo "FAIL page not found\n";
    exit(1);
}

$post_id = (int) $post->ID;
$rows = get_field('flexible_content_blocks', $post_id);
if (! is_array($rows) || $rows === []) {
    echo "FAIL #{$post_id} has no flexible_content_blocks\n";
    exit(1);
}

$hero_short = '<p>Here, we share some useful information if you are receiving inpatient mental healthcare with St Patrick’s Mental Health Services (SPMHS).</p>';

$spuh = matrix_orlaith_a('locations/st-patricks-university-hospital', 'St Patrick’s University Hospital (SPUH)');
$lucan = matrix_orlaith_a('locations/st-patricks-hospital-lucan', 'St Patrick’s Hospital Lucan');
$intro_html = '<p>We provide inpatient care to adult service users in '
    . $spuh
    . ' in Dublin 8 and '
    . $lucan
    . '. Below, we will take you through some practical information about how to prepare for your stay, what the admissions process is like, and what to expect from inpatient care in SPMHS.</p>';

$willow = matrix_orlaith_a(
    'service-users-and-visitors/your-care-with-willow-grove/your-stay-in-hospital-as-an-adolescent',
    'Willow Grove Adolescent Unit'
);

$slides = [];
$feed = wp_remote_get(
    'https://www.youtube.com/feeds/videos.xml?playlist_id=' . rawurlencode($playlist_id),
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
            $slides[] = [
                'url' => 'https://www.youtube.com/watch?v=' . $vid,
                'title' => $title,
                'caption' => '',
            ];
        }
    }
}

if ($slides === []) {
    // Hard fallback if YouTube feed is unreachable.
    $slides = [
        ['url' => 'https://www.youtube.com/watch?v=jZ6xM4MSSNE', 'title' => 'Exploring our adult inpatient services'],
        ['url' => 'https://www.youtube.com/watch?v=_Qz7oaebdO8', 'title' => 'What to expect as an adult inpatient'],
        ['url' => 'https://www.youtube.com/watch?v=mQmYjdN9f6E', 'title' => 'Our inpatient facilities and activities'],
        ['url' => 'https://www.youtube.com/watch?v=2sQxJkjJJ9o', 'title' => 'What is a multidisciplinary team, or MDT?'],
        ['url' => 'https://www.youtube.com/watch?v=GCCl7eVSwPE', 'title' => 'What to expect as an inpatient service user using Your Portal'],
    ];
}

$video_row = matrix_orlaith_video_row(
    'Videos about our inpatient services',
    '<p>Watch videos about our adult inpatient services below, or <a class="text-[#024B79] underline underline-offset-2 hover:no-underline font-medium" href="'
        . esc_url($playlist_url)
        . '" target="_blank" rel="noopener noreferrer">open the full playlist on YouTube</a>.</p>',
    $slides
);
$video_row['layout_style'] = 'feature_slider';
$video_row['section_background'] = 'linear-gradient(-80.44deg, #F8F6F3 3.24%, #F5F6ED 90.88%)';
$video_row['vertical_padding'] = 'default';

$intro_row = matrix_orlaith_content_row(
    '',
    $intro_html,
    'white',
    0,
    'image_left',
    ['vertical_padding' => 'no_bottom']
);

// Navy style bar (callout). Fallback to navy content banner if callout is stripped on save.
$willow_message = 'Please note that inpatient care for adolescent service users is provided through '
    . $willow
    . '.';
$willow_callout = [
    'acf_fc_layout' => 'callout_bar',
    'message' => $willow_message,
    'bar_style' => 'navy',
];
$willow_content_fallback = matrix_orlaith_content_row(
    '',
    '<p>' . $willow_message . '</p>',
    'navy',
    0,
    'image_left',
    [
        'vertical_padding' => 'none',
        'color_scheme' => 'inverse',
    ]
);

// Drop any previous injects from a re-run (match by heading / telltale copy).
$cleaned = [];
foreach ($rows as $row) {
    if (! is_array($row)) {
        continue;
    }
    $layout = (string) ($row['acf_fc_layout'] ?? '');
    $heading = trim((string) ($row['heading'] ?? ''));
    $content = (string) ($row['content'] ?? '');
    $intro = (string) ($row['intro'] ?? '');
    $message = (string) ($row['message'] ?? '');
    $blob = $content . "\n" . $message;

    if ($layout === 'video_showcase' && (
        $heading === 'Videos about our inpatient services'
        || str_contains($intro, 'adult inpatient services')
    )) {
        continue;
    }

    if ($layout === 'content' && $heading === '' && str_contains($content, 'We provide inpatient care to adult service users')) {
        continue;
    }

    if (
        str_contains($blob, 'inpatient care for adolescent service users')
        && in_array($layout, ['content', 'callout_bar'], true)
    ) {
        continue;
    }

    $cleaned[] = $row;
}
$rows = $cleaned;

if (($rows[0]['acf_fc_layout'] ?? '') !== 'hero_with_breadcrumbs') {
    echo "FAIL expected hero_with_breadcrumbs as first block\n";
    exit(1);
}

$rows[0]['content'] = $hero_short;

$tail = array_slice($rows, 1);
$rows = array_merge(
    [$rows[0]],
    [$intro_row, $video_row, $willow_callout],
    $tail
);

$tighten_pairs = static function (array $rows): array {
    for ($i = 0, $count = count($rows); $i < $count - 1; $i++) {
        $current = $rows[$i] ?? null;
        $next = $rows[$i + 1] ?? null;
        if (! is_array($current) || ! is_array($next)) {
            continue;
        }
        if (($current['acf_fc_layout'] ?? '') !== 'content') {
            continue;
        }
        if (($next['acf_fc_layout'] ?? '') !== 'content_accordion') {
            continue;
        }
        // Keep normal top spacing, kill the large bottom gap above the accordion
        // on both mobile and desktop (shared no_bottom only zeros pb from lg up).
        $rows[$i]['vertical_padding'] = 'custom';
        $rows[$i]['padding_settings'] = [
            ['screen_size' => 'mob', 'padding_top' => '3', 'padding_bottom' => '0'],
            ['screen_size' => 'lg', 'padding_top' => '6.25', 'padding_bottom' => '0'],
        ];
        $next_pad = (string) ($rows[$i + 1]['vertical_padding'] ?? '');
        if ($next_pad === '' || $next_pad === 'default') {
            $rows[$i + 1]['vertical_padding'] = 'bottom_only';
        }
    }

    return $rows;
};

$rows = $tighten_pairs($rows);
matrix_orlaith_save_page($post_id, $rows, true, 0);

$verify = get_field('flexible_content_blocks', $post_id);
$layouts = is_array($verify)
    ? array_map(static fn ($r) => (string) ($r['acf_fc_layout'] ?? ''), $verify)
    : [];

if (! in_array('callout_bar', $layouts, true)) {
    // ACF dropped the callout layout — use a navy content banner instead.
    $rows = array_merge(
        [$rows[0]],
        [$intro_row, $video_row, $willow_content_fallback],
        $tail
    );
    $rows = $tighten_pairs($rows);
    matrix_orlaith_save_page($post_id, $rows, true, 0);
    $verify = get_field('flexible_content_blocks', $post_id);
    $layouts = is_array($verify)
        ? array_map(static fn ($r) => (string) ($r['acf_fc_layout'] ?? ''), $verify)
        : [];
    echo "WARN callout_bar not persisted; used navy content fallback\n";
}

echo "OK adult stay #{$post_id}: blocks=" . count($layouts)
    . ' slides=' . count($slides)
    . ' layouts=' . implode(',', $layouts)
    . "\n";
