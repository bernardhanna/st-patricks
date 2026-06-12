<?php

/**
 * Seed top-level /research/ to match the matrix2026 About Us > Research design
 * with live-site copy instead of lorem ipsum.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-research-landing.php
 */

require_once __DIR__ . '/lib/research-page-seed-data.php';

$post = get_page_by_path('research');

if (! $post instanceof WP_Post) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at /research/.');
    }

    exit(1);
}

$post_id = (int) $post->ID;
$home = home_url('/');

$flexi_rows = matrix_build_research_page_flexi_rows([
    [
        'breadcrumb_link' => [
            'title' => 'Home',
            'url' => $home,
            'target' => '',
        ],
    ],
]);

update_field('hero_content_blocks', [], $post_id);
update_field('flexible_content_blocks', $flexi_rows, $post_id);

$saved_rows = get_field('flexible_content_blocks', $post_id);
$saved_count = is_array($saved_rows) ? count($saved_rows) : 0;

if (class_exists('WP_CLI')) {
    if ($saved_count === count($flexi_rows)) {
        WP_CLI::success(sprintf(
            'Seeded /research/ page (%d) with %d flexi blocks.',
            $post_id,
            $saved_count
        ));
    } else {
        WP_CLI::warning(sprintf(
            'Updated page %d but expected %d blocks, found %d.',
            $post_id,
            count($flexi_rows),
            $saved_count
        ));
    }
}
