<?php

function matrix_format_timeline_date($event_date, $event_date_label = '')
{
    $event_date_label = trim((string) $event_date_label);

    if ($event_date_label !== '') {
        return $event_date_label;
    }

    $event_date = trim((string) $event_date);

    if ($event_date === '') {
        return '';
    }

    $timestamp = strtotime($event_date);

    if ($timestamp === false) {
        return $event_date;
    }

    return date('j.n.Y', $timestamp);
}

function matrix_normalize_timeline_link($link)
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

function matrix_normalize_timeline_items($rows)
{
    if (! is_array($rows)) {
        return [];
    }

    $items = [];
    $index = 0;

    foreach ($rows as $row) {
        $item_heading = trim((string) ($row['item_heading'] ?? ''));
        $item_text = trim((string) ($row['item_text'] ?? ''));
        $display_date = matrix_format_timeline_date(
            $row['event_date'] ?? '',
            $row['event_date_label'] ?? ''
        );

        if ($item_heading === '') {
            continue;
        }

        $side = trim((string) ($row['side'] ?? ''));

        if (! in_array($side, ['left', 'right'], true)) {
            $side = $index % 2 === 0 ? 'left' : 'right';
        }

        $image = is_array($row['image'] ?? null) ? $row['image'] : null;
        $cta_link = matrix_normalize_timeline_link($row['cta_link'] ?? null);
        $alt = '';

        if ($image) {
            $alt = trim((string) ($image['alt'] ?? ''));

            if ($alt === '') {
                $alt = trim((string) ($image['title'] ?? ''));
            }

            if ($alt === '' && $item_heading !== '') {
                $alt = $item_heading;
            }
        }

        $items[] = [
            'side' => $side,
            'event_date' => trim((string) ($row['event_date'] ?? '')),
            'display_date' => $display_date,
            'item_heading' => $item_heading,
            'item_heading_tag' => trim((string) ($row['item_heading_tag'] ?? 'h3')),
            'item_text' => $item_text,
            'image' => $image ? [
                'ID' => (int) ($image['ID'] ?? 0),
                'url' => trim((string) ($image['url'] ?? '')),
                'alt' => $alt,
            ] : null,
            'cta_link' => $cta_link,
            'has_cta' => is_array($cta_link),
        ];

        $index++;
    }

    return $items;
}
