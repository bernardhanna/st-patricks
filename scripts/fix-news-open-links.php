<?php
/**
 * Rewrite leftover internal links on open news snag posts using the path map.
 *
 *   /wp-content/themes/matrix-starter/scripts/fix-news-open-links.php?key=fix-news-open
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-news-open') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

$slugs = [
    'annual-and-outcomes-reports-2021',
    'your-portal-personal-health-record',
    'eating-disorder-journey',
    'mental-health-at-work-supports-for-employees-and-employers',
    'meet-our-team-facilities-coordinator',
    'how-helping-others-can-help-us',
];

$map = json_decode((string) file_get_contents(get_template_directory() . '/inc/data/path-redirect-map.json'), true);
if (! is_array($map)) {
    $map = [];
}

$home = home_url();
$about_mh = home_url('/service-users-and-visitors/about-mental-health/');
$team = home_url('/about-us/our-team/');
$feedback = home_url('/service-users-and-visitors/feedback-and-comments/');
$podcasts = home_url('/news-and-events/?blog_category=podcasts');

$pairs = [];
foreach ($map as $from => $to) {
    $from_path = '/' . trim((string) (explode('?', ltrim((string) $from, '/'))[0]), '/');
    if (strlen($from_path) < 4) {
        continue;
    }
    $dest = (string) $to;
    if (! str_starts_with($dest, 'http')) {
        $dest = home_url('/' . ltrim($dest, '/'));
    }
    $pairs[$from_path] = $dest;
}
uksort($pairs, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));

$hosts = ['', $home, 'https://www.stpatricks.ie', 'http://www.stpatricks.ie', 'https://stpatricks.ie'];

foreach ($slugs as $slug) {
    $posts = get_posts([
        'name' => $slug,
        'post_type' => 'post',
        'post_status' => 'any',
        'posts_per_page' => 1,
    ]);
    echo "\n==== $slug ====\n";
    if ($posts === []) {
        echo "MISSING\n";
        continue;
    }
    $post = $posts[0];
    $html = (string) $post->post_content;
    $orig = $html;
    foreach ($pairs as $from_path => $dest) {
        foreach ($hosts as $host_prefix) {
            foreach ([$from_path, $from_path . '/'] as $path) {
                $full = $host_prefix . $path;
                $html = str_replace(
                    ['href="' . $full . '"', "href='" . $full . "'"],
                    ['href="' . esc_url($dest) . '"', "href='" . esc_url($dest) . "'"],
                    $html
                );
            }
        }
    }
    if ($slug === 'how-helping-others-can-help-us') {
        $html = preg_replace(
            '#href="[^"]+"([^>]*>[^<]*mental health difficult[^<]*</a>)#i',
            'href="' . esc_url($about_mh) . '"$1',
            $html
        ) ?? $html;
    }
    if ($slug === 'meet-our-team-facilities-coordinator') {
        $html = preg_replace('#href="[^"]+"([^>]*>[^<]*\bstaff\b[^<]*</a>)#i', 'href="' . esc_url($team) . '"$1', $html) ?? $html;
        $html = preg_replace('#href="[^"]+"([^>]*>[^<]*comment card[^<]*</a>)#i', 'href="' . esc_url($feedback) . '"$1', $html) ?? $html;
        $html = preg_replace('#href="[^"]+"([^>]*>[^<]*Podcasts[^<]*</a>)#i', 'href="' . esc_url($podcasts) . '"$1', $html) ?? $html;
    }
    if ($html !== $orig) {
        wp_update_post(['ID' => $post->ID, 'post_content' => $html]);
        echo "updated #{$post->ID}\n";
    } else {
        echo "unchanged #{$post->ID}\n";
    }

    preg_match_all('#href=["\']([^"\']+)["\']#i', $html, $m);
    foreach (array_unique($m[1] ?? []) as $href) {
        if (preg_match('#^(#|mailto:|tel:|https?://(www\.)?(twitter|facebook|x\.com|wa\.me|youtube|linkedin))#i', $href)) {
            continue;
        }
        if (preg_match('/\.(pdf|jpg|jpeg|png|gif|webp|mp3)(\?|$)/i', $href)) {
            continue;
        }
        $path = (string) (wp_parse_url($href, PHP_URL_PATH) ?? '');
        if ($path === '') {
            continue;
        }
        $id = url_to_postid(home_url(trailingslashit($path)));
        if ($id < 1 && ! str_contains($href, 'blog_category=')) {
            echo "  OPEN $href\n";
        }
    }
}
