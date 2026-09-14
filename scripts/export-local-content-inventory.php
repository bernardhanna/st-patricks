<?php

/**
 * Export local content inventory spreadsheet.
 *
 * - One sheet per public post type (published only; Pages first; homepage first)
 * - Drafts / pending / private / future moved to a separate Drafts sheet
 * - Columns: Title, Local URL, Staging URL, Status, Notes
 * - Status is a colour-coded dropdown: To do | Completed | Edits required | Delete
 *
 * Usage:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/export-local-content-inventory.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

$autoload = WP_PLUGIN_DIR . '/matrix-content-gathering/vendor/autoload.php';
if (! is_readable($autoload)) {
    WP_CLI::error('PhpSpreadsheet missing (matrix-content-gathering plugin vendor).');
}
require_once $autoload;

use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$local_home = untrailingslashit(home_url('/'));
$staging_home = 'http://st-patricks.s1.matrix-test.com';
$out_dir = get_template_directory() . '/old/content';
$xlsx_path = $out_dir . '/LOCAL-SITE-CONTENT-INVENTORY.xlsx';
$csv_path = $out_dir . '/LOCAL-SITE-CONTENT-INVENTORY.csv';
$front_page_id = (int) get_option('page_on_front');

if (! is_dir($out_dir) && ! wp_mkdir_p($out_dir)) {
    WP_CLI::error('Cannot create output directory: ' . $out_dir);
}

require_once ABSPATH . 'wp-admin/includes/post.php';

$to_staging = static function (string $url) use ($local_home, $staging_home): string {
    if ($url === '') {
        return '';
    }

    $url = str_replace($local_home, $staging_home, $url);
    $url = (string) preg_replace('#^https?://localhost(?::\d+)?#i', $staging_home, $url);
    $url = (string) preg_replace('#^https?://127\.0\.0\.1(?::\d+)?#i', $staging_home, $url);

    return $url;
};

$sheet_title_for_type = static function (string $post_type): string {
    $object = get_post_type_object($post_type);
    $label = $object instanceof WP_Post_Type ? (string) $object->labels->name : $post_type;
    $label = trim($label);
    if ($post_type === 'page') {
        $label = 'Pages';
    } elseif ($post_type === 'post') {
        $label = 'Posts';
    }
    // Excel sheet title max 31 chars; ban : \ / ? * [ ]
    $label = str_replace([':', '\\', '/', '?', '*', '[', ']'], '-', $label);
    if (strlen($label) > 31) {
        $label = substr($label, 0, 31);
    }

    return $label !== '' ? $label : $post_type;
};

/**
 * Pretty "would-be" public URL (works for drafts via sample permalink).
 */
$pretty_url_for = static function (WP_Post $post): string {
    if ($post->post_status === 'publish') {
        $url = (string) get_permalink($post);
        if ($url !== '') {
            return $url;
        }
    }

    $sample = get_sample_permalink($post->ID);
    if (is_array($sample) && isset($sample[0], $sample[1])) {
        $url = str_replace(
            ['%pagename%', '%postname%'],
            (string) $sample[1],
            (string) $sample[0]
        );
        $url = (string) preg_replace('#%[^%]+%#', '', $url);
        if ($url !== '') {
            return $url;
        }
    }

    // Hierarchical pages: build from ancestor slugs.
    if (is_post_type_hierarchical($post->post_type)) {
        $uri = get_page_uri($post);
        if (is_string($uri) && $uri !== '') {
            return home_url(user_trailingslashit($uri));
        }
    }

    if ($post->post_name !== '') {
        return home_url(user_trailingslashit($post->post_name));
    }

    return (string) get_permalink($post);
};

/**
 * Local link that opens when logged into WP admin for drafts.
 */
$local_url_for = static function (WP_Post $post) use ($pretty_url_for): string {
    if ($post->post_status === 'publish') {
        return $pretty_url_for($post);
    }

    // Preview link works while logged into wp-admin (plain ?page_id= 404s when logged out).
    $preview = get_preview_post_link($post);
    if (is_string($preview) && $preview !== '') {
        return $preview;
    }

    return admin_url('post.php?post=' . $post->ID . '&action=edit');
};

