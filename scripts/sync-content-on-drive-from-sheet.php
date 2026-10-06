<?php

/**
 * Sync "Content on Drive" from the client workbook into post meta.
 *
 * Reads: old/content/St Patricks Content - List (1).xlsx
 * Writes: matrix_content_on_drive = yes|no
 *
 * Usage:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/sync-content-on-drive-from-sheet.php dry-run
 *   wp eval-file wp-content/themes/matrix-starter/scripts/sync-content-on-drive-from-sheet.php
 */

if (! defined('ABSPATH') || ! class_exists('WP_CLI')) {
    exit(1);
}

require_once WP_PLUGIN_DIR . '/matrix-content-gathering/vendor/autoload.php';
require_once get_template_directory() . '/scripts/lib/drive-library-workbook.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$dry_run = in_array('dry-run', $GLOBALS['argv'] ?? [], true);
$xlsx = get_template_directory() . '/old/content/St Patricks Content - List (1).xlsx';

if (! is_readable($xlsx)) {
    WP_CLI::error('Missing workbook: ' . $xlsx);
}

$wb = IOFactory::load($xlsx);
$stats = ['yes' => 0, 'no' => 0, 'skipped' => 0, 'missing' => 0];

foreach ($wb->getWorksheetIterator() as $ws) {
    $rows = $ws->toArray(null, true, true, false);

    if ($rows === [] || ! is_array($rows[0] ?? null)) {
        continue;
    }

    $headers = array_map(static function ($h) {
        return is_string($h) ? strtolower(trim($h)) : '';
    }, $rows[0]);

    $index = [];

    foreach ($headers as $i => $h) {
        if ($h !== '') {
            $index[$h] = $i;
        }
    }

    if (! isset($index['content on drive']) && ! isset($index['drive folder'])) {
        continue;
    }

    $local_i = $index['local url'] ?? null;
    $form_i = $index['local form link'] ?? null;
    $on_drive_i = $index['content on drive'] ?? null;
    $drive_folder_i = $index['drive folder'] ?? null;

    for ($r = 1, $count = count($rows); $r < $count; $r++) {
        $row = $rows[$r];
        $on_drive = $on_drive_i !== null ? trim((string) ($row[$on_drive_i] ?? '')) : '';
        $drive_folder = $drive_folder_i !== null ? trim((string) ($row[$drive_folder_i] ?? '')) : '';

        // Accept Yes/Y in Content on Drive, or legacy Drive folder = Y
        $value = '';

        if (strcasecmp($on_drive, 'Yes') === 0 || strcasecmp($on_drive, 'Y') === 0) {
            $value = 'yes';
        } elseif (strcasecmp($on_drive, 'No') === 0 || strcasecmp($on_drive, 'N') === 0) {
            $value = 'no';
        } elseif ($on_drive === '' && (strcasecmp($drive_folder, 'Y') === 0 || strcasecmp($drive_folder, 'Yes') === 0)) {
            $value = 'yes';
        }

        if ($value === '') {
            $stats['skipped']++;
            continue;
        }

        $post_id = 0;
        $form = $form_i !== null ? (string) ($row[$form_i] ?? '') : '';

        if (preg_match('/matrix_page=(\d+)/', $form, $m)) {
            $post_id = (int) $m[1];
        }

        if ($post_id <= 0 && $local_i !== null) {
            $local = trim((string) ($row[$local_i] ?? ''));
            $path = trim((string) (wp_parse_url($local, PHP_URL_PATH) ?: ''), '/');

            if ($path !== '') {
                $page = get_page_by_path($path);

                if ($page instanceof WP_Post) {
                    $post_id = (int) $page->ID;
                } else {
                    $found = get_posts([
                        'name' => basename($path),
                        'post_type' => 'any',
                        'post_status' => 'any',
                        'posts_per_page' => 5,
                    ]);

                    foreach ($found as $candidate) {
                        $candidate_path = trim((string) wp_parse_url((string) get_permalink($candidate), PHP_URL_PATH), '/');

                        if ($candidate_path === $path) {
                            $post_id = (int) $candidate->ID;
                            break;
                        }
                    }
                }
            }
        }

        if ($post_id <= 0 || ! get_post($post_id)) {
            $stats['missing']++;
            continue;
        }

        if ($dry_run) {
            WP_CLI::log(sprintf('[dry-run] #%d → %s', $post_id, $value));
        } else {
            update_post_meta($post_id, MATRIX_WORKBOOK_CONTENT_ON_DRIVE_META_KEY, $value);
        }

        $stats[$value]++;
    }
}

WP_CLI::success(sprintf(
    '%sContent on Drive sync — yes:%d no:%d skipped:%d missing:%d',
    $dry_run ? '[DRY RUN] ' : '',
    $stats['yes'],
    $stats['no'],
    $stats['skipped'],
    $stats['missing']
));
