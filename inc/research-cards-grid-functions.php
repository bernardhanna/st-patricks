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

    return [
        'url' => (string) $link['url'],
        'title' => $title !== '' ? $title : 'Learn more',
        'target' => trim((string) ($link['target'] ?? '')) ?: '_self',
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
