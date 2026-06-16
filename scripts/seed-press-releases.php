<?php

/**
 * Import all Media Centre press releases as posts in the Press Releases category.
 *
 * Uses stored HTML where available; fetches and caches missing pages from stpatricks.ie.
 * Also discovers URLs from the live paginated listing.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-press-releases.php
 */

require_once get_template_directory() . '/inc/migrate-functions.php';

if (! class_exists('WP_CLI')) {
    exit(1);
}

$paths = matrix_migrate_media_centre_post_paths(
    'media-centre/press-releases',
    'media-centre/press-releases/\d{4}/[a-z]+/[^"?]+'
);

$press_releases_category_id = matrix_migrate_ensure_category('press-releases', 'Press Releases');

if ($press_releases_category_id < 1) {
    WP_CLI::error('Could not ensure Press Releases category.');
}

WP_CLI::log(sprintf('Importing %d press releases.', count($paths)));

$slug_registry = [];
$created = 0;
$updated = 0;
$failed = 0;

foreach ($paths as $path) {
    $had_file = is_readable(matrix_migrate_html_file_for_path($path));
    $result = matrix_migrate_import_media_post($path, $press_releases_category_id, $slug_registry);

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

    if (! $had_file && is_readable(matrix_migrate_html_file_for_path($path))) {
        WP_CLI::log(sprintf('Cached HTML for %s', $path));
    }

    WP_CLI::log(sprintf('Post %d (%s)', $result['post_id'] ?? 0, $result['slug'] ?? ''));
}

WP_CLI::success(sprintf(
    'Press releases done. total=%d created=%d updated=%d failed=%d',
    count($paths),
    $created,
    $updated,
    $failed
));
