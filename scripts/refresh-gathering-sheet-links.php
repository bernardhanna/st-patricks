<?php

/**
 * Refresh St Patricks Content Migration and gathering (2).xlsx:
 * - All Form Link / Staging URL values use https://st-patricks.s1.matrix-test.com (never localhost)
 * - Backfill WP post ID + Staging URL from Form Link ?matrix_page= when missing
 * - Clear dev-only Local URL column values on client sheets
 *
 * Usage:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/refresh-gathering-sheet-links.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

if (! class_exists('Matrix_Export')) {
    WP_CLI::error('matrix-content-gathering plugin not loaded.');
}

require_once get_template_directory() . '/inc/content-tracker-functions.php';

$autoload = WP_PLUGIN_DIR . '/matrix-content-gathering/vendor/autoload.php';
if (! is_readable($autoload)) {
    WP_CLI::error('PhpSpreadsheet missing.');
}
require_once $autoload;

$staging_home = 'https://st-patricks.s1.matrix-test.com';
$xlsx_path = get_template_directory() . '/old/content/St Patricks Content Migration  and gathering (2).xlsx';

if (! is_readable($xlsx_path)) {
    WP_CLI::error('Missing xlsx: ' . $xlsx_path);
}

$to_staging = static function (string $url) use ($staging_home): string {
    if ($url === '') {
        return '';
    }

    $url = preg_replace('#^https?://localhost(?::\d+)?#i', $staging_home, $url);
    $url = preg_replace('#^http://127\.0\.0\.1(?::\d+)?#i', $staging_home, $url);

    $parts = wp_parse_url($url);
    $path = isset($parts['path']) ? (string) $parts['path'] : '/';
    $query = isset($parts['query']) && $parts['query'] !== '' ? '?' . $parts['query'] : '';

    if (isset($parts['host']) && str_contains((string) $parts['host'], 'matrix-test.com')) {
        return $url;
    }

    return rtrim($staging_home, '/') . $path . $query;
};

$post_id_from_form_link = static function (string $url): int {
    if ($url === '') {
        return 0;
    }

    $query = (string) wp_parse_url($url, PHP_URL_QUERY);
    if ($query === '') {
        return 0;
    }

    parse_str($query, $args);

    return (int) ($args['matrix_page'] ?? 0);
};

$set_hyperlink = static function ($sheet, int $col, int $row, string $url): void {
    $cell = $sheet->getCellByColumnAndRow($col, $row);
    $cell->setValue($url);
    if ($url !== '') {
        $cell->getHyperlink()->setUrl($url);
        return;
    }

    $cell->setHyperlink(null);
};

$header_map_for = static function ($sheet): array {
    $headers = [];
    $col = 1;
    while (true) {
        $val = trim((string) $sheet->getCellByColumnAndRow($col, 1)->getValue());
        if ($val === '') {
            break;
        }
        $headers[$val] = $col;
        $col++;
    }

    return $headers;
};

$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($xlsx_path);
$stats = [
    'form_links_staging' => 0,
    'staging_urls_filled' => 0,
    'wp_ids_filled' => 0,
    'local_cleared' => 0,
    'localhost_fixed' => 0,
];

$all_post_ids = [];

foreach ($spreadsheet->getAllSheets() as $sheet) {
    $sheet_name = $sheet->getTitle();
    $headers = $header_map_for($sheet);
    if ($headers === []) {
        continue;
    }

    $form_cols = array_keys(array_filter($headers, static fn ($name) => stripos($name, 'form link') !== false));
    $url_like_cols = array_keys(array_filter(
        $headers,
        static fn ($name) => preg_match('/(staging url|form link|page link|local url)/i', $name)
    ));

    $highest = (int) $sheet->getHighestRow();
    for ($row = 2; $row <= $highest; $row++) {
        foreach ($url_like_cols as $col_name) {
            $col = $headers[$col_name];
            $cell = $sheet->getCellByColumnAndRow($col, $row);
            $value = trim((string) $cell->getValue());
            $hyperlink = $cell->getHyperlink()->getUrl();
            if ($value === '' && $hyperlink !== '') {
                $value = trim((string) $hyperlink);
            }
            if ($value === '') {
                continue;
            }

            if (stripos($col_name, 'local url') !== false || stripos($col_name, 'dev note') !== false) {
                $set_hyperlink($sheet, $col, $row, '');
                $stats['local_cleared']++;
                continue;
            }

            if (stripos($value, 'localhost') !== false || str_contains($value, ':10034')) {
                $stats['localhost_fixed']++;
            }

            $staging = $to_staging($value);
            if ($staging !== $value || $hyperlink !== $staging) {
                $set_hyperlink($sheet, $col, $row, $staging);
            }
            if (stripos($col_name, 'form link') !== false && $staging !== '') {
                $stats['form_links_staging']++;
            }
        }

        if ($sheet_name !== 'Items') {
            continue;
        }

        $form_col = $headers['Form Link'] ?? null;
        $wp_col = $headers['WP post ID'] ?? null;
        $stg_col = $headers['Staging URL'] ?? null;
        $status_col = $headers['WP status'] ?? null;

        if (! $form_col || ! $stg_col) {
            continue;
        }

        $form = trim((string) $sheet->getCellByColumnAndRow($form_col, $row)->getValue());
        $form = $to_staging($form);
        if ($form !== '') {
            $set_hyperlink($sheet, $form_col, $row, $form);
        }

        $wp_id = $wp_col ? (int) $sheet->getCellByColumnAndRow($wp_col, $row)->getValue() : 0;
        if ($wp_id <= 0 && $form !== '') {
            $wp_id = $post_id_from_form_link($form);
            if ($wp_id > 0 && $wp_col) {
                $sheet->setCellValueByColumnAndRow($wp_col, $row, $wp_id);
                $stats['wp_ids_filled']++;
            }
        }

        $staging_url = trim((string) $sheet->getCellByColumnAndRow($stg_col, $row)->getValue());
        if (($staging_url === '' || ! str_contains($staging_url, 'matrix-test.com')) && $wp_id > 0 && get_post($wp_id)) {
            $staging_url = matrix_content_staging_permalink($wp_id);
            $set_hyperlink($sheet, $stg_col, $row, $staging_url);
            $stats['staging_urls_filled']++;
        }

        if ($status_col && $wp_id > 0 && get_post($wp_id)) {
            $sheet->setCellValueByColumnAndRow($status_col, $row, get_post_status($wp_id));
        }

        if ($wp_id > 0 && get_post($wp_id)) {
            $all_post_ids[] = $wp_id;
        }
    }
}

// Rename dev-only Orlaith sheet column if present.
$orlaith = $spreadsheet->getSheetByName('Orlaith Drive pages');
if ($orlaith) {
    $headers = $header_map_for($orlaith);
    $form_col = $headers['Form Link'] ?? $headers['Form Link (local)'] ?? null;
    if (isset($headers['Form Link (local)'])) {
        $orlaith->setCellValueByColumnAndRow($headers['Form Link (local)'], 1, 'Form Link');
        $form_col = $headers['Form Link (local)'];
    }
    if ($form_col) {
        $highest = (int) $orlaith->getHighestRow();
        for ($row = 2; $row <= $highest; $row++) {
            $form = trim((string) $orlaith->getCellByColumnAndRow($form_col, $row)->getValue());
            $staging_form = $to_staging($form);
            if ($staging_form !== $form) {
                $set_hyperlink($orlaith, $form_col, $row, $staging_form);
                $stats['localhost_fixed']++;
            }
        }
    }
    if (isset($headers['Local URL'])) {
        $orlaith->setCellValueByColumnAndRow($headers['Local URL'], 1, 'Dev note');
    }
    if (isset($headers['Dev note'])) {
        $dev_col = $headers['Dev note'];
        $highest = (int) $orlaith->getHighestRow();
        for ($row = 2; $row <= $highest; $row++) {
            $set_hyperlink($orlaith, $dev_col, $row, '');
        }
    }
}

// Clear dev-only URL column on Items if present.
$items = $spreadsheet->getSheetByName('Items');
if ($items) {
    $item_headers = $header_map_for($items);
    if (isset($item_headers['Local URL'])) {
        $local_col = $item_headers['Local URL'];
        $highest = (int) $items->getHighestRow();
        for ($row = 2; $row <= $highest; $row++) {
            $set_hyperlink($items, $local_col, $row, '');
        }
        if (isset($item_headers['Dev note'])) {
            $items->removeColumnByIndex($local_col);
        } else {
            $items->setCellValueByColumnAndRow($local_col, 1, 'Dev note');
        }
    }
    if (isset($item_headers['Dev note'])) {
        $dev_col = $item_headers['Dev note'];
        $highest = (int) $items->getHighestRow();
        for ($row = 2; $row <= $highest; $row++) {
            $set_hyperlink($items, $dev_col, $row, '');
        }
    }
}

$summary = $spreadsheet->getSheetByName('Summary');
if ($summary) {
    $summary->setCellValue('A30', 'Sheet links refreshed');
    $summary->setCellValue('B30', gmdate('Y-m-d H:i') . ' UTC — staging URLs only');
}

$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
$writer->save($xlsx_path);

WP_CLI::success('Updated ' . basename($xlsx_path));
WP_CLI::log('Form links normalised to staging: ' . $stats['form_links_staging']);
WP_CLI::log('Staging URLs backfilled: ' . $stats['staging_urls_filled']);
WP_CLI::log('WP post IDs backfilled: ' . $stats['wp_ids_filled']);
WP_CLI::log('Local URL cells cleared: ' . $stats['local_cleared']);
WP_CLI::log('Localhost URLs rewritten: ' . $stats['localhost_fixed']);
