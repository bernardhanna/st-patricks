<?php
/**
 * Add communications@ email CTA above press releases on Media Queries.
 * Does not touch hero/nurse/hospital images or press release content.
 *
 * WP-CLI:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/seed-media-queries-email-cta.php
 *
 * Browser (local/staging only):
 *   /wp-content/themes/matrix-starter/scripts/seed-media-queries-email-cta.php?key=seed-media-email-cta
 */
if (! defined('ABSPATH')) {
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed_host = str_contains($host, 'localhost')
        || str_contains($host, 'matrix-test.com')
        || in_array($remote, ['127.0.0.1', '::1'], true);

    if (! $allowed_host || ($_GET['key'] ?? '') !== 'seed-media-email-cta') {
        http_response_code(403);
        exit;
    }

    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

$log = [];
$post = get_page_by_path('about-us/media-queries');
if (! $post instanceof WP_Post) {
    $log[] = 'MISS media-queries page';
    echo implode("\n", $log) . "\n";
    return;
}

$post_id = (int) $post->ID;
$log[] = 'OK page #' . $post_id;

$cta_marker = 'matrix-media-queries-email-cta';
$cta_html = '<p data-matrix-seed="' . esc_attr($cta_marker) . '">'
    . 'If you have any media queries or would like to arrange an interview with a spokesperson, email '
    . '<a href="mailto:communications@stpatricks.ie">communications@stpatricks.ie</a>.'
    . '</p>';

$cta_row = matrix_orlaith_content_row(
    '',
    '',
    'light_blue',
    0,
    'image_left',
    [
        'intro_text' => $cta_html,
        'content' => '',
        'accent_position' => 'none',
        'vertical_padding' => 'medium',
    ]
);

$rows = get_field('flexible_content_blocks', $post_id);
if (! is_array($rows) || $rows === []) {
    $log[] = 'FAIL no flexible_content_blocks';
    echo implode("\n", $log) . "\n";
    return;
}

$press_index = null;
$existing_cta_index = null;

foreach ($rows as $i => $row) {
    $layout = (string) ($row['acf_fc_layout'] ?? '');
    $blob = wp_json_encode($row) ?: '';

    if (str_contains($blob, $cta_marker)
        || (str_contains($blob, 'communications@stpatricks.ie')
            && str_contains($blob, 'arrange an interview'))) {
        $existing_cta_index = (int) $i;
    }

    if ($press_index === null && (
        $layout === 'latest_posts'
        || str_contains(strtolower((string) ($row['heading'] ?? '')), 'press release')
    )) {
        $press_index = (int) $i;
    }
}

if ($existing_cta_index !== null) {
    $rows[$existing_cta_index] = $cta_row;
    $log[] = 'OK updated existing CTA at index ' . $existing_cta_index;
} elseif ($press_index !== null) {
    array_splice($rows, $press_index, 0, [$cta_row]);
    $log[] = 'OK inserted CTA before press releases at index ' . $press_index;
} else {
    $rows[] = $cta_row;
    $log[] = 'OK appended CTA (no press releases block found)';
}

update_field('flexible_content_blocks', $rows, $post_id);
clean_post_cache($post_id);

$log[] = 'OK hub updated ' . get_permalink($post_id);
echo implode("\n", $log) . "\n";
