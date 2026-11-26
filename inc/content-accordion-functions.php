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
            'wrapper_classes' => 'mx-auto flex w-full max-w-[1018px] flex-col gap-3 px-5 xl:px-0',
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
            'content_classes' => 'wp_editor [&_p:last-child]:mb-0 [&_p]:mb-4 [&_p]:font-primary [&_p]:text-[16px] [&_p]:font-medium [&_p]:leading-[28px] [&_p]:text-[#08284B] [&_ul]:mb-4 [&_ul]:list-none [&_ul]:pl-6 [&_ol]:mb-4 [&_ol]:list-decimal [&_ol]:pl-6 [&_li]:mb-2 [&_li]:font-normal',
        ];
    }

    if ($layout_style === 'directions_page') {
        return [
            'wrapper_classes' => 'mx-auto flex w-full max-w-[1018px] flex-col gap-3 px-4 mob:px-5 xl:px-0',
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
            'icon_tile_classes' => 'flex shrink-0 items-center justify-center rounded-[4px] p-3',
            'icon_image_classes' => 'h-12 w-12 object-contain',
            'content_classes' => 'wp_editor w-full min-w-0 lg:flex-1 [&_a:not(.btn)]:text-[#024B79] [&_a:not(.btn)]:underline [&_a.btn]:no-underline [&_p:last-child]:mb-0 [&_p]:mb-4 [&_p]:font-primary [&_p]:text-[16px] [&_p]:font-medium [&_p]:leading-[28px] [&_p]:text-[#08284B] [&_strong]:font-bold [&_ul]:mb-4 [&_ul]:list-none [&_ul]:pl-6 [&_ol]:mb-4 [&_ol]:list-decimal [&_ol]:pl-6 [&_li]:mb-2 [&_li]:font-normal',
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
        'content_classes' => 'wp_editor w-full min-w-0 lg:flex-1 [&_p:last-child]:mb-0 [&_p]:mb-4 [&_p]:font-primary [&_p]:text-[16px] [&_p]:font-medium [&_p]:leading-[28px] [&_p]:text-[#08284B] [&_strong]:font-bold [&_a:not(.btn)]:underline [&_a.btn]:no-underline [&_ul]:mb-4 [&_ul]:list-none [&_ul]:pl-6 [&_ol]:mb-4 [&_ol]:list-decimal [&_ol]:pl-6 [&_li]:mb-2 [&_li]:font-normal',
    ];
}

