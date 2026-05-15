<?php

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
                'target' => (string) ($link['target'] ?? '_self'),
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
        'target' => (string) ($link['target'] ?? '_self'),
    ];
}
