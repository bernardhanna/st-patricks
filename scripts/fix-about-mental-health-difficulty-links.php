<?php
/**
 * Replace "Learn about mental health difficulties" text links with an
 * about_links_grid (thumb + title cards) linking to each condition page.
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
require_once get_template_directory() . '/inc/migrate-functions.php';
require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

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
 * Same label set as the About mental health list, with hub thumbnail paths.
 *
 * @var list<array{title:string,slug:string,image:string,tone:string}>
 */
$cards_def = [
    [
        'title' => 'Addiction and dual diagnosis',
        'slug' => 'addiction-dual-diagnosis',
        'image' => '/media/1639/personality-disorder.jpeg',
        'tone' => 'bg1',
    ],
    [
        'title' => 'Anxiety',
        'slug' => 'anxiety',
        'image' => '/media/3279/anxiety-featured-image.png',
        'tone' => 'bg1',
    ],
    [
        'title' => 'Bipolar disorder',
        'slug' => 'bipolar-disorder',
        'image' => '/media/3263/bipolar-disorder-featured-image.png',
        'tone' => 'bg1',
    ],
    [
        'title' => 'Depression',
        'slug' => 'depression',
        'image' => '/media/3264/depression-featured-image.jpg',
        'tone' => 'bg2',
    ],
    [
        'title' => 'Eating disorders',
        'slug' => 'eating-disorders',
        'image' => '/media/1863/eating-disorders-causes-characteristics-common-misconceptions.jpg',
        'tone' => 'bg2',
    ],
    [
        'title' => 'Personality disorder',
        'slug' => 'personality-disorders',
        'image' => '/media/2113/what-are-personality-disorders.jpg',
        'tone' => 'bg2',
    ],
    [
        'title' => 'Schizophrenia and psychosis',
        'slug' => 'schizophrenia-psychosis',
        'image' => '/media/3292/still-just-me-psychosis-featured-image.jpg',
        'tone' => 'bg3',
    ],
    [
        'title' => 'Mental health difficulties in young adults',
        'slug' => 'young-adults',
        'image' => '/media/1662/adobestock_88353473.jpeg',
        'tone' => 'bg3',
    ],
];

$image_url_for = static function (string $source_path): string {
    if (function_exists('matrix_migrate_attachment_id_for_source_path')) {
        $attachment_id = matrix_migrate_attachment_id_for_source_path($source_path);
        if ($attachment_id > 0) {
            $url = wp_get_attachment_url($attachment_id);
            if (is_string($url) && $url !== '') {
                return $url;
            }
        }
    }

    if (function_exists('matrix_migrate_live_url')) {
        return (string) matrix_migrate_live_url($source_path);
    }

    return '';
};

$target_heading = 'Learn about mental health difficulties';
$section_index = null;

foreach ($rows as $index => $row) {
    if (! is_array($row)) {
        continue;
    }

    $layout = (string) ($row['acf_fc_layout'] ?? '');
    $heading = '';
    if ($layout === 'content') {
        $heading = trim(wp_strip_all_tags((string) ($row['heading'] ?? '')));
    } elseif ($layout === 'about_links_grid') {
        $heading = trim(wp_strip_all_tags((string) ($row['heading_text'] ?? '')));
    }

    if (strcasecmp($heading, $target_heading) === 0) {
        $section_index = $index;
        break;
    }
}

if ($section_index === null) {
    echo "FAIL no block titled \"{$target_heading}\" on #{$post_id}\n";
    exit(1);
}

$existing = $rows[$section_index];
$links = [];
$linked = [];
$missing = [];

foreach ($cards_def as $card) {
    $slug = $card['slug'];
    $title = $card['title'];
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

    $image_url = $image_url_for($card['image']);
    if ($image_url === '') {
        $missing[] = "{$title}: no image for {$card['image']}";
    }

    if ($exists === []) {
        $missing[] = "{$title} → /mental-health/{$slug}/ (no publish CPT)";
    }

    $linked[] = "{$title} → {$url} | img=" . ($image_url !== '' ? 'yes' : 'no');

    $links[] = [
        'icon' => '',
        'image_url' => $image_url,
        'title' => $title,
        'description' => '',
        'link' => [
            'title' => $title,
            'url' => $url,
            'target' => '',
        ],
        'card_tone' => $card['tone'],
    ];
}

$grid = matrix_orlaith_about_links_grid_row($target_heading, $links, [
    'bg_color' => '#FFFFFF',
    'heading_color' => '#08284B',
    'intro_color' => '#08284B',
    'card_bg_color' => '#F1F8F9',
    'columns' => '4',
    'layout_style' => 'flush_image',
    'allow_title_wrap' => 1,
    'vertical_padding' => $existing['vertical_padding'] ?? 'default',
    'padding_settings' => $existing['padding_settings'] ?? [],
]);

$rows[$section_index] = $grid;

echo ($dry ? "DRY RUN\n" : "APPLY\n");
echo "page=#{$post_id} " . get_permalink($post_id) . "\n";
echo "section_index={$section_index}\n";
echo "layout=about_links_grid flush_image columns=4 cards=" . count($links) . "\n";
echo "LINKS:\n" . implode("\n", $linked) . "\n";

if ($missing !== []) {
    echo "WARN:\n" . implode("\n", $missing) . "\n";
}

if ($dry) {
    echo "DONE (dry)\n";
    exit(0);
}

update_field('flexible_content_blocks', $rows, $post_id);
clean_post_cache($post_id);

echo "SAVED\n";
echo "DONE\n";
