<?php

/**
 * Add "Added from form" column to the client workbook.
 *
 * - Inserts the column after "Added from drive"
 * - Sets Yes where Notes mention form edits
 * - Strips form-edit phrases from Notes (keeps remaining editorial text)
 * - Syncs matrix_added_from_form post meta for matched Local URL / form-link rows
 * - Copies the updated workbook to the Desktop
 *
 * Usage:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/add-added-from-form-column.php dry-run
 *   wp eval-file wp-content/themes/matrix-starter/scripts/add-added-from-form-column.php
 */

if (! defined('ABSPATH') || ! class_exists('WP_CLI')) {
    exit(1);
}

require_once WP_PLUGIN_DIR . '/matrix-content-gathering/vendor/autoload.php';
require_once get_template_directory() . '/scripts/lib/drive-library-workbook.php';

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$dry_run = in_array('dry-run', $GLOBALS['argv'] ?? [], true);
$xlsx = get_template_directory() . '/old/content/St Patricks Content - List (1).xlsx';
$desktop = getenv('HOME') . '/Desktop/St Patricks Content - List (1).xlsx';

if (! is_readable($xlsx)) {
    WP_CLI::error('Missing workbook: ' . $xlsx);
}

/**
 * True when Notes indicate content was added/edited via the form.
 */
function matrix_notes_indicate_form(string $notes): bool
{
    $n = strtolower($notes);

    return (bool) preg_match('/\bform used\b|\bcontent input on forms?\b|\binput(ted)? (on|via|through) (the )?forms?\b/i', $notes)
        || str_contains($n, 'content input on form');
}

/**
 * Remove form-edit phrases; keep remaining editorial notes.
 */
function matrix_strip_form_phrases_from_notes(string $notes): string
{
    $cleaned = $notes;

    // Leading "Content input on forms." / "Content input on form."
    $cleaned = preg_replace('/^\s*content input on forms?\.?\s*/i', '', $cleaned) ?? $cleaned;

    // "Form used for review following migration" (keep what follows after ; or leave rest)
    $cleaned = preg_replace('/\bform used for review following migration\b[;,]?\s*/i', '', $cleaned) ?? $cleaned;

    // "Form used for some content but " → drop up through "but "
    $cleaned = preg_replace('/\bform used for some content but\s+/i', '', $cleaned) ?? $cleaned;

    // Plain "Form used" optionally followed by ; or ,
    $cleaned = preg_replace('/\bform used\b[;,]?\s*/i', '', $cleaned) ?? $cleaned;

    // Tidy leftover separators / whitespace
    $cleaned = preg_replace('/\s*;\s*;\s*/', '; ', $cleaned) ?? $cleaned;
    $cleaned = preg_replace('/^\s*[;,.]\s*/', '', $cleaned) ?? $cleaned;
    $cleaned = preg_replace('/\s+/', ' ', $cleaned) ?? $cleaned;
    $cleaned = trim($cleaned, " \t\n\r\0\x0B;.");

    // Capitalise first letter if we left a sentence fragment
    if ($cleaned !== '' && preg_match('/^[a-z]/', $cleaned)) {
        $cleaned = ucfirst($cleaned);
    }

    return $cleaned;
}

/**
 * Resolve a workbook row to a post ID via Local URL or form-link post_id=.
 */
function matrix_workbook_row_post_id(string $local_url, string $form_link): int
{
    if ($form_link !== '' && preg_match('/[?&]post_id=(\d+)/', $form_link, $m)) {
        return (int) $m[1];
    }

    $path = (string) (wp_parse_url($local_url, PHP_URL_PATH) ?? '');
    $path = trim($path, '/');

    if ($path === '' || $path === '/') {
        $front = (int) get_option('page_on_front');

        return $front > 0 ? $front : 0;
    }

    $page = get_page_by_path($path, OBJECT, ['page', 'post']);
    if ($page instanceof WP_Post) {
        return (int) $page->ID;
    }

    // Try CPT path segments (last slug).
    $slug = basename($path);
    $q = new WP_Query([
        'name' => $slug,
        'post_type' => 'any',
        'post_status' => 'any',
        'posts_per_page' => 1,
        'fields' => 'ids',
    ]);

    return ! empty($q->posts[0]) ? (int) $q->posts[0] : 0;
}

