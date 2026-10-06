<?php

/**
 * Export the client content workbook, ordered by the Slickplan sitemap.
 *
 * - Published items only (anything drafted for deletion drops out automatically)
 * - One sheet per top-level section, keyed off the page_section taxonomy
 * - A Pages sheet for published pages that sit outside a section (homepage first)
 * - One sheet per remaining published post type
 * - Columns: Title, Local URL, Staging URL, Local Form Link, Staging Form Link,
 *   Content on Drive, Added from drive, Added from form, Status, Status set by, Notes
 *
 * Client editing forms render every page in a token into one HTML document, so each
 * sheet is split into chunks of MATRIX_WORKBOOK_FORM_CHUNK_SIZE posts with one token each.
 *
 * Usage:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/export-sitemap-content-workbook.php
 */

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (! defined('ABSPATH')) {
    exit(1);
}

if (! defined('MATRIX_WORKBOOK_FORM_CHUNK_SIZE')) {
    define('MATRIX_WORKBOOK_FORM_CHUNK_SIZE', 25);
}

/**
 * Marker stored in each token's custom instructions, so a re-run can retire its own
 * previous tokens without touching links created by hand in Tools > Content Gathering.
 */
if (! defined('MATRIX_WORKBOOK_TOKEN_MARKER')) {
    define('MATRIX_WORKBOOK_TOKEN_MARKER', 'Content review: ');
}

$autoload = WP_PLUGIN_DIR . '/matrix-content-gathering/vendor/autoload.php';

if (! is_readable($autoload)) {
    WP_CLI::error('PhpSpreadsheet missing (matrix-content-gathering plugin vendor).');
}

require_once $autoload;
require_once get_template_directory() . '/scripts/lib/content-workbook-helpers.php';
require_once get_template_directory() . '/scripts/lib/drive-library-workbook.php';
require_once ABSPATH . 'wp-admin/includes/post.php';

if (! class_exists('Matrix_Export')) {
    WP_CLI::error('Matrix_Export not available — activate the matrix-content-gathering plugin.');
}

$local_home = untrailingslashit(home_url('/'));
$staging_home = MATRIX_WORKBOOK_STAGING_HOME;
$out_dir = get_template_directory() . '/old/content';
$xlsx_path = $out_dir . '/ST-PATRICKS-CONTENT-WORKBOOK.xlsx';
$csv_path = $out_dir . '/ST-PATRICKS-CONTENT-WORKBOOK.csv';
$sitemap_path = get_template_directory() . '/old/st patricks hospital 10.0.csv';
$front_page_id = (int) get_option('page_on_front');

if (! is_dir($out_dir) && ! wp_mkdir_p($out_dir)) {
    WP_CLI::error('Cannot create output directory: ' . $out_dir);
}

if (! is_readable($sitemap_path)) {
    WP_CLI::error('Slickplan sitemap not readable: ' . $sitemap_path);
}

/**
 * Section sheets, in Slickplan top-level order.
 * Keys are page_section term slugs.
 */
$section_sheets = [
    'about-us' => 'About Us',
    'what-we-offer' => 'What We Offer',
    'healthcare-professionals' => 'Healthcare Professionals',
    'service-users-and-visitors' => 'Service Users and Visitors',
    'your-portal' => 'Your Portal',
    'news-and-events' => 'News & Events',
    'contact-us' => 'Contact Us',
];

/**
 * WP slug => Slickplan slug, for pages whose slug drifted from the sitemap.
 * Every target here was confirmed to exist in the sitemap CSV.
 */
$sitemap_slug_aliases = [
    'home' => 'home-page',
    'news-and-events' => 'blog-name-as-news-and-events-on-wireframes',
    'psychiatrists' => 'psychiatrists-consultants-and-registrars',
    'psychologists' => 'clinical-psychologists',
    'attending-day-programmes' => 'attending-our-day-programmes',
    'training-centre-2' => 'training-centre',
    'extending-our-services' => 'extending-and-enhancing-our-services',
    'schizophrenia' => 'schizophrenia-psychosis',
    'research-ethics-committee-2' => 'research-ethics-committee',
    'faqs' => 'frequently-asked-questions-faqs',
    'payment' => 'make-a-payment-external-link-to-stripe',
    'spire' => 'research-library-spire',
];

// ---------------------------------------------------------------------------
// 1. Parse the Slickplan sitemap into a depth-first ordering.
// ---------------------------------------------------------------------------

