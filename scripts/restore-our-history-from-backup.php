<?php

/**
 * Restore Our History (page 272) flexi content from the Aug 14 2026 DB backup.
 *
 * Use when seed-our-history.php --force overwrote Orlaith's content with Figma
 * placeholder copy. Restores hero, video showcase, and timeline from backup
 * JSON, then appends the "Our present and future" content block if missing.
 *
 * Run:
 * wp eval-file wp-content/themes/matrix-starter/scripts/restore-our-history-from-backup.php
 */

$post_id = (int) (get_page_by_path('about-us/our-history')?->ID ?? 0);

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at about-us/our-history.');
    }

    exit(1);
}

$json_path = __DIR__ . '/data/our-history-272-backup-2026-08-14.json';

if (! is_readable($json_path)) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Backup JSON not found: ' . $json_path);
    }

    exit(1);
}

$raw = file_get_contents($json_path);

if ($raw === false) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not read backup JSON.');
    }

    exit(1);
}

/** @var array<string, string>|null $backup_meta */
$backup_meta = json_decode($raw, true);

if (! is_array($backup_meta) || $backup_meta === []) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Backup JSON is empty or invalid.');
    }

    exit(1);
}

global $wpdb;

$like = $wpdb->esc_like('flexible_content_blocks') . '%';

$deleted = $wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->postmeta} WHERE post_id = %d AND (meta_key = 'flexible_content_blocks' OR meta_key LIKE %s OR meta_key LIKE %s)",
        $post_id,
        $like,
        '\_' . $like
    )
);

$restored = 0;

foreach ($backup_meta as $meta_key => $meta_value) {
    if (! is_string($meta_key) || ! is_string($meta_value)) {
        continue;
    }

    $decoded = maybe_unserialize($meta_value);
    $stored = ($decoded === false && $meta_value !== 'b:0;') ? $meta_value : $decoded;

    update_post_meta($post_id, $meta_key, $stored);
    $restored++;
}

if (function_exists('acf_get_store')) {
    acf_get_store('values')->reset();
}

if (function_exists('clean_post_cache')) {
    clean_post_cache($post_id);
}

require_once __DIR__ . '/lib/our-history-present-future-block.php';

$present_future = matrix_our_history_ensure_present_future_block($post_id);
$added_present_future = $present_future['added'];
$synced_present_future = $present_future['synced'];

$rows = get_field('flexible_content_blocks', $post_id);

$block_count = is_array($rows) ? count($rows) : 0;
$video_heading = '';
$video_url = '';

if (is_array($rows)) {
    foreach ($rows as $row) {
        if (! is_array($row) || ($row['acf_fc_layout'] ?? '') !== 'video_showcase') {
            continue;
        }

        $video_heading = (string) ($row['heading'] ?? '');
        $slides = is_array($row['slides'] ?? null) ? $row['slides'] : [];

        if ($slides !== [] && is_array($slides[0])) {
            $video_url = (string) ($slides[0]['video_embed_url'] ?? '');
        }
    }
}

if (class_exists('WP_CLI')) {
    WP_CLI::success(sprintf(
        'Restored Our History page (%d): deleted %d meta rows, restored %d keys, %d blocks, present/future block %s. Video: "%s" (%s).',
        $post_id,
        (int) $deleted,
        $restored,
        $block_count,
        $added_present_future ? 'added' : ($synced_present_future ? 'normalised' : 'already present'),
        $video_heading,
        $video_url
    ));
}
