<?php

/**
 * Sync workbook Status / Status set by from the content-gathering plugin.
 *
 * Source of truth: post meta `matrix_content_status` and `matrix_content_status_done_by`
 * (same values as the client form and wp-admin Content status column).
 *
 * Updates: old/content/St Patricks Content - List (1).xlsx
 * Also writes a Google Sheets paste TSV: old/content/STATUS-FOR-GOOGLE-SHEETS.tsv
 *
 * Usage:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/sync-sheet-status-from-plugin.php dry-run
 *   wp eval-file wp-content/themes/matrix-starter/scripts/sync-sheet-status-from-plugin.php
 */

if (! defined('ABSPATH') || ! class_exists('WP_CLI')) {
    exit(1);
}

require_once WP_PLUGIN_DIR . '/matrix-content-gathering/vendor/autoload.php';
require_once get_template_directory() . '/scripts/lib/content-workbook-helpers.php';

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$dry_run = in_array('dry-run', $GLOBALS['argv'] ?? [], true);
$xlsx = get_template_directory() . '/old/content/St Patricks Content - List (1).xlsx';
$tsv_path = get_template_directory() . '/old/content/STATUS-FOR-GOOGLE-SHEETS.tsv';
$desktop = getenv('HOME') . '/Desktop/St Patricks Content - List (1).xlsx';

if (! is_readable($xlsx)) {
    WP_CLI::error('Missing workbook: ' . $xlsx);
}

/**
 * Resolve a workbook row to a WP post ID.
 */
function matrix_status_row_post_id(string $local_url, string $form_link, string $staging_form = '', string $staging_url = ''): int
{
    foreach ([$form_link, $staging_form] as $link) {
        if ($link !== '' && preg_match('/[?&](?:matrix_page|post_id)=(\d+)/', $link, $m)) {
            $id = (int) $m[1];
            if ($id > 0 && get_post($id)) {
                return $id;
            }
        }
    }

    foreach ([$local_url, $staging_url] as $url) {
        $path = trim((string) (wp_parse_url($url, PHP_URL_PATH) ?? ''), '/');

        if ($path === '' && $url === '') {
            continue;
        }

        if ($path === '' || $path === '/') {
            $front = (int) get_option('page_on_front');
            if ($front > 0) {
                return $front;
            }
            continue;
        }

        $page = get_page_by_path($path, OBJECT, ['page', 'post']);
        if ($page instanceof WP_Post) {
            return (int) $page->ID;
        }

        $slug = basename($path);
        $found = get_posts([
            'name' => $slug,
            'post_type' => 'any',
            'post_status' => 'any',
            'posts_per_page' => 5,
        ]);

        foreach ($found as $candidate) {
            $candidate_path = trim((string) wp_parse_url((string) get_permalink($candidate), PHP_URL_PATH), '/');
            if ($candidate_path === $path) {
                return (int) $candidate->ID;
            }
        }
    }

    return 0;
}

