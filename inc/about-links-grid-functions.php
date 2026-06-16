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
