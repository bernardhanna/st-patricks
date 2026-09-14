<?php

/**
 * Scoped DRAFT importer for the curated client worklist.
 *
 * Reads old/content/to-migrate.csv (old_url,type,section,title,source) and imports
 * each item as a DRAFT, pulling content from the local crawl HTML and falling back
 * to a live fetch when no local snapshot exists.
 *
 * Safety guarantees:
 *  - Never deletes or overwrites anything. Only inserts new draft posts/pages.
 *  - Idempotent: skips an item if a post/page already carries its old path meta.
 *  - Everything it creates is post_status = draft for human review before publish.
 *
 * Results CSV: old/content/migration-import-results.csv
 *
 * Run:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/migrate-import-scoped-drafts.php
 *   wp eval-file wp-content/themes/matrix-starter/scripts/migrate-import-scoped-drafts.php dry-run
 */

require_once get_template_directory() . '/inc/migrate-functions.php';

if (! class_exists('WP_CLI')) {
    exit(1);
}

$dry_run = matrix_migrate_is_dry_run();
$theme = dirname(__DIR__);
$in = getenv('MATRIX_IMPORT_IN') ?: ($theme . '/old/content/to-migrate.csv');
$out = getenv('MATRIX_IMPORT_RESULTS') ?: ($theme . '/old/content/migration-import-results.csv');

if (! is_readable($in)) {
    WP_CLI::error('Missing input list: ' . $in);
}

$category_map = [
    'blog' => matrix_migrate_ensure_category('blog', 'Blogs & Articles'),
    'news' => matrix_migrate_ensure_category('news', 'News'),
    'podcasts' => matrix_migrate_ensure_category('podcasts', 'Podcasts'),
    'videos' => matrix_migrate_ensure_category('videos', 'Videos'),
    'clinician-insights' => matrix_migrate_ensure_clinician_insights_category(),
    'events' => matrix_migrate_ensure_category('events', 'Events'),
    'press-releases' => matrix_migrate_ensure_category('press-releases', 'Press Releases'),
];

$handle = fopen($in, 'r');
$header = fgetcsv($handle);
$rows = [];

while (($r = fgetcsv($handle)) !== false) {
    if (count($r) < count($header)) {
        $r = array_pad($r, count($header), '');
    }

    $rows[] = array_combine($header, array_slice($r, 0, count($header)));
}

fclose($handle);

$slug_registry = [];
$seen_paths = [];
$results = [];
$stats = [
    'seen' => 0,
    'created' => 0,
    'skipped_existing' => 0,
    'duplicate_in_list' => 0,
    'no_source' => 0,
    'parse_failed' => 0,
];

$record = static function (array $row, string $action, int $post_id = 0) use (&$results) {
    $results[] = [
        'old_url' => $row['old_url'] ?? '',
        'type' => $row['type'] ?? '',
        'section' => $row['section'] ?? '',
        'title' => $row['title'] ?? '',
        'action' => $action,
        'post_id' => $post_id,
        'status' => $post_id > 0 ? get_post_status($post_id) : '',
        'permalink' => $post_id > 0 ? get_permalink($post_id) : '',
    ];
};

$progress = \WP_CLI\Utils\make_progress_bar('Drafts', count($rows));

