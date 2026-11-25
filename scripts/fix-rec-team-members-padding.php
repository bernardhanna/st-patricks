<?php
/**
 * Set Research Ethics Committee team_members block vertical_padding to none.
 *
 * HTTP (staging/local):
 *   /wp-content/themes/matrix-starter/scripts/fix-rec-team-members-padding.php?key=fix-rec-padding
 *
 * CLI:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/fix-rec-team-members-padding.php
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-rec-padding') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

$post = get_page_by_path('about-us/research/research-ethics-committee');
if (! $post instanceof WP_Post) {
    $post = get_page_by_path('research-ethics-committee');
}

if (! $post instanceof WP_Post) {
    echo "FAIL page not found\n";
    exit(1);
}

$post_id = (int) $post->ID;
$rows = get_field('flexible_content_blocks', $post_id);

if (! is_array($rows) || $rows === []) {
    echo "FAIL no flexi rows on #{$post_id}\n";
    exit(1);
}

$updated = 0;

foreach ($rows as $index => $row) {
    if (! is_array($row) || ($row['acf_fc_layout'] ?? '') !== 'team_members') {
        continue;
    }

    $rows[$index]['vertical_padding'] = 'none';
    $updated++;
}

if ($updated < 1) {
    echo "FAIL no team_members layout on #{$post_id}\n";
    exit(1);
}

update_field('flexible_content_blocks', $rows, $post_id);
clean_post_cache($post_id);

echo "OK REC #{$post_id}: set vertical_padding=none on {$updated} team_members block(s)\n";
