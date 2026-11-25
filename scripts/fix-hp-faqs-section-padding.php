<?php
/**
 * Tighten Healthcare Professionals FAQ page inter-section padding.
 *
 * Sets faqs + trailing content flexi blocks to vertical_padding=medium
 * (60px desktop). Leaves the hero on its existing compact spacing.
 *
 * HTTP (staging/local):
 *   /wp-content/themes/matrix-starter/scripts/fix-hp-faqs-section-padding.php?key=fix-hp-faqs-pad
 *
 * CLI:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/fix-hp-faqs-section-padding.php
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-hp-faqs-pad') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

$post = get_page_by_path('healthcare-professionals/frequently-asked-questions');

if (! $post instanceof WP_Post) {
    echo "FAIL page not found\n";
    exit(1);
}

$post_id = (int) $post->ID;
$rows = get_field('flexible_content_blocks', $post_id);

if (! is_array($rows) || $rows === []) {
    echo "FAIL no flexi rows on #{$post_id}\n";
    exit(1);
}

$target_layouts = ['faqs', 'content'];
$updated = [];

foreach ($rows as $index => $row) {
    if (! is_array($row)) {
        continue;
    }

    $layout = (string) ($row['acf_fc_layout'] ?? '');

    if (! in_array($layout, $target_layouts, true)) {
        continue;
    }

    $before = (string) ($row['vertical_padding'] ?? 'default');
    $rows[$index]['vertical_padding'] = 'medium';
    $rows[$index]['padding_settings'] = [];
    $updated[] = "{$layout}[#{$index}]:{$before}->medium";
}

if ($updated === []) {
    echo "FAIL no faqs/content rows on #{$post_id}\n";
    exit(1);
}

update_field('flexible_content_blocks', $rows, $post_id);
clean_post_cache($post_id);

echo 'OK HP FAQs #' . $post_id . ': ' . implode(', ', $updated) . "\n";
