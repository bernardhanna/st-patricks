<?php

/**
 * Phase 1: Import PDFs and images from the Screaming Frog crawl into the media library.
 *
 * Does not create or update pages.
 *
 * Run from WP root:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/migrate-import-media.php
 *   wp eval-file wp-content/themes/matrix-starter/scripts/migrate-import-media.php dry-run
 *   MATRIX_MIGRATE_DRY_RUN=1 wp eval-file wp-content/themes/matrix-starter/scripts/migrate-import-media.php
 *
 * Optional limits:
 *   MATRIX_MIGRATE_LIMIT=50 wp eval-file ...
 *   MATRIX_MIGRATE_ONLY=pdf wp eval-file ...
 *   MATRIX_MIGRATE_ONLY=image wp eval-file ...
 */

require_once get_template_directory() . '/inc/migrate-functions.php';

if (! class_exists('WP_CLI')) {
    exit(1);
}

$dry_run = matrix_migrate_is_dry_run();
$limit = max(0, (int) (getenv('MATRIX_MIGRATE_LIMIT') ?: 0));
$only = strtolower(trim((string) (getenv('MATRIX_MIGRATE_ONLY') ?: '')));

$rows = matrix_migrate_read_csv_rows();

if ($rows === []) {
    WP_CLI::error('Could not read ' . matrix_migrate_csv_path());
}

$stats = [
    'seen' => 0,
    'skipped_status' => 0,
    'skipped_type' => 0,
    'skipped_existing' => 0,
    'imported' => 0,
    'failed' => 0,
    'dry_run' => 0,
];

$queued = [];

foreach ($rows as $row) {
    $address = trim((string) ($row['Address'] ?? ''));
    $status = (string) ($row['Status Code'] ?? '');
    $content_type = strtolower(trim((string) ($row['Content Type'] ?? '')));

    if ($address === '' || $status !== '200') {
        $stats['skipped_status']++;
        continue;
    }

    $is_pdf = str_contains($content_type, 'pdf') || str_ends_with(strtolower($address), '.pdf');
    $is_image = str_starts_with($content_type, 'image/');

    if (! $is_pdf && ! $is_image) {
        $stats['skipped_type']++;
        continue;
    }

    if ($only === 'pdf' && ! $is_pdf) {
        $stats['skipped_type']++;
        continue;
    }

    if ($only === 'image' && ! $is_image) {
        $stats['skipped_type']++;
        continue;
    }

    $normalized = matrix_migrate_normalize_asset_url($address);
    $cache_key = matrix_migrate_asset_cache_key($address);

    if (! isset($queued[$cache_key])) {
        $queued[$cache_key] = [
            'url' => $normalized,
            'title' => trim((string) ($row['Title 1'] ?? '')),
            'type' => $is_pdf ? 'pdf' : 'image',
        ];
    }
}

$items = array_values($queued);
$stats['seen'] = count($items);

if ($limit > 0) {
    $items = array_slice($items, 0, $limit);
}

WP_CLI::log(sprintf(
    'Migrating %d unique assets (%s)%s.',
    count($items),
    $dry_run ? 'dry-run' : 'live',
    $limit > 0 ? ', limit ' . $limit : ''
));

$progress = \WP_CLI\Utils\make_progress_bar('Importing media', count($items));

foreach ($items as $item) {
    $cache_key = matrix_migrate_asset_cache_key($item['url']);

    $existing = get_posts([
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => 1,
        'meta_query' => [
            [
                'key' => '_matrix_migrate_cache_key',
                'value' => $cache_key,
            ],
        ],
        'fields' => 'ids',
    ]);

    if ($existing !== []) {
        $stats['skipped_existing']++;
        $progress->tick();
        continue;
    }

    if ($dry_run) {
        $stats['dry_run']++;
        $progress->tick();
        continue;
    }

    $path = parse_url($item['url'], PHP_URL_PATH);
    $filename = $path ? basename((string) $path) : 'asset';
    $title = $item['title'] !== '' ? $item['title'] : preg_replace('/\.[^.]+$/', '', $filename);

    $attachment_id = matrix_migrate_import_attachment($item['url'], (string) $title, false);

    if ($attachment_id > 0) {
        $stats['imported']++;
    } else {
        $stats['failed']++;
        WP_CLI::warning('Failed: ' . $item['url']);
    }

    $progress->tick();
}

$progress->finish();

WP_CLI::success(sprintf(
    'Done. seen=%d imported=%d existing=%d failed=%d dry_run=%d',
    $stats['seen'],
    $stats['imported'],
    $stats['skipped_existing'],
    $stats['failed'],
    $stats['dry_run']
));