/**
 * Read the sitemap CSV into nodes keyed by Slickplan id.
 *
 * @return array<string, array{id: string, parent_id: string, level: string, order: int, name: string, slug: string}>
 */
$read_sitemap_nodes = static function (string $path): array {
    $handle = fopen($path, 'rb');

    if ($handle === false) {
        WP_CLI::error('Could not open sitemap: ' . $path);
    }

    $header = fgetcsv($handle, 0, ';', '"', '');

    if (! is_array($header)) {
        fclose($handle);
        WP_CLI::error('Sitemap has no header row.');
    }

    $index = [];

    foreach ($header as $position => $name) {
        $index[trim((string) $name)] = $position;
    }

    foreach (['id', 'parent_id', 'level', 'order', 'name', 'url_slug'] as $required) {
        if (! isset($index[$required])) {
            fclose($handle);
            WP_CLI::error('Sitemap missing expected column: ' . $required);
        }
    }

    $nodes = [];

    while (($row = fgetcsv($handle, 0, ';', '"', '')) !== false) {
        $id = isset($row[$index['id']]) ? trim((string) $row[$index['id']]) : '';

        if ($id === '') {
            continue;
        }

        $nodes[$id] = [
            'id' => $id,
            'parent_id' => isset($row[$index['parent_id']]) ? trim((string) $row[$index['parent_id']]) : '',
            'level' => isset($row[$index['level']]) ? trim((string) $row[$index['level']]) : '',
            'order' => isset($row[$index['order']]) ? (int) $row[$index['order']] : 0,
            'name' => isset($row[$index['name']]) ? trim((string) $row[$index['name']]) : '',
            'slug' => isset($row[$index['url_slug']]) ? trim((string) $row[$index['url_slug']]) : '',
        ];
    }

    fclose($handle);

    return $nodes;
};

$sitemap_nodes = $read_sitemap_nodes($sitemap_path);

if ($sitemap_nodes === []) {
    WP_CLI::error('Sitemap parsed to zero nodes.');
}

$sort_siblings = static function (array $nodes): array {
    usort($nodes, static function (array $a, array $b): int {
        if ($a['order'] !== $b['order']) {
            return $a['order'] <=> $b['order'];
        }

        return strcasecmp($a['name'], $b['name']);
    });

    return $nodes;
};

$children_by_parent = [];
$roots_home = [];
$roots_top = [];
$roots_other = [];

foreach ($sitemap_nodes as $node) {
    if ($node['parent_id'] !== '' && isset($sitemap_nodes[$node['parent_id']])) {
        $children_by_parent[$node['parent_id']][] = $node;
        continue;
    }

    if ($node['level'] === 'home') {
        $roots_home[] = $node;
    } elseif ($node['level'] === '1') {
        $roots_top[] = $node;
    } else {
        $roots_other[] = $node;
    }
}

foreach ($children_by_parent as $parent_id => $children) {
    $children_by_parent[$parent_id] = $sort_siblings($children);
}

// Depth-first walk: homepage, then top-level sections by order, then loose utility pages.
$sitemap_rank_by_slug = [];
$rank = 0;

$walk = static function (array $node) use (&$walk, &$children_by_parent, &$sitemap_rank_by_slug, &$rank): void {
    $rank++;

    // First occurrence wins: the sitemap reuses placeholder slugs such as "day-programme-1".
    if ($node['slug'] !== '' && ! isset($sitemap_rank_by_slug[$node['slug']])) {
        $sitemap_rank_by_slug[$node['slug']] = $rank;
    }

    foreach ($children_by_parent[$node['id']] ?? [] as $child) {
        $walk($child);
    }
};

foreach ([$sort_siblings($roots_home), $sort_siblings($roots_top), $sort_siblings($roots_other)] as $root_group) {
    foreach ($root_group as $root) {
        $walk($root);
    }
}

WP_CLI::log(sprintf('Sitemap: %d nodes, %d unique slugs ranked.', count($sitemap_nodes), count($sitemap_rank_by_slug)));

/**
 * Sitemap rank for a post, or null when it is absent from the sitemap.
 */
