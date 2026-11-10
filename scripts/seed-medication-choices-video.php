<?php
/**
 * One-off: restore missing YouTube talk on mental-health-medication-choices.
 *
 * WP-CLI:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/seed-medication-choices-video.php
 *
 * Or browser (local/staging only):
 *   /wp-content/themes/matrix-starter/scripts/seed-medication-choices-video.php?key=seed-meds-video
 */
if (! defined('ABSPATH')) {
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed_host = str_contains($host, 'localhost')
        || str_contains($host, 'matrix-test.com')
        || in_array($remote, ['127.0.0.1', '::1'], true);

    if (! $allowed_host || ($_GET['key'] ?? '') !== 'seed-meds-video') {
        http_response_code(403);
        exit;
    }

    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

$slug = 'mental-health-medication-choices';
$youtube_id = 'BEEt6th8RbA';
$iframe = '<p><iframe width="560" height="315" src="https://www.youtube.com/embed/'
    . $youtube_id
    . '" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe></p>';

$query = new WP_Query([
    'name' => $slug,
    'post_type' => 'post',
    'post_status' => ['publish', 'draft', 'private'],
    'posts_per_page' => 1,
]);
$post = $query->posts[0] ?? null;

if (! $post) {
    echo "MISS post {$slug}\n";
    return;
}

$content = (string) $post->post_content;

if (str_contains($content, $youtube_id)) {
    echo "OK already present on #{$post->ID}\n";
    return;
}

$needles = [
    '<h3 class="hide-for-main">Watch a talk  with Ciara on medication</h3>',
    '<h3 class="hide-for-main">Watch a talk with Ciara on medication</h3>',
    '<h2>Watch a talk  with Ciara on medication</h2>',
    '<h2>Watch a talk with Ciara on medication</h2>',
];

$updated = false;
foreach ($needles as $needle) {
    if (! str_contains($content, $needle)) {
        continue;
    }
    $content = str_replace($needle, $needle . "\n\n" . $iframe, $content);
    $updated = true;
    break;
}

if (! $updated) {
    // Fallback: append before closing content if heading text exists without exact markup.
    if (preg_match('/Watch a talk\s+with Ciara on medication/i', $content)) {
        $content .= "\n\n" . $iframe;
        $updated = true;
    }
}

if (! $updated) {
    echo "FAIL could not find insertion point on #{$post->ID}\n";
    return;
}

wp_update_post([
    'ID' => (int) $post->ID,
    'post_content' => $content,
]);

echo "DONE inserted {$youtube_id} on #{$post->ID} {$slug}\n";
