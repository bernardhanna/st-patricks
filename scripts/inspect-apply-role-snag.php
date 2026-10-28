<?php

/**
 * Inspect the How to apply for a role page blocks (snag: about-us-menu|apply-for-a-role|1).
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/inspect-apply-role-snag.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

$page = get_page_by_path('about-us/careers/recruitment-and-useful-information/how-to-apply-for-a-role');
if (! $page instanceof WP_Post) {
    WP_CLI::error('page not found');
}

WP_CLI::log('ID: ' . $page->ID . ' | title: ' . $page->post_title);
$rows = get_field('flexible_content_blocks', $page->ID);
if (! is_array($rows)) {
    WP_CLI::error('no flexible_content_blocks');
}

foreach ($rows as $i => $row) {
    $layout = $row['acf_fc_layout'] ?? '?';
    WP_CLI::log('--- [' . $i . '] ' . $layout);
    foreach ($row as $key => $value) {
        if (! is_string($value)) {
            continue;
        }
        if (stripos($value, 'next steps') === false) {
            continue;
        }
        WP_CLI::log('   key: ' . $key);
        WP_CLI::log('   value: ' . $value);
    }
}
