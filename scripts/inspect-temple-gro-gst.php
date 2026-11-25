<?php
/**
 * Inspect Temple / GRO / GST programme snag content (local/staging).
 *
 *   /wp-content/themes/matrix-starter/scripts/inspect-temple-gro-gst.php?key=inspect-temple-gro-gst
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $allowed = str_contains($host, 'localhost')
        || str_contains($host, 'matrix-test.com')
        || in_array($remote, ['127.0.0.1', '::1'], true);
    if (! $allowed || ($_GET['key'] ?? '') !== 'inspect-temple-gro-gst') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

$dump_anchors = static function (string $html, string $prefix = ''): void {
    if ($html === '') {
        return;
    }
    if (preg_match_all('#<a\b[^>]*href=(["\'])([^"\']+)\1[^>]*>(.*?)</a>#is', $html, $m, PREG_SET_ORDER)) {
        foreach ($m as $a) {
            $text = trim(wp_strip_all_tags($a[3]));
            if (preg_match('#Psycholog|brochure|leaflet|Department|Read the Temple|Group Radical#i', $text . $a[2])) {
                echo "{$prefix}ANCHOR text=[{$text}] href={$a[2]}\n";
            }
        }
    }
};

$slugs = ['temple-formulation-group', 'group-radical-openness', 'group-schema-therapy'];
foreach ($slugs as $slug) {
    $posts = get_posts([
        'name' => $slug,
        'post_type' => 'programmes_therapies',
        'post_status' => 'any',
        'posts_per_page' => 5,
    ]);
    echo "\n######## SLUG {$slug} count=" . count($posts) . " ########\n";
    foreach ($posts as $p) {
        echo "ID={$p->ID} status={$p->post_status} modified={$p->post_modified}\n";
        echo 'permalink=' . get_permalink($p) . "\n";
        echo 'listing=' . substr(wp_strip_all_tags((string) get_field('listing_summary', $p->ID)), 0, 220) . "\n";
        echo 'excerpt=' . substr(wp_strip_all_tags((string) $p->post_excerpt), 0, 220) . "\n";
        $content = (string) $p->post_content;
        echo 'content_len=' . strlen($content) . "\n";
        $dump_anchors($content);
        if (preg_match_all('#(?:temple-formulation|group-radical-openness)[^"\']+#i', $content, $fm)) {
            echo 'FILES: ' . implode(' | ', array_unique($fm[0])) . "\n";
        }
        echo 'INTRO_CLASS=' . (preg_match('#class=["\'][^"\']*\bintro\b[^"\']*["\']#', $content) ? 'YES' : 'NO') . "\n";
        echo 'BODY_START=' . substr(preg_replace('/\s+/', ' ', wp_strip_all_tags($content)) ?? '', 0, 240) . "\n";
        $rows = get_field('flexible_content_blocks', $p->ID);
        if (! is_array($rows)) {
            continue;
        }
        foreach ($rows as $i => $row) {
            foreach (['content', 'text_content', 'intro_text', 'body'] as $f) {
                $html = (string) ($row[$f] ?? '');
                if ($html === '') {
                    continue;
                }
                echo "FLEXI[{$i}].{$f} len=" . strlen($html) . "\n";
                $dump_anchors($html, '  ');
                echo '  START=' . substr(preg_replace('/\s+/', ' ', wp_strip_all_tags($html)) ?? '', 0, 200) . "\n";
            }
        }
    }
}

foreach ([
    'about-us/our-team/psychologists',
    'about-us/staff-directory/clinical-psychology',
    'about-us/our-team',
    'about-us/multidisciplinary-teams',
] as $path) {
    $p = get_page_by_path($path);
    if ($p) {
        echo "DEST {$path} => ID={$p->ID} status={$p->post_status} url=" . get_permalink($p) . "\n";
    } else {
        echo "DEST {$path} => MISS\n";
    }
}

$urls = [
    home_url('/about-us/our-team/psychologists/'),
    home_url('/about-us/staff-directory/clinical-psychology'),
    home_url('/about-us/staff-directory/clinical-psychology/'),
    home_url('/about-us/our-team/'),
];
foreach ($urls as $u) {
    $r = wp_remote_get($u, [
        'timeout' => 15,
        'redirection' => 0,
        'sslverify' => false,
    ]);
    if (is_wp_error($r)) {
        echo "HTTP {$u} => ERR " . $r->get_error_message() . "\n";
        continue;
    }
    $code = wp_remote_retrieve_response_code($r);
    $loc = (string) wp_remote_retrieve_header($r, 'location');
    echo "HTTP {$u} => {$code} loc={$loc}\n";
}
