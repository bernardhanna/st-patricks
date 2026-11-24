<?php
/**
 * Fix Dean Clinic FAQ wording (reminders link text + directions/Contact Us).
 *
 *   /wp-content/themes/matrix-starter/scripts/fix-review2-dean-faqs.php?key=fix-dean3
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-dean3') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

global $wpdb;
$log = [];

$patch_post = static function (int $id, callable $fn) use (&$log): void {
    $p = get_post($id);
    if (! $p) {
        $log[] = "missing {$id}";
        return;
    }
    $next = $fn((string) $p->post_content);
    if ($next !== $p->post_content) {
        wp_update_post(['ID' => $id, 'post_content' => $next]);
        $log[] = "OK post {$id} ({$p->post_name})";
    } else {
        $log[] = "no change post {$id} ({$p->post_name})";
    }
};

// Reminders FAQ
$patch_post(4130, static function (string $html): string {
    $next = preg_replace(
        '#(reminders you will receive)\\s*<a([^>]*)>\\s*About Your Portal\\s*</a>#i',
        '$1 <a$2>here</a>',
        $html
    ) ?? $html;
    return $next;
});

// Directions / Contact Us — find FAQs mentioning Dean Clinics Contact Us
$like = '%Dean Clinics%Contact Us%';
$ids = $wpdb->get_col($wpdb->prepare(
    "SELECT ID FROM {$wpdb->posts} WHERE post_type='faqs' AND post_status='publish' AND post_content LIKE %s LIMIT 20",
    $like
));
$log[] = 'dean+contact faqs=' . count($ids);
foreach ($ids as $id) {
    $patch_post((int) $id, static function (string $html): string {
        $next = preg_replace(
            '#(addresses and directions for our Dean Clinics)\\s*<a([^>]*)>\\s*Contact Us\\s*</a>\\.?#i',
            '$1. See <a$2>Contact Us</a>.',
            $html
        ) ?? $html;
        $next = str_replace(
            'addresses and directions for our Dean Clinics Contact Us.',
            'addresses and directions for our Dean Clinics. See <a href="/contact-us/">Contact Us</a>.',
            $next
        );
        return $next;
    });
}

// Also patch any page meta still holding those phrases
foreach ([
    ['%(reminders you will receive)%About Your Portal%', 'reminders'],
    ['%addresses and directions for our Dean Clinics%Contact Us%', 'directions'],
] as [$like, $label]) {
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT meta_id, post_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE meta_value LIKE %s LIMIT 30",
        $like
    ));
    $log[] = "meta {$label}=" . count($rows);
    foreach ($rows as $row) {
        if (is_serialized($row->meta_value)) {
            continue;
        }
        $val = (string) $row->meta_value;
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
            $log[] = "OK meta {$row->meta_id}";
        }
    }
}

// Footer menu titles via post_title on nav menu items
$items = $wpdb->get_results(
    "SELECT p.ID, p.post_title FROM {$wpdb->posts} p
     INNER JOIN {$wpdb->postmeta} m ON m.post_id=p.ID AND m.meta_key='_menu_item_type'
     WHERE p.post_type='nav_menu_item' AND p.post_status='publish'
       AND (p.post_title LIKE 'Cookie%' OR p.post_title LIKE 'Data protection%')"
);
foreach ($items as $item) {
    $title = trim((string) $item->post_title);
    $new = null;
    if (preg_match('/^cookies? and privacy policy$/i', $title)) {
        $new = 'Cookie policy';
    } elseif (preg_match('/^data protection policy$/i', $title)) {
        $new = 'Data Protection Statement';
    }
    if ($new) {
        $wpdb->update($wpdb->posts, ['post_title' => $new], ['ID' => (int) $item->ID]);
        $log[] = "menu {$item->ID}: {$title} -> {$new}";
    }
}

echo implode("\n", $log) . "\nDONE\n";
