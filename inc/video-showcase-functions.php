<?php

function matrix_normalize_video_showcase_link($link)
{
    if (! is_array($link) || empty($link['url'])) {
        return null;
    }

    $title = trim((string) ($link['title'] ?? ''));
    $url = (string) $link['url'];

    return [
        'url' => $url,
        'title' => $title !== '' ? $title : 'Learn more',
        'target' => matrix_normalize_link_target($url, (string) ($link['target'] ?? '')),
    ];
}

function matrix_resolve_video_showcase_media($video_source_type, $video_embed_url, $local_video_file)
{
    $source_type = $video_source_type === 'local_file' ? 'local_file' : 'embed_url';
    $video_url = '';
    $video_type = 'none';
    $resolved_embed_url = '';

    if ($source_type === 'local_file' && is_array($local_video_file) && ! empty($local_video_file['url'])) {
        $video_url = (string) $local_video_file['url'];
    } elseif ($source_type === 'embed_url' && trim((string) $video_embed_url) !== '') {
        $video_url = trim((string) $video_embed_url);
    }

    if ($video_url !== '') {
        if (preg_match('/\.(mp4|webm|ogg)(\?.*)?$/i', $video_url)) {
            $video_type = 'local';
        } elseif (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/shorts\/)([A-Za-z0-9_-]{6,})/i', $video_url, $youtube_match)) {
            $video_type = 'youtube';
            $resolved_embed_url = 'https://www.youtube.com/embed/' . rawurlencode($youtube_match[1]) . '?rel=0&modestbranding=1';
        } elseif (preg_match('/vimeo\.com\/(?:video\/)?([0-9]+)/i', $video_url, $vimeo_match)) {
            $video_type = 'vimeo';
            $resolved_embed_url = 'https://player.vimeo.com/video/' . rawurlencode($vimeo_match[1]) . '?title=0&byline=0&portrait=0';
        } else {
            $video_type = 'external';
        }
    }

    return [
        'video_url' => $video_url,
        'video_type' => $video_type,
        'video_embed_url' => $resolved_embed_url,
    ];
}

function matrix_resolve_video_showcase_poster_url($poster_image, array $media): string
{
    $poster_url = '';

    if (is_array($poster_image) && ! empty($poster_image['url'])) {
        $poster_url = trim((string) $poster_image['url']);
    }

    if ($poster_url === '' && ($media['video_type'] ?? '') === 'youtube') {
        $video_url = trim((string) ($media['video_url'] ?? ''));

        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/shorts\/)([A-Za-z0-9_-]{6,})/i', $video_url, $youtube_match)) {
            $poster_url = 'https://i.ytimg.com/vi/' . $youtube_match[1] . '/hqdefault.jpg';
        }
    }

    return $poster_url;
}

function matrix_normalize_video_showcase_slides($rows)
{
    $slides = [];

    foreach ((array) $rows as $row) {
        if (! is_array($row)) {
            continue;
        }

        $poster_image = is_array($row['poster_image'] ?? null) ? $row['poster_image'] : null;
        $media = matrix_resolve_video_showcase_media(
            (string) ($row['video_source_type'] ?? 'embed_url'),
            (string) ($row['video_embed_url'] ?? ''),
            $row['local_video_file'] ?? null
        );
        $poster_url = matrix_resolve_video_showcase_poster_url($poster_image, $media);

        if ($poster_url === '' || $media['video_type'] === 'none') {
            continue;
        }

        $slides[] = [
            'poster_image' => [
                'url' => $poster_url,
                'alt' => trim((string) ($poster_image['alt'] ?? '')) ?: 'Video poster image',
                'title' => trim((string) ($poster_image['title'] ?? '')) ?: 'Video poster image',
            ],
            'video_url' => $media['video_url'],
            'video_type' => $media['video_type'],
            'video_embed_url' => $media['video_embed_url'],
            'caption' => (string) ($row['caption'] ?? $row['description'] ?? ''),
            'cta_link' => matrix_normalize_video_showcase_link($row['cta_link'] ?? null),
        ];
    }

    return $slides;
}

