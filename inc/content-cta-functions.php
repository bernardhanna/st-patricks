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
        'target' => (string) ($link['target'] ?? '_self'),
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
