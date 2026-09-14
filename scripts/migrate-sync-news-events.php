<?php

/**
 * Sync News & Events tracker rows to WordPress:
 *  - Apply client publish dates from NEWS-AND-EVENTS-TRACKER.csv
 *  - Set editorial flag meta for flagged rows
 *  - Draft published posts not on any of the four client content sheets
 *
 * Never deletes content. Surplus drafting only changes publish → draft on posts.
 *
 * Run:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/migrate-sync-news-events.php dry-run
 *   wp eval-file wp-content/themes/matrix-starter/scripts/migrate-sync-news-events.php
 */

require_once get_template_directory() . '/inc/migrate-functions.php';
require_once get_template_directory() . '/inc/content-tracker-functions.php';

if (! class_exists('WP_CLI')) {
    exit(1);
}

$dry_run = matrix_migrate_is_dry_run();
$theme = dirname(__DIR__);
$news_csv = $theme . '/old/content/NEWS-AND-EVENTS-TRACKER.csv';
$tracker_csv = $theme . '/old/content/CONTENT-MIGRATION-TRACKER-v2.csv';

if (! is_readable($news_csv)) {
    WP_CLI::error('Missing: ' . $news_csv . ' — run build-content-review-artifacts.py first.');
}

$normalize_path = static function (string $url): string {
    $url = strtolower(trim($url));
    $url = preg_replace('#^https?://#', '', $url);
    $url = preg_replace('#^www\.#', '', $url);

    if (str_contains($url, 'stpatricks.ie/')) {
        return trim((string) substr($url, strpos($url, 'stpatricks.ie/') + strlen('stpatricks.ie/')), '/');
    }

    return trim($url, '/');
};

$client_paths = [];

if (is_readable($tracker_csv)) {
    $handle = fopen($tracker_csv, 'r');
    $header = fgetcsv($handle);

    while (($row = fgetcsv($handle)) !== false) {
        $data = array_combine($header, array_pad($row, count($header), ''));
        $url = trim((string) ($data['Old URL'] ?? ''));

        if ($url !== '') {
            $client_paths[$normalize_path($url)] = true;
        }
    }

    fclose($handle);
}

$stats = [
    'news_rows' => 0,
    'dates_updated' => 0,
    'flags_set' => 0,
    'flags_cleared' => 0,
    'not_found' => 0,
    'surplus_drafted' => 0,
    'surplus_skipped' => 0,
];

$handle = fopen($news_csv, 'r');
$header = fgetcsv($handle);

while (($row = fgetcsv($handle)) !== false) {
    $stats['news_rows']++;
    $data = array_combine($header, array_pad($row, count($header), ''));
    $old_url = trim((string) ($data['Old URL'] ?? ''));
    $path = $normalize_path($old_url);

    if ($path === '') {
        continue;
    }

    $post_id = matrix_migrate_find_by_old_path($path, 'post');

    if ($post_id <= 0) {
        $stats['not_found']++;
        continue;
    }

    $publish_date = trim((string) ($data['Publish date'] ?? ''));
    $parsed_date = matrix_content_parse_sheet_date($publish_date);

    if ($parsed_date !== null) {
        $current = get_post_field('post_date', $post_id);

        if ($current !== $parsed_date) {
            if (! $dry_run) {
                wp_update_post([
                    'ID' => $post_id,
                    'post_date' => $parsed_date,
                    'post_date_gmt' => get_gmt_from_date($parsed_date),
                ]);
            }

            $stats['dates_updated']++;
        }
    }

    $action = trim((string) ($data['Editorial action'] ?? ''));
    $notes = trim((string) ($data['Editorial notes'] ?? ''));

    if ($action !== '' && $action !== 'None') {
        if (! $dry_run) {
            matrix_content_set_editorial_flag($post_id, $action, $notes);
        }

        $stats['flags_set']++;
    } else {
        if (! $dry_run) {
            matrix_content_set_editorial_flag($post_id, '', '');
        }

        $stats['flags_cleared']++;
    }

    update_post_meta($post_id, '_matrix_content_on_client_list', 'yes');
}

fclose($handle);

$query = new WP_Query([
    'post_type' => 'post',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'meta_key' => '_matrix_migrate_old_path',
    'fields' => 'ids',
    'no_found_rows' => true,
]);

foreach ($query->posts as $post_id) {
    $post_id = (int) $post_id;
    $path = trim((string) get_post_meta($post_id, '_matrix_migrate_old_path', true), '/');

    if ($path === '' || isset($client_paths[$path])) {
        $stats['surplus_skipped']++;
        continue;
    }

    if (! $dry_run) {
        wp_update_post([
            'ID' => $post_id,
            'post_status' => 'draft',
        ]);
        update_post_meta($post_id, '_matrix_content_surplus_drafted', '1');
    }

    $stats['surplus_drafted']++;
}

WP_CLI::success(sprintf(
    'News/events sync%s: rows=%d dates=%d flags=%d cleared=%d not_found=%d surplus_drafted=%d surplus_kept=%d',
    $dry_run ? ' (DRY RUN)' : '',
    $stats['news_rows'],
    $stats['dates_updated'],
    $stats['flags_set'],
    $stats['flags_cleared'],
    $stats['not_found'],
    $stats['surplus_drafted'],
    $stats['surplus_skipped']
));