$sitemap_rank_for = static function (WP_Post $post) use ($sitemap_rank_by_slug, $sitemap_slug_aliases): ?int {
    $slug = (string) $post->post_name;

    if ($slug === '') {
        return null;
    }

    $candidates = [$slug];

    if (isset($sitemap_slug_aliases[$slug])) {
        array_unshift($candidates, $sitemap_slug_aliases[$slug]);
    }

    foreach ($candidates as $candidate) {
        if (isset($sitemap_rank_by_slug[$candidate])) {
            return $sitemap_rank_by_slug[$candidate];
        }
    }

    return null;
};

// ---------------------------------------------------------------------------
// 2. Collect published items and assign them to sheets.
// ---------------------------------------------------------------------------

$post_types = get_post_types(['public' => true], 'names');
unset($post_types['attachment'], $post_types['page']);

$cpt_counts = [];

foreach ($post_types as $post_type) {
    $cpt_counts[$post_type] = (int) count(get_posts([
        'post_type' => $post_type,
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
    ]));
}

$cpt_counts = array_filter($cpt_counts);

// Posts first, then remaining types by published volume.
uksort($cpt_counts, static function (string $a, string $b) use ($cpt_counts): int {
    if ($a === 'post') {
        return -1;
    }

    if ($b === 'post') {
        return 1;
    }

    if ($cpt_counts[$a] !== $cpt_counts[$b]) {
        return $cpt_counts[$b] <=> $cpt_counts[$a];
    }

    return strcasecmp($a, $b);
});

/**
 * Sheets in output order: key => ['title' => string, 'posts' => WP_Post[]].
 *
 * @var array<string, array{title: string, posts: array<int, WP_Post>}> $sheets
 */
$sheets = ['__pages__' => ['title' => 'Pages', 'posts' => []]];

foreach ($section_sheets as $section_slug => $section_title) {
    $sheets['section:' . $section_slug] = ['title' => $section_title, 'posts' => []];
}

$published_pages = get_posts([
    'post_type' => 'page',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
]);

foreach ($published_pages as $page) {
    $terms = get_the_terms($page->ID, 'page_section');
    $sheet_key = '__pages__';

    if (! is_wp_error($terms) && is_array($terms)) {
        foreach ($terms as $term) {
            if ($term instanceof WP_Term && isset($sheets['section:' . $term->slug])) {
                $sheet_key = 'section:' . $term->slug;
                break;
            }
        }
    }

    $sheets[$sheet_key]['posts'][] = $page;
}

foreach (array_keys($cpt_counts) as $post_type) {
    $type_object = get_post_type_object($post_type);
    $label = $type_object instanceof WP_Post_Type ? (string) $type_object->labels->name : $post_type;

    $sheets['type:' . $post_type] = [
        'title' => matrix_workbook_sanitize_sheet_title($label, $post_type),
        'posts' => get_posts([
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ]),
    ];
}

// Sort each sheet: sitemap order first, then everything the sitemap does not know about.
foreach ($sheets as $sheet_key => $sheet) {
    $posts = $sheet['posts'];

    usort($posts, static function (WP_Post $a, WP_Post $b) use ($sitemap_rank_for, $front_page_id): int {
        if ($a->ID === $front_page_id) {
            return -1;
        }

        if ($b->ID === $front_page_id) {
            return 1;
        }

        $rank_a = $sitemap_rank_for($a);
        $rank_b = $sitemap_rank_for($b);

        if ($rank_a !== null && $rank_b !== null) {
            return $rank_a <=> $rank_b;
        }

        // Unmatched items sink below anything the sitemap places.
        if ($rank_a !== null) {
            return -1;
        }

        if ($rank_b !== null) {
            return 1;
        }

        $path = strcasecmp((string) get_page_uri($a), (string) get_page_uri($b));

        if ($path !== 0) {
            return $path;
        }

        return $a->ID <=> $b->ID;
    });

    $sheets[$sheet_key]['posts'] = $posts;
}

// Drop empty sheets so the workbook has no dead tabs.
$sheets = array_filter($sheets, static function (array $sheet): bool {
    return $sheet['posts'] !== [];
});

// ---------------------------------------------------------------------------
// 3. Mint client form tokens, chunked per sheet.
// ---------------------------------------------------------------------------

$form_url_by_post = [];
$token_log = [];
$retired_tokens = 0;

