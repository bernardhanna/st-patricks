<?php

function matrix_normalize_video_showcase_link($link)
{
    if (! is_array($link) || empty($link['url'])) {
        return null;
    }

    $title = trim((string) ($link['title'] ?? ''));

    return [
        'url' => (string) $link['url'],
        'title' => $title !== '' ? $title : 'Learn more',
        'target' => trim((string) ($link['target'] ?? '')) ?: '_self',
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

function matrix_normalize_video_showcase_slides($rows)
{
    $slides = [];

    foreach ((array) $rows as $row) {
        if (! is_array($row)) {
            continue;
        }

        $poster_image = is_array($row['poster_image'] ?? null) ? $row['poster_image'] : null;
        $poster_url = trim((string) ($poster_image['url'] ?? ''));
        $media = matrix_resolve_video_showcase_media(
            (string) ($row['video_source_type'] ?? 'embed_url'),
            (string) ($row['video_embed_url'] ?? ''),
            $row['local_video_file'] ?? null
        );

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
