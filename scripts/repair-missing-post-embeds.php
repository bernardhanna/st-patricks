<?php
/**
 * Restore known missing embeds on migrated news posts.
 *
 * Migration previously dropped iframe-only sections (YouTube) and left Issuu
 * iframes absolutely positioned without a wrapper. This repairs those posts
 * in-place without needing the old/html archive on the server.
 *
 * Usage:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/repair-missing-post-embeds.php
 *   wp eval-file wp-content/themes/matrix-starter/scripts/repair-missing-post-embeds.php dry-run
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/inc/link-functions.php';

$dry_run = in_array('dry-run', $GLOBALS['args'] ?? [], true)
    || in_array('dry-run', $_SERVER['argv'] ?? [], true);

$youtube_iframe = '<p><iframe width="560" height="315" src="https://www.youtube.com/embed/8Od763ER_Qg" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen=""></iframe></p>';

$jobs = [
    [
        'slug' => 'resources-for-mental-health-and-the-workplace',
        'old_path' => 'media-centre/news/2023/march/resources-for-mental-health-and-the-workplace',
        'needle' => 'youtube.com/embed/8Od763ER_Qg',
        'append_if_missing' => $youtube_iframe,
    ],
    [
        'slug' => 'annual-report-and-outcomes-report-2023',
        'old_path' => 'media-centre/news/2024/july/annual-report-and-outcomes-report-2023',
        'needle' => 'e.issuu.com/embed.html',
        'append_if_missing' => '',
    ],
];

$updated = 0;

foreach ($jobs as $job) {
    $posts = get_posts([
        'name' => $job['slug'],
        'post_type' => 'post',
        'post_status' => ['publish', 'draft', 'private'],
        'numberposts' => 1,
    ]);

    if ($posts === []) {
        $posts = get_posts([
            'post_type' => 'post',
            'post_status' => ['publish', 'draft', 'private'],
            'numberposts' => 1,
            'meta_key' => '_matrix_migrate_old_path',
            'meta_value' => $job['old_path'],
        ]);
    }

    if ($posts === []) {
        WP_CLI::warning('Post not found: ' . $job['slug']);
        continue;
    }

    $post = $posts[0];
    $content = (string) $post->post_content;
    $changed = false;

    if (! str_contains($content, $job['needle']) && $job['append_if_missing'] !== '') {
        $content = rtrim($content) . "\n\n" . $job['append_if_missing'] . "\n";
        $changed = true;
        WP_CLI::log(sprintf('Will append missing embed to #%d %s', $post->ID, $post->post_title));
    }

    if (function_exists('matrix_normalize_absolute_embeds_in_html')) {
        $normalized = matrix_normalize_absolute_embeds_in_html($content);
        if ($normalized !== $content) {
            $content = $normalized;
            $changed = true;
            WP_CLI::log(sprintf('Will normalise absolute embeds on #%d %s', $post->ID, $post->post_title));
        }
    }

    if (! $changed) {
        WP_CLI::log(sprintf('OK already has embed: #%d %s', $post->ID, $post->post_title));
        continue;
    }

    if ($dry_run) {
        WP_CLI::log(sprintf('[dry-run] would update #%d %s', $post->ID, $post->post_title));
        continue;
    }

    wp_update_post([
        'ID' => $post->ID,
        'post_content' => $content,
    ]);
    WP_CLI::success(sprintf('Updated #%d %s', $post->ID, $post->post_title));
    $updated++;
}

WP_CLI::log($dry_run ? 'Dry run complete.' : sprintf('Done. Updated %d post(s).', $updated));
