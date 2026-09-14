<?php

/**
 * Sync Clinician Insights (GP eNewsletter) content:
 *  - Ensure clinician-insights is a top-level category (not under Blogs & Articles)
 *  - Reassign GP eNewsletter posts to clinician-insights only
 *  - Import any client-listed articles not yet in WordPress (insert-only)
 *  - Publish all client-listed clinician insight articles and apply sheet dates
 *
 * Never deletes content.
 *
 * Run:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/migrate-sync-clinician-insights.php dry-run
 *   wp eval-file wp-content/themes/matrix-starter/scripts/migrate-sync-clinician-insights.php
 */

require_once get_template_directory() . '/inc/migrate-functions.php';
require_once get_template_directory() . '/inc/content-tracker-functions.php';

if (! class_exists('WP_CLI')) {
    exit(1);
}

$dry_run = matrix_migrate_is_dry_run();
$theme = dirname(__DIR__);
$tracker_csv = $theme . '/old/content/CLINICIAN-INSIGHTS-TRACKER.csv';

$clinician_insights_id = matrix_migrate_ensure_clinician_insights_category();

if ($clinician_insights_id <= 0) {
    WP_CLI::error('Could not ensure clinician-insights category.');
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

$stats = [
    'category_id' => $clinician_insights_id,
    'reassigned' => 0,
    'already_correct' => 0,
    'import_created' => 0,
    'import_skipped' => 0,
    'import_no_source' => 0,
    'import_failed' => 0,
    'published' => 0,
    'already_published' => 0,
    'dates_updated' => 0,
    'not_found' => 0,
];

$enewsletter_posts = get_posts([
    'post_type' => 'post',
    'post_status' => 'any',
    'posts_per_page' => -1,
    'meta_query' => [
        [
            'key' => '_matrix_migrate_old_path',
            'value' => 'st-patricks-mental-health-services-enewsletter/',
            'compare' => 'LIKE',
        ],
    ],
]);

$newsletter_term = get_category_by_slug('newsletter');
$newsletter_id = $newsletter_term instanceof WP_Term ? (int) $newsletter_term->term_id : 0;
$blog_term = get_category_by_slug('blog');
$blog_id = $blog_term instanceof WP_Term ? (int) $blog_term->term_id : 0;

foreach ($enewsletter_posts as $post) {
    if (! $post instanceof WP_Post) {
        continue;
    }

    $terms = wp_get_post_terms($post->ID, 'category', ['fields' => 'ids']);

    if (is_wp_error($terms)) {
        continue;
    }

    $term_ids = array_map('intval', (array) $terms);
    $strip_ids = array_values(array_filter([$newsletter_id, $blog_id]));
    $next_ids = [$clinician_insights_id];

    if ($term_ids === $next_ids) {
        $stats['already_correct']++;
        continue;
    }

    if ($dry_run) {
        $stats['reassigned']++;
        continue;
    }

    wp_set_post_categories($post->ID, $next_ids, false);
    $stats['reassigned']++;
}

$import_rows = [];

if (is_readable($tracker_csv)) {
    $handle = fopen($tracker_csv, 'r');
    $header = fgetcsv($handle);

    while (($row = fgetcsv($handle)) !== false) {
        $data = array_combine($header, array_pad($row, count($header), ''));
        $action = trim((string) ($data['Migration action'] ?? ''));

        if ($action !== 'Migrate') {
            continue;
        }

        $import_rows[] = $data;
    }

    fclose($handle);
} else {
    $import_rows = [
        [
            'Title' => 'Overcoming challenges to early diagnosis of bipolar affective disorder in primary care',
            'Old URL' => 'https://www.stpatricks.ie/st-patricks-mental-health-services-enewsletter/september-2021/early-diagnosis-of-bipolar-in-primary-care',
            'Publish date' => 'Autumn 2021',
        ],
        [
            'Title' => 'Recognising and responding to under-represented groups in eating disorders',
            'Old URL' => 'https://www.stpatricks.ie/st-patricks-mental-health-services-enewsletter/autumn-2024/under-represented-groups-eating-disorders',
            'Publish date' => 'Autumn 2024',
        ],
    ];
}

$slug_registry = [];

foreach ($import_rows as $row) {
    $old_url = trim((string) ($row['Old URL'] ?? ''));
    $path = $normalize_path($old_url);

    if ($path === '') {
        $stats['import_no_source']++;
        continue;
    }

    $post_id = matrix_migrate_find_by_old_path($path, 'post');

    if ($post_id <= 0) {
        $html = matrix_migrate_fetch_html_for_path($path);

        if ($html === '') {
            $stats['import_no_source']++;
            continue;
        }

        $parsed = matrix_migrate_extract_parsed_page($html, $path);

        if ($parsed === null) {
            $stats['import_failed']++;
            continue;
        }

        if ($dry_run) {
            $stats['import_created']++;
            $post_id = 0;
        } else {
            $title = (string) ($parsed['title'] ?? ($row['Title'] ?? ''));
            $slug = matrix_migrate_unique_slug(basename($path), $path, $slug_registry);
            $hero_image_id = 0;
            $og_image = (string) ($parsed['og_image'] ?? '');

            if ($og_image !== '') {
                $hero_image_id = matrix_migrate_attachment_id_for_source_path($og_image);
            }

            $publish_date = matrix_content_parse_sheet_date(
                trim((string) ($row['Publish date'] ?? '')),
                $path
            ) ?? matrix_migrate_parse_post_date((string) ($parsed['date_text'] ?? ''), $path);

            $post_id = wp_insert_post([
                'post_type' => 'post',
                'post_status' => 'publish',
                'post_title' => $title,
                'post_name' => $slug,
                'post_content' => (string) ($parsed['body_html'] ?? ''),
                'post_excerpt' => (string) ($parsed['meta_description'] ?? ''),
                'post_date' => $publish_date,
                'post_date_gmt' => get_gmt_from_date($publish_date),
            ], true);

            if (is_wp_error($post_id) || ! $post_id) {
                $stats['import_failed']++;
                continue;
            }

            $post_id = (int) $post_id;
            update_post_meta($post_id, '_matrix_migrate_old_path', $path);
            update_post_meta($post_id, '_matrix_migrate_source', 'clinician-insights-sync');
            update_post_meta($post_id, '_matrix_migrate_section', 'Clinician insights');
            wp_set_post_categories($post_id, [$clinician_insights_id], false);

            if ($hero_image_id > 0) {
                set_post_thumbnail($post_id, $hero_image_id);
            }

            $stats['import_created']++;
            $stats['published']++;
        }
    } else {
        $stats['import_skipped']++;
    }

    if ($post_id <= 0) {
        continue;
    }

    $publish_date = matrix_content_parse_sheet_date(
        trim((string) ($row['Publish date'] ?? '')),
        $path
    );

    $update = ['ID' => $post_id];

    if ($publish_date !== null) {
        $current = get_post_field('post_date', $post_id);

        if ($current !== $publish_date) {
            $update['post_date'] = $publish_date;
            $update['post_date_gmt'] = get_gmt_from_date($publish_date);

            if (! $dry_run) {
                wp_update_post($update);
            }

            $stats['dates_updated']++;
        }
    }

    $status = get_post_status($post_id);

    if ($status !== 'publish') {
        if ($dry_run) {
            $stats['published']++;
        } else {
            wp_update_post([
                'ID' => $post_id,
                'post_status' => 'publish',
            ]);
            wp_set_post_categories($post_id, [$clinician_insights_id], false);
            $stats['published']++;
        }
    } else {
        $stats['already_published']++;
    }
}

WP_CLI::success(sprintf(
    'Clinician insights sync%s: category=%d reassigned=%d already_correct=%d published=%d already_published=%d dates_updated=%d import_created=%d import_skipped=%d import_no_source=%d import_failed=%d not_found=%d',
    $dry_run ? ' (dry-run)' : '',
    $stats['category_id'],
    $stats['reassigned'],
    $stats['already_correct'],
    $stats['published'],
    $stats['already_published'],
    $stats['dates_updated'],
    $stats['import_created'],
    $stats['import_skipped'],
    $stats['import_no_source'],
    $stats['import_failed'],
    $stats['not_found']
));
