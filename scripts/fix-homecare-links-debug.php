<?php
/**
 * Debug + fix adolescent homecare broken links.
 *
 *   /wp-content/themes/matrix-starter/scripts/fix-homecare-links-debug.php?key=fix-homecare-links
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-homecare-links') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

$homecare = get_page_by_path('service-users-and-visitors/your-care-with-willow-grove/your-time-in-homecare-as-an-adolescent');
if (! $homecare instanceof WP_Post) {
    echo "missing page\n";
    exit;
}

$id = (int) $homecare->ID;
$suits = matrix_orlaith_permalink('service-users-and-visitors/service-user-it-support');
$remote = matrix_orlaith_permalink('what-you-need-to-know-about-remote-care');
$access = matrix_orlaith_permalink('service-users-and-visitors/service-user-it-support/accessing-remote-services');
$stay = matrix_orlaith_permalink('service-users-and-visitors/your-care-with-willow-grove/your-stay-in-hospital-as-an-adolescent');

echo "suits=$suits\nremote=$remote\naccess=$access\nstay=$stay\n\n";

$rows = get_field('flexible_content_blocks', $id);
$blob = wp_json_encode($rows);
echo "blob_len=" . strlen((string) $blob) . "\n";

foreach (['service-user-it-support', 'practical-information', 'remote', 'care team', 'suits'] as $needle) {
    $pos = stripos((string) $blob, $needle);
    echo "needle '$needle' => " . ($pos === false ? 'NOT FOUND' : 'at ' . $pos) . "\n";
}

// Show href candidates
if (preg_match_all('/https?:\\\\\/\\\\\/[^"\\\\]+|https?:\/\/[^"\\\\]+|\\\\\/[a-z0-9\\-\/]+/i', (string) $blob, $m)) {
    $urls = array_unique($m[0]);
    echo "\nurls sample:\n";
    foreach (array_slice($urls, 0, 40) as $u) {
        $clean = str_replace(['\\/', '\/'], '/', $u);
        if (preg_match('/suit|remote|practical|care|willow|adolescent|video/i', $clean)) {
            echo "  $clean\n";
        }
    }
}

$patterns = [
    '#https?://[^"\']*service-user-it-support/?#i' => $suits,
    '#/service-user-it-support/?#i' => $suits,
    '#https?://[^"\']*practical-information-for-remote-services/?#i' => $remote,
    '#/practical-information-for-remote-services/?#i' => $remote,
];

$walk = static function (&$node) use (&$walk, $patterns): int {
    $n = 0;
    if (is_string($node)) {
        $orig = $node;
        foreach ($patterns as $pattern => $to) {
            $node = preg_replace($pattern, $to, $node) ?? $node;
        }
        if ($node !== $orig) {
            $n++;
        }
        return $n;
    }
    if (is_array($node)) {
        foreach ($node as &$child) {
            $n += $walk($child);
        }
    }
    return $n;
};

$changed = $walk($rows);
update_field('flexible_content_blocks', $rows, $id);
echo "\nreplacements=$changed saved #$id\n";
