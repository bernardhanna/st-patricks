<?php

/**
 * Mark Content on Drive = Yes for pages just imported from Library 4.
 *
 * Updates: old/content/St Patricks Content - List (1).xlsx
 *
 * Usage:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/mark-content-on-drive-yes.php
 *   wp eval-file wp-content/themes/matrix-starter/scripts/mark-content-on-drive-yes.php dry-run
 */

if (! defined('ABSPATH') || ! class_exists('WP_CLI')) {
    exit(1);
}

require_once WP_PLUGIN_DIR . '/matrix-content-gathering/vendor/autoload.php';
require_once get_template_directory() . '/scripts/lib/drive-library-workbook.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$dry_run = in_array('dry-run', $GLOBALS['argv'] ?? [], true);
$xlsx = get_template_directory() . '/old/content/St Patricks Content - List (1).xlsx';

if (! is_readable($xlsx)) {
    WP_CLI::error('Missing workbook: ' . $xlsx);
}

/**
 * Title matchers (case-insensitive substring) to mark Yes.
 *
 * @var list<string>
 */
$matchers = [
    'Research',
    'Traning centre',
    'Training centre',
    'Training Centre',
    'Extending and enhancing our services',
    'Payment for our services',
    'Safeguarding',
    'Service User Advisory Network',
    'Service User and Supporters Council',
    'Clinical Roles Careers Brochure',
    'Clinical careers brochure',
    'Non-clinical careers brochure',
];

$wb = IOFactory::load($xlsx);
$updated = 0;
$skipped = 0;

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

    if (! isset($index['content on drive']) || ! isset($index['title'])) {
        continue;
    }

    $title_i = $index['title'];
    $on_i = $index['content on drive'];
    $status_i = $index['status'] ?? null;

    for ($r = 1, $count = count($rows); $r < $count; $r++) {
        $title = trim((string) ($rows[$r][$title_i] ?? ''));
        if ($title === '') {
            continue;
        }

        $matched = false;
        foreach ($matchers as $needle) {
            if (strcasecmp($title, $needle) === 0 || str_contains(strtolower($title), strtolower($needle))) {
                // Avoid matching unrelated "research projects" etc. for exact-ish titles.
                if (strcasecmp($needle, 'Research') === 0 && strcasecmp($title, 'Research') !== 0) {
                    continue;
                }
                if (strcasecmp($needle, 'Safeguarding') === 0 && strcasecmp($title, 'Safeguarding') !== 0) {
                    continue;
                }
                $matched = true;
                break;
            }
        }

        if (! $matched) {
            continue;
        }

        $current = trim((string) ($rows[$r][$on_i] ?? ''));
        if (strcasecmp($current, 'Yes') === 0) {
            $skipped++;
            continue;
        }

        $cell = $ws->getCell([$on_i + 1, $r + 1]);
        WP_CLI::log(sprintf(
            '%s[%s] %s: %s → Yes',
            $dry_run ? '[dry-run] ' : '',
            $ws->getTitle(),
            $title,
            $current === '' ? '(blank)' : $current
        ));

        if (! $dry_run) {
            $cell->setValue('Yes');
            if ($status_i !== null) {
                $status = trim((string) ($rows[$r][$status_i] ?? ''));
                if ($status === '' || strcasecmp($status, 'To do') === 0 || strcasecmp($status, 'In progress') === 0) {
                    $ws->getCell([$status_i + 1, $r + 1])->setValue('Needs review');
                }
            }
        }
        $updated++;
    }
}

if (! $dry_run && $updated > 0) {
    $writer = IOFactory::createWriter($wb, 'Xlsx');
    $writer->save($xlsx);
}

WP_CLI::success(sprintf(
    '%sMarked Content on Drive Yes — updated:%d already-yes:%d',
    $dry_run ? '[DRY RUN] ' : '',
    $updated,
    $skipped
));
