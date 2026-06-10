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

function matrix_resolve_content_image_height_mode($value)
{
    $value = is_string($value) ? trim($value) : '';

    if ($value === 'fixed_min') {
        return 'fixed_min';
    }

    return 'match_text';
}

function matrix_get_content_grid_class_names($image_height_mode)
{
    $classes = 'grid grid-cols-1 gap-10 items-start w-full lg:grid-cols-2 lg:gap-8';

    if (matrix_resolve_content_image_height_mode($image_height_mode) === 'match_text') {
        return $classes . ' lg:items-stretch';
    }

    return $classes;
}

function matrix_get_content_image_wrapper_class_names($image_column_class, $image_height_mode)
{
    $classes = [
        $image_column_class,
        'order-2',
        'flex',
        'justify-center',
        'lg:justify-start',
    ];

    if (matrix_resolve_content_image_height_mode($image_height_mode) === 'match_text') {
        $classes[] = 'lg:h-full';
    }

    return implode(' ', array_filter($classes));
}

function matrix_get_content_image_figure_class_names($image_height_mode)
{
    $classes = ['w-full', 'lg:max-w-[502px]'];

    if (matrix_resolve_content_image_height_mode($image_height_mode) === 'match_text') {
        $classes[] = 'lg:h-full';
    }

    return implode(' ', $classes);
}

function matrix_get_content_image_class_names($image_height_mode)
{
    $classes = 'h-[212px] w-full rounded-[8px] object-cover lg:h-auto';

    if (matrix_resolve_content_image_height_mode($image_height_mode) === 'fixed_min') {
        return $classes . ' lg:min-h-[19.5rem]';
    }

    return $classes . ' lg:h-full lg:min-h-0';
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
    return 'btn inline-flex h-[36px] w-fit items-center justify-center whitespace-nowrap rounded-[6px] border border-[#024B79] bg-transparent px-3 text-[14px] font-medium leading-[24px] text-[#024B79] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]';
}

function matrix_get_content_pdf_icon_svg()
{
    return '<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M11.5 1.5H4.5C3.67 1.5 3 2.17 3 3V17C3 17.83 3.67 18.5 4.5 18.5H15.5C16.33 18.5 17 17.83 17 17V6.5L11.5 1.5Z" fill="#E53935"/><path d="M11 2V7H16" fill="#FFCDD2"/><path d="M6.5 11H13.5M6.5 14H11" stroke="white" stroke-width="1.25" stroke-linecap="round"/></svg>';
}

function matrix_get_content_document_link_class_names()
{
    return 'btn inline-flex w-fit items-center gap-2 font-primary text-[16px] font-semibold leading-[24px] text-[#1E244B] transition-colors duration-200 hover:text-[#024B79] focus-visible:text-[#024B79]';
}
