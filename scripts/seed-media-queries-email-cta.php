<?php
/**
 * Add communications@ email CTA above press releases on Media Queries.
 * Does not touch hero/nurse/hospital images or press release assets.
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
$cta_body = '<p data-matrix-seed="' . esc_attr($cta_marker) . '"><strong>'
    . 'If you have any media queries or would like to arrange an interview with a spokesperson, email '
    . '<a href="mailto:communications@stpatricks.ie">communications@stpatricks.ie</a>.'
    . '</strong></p>';

// Use a standard content block (heading + content) so it always renders.
$cta_row = [
    'acf_fc_layout' => 'content',
    'heading' => 'Media enquiries',
    'heading_tag' => 'h2',
    'accent_position' => 'below_heading',
    'intro_text' => '',
    'content' => $cta_body,
    'background_type' => 'light_blue',
    'color_scheme' => 'default',
    'column_layout' => 'one_column',
    'layout_style' => 'image_left',
    'image_height_mode' => 'match_text',
    'text_width' => 'full',
    'image' => '',
    'vertical_padding' => 'medium',
    'primary_button' => [
        'title' => 'Email communications@stpatricks.ie',
        'url' => 'mailto:communications@stpatricks.ie',
        'target' => '',
    ],
];
$rows = get_field('flexible_content_blocks', $post_id);
if (! is_array($rows) || $rows === []) {
    $log[] = 'FAIL no flexible_content_blocks';
    echo implode("\n", $log) . "\n";
    return;
}

$press_index = null;
$existing_indexes = [];

foreach ($rows as $i => $row) {
    $layout = (string) ($row['acf_fc_layout'] ?? '');
    $blob = wp_json_encode($row) ?: '';

    $is_our_cta = str_contains($blob, $cta_marker)
        || (
            in_array($layout, ['content', 'content_cta'], true)
            && str_contains($blob, 'communications@stpatricks.ie')
            && str_contains($blob, 'arrange an interview')
        )
        || strcasecmp((string) ($row['heading'] ?? ''), 'Media enquiries') === 0;

    $is_empty_stub = $layout === 'content'
        && trim((string) ($row['heading'] ?? '')) === ''
        && trim(strip_tags((string) ($row['intro_text'] ?? ''))) === ''
        && trim(strip_tags((string) ($row['content'] ?? ''))) === ''
        && empty($row['image']);

    if ($is_our_cta || $is_empty_stub) {
        $existing_indexes[] = (int) $i;
    }

    if ($press_index === null && (
        $layout === 'latest_posts'
        || str_contains(strtolower((string) ($row['heading'] ?? '')), 'press release')
    )) {
        $press_index = (int) $i;
    }
}

// Drop previous CTA / empty stub rows (highest index first).
rsort($existing_indexes);
foreach ($existing_indexes as $idx) {
    array_splice($rows, $idx, 1);
    $log[] = 'OK removed previous row at index ' . $idx;
    if ($press_index !== null && $idx < $press_index) {
        $press_index--;
    }
}

if ($press_index !== null) {
    array_splice($rows, $press_index, 0, [$cta_row]);
    $log[] = 'OK inserted CTA before press releases at index ' . $press_index;
} else {
    $rows[] = $cta_row;
    $log[] = 'OK appended CTA (no press releases block found)';
}

update_field('flexible_content_blocks', $rows, $post_id);
clean_post_cache($post_id);

$verify = get_field('flexible_content_blocks', $post_id);
if (! is_array($verify)) {
    $log[] = 'FAIL verify read empty';
} else {
    foreach ($verify as $i => $row) {
        $layout = (string) ($row['acf_fc_layout'] ?? '?');
        $heading = trim((string) ($row['heading'] ?? ''));
        $has_mail = str_contains(wp_json_encode($row) ?: '', 'communications@stpatricks.ie');
        $log[] = sprintf('ROW %d %s heading=%s mail=%s', $i, $layout, $heading !== '' ? $heading : '-', $has_mail ? 'yes' : 'no');
    }
    $verify_blob = wp_json_encode($verify) ?: '';
    $log[] = str_contains($verify_blob, 'communications@stpatricks.ie')
        ? 'OK saved email in flexi'
        : 'FAIL email missing after save';
}
$log[] = 'OK hub updated ' . get_permalink($post_id);
echo implode("\n", $log) . "\n";
