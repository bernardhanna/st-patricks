<?php

/**
 * Phase 2: Import blog/news/podcast/video posts from stored HTML.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/migrate-import-posts.php
 */

require_once get_template_directory() . '/inc/migrate-functions.php';

if (! class_exists('WP_CLI')) {
    exit(1);
}

$dry_run = matrix_migrate_is_dry_run();
$limit = max(0, (int) (getenv('MATRIX_MIGRATE_LIMIT') ?: 0));
$slug_registry = [];
$stats = ['seen' => 0, 'created' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0];

$category_map = [
    'blog' => matrix_migrate_ensure_category('blog', 'Blogs & Articles'),
    'news' => matrix_migrate_ensure_category('news', 'News'),
    'podcasts' => matrix_migrate_ensure_category('podcasts', 'Podcasts'),
    'videos' => matrix_migrate_ensure_category('videos', 'Videos'),
    'newsletter' => matrix_migrate_ensure_category('newsletter', 'Newsletter'),
    'events' => matrix_migrate_ensure_category('events', 'Events'),
    'press-releases' => matrix_migrate_ensure_category('press-releases', 'Press Releases'),
];

$items = [];

foreach (matrix_migrate_list_html_files() as $item) {
    if (matrix_migrate_classify_old_path($item['path']) !== 'post') {
        continue;
    }

    $items[] = $item;
}

$stats['seen'] = count($items);

if ($limit > 0) {
    $items = array_slice($items, 0, $limit);
}

WP_CLI::log(sprintf('Importing %d posts (%s).', count($items), $dry_run ? 'dry-run' : 'live'));

$progress = \WP_CLI\Utils\make_progress_bar('Posts', count($items));

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
    $existing_id = matrix_migrate_find_by_old_path($item['path'], 'post');

    if ($dry_run) {
        $progress->tick();
        continue;
    }

    $postarr = [
        'post_type' => 'post',
        'post_status' => 'publish',
        'post_title' => (string) $parsed['title'],
        'post_name' => $slug,
        'post_content' => (string) $parsed['body_html'],
        'post_excerpt' => (string) $parsed['meta_description'],
        'post_date' => matrix_migrate_parse_post_date((string) $parsed['date_text'], $item['path']),
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
        WP_CLI::warning('Failed post: ' . $item['path']);
        $progress->tick();
        continue;
    }

    $post_id = (int) $post_id;
    update_post_meta($post_id, '_matrix_migrate_old_path', $item['path']);

    $category_slug = matrix_migrate_post_category_for_path($item['path']);
    $category_id = (int) ($category_map[$category_slug] ?? 0);

    if ($category_id > 0) {
        wp_set_post_categories($post_id, [$category_id], false);
    }

    $og_image = (string) ($parsed['og_image'] ?? '');

    if ($og_image !== '') {
        $attachment_id = matrix_migrate_attachment_id_for_source_path($og_image);

        if ($attachment_id > 0) {
            set_post_thumbnail($post_id, $attachment_id);
        }
    }

    $progress->tick();
}

$progress->finish();

WP_CLI::success(sprintf(
    'Posts done. seen=%d created=%d updated=%d failed=%d',
    $stats['seen'],
    $stats['created'],
    $stats['updated'],
    $stats['failed']
));
