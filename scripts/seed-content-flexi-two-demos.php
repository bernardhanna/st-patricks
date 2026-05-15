<?php

$post_id = 329;
$rows = get_field('flexible_content_blocks', $post_id);

if (! is_array($rows)) {
    $rows = [];
}

$image_id = 0;
$attachments = get_posts([
    'post_type' => 'attachment',
    'post_status' => 'inherit',
    'posts_per_page' => 1,
    'post_mime_type' => 'image',
    'orderby' => 'ID',
    'order' => 'DESC',
]);

if ($attachments !== []) {
    $image_id = (int) $attachments[0]->ID;
}

$filtered_rows = array_values(array_filter($rows, static function ($row) {
    return ! is_array($row) || (($row['acf_fc_layout'] ?? '') !== 'content');
}));

$shared_body = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>';

$intro = '<p>Our vision is to see a society where all citizens are empowered and given the opportunity to live mentally healthy lives.</p>';

$filtered_rows[] = [
    'acf_fc_layout' => 'content',
    'heading' => 'Our Vision and Mission',
    'heading_tag' => 'h2',
    'accent_position' => 'below_heading',
    'intro_text' => $intro,
    'content' => $shared_body,
    'layout_style' => 'image_left',
    'background_type' => 'cream',
    'image' => $image_id,
];

$filtered_rows[] = [
    'acf_fc_layout' => 'content',
    'heading' => 'Placeholder',
    'heading_tag' => 'h2',
    'accent_position' => 'below_heading',
    'intro_text' => $intro,
    'content' => $shared_body,
    'layout_style' => 'image_left',
    'background_type' => 'cream',
    'image' => $image_id,
    'document_link' => [
        'title' => 'PDF open in a new tab',
        'url' => home_url('/wp-content/uploads/sample.pdf'),
        'target' => '_blank',
    ],
];

$updated = update_field('flexible_content_blocks', $filtered_rows, $post_id);

if (! $updated) {
    WP_CLI::error('Failed to update flexible content for page ' . $post_id);
}

WP_CLI::success('Replaced content blocks with 2 demos on page ' . $post_id . '.');
