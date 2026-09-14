<?php

/**
 * Restore Careers page flexi content from the latest revision with valid blocks.
 *
 * Use when flexible_content_blocks meta is empty/corrupt (form shows only core fields).
 *
 * Run:
 * wp eval-file wp-content/themes/matrix-starter/scripts/restore-careers-from-revision.php
 */

$post_id = (int) (get_page_by_path('careers')?->ID ?? 0);

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find Careers page.');
    }

    exit(1);
}

$revision_id = 0;
$revisions = get_children([
    'post_parent' => $post_id,
    'post_type' => 'revision',
    'post_status' => 'inherit',
    'orderby' => 'ID',
    'order' => 'DESC',
    'numberposts' => 20,
]);

foreach ($revisions as $revision) {
    $candidate = (int) $revision->ID;
    $flex = get_field('flexible_content_blocks', $candidate, true);

    if (is_array($flex) && $flex !== [] && is_array($flex[0] ?? null)) {
        $revision_id = $candidate;
        break;
    }
}

if ($revision_id <= 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('No revision with flexible_content_blocks found for Careers.');
    }

    exit(1);
}

$rows = get_field('flexible_content_blocks', $revision_id, true);

if (! is_array($rows) || $rows === []) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Revision ' . $revision_id . ' has no flexible content.');
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

$accordion_index = null;
$accordion_items = 0;

foreach ($rows as $idx => $row) {
    if (! is_array($row) || ($row['acf_fc_layout'] ?? '') !== 'content_accordion') {
        continue;
    }

    $accordion_index = (int) $idx;
    $items = is_array($row['items'] ?? null) ? $row['items'] : [];
    $faq_lorem = '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>';

    while (count($items) < 8) {
        $num = count($items) + 1;
        $items[] = [
            'title' => 'Additional FAQ ' . ($num - 6),
            'starts_open' => false,
            'content_rows' => [
                [
                    'row_type' => 'text',
                    'content' => $faq_lorem,
                ],
            ],
        ];
    }

    $rows[$idx]['items'] = $items;
    $accordion_items = count($items);
    break;
}

update_field('flexible_content_blocks', $rows, $post_id);

if (function_exists('acf_get_store')) {
    acf_get_store('values')->reset();
}

if (function_exists('clean_post_cache')) {
    clean_post_cache($post_id);
}

$saved = get_field('flexible_content_blocks', $post_id, true);
$block_count = is_array($saved) ? count($saved) : 0;

$form_rows = 0;

if (class_exists('Matrix_Export')) {
    $export_rows = Matrix_Export::get_export_data_for_form([$post_id], false);

    foreach ($export_rows['rows'] ?? [] as $row) {
        if (! is_array($row) || (int) ($row['post_id'] ?? 0) !== $post_id) {
            continue;
        }
        if (($row['block_source'] ?? '') === Matrix_Export::POST_FIELDS_SOURCE) {
            continue;
        }
        $form_rows++;
    }
}

if (class_exists('WP_CLI')) {
    WP_CLI::success(sprintf(
        'Restored Careers page (%d) from revision %d: deleted %d stale meta rows, saved %d blocks, accordion index %s with %d items, %d editable form rows.',
        $post_id,
        $revision_id,
        (int) $deleted,
        $block_count,
        $accordion_index === null ? 'n/a' : (string) $accordion_index,
        $accordion_items,
        $form_rows
    ));
}
