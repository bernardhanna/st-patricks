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

function matrix_get_key_contact_info_wrapper_class_names()
{
    return implode(' ', [
        'mx-auto',
        'flex',
        'w-full',
        'max-w-[1018px]',
        'flex-col',
        'px-5',
        'pt-12',
        'lg:px-0',
        'lg:pt-16',
        'lg:pb-[100px]',
    ]);
}

function matrix_get_key_contact_info_grid_class_names()
{
    return 'grid w-full grid-cols-1 gap-4 lg:grid-cols-3 lg:gap-x-8 lg:gap-y-4';
}

function matrix_get_key_contact_info_column_class_names()
{
    return 'flex w-full flex-col gap-4';
}

function matrix_get_key_contact_info_item_class_names()
{
    return 'overflow-hidden rounded-[4px] border border-white';
}

function matrix_get_key_contact_info_header_class_names()
{
    return 'flex min-h-[58px] w-full items-center justify-between gap-4 px-6 py-4 text-left focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#024B79]';
}

function matrix_get_key_contact_info_title_class_names()
{
    return 'font-primary text-[16px] font-semibold leading-[24px] tracking-[-0.1px] text-[#08284B] lg:text-[18px] lg:leading-[28px]';
}

function matrix_get_key_contact_info_panel_class_names()
{
    return 'px-6 pb-4';
}

function matrix_get_key_contact_info_contact_row_class_names()
{
    return 'flex items-center gap-3';
}

function matrix_get_key_contact_info_contact_text_class_names()
{
    return 'font-primary text-[16px] font-medium leading-[28px] text-[#08284B] transition-colors hover:text-[#024B79] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]';
}

function matrix_get_key_contact_info_chevron_svg()
{
    return '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M4 6L8 10L12 6" stroke="#1E244B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

function matrix_get_key_contact_info_phone_icon_svg()
{
    return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24c1.12.37 2.33.57 3.58.57a1 1 0 011 1V20a1 1 0 01-1 1C10.85 21 3 13.15 3 3a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.46.57 3.58a1 1 0 01-.24 1.01l-2.2 2.2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

function matrix_get_key_contact_info_email_icon_svg()
{
    return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 6h16v12H4V6z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M4 7l8 6 8-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

function matrix_key_contact_info_item_has_panel_content(array $item)
{
    return ($item['bullet_items'] ?? []) !== []
        || ($item['phone'] ?? '') !== ''
        || ($item['email'] ?? '') !== '';
}

function matrix_apply_key_contact_info_item_placeholders(array $item)
{
    if (matrix_key_contact_info_item_has_panel_content($item)) {
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

/**
 * @return array{columns: array<int, array<string, mixed>>, initial_open_index: int}
 */
function matrix_normalize_key_contact_info_columns($columns)
{
    $normalized_columns = [];
    $initial_open_index = -1;
    $flat_index = 0;

    foreach ((array) $columns as $column) {
        if (! is_array($column)) {
            continue;
        }

        $items = [];

        foreach ((array) ($column['items'] ?? []) as $item) {
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
                $initial_open_index = $flat_index;
            }

            $items[] = matrix_apply_key_contact_info_item_placeholders([
                'title' => $title,
                'starts_open' => $starts_open,
                'bullet_items' => $bullet_items,
                'phone' => $phone,
                'email' => $email,
                'flat_index' => $flat_index,
            ]);

            $flat_index++;
        }

        if ($items === []) {
            continue;
        }

        $normalized_columns[] = [
            'items' => $items,
        ];
    }

    return [
        'columns' => $normalized_columns,
        'initial_open_index' => $initial_open_index,
    ];
}
