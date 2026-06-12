<?php

/**
 * Export migrated content URL map (old stpatricks.ie URL → new site URL).
 *
 * Run:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/migrate-export-url-map.php
 *   wp eval-file wp-content/themes/matrix-starter/scripts/migrate-export-url-map.php page
 *   wp eval-file wp-content/themes/matrix-starter/scripts/migrate-export-url-map.php page,post
 */

require_once get_template_directory() . '/inc/migrate-functions.php';

if (! class_exists('WP_CLI')) {
    exit(1);
}

$post_types = ['page', 'post'];

if (isset($args[0]) && is_string($args[0]) && trim($args[0]) !== '') {
    $post_types = array_values(array_filter(array_map('trim', explode(',', $args[0]))));
}

$migrated = get_posts([
    'post_type' => $post_types,
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'meta_key' => '_matrix_migrate_old_path',
    'orderby' => 'meta_value',
    'order' => 'ASC',
]);

$rows = [];

foreach ($migrated as $post) {
    if (! $post instanceof WP_Post) {
        continue;
    }

    $old_path = trim((string) get_post_meta($post->ID, '_matrix_migrate_old_path', true), '/');

    if ($old_path === '') {
        continue;
    }

    $rows[] = [
        'old_url' => matrix_migrate_live_url($old_path),
        'new_url' => (string) get_permalink($post),
        'post_type' => $post->post_type,
        'title' => $post->post_title,
        'post_id' => (string) $post->ID,
    ];
}

usort($rows, static function (array $a, array $b): int {
    return strcmp($a['old_url'], $b['old_url']);
});

$csv_path = get_template_directory() . '/old/migrated-url-map.csv';
$handle = fopen($csv_path, 'w');

if ($handle === false) {
    WP_CLI::error('Could not write ' . $csv_path);
}

fputcsv($handle, ['old_url', 'new_url', 'post_type', 'title', 'post_id']);

foreach ($rows as $row) {
    fputcsv($handle, $row);
}

fclose($handle);

$page_count = count(array_filter($rows, static fn (array $row): bool => $row['post_type'] === 'page'));
$post_count = count(array_filter($rows, static fn (array $row): bool => $row['post_type'] === 'post'));

WP_CLI::success(sprintf(
    'Wrote %d rows to %s (%d pages, %d posts)',
    count($rows),
    $csv_path,
    $page_count,
    $post_count
));
