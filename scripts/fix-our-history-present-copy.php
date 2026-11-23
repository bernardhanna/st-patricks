<?php
/**
 * Fix Our history "Our present and future" copy: Here in SPMHS → Here at SPMHS.
 *
 * Browser (local/staging only):
 *   /wp-content/themes/matrix-starter/scripts/fix-our-history-present-copy.php?key=fix-history-present
 */
if (! defined('ABSPATH')) {
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed_host = str_contains($host, 'localhost')
        || str_contains($host, 'matrix-test.com')
        || in_array($remote, ['127.0.0.1', '::1'], true);

    if (! $allowed_host || ($_GET['key'] ?? '') !== 'fix-history-present') {
        http_response_code(403);
        exit;
    }

    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

$log = [];
$post_id = (int) (get_page_by_path('about-us/our-history')?->ID ?? 0);
if ($post_id <= 0) {
    echo "MISS our-history\n";
    return;
}
$log[] = 'OK page #' . $post_id;

$rows = get_field('flexible_content_blocks', $post_id);
if (! is_array($rows) || $rows === []) {
    echo "FAIL no flexi\n";
    return;
}

$changed = 0;
foreach ($rows as &$row) {
    if (($row['acf_fc_layout'] ?? '') !== 'content') {
        continue;
    }
    if (strcasecmp(trim((string) ($row['heading'] ?? '')), 'Our present and future') !== 0) {
        continue;
    }

    foreach (['intro_text', 'content'] as $field) {
        $html = (string) ($row[$field] ?? '');
        if ($html === '') {
            continue;
        }

        $next = str_replace(
            [
                'Here in SPMHS',
                'Here in St Patrick’s Mental Health Services',
                'Here in St Patrick\'s Mental Health Services',
                'Here in St Patrick&rsquo;s Mental Health Services',
            ],
            'Here at SPMHS',
            $html
        );

        // Present/future section should use SPMHS only, not the full org name.
        $next = preg_replace(
            '/St Patrick(?:\'|’|&rsquo;)s Mental Health Services(\s*\(SPMHS\))?/u',
            'SPMHS',
            $next
        ) ?? $next;

        if ($next !== $html) {
            $row[$field] = $next;
            $changed++;
            $log[] = 'OK patched ' . $field;
        } else {
            $log[] = 'OK no change needed in ' . $field;
        }
    }
}
unset($row);

if ($changed > 0) {
    update_field('flexible_content_blocks', $rows, $post_id);
    clean_post_cache($post_id);
}

$log[] = 'DONE ' . get_permalink($post_id);
echo implode("\n", $log) . "\n";
