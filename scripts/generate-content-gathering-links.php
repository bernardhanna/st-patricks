<?php
/**
 * Generate content-gathering form links for Set Pages + Client drafting rows,
 * create missing draft page shells, and write Form Link columns into the gathering xlsx.
 *
 * Usage:
 *   wp eval-file scripts/generate-content-gathering-links.php
 *   wp eval-file scripts/generate-content-gathering-links.php -- --dry-run
 *
 * Safety:
 *   - Never deletes posts
 *   - New shells are created as draft only
 *   - Exact title match to existing pages only (fuzzy matches ignored — too many false positives)
 */

if (! defined('ABSPATH')) {
    exit(1);
}

// wp eval-file script.php dry-run
$dry_run = in_array('dry-run', array_map('strval', $GLOBALS['argv'] ?? []), true)
    || (isset($_SERVER['MATRIX_GATHERING_DRY_RUN']) && $_SERVER['MATRIX_GATHERING_DRY_RUN'] === '1');

$staging_home = 'https://st-patricks.s1.matrix-test.com';
$xlsx_path    = get_template_directory() . '/old/content/St Patricks Content Migration  and gathering (2).xlsx';
$drafting_json = get_template_directory() . '/old/content/client-drafting-rows.json';
$set_json      = get_template_directory() . '/old/content/set-pages-rows.json';

if (! is_readable($xlsx_path) || ! is_readable($drafting_json) || ! is_readable($set_json)) {
    WP_CLI::error('Missing gathering xlsx or JSON inputs. Export drafting JSON first.');
}

if (! class_exists('Matrix_Export') || ! class_exists('Matrix_Flexible_Pages')) {
    WP_CLI::error('matrix-content-gathering plugin classes not loaded.');
}

$autoload = WP_PLUGIN_DIR . '/matrix-content-gathering/vendor/autoload.php';
if (! is_readable($autoload)) {
    WP_CLI::error('PhpSpreadsheet missing in content-gathering plugin (composer install).');
}
require_once $autoload;

$drafting  = json_decode((string) file_get_contents($drafting_json), true) ?: [];
$set_pages = json_decode((string) file_get_contents($set_json), true) ?: [];