// Retire tokens from any previous run of this script so re-exporting stays idempotent.
foreach (Matrix_Export::get_client_links() as $existing_token => $entry) {
    $instructions = isset($entry['custom_instructions']) ? (string) $entry['custom_instructions'] : '';

    if (strpos($instructions, MATRIX_WORKBOOK_TOKEN_MARKER) !== 0) {
        continue;
    }

    Matrix_Export::delete_client_link($existing_token);
    $retired_tokens++;
}

if ($retired_tokens > 0) {
    WP_CLI::log(sprintf('Retired %d form tokens from a previous export.', $retired_tokens));
}

foreach ($sheets as $sheet) {
    $post_ids = array_map(static function (WP_Post $post): int {
        return (int) $post->ID;
    }, $sheet['posts']);

    $chunks = array_chunk($post_ids, MATRIX_WORKBOOK_FORM_CHUNK_SIZE);
    $chunk_total = count($chunks);

    foreach ($chunks as $chunk_index => $chunk_ids) {
        $chunk_label = $chunk_total > 1
            ? sprintf('%s (part %d of %d)', $sheet['title'], $chunk_index + 1, $chunk_total)
            : $sheet['title'];

        $token = Matrix_Export::create_client_link($chunk_ids, [
            'expires_days' => 0,
            'custom_instructions' => MATRIX_WORKBOOK_TOKEN_MARKER . $chunk_label,
        ]);

        if ($token === '') {
            WP_CLI::warning('Could not create a client link for ' . $chunk_label);
            continue;
        }

        $base_form_url = Matrix_Export::get_client_link_url($token);

        foreach ($chunk_ids as $post_id) {
            $form_url_by_post[$post_id] = add_query_arg('matrix_page', $post_id, $base_form_url);
        }

        $token_log[] = [
            'label' => $chunk_label,
            'token' => $token,
            'pages' => count($chunk_ids),
            'url' => $base_form_url,
        ];
    }
}

WP_CLI::log(sprintf('Created %d client form tokens (chunk size %d).', count($token_log), MATRIX_WORKBOOK_FORM_CHUNK_SIZE));

// ---------------------------------------------------------------------------
// 4. Build the workbook.
// ---------------------------------------------------------------------------

$headers = [
    'Title',
    'Local URL',
    'Staging URL',
    'Local Form Link',
    'Staging Form Link',
    'Content on Drive',
    'Added from drive',
    'Added from form',
    'Status',
    'Status set by',
    'Notes',
];
$last_col = 'K';
$on_drive_col = 'F';
$added_drive_col = 'G';
$added_form_col = 'H';
$status_col = 'I';
$set_by_col = 'J';
$link_columns = ['B', 'C', 'D', 'E'];

$build_row = static function (WP_Post $post) use (
    $sitemap_rank_for,
    $form_url_by_post,
    $local_home,
    $front_page_id
): array {
    $post_id = (int) $post->ID;
    $local_url = matrix_workbook_pretty_url($post);
    $title = (string) $post->post_title;
    $notes = [];

    if ($post_id === $front_page_id) {
        $local_url = home_url('/');
        $title = $title !== '' ? $title . ' (homepage)' : 'Home (homepage)';
    }

    // The sitemap only enumerates pages, so the flag would be meaningless on CPT rows.
    if ($post->post_type === 'page' && $sitemap_rank_for($post) === null) {
        $notes[] = 'Not in Slickplan sitemap';
    }

    $form_url = $form_url_by_post[$post_id] ?? '';

    return [
        $title,
        $local_url,
        matrix_workbook_to_staging_url($local_url, $local_home),
        $form_url,
        matrix_workbook_to_staging_url($form_url, $local_home),
        matrix_workbook_content_on_drive_label($post_id),
        matrix_workbook_added_from_drive_label($post_id),
        matrix_workbook_added_from_form_label($post_id),
        matrix_workbook_status_label_from_meta($post_id),
        matrix_workbook_status_set_by($post_id),
        implode('. ', $notes),
    ];
};

$spreadsheet = new Spreadsheet();
$spreadsheet->removeSheetByIndex(0);

$csv_rows = [];
$sheet_counts = [];
$used_titles = [];
$total = 0;

