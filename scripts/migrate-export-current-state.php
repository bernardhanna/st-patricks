<?php

/**
 * Export the current state of every migrated post/page.
 *
 * Outputs CSV (old_path, post_id, post_type, post_status, permalink) for any
 * post/page that carries the _matrix_migrate_old_path meta key, so the
 * consolidated content tracker can be populated with real new links.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/migrate-export-current-state.php
 */

if (! class_exists('WP_CLI')) {
    exit(1);
}

$out = getenv('MATRIX_EXPORT_OUT') ?: '/tmp/current-migrated-state.csv';

$query = new WP_Query([
    'post_type' => ['post', 'page'],
    'post_status' => 'any',
    'posts_per_page' => -1,
    'meta_key' => '_matrix_migrate_old_path',
    'fields' => 'ids',
    'no_found_rows' => true,
]);

$handle = fopen($out, 'w');

if ($handle === false) {
    WP_CLI::error('Could not open for writing: ' . $out);
}

fputcsv($handle, ['old_path', 'post_id', 'post_type', 'post_status', 'permalink', 'title']);

$count = 0;

foreach ($query->posts as $post_id) {
    $post_id = (int) $post_id;
    $old_path = (string) get_post_meta($post_id, '_matrix_migrate_old_path', true);

    fputcsv($handle, [
        $old_path,
        $post_id,
        get_post_type($post_id),
        get_post_status($post_id),
        get_permalink($post_id),
        get_the_title($post_id),
    ]);

    $count++;
}

fclose($handle);

WP_CLI::success(sprintf('Exported %d migrated records to %s', $count, $out));
