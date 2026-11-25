<?php
/**
 * Fix broken internal links on Meet Our Team Human Resources blog.
 *
 *   /wp-content/themes/matrix-starter/scripts/fix-meet-our-team-hr-links.php?key=fix-meet-hr
 *   Add &dry=1 to inspect without saving.
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-meet-hr') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

$dry = isset($_GET['dry']);
$slug = 'meet-our-team-human-resources';
$home = untrailingslashit(home_url('/'));

$map = json_decode((string) file_get_contents(get_template_directory() . '/inc/data/path-redirect-map.json'), true);
if (! is_array($map)) {
    $map = [];
}

$pairs = [];
foreach ($map as $from => $to) {
    $from_path = '/' . trim((string) (explode('?', ltrim((string) $from, '/'))[0]), '/');
    if (strlen($from_path) < 2) {
        continue;
    }
    $dest = (string) $to;
    if (! str_starts_with($dest, 'http')) {
        $dest = home_url('/' . ltrim($dest, '/'));
    }
    $pairs[$from_path] = $dest;
}
// Explicit bare careers (also in map; keep as safety).
$pairs['/careers'] = home_url('/about-us/careers/');
uksort($pairs, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));

$hosts = [
    '',
    $home,
    'https://www.stpatricks.ie',
    'http://www.stpatricks.ie',
    'https://stpatricks.ie',
    'http://stpatricks.ie',
    'https://st-patricks.s1.matrix-test.com',
    'http://st-patricks.s1.matrix-test.com',
];

$rewrite_media_centre = static function (string $html) use ($home): string {
    return preg_replace_callback(
        '#https?://[^"\'\s]*/media-centre/(?:blogs-articles|news|podcasts)/\d{4}/[a-z]+/([a-z0-9\-]+)/?#i',
        static function (array $m) use ($home): string {
            return $home . '/' . $m[1] . '/';
        },
        $html
    ) ?? $html;
};

$resolve_path = static function (string $href): array {
    if (preg_match('/^(#|mailto:|tel:)/i', $href)) {
        return ['skip', 0];
    }
    if (preg_match('/\.(pdf|jpg|jpeg|png|gif|webp|mp3|mp4)(\?|$)/i', $href)) {
        return ['file', 0];
    }
    $path = (string) (wp_parse_url($href, PHP_URL_PATH) ?? '');
    if ($path === '' || $path === '/') {
        return ['skip', 0];
    }
    $id = url_to_postid(home_url(trailingslashit($path)));
    if ($id < 1) {
        $id = url_to_postid(home_url($path));
    }
    if ($id < 1) {
        $by = get_page_by_path(trim($path, '/'), OBJECT, ['page', 'post']);
        if ($by) {
            $id = (int) $by->ID;
        }
    }
    return [$id > 0 ? 'ok' : 'open', $id];
};

$posts = get_posts([
    'name' => $slug,
    'post_type' => 'post',
    'post_status' => ['publish', 'draft', 'private'],
    'posts_per_page' => 1,
]);

echo "==== $slug ====\n";
echo 'dry=' . ($dry ? '1' : '0') . "\n";
echo 'home=' . $home . "\n";

if ($posts === []) {
    echo "MISSING\n";
    exit;
}

$post = $posts[0];
$id = (int) $post->ID;
$html = (string) $post->post_content;
$orig = $html;

echo 'id=' . $id . ' status=' . $post->post_status . ' url=' . get_permalink($id) . "\n";
echo "\n-- BEFORE --\n";
preg_match_all('#href=["\']([^"\']+)["\']#i', $html, $before_m);
foreach (array_unique($before_m[1] ?? []) as $href) {
    [$flag, $resolved] = $resolve_path($href);
    echo "  [$flag" . ($resolved ? "#$resolved" : '') . "] $href\n";
}

foreach ($pairs as $from_path => $dest) {
    foreach ($hosts as $host_prefix) {
        foreach ([$from_path, $from_path . '/'] as $path) {
            if ($path === '//') {
                continue;
            }
            $full = $host_prefix . $path;
            $html = str_replace(
                ['href="' . $full . '"', "href='" . $full . "'"],
                ['href="' . esc_url($dest) . '"', "href='" . esc_url($dest) . "'"],
                $html
            );
        }
    }
}
$html = $rewrite_media_centre($html);

$changed = $html !== $orig;
if ($changed && ! $dry) {
    wp_update_post([
        'ID' => $id,
        'post_content' => $html,
    ]);
    clean_post_cache($id);
    echo "\nUPDATED\n";
} elseif ($changed) {
    echo "\nWOULD_UPDATE (dry)\n";
} else {
    echo "\nUNCHANGED\n";
}

echo "\n-- AFTER --\n";
preg_match_all('#href=["\']([^"\']+)["\']#i', $html, $after_m);
$open = [];
foreach (array_unique($after_m[1] ?? []) as $href) {
    [$flag, $resolved] = $resolve_path($href);
    echo "  [$flag" . ($resolved ? "#$resolved" : '') . "] $href\n";
    if ($flag === 'open') {
        $open[] = $href;
    }
}

echo "\nOPEN_COUNT=" . count($open) . "\n";
if ($open !== []) {
    echo "OPEN:\n";
    foreach ($open as $href) {
        echo "  $href\n";
    }
}
echo "DONE\n";
