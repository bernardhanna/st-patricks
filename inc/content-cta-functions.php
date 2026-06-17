<?php

function matrix_resolve_content_cta_layout_style($layout_style = '')
{
    return trim((string) $layout_style) === 'image_background' ? 'image_background' : 'default';
}

function matrix_content_cta_uses_image_background($layout_style, $background_image): bool
{
    return matrix_resolve_content_cta_layout_style($layout_style) === 'image_background'
        && (bool) $background_image;
}

function matrix_resolve_content_cta_background_image_opacity($value)
{
    $value = trim((string) $value);

    if (in_array($value, ['0', '25', '50', '75', '100'], true)) {
        return (int) $value;
    }

    return 50;
}

function matrix_get_content_cta_background_image_opacity_style($opacity): string
{
    $opacity = matrix_resolve_content_cta_background_image_opacity($opacity);
    $alpha = number_format($opacity / 100, 2, '.', '');

    return 'opacity: ' . $alpha . ';';
}

function matrix_get_content_cta_tint_background_style($background_color): string
{
    $color = trim((string) $background_color);

    if ($color === '') {
        $color = '#F1F3DE';
    }

    return matrix_get_faq_background_style($color);
}

function matrix_get_content_cta_background_image_overlay_style(string $overlay_color, $overlay_opacity = 50): string
{
    if (! function_exists('matrix_get_content_background_image_overlay_style')) {
        return '';
    }

    return matrix_get_content_background_image_overlay_style($overlay_color, $overlay_opacity);
}

function matrix_get_content_cta_wrapper_class_names(string $layout_style = 'default'): string
{
    if (matrix_resolve_content_cta_layout_style($layout_style) === 'image_background') {
        return 'relative z-[1] mx-auto flex w-full max-w-[1040px] flex-col gap-8 px-4 py-12 lg:px-0 lg:py-[100px]';
    }

    return 'mx-auto flex w-full max-w-[1018px] flex-col gap-8 py-12 max-xl:px-5 lg:gap-8 lg:py-[100px]';
}

function matrix_get_content_cta_heading_class_names(string $layout_style, string $heading_class): string
{
    $base = 'font-primary font-semibold ' . trim($heading_class);

    if (matrix_resolve_content_cta_layout_style($layout_style) === 'image_background') {
        return $base . ' text-[24px] leading-[28px] tracking-[-0.18px] lg:text-[32px] lg:leading-[40px] lg:tracking-[-0.384px]';
    }

    return $base . ' text-[24px] leading-[28px] tracking-[-0.18px] lg:text-[30px] lg:leading-[36px] lg:tracking-[-0.225px]';
}

function matrix_get_content_cta_body_class_names(string $layout_style, string $body_classes): string
{
    $base = 'wp_editor [&_p:last-child]:mb-0 [&_p]:font-primary ' . trim($body_classes);

    if (matrix_resolve_content_cta_layout_style($layout_style) === 'image_background') {
        return $base . ' w-full max-w-none lg:max-w-[656px] [&_p]:text-base [&_p]:font-bold [&_p]:leading-7 [&_p]:text-[#08284B]';
    }

    return $base . ' [&_p]:font-normal max-w-[720px] [&_p]:text-[18px] [&_p]:leading-[28px]';
}

function matrix_get_content_cta_content_row_class_names(string $layout_style = 'default'): string
{
    $classes = 'flex w-full flex-col gap-8 lg:flex-row lg:items-center';

    if (matrix_resolve_content_cta_layout_style($layout_style) !== 'image_background') {
        $classes .= ' lg:justify-between';
    }

    return $classes;
}

function matrix_get_content_cta_button_class_names(string $layout_style, string $color_scheme = 'default'): string
{
    $theme = matrix_get_content_cta_theme_classes($color_scheme);
    $base = 'btn focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2';

    if (matrix_resolve_content_cta_layout_style($layout_style) === 'image_background') {
        if (matrix_resolve_content_cta_color_scheme($color_scheme) === 'inverse') {
            return $base . ' inline-flex h-9 w-fit items-center justify-center gap-2.5 whitespace-nowrap rounded-[6px] px-8 text-[14px] font-medium leading-6 bg-white text-[#08284B] hover:bg-[#F1F8F9] focus-visible:outline-white';
        }

        return $base . ' inline-flex h-9 w-fit items-center justify-center gap-2.5 whitespace-nowrap rounded-[6px] px-8 text-[14px] font-medium leading-6 text-white bg-[#024B79] hover:bg-[#013a5f] focus-visible:outline-[#024B79]';
    }

    return $base . ' inline-flex h-[36px] w-fit items-center justify-center whitespace-nowrap rounded-[6px] px-3 text-[14px] font-medium leading-[24px] ' . $theme['button'];
}

function matrix_normalize_content_cta_link($link)
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

function matrix_resolve_content_cta_color_scheme($scheme = '')
{
    return trim((string) $scheme) === 'inverse' ? 'inverse' : 'default';
}

/**
 * @return array{heading: string, body: string, accent: string, button: string}
 */
function matrix_get_content_cta_theme_classes(string $color_scheme = 'default'): array
{
    if (matrix_resolve_content_cta_color_scheme($color_scheme) === 'inverse') {
        return [
            'heading' => 'text-white',
            'body' => '[&_p]:text-white/90',
            'accent' => 'bg-[#6FC9C0]',
            'button' => 'bg-white text-[#08284B] hover:bg-[#F1F8F9] focus-visible:outline-white',
        ];
    }

    return [
        'heading' => 'text-[#1E244B]',
        'body' => '[&_p]:text-[#08284B]',
        'accent' => 'bg-[#6FC9C0]',
        'button' => 'bg-[#024B79] text-white hover:bg-[#013a5f] focus-visible:outline-[#024B79]',
    ];
}

function matrix_get_content_cta_background_style($background_type, $background_color, $background_gradient)
{
    $background_type = trim((string) $background_type);

    if ($background_type === 'gradient') {
        $gradient = trim((string) $background_gradient);

        if ($gradient === '') {
            $gradient = 'linear-gradient(278deg, #F8F6F3 3.24%, #F5F6ED 90.88%)';
        }

        return matrix_get_faq_background_style($gradient);
    }

    $color = trim((string) $background_color);

    if ($color === '') {
        $color = '#E9E2F7';
    }

    return matrix_get_faq_background_style($color);
}