$status_options = ['To do', 'Completed', 'Edits required', 'Delete'];
$status_colours = [
    'To do' => ['bg' => 'FEF3C7', 'fg' => '92400E'],
    'Completed' => ['bg' => 'D1FAE5', 'fg' => '065F46'],
    'Edits required' => ['bg' => 'FFEDD5', 'fg' => '9A3412'],
    'Delete' => ['bg' => 'FEE2E2', 'fg' => '991B1B'],
];

$headers = ['Title', 'Local URL', 'Staging URL', 'Status', 'Notes'];

$post_types = get_post_types(['public' => true], 'names');
unset($post_types['attachment']);
$post_types = array_values($post_types);

// Prefer Pages, then Posts, then everything else A–Z by sheet label.
usort($post_types, static function (string $a, string $b) use ($sheet_title_for_type): int {
    $rank = static function (string $type): int {
        if ($type === 'page') {
            return 0;
        }
        if ($type === 'post') {
            return 1;
        }

        return 2;
    };
    $ra = $rank($a);
    $rb = $rank($b);
    if ($ra !== $rb) {
        return $ra <=> $rb;
    }

    return strcasecmp($sheet_title_for_type($a), $sheet_title_for_type($b));
});

$grouped = [];
$draft_items = [];

foreach ($post_types as $post_type) {
    $published = get_posts([
        'post_type' => $post_type,
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ]);

    $drafts = get_posts([
        'post_type' => $post_type,
        'post_status' => ['draft', 'pending', 'private', 'future'],
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ]);

    $sort_items = static function (array $items) use ($post_type, $front_page_id): array {
        if ($post_type === 'page' && $front_page_id > 0) {
            usort($items, static function (WP_Post $a, WP_Post $b) use ($front_page_id): int {
                if ($a->ID === $front_page_id) {
                    return -1;
                }
                if ($b->ID === $front_page_id) {
                    return 1;
                }
                $title = strcasecmp($a->post_title, $b->post_title);
                if ($title !== 0) {
                    return $title;
                }

                return $a->ID <=> $b->ID;
            });
        } else {
            usort($items, static function (WP_Post $a, WP_Post $b): int {
                $title = strcasecmp($a->post_title, $b->post_title);
                if ($title !== 0) {
                    return $title;
                }

                return $a->ID <=> $b->ID;
            });
        }

        return $items;
    };

    $published = $sort_items($published);
    $drafts = $sort_items($drafts);

    if ($published !== []) {
        $grouped[$post_type] = $published;
    }
    foreach ($drafts as $draft) {
        $draft_items[] = $draft;
    }
}

usort($draft_items, static function (WP_Post $a, WP_Post $b) use ($sheet_title_for_type): int {
    $type = strcasecmp($sheet_title_for_type($a->post_type), $sheet_title_for_type($b->post_type));
    if ($type !== 0) {
        return $type;
    }
    $title = strcasecmp($a->post_title, $b->post_title);
    if ($title !== 0) {
        return $title;
    }

    return $a->ID <=> $b->ID;
});

$header_style = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '1E244B'],
    ],
    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
];

$apply_status_controls = static function (Worksheet $sheet, int $last_row, string $status_col = 'D') use ($status_options, $status_colours): void {
    if ($last_row < 2) {
        return;
    }

    $validation = $sheet->getCell($status_col . '2')->getDataValidation();
    $validation->setType(DataValidation::TYPE_LIST);
    $validation->setErrorStyle(DataValidation::STYLE_STOP);
    $validation->setAllowBlank(true);
    $validation->setShowDropDown(true);
    $validation->setShowInputMessage(true);
    $validation->setPromptTitle('Status');
    $validation->setPrompt('Choose To do, Completed, Edits required, or Delete');
    $validation->setShowErrorMessage(true);
    $validation->setFormula1('"' . implode(',', $status_options) . '"');
    $validation->setSqref($status_col . '2:' . $status_col . $last_row);

    $conditionals = [];
    foreach ($status_colours as $label => $colours) {
        $conditional = new Conditional();
        $conditional->setConditionType(Conditional::CONDITION_CELLIS);
        $conditional->setOperatorType(Conditional::OPERATOR_EQUAL);
        $conditional->addCondition('"' . $label . '"');
        $conditional->getStyle()->getFill()->setFillType(Fill::FILL_SOLID);
        $conditional->getStyle()->getFill()->getStartColor()->setRGB($colours['bg']);
        $conditional->getStyle()->getFont()->getColor()->setRGB($colours['fg']);
        $conditional->getStyle()->getFont()->setBold(true);
        $conditionals[] = $conditional;
    }
    $sheet->getStyle($status_col . '2:' . $status_col . $last_row)->setConditionalStyles($conditionals);
    $sheet->getStyle($status_col . '2:' . $status_col . $last_row)->applyFromArray([
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    ]);
};

