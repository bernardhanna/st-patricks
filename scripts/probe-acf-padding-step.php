<?php
/**
 * Probe ACF rem padding step values on disk (staging deploy check).
 * Usage: /wp-content/themes/matrix-starter/scripts/probe-acf-padding-step.php?key=probe-acf-padding-step
 */
declare(strict_types=1);

$key = $_GET['key'] ?? '';
if ($key !== 'probe-acf-padding-step') {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

$theme = dirname(__DIR__);
$files = [
    'acf_content.php' => $theme . '/acf-fields/partials/blocks/acf_content.php',
    'acf_content_accordion.php' => $theme . '/acf-fields/partials/blocks/acf_content_accordion.php',
    'shared_helper' => $theme . '/inc/content-section-functions.php',
];

$out = [
    'theme' => $theme,
    'git_head' => trim((string) @file_get_contents($theme . '/.git/HEAD')),
    'files' => [],
];

foreach ($files as $label => $path) {
    $src = @file_get_contents($path);
    if ($src === false) {
        $out['files'][$label] = ['error' => 'unreadable', 'path' => $path];
        continue;
    }
    preg_match_all("/'padding_top'.*?\'step\'\s*=>\s*([0-9.]+)/s", $src, $tops);
    preg_match_all("/'padding_bottom'.*?\'step\'\s*=>\s*([0-9.]+)/s", $src, $bots);
    $out['files'][$label] = [
        'padding_top_steps' => $tops[1] ?? [],
        'padding_bottom_steps' => $bots[1] ?? [],
        'has_step_1' => (bool) preg_match("/'step'\s*=>\s*1\b/", $src) && str_contains($src, 'padding_top'),
        'mtime' => @filemtime($path),
    ];
}

header('Content-Type: application/json');
echo json_encode($out, JSON_PRETTY_PRINT);
