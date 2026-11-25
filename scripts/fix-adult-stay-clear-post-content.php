<?php
/**
 * Clear duplicate classic post_content on Your stay in hospital as an adult.
 *
 * The page already has flexible_content_blocks (hero + sections + accordions).
 * Leftover post_content was rendering first via page.php and looked like a
 * raw/broken HTML dump above the real layout.
 *
 * HTTP (staging/local):
 *   /wp-content/themes/matrix-starter/scripts/fix-adult-stay-clear-post-content.php?key=fix-adult-stay-clear
 *
 * CLI:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/fix-adult-stay-clear-post-content.php
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-adult-stay-clear') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

$post = get_page_by_path('service-users-and-visitors/your-stay-in-hospital-as-an-adult');

if (! $post instanceof WP_Post) {
    echo "FAIL page not found\n";
    exit(1);
}

$post_id = (int) $post->ID;
$before_len = strlen((string) $post->post_content);
$rows = get_field('flexible_content_blocks', $post_id);
$row_count = is_array($rows) ? count($rows) : 0;

if ($row_count === 0) {
    echo "FAIL #{$post_id} has no flexible_content_blocks; refusing to clear post_content\n";
    exit(1);
}

if ($before_len === 0) {
    echo "OK adult stay #{$post_id}: post_content already empty, flexi_rows={$row_count}\n";
    exit(0);
}

$result = wp_update_post([
    'ID' => $post_id,
    'post_content' => '',
], true);

if (is_wp_error($result)) {
    echo 'FAIL update: ' . $result->get_error_message() . "\n";
    exit(1);
}

clean_post_cache($post_id);

$after = get_post($post_id);
$after_len = $after instanceof WP_Post ? strlen((string) $after->post_content) : -1;

if ($after_len !== 0) {
    echo "FAIL #{$post_id} post_content still len={$after_len}\n";
    exit(1);
}

echo "OK adult stay #{$post_id}: cleared post_content {$before_len} -> 0, flexi_rows={$row_count}\n";
