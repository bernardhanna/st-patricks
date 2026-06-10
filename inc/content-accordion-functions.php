<?php

function matrix_resolve_content_accordion_layout_style($value)
{
    $value = is_string($value) ? trim($value) : '';

    if ($value === 'directions_page') {
        return 'directions_page';
    }

    if ($value === 'policies_page') {
        return 'policies_page';
    }

    return 'default';
}

/**
 * @return array<string, string>
 */
function matrix_get_content_accordion_layout_config(string $layout_style)
{
    if ($layout_style === 'policies_page') {
        return [
            'wrapper_classes' => 'mx-auto flex w-full max-w-[1018px] flex-col gap-3 px-5 py-12 xl:px-0 xl:py-[100px]',
            'section_background' => '#FFFFFF',
            'panel_background' => 'linear-gradient(-29.03deg, #F3EADE 3.24%, #F1F3DE 90.88%)',
            'open_panel_background' => 'linear-gradient(-80.97deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
            'icon_tile_background_color' => '#FFFFFF',
            'item_classes' => 'overflow-hidden rounded-[4px]',
            'button_classes' => 'flex w-full items-center justify-between gap-4 px-8 py-6 text-left',
            'title_classes' => 'font-primary text-[24px] font-semibold leading-[28px] tracking-[-0.18px] text-[#1E244B] lg:text-[30px] lg:leading-[36px] lg:tracking-[-0.225px]',
            'panel_body_classes' => 'px-8 pb-8',
            'rows_wrapper_classes' => 'flex flex-col gap-4 pr-8',
            'row_classes' => '',
            'icon_tile_classes' => '',
            'content_classes' => 'wp_editor [&_p:last-child]:mb-0 [&_p]:font-primary [&_p]:text-[16px] [&_p]:font-medium [&_p]:leading-[28px] [&_p]:text-[#08284B]',
        ];
    }

    if ($layout_style === 'directions_page') {
        return [
            'wrapper_classes' => 'mx-auto flex w-full max-w-[1018px] flex-col gap-3 px-4 py-12 mob:px-5 xl:px-0 xl:py-[100px]',
            'section_background' => '#FFFFFF',
            'panel_background' => 'linear-gradient(-28.52deg, #F3EADE 3.24%, #F1F3DE 90.88%)',
            'open_panel_background' => 'linear-gradient(-75.64deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
            'icon_tile_background_color' => '#B3DBAE',
            'item_classes' => 'overflow-hidden rounded-[4px]',
            'button_classes' => 'flex w-full items-center justify-between gap-3 px-6 py-4 text-left lg:gap-4 lg:px-8 lg:py-6',
            'title_classes' => 'font-primary text-[24px] font-semibold leading-[28px] tracking-[-0.18px] text-[#1E244B] lg:text-[30px] lg:leading-[36px] lg:tracking-[-0.225px]',
            'panel_body_classes' => 'px-6 pb-3 lg:px-8 lg:pb-6',
            'rows_wrapper_classes' => 'flex flex-col lg:pr-8',
            'row_classes' => 'flex flex-col items-start gap-3 border-t border-[#F1F8F9] py-3 lg:flex-row lg:items-center lg:gap-6 lg:py-4',
            'icon_tile_classes' => 'flex shrink-0 items-center justify-center rounded-[4px] p-3 lg:h-12 lg:w-12',
            'icon_image_classes' => 'h-12 w-12 object-contain lg:h-6 lg:w-6',
            'content_classes' => 'wp_editor w-full min-w-0 lg:flex-1 [&_a]:text-[#024B79] [&_a]:underline [&_p:last-child]:mb-0 [&_p]:font-primary [&_p]:text-[16px] [&_p]:font-medium [&_p]:leading-[28px] [&_p]:text-[#08284B] [&_strong]:font-bold',
        ];
    }

    return [
        'wrapper_classes' => 'mx-auto flex w-full max-w-[1018px] flex-col gap-4 max-xl:px-5',
        'section_background' => '',
        'panel_background' => 'linear-gradient(135deg, #F6EDE0 0%, #F5F0E0 48%, #F4F5DE 100%)',
        'open_panel_background' => 'linear-gradient(135deg, #F6EDE0 0%, #F5F0E0 48%, #F4F5DE 100%)',
        'icon_tile_background_color' => '#FFFFFF',
        'item_classes' => 'overflow-hidden rounded-[8px] shadow-[0px_1px_1px_rgba(0,0,0,0.05)]',
        'button_classes' => 'flex w-full items-center justify-between gap-4 px-5 py-5 text-left lg:px-8 lg:py-6',
        'title_classes' => 'font-primary text-[20px] font-semibold leading-[24px] tracking-[-0.12px] text-[#1E244B] lg:text-[24px] lg:leading-[28px] lg:tracking-[-0.18px]',
        'panel_body_classes' => 'px-5 pb-5 lg:px-8 lg:pb-8',
        'rows_wrapper_classes' => 'flex flex-col gap-4 border-t border-[rgba(30,36,75,0.12)] pt-5 lg:gap-5 lg:pt-6',
        'row_classes' => 'flex flex-col items-start gap-3 lg:flex-row lg:items-start lg:gap-4',
        'icon_tile_classes' => 'flex shrink-0 items-center justify-center rounded-[8px] p-3 lg:h-12 lg:w-12',
        'icon_image_classes' => 'h-12 w-12 object-contain lg:h-6 lg:w-6',
        'content_classes' => 'wp_editor w-full min-w-0 lg:flex-1 [&_p:last-child]:mb-0 [&_p]:font-primary [&_p]:text-[16px] [&_p]:font-medium [&_p]:leading-[28px] [&_p]:text-[#08284B] [&_strong]:font-bold [&_a]:underline',
    ];
}

