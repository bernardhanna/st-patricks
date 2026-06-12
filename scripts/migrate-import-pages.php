<?php

/**
 * Phase 3: Import content pages from stored HTML using hero + wysiwyg flexi blocks.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/migrate-import-pages.php
 */

require_once get_template_directory() . '/inc/migrate-functions.php';
require_once get_template_directory() . '/inc/migrate-restyle-functions.php';

if (! class_exists('WP_CLI')) {
    exit(1);
}

$dry_run = matrix_migrate_is_dry_run();
$limit = max(0, (int) (getenv('MATRIX_MIGRATE_LIMIT') ?: 0));
$slug_registry = [];
$stats = ['seen' => 0, 'created' => 0, 'updated' => 0, 'failed' => 0];

$items = [];

foreach (matrix_migrate_list_html_files() as $item) {
    if (matrix_migrate_classify_old_path($item['path']) !== 'page') {
        continue;
    }

    $items[] = $item;
}

$stats['seen'] = count($items);

if ($limit > 0) {
    $items = array_slice($items, 0, $limit);
}

WP_CLI::log(sprintf('Importing %d pages (%s).', count($items), $dry_run ? 'dry-run' : 'live'));

$progress = \WP_CLI\Utils\make_progress_bar('Pages', count($items));

foreach ($items as $item) {
    $html = (string) file_get_contents($item['file']);
    $parsed = matrix_migrate_extract_parsed_page($html, $item['path']);

    if ($parsed === null) {
        $stats['failed']++;
        WP_CLI::warning('Could not parse: ' . $item['path']);
        $progress->tick();
        continue;
    }

    $slug = matrix_migrate_unique_slug(basename($item['path']), $item['path'], $slug_registry);
    $existing_id = matrix_migrate_find_by_old_path($item['path'], 'page');

    if ($existing_id === 0) {
        $existing = get_page_by_path($slug, OBJECT, 'page');
        $existing_id = $existing instanceof WP_Post ? (int) $existing->ID : 0;
    }

    if ($existing_id > 0 && get_post_meta($existing_id, '_matrix_migrate_old_path', true) === '') {
        $stats['failed']++;
        WP_CLI::warning('Skipped existing designed page slug collision: ' . $slug . ' (' . $item['path'] . ')');
        $progress->tick();
        continue;
    }

    if ($dry_run) {
        $progress->tick();
        continue;
    }

    $hero_image_id = 0;
    $og_image = (string) ($parsed['og_image'] ?? '');

    if ($og_image !== '') {
        $hero_image_id = matrix_migrate_attachment_id_for_source_path($og_image);
    }

    $flexi_rows = matrix_migrate_page_flexi_rows($parsed, $hero_image_id, $html);

    $postarr = [
        'post_type' => 'page',
        'post_status' => 'publish',
        'post_title' => (string) $parsed['title'],
        'post_name' => $slug,
        'post_content' => '',
    ];

    if ($existing_id > 0) {
        $postarr['ID'] = $existing_id;
        $post_id = wp_update_post($postarr, true);
        $stats['updated']++;
    } else {
        $post_id = wp_insert_post($postarr, true);
        $stats['created']++;
    }

    if (is_wp_error($post_id) || ! $post_id) {
        $stats['failed']++;
        WP_CLI::warning('Failed page: ' . $item['path']);
        $progress->tick();
        continue;
    }

    $post_id = (int) $post_id;
    update_post_meta($post_id, '_matrix_migrate_old_path', $item['path']);
    update_field('hero_content_blocks', [], $post_id);
    update_field('flexible_content_blocks', $flexi_rows, $post_id);

    $progress->tick();
}

$progress->finish();

WP_CLI::success(sprintf(
    'Pages done. seen=%d created=%d updated=%d failed=%d',
    $stats['seen'],
    $stats['created'],
    $stats['updated'],
    $stats['failed']
));
