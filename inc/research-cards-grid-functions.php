<?php

function matrix_get_research_cards_grid_defaults()
{
    return [
        'heading' => 'Our Research Ethics Committee',
        'background_color' => '#FFFFFF',
        'heading_color' => '#1E244B',
        'intro_color' => '#08284B',
        'card_title_color' => '#1E244B',
        'card_body_color' => '#1E244B',
        'button_border_color' => '#024B79',
        'button_text_color' => '#08284B',
    ];
}

function matrix_normalize_research_cards_grid_link($link)
{
    if (! is_array($link) || empty($link['url'])) {
        return null;
    }

    $title = trim((string) ($link['title'] ?? ''));

    $url = (string) $link['url'];

    return [
        'url' => $url,
        'title' => $title !== '' ? $title : 'Learn more',
        'target' => matrix_normalize_link_target($url, (string) ($link['target'] ?? '')),
    ];
}

function matrix_get_research_cards_grid_card_title_tag($section_heading_tag)
{
    switch (strtolower(trim((string) $section_heading_tag))) {
        case 'h1':
            return 'h2';
        case 'h2':
            return 'h3';
        case 'h3':
            return 'h4';
        case 'h4':
            return 'h5';
        case 'h5':
            return 'h6';
        default:
            return 'p';
    }
}

function matrix_resolve_research_cards_grid_source_mode($source_mode)
{
    $source_mode = strtolower(trim((string) $source_mode));

    if (! in_array($source_mode, ['manual', 'category', 'latest', 'selected'], true)) {
        return 'manual';
    }

    return $source_mode;
}

function matrix_build_research_cards_grid_query_args($source_mode, $args = [])
{
    $source_mode = matrix_resolve_research_cards_grid_source_mode($source_mode);
    $posts_per_page = max(1, (int) ($args['posts_per_page'] ?? 4));

    $query_args = [
        'post_type' => 'research_projects',
        'post_status' => 'publish',
        'posts_per_page' => $posts_per_page,
        'orderby' => 'date',
        'order' => 'DESC',
    ];

    if ($source_mode === 'category') {
        $category_ids = array_values(array_filter(array_map('intval', (array) ($args['selected_categories'] ?? []))));

        if ($category_ids !== []) {
            $query_args['tax_query'] = [[
                'taxonomy' => 'research_project_category',
                'field' => 'term_id',
                'terms' => $category_ids,
            ]];
        }
    }

    return $query_args;
}

function matrix_normalize_research_cards_grid_card_from_post($post)
{
    // A plain data array already carries the fields we need, so use them
    // directly. This keeps the helper deterministic regardless of which
    // WordPress functions happen to be loaded.
    if (is_array($post)) {
        return matrix_build_research_cards_grid_card_from_data($post);
    }

    $post_id = 0;

    if ($post instanceof WP_Post) {
        $post_id = (int) $post->ID;
    } elseif (is_numeric($post)) {
        $post_id = (int) $post;
    }

    if ($post_id < 1) {
        return null;
    }

    $title = function_exists('get_the_title') ? trim((string) get_the_title($post_id)) : '';

    if ($title === '') {
        return null;
    }

    $summary = function_exists('get_the_excerpt') ? trim((string) get_the_excerpt($post_id)) : '';

    if ($summary === '' && function_exists('get_post_field')) {
        $content = (string) get_post_field('post_content', $post_id);
        $summary = function_exists('wp_trim_words')
            ? wp_trim_words(wp_strip_all_tags($content), 24)
            : trim($content);
    }

    $image = null;
    $image_id = function_exists('get_post_thumbnail_id') ? (int) get_post_thumbnail_id($post_id) : 0;

    if ($image_id > 0) {
        $image_url = function_exists('wp_get_attachment_image_url')
            ? (string) wp_get_attachment_image_url($image_id, 'medium_large')
            : '';
        $image_alt = function_exists('get_post_meta')
            ? trim((string) get_post_meta($image_id, '_wp_attachment_image_alt', true))
            : '';

        if ($image_alt === '') {
            $image_alt = $title;
        }

        $image = [
            'ID' => $image_id,
            'url' => $image_url,
            'alt' => $image_alt,
        ];
    }

    $permalink = function_exists('get_permalink') ? (string) get_permalink($post_id) : '';

    return matrix_build_research_cards_grid_card([
        'title' => $title,
        'summary' => $summary,
        'image' => $image,
        'permalink' => $permalink,
    ]);
}

function matrix_build_research_cards_grid_card_from_data(array $post)
{
    $post_id = (int) ($post['ID'] ?? $post['id'] ?? 0);

    if ($post_id < 1) {
        return null;
    }

    $title = trim((string) ($post['post_title'] ?? ''));

    if ($title === '') {
        return null;
    }

    $image = null;
    $image_id = (int) ($post['image_id'] ?? 0);

    if ($image_id > 0) {
        $image_alt = trim((string) ($post['image_alt'] ?? ''));

        $image = [
            'ID' => $image_id,
            'url' => (string) ($post['image_url'] ?? ''),
            'alt' => $image_alt !== '' ? $image_alt : $title,
        ];
    }

    return matrix_build_research_cards_grid_card([
        'title' => $title,
        'summary' => trim((string) ($post['post_excerpt'] ?? '')),
        'image' => $image,
        'permalink' => (string) ($post['permalink'] ?? ''),
    ]);
}

function matrix_build_research_cards_grid_card(array $parts)
{
    return [
        'title' => $parts['title'],
        'summary' => $parts['summary'],
        'image' => $parts['image'],
        'link' => matrix_normalize_research_cards_grid_link([
            'url' => $parts['permalink'],
            'title' => $parts['title'],
            'target' => '_self',
        ]),
    ];
}

function matrix_normalize_research_cards_grid_cards($rows)
{
    $cards = [];

    foreach ((array) $rows as $row) {
        if (! is_array($row)) {
            continue;
        }

        $title = trim((string) ($row['title'] ?? ''));

        if ($title === '') {
            continue;
        }

        $cards[] = [
            'title' => $title,
            'summary' => trim((string) ($row['summary'] ?? '')),
            'image' => is_array($row['image'] ?? null) ? $row['image'] : null,
            'link' => matrix_normalize_research_cards_grid_link($row['link'] ?? null),
        ];
    }

    return $cards;
}

function matrix_resolve_research_cards_grid_cards($args = [])
{
    $source_mode = matrix_resolve_research_cards_grid_source_mode($args['cards_source'] ?? 'manual');
    $posts_per_page = max(1, (int) ($args['posts_per_page'] ?? 4));

    if ($source_mode === 'manual') {
        return matrix_normalize_research_cards_grid_cards($args['manual_cards'] ?? []);
    }

    $posts = [];

    if ($source_mode === 'selected') {
        $selected_projects = is_array($args['selected_projects'] ?? null) ? $args['selected_projects'] : [];
        $posts = array_slice($selected_projects, 0, $posts_per_page);
    } elseif (function_exists('get_posts')) {
        $posts = get_posts(matrix_build_research_cards_grid_query_args($source_mode, $args));
    }

    $cards = [];

    foreach ($posts as $post) {
        $card = matrix_normalize_research_cards_grid_card_from_post($post);

        if (is_array($card)) {
            $cards[] = $card;
        }
    }

    return $cards;
}
