<?php

/**
 * Write Drive folder/Word URLs onto the gathering xlsx Items tab and a compact
 * "Orlaith Drive pages" sheet, plus refresh ORLAITH-AUGUST-NEW-PAGES.csv.
 *
 * Usage: php scripts/add-orlaith-drive-links.php
 */

require_once __DIR__ . '/lib/orlaith-drive-links.php';

$autoload = dirname(__DIR__, 3) . '/plugins/matrix-content-gathering/vendor/autoload.php';
if (! is_readable($autoload)) {
    fwrite(STDERR, "PhpSpreadsheet autoload missing: {$autoload}\n");
    exit(1);
}
require_once $autoload;

$theme = dirname(__DIR__);
$xlsx_path = $theme . '/old/content/St Patricks Content Migration  and gathering (2).xlsx';
$csv_path = $theme . '/old/content/ORLAITH-AUGUST-NEW-PAGES.csv';

if (! is_readable($xlsx_path)) {
    fwrite(STDERR, "Missing xlsx: {$xlsx_path}\n");
    exit(1);
}

$staging_home = 'https://st-patricks.s1.matrix-test.com';

$to_staging = static function (string $url) use ($staging_home): string {
    if ($url === '') {
        return '';
    }

    $url = preg_replace('#^https?://localhost(?::\d+)?#i', $staging_home, $url);
    $url = preg_replace('#^http://127\.0\.0\.1(?::\d+)?#i', $staging_home, $url);
    $parts = function_exists('wp_parse_url') ? wp_parse_url($url) : parse_url($url);
    $path = isset($parts['path']) ? (string) $parts['path'] : '/';
    $query = isset($parts['query']) && $parts['query'] !== '' ? '?' . $parts['query'] : '';

    if (isset($parts['host']) && str_contains((string) $parts['host'], 'matrix-test.com')) {
        return $url;
    }

    return rtrim($staging_home, '/') . $path . $query;
};

$links = matrix_orlaith_drive_links();
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($xlsx_path);
$items = $spreadsheet->getSheetByName('Items');
if (! $items) {
    fwrite(STDERR, "Items sheet missing.\n");
    exit(1);
}

$set_link = static function ($sheet, int $col, int $row, string $url): void {
    $cell = $sheet->getCellByColumnAndRow($col, $row);
    $cell->setValue($url);
    if ($url !== '') {
        $cell->getHyperlink()->setUrl($url);
    }
};

$header_map = [];
for ($col = 1; $col <= 40; $col++) {
    $val = trim((string) $items->getCellByColumnAndRow($col, 1)->getValue());
    if ($val !== '') {
        $header_map[$val] = $col;
    }
}
$ensure_col = static function (string $name, int $fallback) use (&$header_map, $items): int {
    if (isset($header_map[$name])) {
        return $header_map[$name];
    }
    $idx = empty($header_map) ? $fallback : (max($header_map) + 1);
    $header_map[$name] = $idx;
    $items->setCellValueByColumnAndRow($idx, 1, $name);

    return $idx;
};

$id_col = $header_map['ID'] ?? 1;
$title_col = $header_map['Title'] ?? 3;
$section_col = $header_map['Menu / Section'] ?? 2;
$wp_col = $header_map['WP post ID'] ?? 13;
$stg_col = $header_map['Staging URL'] ?? 15;
$form_col = $header_map['Form Link'] ?? 19;
$local_col = $ensure_col('Dev note', 23);
$folder_col = $ensure_col('Drive folder', 21);
$word_col = $ensure_col('Drive Word', 22);

