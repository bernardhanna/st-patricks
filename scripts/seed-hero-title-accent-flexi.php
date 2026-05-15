<?php

$post_id = 329;
$rows = get_field('flexible_content_blocks', $post_id);

if (! is_array($rows)) {
    $rows = [];
}

$rows[] = [
    'acf_fc_layout' => 'hero_with_breadcrumbs',
    'layout_style' => 'title_accent',
    'show_breadcrumbs' => 1,
    'breadcrumb_source' => 'manual',
    'manual_breadcrumbs' => [
        [
            'breadcrumb_link' => [
                'title' => 'Home',
                'url' => home_url('/'),
                'target' => '',
            ],
        ],
        [
            'breadcrumb_link' => [
                'title' => 'Media',
                'url' => home_url('/media/'),
                'target' => '',
            ],
        ],
    ],
    'current_crumb_label' => 'Press Releases',
    'heading_tag' => 'h1',
    'heading' => 'Press Releases',
    'content' => '',
    'background_color' => '#FBF8F3',
    'breadcrumb_background_color' => '#F1F8F9',
    'heading_color' => '#1E244B',
    'text_color' => '#08284B',
    'accent_color' => '#6FC9C0',
];

$updated = update_field('flexible_content_blocks', $rows, $post_id);

if (! $updated) {
    WP_CLI::error('Failed to update flexible content for page ' . $post_id);
}

WP_CLI::success('Added title_accent hero_with_breadcrumbs block to page ' . $post_id . '.');
