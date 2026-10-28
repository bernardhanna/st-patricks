<?php

/**
 * Homepage hero slide 1–2 heading punctuation (snag homepage|homepage|2).
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/fix-homepage-heading-snag-2026-10-23.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

$post_id = (int) get_option('page_on_front');
if ($post_id <= 0) {
    $post_id = (int) (get_page_by_path('home')?->ID ?? 0);
}
if ($post_id <= 0) {
    WP_CLI::error('Homepage not found.');
}

$blocks = get_field('hero_content_blocks', $post_id);
if (! is_array($blocks)) {
    WP_CLI::error('No hero_content_blocks on homepage.');
}

$updated = false;

foreach ($blocks as &$row) {
    if (($row['acf_fc_layout'] ?? '') !== 'hero_slider' || ! is_array($row['slides'] ?? null)) {
        continue;
    }

    if (isset($row['slides'][0]['heading_text'])) {
        $row['slides'][0]['heading_text'] = "Ireland's largest independent\nnot-for-profit\nmental health service";
        $updated = true;
    }
    if (isset($row['slides'][1]['heading_text'])) {
        $row['slides'][1]['heading_text'] = 'High-quality mental healthcare';
        $updated = true;
    }
}
unset($row);

if (! $updated) {
    WP_CLI::error('Hero slider slides not found.');
}

update_field('hero_content_blocks', $blocks, $post_id);
WP_CLI::success("Updated homepage hero headings on page {$post_id}.");
