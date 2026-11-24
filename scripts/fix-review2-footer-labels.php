<?php
/**
 * Rename footer legal menu labels.
 *
 *   /wp-content/themes/matrix-starter/scripts/fix-review2-footer-labels.php?key=fix-foot1
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-foot1') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

global $wpdb;

$map = [
    'Cookies and privacy policy' => 'Cookie policy',
    'Cookie and privacy policy' => 'Cookie policy',
    'Data protection policy' => 'Data Protection Statement',
];

$items = $wpdb->get_results(
    "SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type='nav_menu_item' AND post_status='publish'"
);
$n = 0;
foreach ($items as $item) {
    $title = trim((string) $item->post_title);
    foreach ($map as $from => $to) {
        if (strcasecmp($title, $from) === 0) {
            $wpdb->update($wpdb->posts, ['post_title' => $to], ['ID' => (int) $item->ID]);
            // Nav items also store title in postmeta sometimes for custom labels
            update_post_meta((int) $item->ID, '_menu_item_title', $to);
            echo "{$item->ID}: {$title} -> {$to}\n";
            $n++;
        }
    }
}

// Also pages themselves if titled that way
foreach ($map as $from => $to) {
    $pages = get_posts([
        'post_type' => 'page',
        'title' => $from,
        'post_status' => 'any',
        'numberposts' => 5,
    ]);
    // get_posts title param unreliable — use SQL
}
$rows = $wpdb->get_results(
    "SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type='page' AND post_status='publish'
     AND (post_title LIKE 'Cookie%' OR post_title LIKE 'Data protection%')"
);
foreach ($rows as $row) {
    $title = trim((string) $row->post_title);
    foreach ($map as $from => $to) {
        if (strcasecmp($title, $from) === 0) {
            wp_update_post(['ID' => (int) $row->ID, 'post_title' => $to]);
            echo "page {$row->ID}: {$title} -> {$to}\n";
            $n++;
        }
    }
}

wp_cache_flush();
echo "updated={$n}\nDONE\n";
