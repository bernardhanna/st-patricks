<?php

require_once dirname(__DIR__, 2) . '/inc/blog-filter-archive-functions.php';

test('blog filter archive resolves request state against allowed categories', function () {
    expect(function_exists('matrix_resolve_blog_filter_archive_state'))->toBeTrue();

    $state = matrix_resolve_blog_filter_archive_state(
        [
            'blog_category' => 'events',
            'blog_search' => '  mental health  ',
            'blog_page' => '3',
        ],
        ['news', 'events'],
        12
    );

    expect($state['category'])->toBe('events')
        ->and($state['search'])->toBe('mental health')
        ->and($state['paged'])->toBe(3)
        ->and($state['posts_per_page'])->toBe(12);
});

test('blog filter archive falls back to all when category is not allowed', function () {
    $state = matrix_resolve_blog_filter_archive_state(
        [
            'blog_category' => 'projects',
            'blog_search' => '',
            'blog_page' => '0',
        ],
        ['news', 'events'],
        12
    );

    expect($state['category'])->toBe('all')
        ->and($state['search'])->toBe('')
        ->and($state['paged'])->toBe(1);
});

test('blog filter archive builds query args for category search and paging', function () {
    expect(function_exists('matrix_build_blog_filter_archive_query_args'))->toBeTrue();

    $args = matrix_build_blog_filter_archive_query_args([
        'category' => 'events',
        'search' => 'anxiety',
        'paged' => 2,
        'posts_per_page' => 12,
    ], [
        'events' => 17,
        'news' => 19,
    ]);

    expect($args['post_type'])->toBe('post')
        ->and($args['post_status'])->toBe('publish')
        ->and($args['posts_per_page'])->toBe(12)
        ->and($args['paged'])->toBe(2)
        ->and($args['s'])->toBe('anxiety')
        ->and($args['category__in'])->toBe([17]);
});

test('blog filter archive preserves filters in pagination urls', function () {
    expect(function_exists('matrix_build_blog_filter_archive_page_url'))->toBeTrue();

    $url = matrix_build_blog_filter_archive_page_url(
        'http://localhost:10034/flexi/',
        [
            'category' => 'news',
            'search' => 'service user',
            'paged' => 1,
        ],
        4
    );

    expect($url)->toContain('blog_category=news')
        ->and($url)->toContain('blog_search=service+user')
        ->and($url)->toContain('blog_page=4');
});
