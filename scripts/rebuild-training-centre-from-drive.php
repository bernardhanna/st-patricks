<?php

/**
 * Rebuild Training Centre (GP education supports).
 *
 * Canonical layout lives in scripts/lib/training-centre-seed.php so a reseed
 * keeps the short hero, Resources for GPs (mailto), and a single GP newsletter
 * flexi (no contact_form).
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/rebuild-training-centre-from-drive.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';
require_once get_template_directory() . '/scripts/lib/training-centre-seed.php';

$post_id = (int) (get_page_by_path('healthcare-professionals/training-centre')?->ID ?? 0);
if ($post_id === 0) {
    WP_CLI::error('Could not find healthcare-professionals/training-centre');
}

$img_id = matrix_orlaith_find_image(0, 'GP Training Centre.png');
if ($img_id <= 0) {
    $img_id = matrix_orlaith_find_image(1911, 'future-in-mind-research-training-poster.jpg');
}
if ($img_id <= 0) {
    $img_id = (int) get_post_thumbnail_id($post_id);
}

$poster = matrix_orlaith_find_image(4091, 'Research and Training Video.png');
if ($poster <= 0) {
    $poster = $img_id;
}

$home = untrailingslashit(home_url('/'));
$flexi = matrix_training_centre_flexi_rows([
    'hero_image' => $img_id,
    'poster' => $poster,
    'webinars_url' => $home . '/healthcare-professionals/webinars-events/',
    'youtube_url' => 'https://www.youtube.com/channel/UCOI_6n3TndtZlW34C4RCdQw',
    'clinician_insights_url' => $home . '/healthcare-professionals/clinician-insights/',
    'privacy_url' => matrix_orlaith_permalink('data-protection-policy'),
]);

wp_update_post([
    'ID' => $post_id,
    'post_title' => 'Training Centre',
]);

matrix_orlaith_save_page($post_id, $flexi, true, $img_id);
matrix_orlaith_set_seo(
    $post_id,
    'Training Centre | St Patrick’s Mental Health Services',
    'GP webinars, mental health films, clinician insights and newsletter updates from the St Patrick’s Mental Health Services Training Centre.'
);

WP_CLI::success(sprintf(
    'Rebuilt Training Centre #%d → %s',
    $post_id,
    get_permalink($post_id)
));

foreach ((array) get_field('flexible_content_blocks', $post_id) as $i => $row) {
    $layout = (string) ($row['acf_fc_layout'] ?? '?');
    $heading = wp_strip_all_tags((string) ($row['heading'] ?? ''));
    $btn = is_array($row['primary_button'] ?? null) ? (string) ($row['primary_button']['title'] ?? '') : '';
    $extra = $btn !== '' ? ' | btn=' . $btn : '';
    if ($layout === 'video_showcase' && ! empty($row['slides'])) {
        $extra .= ' | slides=' . count($row['slides']);
    }
    WP_CLI::log("[{$i}] {$layout} {$heading}{$extra}");
}