$wb = IOFactory::load($xlsx);
$stats = [
    'rows' => 0,
    'matched' => 0,
    'unchanged' => 0,
    'updated' => 0,
    'missing' => 0,
    'skipped_sheets' => 0,
    'examples' => [],
    'counts' => [
        'To do' => 0,
        'In progress' => 0,
        'Done' => 0,
        'Delete' => 0,
    ],
];
$tsv_rows = [["Sheet", "Title", "Status", "Status set by", "Local URL"]];

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

    if (! isset($index['title'], $index['status'])) {
        $stats['skipped_sheets']++;
        continue;
    }

    $title_i = $index['title'];
    $status_i = $index['status'];
    $set_by_i = $index['status set by'] ?? null;
    $local_i = $index['local url'] ?? null;
    $form_i = $index['local form link'] ?? $index['form link'] ?? null;
    $stg_form_i = $index['staging form link'] ?? null;
    $stg_url_i = $index['staging url'] ?? null;

    $status_col = Coordinate::stringFromColumnIndex($status_i + 1);
    $set_by_col = $set_by_i !== null ? Coordinate::stringFromColumnIndex($set_by_i + 1) : null;
    $last_row = max(2, $ws->getHighestDataRow());
    $sheet_changed = false;

    for ($r = 1, $count = count($rows); $r < $count; $r++) {
        $row = $rows[$r];
        $title = trim((string) ($row[$title_i] ?? ''));
        if ($title === '') {
            continue;
        }

        $stats['rows']++;
        $excel_row = $r + 1;
        $local = $local_i !== null ? trim((string) ($row[$local_i] ?? '')) : '';
        $form = $form_i !== null ? (string) ($row[$form_i] ?? '') : '';
        $stg_form = $stg_form_i !== null ? (string) ($row[$stg_form_i] ?? '') : '';
        $stg_url = $stg_url_i !== null ? trim((string) ($row[$stg_url_i] ?? '')) : '';

        $post_id = matrix_status_row_post_id($local, $form, $stg_form, $stg_url);
        if ($post_id <= 0) {
            $stats['missing']++;
            continue;
        }

        $stats['matched']++;
        $plugin_status = matrix_workbook_status_label_from_meta($post_id);
        $plugin_set_by = matrix_workbook_status_set_by($post_id);
        $sheet_status = trim((string) ($row[$status_i] ?? ''));
        $sheet_set_by = $set_by_i !== null ? trim((string) ($row[$set_by_i] ?? '')) : '';

        if (strcasecmp($sheet_status, 'Deleted') === 0) {
            $sheet_status = 'Delete';
        }

        $stats['counts'][$plugin_status] = ($stats['counts'][$plugin_status] ?? 0) + 1;

        $status_diff = $sheet_status !== $plugin_status;
        $set_by_diff = $set_by_col !== null && $sheet_set_by !== $plugin_set_by;

        $tsv_rows[] = [$ws->getTitle(), $title, $plugin_status, $plugin_set_by, $local];

        if (! $status_diff && ! $set_by_diff) {
            $stats['unchanged']++;
            continue;
        }

        $stats['updated']++;
        if (count($stats['examples']) < 25) {
            $stats['examples'][] = sprintf(
                '%s | %s | "%s"%s → "%s"%s',
                $ws->getTitle(),
                $title,
                $sheet_status,
                $sheet_set_by !== '' ? ' (' . $sheet_set_by . ')' : '',
                $plugin_status,
                $plugin_set_by !== '' ? ' (' . $plugin_set_by . ')' : ''
            );
        }

        if (! $dry_run) {
            $ws->setCellValue($status_col . $excel_row, $plugin_status);
            if ($set_by_col !== null) {
                $ws->setCellValue($set_by_col . $excel_row, $plugin_set_by);
            }
            $sheet_changed = true;
        }
    }

    if ($sheet_changed) {
        matrix_workbook_apply_status_controls($ws, $last_row, $status_col);
        $ws->getColumnDimension($status_col)->setWidth(18);
        if ($set_by_col !== null) {
            $ws->getColumnDimension($set_by_col)->setWidth(30);
        }
    }
}

if (! $dry_run) {
    $writer = new Xlsx($wb);
    $writer->save($xlsx);

    if (is_dir(dirname($desktop))) {
        copy($xlsx, $desktop);
    }

    $fh = fopen($tsv_path, 'w');
    if ($fh) {
        foreach ($tsv_rows as $line) {
            fputcsv($fh, $line, "\t");
        }
        fclose($fh);
    }
}

WP_CLI::log(sprintf(
    '%sStatus sync — rows:%d matched:%d updated:%d unchanged:%d missing:%d skipped_sheets:%d',
    $dry_run ? '[dry-run] ' : '',
    $stats['rows'],
    $stats['matched'],
    $stats['updated'],
    $stats['unchanged'],
    $stats['missing'],
    $stats['skipped_sheets']
));

WP_CLI::log(sprintf(
    'Plugin statuses on matched rows — To do:%d In progress:%d Done:%d Delete:%d',
    $stats['counts']['To do'] ?? 0,
    $stats['counts']['In progress'] ?? 0,
    $stats['counts']['Done'] ?? 0,
    $stats['counts']['Delete'] ?? 0
));

foreach ($stats['examples'] as $ex) {
    WP_CLI::log('  ' . $ex);
}

if ($dry_run) {
    WP_CLI::success('Dry run only — no files written.');
    return;
}

WP_CLI::success('Saved workbook' . (is_file($desktop) ? ' + Desktop copy' : '') . ' + ' . basename($tsv_path) . '.');
