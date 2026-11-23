<?php
/**
 * Repair mangled SUITS URL on adolescent homecare page.
 *
 *   /wp-content/themes/matrix-starter/scripts/fix-homecare-suits-url.php?key=fix-homecare-suits
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-homecare-suits') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

$homecare = get_page_by_path('service-users-and-visitors/your-care-with-willow-grove/your-time-in-homecare-as-an-adolescent');
if (! $homecare instanceof WP_Post) {
    echo "missing\n";
    exit;
}

$id = (int) $homecare->ID;
$suits = matrix_orlaith_permalink('service-users-and-visitors/service-user-it-support');
$remote = matrix_orlaith_permalink('what-you-need-to-know-about-remote-care');
$stay = matrix_orlaith_permalink('service-users-and-visitors/your-care-with-willow-grove/your-stay-in-hospital-as-an-adolescent');

$rows = get_field('flexible_content_blocks', $id);
$changed = 0;

$fix = static function (string $html) use ($suits, $remote, $stay, &$changed): string {
    $orig = $html;

    $html = preg_replace(
        '#https?://[^"\']*service-users-and-visitorshttps?://[^"\']*service-user-it-support/?#i',
        $suits,
        $html
    ) ?? $html;

    $html = preg_replace(
        '#href=(["\'])/service-user-it-support/?\1#i',
        'href=$1' . $suits . '$1',
        $html
    ) ?? $html;

    $html = preg_replace(
        '#href=(["\'])/practical-information-for-remote-services/?\1#i',
        'href=$1' . $remote . '$1',
        $html
    ) ?? $html;

    $html = preg_replace(
        '#href=(["\'])/your-stay-in-hospital-as-an-adolescent/?\1#i',
        'href=$1' . $stay . '$1',
        $html
    ) ?? $html;

    if ($html !== $orig) {
        $changed++;
    }

    return $html;
};

$walk = static function (&$node) use (&$walk, $fix): void {
    if (is_string($node)) {
        $node = $fix($node);
        return;
    }
    if (is_array($node)) {
        foreach ($node as &$child) {
            $walk($child);
        }
    }
};

$walk($rows);
update_field('flexible_content_blocks', $rows, $id);

$check = (string) wp_json_encode(get_field('flexible_content_blocks', $id));
echo "saved #$id changes=$changed\n";
echo 'suits_ok=' . (str_contains($check, $suits) ? 'yes' : 'no') . "\n";
echo 'mangled=' . (str_contains($check, 'visitorshttps') ? 'YES STILL' : 'no') . "\n";
echo "suits=$suits\n";
