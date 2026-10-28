<?php
/**
 * Fix REC "Board of Governors" link → About Us / Our team (hospital organisation section).
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/fix-rec-board-link.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

$post = get_page_by_path('about-us/research/research-ethics-committee');
if (! $post instanceof WP_Post) {
    $post = get_page_by_path('research-ethics-committee');
}
if (! $post instanceof WP_Post) {
    WP_CLI::error('REC page not found.');
}

$team_page = get_page_by_path('about-us/our-team');
if (! $team_page instanceof WP_Post) {
    WP_CLI::error('Our team page not found.');
}

$team_url = (string) get_permalink($team_page);
$blocks = get_field('flexible_content_blocks', $post->ID);
if (! is_array($blocks)) {
    WP_CLI::error('No flexible content on REC page.');
}

$changed = false;
foreach ($blocks as $index => $row) {
    if (($row['acf_fc_layout'] ?? '') !== 'content' || ($row['heading'] ?? '') !== 'Role of the REC') {
        continue;
    }

    $content = (string) ($row['content'] ?? '');
    $updated = preg_replace(
        '#https?://[^"\']+/about-us/hospital-organisation/?#i',
        esc_url($team_url),
        $content
    );
    $updated = str_replace('/about-us/hospital-organisation/', trailingslashit($team_url), $updated);

    if ($updated === $content) {
        WP_CLI::warning('Role of the REC content unchanged — check link manually.');
        continue;
    }

    $blocks[$index]['content'] = $updated;
    $changed = true;
}

if (! $changed) {
    WP_CLI::error('No link update applied.');
}

update_field('flexible_content_blocks', $blocks, $post->ID);
WP_CLI::success('REC Board of Governors link set to ' . $team_url);