function matrix_get_content_accordion_icon_svg(string $icon_key)
{
    $icons = [
        'car' => '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48" fill="none" aria-hidden="true"><path d="M28 32H18M38 32H44V25.7C44.0014 25.224 43.833 24.7632 43.525 24.4003C43.2171 24.0373 42.7898 23.7961 42.32 23.72L32 22L26.6 14.8C26.4137 14.5516 26.1721 14.35 25.8944 14.2111C25.6167 14.0723 25.3105 14 25 14H10.48C9.73454 13.9948 9.00246 14.198 8.36636 14.5868C7.73026 14.9755 7.21545 15.5342 6.88 16.2L5.28 19.46C4.44029 21.1292 4.00199 22.9714 4 24.84V32H8" stroke="#08284B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M18 33C18 35.7614 15.7614 38 13 38C10.2386 38 8 35.7614 8 33C8 30.2386 10.2386 28 13 28C15.7614 28 18 30.2386 18 33Z" stroke="#08284B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M38 33C38 35.7614 35.7614 38 33 38C30.2386 38 28 35.7614 28 33C28 30.2386 30.2386 28 33 28C35.7614 28 38 30.2386 38 33Z" stroke="#08284B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'map_pin' => '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48" fill="none" aria-hidden="true"><path d="M40 20C40 32 24 44 24 44C24 44 8 32 8 20C8 15.7565 9.68571 11.6869 12.6863 8.68629C15.6869 5.68571 19.7565 4 24 4C28.2435 4 32.3131 5.68571 35.3137 8.68629C38.3143 11.6869 40 15.7565 40 20Z" stroke="#08284B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M30 20C30 23.3137 27.3137 26 24 26C20.6863 26 18 23.3137 18 20C18 16.6863 20.6863 14 24 14C27.3137 14 30 16.6863 30 20Z" stroke="#08284B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'clock' => '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48" fill="none" aria-hidden="true"><path d="M44 24C44 35.0457 35.0457 44 24 44C12.9543 44 4 35.0457 4 24C4 12.9543 12.9543 4 24 4C35.0457 4 44 12.9543 44 24Z" stroke="#08284B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M24 12V24L32 28" stroke="#08284B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'bus' => '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48" fill="none" aria-hidden="true"><path d="M38 34H42L43.28 28.92C43.76 27.002 43.76 24.996 43.28 23.08L41.14 14.54C40.8146 13.2429 40.0652 12.0917 39.0107 11.2691C37.9563 10.4466 36.6573 9.99992 35.32 10H8C6.93913 10 5.92172 10.4214 5.17157 11.1716C4.42143 11.9217 4 12.9391 4 14V34H8" stroke="#08284B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M28 34H18" stroke="#08284B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M18 35C18 37.7614 15.7614 40 13 40C10.2386 40 8 37.7614 8 35C8 32.2386 10.2386 30 13 30C15.7614 30 18 32.2386 18 35Z" stroke="#08284B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M38 35C38 37.7614 35.7614 40 33 40C30.2386 40 28 37.7614 28 35C28 32.2386 30.2386 30 33 30C35.7614 30 38 32.2386 38 35Z" stroke="#08284B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'train' => '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 48 48" fill="none" aria-hidden="true"><path d="M12 6H36C38.2091 6 40 7.79086 40 10V34C40 36.2091 38.2091 38 36 38H12C9.79086 38 8 36.2091 8 34V10C8 7.79086 9.79086 6 12 6Z" stroke="#08284B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 22H40" stroke="#08284B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M24 6V22" stroke="#08284B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M16 38L12 44" stroke="#08284B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M36 44L32 38" stroke="#08284B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
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
    $initial_open_index = -1;
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

    return [
        'items' => $normalized_items,
        'initial_open_index' => $initial_open_index,
    ];
}

function matrix_resolve_content_accordion_vertical_padding($value = '')
{
    $value = trim((string) $value);

    $allowed = [
        'bottom_only',
        'small_top_large_bottom',
        'top_50_bottom_100',
        'compact',
        'no_bottom',
        'no_top',
        'none',
        'medium',
        'custom',
    ];

    if (in_array($value, $allowed, true)) {
        return $value;
    }

    return 'default';
}

/**
 * @param array<int, array<string, mixed>>|mixed $padding_settings
 */
function matrix_get_content_accordion_vertical_padding_classes(
    string $vertical_padding = 'default',
    string $layout_style = 'default',
    $padding_settings = []
): string {
    $vertical_padding = matrix_resolve_content_accordion_vertical_padding($vertical_padding);
    $padding_settings = is_array($padding_settings) ? $padding_settings : [];

    // Legacy presets — keep exact class output so saved content is unchanged.
    if ($vertical_padding === 'bottom_only') {
        return 'pt-0 pb-12 xl:pt-0 xl:pb-[100px]';
    }

    if ($vertical_padding === 'small_top_large_bottom') {
        return 'pt-8 pb-[100px]';
    }

    if ($vertical_padding === 'top_50_bottom_100') {
        return 'pt-[50px] pb-[100px]';
    }

    if ($vertical_padding === 'compact') {
        return 'py-12';
    }

    // Shared presets use accordion xl: desktop breakpoints for visual consistency.
    if ($vertical_padding === 'none') {
        return 'py-0';
    }

    if ($vertical_padding === 'medium') {
        return 'py-12 xl:py-[60px]';
    }

    if ($vertical_padding === 'no_bottom') {
        return 'py-12 xl:pt-[100px] xl:pb-0';
    }

    if ($vertical_padding === 'no_top') {
        return 'pt-0 pb-12 xl:pt-0 xl:pb-[100px]';
    }

    if ($vertical_padding === 'custom') {
        $custom = function_exists('matrix_build_breakpoint_padding_classes')
            ? matrix_build_breakpoint_padding_classes($padding_settings)
            : [];

        if ($custom !== []) {
            return implode(' ', $custom);
        }

        return 'py-12 xl:py-[100px]';
    }

    return 'py-12 xl:py-[100px]';
}

function matrix_resolve_content_accordion_heading($value = '')
{
    return trim((string) $value);
}

function matrix_resolve_content_accordion_heading_tag($value = '')
{
    $tag = strtolower(trim((string) $value));

    if (in_array($tag, ['h2', 'h3', 'h4'], true)) {
        return $tag;
    }

    return 'h2';
}

function matrix_get_content_accordion_heading_class_names()
{
    return 'font-primary text-[24px] font-semibold leading-[28px] tracking-[-0.18px] text-[#1E244B] lg:text-[30px] lg:leading-[36px] lg:tracking-[-0.225px]';
}
