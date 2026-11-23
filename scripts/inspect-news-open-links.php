<?php
/**
 * List internal links on open news snag posts.
 *
 *   /wp-content/themes/matrix-starter/scripts/inspect-news-open-links.php?key=inspect-news-open
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'inspect-news-open') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

$slugs = [
    'founders-day-youth-mental-health',
    'annual-and-outcomes-reports-2021',
    'award',
    'your-portal-personal-health-record',
    'eating-disorder-journey',
    'mental-health-at-work-supports-for-employees-and-employers',
    'meet-our-team-facilities-coordinator',
    'how-helping-others-can-help-us',
];

foreach ($slugs as $slug) {
    $posts = get_posts([
        'name' => $slug,
        'post_type' => 'post',
        'post_status' => ['publish', 'draft'],
        'posts_per_page' => 1,
    ]);
    echo "\n==== $slug ====\n";
    if ($posts === []) {
        echo "MISSING\n";
        continue;
    }
    $post = $posts[0];
    echo 'id=' . $post->ID . ' status=' . $post->post_status . "\n";
    $tags = get_the_tags($post->ID);
    echo 'tags=' . (is_array($tags) ? implode(', ', wp_list_pluck($tags, 'name')) : 'none') . "\n";
    $html = (string) $post->post_content;
    if (function_exists('get_field')) {
        $flex = get_field('flexible_content_blocks', $post->ID);
        if (is_array($flex)) {
            $html .= "\n" . wp_json_encode($flex);
        }
    }
    preg_match_all('#href=["\']([^"\']+)["\']#i', $html, $m);
    $hrefs = array_unique($m[1] ?? []);
    foreach ($hrefs as $href) {
        if (str_starts_with($href, '#') || str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:')) {
            continue;
        }
        $path = (string) (wp_parse_url($href, PHP_URL_PATH) ?? $href);
        $path = '/' . trim($path, '/');
        $is_file = (bool) preg_match('/\.(pdf|jpg|jpeg|png|gif|webp|mp3|mp4)$/i', $path);
        $id = url_to_postid(home_url($path . '/'));
        if ($id < 1) {
            $id = url_to_postid(home_url($path));
        }
        $flag = $id > 0 ? "ok#$id" : ($is_file ? 'file' : 'UNRESOLVED');
        if ($flag === 'UNRESOLVED' || str_contains($href, 'wp-content/uploads') && ! $is_file) {
            echo "  $flag  $href\n";
        }
    }
}
