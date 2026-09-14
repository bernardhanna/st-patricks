<?php
/**
 * Refresh content-gathering form token + Form Link URLs from existing WP post IDs in the xlsx.
 * Does not create pages. Use after Updraft/staging sync when links say "Invalid or expired".
 *
 * Usage: wp eval-file scripts/refresh-content-gathering-form-links.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

if (! class_exists('Matrix_Export') || ! class_exists('Matrix_Flexible_Pages')) {
    WP_CLI::error('matrix-content-gathering plugin not loaded.');
}

$autoload = WP_PLUGIN_DIR . '/matrix-content-gathering/vendor/autoload.php';
if (! is_readable($autoload)) {
    WP_CLI::error('PhpSpreadsheet missing.');
}
require_once $autoload;

$staging_home = 'https://st-patricks.s1.matrix-test.com';
$xlsx_path    = get_template_directory() . '/old/content/St Patricks Content Migration  and gathering (2).xlsx';

if (! is_readable($xlsx_path)) {
    WP_CLI::error('Missing xlsx: ' . $xlsx_path);
}

$to_staging = static function ($url) use ($staging_home) {
    $url = (string) $url;
    if ($url === '') {
        return '';
    }
    $parts = wp_parse_url($url);
    $path  = isset($parts['path']) ? $parts['path'] : '/';
    $query = isset($parts['query']) && $parts['query'] !== '' ? '?' . $parts['query'] : '';
    return rtrim($staging_home, '/') . $path . $query;
};

$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($xlsx_path);
$all_ids = [];

// Items: WP post ID col O, Form Link U, Form mode V
$items = $spreadsheet->getSheetByName('Items');
$item_rows = [];
if ($items) {
    $highest = $items->getHighestRow();
    for ($row = 2; $row <= $highest; $row++) {
        $pid = (int) $items->getCell('O' . $row)->getValue();
        $mode = strtolower(trim((string) $items->getCell('V' . $row)->getValue()));
        if ($pid > 0 && get_post($pid)) {
            $all_ids[] = $pid;
            $item_rows[] = ['row' => $row, 'id' => $pid, 'mode' => $mode];
            if ($mode === 'builder') {
                Matrix_Flexible_Pages::set_flexible_page($pid, true);
            } elseif ($mode === 'fixed') {
                Matrix_Flexible_Pages::set_flexible_page($pid, false);
            }
        }
    }
}

// Set Pages: E = WP post ID, C = Form Link, D = mode
$set = $spreadsheet->getSheetByName('Set Pages');
$set_rows = [];
if ($set) {
    $highest = $set->getHighestRow();
    for ($row = 2; $row <= $highest; $row++) {
        $pid = (int) $set->getCell('E' . $row)->getValue();
        if ($pid > 0 && get_post($pid)) {
            $all_ids[] = $pid;
            $set_rows[] = ['row' => $row, 'id' => $pid];
            Matrix_Flexible_Pages::set_flexible_page($pid, false);
        }
    }
}

$all_ids = array_values(array_unique(array_filter($all_ids)));
if (empty($all_ids)) {
    WP_CLI::error('No valid WP post IDs found in the sheet. Run generate-content-gathering-links.php first on this environment.');
}

$token = Matrix_Export::create_client_link($all_ids, [
    'expires_days' => 0,
    'custom_instructions' => 'St Patrick\'s content gathering — refreshed form link.',
    'requires_approval' => false,
    'strict_mode' => false,
    'ai_mode' => false,
]);

if ($token === '') {
    WP_CLI::error('Failed to create token.');
}

$base = $to_staging(Matrix_Export::get_client_link_url($token));
$form_for = static function ($pid) use ($base, $to_staging) {
    return $to_staging(add_query_arg('matrix_page', (int) $pid, $base));
};

foreach ($item_rows as $r) {
    $items->setCellValue('U' . $r['row'], $form_for($r['id']));
    if ($r['id']) {
        $items->setCellValue('Q' . $r['row'], $to_staging(get_permalink($r['id'])));
        $items->setCellValue('P' . $r['row'], get_post_status($r['id']));
    }
}

foreach ($set_rows as $r) {
    $set->setCellValue('C' . $r['row'], $form_for($r['id']));
    $set->setCellValue('D' . $r['row'], 'Fixed');
}

$summary = $spreadsheet->getSheetByName('Summary');
if ($summary) {
    $summary->setCellValue('A24', 'Content gathering forms');
    $summary->setCellValue('B24', 'Refreshed ' . gmdate('Y-m-d H:i') . ' UTC');
    $summary->setCellValue('A25', 'Form token');
    $summary->setCellValue('B25', $token);
    $summary->setCellValue('A26', 'Base form URL');
    $summary->setCellValue('B26', $base);
}

$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
$writer->save($xlsx_path);

// CSV for Google Sheet paste
$csv_path = get_template_directory() . '/old/content/CONTENT-GATHERING-FORM-LINKS.csv';
$fh = fopen($csv_path, 'w');
fputcsv($fh, ['Sheet', 'Row hint', 'WP post ID', 'Form Link']);
foreach ($item_rows as $r) {
    fputcsv($fh, ['Items', 'row ' . $r['row'], $r['id'], $form_for($r['id'])]);
}
foreach ($set_rows as $r) {
    fputcsv($fh, ['Set Pages', 'row ' . $r['row'], $r['id'], $form_for($r['id'])]);
}
fclose($fh);

// Option snapshot for staging (only works if post IDs match)
$links = Matrix_Export::get_client_links();
$snapshot = [
    'token' => $token,
    'base_form_url' => $base,
    'post_count' => count($all_ids),
    'option_name' => Matrix_Export::CLIENT_LINKS_OPTION,
    'option_value' => $links,
];
file_put_contents(
    get_template_directory() . '/old/content/matrix-export-client-links-snapshot.json',
    wp_json_encode($snapshot, JSON_PRETTY_PRINT)
);

WP_CLI::success('New token: ' . $token);
WP_CLI::success('Base form: ' . $base);
WP_CLI::log('Updated ' . $xlsx_path);
WP_CLI::log('Re-upload the xlsx Form Link columns to Google Sheets.');
WP_CLI::log('On staging, either restore DB options from this environment, or run this same script there (preferred).');
