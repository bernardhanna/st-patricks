<?php

/**
 * Sync sitemap-breakdown "Migrate" pages to canonical published destinations.
 *
 * - Resolves old stpatricks.ie paths to seeded/published pages (not bulk-import drafts)
 * - Attaches _matrix_migrate_old_path to canonical items
 * - Publishes canonical pages still in draft
 * - Writes old/content/sitemap-canonical-state.csv for tracker enrichment
 *
 * Never deletes content.
 *
 * Run:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/migrate-sync-sitemap-pages.php dry-run
 *   wp eval-file wp-content/themes/matrix-starter/scripts/migrate-sync-sitemap-pages.php
 */

require_once get_template_directory() . '/inc/migrate-functions.php';

if (! class_exists('WP_CLI')) {
    exit(1);
}

$dry_run = matrix_migrate_is_dry_run();
$theme = dirname(__DIR__);
$tracker_csv = $theme . '/old/content/CONTENT-MIGRATION-TRACKER-v2.csv';
$out_csv = $theme . '/old/content/sitemap-canonical-state.csv';

$normalize_path = static function (string $url): string {
    $url = strtolower(trim($url));
    $url = preg_replace('#^https?://#', '', $url);
    $url = preg_replace('#^www\.#', '', $url);

    if (str_contains($url, 'stpatricks.ie/')) {
        return trim((string) substr($url, strpos($url, 'stpatricks.ie/') + strlen('stpatricks.ie/')), '/');
    }

    return trim($url, '/');
};

$seed_scripts = [
    'research/research-ethics-committee' => 'seed-research-ethics-committee.php',
    'careers/work-with-us/attending-for-interview' => 'seed-recruitment-and-useful-information.php',
    'careers/placements-and-work-experience' => 'seed-recruitment-and-useful-information.php',
    'about-us/policies-and-publications/clinical-governance/service-users-feedback' => 'seed-feedback-and-comments.php',
    'care-treatment/your-portal/register' => 'seed-register-for-your-portal.php',
    'cookies' => 'seed-cookie-privacy-policy.php',
    'privacy-notice' => 'seed-data-protection-policy.php',
];

$stats = [
    'rows' => 0,
    'resolved' => 0,
    'published' => 0,
    'meta_set' => 0,
    'meta_moved' => 0,
    'seeds_run' => 0,
    'unresolved' => 0,
];

$rows = [];

if (! is_readable($tracker_csv)) {
    WP_CLI::error('Missing tracker CSV: ' . $tracker_csv);
}

$handle = fopen($tracker_csv, 'r');
$header = fgetcsv($handle);
$seen_paths = [];
$seeds_ran = [];

while (($row = fgetcsv($handle)) !== false) {
    $data = array_combine($header, array_pad($row, count($header), ''));
    $source = (string) ($data['Source file'] ?? '');

    if (! str_contains($source, 'sitemap breakdown')) {
        continue;
    }

    if ((string) ($data['Migration action'] ?? '') !== 'Migrate') {
        continue;
    }

    $old_url = trim((string) ($data['Old URL'] ?? ''));
    $old_path = $normalize_path($old_url);

    if ($old_path === '' || isset($seen_paths[$old_path])) {
        continue;
    }

    $seen_paths[$old_path] = true;
    $stats['rows']++;

    if (! $dry_run && isset($seed_scripts[$old_path]) && ! isset($seeds_ran[$seed_scripts[$old_path]])) {
        $script = $theme . '/scripts/' . $seed_scripts[$old_path];

        if (is_readable($script)) {
            WP_CLI::log('Running ' . basename($script) . ' …');
            require $script;
            $seeds_ran[$seed_scripts[$old_path]] = true;
            $stats['seeds_run']++;
        }
    }

    $resolved = matrix_migrate_resolve_sitemap_item($old_path);

    if ((int) ($resolved['post_id'] ?? 0) <= 0) {
        $stats['unresolved']++;
        continue;
    }

    $stats['resolved']++;
    $post_id = (int) $resolved['post_id'];
    $post_type = (string) ($resolved['post_type'] ?? get_post_type($post_id));
    $target_path = (string) ($resolved['target_path'] ?? '');

    if (! $dry_run) {
        $duplicate_ids = get_posts([
            'post_type' => $post_type === 'page' ? 'page' : ['page', 'programmes_therapies', 'mental_health'],
            'post_status' => 'any',
            'posts_per_page' => -1,
            'meta_key' => '_matrix_migrate_old_path',
            'meta_value' => $old_path,
            'fields' => 'ids',
            'exclude' => [$post_id],
        ]);

        foreach ($duplicate_ids as $duplicate_id) {
            delete_post_meta((int) $duplicate_id, '_matrix_migrate_old_path');
            $stats['meta_moved']++;
        }

        $current_meta = trim((string) get_post_meta($post_id, '_matrix_migrate_old_path', true));

        if ($current_meta !== $old_path) {
            update_post_meta($post_id, '_matrix_migrate_old_path', $old_path);
            $stats['meta_set']++;
        }

        if (get_post_status($post_id) !== 'publish') {
            wp_update_post([
                'ID' => $post_id,
                'post_status' => 'publish',
            ]);
            $stats['published']++;
        }
    } elseif (get_post_status($post_id) !== 'publish') {
        $stats['published']++;
    }

    $rows[] = [
        'old_path' => $old_path,
        'post_id' => (string) $post_id,
        'post_type' => $post_type,
        'post_status' => $dry_run ? get_post_status($post_id) : 'publish',
        'permalink' => get_permalink($post_id),
        'title' => get_the_title($post_id),
        'target_path' => $target_path,
    ];
}

fclose($handle);

if (! $dry_run) {
    $out = fopen($out_csv, 'w');

    if ($out === false) {
        WP_CLI::error('Could not write: ' . $out_csv);
    }

    fputcsv($out, ['old_path', 'post_id', 'post_type', 'post_status', 'permalink', 'title', 'target_path']);

    foreach ($rows as $row) {
        fputcsv($out, [
            $row['old_path'],
            $row['post_id'],
            $row['post_type'],
            $row['post_status'],
            $row['permalink'],
            $row['title'],
            $row['target_path'],
        ]);
    }

    fclose($out);
}

WP_CLI::success(sprintf(
    'Sitemap sync%s: rows=%d resolved=%d published=%d meta_set=%d meta_moved=%d seeds_run=%d unresolved=%d',
    $dry_run ? ' (dry-run)' : '',
    $stats['rows'],
    $stats['resolved'],
    $stats['published'],
    $stats['meta_set'],
    $stats['meta_moved'],
    $stats['seeds_run'],
    $stats['unresolved']
));

if (! $dry_run) {
    WP_CLI::log('Wrote ' . $out_csv);
}
