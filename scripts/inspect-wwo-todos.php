<?php
/**
 * Inspect What We Offer To-do snag pages (local/staging).
 *
 *   /wp-content/themes/matrix-starter/scripts/inspect-wwo-todos.php?key=inspect-wwo-todos
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $allowed = str_contains($host, 'localhost')
        || str_contains($host, 'matrix-test.com')
        || in_array($remote, ['127.0.0.1', '::1'], true);
    if (! $allowed || ($_GET['key'] ?? '') !== 'inspect-wwo-todos') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

$dump = static function (string $label, int $id): void {
    echo "\n======== {$label} #{$id} ========\n";
    $p = get_post($id);
    if (! $p instanceof WP_Post) {
        echo "MISS\n";

        return;
    }
    echo 'title=' . $p->post_title . ' slug=' . $p->post_name . "\n";
    echo 'permalink=' . get_permalink($id) . "\n";
    echo 'excerpt=' . substr(wp_strip_all_tags((string) $p->post_excerpt), 0, 400) . "\n";
    $ls = (string) get_field('listing_summary', $id);
    echo 'listing_summary=' . substr(wp_strip_all_tags($ls), 0, 400) . "\n";
    $content = (string) $p->post_content;
    echo 'post_content_plain=' . substr(wp_strip_all_tags($content), 0, 600) . "\n";
    if (preg_match_all('#(?:youtube\.com/embed/|youtu\.be/)([A-Za-z0-9_-]+)#', $content . $ls, $m)) {
        echo 'youtube_in_content=' . implode(',', $m[1]) . "\n";
    }

    $rows = get_field('flexible_content_blocks', $id);
    if (! is_array($rows)) {
        echo "no flexi\n";

        return;
    }
    foreach ($rows as $i => $row) {
        $layout = (string) ($row['acf_fc_layout'] ?? '?');
        echo "-- row {$i} layout={$layout}\n";
        foreach ($row as $k => $v) {
            if ($k === 'acf_fc_layout') {
                continue;
            }
            if (is_string($v) && trim(wp_strip_all_tags($v)) !== '') {
                echo "  {$k}: " . substr(wp_strip_all_tags($v), 0, 320) . "\n";
                if (preg_match_all('#(?:youtube\.com/embed/|youtu\.be/)([A-Za-z0-9_-]+)#', $v, $ym)) {
                    echo '  yt=' . implode(',', $ym[1]) . "\n";
                }
                if (preg_match_all('#href=(["\'])(.*?)\1#', $v, $hm)) {
                    foreach ($hm[2] as $h) {
                        if (preg_match('#stpatricks|living-with|psychosis|multidisciplinary|anxiety-disorders|programmes|leaflet|brochure#i', $h)) {
                            echo "  href={$h}\n";
                        }
                    }
                }
                if (preg_match('#xxxx|xxx sessions|include…|include\.\.\.|Full list|Read the leaflet|Click here|difficulties\.Here#i', $v)) {
                    echo "  FLAG_MATCH in {$k}\n";
                }
            } elseif (is_array($v)) {
                if (isset($v['url'])) {
                    echo "  {$k}.url=" . $v['url'] . ' title=' . ($v['title'] ?? $v['label'] ?? '') . "\n";
                } elseif (isset($v['title'], $v['url']) || (isset($v[0]) && is_array($v[0]))) {
                    $json = wp_json_encode($v);
                    if (is_string($json) && strlen($json) < 800) {
                        echo "  {$k}=" . $json . "\n";
                    } else {
                        echo "  {$k}=[array len=" . count($v) . "]\n";
                    }
                }
            }
        }
    }
};

$by_slug = static function (string $slug): int {
    $posts = get_posts([
        'name' => $slug,
        'post_type' => 'programmes_therapies',
        'post_status' => 'any',
        'posts_per_page' => 1,
    ]);

    return $posts === [] ? 0 : (int) $posts[0]->ID;
};

$map = [
    'day-programmes' => (int) (get_page_by_path('what-we-offer/day-programmes')?->ID ?? 0),
    'anxiety-disorders-programme' => $by_slug('anxiety-disorders-programme'),
    'anxiety-disorders-programme-2' => $by_slug('anxiety-disorders-programme-2'),
    'art-therapy' => $by_slug('art-therapy'),
    'binge-eating-disorder-programme' => $by_slug('binge-eating-disorder-programme'),
    'bipolar-education-programme' => $by_slug('bipolar-education-programme'),
    'cognitive-behavioural-therapy' => $by_slug('cognitive-behavioural-therapy'),
    'compassion-focused-therapy' => $by_slug('compassion-focused-therapy'),
    'cft-for-adolescents-and-families' => $by_slug('cft-for-adolescents-and-families'),
    'compassion-focused-therapy-eating-disorders' => $by_slug('compassion-focused-therapy-eating-disorders'),
    'cft-for-older-adults' => $by_slug('cft-for-older-adults'),
    'compassion-focused-therapy-for-psychosis' => $by_slug('compassion-focused-therapy-for-psychosis'),
    'compassion-focused-therapy-for-psychosis-2' => $by_slug('compassion-focused-therapy-for-psychosis-2'),
];

foreach ($map as $label => $id) {
    if ($id <= 0) {
        echo "\n======== {$label} MISS ========\n";
        continue;
    }
    $dump($label, $id);
}

// Menu / link scan for anxiety-2
echo "\n======== LINK SCAN anxiety-disorders-programme-2 ========\n";
global $wpdb;
$like = '%anxiety-disorders-programme-2%';
$hits = $wpdb->get_results($wpdb->prepare(
    "SELECT ID, post_type, post_name FROM {$wpdb->posts} WHERE post_status NOT IN ('trash','auto-draft') AND (post_content LIKE %s OR post_excerpt LIKE %s) LIMIT 40",
    $like,
    $like
));
foreach ($hits as $h) {
    echo "post #{$h->ID} {$h->post_type}/{$h->post_name}\n";
}
$meta = $wpdb->get_results($wpdb->prepare(
    "SELECT post_id, meta_key FROM {$wpdb->postmeta} WHERE meta_value LIKE %s LIMIT 40",
    $like
));
foreach ($meta as $m) {
    echo "meta post_id={$m->post_id} key={$m->meta_key}\n";
}

echo "\nDONE\n";