foreach ($sheets as $sheet) {
    $title = matrix_workbook_sanitize_sheet_title($sheet['title']);

    if (isset($used_titles[$title])) {
        $used_titles[$title]++;
        $title = matrix_workbook_sanitize_sheet_title($title . ' ' . $used_titles[$title]);
    } else {
        $used_titles[$title] = 1;
    }

    $worksheet = new Worksheet($spreadsheet, $title);
    $spreadsheet->addSheet($worksheet);
    $worksheet->fromArray($headers, null, 'A1');

    $rows = [];

    foreach ($sheet['posts'] as $post) {
        $row = $build_row($post);
        $rows[] = $row;
        $csv_rows[] = array_merge([$title], $row);
    }

    $worksheet->fromArray($rows, null, 'A2');

    $last_row = count($rows) + 1;

    $worksheet->getStyle('A1:' . $last_col . '1')->applyFromArray(matrix_workbook_header_style());
    $worksheet->getRowDimension(1)->setRowHeight(22);
    $worksheet->freezePane('A2');
    $worksheet->setAutoFilter('A1:' . $last_col . $last_row);
    $worksheet->getColumnDimension('A')->setWidth(48);
    $worksheet->getColumnDimension('B')->setWidth(52);
    $worksheet->getColumnDimension('C')->setWidth(52);
    $worksheet->getColumnDimension('D')->setWidth(58);
    $worksheet->getColumnDimension('E')->setWidth(58);
    $worksheet->getColumnDimension($on_drive_col)->setWidth(18);
    $worksheet->getColumnDimension($added_drive_col)->setWidth(18);
    $worksheet->getColumnDimension($added_form_col)->setWidth(18);
    $worksheet->getColumnDimension($status_col)->setWidth(18);
    $worksheet->getColumnDimension($set_by_col)->setWidth(30);
    $worksheet->getColumnDimension($last_col)->setWidth(34);

    foreach ($link_columns as $column) {
        matrix_workbook_hyperlink_column($worksheet, $column, $last_row);
    }

    matrix_workbook_apply_yes_no_controls($worksheet, $last_row, $on_drive_col);
    matrix_workbook_apply_added_from_drive_controls($worksheet, $last_row, $added_drive_col);
    matrix_workbook_apply_added_from_form_controls($worksheet, $last_row, $added_form_col);
    matrix_workbook_apply_status_controls($worksheet, $last_row, $status_col);

    $sheet_counts[$title] = count($rows);
    $total += count($rows);
    WP_CLI::log(sprintf('%s: %d', $title, count($rows)));
}

// --- Summary ---
$summary = new Worksheet($spreadsheet, 'Summary');
$spreadsheet->addSheet($summary, 0);

$summary_rows = [
    ['St Patrick\'s content workbook'],
    ['Generated', gmdate('Y-m-d H:i:s') . ' UTC'],
    ['Local home', $local_home],
    ['Staging home', $staging_home],
    ['Published items', (string) $total],
    ['Form tokens', (string) count($token_log)],
    [],
    ['Status legend', 'Meaning'],
];

$legend = [
    'To do' => 'Not started (also the default when no status is set)',
    'In progress' => 'Being worked on',
    'Done' => 'Marked complete — see Status set by',
    'Delete' => 'Marked for removal',
];

foreach ($legend as $label => $meaning) {
    $summary_rows[] = [$label, $meaning];
}

$summary_rows[] = [];
$summary_rows[] = ['Content on Drive', 'Meaning'];
$summary_rows[] = ['Yes', 'Drive has source copy for this page (folder Y / link)'];
$summary_rows[] = ['No', 'No Drive copy expected for this page'];
$summary_rows[] = ['(blank)', 'Not marked yet — use the Yes/No dropdown'];
$summary_rows[] = [];
$summary_rows[] = ['Added from drive', 'Meaning'];
$summary_rows[] = ['Done', 'Drive copy is already on the website — do not re-import'];
$summary_rows[] = ['(blank)', 'Not yet added from Drive (or needs a check)'];
$summary_rows[] = [];
$summary_rows[] = ['Added from form', 'Meaning'];
$summary_rows[] = ['Yes', 'Content was added/edited via the content form'];
$summary_rows[] = ['(blank)', 'Not marked as form-edited'];
$summary_rows[] = [];
$summary_rows[] = ['Drive library', matrix_workbook_drive_folder_ids()['root_url']];
$summary_rows[] = [];
$summary_rows[] = ['Sheet', 'Rows'];

foreach ($sheet_counts as $label => $count) {
    $summary_rows[] = [$label, (string) $count];
}

$summary_rows[] = [];
$summary_rows[] = ['Form', 'Pages', 'Token', 'Local form URL'];

