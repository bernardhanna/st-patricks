<?php
/**
 * Set organisation bylines on Speak against stigma and Art mindfulness.
 *
 *   /wp-content/themes/matrix-starter/scripts/fix-speak-stigma-byline.php?key=fix-speak-byline
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-speak-byline') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

$slugs = ['speakagainststigma', 'artmindfulness'];
$name = "St Patrick's Mental Health Services";

foreach ($slugs as $slug) {
    $posts = get_posts([
        'name' => $slug,
        'post_type' => 'post',
        'post_status' => 'any',
        'posts_per_page' => 1,
    ]);
    if ($posts === []) {
        echo "MISSING $slug\n";
        continue;
    }
    $post_id = (int) $posts[0]->ID;
    if (function_exists('update_field')) {
        update_field('post_author_name', $name, $post_id);
    } else {
        update_post_meta($post_id, 'post_author_name', $name);
    }
    echo "$slug #$post_id=" . matrix_get_blog_post_author_name($post_id) . "\n";
}
