<?php
/**
 * Set "Explore clinician resources" about_links_grid to 3 desktop columns.
 *
 * HTTP (staging/local):
 *   /wp-content/themes/matrix-starter/scripts/fix-clinician-insights-resources-columns.php?key=fix-ci-resources-cols
 *
 * CLI:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/fix-clinician-insights-resources-columns.php
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-ci-resources-cols') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

$post = get_page_by_path('healthcare-professionals/clinician-insights');

if (! $post instanceof WP_Post) {
    echo "FAIL page not found\n";
    exit(1);
}

$post_id = (int) $post->ID;
$rows = get_field('flexible_content_blocks', $post_id);

if (! is_array($rows) || $rows === []) {
    echo "FAIL no flexi rows on #{$post_id}\n";
    exit(1);
}

$updated = [];

foreach ($rows as $index => $row) {
    if (! is_array($row) || ($row['acf_fc_layout'] ?? '') !== 'about_links_grid') {
        continue;
    }

    $heading = trim(wp_strip_all_tags((string) ($row['heading_text'] ?? $row['heading'] ?? '')));

    if (strcasecmp($heading, 'Explore clinician resources') !== 0) {
        continue;
    }

    $before = (string) ($row['columns'] ?? '');
    $rows[$index]['columns'] = '3';
    $updated[] = "Explore clinician resources[#{$index}] columns {$before} -> 3";
}

if ($updated === []) {
    echo "FAIL no Explore clinician resources about_links_grid on #{$post_id}\n";
    exit(1);
}

update_field('flexible_content_blocks', $rows, $post_id);
clean_post_cache($post_id);

echo 'OK clinician insights #' . $post_id . ': ' . implode(', ', $updated) . "\n";
