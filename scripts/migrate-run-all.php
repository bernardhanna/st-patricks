<?php

/**
 * Run all migration phases in order.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/migrate-run-all.php
 */

$theme_dir = get_template_directory();
$phases = [
    'Phase 1: Media/PDFs' => $theme_dir . '/scripts/migrate-import-media.php',
    'Phase 2: Posts' => $theme_dir . '/scripts/migrate-import-posts.php',
    'Phase 3: Pages' => $theme_dir . '/scripts/migrate-import-pages.php',
    'Phase 4: Redirects CSV' => $theme_dir . '/scripts/migrate-generate-redirects.php',
];

if (! class_exists('WP_CLI')) {
    exit(1);
}

foreach ($phases as $label => $script) {
    WP_CLI::log('');
    WP_CLI::log('=== ' . $label . ' ===');

    if (! is_readable($script)) {
        WP_CLI::error('Missing script: ' . $script);
    }

    require $script;
}

WP_CLI::success('All migration phases completed.');