$style_sheet = static function (
    Worksheet $sheet,
    int $row_count,
    string $last_col = 'E',
    string $local_col = 'B',
    string $staging_col = 'C',
    string $status_col = 'D'
) use ($header_style, $apply_status_controls): void {
    $last_row = max(2, $row_count + 1);
    $sheet->getStyle('A1:' . $last_col . '1')->applyFromArray($header_style);
    $sheet->getRowDimension(1)->setRowHeight(22);
    $sheet->freezePane('A2');
    $sheet->setAutoFilter('A1:' . $last_col . $last_row);
    $sheet->getColumnDimension('A')->setWidth(48);
    $sheet->getColumnDimension($local_col)->setWidth(55);
    $sheet->getColumnDimension($staging_col)->setWidth(55);
    $sheet->getColumnDimension($status_col)->setWidth(18);
    $sheet->getColumnDimension($last_col)->setWidth(40);

    for ($r = 2; $r <= $row_count + 1; $r++) {
        $local = (string) $sheet->getCell($local_col . $r)->getValue();
        $staging = (string) $sheet->getCell($staging_col . $r)->getValue();
        if ($local !== '') {
            $sheet->getCell($local_col . $r)->getHyperlink()->setUrl($local);
        }
        if ($staging !== '') {
            $sheet->getCell($staging_col . $r)->getHyperlink()->setUrl($staging);
        }
        if ((string) $sheet->getCell($status_col . $r)->getValue() === '') {
            $sheet->setCellValue($status_col . $r, 'To do');
        }
    }

    $apply_status_controls($sheet, $last_row, $status_col);
};

$build_row = static function (WP_Post $post, bool $is_draft_sheet = false) use (
    $pretty_url_for,
    $local_url_for,
    $to_staging,
    $front_page_id,
    $sheet_title_for_type
): array {
    $pretty_url = $pretty_url_for($post);
    $local_url = $local_url_for($post);
    $display_title = (string) $post->post_title;
    $notes = '';

    if ($post->ID === $front_page_id) {
        $pretty_url = home_url('/');
        $local_url = $pretty_url;
        $display_title = $display_title !== '' ? $display_title . ' (homepage)' : 'Home (homepage)';
    }

    if ($is_draft_sheet) {
        $notes = 'WP status: ' . $post->post_status . '. Local URL is a preview link (log into WP admin).';

        return [
            $display_title,
            $sheet_title_for_type($post->post_type),
            $local_url,
            $to_staging($pretty_url),
            'To do',
            $notes,
        ];
    }

    return [
        $display_title,
        $local_url,
        $to_staging($pretty_url),
        'To do',
        $notes,
    ];
};

$spreadsheet = new Spreadsheet();
$spreadsheet->removeSheetByIndex(0);

$csv_rows = [];
$total = 0;
$summary_counts = [];

foreach ($grouped as $post_type => $items) {
    $title = $sheet_title_for_type($post_type);
    $sheet = new Worksheet($spreadsheet, $title);
    $spreadsheet->addSheet($sheet);
    $sheet->fromArray($headers, null, 'A1');

    $rows = [];
    foreach ($items as $post) {
        $row = $build_row($post, false);
        $rows[] = $row;
        $csv_rows[] = array_merge([$title], $row);
    }

    if ($rows !== []) {
        $sheet->fromArray($rows, null, 'A2');
    }
    $style_sheet($sheet, count($rows));
    $summary_counts[$title] = count($rows);
    $total += count($rows);
    WP_CLI::log(sprintf('%s: %d', $title, count($rows)));
}

// Drafts on a separate sheet (not mixed into type sheets).
if ($draft_items !== []) {
    $draft_headers = ['Title', 'Type', 'Local URL', 'Staging URL', 'Status', 'Notes'];
    $draft_sheet = new Worksheet($spreadsheet, 'Drafts');
    $spreadsheet->addSheet($draft_sheet);
    $draft_sheet->fromArray($draft_headers, null, 'A1');

    $draft_rows = [];
    foreach ($draft_items as $post) {
        $row = $build_row($post, true);
        $draft_rows[] = $row;
        $csv_rows[] = array_merge(['Drafts'], [
            $row[0] . ' [' . $row[1] . ']',
            $row[2],
            $row[3],
            $row[4],
            $row[5],
        ]);
    }
    $draft_sheet->fromArray($draft_rows, null, 'A2');
    $draft_sheet->getColumnDimension('B')->setWidth(24);
    $style_sheet($draft_sheet, count($draft_rows), 'F', 'C', 'D', 'E');
    $summary_counts['Drafts'] = count($draft_rows);
    $total += count($draft_rows);
    WP_CLI::log(sprintf('Drafts: %d', count($draft_rows)));
}

