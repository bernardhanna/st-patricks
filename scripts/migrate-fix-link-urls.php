<?php

/**
 * Resolve legacy internal URLs in migrated flexi blocks and post content.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/migrate-fix-link-urls.php
 */

require_once get_template_directory() . '/inc/migrate-functions.php';

if (! class_exists('WP_CLI')) {
    exit(1);
}

$post_ids = get_posts([
    'post_type' => ['page', 'post'],
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'meta_key' => '_matrix_migrate_old_path',
    'fields' => 'ids',
]);

$post_ids = array_values(array_unique(array_map('intval', $post_ids)));

$stats = [
    'seen' => count($post_ids),
    'updated' => 0,
    'changes' => 0,
];

WP_CLI::log(sprintf('Fixing legacy link URLs in %d migrated items.', count($post_ids)));

$progress = \WP_CLI\Utils\make_progress_bar('Link URLs', count($post_ids));

foreach ($post_ids as $post_id) {
    $changes = matrix_migrate_fixup_post_link_urls((int) $post_id);

    if ($changes > 0) {
        $stats['updated']++;
        $stats['changes'] += $changes;
    }

    $progress->tick();
}

$progress->finish();

WP_CLI::success(sprintf(
    'Link URL fix done. seen=%d updated=%d change_sets=%d',
    $stats['seen'],
    $stats['updated'],
    $stats['changes']
));
