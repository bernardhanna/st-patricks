<?php
/**
 * Rename footer ACF legal_links labels.
 *
 *   /wp-content/themes/matrix-starter/scripts/fix-review2-footer-labels.php?key=fix-foot1
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-foot1') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

if (! function_exists('get_field') || ! function_exists('update_field')) {
    echo "no ACF\n";
    exit;
}

$rows = get_field('legal_links', 'option');
if (! is_array($rows)) {
    echo "no legal_links\n";
    exit;
}

$changed = 0;
foreach ($rows as &$row) {
    $link = $row['link'] ?? null;
    if (! is_array($link)) {
        echo 'row keys=' . implode(',', array_keys($row)) . "\n";
        continue;
    }
    $title = (string) ($link['title'] ?? '');
    $url = (string) ($link['url'] ?? '');
    echo "before title=[{$title}] url={$url}\n";

    $new = null;
    if (preg_match('/cookie/i', $title) && preg_match('/privacy/i', $title)) {
        $new = 'Cookie policy';
    } elseif (preg_match('/data protection policy/i', $title)) {
        $new = 'Data Protection Statement';
    } elseif (preg_match('/cookie/i', $url) && preg_match('/privacy|cookie/i', $title)) {
        $new = 'Cookie policy';
    } elseif (preg_match('/data-protection/i', $url)) {
        $new = 'Data Protection Statement';
    }

    if ($new !== null && $new !== $title) {
        $row['link']['title'] = $new;
        $changed++;
        echo "  -> {$new}\n";
    }
}
unset($row);

if ($changed > 0) {
    update_field('legal_links', $rows, 'option');
    wp_cache_flush();
}

echo "changed={$changed}\nDONE\n";
