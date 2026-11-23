<?php
/**
 * Close remaining Advocacy snag link/copy gaps on about-us/advocacy.
 *
 * - Hyperlink Youth Empowerment Service
 * - Hyperlink "Learn more and join the network here"
 * - Ensure Child Care in Practice uses the correct journal URL (cccp20)
 *
 * Browser (local/staging only):
 *   /wp-content/themes/matrix-starter/scripts/fix-advocacy-snag-links.php?key=fix-advocacy-snag
 */
if (! defined('ABSPATH')) {
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed_host = str_contains($host, 'localhost')
        || str_contains($host, 'matrix-test.com')
        || in_array($remote, ['127.0.0.1', '::1'], true);

    if (! $allowed_host || ($_GET['key'] ?? '') !== 'fix-advocacy-snag') {
        http_response_code(403);
        exit;
    }

    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

$log = [];
$post_id = (int) (get_page_by_path('about-us/advocacy')?->ID ?? 0);
if ($post_id <= 0) {
    echo "MISS advocacy page\n";
    return;
}
$log[] = 'OK page #' . $post_id;

$youth_url = get_permalink((int) (get_page_by_path('about-us/advocacy/youth-advocacy-service')?->ID ?? 0))
    ?: home_url('/about-us/advocacy/youth-advocacy-service/');
$wmhn_url = get_permalink((int) (get_page_by_path('about-us/advocacy/women-s-mental-health-network')?->ID ?? 0))
    ?: home_url('/about-us/advocacy/women-s-mental-health-network/');
$ccip_url = 'https://www.tandfonline.com/journals/cccp20';

$rows = get_field('flexible_content_blocks', $post_id);
if (! is_array($rows) || $rows === []) {
    echo "FAIL no flexi\n";
    return;
}

$changed = 0;
$patch_html = static function (string $html) use ($youth_url, $wmhn_url, $ccip_url, &$changed): string {
    $before = $html;

    // Youth Empowerment Service plain text → linked.
    if (str_contains($html, 'Youth Empowerment Service') && ! str_contains($html, '>' . 'Youth Empowerment Service' . '</a>')) {
        $html = preg_replace(
            '/(?<![\">])Youth Empowerment Service(?!<\\/a>)/',
            '<a href="' . esc_url($youth_url) . '">Youth Empowerment Service</a>',
            $html,
            1
        ) ?? $html;
    }

    // Women's network join CTA.
    if (str_contains($html, 'Learn more and join the network here') && ! str_contains($html, 'join the network here</a>')) {
        $html = str_replace(
            'Learn more and join the network here.',
            '<a href="' . esc_url($wmhn_url) . '">Learn more and join the network here</a>.',
            $html
        );
        $html = str_replace(
            'Learn more and join the network here</p>',
            '<a href="' . esc_url($wmhn_url) . '">Learn more and join the network here</a></p>',
            $html
        );
    }

    // Wrong / old Child Care journal slug → correct cccp20 journal.
    $html = str_replace(
        [
            'https://www.tandfonline.com/journals/ccpp20',
            'http://www.tandfonline.com/journals/ccpp20',
        ],
        $ccip_url,
        $html
    );

    if ($html !== $before) {
        $changed++;
    }

    return $html;
};

foreach ($rows as &$row) {
    $layout = (string) ($row['acf_fc_layout'] ?? '');

    if ($layout === 'hero_with_breadcrumbs' || $layout === 'content') {
        foreach (['content', 'intro_text'] as $field) {
            if (! empty($row[$field]) && is_string($row[$field])) {
                $row[$field] = $patch_html($row[$field]);
            }
        }
    }

    if ($layout === 'content_accordion' && ! empty($row['items']) && is_array($row['items'])) {
        foreach ($row['items'] as &$item) {
            if (! empty($item['content_rows']) && is_array($item['content_rows'])) {
                foreach ($item['content_rows'] as &$content_row) {
                    if (! empty($content_row['content']) && is_string($content_row['content'])) {
                        $content_row['content'] = $patch_html($content_row['content']);
                    }
                }
                unset($content_row);
            }
            if (! empty($item['content']) && is_string($item['content'])) {
                $item['content'] = $patch_html($item['content']);
            }
        }
        unset($item);
    }
}
unset($row);

if ($changed > 0) {
    update_field('flexible_content_blocks', $rows, $post_id);
    clean_post_cache($post_id);
    $log[] = 'OK patched fields=' . $changed;
} else {
    $log[] = 'OK no HTML changes needed';
}

$log[] = 'OK youth ' . $youth_url;
$log[] = 'OK wmhn ' . $wmhn_url;
$log[] = 'DONE ' . get_permalink($post_id);
echo implode("\n", $log) . "\n";
