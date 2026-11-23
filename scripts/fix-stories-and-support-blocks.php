<?php
/**
 * Stories and support: Recovery stories → video_showcase, Shared experiences → testimonials.
 *
 *   /wp-content/themes/matrix-starter/scripts/fix-stories-and-support-blocks.php?key=fix-stories-blocks
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-stories-blocks') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

$post_id = (int) (get_page_by_path('service-users-and-visitors/stories-and-support')?->ID ?? 0);
if ($post_id < 1) {
    echo "MISSING PAGE\n";
    exit;
}

echo "page=#{$post_id}\n";

$rows = get_field('flexible_content_blocks', $post_id);
if (! is_array($rows)) {
    $rows = [];
}

echo "BEFORE\n";
foreach ($rows as $i => $row) {
    $layout = (string) ($row['acf_fc_layout'] ?? '?');
    $heading = wp_strip_all_tags((string) ($row['heading'] ?? $row['heading_text'] ?? ''));
    echo "[{$i}] {$layout} {$heading}\n";
}

$hero = null;
$share = null;
$newsletter = null;
$other = [];

foreach ($rows as $index => $row) {
    if (! is_array($row)) {
        continue;
    }
    $layout = (string) ($row['acf_fc_layout'] ?? '');
    $heading = strtolower(wp_strip_all_tags((string) ($row['heading'] ?? $row['heading_text'] ?? '')));

    if ($layout === 'hero_with_breadcrumbs' || ($index === 0 && $hero === null && str_contains($heading, 'stories and support'))) {
        $hero = $row;
        continue;
    }
    if (str_contains($heading, 'share your story')) {
        $share = $row;
        continue;
    }
    if ($layout === 'newsletter' || str_contains($heading, 'latest news')) {
        $newsletter = $row;
        continue;
    }
    if (str_contains($heading, 'recovery stories') || str_contains($heading, 'shared experiences')) {
        continue;
    }
    if (in_array($layout, ['video_showcase', 'testimonials'], true)) {
        continue;
    }
    $other[] = $row;
}

if ($hero === null) {
    $hero = matrix_orlaith_hero_row(
        'Stories and support',
        '<p>Hearing from people who have experienced mental health difficulties can offer hope, reassurance and encouragement. Here, you can explore stories shared by people who have accessed our services and learn more about their experiences of recovery and support.</p>'
    );
}

$video = matrix_orlaith_video_row(
    'Recovery stories',
    '<p>Watch videos from people who have chosen to share their experiences of mental health difficulties, treatment, and recovery.</p>',
    [
        [
            'url' => 'https://youtu.be/gbLilsK6yjs',
            'title' => "Padraig's story",
        ],
        [
            'url' => 'https://youtu.be/Vxca9a49vFw',
            'title' => "Charlotte's story",
        ],
        [
            'url' => 'https://youtu.be/EFZrl0PqyXY',
            'title' => "Sean's story",
        ],
    ]
);

$testimonials = [
    'acf_fc_layout' => 'testimonials',
    'heading_tag' => 'h2',
    'heading_text' => 'Shared experiences',
    'layout_style' => 'editorial_featured',
    'source_mode' => 'manual',
    'manual_items' => [
        [
            'quote' => '<p>“I never knew a place like St Patricks Mental Health Services existed. I am so grateful for the time I spent there. It allowed me space to be myself in my illness and to reassess my values in life and to learn how to live by them. I learned that its ok to not be ok. There is no shame, no judgment - just wonderfully kind empathetic staff be they in nursing, catering, household, craft rooms, therapy rooms, activity rooms, reception, hairdressing or maintenance. I felt so well cared for and supported throughout my time in St Patricks.”</p>',
            'author_name' => 'Previous service user',
            'author_title' => '',
            'card_tone' => 'lavender',
        ],
        [
            'quote' => '<p>“I think my whole experience with SPMHS was nothing short of exceptional. From the day I arrived until the day I left I was treated with the highest level of professionalism and courtesy. It has been life changing for me and my only regret is I did not go sooner.”</p>',
            'author_name' => 'Nigel C',
            'author_title' => 'Previous service user',
            'card_tone' => 'mauve',
        ],
        [
            'quote' => '<p>“I have been under the care of many other psychiatric hospitals and services. I must say that this hospital has the highest standard of care that I have experienced. The groups, activities and rooms (like the art room, pottery room, music room etc.) blew me away. This hospital is a place of recovery, as I have seen through my own and others experiences. Thank you so much for giving me this chance at recovery, it has made every difference in my life.”</p>',
            'author_name' => 'Previous service user',
            'author_title' => '',
            'card_tone' => 'lavender',
        ],
    ],
    'footer_action_mode' => 'none',
    'background_color' => '#F6EDE0',
];

if ($share === null) {
    $share = matrix_orlaith_content_row(
        'Share your story',
        '<p>We welcome hearing from people who would like to contribute their story to help inform, encourage and support others. If you are interested in sharing your experience, please get in touch with our team on <a href="mailto:communications@stpatricks.ie">communications@stpatricks.ie</a>.</p>',
        'white'
    );
}

$flexi = array_values(array_filter([
    $hero,
    $video,
    $testimonials,
    $share,
    ...$other,
    $newsletter,
]));

update_field('flexible_content_blocks', $flexi, $post_id);

echo "\nAFTER\n";
foreach ((array) get_field('flexible_content_blocks', $post_id) as $i => $row) {
    $layout = (string) ($row['acf_fc_layout'] ?? '?');
    $heading = wp_strip_all_tags((string) ($row['heading'] ?? $row['heading_text'] ?? ''));
    $extra = '';
    if ($layout === 'video_showcase') {
        $extra = ' slides=' . count($row['slides'] ?? []);
    }
    if ($layout === 'testimonials') {
        $extra = ' items=' . count($row['manual_items'] ?? []);
    }
    echo "[{$i}] {$layout} {$heading}{$extra}\n";
}

echo "OK " . get_permalink($post_id) . "\n";
