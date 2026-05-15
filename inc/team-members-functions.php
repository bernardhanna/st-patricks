<?php

function matrix_normalize_team_member_item($item, $layout_style = 'standard_grid')
{
    if (! is_array($item)) {
        return null;
    }

    $name = trim(strip_tags((string) ($item['name'] ?? $item['post_title'] ?? '')));
    $job_title = trim(strip_tags((string) ($item['job_title'] ?? $item['post_excerpt'] ?? '')));
    $profile_teaser = (string) ($item['profile_teaser'] ?? $item['teaser'] ?? '');
    $permalink = trim((string) ($item['permalink'] ?? $item['url'] ?? ''));
    $image = is_array($item['image'] ?? null) ? $item['image'] : null;

    if ($name === '') {
        return null;
    }

    $normalized_layout_style = $layout_style === 'spokespeople_grid' ? 'spokespeople_grid' : 'standard_grid';

    return [
        'name' => $name,
        'job_title' => $job_title,
        'profile_teaser' => $profile_teaser,
        'permalink' => $permalink,
        'image' => $image,
        'layout_style' => $normalized_layout_style,
        'show_arrow' => $normalized_layout_style === 'standard_grid' && $permalink !== '',
    ];
}

function matrix_resolve_team_member_items($source_mode, $selected_posts = [], $category_posts = [], $layout_style = 'standard_grid')
{
    $resolved_source = [];

    if ($source_mode === 'selected') {
        $resolved_source = $selected_posts;
    } elseif ($source_mode === 'category') {
        $resolved_source = $category_posts;
    } elseif ($source_mode === 'all') {
        $resolved_source = $category_posts;
    }

    $items = [];

    foreach ((array) $resolved_source as $item) {
        $normalized_item = matrix_normalize_team_member_item($item, $layout_style);

        if (is_array($normalized_item)) {
            $items[] = $normalized_item;
        }
    }

    return $items;
}

function matrix_get_team_member_section_background_style($background_value, $fallback = '#FBFAF7')
{
    $resolved_value = trim((string) $background_value);

    if ($resolved_value === '') {
        $resolved_value = trim((string) $fallback);
    }

    if ($resolved_value === '') {
        return '';
    }

    if (stripos($resolved_value, 'gradient(') !== false) {
        return "background: {$resolved_value};";
    }

    return "background-color: {$resolved_value};";
}
