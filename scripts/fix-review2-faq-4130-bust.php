<?php
/**
 * Bust caches and force FAQ 4130 + Dean directions FAQ wording.
 *
 *   /wp-content/themes/matrix-starter/scripts/fix-review2-faq-4130-bust.php?key=fix-faq4130b
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-faq4130b') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

global $wpdb;

// "here" alone is rewritten by matrix vague-link filter → keep visible "here" + aria-label.
$desired_4130 = '<p>You will get reminders of your appointments. How you receive your reminders depends on whether you are attending in-person or remotely, and whether you use Your Portal or not. You can find out more about the reminders you will receive <a href="https://st-patricks.s1.matrix-test.com/your-portal/about-your-portal/" aria-label="About Your Portal">here</a>.</p>';

$wpdb->update($wpdb->posts, [
    'post_content' => $desired_4130,
    'post_modified' => current_time('mysql'),
    'post_modified_gmt' => current_time('mysql', true),
], ['ID' => 4130]);
clean_post_cache(4130);
wp_cache_delete(4130, 'posts');
echo "4130 set with here+aria-label\n";

// FAQ 4129 directions wording
$raw_4129 = (string) $wpdb->get_var("SELECT post_content FROM {$wpdb->posts} WHERE ID=4129");
$from_4129 = 'addresses and directions for our Dean Clinics <a href="https://st-patricks.s1.matrix-test.com/contact-us/">Contact Us</a>.';
$to_4129 = 'addresses and directions for our Dean Clinics. See <a href="https://st-patricks.s1.matrix-test.com/contact-us/">Contact Us</a>.';
$next_4129 = str_replace($from_4129, $to_4129, $raw_4129);
if ($next_4129 === $raw_4129) {
    $next_4129 = preg_replace(
        '#addresses and directions for our Dean Clinics\s*<a href="[^"]*contact-us[^"]*">\s*Contact Us\s*</a>\.?#i',
        'addresses and directions for our Dean Clinics. See <a href="https://st-patricks.s1.matrix-test.com/contact-us/">Contact Us</a>.',
        $raw_4129
    ) ?? $raw_4129;
}
if ($next_4129 !== $raw_4129) {
    $wpdb->update($wpdb->posts, ['post_content' => $next_4129], ['ID' => 4129]);
    clean_post_cache(4129);
    echo "4129 directions wording patched\n";
} else {
    echo "4129 no change; contains Contact Us after Clinics? " . (str_contains($raw_4129, 'Dean Clinics <a') ? 'yes' : 'no') . "\n";
}

wp_cache_flush();
if (function_exists('rocket_clean_domain')) {
    rocket_clean_domain();
}

$v4130 = (string) $wpdb->get_var("SELECT post_content FROM {$wpdb->posts} WHERE ID=4130");
$v4129 = (string) $wpdb->get_var("SELECT post_content FROM {$wpdb->posts} WHERE ID=4129");
echo '4130 aria+here=' . (str_contains($v4130, 'aria-label="About Your Portal">here</a>') ? 'yes' : 'no') . "\n";
echo '4129 See Contact Us=' . (str_contains($v4129, 'Dean Clinics. See <a') ? 'yes' : 'no') . "\n";
echo "DONE\n";
