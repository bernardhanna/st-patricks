<?php

/**
 * Phase 4: Generate Rank Math redirect CSV from migrated content and frozen path map.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/migrate-generate-redirects.php
 */

require_once get_template_directory() . '/inc/migrate-functions.php';

if (! class_exists('WP_CLI')) {
    exit(1);
}

$redirects = [];
$home_path = wp_parse_url(home_url('/'), PHP_URL_PATH) ?: '/';

foreach (matrix_migrate_frozen_redirect_map() as $old_path => $destination) {
    $redirects['/' . trim($old_path, '/')] = $destination;
}

$migrated = get_posts([
    'post_type' => ['post', 'page'],
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'meta_key' => '_matrix_migrate_old_path',
]);

foreach ($migrated as $post) {
    if (! $post instanceof WP_Post) {
        continue;
    }

    $old_path = trim((string) get_post_meta($post->ID, '_matrix_migrate_old_path', true), '/');

    if ($old_path === '') {
        continue;
    }

    $redirects['/' . $old_path] = '/' . trim($post->post_name, '/') . '/';
}

ksort($redirects);

$csv_path = matrix_migrate_redirects_csv_path();
$handle = fopen($csv_path, 'w');

if ($handle === false) {
    WP_CLI::error('Could not write ' . $csv_path);
}

fputcsv($handle, ['source', 'destination', 'type', 'matching', 'status', 'category']);

foreach ($redirects as $source => $destination) {
    if (! str_starts_with($destination, 'http')) {
        $destination = home_url($destination);
    }

    fputcsv($handle, [
        $source,
        $destination,
        '301',
        'exact',
        'active',
        'migration',
    ]);
}

fclose($handle);

WP_CLI::success(sprintf('Wrote %d redirects to %s', count($redirects), $csv_path));
