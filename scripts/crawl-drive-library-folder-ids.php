<?php

/**
 * Crawl the shared SPMHS Content Gathering Library on Google Drive and write
 * a path → folder-id map for the content workbook.
 *
 * Source: https://drive.google.com/drive/folders/19x_kP3NV29kzesNI81ob9XFB9dTUctRk
 *
 * Usage (no WordPress required):
 *   php wp-content/themes/matrix-starter/scripts/crawl-drive-library-folder-ids.php
 */

$root_id = '19x_kP3NV29kzesNI81ob9XFB9dTUctRk';
$out = dirname(__DIR__) . '/old/content/drive-library-folder-ids.json';
$user_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36';

$fetch = static function (string $folder_id) use ($user_agent): string {
    $url = 'https://drive.google.com/drive/folders/' . rawurlencode($folder_id) . '?usp=sharing';
    $context = stream_context_create([
        'http' => [
            'header' => "User-Agent: {$user_agent}\r\n",
            'timeout' => 45,
        ],
    ]);
    $html = @file_get_contents($url, false, $context);

    if ($html === false || $html === '') {
        throw new RuntimeException('Failed to fetch Drive folder: ' . $folder_id);
    }

    return $html;
};

/**
 * @return array<int, array{name: string, id: string}>
 */
$children = static function (string $html): array {
    $found = [];

    if (preg_match_all(
        '/aria-label="([^"]+)"[^>]*ssk=\'5:[^:]+:([a-zA-Z0-9_-]+)-0-16\'/',
        $html,
        $matches,
        PREG_SET_ORDER
    )) {
        foreach ($matches as $match) {
            $label = html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $name = preg_replace('/ Shared (folder|file)$/', '', $label) ?? $label;
            $name = trim($name);
            $id = $match[2];

            if ($name === '' || str_starts_with($name, '.')) {
                continue;
            }

            // Only keep real folders. Drive labels look like "Name Shared folder".
            if (! preg_match('/\bShared folder$/i', $label) && ! preg_match('/\bfolder$/i', $label)) {
                continue;
            }

            // Strip trailing " Shared" if the aria-label included it in the name capture.
            $name = preg_replace('/ Shared$/i', '', $name) ?? $name;
            $name = trim($name);

            if ($name === '') {
                continue;
            }

            $found[] = ['name' => $name, 'id' => $id];
        }
    }

    $seen = [];
    $out = [];

    foreach ($found as $row) {
        if (isset($seen[$row['id']])) {
            continue;
        }

        $seen[$row['id']] = true;
        $out[] = $row;
    }

    return $out;
};

$map = [
    '' => $root_id,
];

// Only crawl page-content trees. Skip 03-Media-library (huge, not needed for workbook links).
$allowed_top = [
    '01-Set-pages' => true,
    '02-Page-content' => true,
    '04-New-pages' => true,
];

$queue = [
    ['path' => '', 'id' => $root_id, 'depth' => 0],
];

$max_depth = 3;
$visited = [];

while ($queue !== []) {
    $current = array_shift($queue);
    $path = $current['path'];
    $id = $current['id'];
    $depth = $current['depth'];

    if (isset($visited[$id]) || $depth > $max_depth) {
        continue;
    }

    $visited[$id] = true;

    try {
        $html = $fetch($id);
    } catch (Throwable $e) {
        fwrite(STDERR, $e->getMessage() . PHP_EOL);
        continue;
    }

    foreach ($children($html) as $child) {
        $child_path = $path === '' ? $child['name'] : $path . '/' . $child['name'];

        if ($depth === 0 && ! isset($allowed_top[$child['name']])) {
            // Still record top-level folders we skip crawling into.
            $map[$child_path] = $child['id'];
            continue;
        }

        $map[$child_path] = $child['id'];

        if ($depth < $max_depth) {
            $queue[] = [
                'path' => $child_path,
                'id' => $child['id'],
                'depth' => $depth + 1,
            ];
        }
    }

    fwrite(STDOUT, sprintf("[%d] %s (%d folders so far)\n", $depth, $path === '' ? '(root)' : $path, count($map)));
    usleep(120000);
}

ksort($map);

$payload = [
    'root_id' => $root_id,
    'root_url' => 'https://drive.google.com/drive/folders/' . $root_id,
    'generated_at' => gmdate('c'),
    'folders' => $map,
];

$dir = dirname($out);

if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
    fwrite(STDERR, "Cannot create {$dir}\n");
    exit(1);
}

file_put_contents($out, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

fwrite(STDOUT, sprintf("Wrote %d folder paths → %s\n", count($map), $out));
