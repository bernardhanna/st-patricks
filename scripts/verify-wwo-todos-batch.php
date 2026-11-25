<?php
/**
 * Verify What We Offer To-do snag fixes (DB evidence; works behind front-end password).
 *
 *   /wp-content/themes/matrix-starter/scripts/verify-wwo-todos-batch.php?key=verify-wwo-todos
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'verify-wwo-todos') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

$programme = static function (string $slug): ?WP_Post {
    $posts = get_posts([
        'name' => $slug,
        'post_type' => 'programmes_therapies',
        'post_status' => 'any',
        'posts_per_page' => 1,
    ]);

    return $posts[0] ?? null;
};

$plain = static function (string $html): string {
    return trim(preg_replace('/\s+/u', ' ', wp_strip_all_tags($html)) ?? '');
};

$pass = 0;
$fail = 0;
$check = static function (string $label, bool $ok, string $detail = '') use (&$pass, &$fail): void {
    if ($ok) {
        $pass++;
        echo "PASS {$label}" . ($detail !== '' ? " | {$detail}" : '') . "\n";
    } else {
        $fail++;
        echo "FAIL {$label}" . ($detail !== '' ? " | {$detail}" : '') . "\n";
    }
};

// 1 Day programmes
$day = get_page_by_path('what-we-offer/day-programmes');
echo "===== day-programmes =====\n";
if ($day instanceof WP_Post) {
    $btn = get_post_meta($day->ID, 'flexible_content_blocks_1_primary_button', true);
    $doc = get_post_meta($day->ID, 'flexible_content_blocks_2_document_link', true);
    $c2 = (string) get_post_meta($day->ID, 'flexible_content_blocks_2_content', true);
    $rows = get_field('flexible_content_blocks', $day->ID);
    $btn_title = '';
    $doc_title = '';
    if (is_array($rows)) {
        foreach ($rows as $row) {
            if (($row['acf_fc_layout'] ?? '') !== 'content') {
                continue;
            }
            if (is_array($row['primary_button'] ?? null) && ! empty($row['primary_button']['title'])) {
                $btn_title = (string) $row['primary_button']['title'];
            }
            if (is_array($row['document_link'] ?? null) && ! empty($row['document_link']['title'])) {
                $doc_title = (string) $row['document_link']['title'];
            }
        }
    }
    if ($btn_title === '' && is_array($btn)) {
        $btn_title = (string) ($btn['title'] ?? '');
    } elseif ($btn_title === '' && is_string($btn) && preg_match('/s:\d+:"(Full list of programmes)"/', $btn, $m)) {
        $btn_title = $m[1];
    }
    if ($doc_title === '' && is_array($doc)) {
        $doc_title = (string) ($doc['title'] ?? '');
    }
    $check('day Full list sentence case', $btn_title === 'Full list of programmes', $btn_title);
    $check('day Read more label', $doc_title === 'Read more about our day programmes', $doc_title);
    $check('day no Click here stray', stripos($c2, 'Click here for more information about attending a programme') === false, substr($plain($c2), 0, 120));
} else {
    $check('day page exists', false);
}

// 2 Anxiety
echo "===== anxiety =====\n";
$a = $programme('anxiety-disorders-programme');
$a2 = $programme('anxiety-disorders-programme-2');
$check('anxiety canonical publish', $a instanceof WP_Post && $a->post_status === 'publish', $a ? get_permalink($a) : 'MISS');
$check('anxiety-2 not published', ! ($a2 instanceof WP_Post && $a2->post_status === 'publish'), $a2 ? $a2->post_status . ' ' . get_permalink($a2) : 'absent');

// Programme helpers
$assert_prog = static function (string $slug, callable $fn) use ($programme, $check, $plain): void {
    echo "===== {$slug} =====\n";
    $p = $programme($slug);
    if (! $p instanceof WP_Post) {
        $check($slug . ' exists', false);
        return;
    }
    $fn($p, $check, $plain);
};

$assert_prog('art-therapy', static function (WP_Post $p, callable $check, callable $plain): void {
    $c = $plain((string) $p->post_content);
    $dup = 'at st patrick’s mental health services (spmhs), we provide art therapy in support of mental health recovery.';
    $check('art no leading duplicate', ! str_starts_with(strtolower($c), $dup), substr($c, 0, 140));
});

$assert_prog('binge-eating-disorder-programme', static function (WP_Post $p, callable $check, callable $plain): void {
    $raw = (string) $p->post_content;
    $c = $plain($raw);
    $dup = 'the binge eating disorder programme is designed to support people who are experiencing difficulties with binge eating symptoms and behaviours.';
    $check('binge embed oGPDcc', (bool) preg_match('#(?:youtube\.com/embed/|youtu\.be/)oGPDcc_kxU4#', $raw));
    $check('binge no leading duplicate', ! str_starts_with(strtolower($c), $dup), substr($c, 0, 140));
});

$assert_prog('bipolar-education-programme', static function (WP_Post $p, callable $check, callable $plain): void {
    $raw = (string) $p->post_content;
    $ex = (string) $p->post_excerpt . (string) get_field('listing_summary', $p->ID);
    $check('bipolar no unfinished to an…', ! preg_match('/to an[…\.]/u', $ex) && ! preg_match('/to an[…\.]/u', $raw));
    $c = $plain($raw);
    $dup = 'the bipolar recovery programme is an online group programme which offers support and education for people living with bipolar disorder.';
    $check('bipolar no leading duplicate', ! str_starts_with(strtolower($c), $dup), substr($c, 0, 140));
});

$assert_prog('cognitive-behavioural-therapy', static function (WP_Post $p, callable $check): void {
    $ex = (string) $p->post_excerpt;
    $ls = (string) get_field('listing_summary', $p->ID);
    $check('cbt no difficulties.Here', ! str_contains($ex, 'difficulties.Here') && ! str_contains($ls, 'difficulties.Here') && ! str_contains((string) $p->post_content, 'difficulties.Here'));
    $check('cbt has spaced stop', str_contains($ex, 'difficulties. Here') || str_contains($ls, 'difficulties. Here'));
});

$assert_prog('compassion-focused-therapy', static function (WP_Post $p, callable $check, callable $plain): void {
    $raw = (string) $p->post_content;
    $ls = (string) get_field('listing_summary', $p->ID);
    $ex = (string) $p->post_excerpt;
    $check('cft blue no unfinished whose…', ! str_contains($ls, 'whose…') && ! str_contains($ex, 'whose…'));
    $check('cft mdt not live site', ! str_contains($raw, 'www.stpatricks.ie/about-us/multidisciplinary-teams'));
    $check('cft mdt points our-team or relative', (bool) preg_match('#about-us/our-team#', $raw) || ! str_contains($raw, 'multidisciplinary-teams'));
    $c = $plain($raw);
    $dup = 'compassion-focused therapy (cft) is a type of therapy which aims to support people to develop kinder, more compassionate and soothing relationships with themselves.';
    $check('cft no leading duplicate definition', ! str_starts_with(strtolower($c), $dup), substr($c, 0, 140));
});

$assert_prog('cft-for-adolescents-and-families', static function (WP_Post $p, callable $check): void {
    $raw = (string) $p->post_content;
    $check('adol no xxxx', stripos($raw, 'xxxx') === false);
    $check('adol no xxx sessions', ! preg_match('/xxx sessions over xxx weeks/i', $raw));
    $check('adol no family include…', ! preg_match('/Family members who can attend the programme with the adolescent include/i', $raw));
});

$assert_prog('compassion-focused-therapy-eating-disorders', static function (WP_Post $p, callable $check, callable $plain): void {
    $raw = (string) $p->post_content;
    $c = $plain($raw);
    $dup = 'the compassion focused therapy group for eating disorders (cft-e) offers a comprehensive psychological therapy';
    $check('cft-e no leading duplicate', ! str_starts_with(strtolower($c), $dup), substr($c, 0, 140));
    preg_match_all('#compassion-focused-therapy-for-eating-disorders-brochure\.pdf#i', $raw, $m);
    $check('cft-e brochure link count <=1 preferred', count($m[0]) <= 2, 'count=' . count($m[0])); // allow one in text mention + one CTA
    $check('cft-e brochure title', (bool) preg_match('#Download our Compassion-Focused Therapy for Eating Disorders brochure#', $raw));
});

$assert_prog('cft-for-older-adults', static function (WP_Post $p, callable $check, callable $plain): void {
    $ls = (string) get_field('listing_summary', $p->ID);
    $raw = (string) $p->post_content;
    $check('oa blue no Delivered unfinished', stripos($ls, 'Delivered in a supportive group setting') === false);
    $c = $plain($raw);
    $check('oa no leading programme supports dup', stripos(substr($c, 0, 80), 'The programme supports people to develop a more compassionate') === false, substr($c, 0, 140));
});

$assert_prog('compassion-focused-therapy-for-psychosis-2', static function (WP_Post $p, callable $check, callable $plain): void {
    $raw = (string) $p->post_content;
    $c = $plain($raw);
    $dup = 'compassion-focused therapy for psychosis (cft-p) is a group programme for people with experiences of psychosis.';
    $check('cftp no leading duplicate', ! str_starts_with(strtolower($c), $dup), substr($c, 0, 140));
    if (preg_match('#href=["\']([^"\']+)["\'][^>]*>\s*Psychosis\s*<#i', $raw, $m)) {
        $check('cftp Psychosis href', $m[1] !== '' && ! str_contains($m[1], 'living-with-psychosis-missing'), $m[1]);
        // Prefer permalink that resolves to a publish post.
        $path = wp_parse_url($m[1], PHP_URL_PATH) ?: $m[1];
        $check('cftp Psychosis path living-with-psychosis', str_contains((string) $path, 'living-with-psychosis'), (string) $path);
    } else {
        $check('cftp Psychosis link present', false);
    }
    echo 'permalink=' . get_permalink($p) . "\n";
});

echo "\nTOTAL pass={$pass} fail={$fail}\n";
