<?php
/**
 * Wire "Learn about mental health difficulties" labels to condition pages.
 *
 * HTTP (staging/local):
 *   /wp-content/themes/matrix-starter/scripts/fix-about-mental-health-difficulty-links.php?key=fix-amh-difficulty-links
 *   Add &dry=1 to inspect without writing.
 *
 * CLI:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/fix-about-mental-health-difficulty-links.php
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-amh-difficulty-links') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

require_once get_template_directory() . '/inc/mental-health-functions.php';

$dry = isset($_GET['dry']) || (defined('WP_CLI') && WP_CLI && in_array('--dry', $_SERVER['argv'] ?? [], true));

$post = get_page_by_path('service-users-and-visitors/about-mental-health');
if (! $post instanceof WP_Post) {
    $post = get_page_by_path('about-mental-health');
}

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

/**
 * Label (as shown on page) => mental_health CPT slug.
 *
 * @var array<string, string>
 */
$label_to_slug = [
    'Addiction and dual diagnosis' => 'addiction-dual-diagnosis',
    'Anxiety' => 'anxiety',
    'Bipolar disorder' => 'bipolar-disorder',
    'Depression' => 'depression',
    'Eating disorders' => 'eating-disorders',
    'Personality disorder' => 'personality-disorders',
    'Personality disorders' => 'personality-disorders',
    'Schizophrenia and psychosis' => 'schizophrenia-psychosis',
    'Mental health difficulties in young adults' => 'young-adults',
];

$target_heading = 'Learn about mental health difficulties';
$section_index = null;

foreach ($rows as $index => $row) {
    if (! is_array($row) || ($row['acf_fc_layout'] ?? '') !== 'content') {
        continue;
    }

    $heading = trim(wp_strip_all_tags((string) ($row['heading'] ?? '')));
    if (strcasecmp($heading, $target_heading) === 0) {
        $section_index = $index;
        break;
    }
}

if ($section_index === null) {
    echo "FAIL no content block titled \"{$target_heading}\" on #{$post_id}\n";
    exit(1);
}

$before_html = (string) ($rows[$section_index]['content'] ?? '');
$linked = [];
$missing = [];
$paragraphs = [];

foreach ($label_to_slug as $label => $slug) {
    // Prefer unique labels; Personality disorders alias shares the slug.
    if ($label === 'Personality disorders') {
        continue;
    }

    $url = function_exists('matrix_mental_health_condition_url')
        ? matrix_mental_health_condition_url($slug)
        : home_url('/mental-health/' . $slug . '/');

    $exists = get_posts([
        'post_type' => 'mental_health',
        'name' => $slug,
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'fields' => 'ids',
    ]);

    if ($exists === []) {
        $missing[] = "{$label} → /mental-health/{$slug}/ (no publish CPT)";
        // Keep label as plain text rather than a dead link.
        $paragraphs[] = '<p>' . esc_html($label) . '</p>';
        continue;
    }

    $linked[] = "{$label} → {$url}";
    $paragraphs[] = '<p><a href="' . esc_url($url) . '">' . esc_html($label) . '</a></p>';
}

$after_html = implode("\n", $paragraphs);

$rows[$section_index]['content'] = $after_html;
// Misplaced leftover from an older anxiety-focused seed — not part of this list.
$rows[$section_index]['primary_button'] = '';
$rows[$section_index]['primary_button_variant'] = '';

echo ($dry ? "DRY RUN\n" : "APPLY\n");
echo "page=#{$post_id} " . get_permalink($post_id) . "\n";
echo "section_index={$section_index}\n";
echo "BEFORE:\n{$before_html}\n";
echo "AFTER:\n{$after_html}\n";
echo "LINKS:\n" . implode("\n", $linked) . "\n";

if ($missing !== []) {
    echo "MISSING:\n" . implode("\n", $missing) . "\n";
}

if ($dry) {
    echo "DONE (dry)\n";
    exit(0);
}

update_field('flexible_content_blocks', $rows, $post_id);
clean_post_cache($post_id);

echo "SAVED\n";
echo "DONE\n";
