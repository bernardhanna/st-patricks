<?php
/**
 * Locate Dean Clinic sentences that front-end still shows.
 *
 *   /wp-content/themes/matrix-starter/scripts/fix-review2-dean-locate.php?key=fix-dean2
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-dean2') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

global $wpdb;
$page = get_page_by_path('service-users-and-visitors/attending-a-dean-clinic');
echo 'page=' . ($page->ID ?? 0) . "\n";
if (! $page) {
    exit;
}

// Dump ALL meta containing Dean or Portal
$rows = $wpdb->get_results($wpdb->prepare(
    "SELECT meta_id, meta_key, LENGTH(meta_value) len, meta_value FROM {$wpdb->postmeta} WHERE post_id=%d AND (meta_value LIKE %s OR meta_value LIKE %s OR meta_value LIKE %s) LIMIT 30",
    $page->ID,
    '%Dean Clinic%',
    '%About Your Portal%',
    '%reminders%'
));
echo 'matching meta=' . count($rows) . "\n";
foreach ($rows as $r) {
    echo "---- {$r->meta_key} len={$r->len}\n";
    if (preg_match('/.{0,60}(reminders|Dean Clinics|About Your Portal).{0,80}/i', (string) $r->meta_value, $m)) {
        echo $m[0] . "\n";
    }
}

if (function_exists('get_field')) {
    $blocks = get_field('flexible_content_blocks', $page->ID);
    echo 'blocks=' . (is_array($blocks) ? count($blocks) : 0) . "\n";
    $json = wp_json_encode($blocks);
    echo 'json has reminders=' . (str_contains((string) $json, 'reminders') ? 'yes' : 'no') . "\n";
    echo 'json has About Your Portal=' . (str_contains((string) $json, 'About Your Portal') ? 'yes' : 'no') . "\n";
    if (preg_match('/.{0,80}reminders.{0,120}/i', (string) $json, $m)) {
        echo 'CTX ' . $m[0] . "\n";
    }
    // Print content fields
    if (is_array($blocks)) {
        foreach ($blocks as $i => $b) {
            $layout = $b['acf_fc_layout'] ?? '?';
            foreach (['content', 'intro_text', 'heading'] as $k) {
                if (! empty($b[$k]) && is_string($b[$k]) && (str_contains($b[$k], 'Portal') || str_contains($b[$k], 'Dean') || str_contains($b[$k], 'reminder'))) {
                    echo "block {$i} {$layout}.{$k}:\n" . $b[$k] . "\n\n";
                }
            }
        }
    }
}

echo "DONE\n";
