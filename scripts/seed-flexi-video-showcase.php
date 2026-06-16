<?php

/**
 * Fix /flexi/ video demos: use video_showcase with the real SPMHS YouTube video,
 * and keep story_slider as an image gallery only.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-flexi-video-showcase.php
 */

$post_id = 329;

$spmh_video_url = 'https://www.youtube.com/watch?v=mN0Qyhix71E';
$placeholder_video_url = 'https://www.youtube.com/watch?v=ysz5S6PUM-U';

if (! function_exists('matrix_seed_build_image_field')) {
    function matrix_seed_build_image_field(int $attachment_id, string $alt): array
    {
        if ($attachment_id <= 0) {
            return [];
        }

        return [
            'ID' => $attachment_id,
            'url' => wp_get_attachment_url($attachment_id),
            'alt' => $alt,
            'title' => $alt,
        ];
    }
}

$poster_id = 0;
$poster_candidates = get_posts([
    'post_type' => 'attachment',
    'post_status' => 'inherit',
    'posts_per_page' => 1,
    'meta_query' => [
        [
            'key' => '_matrix_seed_figma_key',
            'value' => 'overview-video-poster',
        ],
    ],
]);

if ($poster_candidates !== []) {
    $poster_id = (int) $poster_candidates[0]->ID;
}

if ($poster_id <= 0) {
    $fallback = get_posts([
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => 1,
        'post_mime_type' => 'image',
        'orderby' => 'ID',
        'order' => 'DESC',
    ]);
    $poster_id = $fallback !== [] ? (int) $fallback[0]->ID : 0;
}

$rows = get_field('flexible_content_blocks', $post_id);
if (! is_array($rows)) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('No flexi rows found on page ' . $post_id);
    }

    exit(1);
}

$video_showcase_updated = 0;
$story_slider_updated = 0;

foreach ($rows as $index => $row) {
    $layout = $row['acf_fc_layout'] ?? '';

    if ($layout === 'video_showcase') {
        $heading = trim((string) ($row['heading'] ?? ''));

        if ($heading === 'Featured video' || $heading === '') {
            $rows[$index]['heading'] = "St Patrick's Mental Health Services 1746–2016";
        }

        $slides = is_array($row['slides'] ?? null) ? $row['slides'] : [];

        foreach ($slides as $slide_index => $slide) {
            if (! is_array($slide)) {
                continue;
            }

            $embed = trim((string) ($slide['video_embed_url'] ?? ''));

            if ($embed === '' || $embed === $placeholder_video_url) {
                $slides[$slide_index]['video_source_type'] = 'embed_url';
                $slides[$slide_index]['video_embed_url'] = $spmh_video_url;
            }

            if (empty($slide['poster_image']) && $poster_id > 0) {
                $slides[$slide_index]['poster_image'] = matrix_seed_build_image_field(
                    $poster_id,
                    "St Patrick's Mental Health Services 1746–2016"
                );
            }
        }

        $rows[$index]['slides'] = $slides;
        $video_showcase_updated++;
    }

    if ($layout === 'story_slider') {
        $slides = is_array($row['slides'] ?? null) ? $row['slides'] : [];

        foreach ($slides as $slide_index => $slide) {
            if (! is_array($slide)) {
                continue;
            }

            $slides[$slide_index]['has_video'] = 0;
            $slides[$slide_index]['video_embed_url'] = '';
            $slides[$slide_index]['video_link'] = '';
            $slides[$slide_index]['local_video_file'] = '';
        }

        $rows[$index]['slides'] = $slides;
        $story_slider_updated++;
    }
}

$updated = update_field('flexible_content_blocks', $rows, $post_id);

if (! $updated) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Failed to update flexible content for page ' . $post_id);
    }

    exit(1);
}

if (class_exists('WP_CLI')) {
    WP_CLI::success(sprintf(
        'Updated %d video_showcase block(s) with SPMHS YouTube video and %d story_slider block(s) to image-only on page %d.',
        $video_showcase_updated,
        $story_slider_updated,
        $post_id
    ));
}
