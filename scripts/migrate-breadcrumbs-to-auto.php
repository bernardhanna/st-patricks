<?php
/**
 * Flip hero_with_breadcrumbs breadcrumb_source from manual → auto site-wide.
 *
 * WP-CLI:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/migrate-breadcrumbs-to-auto.php
 *
 * Browser (local/staging only):
 *   /wp-content/themes/matrix-starter/scripts/migrate-breadcrumbs-to-auto.php?key=migrate-breadcrumbs-auto
 */
if (! defined('ABSPATH')) {
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed_host = str_contains($host, 'localhost')
        || str_contains($host, 'matrix-test.com')
        || in_array($remote, ['127.0.0.1', '::1'], true);

    if (! $allowed_host || ($_GET['key'] ?? '') !== 'migrate-breadcrumbs-auto') {
        http_response_code(403);
        exit;
    }

    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

$log = [];
$scanned = 0;
$updated_posts = 0;
$updated_rows = 0;
$already_auto = 0;
$who_we_are = 0;

$query = new WP_Query([
    'post_type' => 'any',
    'post_status' => ['publish', 'draft', 'private', 'pending', 'future'],
    'posts_per_page' => -1,
    'fields' => 'ids',
    'meta_query' => [
        [
            'key' => 'flexible_content_blocks',
            'compare' => 'EXISTS',
        ],
    ],
]);

foreach ($query->posts as $post_id) {
    $post_id = (int) $post_id;
    $rows = get_field('flexible_content_blocks', $post_id);
    if (! is_array($rows) || $rows === []) {
        continue;
    }

    $scanned++;
    $changed = false;

    foreach ($rows as $i => $row) {
        if (($row['acf_fc_layout'] ?? '') !== 'hero_with_breadcrumbs') {
            continue;
        }

        $source = (string) ($row['breadcrumb_source'] ?? 'auto');
        $blob = wp_json_encode($row['manual_breadcrumbs'] ?? $row['manual_breadcrumb_items'] ?? []) ?: '';
        if (str_contains($blob, 'Who we are')) {
            $who_we_are++;
        }

        if ($source === 'auto') {
            $already_auto++;
            continue;
        }

        $rows[$i]['breadcrumb_source'] = 'auto';
        // Keep manual rows in ACF for rollback, but they are unused while source=auto.
        $changed = true;
        $updated_rows++;
    }

    if (! $changed) {
        continue;
    }

    update_field('flexible_content_blocks', $rows, $post_id);
    clean_post_cache($post_id);
    $updated_posts++;
}

$log[] = 'OK scanned posts with flexi: ' . $scanned;
$log[] = 'OK already auto hero rows: ' . $already_auto;
$log[] = 'OK flipped manual→auto rows: ' . $updated_rows;
$log[] = 'OK updated posts: ' . $updated_posts;
$log[] = 'OK heroes that had Who we are manual crumbs: ' . $who_we_are;
$log[] = 'DONE';

echo implode("\n", $log) . "\n";
