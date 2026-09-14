<?php

/**
 * Rebuild Training Centre from Drive Library 3 (About Us).
 *
 * Sources:
 * - Training Centre page (About Us).docx
 * - Image for training centre page (already in media library).docx
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/rebuild-training-centre-from-drive.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

$post_id = (int) (get_page_by_path('healthcare-professionals/training-centre')?->ID ?? 0);
if ($post_id === 0) {
    WP_CLI::error('Could not find healthcare-professionals/training-centre');
}

$p = static function (string $html): string {
    return '<p>' . $html . '</p>';
};

$img_id = matrix_orlaith_find_image(1911, 'future-in-mind-research-training-poster.jpg');
if ($img_id <= 0) {
    $img_id = matrix_orlaith_find_image(4091, 'Research and Training Video.png');
}
if ($img_id <= 0) {
    $img_id = (int) get_post_thumbnail_id($post_id);
}

$poster = matrix_orlaith_find_image(4091, 'Research and Training Video.png');
if ($poster <= 0) {
    $poster = $img_id;
}

$hero_intro = $p("St Patrick's Mental Health Services is establishing a dedicated Training Centre to advance the skills and competencies of those working in the mental healthcare sector.");

$body = $p('We will expand our training for mental health professionals through the Training Centre. We will also further develop comprehensive continuing professional development (CPD) programmes for people working in mental healthcare and the organisations providing mental health services.')
    . $p('Our Training Centre will strengthen our existing training partnerships, while also creating new opportunities for the skills development of mental health professionals throughout Ireland.');

$flexi = [
    matrix_orlaith_hero_row('Training Centre', $hero_intro, $img_id),
    matrix_orlaith_content_row('', $body, 'white'),
    matrix_orlaith_video_row(
        'Research and training',
        $p('Watch how our Academic Institute and Training Centre support staff and organisations working in mental health.'),
        [[
            'url' => 'https://www.youtube.com/watch?v=AjJQxOrmv1o',
            'caption' => 'Learn more about our Academic Institute and our commitment to supporting staff and organisations working in mental health through our new training centre.',
            'poster' => $poster,
        ]]
    ),
];

wp_update_post([
    'ID' => $post_id,
    'post_title' => 'Training Centre',
]);

update_field('flexible_content_blocks', $flexi, $post_id);
if ($img_id > 0) {
    set_post_thumbnail($post_id, $img_id);
}

WP_CLI::success(sprintf(
    'Rebuilt Training Centre #%d → %s',
    $post_id,
    get_permalink($post_id)
));

foreach ((array) get_field('flexible_content_blocks', $post_id) as $i => $row) {
    $layout = (string) ($row['acf_fc_layout'] ?? '?');
    $heading = wp_strip_all_tags((string) ($row['heading'] ?? ''));
    $extra = '';
    if ($layout === 'video_showcase' && ! empty($row['slides'])) {
        $extra = ' | slides=' . count($row['slides']);
    }
    WP_CLI::log("[{$i}] {$layout} {$heading}{$extra}");
}