$norm = static function ($s) {
    $s = strtolower(trim(html_entity_decode(wp_strip_all_tags((string) $s), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    $s = preg_replace('/[^a-z0-9]+/', ' ', $s);
    return trim(preg_replace('/\s+/', ' ', $s));
};

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

/** @return array<string, array<int, int>> */
$index_pages_by_title = static function () use ($norm) {
    $by_title = [];
    $q = new WP_Query([
        'post_type'      => 'page',
        'post_status'    => ['publish', 'draft', 'pending', 'private'],
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ]);
    foreach ($q->posts as $pid) {
        $by_title[$norm(get_the_title($pid))][] = (int) $pid;
    }
    return $by_title;
};

$find_page_by_path = static function ($path) {
    $path = trim((string) $path, '/');
    if ($path === '') {
        $front = (int) get_option('page_on_front');
        return $front > 0 ? $front : 0;
    }
    $page = get_page_by_path($path);
    return $page ? (int) $page->ID : 0;
};

$seed_draft_shell = static function ($post_id, $title) {
    $home = home_url('/');
    $rows = [
        [
            'acf_fc_layout'               => 'hero_with_breadcrumbs',
            'layout_style'                => 'image_split',
            'show_breadcrumbs'            => 1,
            'breadcrumb_source'           => 'auto',
            'manual_breadcrumbs'          => [],
            'current_crumb_label'         => $title,
            'heading_tag'                 => 'h1',
            'heading'                     => $title,
            'content'                     => '<p>Draft content for client gathering. Replace this copy via the content form.</p>',
            'primary_button'              => '',
            'hero_image'                  => '',
            'background_color'            => '#C6ECF4',
            'breadcrumb_background_color' => '#F1F8F9',
            'heading_color'               => '#08284B',
            'text_color'                  => '#08284B',
        ],
        [
            'acf_fc_layout' => 'wysiwyg',
            'heading_tag'   => 'h2',
            'heading_text'  => '',
            'content'       => '<p></p>',
            'bg_color'      => '#FFFFFF',
            'padding_settings' => [
                ['screen_size' => 'mob', 'padding_top' => '3', 'padding_bottom' => '3'],
                ['screen_size' => 'lg', 'padding_top' => '4', 'padding_bottom' => '4'],
            ],
        ],
    ];

    if (function_exists('update_field')) {
        update_field('hero_content_blocks', [], $post_id);
        update_field('flexible_content_blocks', $rows, $post_id);
    }

    update_post_meta($post_id, '_matrix_content_gathering_shell', '1');
    update_post_meta($post_id, '_matrix_content_gathering_shell_title', $title);
};

$section_parent_slugs = [
    'About Us'                 => 'about-us',
    'What We Offer'            => 'what-we-offer',
    'Healthcare Professionals' => 'healthcare-professionals',
    'Service Users'            => 'service-users-and-visitors',
    'Service Users and Visitors' => 'service-users-and-visitors',
    'Blogs'                    => 'news-and-events',
    'Footer'                   => '',
];

$by_title = $index_pages_by_title();

// --- Resolve Set Pages ---
$set_resolved = [];
$set_skip_names = [
    'Search Page results',
    'Search results page - no search found',
    '404.0',
    'FLEXI BLOCKS / ALL BLOCKS',
];

foreach ($set_pages as $sp) {
    $name = trim((string) ($sp['name'] ?? ''));
    if ($name === '' || in_array($name, $set_skip_names, true)) {
        continue;
    }
    $url  = (string) ($sp['page_link'] ?? '');
    $path = (string) (wp_parse_url($url, PHP_URL_PATH) ?: '');
    $pid  = $find_page_by_path($path);

    // Fallback: exact title
    if (! $pid && isset($by_title[$norm($name)]) && count($by_title[$norm($name)]) === 1) {
        $pid = $by_title[$norm($name)][0];
    }

    $set_resolved[] = [
        'name'   => $name,
        'wp_id'  => $pid,
        'mode'   => 'fixed',
        'source' => $pid ? 'existing' : 'missing',
        'page_link' => $url,
    ];
}

// --- Resolve Client drafting ---
$draft_resolved = [];
$created = 0;
$reused  = 0;

foreach ($drafting as $row) {
    $sheet_id = (string) ($row['id'] ?? '');
    $title    = trim((string) ($row['title'] ?? ''));
    $section  = (string) ($row['section'] ?? '');
    $nt       = $norm($title);
    $pid      = 0;
    $source   = 'create';

    if ($nt !== '' && ! empty($by_title[$nt])) {
        // Prefer a single exact page match
        $pid = (int) $by_title[$nt][0];
        $source = 'existing-title';
        $reused++;
    }

    if (! $pid && ! $dry_run) {
        $parent_id = 0;
        $parent_slug = $section_parent_slugs[$section] ?? '';
        if ($parent_slug !== '') {
            $parent = get_page_by_path($parent_slug);
            if ($parent) {
                $parent_id = (int) $parent->ID;
            }
        }

        $pid = (int) wp_insert_post([
            'post_title'  => $title,
            'post_name'   => sanitize_title($title),
            'post_status' => 'draft',
            'post_type'   => 'page',
            'post_parent' => $parent_id,
            'post_content'=> '',
        ], true);

        if (is_wp_error($pid) || $pid <= 0) {
            WP_CLI::warning('Failed creating shell for ' . $sheet_id . ' ' . $title);
            $pid = 0;
        } else {
            $seed_draft_shell($pid, $title);
            update_post_meta($pid, '_matrix_gathering_sheet_id', $sheet_id);
            $created++;
            $source = 'created-shell';
            // refresh title index
            $by_title[$nt][] = $pid;
        }
    } elseif (! $pid && $dry_run) {
        $source = 'would-create';
        $created++;
    }

    $draft_resolved[] = [
        'sheet_id' => $sheet_id,
        'title'    => $title,
        'section'  => $section,
        'wp_id'    => $pid,
        'mode'     => 'builder',
        'source'   => $source,
    ];
}

WP_CLI::log(sprintf(
    'Set Pages resolvable: %d / %d | Drafting reused: %d | shells %s: %d',
    count(array_filter($set_resolved, static fn($r) => $r['wp_id'] > 0)),
    count($set_resolved),
    $reused,
    $dry_run ? 'would create' : 'created',
    $created
));

if ($dry_run) {
    WP_CLI::success('Dry run only — no links generated, sheet not modified.');
    return;
}

// Refresh title index after creates
$by_title = $index_pages_by_title();

$set_ids = array_values(array_unique(array_filter(array_map(static fn($r) => (int) $r['wp_id'], $set_resolved))));
$draft_ids = array_values(array_unique(array_filter(array_map(static fn($r) => (int) $r['wp_id'], $draft_resolved))));

foreach ($set_ids as $pid) {
    Matrix_Flexible_Pages::set_flexible_page($pid, false); // Fixed
}
foreach ($draft_ids as $pid) {
    // Builder for client drafting (even if page already had a layout)
    Matrix_Flexible_Pages::set_flexible_page($pid, true);
}

$all_ids = array_values(array_unique(array_merge($set_ids, $draft_ids)));
if (empty($all_ids)) {
    WP_CLI::error('No post IDs to generate links for.');
}

$token = Matrix_Export::create_client_link($all_ids, [
    'expires_days' => 0,
    'custom_instructions' => 'St Patrick\'s content gathering — use the page dropdown to open each page. Set Pages are fixed layout; other drafting pages allow Flexiblocks (Builder).',
    'requires_approval' => false,
    'strict_mode' => false,
    'ai_mode' => false,
]);

if ($token === '') {
    WP_CLI::error('Failed to create client link token.');
}

$base_form = $to_staging(Matrix_Export::get_client_link_url($token));
WP_CLI::log('Token: ' . $token);
WP_CLI::log('Base form: ' . $base_form);

$form_link_for = static function ($post_id) use ($base_form, $to_staging) {
    $post_id = (int) $post_id;
    if ($post_id <= 0) {
        return '';
    }
    return $to_staging(add_query_arg('matrix_page', $post_id, $base_form));
};

// Map for sheet writes
$set_by_name = [];
foreach ($set_resolved as $r) {
    $set_by_name[$r['name']] = $r + ['form_link' => $form_link_for($r['wp_id'])];
}

$draft_by_id = [];
foreach ($draft_resolved as $r) {
    $draft_by_id[$r['sheet_id']] = $r + [
        'form_link' => $form_link_for($r['wp_id']),
        'staging_url' => $r['wp_id'] ? $to_staging(get_permalink($r['wp_id'])) : '',
    ];
}

// --- Write xlsx ---
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($xlsx_path);

// Set Pages sheet
$sp_sheet = $spreadsheet->getSheetByName('Set Pages');
if ($sp_sheet) {
    $highest = $sp_sheet->getHighestRow();
    // Ensure headers
    $sp_sheet->setCellValue('A1', 'Page Name');
    $sp_sheet->setCellValue('B1', 'Page Link');
    $sp_sheet->setCellValue('C1', 'Form Link used to edit content');
    $sp_sheet->setCellValue('D1', 'Form mode');
    $sp_sheet->setCellValue('E1', 'WP post ID');
    $sp_sheet->setCellValue('F1', 'Match source');

    for ($row = 2; $row <= $highest; $row++) {
        $name = trim((string) $sp_sheet->getCell('A' . $row)->getValue());
        if ($name === '' || ! isset($set_by_name[$name])) {
            continue;
        }
        $info = $set_by_name[$name];
        if (! empty($info['page_link'])) {
            $sp_sheet->setCellValue('B' . $row, $to_staging($info['page_link']));
        } elseif ($info['wp_id']) {
            $sp_sheet->setCellValue('B' . $row, $to_staging(get_permalink($info['wp_id'])));
        }
        $sp_sheet->setCellValue('C' . $row, $info['form_link']);
        $sp_sheet->setCellValue('D' . $row, 'Fixed');
        $sp_sheet->setCellValue('E' . $row, $info['wp_id'] ?: '');
        $sp_sheet->setCellValue('F' . $row, $info['source']);
    }
}

// Items sheet — add Form Link / Form mode columns after Done?
$items = $spreadsheet->getSheetByName('Items');
if ($items) {
    $headers = [];
    $col = 1;
    while (true) {
        $val = $items->getCellByColumnAndRow($col, 1)->getValue();
        if ($val === null || $val === '') {
            break;
        }
        $headers[(string) $val] = $col;
        $col++;
    }

    $ensure_col = static function ($name) use (&$headers, $items, &$col) {
        if (isset($headers[$name])) {
            return $headers[$name];
        }
        $headers[$name] = $col;
        $items->setCellValueByColumnAndRow($col, 1, $name);
        $col++;
        return $headers[$name];
    };

    $col_form = $ensure_col('Form Link');
    $col_mode = $ensure_col('Form mode');
    $col_wp   = $headers['WP post ID'] ?? $ensure_col('WP post ID');
    $col_stg  = $headers['Staging URL'] ?? $ensure_col('Staging URL');
    $col_rec  = $headers['Reconciliation'] ?? null;
    $col_id   = $headers['ID'] ?? 1;
    $col_mig  = $headers['Migration action'] ?? null;

    $highest = $items->getHighestRow();
    $updated = 0;
    for ($row = 2; $row <= $highest; $row++) {
        $sheet_id = (string) $items->getCellByColumnAndRow($col_id, $row)->getValue();
        $mig = $col_mig ? (string) $items->getCellByColumnAndRow($col_mig, $row)->getValue() : '';

        // Fill drafting rows
        if (isset($draft_by_id[$sheet_id])) {
            $info = $draft_by_id[$sheet_id];
            $items->setCellValueByColumnAndRow($col_form, $row, $info['form_link']);
            $items->setCellValueByColumnAndRow($col_mode, $row, 'Builder');
            if ($info['wp_id']) {
                $items->setCellValueByColumnAndRow($col_wp, $row, $info['wp_id']);
                $items->setCellValueByColumnAndRow($col_stg, $row, $info['staging_url']);
            }
            $updated++;
            continue;
        }

        // Also fill form links for Items that match Set Pages by title (optional nicety)
        if ($mig === 'Client drafting') {
            continue;
        }
    }

    WP_CLI::log("Items drafting rows updated: {$updated}");
}

// Write summary sheet note
$summary = $spreadsheet->getSheetByName('Summary');
if ($summary) {
    $summary->setCellValue('A24', 'Content gathering forms');
    $summary->setCellValue('B24', 'Generated ' . gmdate('Y-m-d H:i') . ' UTC');
    $summary->setCellValue('A25', 'Form token');
    $summary->setCellValue('B25', $token);
    $summary->setCellValue('A26', 'Base form URL');
    $summary->setCellValue('B26', $base_form);
    $summary->setCellValue('A27', 'Draft shells created');
    $summary->setCellValue('B27', $created);
    $summary->setCellValue('A28', 'Set pages in form (Fixed)');
    $summary->setCellValue('B28', count($set_ids));
    $summary->setCellValue('A29', 'Drafting pages in form (Builder)');
    $summary->setCellValue('B29', count($draft_ids));
}

$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
$writer->save($xlsx_path);

// Sidecar CSV for Google Sheet paste
$csv_path = get_template_directory() . '/old/content/CONTENT-GATHERING-FORM-LINKS.csv';
$fh = fopen($csv_path, 'w');
fputcsv($fh, ['Sheet ID / Name', 'Group', 'Title', 'WP post ID', 'Form mode', 'Staging URL', 'Form Link', 'Source']);
foreach ($set_by_name as $name => $info) {
    fputcsv($fh, [
        $name,
        'Set Pages',
        $name,
        $info['wp_id'],
        'Fixed',
        $info['wp_id'] ? $to_staging(get_permalink($info['wp_id'])) : '',
        $info['form_link'],
        $info['source'],
    ]);
}
foreach ($draft_by_id as $sid => $info) {
    fputcsv($fh, [
        $sid,
        'Client drafting',
        $info['title'],
        $info['wp_id'],
        'Builder',
        $info['staging_url'],
        $info['form_link'],
        $info['source'],
    ]);
}
fclose($fh);

WP_CLI::success('Updated ' . $xlsx_path);
WP_CLI::success('Wrote ' . $csv_path);
WP_CLI::log('Open a Form Link while logged in as an allowed editor role on staging.');
