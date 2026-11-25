<?php
/**
 * Deep dump Temple/GRO/GST raw HTML + meta for snag diagnosis.
 *
 *   .../inspect-temple-gro-gst-deep.php?key=inspect-temple-gro-gst-deep
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $allowed = str_contains($host, 'localhost')
        || str_contains($host, 'matrix-test.com')
        || in_array($remote, ['127.0.0.1', '::1'], true);
    if (! $allowed || ($_GET['key'] ?? '') !== 'inspect-temple-gro-gst-deep') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

$slug = sanitize_title((string) ($_GET['slug'] ?? 'temple-formulation-group'));
$posts = get_posts([
    'name' => $slug,
    'post_type' => 'programmes_therapies',
    'post_status' => 'any',
    'posts_per_page' => 1,
]);
if ($posts === []) {
    echo "MISS {$slug}\n";
    exit;
}
$p = $posts[0];
$id = (int) $p->ID;
echo "ID={$id} {$p->post_title}\n";
echo "==== post_content ====\n";
echo $p->post_content . "\n";
echo "==== listing_summary ====\n";
echo (string) get_field('listing_summary', $id) . "\n";
echo "==== excerpt ====\n";
echo $p->post_excerpt . "\n";
echo "==== all meta keys with leaflet/brochure/pdf/psych ====\n";
$keys = get_post_custom_keys($id) ?: [];
foreach ($keys as $k) {
    if (str_starts_with($k, '_')) {
        continue;
    }
    $v = get_post_meta($id, $k, true);
    $s = is_string($v) ? $v : wp_json_encode($v);
    if (! is_string($s)) {
        continue;
    }
    if (preg_match('#leaflet|brochure|\.pdf|psycholog|document|download#i', $s . $k)) {
        echo "META {$k}=\n{$s}\n---\n";
    }
}
echo "==== psychologists page ====\n";
$psych = get_page_by_path('about-us/our-team/psychologists');
if ($psych) {
    echo "ID={$psych->ID} status={$psych->post_status} title={$psych->post_title}\n";
    echo 'permalink=' . get_permalink($psych) . "\n";
    echo 'content_len=' . strlen((string) $psych->post_content) . "\n";
    echo 'content_start=' . substr(wp_strip_all_tags((string) $psych->post_content), 0, 300) . "\n";
    $rows = get_field('flexible_content_blocks', $psych->ID);
    echo 'flexi_rows=' . (is_array($rows) ? count($rows) : 0) . "\n";
    if (is_array($rows)) {
        foreach ($rows as $i => $row) {
            echo "flexi[{$i}]=" . ($row['acf_fc_layout'] ?? '?') . "\n";
            foreach (['heading', 'content', 'text_content', 'intro_text'] as $f) {
                if (! empty($row[$f])) {
                    echo "  {$f}=" . substr(wp_strip_all_tags((string) $row[$f]), 0, 200) . "\n";
                }
            }
        }
    }
} else {
    echo "MISS psychologists page\n";
}
