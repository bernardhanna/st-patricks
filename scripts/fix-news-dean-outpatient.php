<?php
/**
 * Point the 2021 outcomes article outpatient link at Dean Clinics.
 *
 *   /wp-content/themes/matrix-starter/scripts/fix-news-dean-outpatient.php?key=fix-news-dean
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-news-dean') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

$posts = get_posts([
    'name' => 'annual-and-outcomes-reports-2021',
    'post_type' => 'post',
    'post_status' => 'any',
    'posts_per_page' => 1,
]);
if ($posts === []) {
    echo "MISSING\n";
    exit;
}

$post = $posts[0];
$home = home_url();
$good = $home . '/what-we-offer/outpatient-care-dean-clinics/';
$html = (string) $post->post_content;
$html = str_replace($home . $good, $good, $html);
$html = str_replace(
    [
        $home . '/what-we-offer/outpatient-clinics/about-the-dean-clinics',
        '/what-we-offer/outpatient-clinics/about-the-dean-clinics',
    ],
    [$good, $good],
    $html
);
if ($html === $post->post_content) {
    echo "unchanged #{$post->ID}\n";
    exit;
}
wp_update_post(['ID' => $post->ID, 'post_content' => $html]);
echo "updated #{$post->ID}\n";
