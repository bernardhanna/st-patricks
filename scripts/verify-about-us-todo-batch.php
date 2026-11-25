<?php
/**
 * Staging/local evidence dump for About Us To-do snags.
 *
 *   /wp-content/themes/matrix-starter/scripts/verify-about-us-todo-batch.php?key=verify-about-todo
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'verify-about-todo') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

$paths = [
    'support' => 'about-us/support-us',
    'team' => 'about-us/our-team',
    'careers' => 'about-us/careers',
    'research' => 'about-us/research',
    'rec' => 'about-us/research/research-ethics-committee',
    'wellbeing' => 'about-us/careers/recruitment-and-useful-information/staff-wellbeing',
];

foreach ($paths as $label => $path) {
    $post = get_page_by_path($path);
    if (! $post instanceof WP_Post && $label === 'rec') {
        $post = get_page_by_path('research-ethics-committee');
    }
    if (! $post instanceof WP_Post && $label === 'wellbeing') {
        $post = get_page_by_path('recruitment-and-useful-information/staff-wellbeing');
    }
    echo "===== {$label} /{$path}/ =====\n";
    if (! $post instanceof WP_Post) {
        echo "MISS page\n\n";
        continue;
    }
    echo 'ID=' . $post->ID . ' title=' . $post->post_title . "\n";
    $html = (string) apply_filters('the_content', '');
    // Render via HTTP-less: dump flexi facts instead.
    $rows = get_field('flexible_content_blocks', $post->ID);
    if (! is_array($rows)) {
        echo "no flexi\n\n";
        continue;
    }
    foreach ($rows as $i => $row) {
        $layout = (string) ($row['acf_fc_layout'] ?? '');
        $heading = trim((string) ($row['heading'] ?? $row['heading_text'] ?? ''));
        echo "[{$i}] {$layout} | {$heading}\n";
        if ($layout === 'about_links_grid' || $layout === 'multidisciplinary_team_grid' || $layout === 'research_cards_grid') {
            $items = $row['links'] ?? $row['cards'] ?? [];
            if (is_array($items)) {
                foreach ($items as $item) {
                    $t = (string) ($item['title'] ?? '');
                    $u = (string) ($item['url'] ?? '');
                    if ($u === '' && is_array($item['link'] ?? null)) {
                        $u = (string) ($item['link']['url'] ?? '');
                    }
                    echo "  - {$t} -> {$u}\n";
                }
            }
        }
        if ($layout === 'content' && (stripos($heading, 'fundraising') !== false || stripos($heading, 'Why work') !== false || stripos($heading, 'Role of the REC') !== false || stripos($heading, 'workplace culture') !== false)) {
            $content = (string) ($row['content'] ?? '');
            if (preg_match_all('#<a[^>]+href=["\']([^"\']+)["\'][^>]*>(.*?)</a>#is', $content, $m, PREG_SET_ORDER)) {
                foreach ($m as $mm) {
                    echo '  a ' . wp_strip_all_tags($mm[2]) . ' -> ' . $mm[1] . "\n";
                }
            }
            if (stripos($content, 'Here at') !== false) {
                if (preg_match('/Here at[^.<]{0,80}/', wp_strip_all_tags($content), $mm)) {
                    echo '  sentence: ' . $mm[0] . "\n";
                }
            }
            if (stripos($content, 'Culture to Value') !== false) {
                echo "  has Culture to Value\n";
            }
        }
        if ($layout === 'team_members' && stripos($heading, 'REC') !== false) {
            echo "  WARN REC headshots still present\n";
        }
    }
    echo "\n";
}
