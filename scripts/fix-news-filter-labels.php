<?php
/**
 * Sentence-case news filter labels (Blogs and articles, Press releases).
 *
 *   /wp-content/themes/matrix-starter/scripts/fix-news-filter-labels.php?key=fix-news-filters
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-news-filters') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

$map = [
    'blog' => 'Blogs and articles',
    'press-releases' => 'Press releases',
];

foreach ($map as $slug => $name) {
    $term = get_term_by('slug', $slug, 'category');
    if (! $term instanceof WP_Term) {
        echo "missing $slug\n";
        continue;
    }
    if ($term->name === $name) {
        echo "ok #$term->term_id $name\n";
        continue;
    }
    $r = wp_update_term((int) $term->term_id, 'category', ['name' => $name]);
    echo (is_wp_error($r) ? 'ERR ' . $r->get_error_message() : "updated #$term->term_id → $name") . "\n";
}
