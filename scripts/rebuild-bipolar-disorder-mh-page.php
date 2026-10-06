<?php

/**
 * Rebuild the Bipolar disorder mental_health page layout.
 *
 * Usage: wp eval-file wp-content/themes/matrix-starter/scripts/rebuild-bipolar-disorder-mh-page.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/inc/mental-health-functions.php';
require_once get_template_directory() . '/scripts/lib/orlaith-bipolar-layout.php';

$post_id = (int) (get_posts([
    'post_type' => 'mental_health',
    'name' => 'bipolar-disorder',
    'post_status' => 'any',
    'posts_per_page' => 1,
    'fields' => 'ids',
])[0] ?? 0);

if ($post_id <= 0) {
    WP_CLI::error('Bipolar disorder post not found.');
}

matrix_orlaith_rebuild_bipolar_layout($post_id);
WP_CLI::success('Rebuilt Bipolar disorder layout on post ' . $post_id);
