<?php

/**
 * Restyle migrated pages into flexi blocks and clean migrated posts.
 *
 * Run all migrated content:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/migrate-restyle-content.php
 *
 * Force include hand-tuned pages:
 *   MATRIX_MIGRATE_FORCE=1 wp eval-file wp-content/themes/matrix-starter/scripts/migrate-restyle-content.php
 *
 * Limit to URL list file (one URL per line):
 *   MATRIX_MIGRATE_URLS_FILE=/path/to/urls.txt wp eval-file wp-content/themes/matrix-starter/scripts/migrate-restyle-content.php
 */

require_once get_template_directory() . '/inc/migrate-restyle-functions.php';

if (file_exists(get_template_directory() . '/inc/blog-single-functions.php')) {
    require_once get_template_directory() . '/inc/blog-single-functions.php';
}

if (! class_exists('WP_CLI')) {
    exit(1);
}

$force = matrix_migrate_is_dry_run() ? false : (getenv('MATRIX_MIGRATE_FORCE') === '1');
$urls_file = trim((string) (getenv('MATRIX_MIGRATE_URLS_FILE') ?: ''));
$post_ids = [];

if ($urls_file !== '' && is_readable($urls_file)) {
    foreach (file($urls_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, 'new_url')) {
            continue;
        }

        if (! str_starts_with($line, 'http')) {
            continue;
        }

        $post_id = matrix_migrate_post_id_from_public_url($line);

        if ($post_id > 0) {
            $post_ids[] = $post_id;
        }
    }
} else {
    $posts = get_posts([
        'post_type' => ['page', 'post'],
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'meta_key' => '_matrix_migrate_old_path',
        'fields' => 'ids',
    ]);

    $post_ids = array_map('intval', $posts);
}

$post_ids = array_values(array_unique(array_filter($post_ids)));

$stats = [
    'seen' => count($post_ids),
    'pages' => 0,
    'posts' => 0,
    'skipped' => 0,
    'failed' => 0,
];

WP_CLI::log(sprintf('Restyling %d migrated items (%s).', count($post_ids), $force ? 'force' : 'default'));

$progress = \WP_CLI\Utils\make_progress_bar('Restyle', count($post_ids));

foreach ($post_ids as $post_id) {
    $post = get_post($post_id);

    if (! $post instanceof WP_Post) {
        $stats['failed']++;
        $progress->tick();
        continue;
    }

    if (! $force && get_post_meta($post_id, '_matrix_migrate_restyle_skip', true) === '1') {
        $stats['skipped']++;
        $progress->tick();
        continue;
    }

    $ok = $post->post_type === 'page'
        ? matrix_migrate_restyle_page($post_id, $force)
        : matrix_migrate_restyle_post($post_id);

    if ($ok) {
        if ($post->post_type === 'page') {
            $stats['pages']++;
        } else {
            $stats['posts']++;
        }
    } else {
        $stats['failed']++;
        WP_CLI::warning('Failed: ' . $post->post_name . ' (ID ' . $post_id . ')');
    }

    $progress->tick();
}

$progress->finish();

WP_CLI::success(sprintf(
    'Restyle done. seen=%d pages=%d posts=%d skipped=%d failed=%d',
    $stats['seen'],
    $stats['pages'],
    $stats['posts'],
    $stats['skipped'],
    $stats['failed']
));
