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

test('video showcase derives a youtube poster when none is provided', function () {
    $slides = matrix_normalize_video_showcase_slides([
        [
            'poster_image' => [],
            'video_source_type' => 'embed_url',
            'video_embed_url' => 'https://www.youtube.com/watch?v=9fTMEZW03lg',
            'local_video_file' => null,
            'caption' => '',
            'cta_link' => [],
        ],
    ]);

    expect($slides)->toHaveCount(1)
        ->and($slides[0]['poster_image']['url'])->toBe('https://i.ytimg.com/vi/9fTMEZW03lg/hqdefault.jpg')
        ->and($slides[0]['video_type'])->toBe('youtube');
});

test('video showcase surface size helpers resolve small dimensions', function () {
    expect(matrix_get_video_showcase_surface_width_class('feature_single', 'small'))->toBe('max-w-[48.625rem]')
        ->and(matrix_get_video_showcase_surface_height_class('feature_single', 'small'))->toBe('h-[24.5rem] max-h-[24.5rem]')
        ->and(matrix_get_video_showcase_caption_width_class('feature_slider', 'small'))->toBe('max-w-[48.625rem]')
        ->and(matrix_get_video_showcase_heading_wrap_width_class('compact_slider', 'small'))->toBe('max-w-[48.625rem]')
        ->and(matrix_get_video_showcase_surface_width_class('feature_single', 'default'))->toBe('max-w-[1018px]');
});

test('video showcase text width full option spans the content area', function () {
    expect(matrix_get_video_showcase_heading_wrap_width_class('feature_single', 'default', 'full'))->toBe('w-full max-w-[1018px]')
        ->and(matrix_get_video_showcase_caption_width_class('feature_slider', 'default', 'full'))->toBe('w-full max-w-[1018px]')
        ->and(matrix_get_video_showcase_heading_wrap_width_class('feature_single', 'default', 'default'))->toBe('max-w-[680px]');
});

test('video showcase vertical padding can omit large desktop spacing', function () {
    require_once dirname(__DIR__, 2) . '/inc/content-section-functions.php';

    expect(matrix_resolve_video_showcase_vertical_padding('standard'))->toBe('standard')
        ->and(matrix_resolve_video_showcase_vertical_padding('bottom_only'))->toBe('bottom_only')
        ->and(matrix_resolve_video_showcase_vertical_padding('default'))->toBe('default')
        ->and(matrix_resolve_video_showcase_vertical_padding(''))->toBe('default');

    expect(matrix_get_video_showcase_section_wrapper_class_names('default'))->toContain('lg:py-[100px]')
        ->and(matrix_get_video_showcase_section_wrapper_class_names('standard'))->not->toContain('lg:py-[100px]')
        ->and(matrix_get_video_showcase_section_wrapper_class_names('standard'))->toContain('py-12')
        ->and(matrix_get_video_showcase_section_wrapper_class_names('bottom_only'))->toContain('lg:pt-0')
        ->and(matrix_get_video_showcase_section_wrapper_class_names('bottom_only'))->toContain('lg:pb-[100px]')
        ->and(matrix_get_video_showcase_section_wrapper_class_names('bottom_only'))->not->toContain('lg:py-[100px]');
});
