<?php

function matrix_get_key_contact_info_background_style($background_value, $fallback = '#FBFAF7')
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

function matrix_apply_key_contact_info_item_placeholders(array $item)
{
    $has_panel_content = ($item['bullet_items'] ?? []) !== []
        || ($item['phone'] ?? '') !== ''
        || ($item['email'] ?? '') !== '';

    if ($has_panel_content) {
        return $item;
    }

    return array_merge($item, [
        'bullet_items' => [
            'General enquiries',
            'Referrals and admissions',
            'Out-of-hours support',
        ],
        'phone' => '01 012 123 123',
        'email' => 'hello@StPatrick.ie',
    ]);
}

function matrix_normalize_key_contact_info_columns($columns)
{
    $normalized_columns = [];

    foreach ((array) $columns as $column) {
        if (! is_array($column)) {
            continue;
        }

        $items = [];
        $initial_open_index = -1;

        foreach ((array) ($column['items'] ?? []) as $item_index => $item) {
            if (! is_array($item)) {
                continue;
            }

            $title = trim(strip_tags((string) ($item['title'] ?? '')));

            if ($title === '') {
                continue;
            }

            $bullet_items = [];

            foreach ((array) ($item['bullet_items'] ?? []) as $bullet_item) {
                if (! is_array($bullet_item)) {
                    continue;
                }

                $label = trim(strip_tags((string) ($bullet_item['label'] ?? '')));

                if ($label === '') {
                    continue;
                }

                $bullet_items[] = $label;
            }

            $phone = trim(strip_tags((string) ($item['phone'] ?? '')));
            $email = trim(strip_tags((string) ($item['email'] ?? '')));
            $starts_open = ! empty($item['starts_open']);

            if ($starts_open && $initial_open_index < 0) {
                $initial_open_index = count($items);
            }

            $items[] = matrix_apply_key_contact_info_item_placeholders([
                'title' => $title,
                'starts_open' => $starts_open,
                'bullet_items' => $bullet_items,
                'phone' => $phone,
                'email' => $email,
            ]);
        }

        if ($items === []) {
            continue;
        }

        $normalized_columns[] = [
            'items' => $items,
            'initial_open_index' => $initial_open_index,
        ];
    }

    return $normalized_columns;
}
