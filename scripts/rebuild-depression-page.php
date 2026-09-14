<?php

/**
 * Rebuild the Depression mental_health page layout.
 *
 * Usage: wp eval-file wp-content/themes/matrix-starter/scripts/rebuild-depression-page.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/inc/mental-health-functions.php';
require_once get_template_directory() . '/scripts/lib/orlaith-depression-layout.php';

$depression_id = (int) (get_posts([
    'post_type' => 'mental_health',
    'name' => 'depression',
    'post_status' => 'any',
    'posts_per_page' => 1,
    'fields' => 'ids',
])[0] ?? 0);

if ($depression_id <= 0) {
    WP_CLI::error('Depression post not found.');
}

matrix_orlaith_rebuild_depression_layout($depression_id);
WP_CLI::success('Rebuilt Depression layout on post ' . $depression_id);