function matrix_get_content_accordion_icon_svg(string $icon_key)
{
    $icons = [
        'car' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 11L6.5 6.5H17.5L19 11M5 11H19M5 11V16.5H6.75M19 11V16.5H17.25M6.75 16.5C6.75 17.4665 5.9665 18.25 5 18.25C4.0335 18.25 3.25 17.4665 3.25 16.5C3.25 15.5335 4.0335 14.75 5 14.75C5.9665 14.75 6.75 15.5335 6.75 16.5ZM17.25 16.5C17.25 17.4665 16.4665 18.25 15.5 18.25C14.5335 18.25 13.75 17.4665 13.75 16.5C13.75 15.5335 14.5335 14.75 15.5 14.75C16.4665 14.75 17.25 15.5335 17.25 16.5Z" stroke="#08284B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'map_pin' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 21C12 21 18 14.5 18 10.25C18 6.8 15.45 4.25 12 4.25C8.55 4.25 6 6.8 6 10.25C6 14.5 12 21 12 21Z" stroke="#08284B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="10.25" r="2.25" stroke="#08284B" stroke-width="1.5"/></svg>',
        'clock' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="8.25" stroke="#08284B" stroke-width="1.5"/><path d="M12 8.25V12L14.25 14.25" stroke="#08284B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'bus' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6.75 6.75H17.25V15.75H6.75V6.75Z" stroke="#08284B" stroke-width="1.5" stroke-linejoin="round"/><path d="M6.75 15.75H17.25V17.25H6.75V15.75Z" stroke="#08284B" stroke-width="1.5"/><circle cx="8.75" cy="17.25" r="1" fill="#08284B"/><circle cx="15.25" cy="17.25" r="1" fill="#08284B"/><path d="M8.25 6.75V4.75H15.75V6.75" stroke="#08284B" stroke-width="1.5" stroke-linecap="round"/></svg>',
        'train' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6.75 5.25H17.25V15.75H6.75V5.25Z" stroke="#08284B" stroke-width="1.5" stroke-linejoin="round"/><path d="M6.75 12H17.25" stroke="#08284B" stroke-width="1.5"/><path d="M9.75 5.25V3.75H14.25V5.25" stroke="#08284B" stroke-width="1.5" stroke-linecap="round"/><circle cx="9.25" cy="17.25" r="1" fill="#08284B"/><circle cx="14.75" cy="17.25" r="1" fill="#08284B"/></svg>',
    ];

    return $icons[$icon_key] ?? '';
}

function matrix_get_content_accordion_background_style($background_value, $fallback = '#FBFAF7')
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

function matrix_get_policies_pdf_icon_svg()
{
    return '<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48" fill="none" class="shrink-0"><rect width="48" height="48" rx="4" fill="#FADBD8"/><path d="M16 14H28L32 18V34C32 35.1046 31.1046 36 30 36H18C16.8954 36 16 35.1046 16 34V14Z" fill="#E53935"/><path d="M28 14V18H32" fill="#FFCDD2"/><path d="M20 24H28M20 28H26" stroke="white" stroke-width="1.5" stroke-linecap="round"/></svg>';
}

