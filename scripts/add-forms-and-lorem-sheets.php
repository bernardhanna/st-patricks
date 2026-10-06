<?php

/**
 * Add two workbook tabs:
 * - Pages with forms (excludes the sitewide footer newsletter; lists it once on Home)
 * - Lorem ipsum (pages still containing placeholder copy)
 *
 * Updates: old/content/St Patricks Content - List (1).xlsx
 * Also copies to Desktop.
 *
 * Usage:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/add-forms-and-lorem-sheets.php dry-run
 *   wp eval-file wp-content/themes/matrix-starter/scripts/add-forms-and-lorem-sheets.php
 */

if (! defined('ABSPATH') || ! class_exists('WP_CLI')) {
    exit(1);
}

require_once WP_PLUGIN_DIR . '/matrix-content-gathering/vendor/autoload.php';
require_once get_template_directory() . '/scripts/lib/content-workbook-helpers.php';

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$dry_run = in_array('dry-run', $GLOBALS['argv'] ?? [], true);
$xlsx = get_template_directory() . '/old/content/St Patricks Content - List (1).xlsx';
$desktop = getenv('HOME') . '/Desktop/St Patricks Content - List (1).xlsx';
$newsletter_heading = 'Latest News, Events, and Expert advice from SPMHS';
$staging_home = defined('MATRIX_WORKBOOK_STAGING_HOME')
    ? untrailingslashit(MATRIX_WORKBOOK_STAGING_HOME)
    : 'http://st-patricks.s1.matrix-test.com';

if (! is_readable($xlsx)) {
    WP_CLI::error('Missing workbook: ' . $xlsx);
}

if (! function_exists('matrix_workbook_flatten_strings')) {
    function matrix_workbook_flatten_strings($value, array &$out): void
    {
        if (is_string($value) || is_numeric($value)) {
            $out[] = (string) $value;

            return;
        }
        if (! is_array($value)) {
            return;
        }
        foreach ($value as $item) {
            matrix_workbook_flatten_strings($item, $out);
        }
    }
}

$to_staging = static function (string $url) use ($staging_home): string {
    $local = untrailingslashit(home_url('/'));
    if ($url === '') {
        return '';
    }

    return str_replace($local, $staging_home, $url);
};

$collect_strings = static function ($value): array {
    $out = [];
    matrix_workbook_flatten_strings($value, $out);

    return $out;
};

