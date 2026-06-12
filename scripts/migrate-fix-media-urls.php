<?php

/**
 * Fix legacy /media/{id}/file.pdf URLs in migrated post content and ACF meta.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/migrate-fix-media-urls.php
 */

require_once get_template_directory() . '/inc/migrate-functions.php';

if (! class_exists('WP_CLI')) {
    exit(1);
}

global $wpdb;

$post_ids = $wpdb->get_col(
    "SELECT DISTINCT post_id
     FROM {$wpdb->postmeta}
     WHERE meta_value LIKE '%/media/%'
     UNION
     SELECT ID FROM {$wpdb->posts}
     WHERE post_content LIKE '%/media/%' AND post_status != 'trash'"
);

$post_ids = array_values(array_unique(array_map('intval', $post_ids ?: [])));

$stats = [
    'seen' => count($post_ids),
    'updated' => 0,
    'changes' => 0,
];

WP_CLI::log(sprintf('Fixing legacy media URLs in %d posts.', count($post_ids)));

$progress = \WP_CLI\Utils\make_progress_bar('Media URLs', count($post_ids));

foreach ($post_ids as $post_id) {
    $changes = matrix_migrate_fix_post_media_urls((int) $post_id);

    if ($changes > 0) {
        $stats['updated']++;
        $stats['changes'] += $changes;
    }

    $progress->tick();
}

$progress->finish();

WP_CLI::success(sprintf(
    'Media URL fix done. seen=%d updated=%d field_changes=%d',
    $stats['seen'],
    $stats['updated'],
    $stats['changes']
));
