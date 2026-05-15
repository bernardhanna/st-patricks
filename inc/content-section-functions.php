<?php

function matrix_resolve_content_layout_style($layout_style, $reverse_layout = false)
{
    $layout_style = is_string($layout_style) ? trim($layout_style) : '';

    if ($layout_style === 'image_right') {
        return 'image_right';
    }

    if ($layout_style === 'image_left') {
        return 'image_left';
    }

    return $reverse_layout ? 'image_right' : 'image_left';
}

function matrix_resolve_content_accent_position($value)
{
    $value = is_string($value) ? trim($value) : '';

    if ($value === 'above_heading') {
        return 'above_heading';
    }

    return 'below_heading';
}

function matrix_resolve_content_button_variant($value, $default = 'filled')
{
    $value = is_string($value) ? trim($value) : '';

    if ($value === 'outline') {
        return 'outline';
    }

    return $default === 'outline' ? 'outline' : 'filled';
}

function matrix_normalize_content_link($link)
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

function matrix_content_has_visible_rich_text($value)
{
    return is_string($value) && trim(strip_tags($value)) !== '';
}

function matrix_get_content_background_style($background_type, $background_color = '', $background_gradient = '')
{
    $background_type = is_string($background_type) ? trim($background_type) : 'gradient';

    if ($background_type === 'white') {
        return 'background-color: #FFFFFF;';
    }

    if ($background_type === 'cream') {
        return 'background-color: #FBF8F3;';
    }

    if ($background_type === 'light_blue') {
        return 'background-color: #C6ECF4;';
    }

    if ($background_type === 'gradient') {
        $gradient = trim((string) $background_gradient);

        if ($gradient === '') {
            $gradient = 'linear-gradient(278deg, #F8F6F3 3.24%, #F5F6ED 90.88%)';
        }

        return function_exists('matrix_get_faq_background_style')
            ? matrix_get_faq_background_style($gradient)
            : 'background: ' . $gradient . ';';
    }

    $color = trim((string) $background_color);

    if ($color === '') {
        $color = '#FFFFFF';
    }

    return function_exists('matrix_get_faq_background_style')
        ? matrix_get_faq_background_style($color)
        : 'background-color: ' . $color . ';';
}

function matrix_get_content_button_class_names($variant = 'filled')
{
    $base = 'btn inline-flex h-[36px] w-fit items-center justify-center whitespace-nowrap rounded-[6px] px-3 text-[14px] font-medium leading-[24px] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]';

    if ($variant === 'outline') {
        return $base . ' border border-[#024B79] bg-white text-[#024B79]';
    }

    return $base . ' border border-[#024B79] bg-[#024B79] text-white';
}

function matrix_get_content_pdf_icon_svg()
{
    return '<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M11.5 1.5H4.5C3.67 1.5 3 2.17 3 3V17C3 17.83 3.67 18.5 4.5 18.5H15.5C16.33 18.5 17 17.83 17 17V6.5L11.5 1.5Z" fill="#E53935"/><path d="M11 2V7H16" fill="#FFCDD2"/><path d="M6.5 11H13.5M6.5 14H11" stroke="white" stroke-width="1.25" stroke-linecap="round"/></svg>';
}

function matrix_get_content_document_link_class_names()
{
    return 'btn inline-flex w-fit items-center gap-2 font-primary text-[16px] font-semibold leading-[24px] text-[#1E244B] transition-colors duration-200 hover:text-[#024B79] focus-visible:text-[#024B79]';
}
