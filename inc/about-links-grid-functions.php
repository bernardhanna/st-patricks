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

    return 'image_feature';
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
