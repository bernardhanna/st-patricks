<?php

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
