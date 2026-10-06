<?php

/**
 * Audit Drive-flagged sheet rows vs the site.
 *
 * Rules:
 * - Drive folder Y/link = Drive has source copy to check
 * - If site already matches Drive → mark Added from drive = Done (do not re-import)
 * - If site is empty/placeholder → import Drive copy once, then mark Done
 * - If site has different curated copy → leave alone (do not overwrite)
 *
 * Also ensures the workbook has an "Added from drive" column (Done dropdown).
 *
 * Usage:
 *   wp eval-file .../scripts/audit-drive-vs-site.php dry-run
 *   wp eval-file .../scripts/audit-drive-vs-site.php
 *   wp eval-file .../scripts/audit-drive-vs-site.php fill
 *   wp eval-file .../scripts/audit-drive-vs-site.php fill dry-run
 */

if (! defined('ABSPATH') || ! class_exists('WP_CLI')) {
    exit(1);
}

define('MATRIX_DRIVE3_NO_RUN', true);

require_once WP_PLUGIN_DIR . '/matrix-content-gathering/vendor/autoload.php';
require_once get_template_directory() . '/scripts/lib/drive-library-workbook.php';
require_once get_template_directory() . '/scripts/import-drive-library-3.php';
require_once get_template_directory() . '/scripts/lib/page-seed-conventions.php';

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

$argv_flags = array_map('strval', $GLOBALS['argv'] ?? []);
$dry_run = in_array('dry-run', $argv_flags, true);
$do_fill = in_array('fill', $argv_flags, true);

$xlsx = get_template_directory() . '/old/content/St Patricks Content - List (1).xlsx';
$library = get_template_directory() . '/old/content/SPMHS-Content-Gathering-Library 4';

if (! is_readable($xlsx)) {
    WP_CLI::error('Missing workbook: ' . $xlsx);
}
if (! is_dir($library)) {
    WP_CLI::error('Missing Library 4: ' . $library);
}

/**
 * Sheet title/path hints → Drive relative folder (About Us + shared maps).
 *
 * @return array<string, string>
 */
$folder_by_hint = static function (): array {
    $map = matrix_workbook_drive_page_folder_map();
    // Extra title hints from the client About Us sheet.
    $map['support us'] = '02-Page-content/About Us/Support Us';
    $map['psychiatrists'] = '02-Page-content/About Us/Psychiatrists';
    $map['social workers'] = '02-Page-content/About Us/Social workers';
    $map['nurses'] = '02-Page-content/About Us/Nurses';
    $map['occupational therapists'] = '02-Page-content/About Us/Occupational therapists';
    $map['psychologists'] = '02-Page-content/About Us/Clinical psychologists';
    $map['policies and publications'] = '02-Page-content/About Us/Policies and publications';
    $map['recruitment and useful information'] = '02-Page-content/About Us/Recruitment and useful information';
    $map['staff wellbeing'] = '02-Page-content/About Us/Staff wellbeing';
    $map['apply for a nursing role'] = '02-Page-content/About Us/Apply for a role';
    $map['research'] = '02-Page-content/About Us/Research';
    $map['advocacy'] = '02-Page-content/About Us/Advocacy';
    $map['our present and future'] = '02-Page-content/About Us/Our present and future';
    $map['national centre'] = '02-Page-content/About Us/National centre';
    $map['modernising our facilities'] = '02-Page-content/About Us/New hospital';
    $map['advocacy centre'] = '02-Page-content/About Us/Advocacy centre';
    $map['academic institute'] = '02-Page-content/About Us/Academic Institute';
    $map['traning centre'] = '02-Page-content/About Us/Training Centre';
    $map['training centre'] = '02-Page-content/About Us/Training Centre';
    $map['extending and enhancing our services'] = '02-Page-content/About Us/Extending our services';
    $map['partnering with service users'] = '02-Page-content/About Us/Partnering with service users';
    $map['our locations'] = '02-Page-content/About Us/Our locations';
    $map['payment for our services'] = '02-Page-content/About Us/Payment';
    $map['women’s mental health network'] = '02-Page-content/About Us/Women_s Mental Health Network (WMHN)';
    $map["women's mental health network"] = '02-Page-content/About Us/Women_s Mental Health Network (WMHN)';
    $map['safeguarding'] = '02-Page-content/About Us/Safeguarding';

    return $map;
};

