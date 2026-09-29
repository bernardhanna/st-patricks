<?php

/**
 * Strip Word/Office leftover fragment anchors from published post_content.
 *
 * Usage:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/cleanup-orphan-word-anchors.php
 */

if (! defined('ABSPATH')) {
    fwrite(STDERR, "Run via WP-CLI: wp eval-file scripts/cleanup-orphan-word-anchors.php\n");
    exit(1);
}

global $wpdb;

$like_patterns = [
    '%#_msocom%',
    '%#_ednref%',
    '%#_ftnref%',
    '%#_edn%',
    '%#_ftn%',
];

$ids = [];

foreach ($like_patterns as $like) {
    $found = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_content LIKE %s",
            $like
        )
    );

    foreach ($found as $id) {
        $ids[(int) $id] = true;
    }
}

$updated = 0;

foreach (array_keys($ids) as $post_id) {
    $post = get_post($post_id);

    if (! $post instanceof WP_Post) {
        continue;
    }

    $original = (string) $post->post_content;
    $clean = $original;

    // Empty Word comment / footnote anchors.
    $clean = preg_replace(
        '/<a\b[^>]*href=["\']#_?(?:msocom|ednref|ftnref|edn|ftn)[^"\']*["\'][^>]*>\s*<\/a>/i',
        '',
        $clean
    ) ?? $clean;

    // Footnote refs that only wrap empty/whitespace nodes.
    $clean = preg_replace(
        '/<a\b[^>]*href=["\']#_?(?:msocom|ednref|ftnref|edn|ftn)[^"\']*["\'][^>]*>\s*<\/a>/i',
        '',
        $clean
    ) ?? $clean;

    // Named empty anchors left by Word comments.
    $clean = preg_replace(
        '/<a\b[^>]*(?:id|name)=["\']_?(?:msocom|ednref|ftnref|edn|ftn)[^"\']*["\'][^>]*>\s*<\/a>/i',
        '',
        $clean
    ) ?? $clean;

    if ($clean === $original) {
        continue;
    }

    $result = wp_update_post([
        'ID' => $post_id,
        'post_content' => $clean,
    ], true);

    if (is_wp_error($result)) {
        WP_CLI::warning("#{$post_id}: " . $result->get_error_message());
        continue;
    }

    $updated++;
    WP_CLI::log("Cleaned orphan anchors on #{$post_id} {$post->post_title}");
}

WP_CLI::success("Updated {$updated} of " . count($ids) . ' matching posts.');