$normalise_heading = static function (string $text): string {
    $text = html_entity_decode(wp_strip_all_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

    return trim($text);
};

$is_sitewide_newsletter_heading = static function (string $heading) use ($normalise_heading, $newsletter_heading): bool {
    return strcasecmp($normalise_heading($heading), $newsletter_heading) === 0;
};

$walk_flexi_forms = static function (array $rows) use ($is_sitewide_newsletter_heading): array {
    $forms = [];
    foreach ($rows as $row) {
        if (! is_array($row)) {
            continue;
        }
        $layout = (string) ($row['acf_fc_layout'] ?? '');
        if ($layout === 'contact_form') {
            $heading = trim((string) ($row['heading'] ?? ''));
            $name = trim((string) ($row['form_name'] ?? ''));
            $style = trim((string) ($row['form_style'] ?? ''));
            $forms[] = [
                'type' => $style !== '' ? 'contact_form (' . $style . ')' : 'contact_form',
                'heading' => $heading !== '' ? $heading : ($name !== '' ? $name : 'Contact form'),
            ];
            continue;
        }
        if ($layout === 'newsletter') {
            $heading = trim((string) ($row['heading'] ?? ''));
            if ($is_sitewide_newsletter_heading($heading) || $heading === '') {
                continue;
            }
            $forms[] = [
                'type' => 'newsletter (page)',
                'heading' => $heading,
            ];
        }
    }

    return $forms;
};

$content_has_form = static function (string $content): array {
    $forms = [];
    if (preg_match('/\[gravityform[^\]]*\]/i', $content, $m)) {
        $forms[] = ['type' => 'gravity_forms', 'heading' => $m[0]];
    }
    if (preg_match('/\[contact-form-7[^\]]*\]/i', $content, $m)) {
        $forms[] = ['type' => 'contact_form_7', 'heading' => $m[0]];
    }
    if (preg_match('/\[wpforms[^\]]*\]/i', $content, $m)) {
        $forms[] = ['type' => 'wpforms', 'heading' => $m[0]];
    }

    return $forms;
};

$lorem_pattern = '/lorem ipsum|consetetur sadipscing|placeholder copy|replace this copy|draft content for client gathering/i';

$lorem_snippet = static function (string $blob) use ($lorem_pattern): string {
    if (! preg_match($lorem_pattern, $blob, $m, PREG_OFFSET_CAPTURE)) {
        return '';
    }
    $pos = max(0, (int) $m[0][1] - 40);
    $snip = substr($blob, $pos, 160);
    $snip = preg_replace('/\s+/u', ' ', $snip) ?? $snip;

    return trim($snip);
};

$post_types = get_post_types(['public' => true], 'names');
$post_types = array_values(array_diff($post_types, ['attachment']));

$posts = get_posts([
    'post_type' => $post_types,
    'post_status' => ['publish', 'draft', 'private', 'pending'],
    'numberposts' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
]);

$front_id = (int) get_option('page_on_front');
$form_rows = [];
$lorem_rows = [];

foreach ($posts as $post) {
    $pid = (int) $post->ID;
    $permalink = (string) get_permalink($pid);
    $flexi = get_field('flexible_content_blocks', $pid);
    $hero = get_field('hero_content_blocks', $pid);
    $forms = [];
    if (is_array($flexi)) {
        $forms = array_merge($forms, $walk_flexi_forms($flexi));
    }
    if (is_array($hero)) {
        $forms = array_merge($forms, $walk_flexi_forms($hero));
    }
    $forms = array_merge($forms, $content_has_form((string) $post->post_content));

    foreach ($forms as $form) {
        $form_rows[] = [
            'title' => $post->post_title,
            'post_type' => $post->post_type,
            'status' => $post->post_status,
            'local' => $permalink,
            'staging' => $to_staging($permalink),
            'type' => $form['type'],
            'heading' => $form['heading'],
            'id' => $pid,
        ];
    }

    $parts = [(string) $post->post_title, (string) $post->post_content, (string) $post->post_excerpt];
    $fields = function_exists('get_fields') ? get_fields($pid) : [];
    if (is_array($fields)) {
        $parts = array_merge($parts, $collect_strings($fields));
    }
    $blob = implode(' ', $parts);
    $snip = $lorem_snippet($blob);
    if ($snip !== '') {
        $lorem_rows[] = [
            'title' => $post->post_title,
            'post_type' => $post->post_type,
            'status' => $post->post_status,
            'local' => $permalink,
            'staging' => $to_staging($permalink),
            'snippet' => $snip,
            'id' => $pid,
        ];
    }
}

$home = $front_id > 0 ? get_post($front_id) : null;
if ($home instanceof WP_Post) {
    $permalink = (string) get_permalink($front_id);
    array_unshift($form_rows, [
        'title' => $home->post_title,
        'post_type' => $home->post_type,
        'status' => $home->post_status,
        'local' => $permalink,
        'staging' => $to_staging($permalink),
        'type' => 'sitewide newsletter (footer)',
        'heading' => $newsletter_heading,
        'id' => $front_id,
    ]);
}

WP_CLI::log(sprintf('Pages with forms: %d rows (incl. homepage newsletter once)', count($form_rows)));
WP_CLI::log(sprintf('Lorem ipsum: %d pages', count($lorem_rows)));
foreach (array_slice($form_rows, 0, 12) as $row) {
    WP_CLI::log('  form: ' . $row['title'] . ' — ' . $row['type'] . ' — ' . $row['heading']);
}
foreach (array_slice($lorem_rows, 0, 12) as $row) {
    WP_CLI::log('  lorem: ' . $row['title'] . ' — ' . $row['snippet']);
}

if ($dry_run) {
    WP_CLI::success('Dry run — workbook not written.');
    return;
}

$wb = IOFactory::load($xlsx);

$write_sheet = static function (
    $wb,
    string $title,
    array $headers,
    array $rows,
    callable $map_row,
    array $link_cols,
    array $widths
): void {
    $existing = $wb->getSheetByName($title);
    if ($existing) {
        $index = $wb->getIndex($existing);
        $wb->removeSheetByIndex($index);
    }
    $sheet = new Worksheet($wb, $title);
    $wb->addSheet($sheet);

    foreach ($headers as $i => $header) {
        $sheet->setCellValueByColumnAndRow($i + 1, 1, $header);
    }
    $last_col = Coordinate::stringFromColumnIndex(count($headers));
    $sheet->getStyle('A1:' . $last_col . '1')->applyFromArray(matrix_workbook_header_style());
    $sheet->getRowDimension(1)->setRowHeight(22);
    $sheet->freezePane('A2');
    $sheet->setAutoFilter('A1:' . $last_col . '1');

    $r = 2;
    foreach ($rows as $row) {
        $values = $map_row($row);
        foreach ($values as $i => $value) {
            $sheet->setCellValueByColumnAndRow($i + 1, $r, $value);
        }
        $r++;
    }
    $last_row = max(2, $r - 1);
    foreach ($link_cols as $col) {
        matrix_workbook_hyperlink_column($sheet, $col, $last_row);
    }
    foreach ($widths as $col => $width) {
        $sheet->getColumnDimension($col)->setWidth($width);
    }
    $sheet->getStyle('A2:' . $last_col . $last_row)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    $sheet->getStyle('A2:' . $last_col . $last_row)->getAlignment()->setWrapText(true);
};

$write_sheet(
    $wb,
    'Pages with forms',
    ['Title', 'Post type', 'WP status', 'Local URL', 'Staging URL', 'Form type', 'Form heading', 'WP post ID'],
    $form_rows,
    static fn (array $row): array => [
        $row['title'],
        $row['post_type'],
        $row['status'],
        $row['local'],
        $row['staging'],
        $row['type'],
        $row['heading'],
        $row['id'],
    ],
    ['D', 'E'],
    ['A' => 42, 'B' => 18, 'C' => 14, 'D' => 55, 'E' => 55, 'F' => 28, 'G' => 48, 'H' => 14]
);

$write_sheet(
    $wb,
    'Lorem ipsum',
    ['Title', 'Post type', 'WP status', 'Local URL', 'Staging URL', 'Match snippet', 'WP post ID'],
    $lorem_rows,
    static fn (array $row): array => [
        $row['title'],
        $row['post_type'],
        $row['status'],
        $row['local'],
        $row['staging'],
        $row['snippet'],
        $row['id'],
    ],
    ['D', 'E'],
    ['A' => 42, 'B' => 18, 'C' => 14, 'D' => 55, 'E' => 55, 'F' => 70, 'G' => 14]
);

$summary = $wb->getSheetByName('Summary');
if ($summary) {
    $highest = (int) $summary->getHighestDataRow();
    $insert = $highest + 2;
    $summary->setCellValue('A' . $insert, 'Pages with forms');
    $summary->setCellValue('B' . $insert, count($form_rows) . ' rows. Sitewide footer newsletter listed once on Home. Page contact/newsletter forms only.');
    $summary->setCellValue('A' . ($insert + 1), 'Lorem ipsum');
    $summary->setCellValue('B' . ($insert + 1), count($lorem_rows) . ' pages still contain placeholder copy.');
    $summary->getStyle('A' . $insert . ':A' . ($insert + 1))->getFont()->setBold(true);
}

$writer = new Xlsx($wb);
$writer->save($xlsx);
if (is_dir(dirname($desktop))) {
    copy($xlsx, $desktop);
}

WP_CLI::success('Saved workbook + Desktop copy with Pages with forms (' . count($form_rows) . ') and Lorem ipsum (' . count($lorem_rows) . ').');