// --- Legend / Summary ---
$summary = new Worksheet($spreadsheet, 'Summary');
$spreadsheet->addSheet($summary, 0);
$summary->fromArray([
    ['Generated', gmdate('Y-m-d H:i:s') . ' UTC'],
    ['Local home', $local_home],
    ['Staging home', $staging_home],
    ['Total items', (string) $total],
    [],
    ['Status legend', 'Meaning'],
    ['To do', 'Still needs review / work'],
    ['Completed', 'Reviewed and done'],
    ['Edits required', 'Needs content or layout edits'],
    ['Delete', 'Mark for removal'],
    [],
    ['Sheet', 'Count'],
], null, 'A1');

$r = 13;
foreach ($summary_counts as $label => $count) {
    $summary->fromArray([[$label, (string) $count]], null, 'A' . $r);
    $r++;
}

$summary->getStyle('A1')->getFont()->setBold(true);
$summary->getStyle('A6')->getFont()->setBold(true);
$summary->getStyle('A12')->getFont()->setBold(true);
$summary->getColumnDimension('A')->setWidth(28);
$summary->getColumnDimension('B')->setWidth(40);

// Colour the legend samples.
$legend_map = [
    7 => 'To do',
    8 => 'Completed',
    9 => 'Edits required',
    10 => 'Delete',
];
foreach ($legend_map as $row_num => $label) {
    $colours = $status_colours[$label];
    $summary->getStyle('A' . $row_num)->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => $colours['fg']]],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => $colours['bg']],
        ],
    ]);
}

// --- Instructions ---
$instructions = new Worksheet($spreadsheet, 'Instructions');
$spreadsheet->addSheet($instructions, 1);
$instructions->fromArray([
    ['How to use this inventory'],
    [''],
    ['1. Upload the xlsx to Google Drive → Open with Google Sheets (or open in Excel).'],
    ['2. Each published post type has its own sheet. Pages is first; the homepage is the first row on Pages.'],
    ['3. Non-published items (draft / pending / private / future) are on the Drafts sheet only.'],
    ['4. Columns are Title, Local URL, Staging URL, Status, Notes (Drafts also has a Type column).'],
    ['5. Use the Status dropdown on each row: To do, Completed, Edits required, or Delete.'],
    ['6. Status cells are colour-coded automatically (see Summary legend).'],
    ['7. Staging URLs use ' . $staging_home . ' instead of localhost.'],
    ['8. Draft Local URLs are WP preview links (work when logged into wp-admin).'],
    ['9. Re-running the export regenerates the file and resets Status marks — work in a copy once you start marking.'],
    [''],
    ['Regenerate command:'],
    ['wp eval-file wp-content/themes/matrix-starter/scripts/export-local-content-inventory.php'],
], null, 'A1');
$instructions->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$instructions->getColumnDimension('A')->setWidth(110);

// Put Pages sheet first among type sheets (Summary + Instructions already at front).
// Active sheet: Pages if present, else Summary.
$page_index = null;
foreach ($spreadsheet->getAllSheets() as $index => $sheet) {
    if ($sheet->getTitle() === 'Pages') {
        $page_index = $index;
        break;
    }
}
$spreadsheet->setActiveSheetIndex($page_index ?? 0);

$writer = new Xlsx($spreadsheet);
$writer->save($xlsx_path);

$csv = fopen($csv_path, 'wb');
if ($csv === false) {
    WP_CLI::error('Could not write CSV: ' . $csv_path);
}
fputcsv($csv, array_merge(['Sheet'], $headers));
foreach ($csv_rows as $row) {
    fputcsv($csv, $row);
}
fclose($csv);

WP_CLI::success(sprintf('Exported %d items across %d type sheets → %s', $total, count($grouped), $xlsx_path));
WP_CLI::log('CSV also written → ' . $csv_path);
