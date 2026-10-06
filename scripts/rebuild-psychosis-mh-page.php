<?php

/**
 * Rebuild the Psychosis mental_health page layout (slug schizophrenia-psychosis).
 *
 * Usage: wp eval-file wp-content/themes/matrix-starter/scripts/rebuild-psychosis-mh-page.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/inc/mental-health-functions.php';
require_once get_template_directory() . '/scripts/lib/orlaith-psychosis-layout.php';

$post_id = (int) (get_posts([
    'post_type' => 'mental_health',
    'name' => 'schizophrenia-psychosis',
    'post_status' => 'any',
    'posts_per_page' => 1,
    'fields' => 'ids',
])[0] ?? 0);

if ($post_id <= 0) {
    WP_CLI::error('Psychosis post not found.');
}

matrix_orlaith_rebuild_psychosis_layout($post_id);
WP_CLI::success('Rebuilt Psychosis layout on post ' . $post_id);
