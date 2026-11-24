<?php
/**
 * Force Dean Clinic link wording + footer menu renames.
 *
 *   /wp-content/themes/matrix-starter/scripts/fix-review2-dean-force.php?key=fix-dean1
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-dean1') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

global $wpdb;

$needles = [
    'About Your Portal</a>',
    'Dean Clinics <a',
    'reminders you will receive',
];
foreach ($needles as $needle) {
    $like = '%' . $wpdb->esc_like($needle) . '%';
    $n = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_value LIKE %s",
        $like
    ));
    echo "meta for [{$needle}] = {$n}\n";
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT meta_id, post_id, meta_key FROM {$wpdb->postmeta} WHERE meta_value LIKE %s LIMIT 20",
        $like
    ));
    foreach ($rows as $r) {
        echo "  post={$r->post_id} key={$r->meta_key} meta_id={$r->meta_id}\n";
    }
}

$replacements = [
    [
        'from' => 'reminders you will receive <a href="https://st-patricks.s1.matrix-test.com/your-portal/about-your-portal/">About Your Portal</a>',
        'to' => 'reminders you will receive <a href="https://st-patricks.s1.matrix-test.com/your-portal/about-your-portal/">here</a>',
    ],
    [
        'from' => 'reminders you will receive <a href="/your-portal/about-your-portal/">About Your Portal</a>',
        'to' => 'reminders you will receive <a href="/your-portal/about-your-portal/">here</a>',
    ],
    [
        'from' => 'addresses and directions for our Dean Clinics <a href="https://st-patricks.s1.matrix-test.com/contact-us/">Contact Us</a>.',
        'to' => 'addresses and directions for our Dean Clinics. See <a href="https://st-patricks.s1.matrix-test.com/contact-us/">Contact Us</a>.',
    ],
    [
        'from' => 'addresses and directions for our Dean Clinics <a href="/contact-us/">Contact Us</a>.',
        'to' => 'addresses and directions for our Dean Clinics. See <a href="/contact-us/">Contact Us</a>.',
    ],
];

foreach ($replacements as $pair) {
    $like = '%' . $wpdb->esc_like($pair['from']) . '%';
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT meta_id, post_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE meta_value LIKE %s LIMIT 50",
        $like
    ));
    echo 'exact hits for from-snippet = ' . count($rows) . "\n";
    foreach ($rows as $row) {
        if (is_serialized($row->meta_value)) {
            echo "skip serialized {$row->meta_id}\n";
            continue;
        }
        $next = str_replace($pair['from'], $pair['to'], (string) $row->meta_value);
        if ($next !== $row->meta_value) {
            $wpdb->update($wpdb->postmeta, ['meta_value' => $next], ['meta_id' => (int) $row->meta_id]);
            clean_post_cache((int) $row->post_id);
            echo "OK meta_id={$row->meta_id} post={$row->post_id} key={$row->meta_key}\n";
        }
    }
}

// Broader regex on any meta containing the reminders phrase
$like = '%reminders you will receive%About Your Portal%';
$rows = $wpdb->get_results($wpdb->prepare(
    "SELECT meta_id, post_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE meta_value LIKE %s LIMIT 50",
    $like
));
echo 'broad reminders rows=' . count($rows) . "\n";
foreach ($rows as $row) {
    $val = (string) $row->meta_value;
    if (is_serialized($val)) {
        continue;
    }
    $next = preg_replace(
        '#(reminders you will receive)\\s*<a([^>]*)>\\s*About Your Portal\\s*</a>#i',
        '$1 <a$2>here</a>',
        $val
    ) ?? $val;
    $next = preg_replace(
        '#(addresses and directions for our Dean Clinics)\\s*<a([^>]*)>\\s*Contact Us\\s*</a>\\.?#i',
        '$1. See <a$2>Contact Us</a>.',
        $next
    ) ?? $next;
    if ($next !== $val) {
        $wpdb->update($wpdb->postmeta, ['meta_value' => $next], ['meta_id' => (int) $row->meta_id]);
        clean_post_cache((int) $row->post_id);
        echo "OK broad meta_id={$row->meta_id}\n";
    } else {
        echo "no change meta_id={$row->meta_id} sample=" . substr($val, 0, 180) . "\n";
    }
}

// Footer menus
$menus = wp_get_nav_menus();
foreach ($menus as $menu) {
    $items = wp_get_nav_menu_items($menu->term_id) ?: [];
    foreach ($items as $item) {
        $title = trim((string) $item->title);
        $new = null;
        if (preg_match('/^cookies? and privacy policy$/i', $title)) {
            $new = 'Cookie policy';
        } elseif (preg_match('/^data protection policy$/i', $title)) {
            $new = 'Data Protection Statement';
        }
        if ($new) {
            $wpdb->update($wpdb->posts, ['post_title' => $new], ['ID' => (int) $item->ID]);
            // Also update _menu_item display via post_title which nav uses
            echo "menu item {$item->ID} '{$title}' -> '{$new}'\n";
        }
    }
}
wp_cache_flush();
echo "DONE\n";
