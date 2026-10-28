<?php

/**
 * Snag fix: About Us > Social workers — Useful links empty, so the block never
 * rendered. Populate the existing (last) useful_links row so it renders near the
 * footer, directly under the "Our multidisciplinary team" grid.
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/fix-social-workers-useful-links.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

$post_id = (int) (get_page_by_path('about-us/our-team/social-workers')?->ID ?? 0);
if ($post_id === 0) {
    WP_CLI::error('Could not find about-us/our-team/social-workers');
}

$wanted = [
    'Our team' => 'about-us/our-team',
    'About us' => 'about-us',
    'Family supports' => 'service-users-and-visitors/carers-and-supporters',
    'Stories and support' => 'service-users-and-visitors/stories-and-support',
    'Safeguarding' => 'about-us/policies-and-publications/safeguarding',
    'Service user information' => 'service-users-and-visitors',
];

$resolved = [];
foreach ($wanted as $label => $path) {
    $page = get_page_by_path($path);
    if (! $page instanceof WP_Post) {
        WP_CLI::warning('Skipped missing page: ' . $path);
        continue;
    }
    $resolved[$label] = $path;
}

$rows = get_field('flexible_content_blocks', $post_id);
if (! is_array($rows) || $rows === []) {
    WP_CLI::error('No flexible_content_blocks on #' . $post_id);
}

$useful = matrix_orlaith_useful_links_row($resolved);
$placed = false;
foreach ($rows as $index => $row) {
    if (($row['acf_fc_layout'] ?? '') !== 'useful_links') {
        continue;
    }
    $rows[$index] = array_merge($row, $useful);
    $placed = true;
    break;
}

if (! $placed) {
    $rows[] = $useful;
}

update_field('flexible_content_blocks', $rows, $post_id);

$saved = get_field('flexible_content_blocks', $post_id);
$last = is_array($saved) ? end($saved) : [];
WP_CLI::success(sprintf(
    'Social workers #%d last layout=%s links=%d',
    $post_id,
    (string) ($last['acf_fc_layout'] ?? '?'),
    is_array($last['links'] ?? null) ? count($last['links']) : 0
));
