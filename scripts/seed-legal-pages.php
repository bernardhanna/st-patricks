<?php

/**
 * Seed legal / policy pages with a title hero and default editor content.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-legal-pages.php
 */

if (! function_exists('matrix_seed_legal_page_body')) {
    function matrix_seed_legal_page_body(string $intro_sentence = ''): string
    {
        $paragraph = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.';

        if ($intro_sentence === '') {
            $intro_sentence = $paragraph;
        }

        $sections = [
            'Introduction' => $intro_sentence,
            'How we use your information' => $paragraph,
            'Your rights' => $paragraph,
            'Contact us' => $paragraph,
        ];

        $html = '';

        foreach ($sections as $heading => $copy) {
            $html .= '<h2>' . esc_html($heading) . '</h2>';
            $html .= '<p>' . esc_html($copy) . '</p>';
        }

        return $html;
    }
}

if (! function_exists('matrix_seed_legal_page_hero')) {
    function matrix_seed_legal_page_hero(string $title): array
    {
        return [
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
            ],
            'current_crumb_label' => $title,
            'heading_tag' => 'h1',
            'heading' => $title,
            'content' => '',
            'background_color' => '#FBF8F3',
            'breadcrumb_background_color' => '#F1F8F9',
            'heading_color' => '#1E244B',
            'text_color' => '#08284B',
            'accent_color' => '#6FC9C0',
        ];
    }
}

$pages = [
    [
        'path' => 'cookie-privacy-policy',
        'title' => 'Cookie & Privacy policy',
        'intro' => 'This page explains how St Patrick\'s Mental Health Services uses cookies and handles personal data on this website.',
    ],
    [
        'path' => 'data-protection-policy',
        'title' => 'Data protection policy',
        'intro' => 'This page outlines how we collect, store, and protect personal information in line with data protection requirements.',
    ],
    [
        'path' => 'accessibility',
        'title' => 'Accessibility',
        'intro' => 'This page describes our commitment to making this website accessible to as many people as possible.',
    ],
];

$home = home_url('/');
$updated = 0;

foreach ($pages as $page_config) {
    $post = get_page_by_path($page_config['path']);

    if (! $post instanceof WP_Post) {
        if (class_exists('WP_CLI')) {
            WP_CLI::warning('Could not find page: ' . $page_config['path']);
        }

        continue;
    }

    $post_id = (int) $post->ID;
    $title = (string) $page_config['title'];

    wp_update_post([
        'ID' => $post_id,
        'post_content' => matrix_seed_legal_page_body((string) $page_config['intro']),
    ]);

    update_field('hero_content_blocks', [matrix_seed_legal_page_hero($title)], $post_id);
    update_field('flexible_content_blocks', [], $post_id);

    $updated++;

    if (class_exists('WP_CLI')) {
        WP_CLI::log(sprintf('Seeded %s (%d)', $page_config['path'], $post_id));
    }
}

if (class_exists('WP_CLI')) {
    if ($updated === count($pages)) {
        WP_CLI::success(sprintf('Seeded %d legal pages.', $updated));
    } else {
        WP_CLI::warning(sprintf('Seeded %d of %d legal pages.', $updated, count($pages)));
    }
}
