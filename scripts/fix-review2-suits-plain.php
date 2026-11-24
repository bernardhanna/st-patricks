<?php
/**
 * Replace plain "emailing suits@stpatricks.ie" with mailto anywhere in DB.
 *
 *   /wp-content/themes/matrix-starter/scripts/fix-review2-suits-plain.php?key=fix-suits2
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-suits2') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

global $wpdb;
$needle = 'emailing suits@stpatricks.ie';
$replacement = 'emailing <a href="mailto:suits@stpatricks.ie">suits@stpatricks.ie</a>';
$like = '%' . $wpdb->esc_like($needle) . '%';

$meta = $wpdb->get_results($wpdb->prepare(
    "SELECT meta_id, post_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE meta_value LIKE %s LIMIT 100",
    $like
));
echo 'meta hits=' . count($meta) . "\n";
foreach ($meta as $row) {
    $val = (string) $row->meta_value;
    if (str_contains($val, 'mailto:suits@stpatricks.ie')) {
        echo "skip already mailto meta_id={$row->meta_id}\n";
        continue;
    }
    if (is_serialized($val)) {
        echo "skip serialized meta_id={$row->meta_id}\n";
        continue;
    }
    $next = str_replace($needle, $replacement, $val);
    if ($next !== $val) {
        $wpdb->update($wpdb->postmeta, ['meta_value' => $next], ['meta_id' => (int) $row->meta_id]);
        clean_post_cache((int) $row->post_id);
        echo "OK meta post={$row->post_id} key={$row->meta_key}\n";
    }
}

$posts = $wpdb->get_results($wpdb->prepare(
    "SELECT ID, post_type, post_name, post_content FROM {$wpdb->posts} WHERE post_content LIKE %s LIMIT 50",
    $like
));
echo 'post hits=' . count($posts) . "\n";
foreach ($posts as $p) {
    $val = (string) $p->post_content;
    if (str_contains($val, 'mailto:suits@stpatricks.ie') && ! str_contains($val, $needle)) {
        echo "skip post {$p->ID}\n";
        continue;
    }
    // Replace only plain occurrences
    $next = preg_replace(
        '#emailing suits@stpatricks\.ie#i',
        'emailing <a href="mailto:suits@stpatricks.ie">suits@stpatricks.ie</a>',
        $val
    ) ?? $val;
    // Also catch "or by emailing" already partially linked cases: plain email only
    $next2 = preg_replace(
        '#(?<!mailto:|">|\'>)suits@stpatricks\.ie#i',
        '<a href="mailto:suits@stpatricks.ie">suits@stpatricks.ie</a>',
        $next
    ) ?? $next;
    if ($next2 !== $val) {
        wp_update_post(['ID' => (int) $p->ID, 'post_content' => $next2]);
        echo "OK post {$p->ID} type={$p->post_type} name={$p->post_name}\n";
    }
}

echo "DONE\n";
