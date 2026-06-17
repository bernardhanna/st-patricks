<?php

/**
 * About Links Grid tone backgrounds (Figma 2780:3450).
 */
function matrix_get_about_links_grid_tone_backgrounds(): array
{
    return [
        'bg1' => '#D9F0F4',
        'bg2' => '#E2EBCF',
        'bg3' => '#E5E1F3',
        'bg4' => '#F3DDE8',
    ];
}

function matrix_get_about_links_grid_card_footer_background(string $card_tone, string $fallback = '#F1F8F9'): string
{
    $card_tone = trim($card_tone);
    $backgrounds = matrix_get_about_links_grid_tone_backgrounds();

    if ($card_tone !== '' && isset($backgrounds[$card_tone])) {
        return $backgrounds[$card_tone];
    }

    return $fallback !== '' ? $fallback : '#F1F8F9';
}

/**
 * Normalize the About Links Grid layout style.
 */
function matrix_resolve_about_links_grid_layout_style($value = ''): string
{
    $value = trim((string) $value);

    if ($value === 'compact_row') {
        return 'compact_row';
    }

    if ($value === 'flush_image') {
        return 'flush_image';
    }

    return 'image_feature';
}

/**
 * Resolve the card partial for the selected About Links Grid layout.
 */
function matrix_get_about_links_grid_card_partial(string $layout_style = 'image_feature'): string
{
    if ($layout_style === 'compact_row') {
        return 'template-parts/flexi/partials/about-links-grid-card-compact-row';
    }

    if ($layout_style === 'flush_image') {
        return 'template-parts/flexi/partials/about-links-grid-card-flush-image';
    }

    return 'template-parts/flexi/partials/about-links-grid-card-image-feature';
}

/**
 * Grid wrapper classes for About Links Grid.
 */
function matrix_get_about_links_grid_grid_class_names(string $layout_style, string $columns): string
{
    $column_classes = [
        '2' => 'lg:grid-cols-2',
        '3' => 'lg:grid-cols-3',
        '4' => 'lg:grid-cols-4',
    ];
    $grid_columns = $column_classes[$columns] ?? 'lg:grid-cols-3';

    if ($layout_style === 'flush_image') {
        return 'grid grid-cols-1 gap-4 ' . $grid_columns . ' lg:gap-x-8 lg:gap-y-4';
    }

    return 'grid grid-cols-1 gap-4 ' . $grid_columns . ' lg:gap-x-8 lg:gap-y-4';
}

/**
 * Footer background for About Links Grid cards.
 */
function matrix_get_about_links_grid_card_background(string $layout_style, string $card_tone, string $card_bg_color): string
{
    if (matrix_resolve_about_links_grid_layout_style($layout_style) === 'flush_image') {
        $card_bg_color = trim($card_bg_color);

        return $card_bg_color !== '' ? $card_bg_color : '#F1F8F9';
    }

    return matrix_get_about_links_grid_card_footer_background($card_tone, $card_bg_color);
}

/**
 * Normalize the desktop column count for About Links Grid.
 */
function matrix_resolve_about_links_grid_columns($value = ''): string
{
    $value = trim((string) $value);

    if (preg_match('/^([234])(?:\s*columns?)?$/i', $value, $matches)) {
        return $matches[1];
    }

    if (in_array($value, ['2', '3', '4'], true)) {
        return $value;
    }

    return '3';
}
