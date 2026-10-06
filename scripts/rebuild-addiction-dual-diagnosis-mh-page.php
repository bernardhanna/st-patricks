<?php

/**
 * Rebuild the Addiction & dual diagnosis mental_health page layout.
 *
 * Usage: wp eval-file wp-content/themes/matrix-starter/scripts/rebuild-addiction-dual-diagnosis-mh-page.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/inc/mental-health-functions.php';
require_once get_template_directory() . '/scripts/lib/orlaith-addiction-layout.php';

$post_id = (int) (get_posts([
    'post_type' => 'mental_health',
    'name' => 'addiction-dual-diagnosis',
    'post_status' => 'any',
    'posts_per_page' => 1,
    'fields' => 'ids',
])[0] ?? 0);

if ($post_id <= 0) {
    WP_CLI::error('Addiction & dual diagnosis post not found.');
}

matrix_orlaith_rebuild_addiction_layout($post_id);
WP_CLI::success('Rebuilt Addiction & dual diagnosis layout on post ' . $post_id);
