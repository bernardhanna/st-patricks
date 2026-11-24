<?php
/**
 * Dump referral video_showcase / testimonial-related fields.
 *
 *   /wp-content/themes/matrix-starter/scripts/fix-review2-referral-dump.php?key=fix-refdump
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-refdump') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

foreach ([
    'healthcare-professionals/refer-an-adult-for-inpatient-care',
    'healthcare-professionals/refer-to-a-day-programme',
    'healthcare-professionals/refer-for-outpatient-care',
] as $path) {
    $page = get_page_by_path($path);
    echo "==== {$path} id=" . ($page->ID ?? 0) . "\n";
    if (! $page || ! function_exists('get_field')) {
        continue;
    }
    $rows = get_field('flexible_content_blocks', $page->ID);
    if (! is_array($rows)) {
        continue;
    }
    foreach ($rows as $i => $row) {
        $layout = $row['acf_fc_layout'] ?? '?';
        if (! in_array($layout, ['video_showcase', 'what_we_offer', 'testimonials', 'testimonial_slider'], true)
            && ! str_contains($layout, 'testimonial')
            && ! str_contains($layout, 'video')) {
            continue;
        }
        echo "block {$i} {$layout}\n";
        echo substr(wp_json_encode($row, JSON_PRETTY_PRINT), 0, 2500) . "\n\n";
    }
}
echo "DONE\n";
