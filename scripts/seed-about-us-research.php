<?php

/**
 * Seed About Us > Research to match Figma frame 2780:3856 / matrix2026 design
 * with live-site copy instead of lorem ipsum.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-about-us-research.php
 */

require_once __DIR__ . '/lib/research-page-seed-data.php';

$post_id = (int) (get_page_by_path('about-us/research')?->ID ?? 0);

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at about-us/research.');
    }

    exit(1);
}

$home = home_url('/');
$about_us_url = home_url('/about-us/');

$flexi_rows = matrix_build_research_page_flexi_rows([
    [
        'breadcrumb_link' => [
            'title' => 'Home',
            'url' => $home,
            'target' => '',
        ],
    ],
    [
        'breadcrumb_link' => [
            'title' => 'About Us',
            'url' => $about_us_url,
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
            'Seeded Research page (%d) with %d flexi blocks.',
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
