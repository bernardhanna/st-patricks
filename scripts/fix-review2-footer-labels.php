<?php
/**
 * Rename footer legal menu labels (force by object id / fuzzy title).
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

$cookie_page = (int) $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE post_type='page' AND post_name LIKE '%cookie%' AND post_status='publish' LIMIT 1");
$data_page = (int) $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE post_type='page' AND (post_name LIKE '%data-protection%' OR post_title LIKE 'Data Protection%') AND post_status='publish' LIMIT 1");
echo "cookie_page={$cookie_page} data_page={$data_page}\n";

$items = $wpdb->get_results(
    "SELECT p.ID, p.post_title, m.meta_value AS object_id
     FROM {$wpdb->posts} p
     INNER JOIN {$wpdb->postmeta} m ON m.post_id=p.ID AND m.meta_key='_menu_item_object_id'
     WHERE p.post_type='nav_menu_item' AND p.post_status='publish'"
);

$n = 0;
foreach ($items as $item) {
    $oid = (int) $item->object_id;
    $title = (string) $item->post_title;
    $new = null;
    if ($cookie_page && $oid === $cookie_page) {
        $new = 'Cookie policy';
    } elseif ($data_page && $oid === $data_page) {
        $new = 'Data Protection Statement';
    } elseif (stripos($title, 'cookie') !== false && stripos($title, 'privacy') !== false) {
        $new = 'Cookie policy';
    } elseif (stripos($title, 'data protection policy') !== false) {
        $new = 'Data Protection Statement';
    }

    if ($new === null) {
        continue;
    }

    $wpdb->update($wpdb->posts, ['post_title' => $new], ['ID' => (int) $item->ID]);
    echo "menu {$item->ID} object={$oid} title=[{$title}] -> {$new}\n";
    $n++;
}

// Dump remaining fuzzy matches for debug
$dbg = $wpdb->get_results(
    "SELECT p.ID, p.post_title, m.meta_value AS object_id
     FROM {$wpdb->posts} p
     INNER JOIN {$wpdb->postmeta} m ON m.post_id=p.ID AND m.meta_key='_menu_item_object_id'
     WHERE p.post_type='nav_menu_item' AND p.post_status='publish'
       AND (p.post_title LIKE '%ookie%' OR p.post_title LIKE '%rotection%' OR m.meta_value IN ('{$cookie_page}','{$data_page}'))"
);
foreach ($dbg as $d) {
    echo "dbg menu {$d->ID} object={$d->object_id} title=[{$d->post_title}]\n";
}

wp_cache_flush();
echo "updated={$n}\nDONE\n";
