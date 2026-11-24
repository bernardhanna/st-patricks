<?php
/**
 * Locate where day-programmes SUITS email copy lives on staging.
 *
 *   /wp-content/themes/matrix-starter/scripts/fix-review2-suits-find.php?key=fix-suits3
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-suits3') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

global $wpdb;

$needles = [
    'suits@stpatricks.ie',
    'suits&#64;stpatricks.ie',
    '01 249 3629 or by emailing',
    'Service User IT Support (SUITS) team',
];

foreach ($needles as $needle) {
    $like = '%' . $wpdb->esc_like($needle) . '%';
    $meta = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_value LIKE %s",
        $like
    ));
    $posts = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_content LIKE %s",
        $like
    ));
    $opts = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_value LIKE %s",
        $like
    ));
    echo "needle=[{$needle}] meta={$meta} posts={$posts} options={$opts}\n";
}

// Dump FAQ relationship / accordion fields on day programmes page
$page = get_page_by_path('service-users-and-visitors/attending-day-programmes');
if ($page) {
    echo "page={$page->ID}\n";
    $rows = function_exists('get_field') ? get_field('flexible_content_blocks', $page->ID) : null;
    if (is_array($rows)) {
        foreach ($rows as $i => $row) {
            $layout = $row['acf_fc_layout'] ?? '?';
            echo "block {$i} layout={$layout}\n";
            $json = wp_json_encode($row);
            if (is_string($json) && str_contains($json, 'suits')) {
                echo "  contains suits in json\n";
                if (preg_match('/.{0,80}suits.{0,80}/', $json, $m)) {
                    echo '  ' . $m[0] . "\n";
                }
            }
            // FAQ ids
            foreach (['faqs', 'faq_items', 'selected_faqs', 'items', 'accordion_items'] as $k) {
                if (! empty($row[$k])) {
                    echo "  key={$k} type=" . gettype($row[$k]) . ' sample=' . substr(wp_json_encode($row[$k]), 0, 300) . "\n";
                }
            }
        }
    }

    // All meta keys containing FAQ
    $all = $wpdb->get_results($wpdb->prepare(
        "SELECT meta_key, LEFT(meta_value,120) v FROM {$wpdb->postmeta} WHERE post_id=%d AND (meta_key LIKE %s OR meta_value LIKE %s) LIMIT 40",
        $page->ID,
        '%faq%',
        '%SUITS%'
    ));
    foreach ($all as $r) {
        echo "meta {$r->meta_key} => {$r->v}\n";
    }
}

// Direct FAQ IDs from earlier REST
foreach ([4131, 4141, 4187] as $id) {
    $p = get_post($id);
    if (! $p) {
        echo "faq {$id} missing\n";
        continue;
    }
    $has = str_contains((string) $p->post_content, 'suits@') ? 'yes' : 'no';
    $mailto = str_contains((string) $p->post_content, 'mailto:suits') ? 'yes' : 'no';
    echo "faq {$id} slug={$p->post_name} suits={$has} mailto={$mailto}\n";
    if ($has === 'yes' && preg_match('/.{0,60}suits@.{0,40}/', $p->post_content, $m)) {
        echo '  ' . $m[0] . "\n";
    }
    // ACF answer field variants
    if (function_exists('get_fields')) {
        $fields = get_fields($id);
        if (is_array($fields)) {
            $j = wp_json_encode($fields);
            if (is_string($j) && str_contains($j, 'suits')) {
                echo "  acf contains suits\n";
                if (preg_match('/.{0,80}suits.{0,80}/', $j, $m)) {
                    echo '  ' . $m[0] . "\n";
                }
            }
        }
    }
}

echo "DONE\n";
