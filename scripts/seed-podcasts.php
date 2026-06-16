<?php

/**
 * Import all Media Centre podcasts as posts in the Podcasts category.
 *
 * Uses stored HTML where available; fetches and caches missing pages from stpatricks.ie.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-podcasts.php
 */

require_once get_template_directory() . '/inc/migrate-functions.php';

if (! class_exists('WP_CLI')) {
    exit(1);
}

$skip_paths = [
    // Redirects away from a podcast article in permalink map.
    'media-centre/podcasts/2020/june/the-irish-times-women-s-podcast-covid-19-and-the-impact-on-womens-mental-health',
];

$paths = array_values(array_filter(
    matrix_migrate_paths_from_csv('media-centre/podcasts/\d{4}/[a-z]+/[^"?]+'),
    static fn (string $path): bool => ! in_array($path, $skip_paths, true)
));

$podcasts_category_id = matrix_migrate_ensure_category('podcasts', 'Podcasts');

if ($podcasts_category_id < 1) {
    WP_CLI::error('Could not ensure Podcasts category.');
}

WP_CLI::log(sprintf('Importing %d podcasts.', count($paths)));

$slug_registry = [];
$created = 0;
$updated = 0;
$failed = 0;

foreach ($paths as $path) {
    $result = matrix_migrate_import_media_post($path, $podcasts_category_id, $slug_registry);

    if ($result['status'] === 'failed') {
        $failed++;
        WP_CLI::warning('Failed: ' . $path);
        continue;
    }

    if ($result['status'] === 'created') {
        $created++;
    } else {
        $updated++;
    }

    WP_CLI::log(sprintf('Post %d (%s)', $result['post_id'] ?? 0, $result['slug'] ?? ''));
}

WP_CLI::success(sprintf(
    'Podcasts done. total=%d created=%d updated=%d failed=%d',
    count($paths),
    $created,
    $updated,
    $failed
));
