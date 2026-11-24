<?php
/**
 * Force mailto on suits@ for attending day programmes page.
 *
 *   /wp-content/themes/matrix-starter/scripts/fix-review2-suits-day.php?key=fix-suits1
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-suits1') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

$page = get_page_by_path('service-users-and-visitors/attending-day-programmes')
    ?: get_page_by_path('service-users-and-visitors/attending-our-day-programmes')
    ?: get_page_by_path('attending-day-programmes')
    ?: get_page_by_path('attending-our-day-programmes');

if (! $page) {
    // slug search
    $q = new WP_Query([
        'post_type' => 'page',
        'name' => 'attending-our-day-programmes',
        'posts_per_page' => 1,
        'post_status' => 'any',
    ]);
    $page = $q->posts[0] ?? null;
}

if (! $page) {
    echo "PAGE NOT FOUND\n";
    exit;
}

echo 'page=' . $page->ID . ' slug=' . $page->post_name . "\n";

global $wpdb;
$rows = $wpdb->get_results($wpdb->prepare(
    "SELECT meta_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id=%d AND meta_value LIKE %s",
    $page->ID,
    '%suits@stpatricks.ie%'
));
echo 'matching meta rows=' . count($rows) . "\n";

$changed = 0;
foreach ($rows as $row) {
    $val = (string) $row->meta_value;
    if (str_contains($val, 'mailto:suits@stpatricks.ie')) {
        echo 'already mailto key=' . $row->meta_key . "\n";
        continue;
    }
    $next = preg_replace(
        '#(?<!mailto:|">|\'>|href=")suits@stpatricks\.ie#i',
        '<a href="mailto:suits@stpatricks.ie">suits@stpatricks.ie</a>',
        $val
    ) ?? $val;
    // Safer plain replace if still no mailto
    if ($next === $val && str_contains($val, 'suits@stpatricks.ie')) {
        $next = str_replace(
            'suits@stpatricks.ie',
            '<a href="mailto:suits@stpatricks.ie">suits@stpatricks.ie</a>',
            $val
        );
    }
    if ($next !== $val) {
        $wpdb->update($wpdb->postmeta, ['meta_value' => $next], ['meta_id' => (int) $row->meta_id]);
        $changed++;
        echo 'UPDATED key=' . $row->meta_key . "\n";
    }
}

clean_post_cache($page->ID);
echo "changed={$changed}\nDONE\n";
