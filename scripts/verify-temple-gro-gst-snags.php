<?php
/**
 * Verify Temple / GRO / GST snag fixes on local/staging.
 *
 *   /wp-content/themes/matrix-starter/scripts/verify-temple-gro-gst-snags.php?key=verify-temple-gro-gst
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $allowed = str_contains($host, 'localhost')
        || str_contains($host, 'matrix-test.com')
        || in_array($remote, ['127.0.0.1', '::1'], true);
    if (! $allowed || ($_GET['key'] ?? '') !== 'verify-temple-gro-gst') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

$plain = static function (string $html): string {
    $text = html_entity_decode(wp_strip_all_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

    return trim($text);
};

$by_slug = static function (string $slug): ?WP_Post {
    $posts = get_posts([
        'name' => $slug,
        'post_type' => 'programmes_therapies',
        'post_status' => 'any',
        'posts_per_page' => 1,
    ]);

    return $posts[0] ?? null;
};

$psych = get_page_by_path('about-us/our-team/psychologists');
$psych_url = $psych instanceof WP_Post ? untrailingslashit((string) get_permalink($psych)) : '';

$pass = true;
$check = static function (string $label, bool $ok, string $detail = '') use (&$pass): void {
    if (! $ok) {
        $pass = false;
    }
    echo ($ok ? 'PASS' : 'FAIL') . " {$label}" . ($detail !== '' ? " ({$detail})" : '') . "\n";
};

$psych_href_ok = static function (string $html) use ($psych_url): bool {
    if ($psych_url === '') {
        // Unlinked expected.
        return ! preg_match('#<(?:a)\b[^>]*>\s*(?:Psychology Department|Department of Psychology|Psychologists|psychologists)\s*</a>#iu', $html);
    }
    if (! preg_match_all('#<a\b[^>]*href=(["\'])([^"\']+)\1[^>]*>(.*?)</a>#is', $html, $m, PREG_SET_ORDER)) {
        return true;
    }
    foreach ($m as $a) {
        $text = trim(wp_strip_all_tags($a[3]));
        if (! preg_match('#^(Psychology Department|Department of Psychology|Psychologists|psychologists)$#iu', $text)) {
            continue;
        }
        if (untrailingslashit($a[2]) !== $psych_url) {
            return false;
        }
    }

    return true;
};

// Temple
$temple = $by_slug('temple-formulation-group');
$temple_dup = 'The Temple Formulation Group is a group programme which aims to support people experiencing mental health difficulties.';
if (! $temple) {
    $check('temple exists', false);
} else {
    $c = (string) $temple->post_content;
    $ls = (string) get_field('listing_summary', $temple->ID);
    preg_match_all('#temple-formulation[^"\']*\.pdf#i', $c, $fm);
    $check('temple leaflet count <=1', count($fm[0]) <= 1, 'count=' . count($fm[0]));
    $check('temple preferred CTA', (bool) preg_match('#Read the Temple Formulation Group leaflet here#i', $c));
    $check('temple body no dup intro', ! str_contains(strtolower($plain($c)), strtolower($temple_dup)));
    $check('temple listing keeps intro', str_contains(strtolower($plain($ls)), strtolower($temple_dup)));
    $check('temple psych link', $psych_href_ok($c));
    echo 'temple permalink=' . get_permalink($temple) . "\n";
}

// GRO
$gro = $by_slug('group-radical-openness');
$gro_dup = 'Group Radical Openness (GRO) is a group therapy programme for people who have difficulties with costly overcontrol. This includes keeping others at a distance, having trouble showing emotions and a strong desire for control.';
$gro_label = 'Download our Group Radical Openness programme brochure';
if (! $gro) {
    $check('gro exists', false);
} else {
    $c = (string) $gro->post_content;
    $ls = (string) get_field('listing_summary', $gro->ID);
    preg_match_all('#group-radical-openness[^"\']*\.pdf#i', $c, $fm);
    $check('gro brochure count <=1', count($fm[0]) <= 1, 'count=' . count($fm[0]));
    $check('gro download-our CTA', str_contains($c, $gro_label));
    $check('gro no filename CTA', ! preg_match('#programme brochure\.pdf\s*\(#i', $c));
    $check('gro body no dup intro', ! str_contains(strtolower($plain($c)), strtolower($gro_dup)));
    $check('gro listing keeps intro', str_contains(strtolower($plain($ls)), strtolower($gro_dup)));
    $check('gro psych link', $psych_href_ok($c));
    echo 'gro permalink=' . get_permalink($gro) . "\n";
}

// GST
$gst = $by_slug('group-schema-therapy');
$gst_dup = 'Group Schema Therapy (GST) is a group therapy programme for people with experiences of childhood or attachment trauma.';
if (! $gst) {
    $check('gst exists', false);
} else {
    $c = (string) $gst->post_content;
    $ls = (string) get_field('listing_summary', $gst->ID);
    $check('gst body no dup intro', ! str_contains(strtolower($plain($c)), strtolower($gst_dup)));
    $check('gst listing keeps intro', str_contains(strtolower($plain($ls)), strtolower($gst_dup)));
    $check('gst psych link', $psych_href_ok($c));
    echo 'gst permalink=' . get_permalink($gst) . "\n";
}

echo $psych_url !== '' ? "psych_dest={$psych_url}\n" : "psych_dest=NONE\n";
echo $pass ? "RESULT=PASS\n" : "RESULT=FAIL\n";
