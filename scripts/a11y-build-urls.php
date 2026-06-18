<?php

/**
 * Build the list of public URLs for the full-site accessibility scan.
 *
 * Writes path-only entries (host-agnostic) to tests/a11y/urls.json so the
 * Playwright baseURL (BASE_URL) controls which environment is scanned.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/a11y-build-urls.php
 */

$paths = ['/'];

$types = get_post_types(['public' => true], 'names');
unset($types['attachment']);

foreach ($types as $type) {
    $ids = get_posts([
        'post_type' => $type,
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'suppress_filters' => false,
    ]);

    foreach ($ids as $id) {
        $url = get_permalink($id);

        if (! is_string($url) || $url === '') {
            continue;
        }

        $path = (string) wp_parse_url($url, PHP_URL_PATH);

        if ($path === '') {
            $path = '/';
        }

        $paths[] = $path;
    }
}

$paths = array_values(array_unique($paths));
sort($paths);

$out_dir = get_template_directory() . '/tests/a11y';

if (! is_dir($out_dir)) {
    wp_mkdir_p($out_dir);
}

$out_file = $out_dir . '/urls.json';
file_put_contents($out_file, json_encode($paths, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

if (class_exists('WP_CLI')) {
    WP_CLI::success(sprintf('Wrote %d URLs to %s', count($paths), $out_file));
} else {
    echo 'Wrote ' . count($paths) . " URLs to {$out_file}\n";
}