foreach ($rows as $row) {
    $stats['seen']++;
    $old_url = trim((string) ($row['old_url'] ?? ''));
    $type = (($row['type'] ?? 'page') === 'post') ? 'post' : 'page';
    $path = trim(preg_replace('#^https?://(www\.)?stpatricks\.ie/#i', '', $old_url), '/');

    if ($path === '') {
        $record($row, 'no_source');
        $stats['no_source']++;
        $progress->tick();
        continue;
    }

    if (isset($seen_paths[$path])) {
        $record($row, 'duplicate_in_list');
        $stats['duplicate_in_list']++;
        $progress->tick();
        continue;
    }

    $seen_paths[$path] = true;

    if (matrix_migrate_find_by_old_path($path, $type) > 0) {
        $record($row, 'skipped_existing', matrix_migrate_find_by_old_path($path, $type));
        $stats['skipped_existing']++;
        $progress->tick();
        continue;
    }

    $html = matrix_migrate_fetch_html_for_path($path);

    if ($html === '') {
        $record($row, 'no_source');
        $stats['no_source']++;
        $progress->tick();
        continue;
    }

    $parsed = matrix_migrate_extract_parsed_page($html, $path);

    if ($parsed === null) {
        $record($row, 'parse_failed');
        $stats['parse_failed']++;
        $progress->tick();
        continue;
    }

    if ($dry_run) {
        $record($row, 'would_create');
        $progress->tick();
        continue;
    }

    $title = (string) ($parsed['title'] ?? ($row['title'] ?? ''));
    $slug = matrix_migrate_unique_slug(basename($path), $path, $slug_registry);

    $hero_image_id = 0;
    $og_image = (string) ($parsed['og_image'] ?? '');

    if ($og_image !== '') {
        $hero_image_id = matrix_migrate_attachment_id_for_source_path($og_image);
    }

    if ($type === 'post') {
        $postarr = [
            'post_type' => 'post',
            'post_status' => 'draft',
            'post_title' => $title,
            'post_name' => $slug,
            'post_content' => (string) ($parsed['body_html'] ?? ''),
            'post_excerpt' => (string) ($parsed['meta_description'] ?? ''),
            'post_date' => matrix_migrate_parse_post_date((string) ($parsed['date_text'] ?? ''), $path),
        ];
    } else {
        $postarr = [
            'post_type' => 'page',
            'post_status' => 'draft',
            'post_title' => $title,
            'post_name' => $slug,
            'post_content' => '',
        ];
    }

    $post_id = wp_insert_post($postarr, true);

    if (is_wp_error($post_id) || ! $post_id) {
        $record($row, 'insert_failed');
        $progress->tick();
        continue;
    }

    $post_id = (int) $post_id;
    update_post_meta($post_id, '_matrix_migrate_old_path', $path);
    update_post_meta($post_id, '_matrix_migrate_source', 'scoped-draft');
    update_post_meta($post_id, '_matrix_migrate_section', (string) ($row['section'] ?? ''));

    if ($type === 'post') {
        $category_slug = matrix_migrate_post_category_for_path($path);
        $category_id = (int) ($category_map[$category_slug] ?? 0);

        if ($category_id > 0) {
            wp_set_post_categories($post_id, [$category_id], false);
        }

        if ($hero_image_id > 0) {
            set_post_thumbnail($post_id, $hero_image_id);
        }
    } else {
        $flexi_rows = matrix_migrate_page_flexi_rows($parsed, $hero_image_id, $html);

        if (function_exists('update_field')) {
            update_field('hero_content_blocks', [], $post_id);
            update_field('flexible_content_blocks', $flexi_rows, $post_id);
        }
    }

    $record($row, 'created', $post_id);
    $stats['created']++;
    $progress->tick();
}

$progress->finish();

$rh = fopen($out, 'w');

if ($rh !== false) {
    fputcsv($rh, ['old_url', 'type', 'section', 'title', 'action', 'post_id', 'status', 'permalink']);

    foreach ($results as $r) {
        fputcsv($rh, $r);
    }

    fclose($rh);
    WP_CLI::log('Results written to: ' . $out);
}

WP_CLI::success(sprintf(
    'Scoped draft import done%s. seen=%d created=%d skipped_existing=%d dup=%d no_source=%d parse_failed=%d',
    $dry_run ? ' (DRY RUN)' : '',
    $stats['seen'],
    $stats['created'],
    $stats['skipped_existing'],
    $stats['duplicate_in_list'],
    $stats['no_source'],
    $stats['parse_failed']
));