$wb = IOFactory::load($xlsx);
$stats = [
    'sheets_touched' => 0,
    'columns_inserted' => 0,
    'marked_yes' => 0,
    'notes_cleared' => 0,
    'meta_set' => 0,
    'examples' => [],
];

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

    if (! isset($index['title'])) {
        continue;
    }

    $sheet_changed = false;

    // Ensure Added from form exists after Added from drive (or Content on Drive / Notes fallback).
    if (! isset($index['added from form'])) {
        $after = $index['added from drive']
            ?? $index['content on drive']
            ?? (isset($index['notes']) ? $index['notes'] - 1 : count($headers) - 1);
        $insert_at = $after + 2; // 1-based Excel column after that 0-based index
        $ws->insertNewColumnBefore(Coordinate::stringFromColumnIndex($insert_at));
        $ws->setCellValue(Coordinate::stringFromColumnIndex($insert_at) . '1', 'Added from form');
        $sheet_changed = true;
        $stats['columns_inserted']++;

        $rows = $ws->toArray(null, true, true, false);
        $headers = array_map(static function ($h) {
            return is_string($h) ? strtolower(trim($h)) : '';
        }, $rows[0]);
        $index = [];
        foreach ($headers as $i => $h) {
            if ($h !== '') {
                $index[$h] = $i;
            }
        }
    }

    if (! isset($index['added from form'], $index['notes'])) {
        continue;
    }

    $title_i = $index['title'];
    $notes_i = $index['notes'];
    $form_i = $index['added from form'];
    $local_i = $index['local url'] ?? null;
    $form_link_i = $index['local form link'] ?? null;
    $form_col = Coordinate::stringFromColumnIndex($form_i + 1);
    $notes_col = Coordinate::stringFromColumnIndex($notes_i + 1);
    $last_row = max(2, $ws->getHighestDataRow());

    for ($r = 1, $count = count($rows); $r < $count; $r++) {
        $row = $rows[$r];
        $title = trim((string) ($row[$title_i] ?? ''));
        if ($title === '') {
            continue;
        }

        $notes = trim((string) ($row[$notes_i] ?? ''));
        $existing_form = strtolower(trim((string) ($row[$form_i] ?? '')));
        $excel_row = $r + 1;
        $is_form = matrix_notes_indicate_form($notes) || in_array($existing_form, ['yes', 'y'], true);

        if (! $is_form) {
            continue;
        }

        $new_notes = matrix_strip_form_phrases_from_notes($notes);
        $local = $local_i !== null ? trim((string) ($row[$local_i] ?? '')) : '';
        $form_link = $form_link_i !== null ? trim((string) ($row[$form_link_i] ?? '')) : '';

        if (! $dry_run) {
            $ws->setCellValue($form_col . $excel_row, 'Yes');
            if ($new_notes !== $notes) {
                $ws->setCellValue($notes_col . $excel_row, $new_notes);
            }

            $post_id = matrix_workbook_row_post_id($local, $form_link);
            if ($post_id > 0) {
                update_post_meta($post_id, MATRIX_WORKBOOK_ADDED_FROM_FORM_META_KEY, 'yes');
                $stats['meta_set']++;
            }
        }

        $stats['marked_yes']++;
        if ($new_notes !== $notes) {
            $stats['notes_cleared']++;
        }

        if (count($stats['examples']) < 12) {
            $stats['examples'][] = sprintf(
                '%s | %s | Yes | notes: "%s" → "%s"',
                $ws->getTitle(),
                $title,
                $notes,
                $new_notes
            );
        }

        $sheet_changed = true;
    }

    if ($sheet_changed) {
        $stats['sheets_touched']++;
        if (! $dry_run) {
            $ws->getColumnDimension($form_col)->setWidth(18);
            matrix_workbook_apply_added_from_form_controls($ws, $last_row, $form_col);
        }
    }
}

if (! $dry_run) {
    // Refresh Summary legend if present.
    $summary = $wb->getSheetByName('Summary');
    if ($summary) {
        $found = false;
        $highest = $summary->getHighestDataRow();
        for ($r = 1; $r <= $highest; $r++) {
            $val = trim((string) $summary->getCell('A' . $r)->getValue());
            if (strcasecmp($val, 'Added from form') === 0) {
                $found = true;
                break;
            }
        }
        if (! $found) {
            $insert = $highest + 2;
            $summary->setCellValue('A' . $insert, 'Added from form');
            $summary->setCellValue('B' . $insert, 'Meaning');
            $summary->getStyle('A' . $insert . ':B' . $insert)->getFont()->setBold(true);
            $summary->setCellValue('A' . ($insert + 1), 'Yes');
            $summary->setCellValue('B' . ($insert + 1), 'Content was added/edited via the content form');
            $summary->setCellValue('A' . ($insert + 2), '(blank)');
            $summary->setCellValue('B' . ($insert + 2), 'Not marked as form-edited');
        }
    }

    $writer = new Xlsx($wb);
    $writer->save($xlsx);
    if (is_dir(dirname($desktop))) {
        copy($xlsx, $desktop);
    }
}

WP_CLI::log(sprintf(
    '%sAdded from form — sheets:%d cols_inserted:%d marked_yes:%d notes_cleared:%d meta_set:%d',
    $dry_run ? '[dry-run] ' : '',
    $stats['sheets_touched'],
    $stats['columns_inserted'],
    $stats['marked_yes'],
    $stats['notes_cleared'],
    $stats['meta_set']
));

foreach ($stats['examples'] as $ex) {
    WP_CLI::log('  ' . $ex);
}

if (! $dry_run) {
    WP_CLI::success('Saved workbook + Desktop copy.');
}
