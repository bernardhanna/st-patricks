<?php

require_once dirname(__DIR__, 2) . '/inc/video-showcase-functions.php';

test('video showcase slides are normalized for embed and local sources', function () {
    expect(function_exists('matrix_normalize_video_showcase_slides'))->toBeTrue();

    $slides = matrix_normalize_video_showcase_slides([
        [
            'poster_image' => [
                'url' => 'https://example.com/poster-one.jpg',
                'alt' => 'Poster one',
                'title' => 'Poster one title',
            ],
            'video_source_type' => 'embed_url',
            'video_embed_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'local_video_file' => null,
            'caption' => '<p>Intro video.</p>',
            'cta_link' => [
                'url' => '/watch',
                'title' => 'Watch now',
                'target' => '',
            ],
        ],
        [
            'poster_image' => [
                'url' => 'https://example.com/poster-two.jpg',
                'alt' => 'Poster two',
                'title' => 'Poster two title',
            ],
            'video_source_type' => 'local_file',
            'video_embed_url' => '',
            'local_video_file' => [
                'url' => 'https://example.com/video.mp4',
                'title' => 'Local mp4',
            ],
            'caption' => '<p>Local file video.</p>',
            'cta_link' => [],
        ],
        [
            'poster_image' => [],
            'video_source_type' => 'embed_url',
            'video_embed_url' => '',
            'local_video_file' => [],
            'caption' => '',
            'cta_link' => [],
        ],
    ]);

    expect($slides)->toHaveCount(2)
        ->and($slides[0]['video_type'])->toBe('youtube')
        ->and($slides[0]['video_embed_url'])->toBe('https://www.youtube.com/embed/dQw4w9WgXcQ?rel=0&modestbranding=1')
        ->and($slides[0]['cta_link'])->toBe([
            'url' => '/watch',
            'title' => 'Watch now',
            'target' => '_self',
        ])
        ->and($slides[1]['video_type'])->toBe('local')
        ->and($slides[1]['video_url'])->toBe('https://example.com/video.mp4');
});