foreach ($token_log as $entry) {
    $summary_rows[] = [$entry['label'], (string) $entry['pages'], $entry['token'], $entry['url']];
}

$summary->fromArray($summary_rows, null, 'A1');

$summary->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$summary->getColumnDimension('A')->setWidth(34);
$summary->getColumnDimension('B')->setWidth(34);
$summary->getColumnDimension('C')->setWidth(52);
$summary->getColumnDimension('D')->setWidth(64);

// Bold the section headers and colour the legend samples.
$summary->getStyle('A8')->getFont()->setBold(true);
$summary->getStyle('B8')->getFont()->setBold(true);

$legend_row = 9;

foreach (array_keys($legend) as $label) {
    $colours = matrix_workbook_status_colours()[$label];
    $summary->getStyle('A' . $legend_row)->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => $colours['fg']]],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => $colours['bg']],
        ],
    ]);
    $legend_row++;
}

$drive_legend_header = $legend_row + 1;
$summary->getStyle('A' . $drive_legend_header . ':B' . $drive_legend_header)->getFont()->setBold(true);

$sheet_header_row = $drive_legend_header + 6;
$summary->getStyle('A' . $sheet_header_row . ':B' . $sheet_header_row)->getFont()->setBold(true);

$token_header_row = $sheet_header_row + count($sheet_counts) + 2;
$summary->getStyle('A' . $token_header_row . ':D' . $token_header_row)->getFont()->setBold(true);

// --- Instructions ---
$instructions = new Worksheet($spreadsheet, 'Instructions');
$spreadsheet->addSheet($instructions, 1);
$instructions->fromArray([
    ['How to use this workbook'],
    [''],
    ['1. Upload the xlsx to Google Drive and open with Google Sheets, or open it in Excel.'],
    ['2. Published content only. Anything marked for deletion has been drafted and is intentionally absent.'],
    ['3. The Pages sheet holds pages outside a top-level section, with the homepage first.'],
    ['4. Each top-level section has its own sheet (About Us, What We Offer, and so on), then one sheet per post type.'],
    ['5. Rows follow the Slickplan sitemap order. Anything the sitemap does not list sits at the bottom of its sheet, flagged in Notes.'],
    ['6. Local URL and Local Form Link point at ' . $local_home . ' and are for internal reference.'],
    ['7. Staging URL and Staging Form Link point at ' . $staging_home . ' and are the client-facing links.'],
    ['8. Staging form links only resolve after the database is search-replaced onto staging, because the form tokens live in the database.'],
    ['9. Form links open the content editing form directly on that row\'s page. Editing requires a logged-in Administrator or Site Editor.'],
    ['10. Large sheets are split across several forms (see the Form table on Summary) so no single form has to render too many pages.'],
    ['11. Content on Drive = Yes/No: does Drive have source copy for this page?'],
    ['12. Added from drive = Done when that Drive copy is already on the website. Leave blank until added. Do not re-import Done rows if copy already matches.'],
    ['13. Added from form = Yes when content was added/edited via the content form (not Drive).'],
    ['14. Status mirrors the Content status column in wp-admin (Pages and Posts lists): To do, In progress, Done, or Delete.'],
    ['15. Status set by shows who marked a row Done. The plugin only records an author for Done, so other statuses are blank.'],
    ['16. Re-running the export regenerates the file and mints fresh form tokens, so work in a copy once you start marking Drive / Status columns.'],
    [''],
    ['Regenerate command:'],
    ['wp eval-file wp-content/themes/matrix-starter/scripts/export-sitemap-content-workbook.php'],
], null, 'A1');
$instructions->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$instructions->getColumnDimension('A')->setWidth(120);

$spreadsheet->setActiveSheetIndex(0);

$writer = new Xlsx($spreadsheet);
$writer->save($xlsx_path);

$csv = fopen($csv_path, 'wb');

if ($csv === false) {
    WP_CLI::error('Could not write CSV: ' . $csv_path);
}

fputcsv($csv, array_merge(['Sheet'], $headers), ',', '"', '');

foreach ($csv_rows as $row) {
    fputcsv($csv, $row, ',', '"', '');
}

fclose($csv);

WP_CLI::success(sprintf('Exported %d published items across %d sheets → %s', $total, count($sheet_counts), $xlsx_path));
WP_CLI::log('CSV also written → ' . $csv_path);