$rows_out = [];
$highest = (int) $items->getHighestRow();
for ($row = 2; $row <= $highest; $row++) {
    $sid = trim((string) $items->getCellByColumnAndRow($id_col, $row)->getValue());
    if (! isset($links[$sid])) {
        continue;
    }
    $folder = $links[$sid]['drive_folder'];
    $word = $links[$sid]['drive_word'];
    $set_link($items, $folder_col, $row, $folder);
    $set_link($items, $word_col, $row, $word);
    $stg = (string) $items->getCellByColumnAndRow($stg_col, $row)->getValue();
    $form = (string) $items->getCellByColumnAndRow($form_col, $row)->getValue();
    $form = $to_staging($form);
    if ($stg !== '') {
        $items->getCellByColumnAndRow($stg_col, $row)->getHyperlink()->setUrl($stg);
    }
    if ($form !== '') {
        $set_link($items, $form_col, $row, $form);
    }
    $set_link($items, $local_col, $row, '');
    $rows_out[$sid] = [
        'id' => $sid,
        'section' => (string) $items->getCellByColumnAndRow($section_col, $row)->getValue(),
        'title' => (string) $items->getCellByColumnAndRow($title_col, $row)->getValue(),
        'wp_id' => (string) $items->getCellByColumnAndRow($wp_col, $row)->getValue(),
        'staging' => $stg,
        'form' => $form,
        'drive_folder' => $folder,
        'drive_word' => $word,
    ];
}

$sheet_name = 'Orlaith Drive pages';
$existing = $spreadsheet->getSheetByName($sheet_name);
if ($existing) {
    $compact = $existing;
    $compact->removeRow(1, max(1, (int) $compact->getHighestRow()));
} else {
    $compact = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, $sheet_name);
    $items_index = $spreadsheet->getIndex($items);
    $spreadsheet->addSheet($compact, $items_index + 1);
}

$headers = ['ID', 'Section', 'Title', 'WP post ID', 'Staging URL', 'Drive folder', 'Drive Word', 'Form Link'];
foreach ($headers as $i => $h) {
    $compact->setCellValueByColumnAndRow($i + 1, 1, $h);
}
$compact->getStyle('A1:H1')->getFont()->setBold(true);

$order = array_keys($links);
$n = 2;
foreach ($order as $sid) {
    if (! isset($rows_out[$sid])) {
        continue;
    }
    $r = $rows_out[$sid];
    $compact->setCellValueByColumnAndRow(1, $n, $r['id']);
    $compact->setCellValueByColumnAndRow(2, $n, $r['section']);
    $compact->setCellValueByColumnAndRow(3, $n, $r['title']);
    $compact->setCellValueByColumnAndRow(4, $n, $r['wp_id']);
    $set_link($compact, 5, $n, $r['staging']);
    $set_link($compact, 6, $n, $r['drive_folder']);
    $set_link($compact, 7, $n, $r['drive_word']);
    $set_link($compact, 8, $n, $r['form']);
    $n++;
}

foreach (range(1, 8) as $c) {
    $compact->getColumnDimensionByColumn($c)->setAutoSize(true);
}
$items->getColumnDimensionByColumn($folder_col)->setWidth(42);
$items->getColumnDimensionByColumn($word_col)->setWidth(42);

$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
$writer->save($xlsx_path);

$fh = fopen($csv_path, 'w');
fputcsv($fh, ['Sheet ID', 'Section', 'Title', 'WP post ID', 'Staging URL', 'Drive folder', 'Drive Word', 'Form Link'], ',', '"', '\\');
foreach ($order as $sid) {
    if (! isset($rows_out[$sid])) {
        continue;
    }
    $r = $rows_out[$sid];
    fputcsv($fh, [$r['id'], $r['section'], $r['title'], $r['wp_id'], $r['staging'], $r['drive_folder'], $r['drive_word'], $r['form']], ',', '"', '\\');
}
fclose($fh);

echo 'Updated ' . $xlsx_path . "\n";
echo 'Wrote ' . count($rows_out) . " rows to Orlaith Drive pages + {$csv_path}\n";
foreach ($order as $sid) {
    if (! isset($rows_out[$sid])) {
        echo "MISSING {$sid}\n";
        continue;
    }
    echo $sid . '  ' . $rows_out[$sid]['title'] . "\n  staging  " . $rows_out[$sid]['staging'] . "\n";
}