function matrix_get_video_showcase_section_background_style($background_value, $fallback = '#FBFAF7')
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

function matrix_resolve_video_showcase_layout_style($layout_style)
{
    $layout_style = (string) $layout_style;

    if (in_array($layout_style, ['feature_single', 'feature_slider', 'compact_slider'], true)) {
        return $layout_style;
    }

    return 'feature_single';
}

function matrix_resolve_video_showcase_surface_size($video_surface_size)
{
    return (string) $video_surface_size === 'small' ? 'small' : 'default';
}

function matrix_resolve_video_showcase_text_width($text_max_width)
{
    return (string) $text_max_width === 'full' ? 'full' : 'default';
}

function matrix_get_video_showcase_text_width_class($text_max_width = 'default')
{
    if (matrix_resolve_video_showcase_text_width($text_max_width) === 'full') {
        return 'w-full max-w-[1018px]';
    }

    return '';
}

function matrix_get_video_showcase_surface_width_class($layout_style, $video_surface_size = 'default')
{
    if (matrix_resolve_video_showcase_surface_size($video_surface_size) === 'small') {
        return 'max-w-[48.625rem]';
    }

    return matrix_resolve_video_showcase_layout_style($layout_style) === 'compact_slider'
        ? 'max-w-[780px]'
        : 'max-w-[1018px]';
}

function matrix_get_video_showcase_surface_height_class($layout_style, $video_surface_size = 'default')
{
    if (matrix_resolve_video_showcase_surface_size($video_surface_size) === 'small') {
        return 'h-[24.5rem] max-h-[24.5rem]';
    }

    return matrix_resolve_video_showcase_layout_style($layout_style) === 'compact_slider'
        ? 'h-[220px] xs:h-[260px] md:h-[320px] lg:h-[380px]'
        : 'h-[240px] xs:h-[300px] md:h-[400px] lg:h-[540px]';
}

function matrix_get_video_showcase_caption_width_class($layout_style, $video_surface_size = 'default', $text_max_width = 'default')
{
    $full_width_class = matrix_get_video_showcase_text_width_class($text_max_width);

    if ($full_width_class !== '') {
        return $full_width_class;
    }

    if (matrix_resolve_video_showcase_surface_size($video_surface_size) === 'small') {
        return 'max-w-[48.625rem]';
    }

    return matrix_resolve_video_showcase_layout_style($layout_style) === 'compact_slider'
        ? 'max-w-[780px]'
        : 'max-w-[1018px]';
}

function matrix_get_video_showcase_heading_wrap_width_class($layout_style, $video_surface_size = 'default', $text_max_width = 'default')
{
    $full_width_class = matrix_get_video_showcase_text_width_class($text_max_width);

    if ($full_width_class !== '') {
        return $full_width_class;
    }

    if (matrix_resolve_video_showcase_surface_size($video_surface_size) === 'small') {
        return 'max-w-[48.625rem]';
    }

    return matrix_resolve_video_showcase_layout_style($layout_style) === 'compact_slider'
        ? 'max-w-[780px]'
        : 'max-w-[680px]';
}

function matrix_resolve_video_showcase_vertical_padding($vertical_padding)
{
    return matrix_resolve_section_vertical_padding($vertical_padding);
}

function matrix_get_video_showcase_section_wrapper_class_names($vertical_padding = 'default')
{
    $padding_classes = matrix_get_section_vertical_padding_classes(
        matrix_resolve_video_showcase_vertical_padding($vertical_padding),
        'lg:py-[100px]'
    );

    return implode(' ', [
        'mx-auto',
        'flex',
        'w-full',
        'max-w-[1018px]',
        'flex-col',
        'max-xl:px-5',
        $padding_classes,
    ]);
}
