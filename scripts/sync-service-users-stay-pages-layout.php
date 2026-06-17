<?php

/**
 * Apply About our St Patrick's at Home Service flexi design options to the
 * Your Stay in Hospital pages without replacing page copy.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/sync-service-users-stay-pages-layout.php
 */

require_once __DIR__ . '/lib/service-users-visitors-page-layout.php';

$target_paths = [
    'service-users-and-visitors/your-stay-in-hospital-as-an-adult',
    'service-users-and-visitors/your-stay-in-hospital-as-an-adolescent',
];

$updated = 0;

foreach ($target_paths as $path) {
    $post_id = (int) (get_page_by_path($path)?->ID ?? 0);

    if ($post_id === 0) {
        if (class_exists('WP_CLI')) {
            WP_CLI::warning('Could not find page at ' . $path . '.');
        }
        continue;
    }

    $rows = get_field('flexible_content_blocks', $post_id);

    if (! is_array($rows) || $rows === []) {
        if (class_exists('WP_CLI')) {
            WP_CLI::warning('No flexi rows found on ' . $path . '.');
        }
        continue;
    }

    $rows = matrix_apply_service_users_visitors_flexi_layout($rows);
    update_field('flexible_content_blocks', $rows, $post_id);

    if (class_exists('WP_CLI')) {
        WP_CLI::success('Applied shared layout options to ' . $path . ' (ID ' . $post_id . ').');
    }

    $updated++;
}

if ($updated === 0 && class_exists('WP_CLI')) {
    WP_CLI::error('No stay-in-hospital pages were updated.');
}
