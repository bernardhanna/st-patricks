<?php
/**
 * Seed FileBird media folders from the gathering workbook Media folders tab.
 *
 * Usage: wp eval-file scripts/seed-filebird-media-folders.php
 *        wp eval-file scripts/seed-filebird-media-folders.php dry-run
 */

if (! defined('ABSPATH')) {
    exit(1);
}

$dry_run = in_array('dry-run', array_map('strval', $GLOBALS['argv'] ?? []), true);

if (! class_exists('\FileBird\Model\Folder')) {
    WP_CLI::error('FileBird is not active. Install/activate the filebird plugin first.');
}

use FileBird\Model\Folder as FolderModel;

$xlsx = get_template_directory() . '/old/content/St Patricks Content Migration  and gathering.xlsx';
if (! is_readable($xlsx)) {
    WP_CLI::error('Missing gathering xlsx: ' . $xlsx);
}

// Parse Media folders sheet
$zip = new ZipArchive();
if ($zip->open($xlsx) !== true) {
    WP_CLI::error('Cannot open xlsx');
}

$ns = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
$ss = [];
$ss_xml = new DOMDocument();
$ss_xml->loadXML($zip->getFromName('xl/sharedStrings.xml'));
foreach ($ss_xml->getElementsByTagNameNS($ns, 'si') as $si) {
    $text = '';
    foreach ($si->getElementsByTagNameNS($ns, 't') as $t) {
        $text .= $t->textContent;
    }
    $ss[] = $text;
}

$wb = new DOMDocument();
$wb->loadXML($zip->getFromName('xl/workbook.xml'));
$rels = new DOMDocument();
$rels->loadXML($zip->getFromName('xl/_rels/workbook.xml.rels'));
$rid_map = [];
foreach ($rels->getElementsByTagName('Relationship') as $rel) {
    $rid_map[$rel->getAttribute('Id')] = $rel->getAttribute('Target');
}

$sheet_target = null;
foreach ($wb->getElementsByTagNameNS($ns, 'sheet') as $sh) {
    if ($sh->getAttribute('name') === 'Media folders') {
        $rid = $sh->getAttributeNS('http://schemas.openxmlformats.org/officeDocument/2006/relationships', 'id');
        $sheet_target = 'xl/' . ltrim($rid_map[$rid], '/');
        break;
    }
}

$sheet = new DOMDocument();
$sheet->loadXML($zip->getFromName($sheet_target));
$zip->close();

$rows = [];
foreach ($sheet->getElementsByTagNameNS($ns, 'c') as $c) {
    $ref = $c->getAttribute('r');
    if (! preg_match('/^([A-Z]+)(\d+)$/', $ref, $m)) {
        continue;
    }
    $v_nodes = $c->getElementsByTagNameNS($ns, 'v');
    if ($v_nodes->length === 0) {
        continue;
    }
    $v = $v_nodes->item(0)->textContent;
    if ($c->getAttribute('t') === 's') {
        $v = $ss[(int) $v] ?? '';
    }
    $rows[(int) $m[2]][$m[1]] = $v;
}

$paths = [];
foreach ($rows as $r => $cols) {
    if ($r === 1) {
        continue;
    }
    $raw = trim((string) ($cols['C'] ?? ''));
    if ($raw === '') {
        continue;
    }
    // "Files and documents › Advocacy › …"
    $parts = array_values(array_filter(array_map('trim', preg_split('/\s*›\s*/u', $raw))));
    if (! $parts) {
        continue;
    }
    // Drop redundant top "Files and documents" — FileBird Media Library is already files
    if (isset($parts[0]) && strcasecmp($parts[0], 'Files and documents') === 0) {
        array_shift($parts);
    }
    if (! $parts) {
        continue;
    }
    $paths[] = [
        'id' => (string) ($cols['A'] ?? ''),
        'parts' => $parts,
        'notes' => (string) ($cols['D'] ?? ''),
        'raw' => $raw,
    ];
}

WP_CLI::log(sprintf('Media folder paths to seed: %d%s', count($paths), $dry_run ? ' (dry-run)' : ''));

$created = 0;
$existed = 0;
$cache = []; // "parentId|name" => folder id

$ensure = static function (string $name, int $parent) use (&$cache, &$created, &$existed, $dry_run) {
    $key = $parent . '|' . mb_strtolower($name);
    if (isset($cache[$key])) {
        return $cache[$key];
    }
    if ($dry_run) {
        $fake = 100000 + count($cache);
        $cache[$key] = $fake;
        $created++;
        return $fake;
    }
    $before = FolderModel::detail($name, $parent);
    $folder = FolderModel::newOrGet($name, $parent, true);
    if (! is_array($folder) || empty($folder['id'])) {
        WP_CLI::warning('Failed creating folder: ' . $name . ' (parent ' . $parent . ')');
        return 0;
    }
    $id = (int) $folder['id'];
    $cache[$key] = $id;
    if (is_null($before)) {
        $created++;
    } else {
        $existed++;
    }
    return $id;
};

foreach ($paths as $path) {
    $parent = 0;
    foreach ($path['parts'] as $part) {
        $part = sanitize_text_field($part);
        if ($part === '') {
            continue;
        }
        $parent = $ensure($part, $parent);
        if (! $parent) {
            break;
        }
    }
}

WP_CLI::success(sprintf(
    'FileBird folders %s: created=%d reused=%d',
    $dry_run ? 'would process' : 'seeded',
    $created,
    $existed
));

if (! $dry_run) {
    WP_CLI::log('Open Media in WP admin to see the folder tree.');
    WP_CLI::log('Free FileBird is enough for nested media folders. Pro is optional later (ZIP download, colours, etc.).');
}
