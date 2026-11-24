<?php
/**
 * Force FAQ 4130 link text + Dean Clinics Contact Us FAQs via raw SQL content.
 *
 *   /wp-content/themes/matrix-starter/scripts/fix-review2-faq-4130.php?key=fix-faq4130
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-faq4130') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

global $wpdb;

$raw = (string) $wpdb->get_var("SELECT post_content FROM {$wpdb->posts} WHERE ID=4130");
echo "raw len=" . strlen($raw) . "\n";
echo "raw=\n{$raw}\n\n";

$from = 'reminders you will receive <a href="https://st-patricks.s1.matrix-test.com/your-portal/about-your-portal/">About Your Portal</a>';
$to = 'reminders you will receive <a href="https://st-patricks.s1.matrix-test.com/your-portal/about-your-portal/">here</a>';

$next = str_replace($from, $to, $raw);
if ($next === $raw) {
    // Try regex
    $next = preg_replace(
        '#reminders you will receive <a href="[^"]+">About Your Portal</a>#',
        'reminders you will receive <a href="https://st-patricks.s1.matrix-test.com/your-portal/about-your-portal/">here</a>',
        $raw
    ) ?? $raw;
}

if ($next !== $raw) {
    $wpdb->update($wpdb->posts, ['post_content' => $next], ['ID' => 4130]);
    clean_post_cache(4130);
    echo "OK 4130 updated\n";
} else {
    echo "FAIL 4130 still unchanged; contains About Your Portal? " . (str_contains($raw, 'About Your Portal') ? 'yes' : 'no') . "\n";
    echo "contains reminders you will receive? " . (str_contains($raw, 'reminders you will receive') ? 'yes' : 'no') . "\n";
    // Show around Portal
    if (preg_match('/.{0,40}Portal.{0,40}/', $raw, $m)) {
        echo "CTX " . $m[0] . "\n";
        echo "HEX " . bin2hex($m[0]) . "\n";
    }
}

// Directions Contact Us on any FAQ
$rows = $wpdb->get_results(
    "SELECT ID, post_name, post_content FROM {$wpdb->posts}
     WHERE post_type='faqs' AND post_status='publish'
       AND post_content LIKE '%Dean Clinics%' AND post_content LIKE '%Contact Us%'
     LIMIT 20"
);
echo 'dean contact faqs=' . count($rows) . "\n";
foreach ($rows as $row) {
    $val = (string) $row->post_content;
    $n = preg_replace(
        '#(addresses and directions for our Dean Clinics)\\s*<a([^>]*)>\\s*Contact Us\\s*</a>\\.?#i',
        '$1. See <a$2>Contact Us</a>.',
        $val
    ) ?? $val;
    if ($n !== $val) {
        $wpdb->update($wpdb->posts, ['post_content' => $n], ['ID' => (int) $row->ID]);
        clean_post_cache((int) $row->ID);
        echo "OK faq {$row->ID} {$row->post_name}\n";
    } else {
        echo "no regex change {$row->ID}; sample:\n" . substr($val, 0, 300) . "\n";
    }
}

echo "DONE\n";
