<?php

function matrix_get_locations_grid_vertical_padding_classes($vertical_padding = 'default'): string
{
    $vertical_padding = trim((string) $vertical_padding);

    if ($vertical_padding === 'no_bottom') {
        return 'py-12 lg:pt-[100px] lg:pb-0';
    }

    if ($vertical_padding === 'no_top') {
        return 'pt-0 pb-12 lg:pt-0 lg:pb-[100px]';
    }

    return 'py-12 lg:py-[100px]';
}

function matrix_normalize_locations_grid_cards($rows)
{
    if (! is_array($rows)) {
        return [];
    }

    $cards = [];

    foreach ($rows as $row) {
        $title = trim((string) ($row['title'] ?? ''));

        if ($title === '') {
            continue;
        }

        $image = is_array($row['image'] ?? null) ? $row['image'] : null;
        $link = is_array($row['link'] ?? null) ? $row['link'] : [];
        $url = trim((string) ($link['url'] ?? ''));
        $alt = '';

        if ($image) {
            $alt = trim((string) ($image['alt'] ?? ''));

            if ($alt === '') {
                $alt = trim((string) ($image['title'] ?? ''));
            }
        }

        $cards[] = [
            'title' => $title,
            'image' => $image ? [
                'ID' => (int) ($image['ID'] ?? 0),
                'url' => trim((string) ($image['url'] ?? '')),
                'alt' => $alt,
            ] : null,
            'link' => [
                'title' => trim((string) ($link['title'] ?? '')),
                'url' => $url,
                'target' => matrix_normalize_link_target($url, (string) ($link['target'] ?? '')),
            ],
            'is_linked' => $url !== '',
        ];
    }

    return $cards;
}

function matrix_normalize_locations_grid_link($link)
{
    if (! is_array($link)) {
        return null;
    }

    $title = trim((string) ($link['title'] ?? ''));
    $url = trim((string) ($link['url'] ?? ''));

    if ($title === '' || $url === '') {
        return null;
    }

    return [
        'title' => $title,
        'url' => $url,
        'target' => matrix_normalize_link_target($url, (string) ($link['target'] ?? '')),
    ];
}
