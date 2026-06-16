<?php

/**
 * Import mental health condition pages into the mental_health CPT.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/migrate-import-mental-health.php
 */

require_once get_template_directory() . '/inc/migrate-functions.php';
require_once get_template_directory() . '/inc/migrate-restyle-functions.php';
require_once get_template_directory() . '/inc/mental-health-functions.php';

if (! class_exists('WP_CLI')) {
    exit(1);
}

$dry_run = matrix_migrate_is_dry_run();
$paths = matrix_get_mental_health_condition_paths();
$stats = ['seen' => count($paths), 'created' => 0, 'updated' => 0, 'failed' => 0];

WP_CLI::log(sprintf('Importing %d mental health conditions (%s).', count($paths), $dry_run ? 'dry-run' : 'live'));

$progress = \WP_CLI\Utils\make_progress_bar('Mental health', count($paths));

foreach ($paths as $old_path) {
    $slug = matrix_mental_health_slug_from_old_path($old_path);
    $existing_id = matrix_migrate_find_mental_health_candidate($old_path, $slug);
    $had_existing = $existing_id > 0;

    $post_id = matrix_migrate_import_mental_health_path($old_path, $dry_run);

    if ($post_id < 1) {
        $stats['failed']++;
        WP_CLI::warning('Failed: ' . $old_path);
        $progress->tick();
        continue;
    }

    if ($had_existing) {
        $stats['updated']++;
    } else {
        $stats['created']++;
    }

    if (! $dry_run) {
        WP_CLI::log(sprintf('  %s -> %s', $old_path, get_permalink($post_id)));
    }

    $progress->tick();
}

$progress->finish();

if (! $dry_run) {
    flush_rewrite_rules(false);
}

WP_CLI::success(sprintf(
    'Mental health import done. seen=%d created=%d updated=%d failed=%d',
    $stats['seen'],
    $stats['created'],
    $stats['updated'],
    $stats['failed']
));
