<?php

/**
 * Pastel card backgrounds for the multidisciplinary team grid (Figma 4642:3295).
 *
 * @return array<string, string>
 */
function matrix_get_multidisciplinary_team_grid_tone_backgrounds(): array
{
    return [
        'teal' => '#CDF1ED',
        'green' => '#E3F3D5',
        'lavender' => '#E8E2F7',
        'pink' => '#F8E4F2',
        'coral' => '#F9DBD8',
        'yellow' => '#F8F1D1',
    ];
}

function matrix_normalize_multidisciplinary_team_cards($cards)
{
    $allowed_tones = ['teal', 'green', 'yellow', 'lavender', 'pink', 'coral'];
    $normalized_cards = [];

    foreach ((array) $cards as $index => $card) {
        if (! is_array($card)) {
            continue;
        }

        $title = trim(strip_tags((string) ($card['title'] ?? '')));
        $description = (string) ($card['description'] ?? '');
        $description_text = trim(strip_tags($description));

        if ($title === '' && $description_text === '') {
            continue;
        }

        $raw_tone = (string) ($card['card_tone'] ?? '');
        $tone = in_array($raw_tone, $allowed_tones, true)
            ? $raw_tone
            : $allowed_tones[$index % count($allowed_tones)];

        $raw_link = $card['link'] ?? null;
        $link = null;

        if (is_array($raw_link) && ! empty($raw_link['url'])) {
            $url = (string) $raw_link['url'];
            $link = [
                'url' => $url,
                'title' => trim((string) ($raw_link['title'] ?? '')),
                'target' => matrix_normalize_link_target($url, (string) ($raw_link['target'] ?? '')),
            ];
        }

        $normalized_cards[] = [
            'title' => $title,
            'description' => $description,
            'card_tone' => $tone,
            'link' => $link,
            'is_linked' => is_array($link),
        ];
    }

    return $normalized_cards;
}