$plain = static function (string $text): string {
    $text = html_entity_decode(wp_strip_all_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = strtolower((string) preg_replace('/\s+/u', ' ', $text));

    return trim($text);
};

$wp_blob = static function (int $post_id) use ($plain): string {
    $post = get_post($post_id);
    if (! $post instanceof WP_Post) {
        return '';
    }
    $parts = [(string) $post->post_content, (string) $post->post_excerpt];
    $flexi = get_field('flexible_content_blocks', $post_id);
    if (is_array($flexi)) {
        foreach ($flexi as $row) {
            foreach (['heading', 'content', 'intro', 'intro_text'] as $key) {
                if (! empty($row[$key]) && is_string($row[$key])) {
                    $parts[] = $row[$key];
                }
            }
            if (! empty($row['items']) && is_array($row['items'])) {
                foreach ($row['items'] as $item) {
                    $parts[] = (string) ($item['title'] ?? '');
                    if (! empty($item['content_rows']) && is_array($item['content_rows'])) {
                        foreach ($item['content_rows'] as $cr) {
                            $parts[] = (string) ($cr['content'] ?? '');
                        }
                    }
                }
            }
            if (! empty($row['links']) && is_array($row['links'])) {
                foreach ($row['links'] as $link_row) {
                    $parts[] = (string) ($link_row['link']['title'] ?? '');
                }
            }
        }
    }

    return $plain(implode(' ', $parts));
};

$is_empty_site = static function (int $post_id) use ($wp_blob): bool {
    $blob = $wp_blob($post_id);
    if (strlen($blob) < 120) {
        return true;
    }
    if (preg_match('/draft content for client gathering|replace this copy|lorem ipsum/i', $blob)) {
        return true;
    }

    return false;
};

$overlap = static function (string $drive, string $wp): float {
    if ($drive === '' || $wp === '') {
        return 0.0;
    }
    $words = preg_split('/\W+/u', $drive) ?: [];
    $words = array_values(array_unique(array_filter($words, static function (string $w): bool {
        return strlen($w) > 4;
    })));
    if (count($words) < 12) {
        // Short docs: use similar_text.
        similar_text($drive, $wp, $pct);

        return $pct / 100;
    }
    $hits = 0;
    foreach ($words as $word) {
        if (str_contains($wp, $word)) {
            $hits++;
        }
    }

    return $hits / count($words);
};

$docx_plain = static function (string $docx) use ($plain): string {
    $tmp = sys_get_temp_dir() . '/drive-audit-' . md5($docx) . '.txt';
    if (! is_readable($tmp) || filemtime($tmp) < filemtime($docx)) {
        exec('pandoc ' . escapeshellarg($docx) . ' -t plain --wrap=none -o ' . escapeshellarg($tmp) . ' 2>/dev/null');
    }
    if (! is_readable($tmp)) {
        return '';
    }

    return $plain((string) file_get_contents($tmp));
};

$resolve_folder = static function (string $title, string $local_url) use ($folder_by_hint): string {
    $hints = $folder_by_hint();
    $path = trim((string) (wp_parse_url($local_url, PHP_URL_PATH) ?: ''), '/');
    if ($path !== '' && isset($hints[$path])) {
        return $hints[$path];
    }
    // CPT style keys.
    if (preg_match('#programmes-therapies/([^/]+)#', $path, $m) && isset($hints['cpt:programmes_therapies:' . $m[1]])) {
        return $hints['cpt:programmes_therapies:' . $m[1]];
    }
    if (preg_match('#mental-health/([^/]+)#', $path, $m) && isset($hints['cpt:mental_health:' . $m[1]])) {
        return $hints['cpt:mental_health:' . $m[1]];
    }
    $title_key = strtolower(trim($title));
    if (isset($hints[$title_key])) {
        return $hints[$title_key];
    }
    // Walk path suffixes.
    $parts = explode('/', $path);
    for ($i = 0; $i < count($parts); $i++) {
        $try = implode('/', array_slice($parts, $i));
        if (isset($hints[$try])) {
            return $hints[$try];
        }
    }

    return '';
};

$resolve_post = static function (string $local_url, string $form): int {
    if (preg_match('/matrix_page=(\d+)/', $form, $m)) {
        return (int) $m[1];
    }
    $path = trim((string) (wp_parse_url($local_url, PHP_URL_PATH) ?: ''), '/');
    if ($path === '') {
        return 0;
    }
    $page = get_page_by_path($path);
    if ($page instanceof WP_Post) {
        return (int) $page->ID;
    }
    foreach (['page', 'get_involved', 'programmes_therapies', 'mental_health', 'locations'] as $type) {
        $page = get_page_by_path($path, OBJECT, $type);
        if ($page instanceof WP_Post) {
            return (int) $page->ID;
        }
    }
    $found = get_posts([
        'name' => basename($path),
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

    return 0;
};

$mark_done = static function (int $post_id): void {
    update_post_meta($post_id, MATRIX_WORKBOOK_ADDED_FROM_DRIVE_META_KEY, 'done');
    update_post_meta($post_id, MATRIX_WORKBOOK_CONTENT_ON_DRIVE_META_KEY, 'yes');
};

$wb = IOFactory::load($xlsx);
$stats = [
    'done_match' => 0,
    'filled' => 0,
    'skipped_curated' => 0,
    'missing_page' => 0,
    'missing_docx' => 0,
    'already_done' => 0,
];
$sheet_changed = false;

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

    // Ensure Added from drive column exists (after Content on Drive, else after Drive folder).
    if (! isset($index['added from drive'])) {
        $after = $index['content on drive'] ?? $index['drive folder'] ?? (count($headers) - 1);
        $insert_at = $after + 2; // 1-based excel col after that index
        $ws->insertNewColumnBefore(Coordinate::stringFromColumnIndex($insert_at));
        $ws->setCellValue(Coordinate::stringFromColumnIndex($insert_at) . '1', 'Added from drive');
        $sheet_changed = true;
        // Refresh headers/index.
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

    $title_i = $index['title'];
    $local_i = $index['local url'] ?? null;
    $form_i = $index['local form link'] ?? null;
    $drive_i = $index['drive folder'] ?? null;
    $on_i = $index['content on drive'] ?? null;
    $added_i = $index['added from drive'];
    $status_i = $index['status'] ?? null;
    $added_col = Coordinate::stringFromColumnIndex($added_i + 1);
    $last_row = max(2, $ws->getHighestDataRow());

    for ($r = 1, $count = count($rows); $r < $count; $r++) {
        $row = $rows[$r];
        $title = trim((string) ($row[$title_i] ?? ''));
        if ($title === '') {
            continue;
        }
        $status = $status_i !== null ? strtolower(trim((string) ($row[$status_i] ?? ''))) : '';
        if ($status === 'deleted') {
            continue;
        }

        $drive = $drive_i !== null ? trim((string) ($row[$drive_i] ?? '')) : '';
        $on = $on_i !== null ? strtolower(trim((string) ($row[$on_i] ?? ''))) : '';
        $added = strtolower(trim((string) ($row[$added_i] ?? '')));
        $has_drive = $drive !== '' && ! in_array(strtolower($drive), ['none', 'n/a', '-', 'nan'], true);
        $drive_flagged = $has_drive || in_array($on, ['yes', 'y'], true);
        if (! $drive_flagged) {
            continue;
        }

        $excel_row = $r + 1;
        $local = $local_i !== null ? trim((string) ($row[$local_i] ?? '')) : '';
        $form = $form_i !== null ? (string) ($row[$form_i] ?? '') : '';
        $post_id = $resolve_post($local, $form);

        if ($post_id <= 0) {
            $stats['missing_page']++;
            WP_CLI::log(sprintf('[%s] %s → missing WP page', $ws->getTitle(), $title));
            continue;
        }

        if (in_array($added, ['done', 'yes', 'y'], true) && ! $is_empty_site($post_id)) {
            $stats['already_done']++;
            if (! $dry_run) {
                $mark_done($post_id);
            }
            continue;
        }

        $folder_rel = $resolve_folder($title, $local);
        if ($folder_rel === '') {
            $stats['missing_docx']++;
            WP_CLI::log(sprintf('[%s] %s → no folder map', $ws->getTitle(), $title));
            continue;
        }

        $folder = $library . '/' . $folder_rel;
        $docx = matrix_drive3_find_docx($folder);
        if ($docx === '' || preg_match('/comments on migrated|already in media library|^image for /i', basename($docx))) {
            // Prefer a non-image doc when find_docx picked a media note.
            $candidates = glob($folder . '/*.docx') ?: [];
            $docx = '';
            foreach ($candidates as $file) {
                $base = basename($file);
                if (str_starts_with($base, '~$')) {
                    continue;
                }
                if (preg_match('/comments on migrated|already in media library|^image for |layout/i', $base)) {
                    continue;
                }
                $docx = $file;
                break;
            }
        }
        if ($docx === '') {
            $stats['missing_docx']++;
            WP_CLI::log(sprintf('[%s] %s → no usable docx in %s', $ws->getTitle(), $title, $folder_rel));
            continue;
        }

        $drive_text = $docx_plain($docx);
        $site_text = $wp_blob($post_id);
        $score = $overlap($drive_text, $site_text);
        $empty = $is_empty_site($post_id);

        if (! $empty && $score >= 0.55) {
            $stats['done_match']++;
            WP_CLI::log(sprintf(
                '[%s] %s → MATCH (%.0f%%) → Done #%d',
                $ws->getTitle(),
                $title,
                $score * 100,
                $post_id
            ));
            if (! $dry_run) {
                $mark_done($post_id);
                $ws->setCellValue($added_col . $excel_row, 'Done');
                if ($on_i !== null) {
                    $ws->setCellValue(Coordinate::stringFromColumnIndex($on_i + 1) . $excel_row, 'Yes');
                }
                $sheet_changed = true;
            }
            continue;
        }

        if ($empty && $do_fill) {
            WP_CLI::log(sprintf(
                '[%s] %s → EMPTY → %s #%d <- %s',
                $ws->getTitle(),
                $title,
                $dry_run ? 'would-fill' : 'fill',
                $post_id,
                basename($docx)
            ));
            if (! $dry_run) {
                $existing_flexi = get_field('flexible_content_blocks', $post_id);
                $has_special = false;
                if (is_array($existing_flexi)) {
                    foreach ($existing_flexi as $row_flexi) {
                        $layout = (string) ($row_flexi['acf_fc_layout'] ?? '');
                        if (in_array($layout, [
                            'locations_grid',
                            'about_links_grid',
                            'programmes_therapies_archive',
                            'multidisciplinary_team_grid',
                            'research_cards_grid',
                            'stories',
                            'contact_form',
                        ], true)) {
                            $has_special = true;
                            break;
                        }
                    }
                }
                if (get_post_type($post_id) === 'programmes_therapies') {
                    $spec = [
                        'slug' => (string) get_post_field('post_name', $post_id),
                        'title' => get_the_title($post_id),
                        'type' => 'programme',
                    ];
                    matrix_drive3_apply_programme($spec, $docx, $folder);
                } else {
                    matrix_drive3_apply_page($post_id, $docx, $folder, [
                        'keep' => $has_special,
                        'builder' => true,
                        'status' => 'publish',
                    ]);
                }
                $mark_done($post_id);
                $ws->setCellValue($added_col . $excel_row, 'Done');
                if ($on_i !== null) {
                    $ws->setCellValue(Coordinate::stringFromColumnIndex($on_i + 1) . $excel_row, 'Yes');
                }
                $sheet_changed = true;
            }
            $stats['filled']++;
            continue;
        }

        if ($empty && ! $do_fill) {
            WP_CLI::log(sprintf(
                '[%s] %s → EMPTY (run with fill to import) #%d',
                $ws->getTitle(),
                $title,
                $post_id
            ));
            $stats['filled']++; // counted as needing fill
            continue;
        }

        // Site has different curated content — do not overwrite.
        $stats['skipped_curated']++;
        WP_CLI::log(sprintf(
            '[%s] %s → curated/different (%.0f%%) — leave #%d',
            $ws->getTitle(),
            $title,
            $score * 100,
            $post_id
        ));
    }

    matrix_workbook_apply_added_from_drive_controls($ws, $last_row, $added_col);
    if (isset($on_i)) {
        matrix_workbook_apply_yes_no_controls($ws, $last_row, Coordinate::stringFromColumnIndex($on_i + 1));
    }
    $ws->getColumnDimension($added_col)->setWidth(18);
}

if (! $dry_run && $sheet_changed) {
    $writer = IOFactory::createWriter($wb, 'Xlsx');
    $writer->save($xlsx);
}

WP_CLI::success(sprintf(
    '%sDrive audit — match/Done:%d filled/needed:%d curated-skip:%d missing-page:%d missing-docx:%d already-done:%d%s',
    $dry_run ? '[DRY RUN] ' : '',
    $stats['done_match'],
    $stats['filled'],
    $stats['skipped_curated'],
    $stats['missing_page'],
    $stats['missing_docx'],
    $stats['already_done'],
    $do_fill ? '' : ' (add "fill" to import empties only)'
));
