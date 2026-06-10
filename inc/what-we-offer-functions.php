<?php

function matrix_resolve_what_we_offer_layout_style($value)
{
    $value = is_string($value) ? trim($value) : '';

    if ($value === 'intro_two_column') {
        return 'intro_two_column';
    }

    return 'image_feature';
}

function matrix_get_what_we_offer_accent_color($service_row, $index = 0)
{
    $accent_color = is_array($service_row) ? trim((string) ($service_row['accent_color'] ?? '')) : '';

    if ($accent_color !== '') {
        return $accent_color;
    }

    $palette = [
        '#6FC9C0',
        '#C3DBAE',
        '#B4A8CE',
        '#E4B8D6',
    ];

    return $palette[max(0, (int) $index) % count($palette)];
}

function matrix_get_what_we_offer_intro_two_column_icon_urls($base_url = '')
{
    $base_url = rtrim((string) $base_url, '/');

    return [
        'default' => $base_url . '/wp-content/uploads/2025/11/left.svg',
        'hover' => $base_url . '/wp-content/uploads/2026/03/left.svg',
    ];
}

function matrix_get_what_we_offer_intro_two_column_icon_svg($state = 'default')
{
    $theme_dir = function_exists('get_template_directory')
        ? get_template_directory()
        : dirname(__DIR__);
    $svg_path = $theme_dir . '/assets/svg/st-patricks-logo-symbol.svg';

    if (! is_readable($svg_path)) {
        return '';
    }

    $svg = file_get_contents($svg_path);

    if ($svg === false || $svg === '') {
        return '';
    }

    if ($state === 'hover') {
        $svg = str_replace('opacity="0.25"', 'opacity="1"', $svg);
    }

    return $svg;
}
