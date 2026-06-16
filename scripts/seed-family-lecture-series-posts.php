<?php

/**
 * Import Family Information Series events as blog posts (Events category).
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-family-lecture-series-posts.php
 */

require_once get_template_directory() . '/inc/migrate-functions.php';
require_once get_template_directory() . '/scripts/lib/family-lecture-series-seed.php';

if (! class_exists('WP_CLI')) {
    exit(1);
}

$events = [];

foreach (matrix_family_lecture_series_items() as $item) {
    $events[$item['old_path']] = $item['slug'];
}

$events_category_id = matrix_migrate_ensure_category('events', 'Events');

if ($events_category_id < 1) {
    WP_CLI::error('Could not ensure Events category.');
}

$slug_registry = [];
$created = 0;
$updated = 0;
$trashed = 0;

foreach ($events as $old_path => $slug) {
    $page = get_page_by_path($slug, OBJECT, 'page');

    if ($page instanceof WP_Post && $page->post_status !== 'trash') {
        wp_trash_post((int) $page->ID);
        $trashed++;
        WP_CLI::log(sprintf('Trashed incorrect page %d (%s).', $page->ID, $slug));
    }

    $file = matrix_migrate_html_dir() . '/original_https_www.stpatricks.ie_' . str_replace('/', '_', $old_path) . '.html';

    if ($file === '' || ! is_readable($file)) {
        WP_CLI::warning('Missing HTML file for: ' . $old_path);
        continue;
    }

    $html = (string) file_get_contents($file);
    $parsed = matrix_migrate_extract_parsed_page($html, $old_path);

    if ($parsed === null) {
        WP_CLI::warning('Could not parse: ' . $old_path);
        continue;
    }

    $post_slug = matrix_migrate_unique_slug($slug, $old_path, $slug_registry);
    $existing_id = matrix_migrate_find_by_old_path($old_path, 'post');

    if ($existing_id === 0) {
        $by_slug = get_page_by_path($post_slug, OBJECT, 'post');

        if ($by_slug instanceof WP_Post) {
            $existing_id = (int) $by_slug->ID;
        }
    }

    $postarr = [
        'post_type' => 'post',
        'post_status' => 'publish',
        'post_title' => (string) $parsed['title'],
        'post_name' => $post_slug,
        'post_content' => (string) $parsed['body_html'],
        'post_excerpt' => (string) $parsed['meta_description'],
        'post_date' => matrix_migrate_parse_post_date((string) $parsed['date_text'], $old_path),
    ];

    if ($existing_id > 0) {
        $postarr['ID'] = $existing_id;
        $post_id = wp_update_post($postarr, true);
        $updated++;
    } else {
        $post_id = wp_insert_post($postarr, true);
        $created++;
    }

    if (is_wp_error($post_id) || ! $post_id) {
        WP_CLI::warning('Failed post: ' . $old_path);
        continue;
    }

    $post_id = (int) $post_id;
    update_post_meta($post_id, '_matrix_migrate_old_path', $old_path);
    wp_set_post_categories($post_id, [$events_category_id], false);

    $og_image = (string) ($parsed['og_image'] ?? '');

    if ($og_image !== '') {
        $attachment_id = matrix_migrate_attachment_id_for_source_path($og_image);

        if ($attachment_id > 0) {
            set_post_thumbnail($post_id, $attachment_id);
        }
    }

    WP_CLI::log(sprintf(
        'Post %d (%s) → %s',
        $post_id,
        $post_slug,
        get_permalink($post_id)
    ));
}

matrix_family_lecture_patch_youth_advocacy_related_cards();
WP_CLI::log('Updated youth advocacy related cards with family lecture series post links.');

WP_CLI::success(sprintf(
    'Family lecture series posts done. created=%d updated=%d trashed_pages=%d',
    $created,
    $updated,
    $trashed
));