function matrix_get_policies_external_link_icon_svg()
{
    return '<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none" class="shrink-0"><path d="M6.5 2.5H12.5V8.5" stroke="#08284B" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/><path d="M12.5 2.5L3.5 11.5" stroke="#08284B" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

function matrix_normalize_policies_accordion_row($row)
{
    if (! is_array($row)) {
        return null;
    }

    $row_type = trim((string) ($row['row_type'] ?? 'text'));
    if ($row_type === '') {
        $row_type = 'text';
    }

    if ($row_type === 'pdf_grid') {
        $documents = [];
        foreach ((array) ($row['pdf_documents'] ?? []) as $document) {
            if (! is_array($document)) {
                continue;
            }

            $title = trim(strip_tags((string) ($document['title'] ?? '')));
            $link = function_exists('matrix_normalize_content_link')
                ? matrix_normalize_content_link($document['document_link'] ?? null)
                : null;

            if ($title === '' || $link === null) {
                continue;
            }

            $documents[] = [
                'title' => $title,
                'link' => $link,
            ];
        }

        return $documents !== [] ? ['type' => 'pdf_grid', 'documents' => $documents] : null;
    }

    if ($row_type === 'link_cards') {
        $cards = [];
        foreach ((array) ($row['link_cards'] ?? []) as $card) {
            if (! is_array($card)) {
                continue;
            }

            $title = trim(strip_tags((string) ($card['title'] ?? '')));
            $link = function_exists('matrix_normalize_content_link')
                ? matrix_normalize_content_link($card['button_link'] ?? null)
                : null;

            if ($title === '' || $link === null) {
                continue;
            }

            $cards[] = [
                'title' => $title,
                'link' => $link,
            ];
        }

        return $cards !== [] ? ['type' => 'link_cards', 'cards' => $cards] : null;
    }

    if ($row_type === 'external_links') {
        $links = [];
        foreach ((array) ($row['external_links'] ?? []) as $external_link) {
            if (! is_array($external_link)) {
                continue;
            }

            $title = trim(strip_tags((string) ($external_link['title'] ?? '')));
            $link = function_exists('matrix_normalize_content_link')
                ? matrix_normalize_content_link($external_link['link'] ?? null)
                : null;

            if ($title === '' || $link === null) {
                continue;
            }

            $links[] = [
                'title' => $title,
                'link' => $link,
            ];
        }

        return $links !== [] ? ['type' => 'external_links', 'links' => $links] : null;
    }

    $content = (string) ($row['content'] ?? '');
    if (trim(strip_tags($content)) === '') {
        return null;
    }

    return [
        'type' => 'text',
        'content' => $content,
    ];
}

function matrix_normalize_content_accordion_items($items, $layout_style = 'default')
{
    $layout_style = matrix_resolve_content_accordion_layout_style($layout_style);
    $normalized_items = [];
    $initial_open_index = 0;
    $found_open_item = false;

    foreach ((array) $items as $item) {
        if (! is_array($item)) {
            continue;
        }

        $title = trim(strip_tags((string) ($item['title'] ?? '')));
        $rows = [];

        foreach ((array) ($item['content_rows'] ?? []) as $row) {
            if ($layout_style === 'policies_page') {
                $normalized_row = matrix_normalize_policies_accordion_row($row);
                if ($normalized_row !== null) {
                    $rows[] = $normalized_row;
                }
                continue;
            }

            if (! is_array($row)) {
                continue;
            }

            $content = (string) ($row['content'] ?? '');
            if (trim(strip_tags($content)) === '') {
                continue;
            }

            $icon = is_array($row['icon'] ?? null) ? $row['icon'] : null;
            $icon_key = trim((string) ($row['icon_key'] ?? ''));

            $rows[] = [
                'type' => 'text',
                'icon' => [
                    'url' => trim((string) ($icon['url'] ?? '')),
                    'alt' => trim((string) ($icon['alt'] ?? '')) ?: 'Accordion icon',
                ],
                'icon_key' => $icon_key,
                'content' => $content,
            ];
        }

        if ($title === '' || $rows === []) {
            continue;
        }

        $normalized_items[] = [
            'title' => $title,
            'starts_open' => ! empty($item['starts_open']),
            'rows' => $rows,
        ];
    }

    foreach ($normalized_items as $index => $item) {
        if ($item['starts_open']) {
            $initial_open_index = $index;
            $found_open_item = true;
            break;
        }
    }

    if (! $found_open_item && $normalized_items === []) {
        $initial_open_index = -1;
    }

    return [
        'items' => $normalized_items,
        'initial_open_index' => $initial_open_index,
    ];
}
