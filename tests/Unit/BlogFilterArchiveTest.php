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

test('blog post category badge colors match figma news and events tokens', function () {
    expect(matrix_get_blog_post_category_badge_colors('News', 'news'))->toBe([
        'background' => '#F4978E',
        'text' => '#08284B',
    ])->and(matrix_get_blog_post_category_badge_colors('Events', 'events'))->toBe([
        'background' => '#C3DBAE',
        'text' => '#08284B',
    ])->and(matrix_get_blog_filter_archive_chip_button_class_names())->toContain('h-[36px]')
        ->and(matrix_get_blog_filter_archive_chip_button_class_names())->toContain('px-6')
        ->and(matrix_get_blog_filter_archive_chip_button_class_names())->toContain('shrink-0');
});

test('blog filter archive pagination collapses large page counts', function () {
    expect(matrix_build_blog_filter_archive_pagination_items(15, 32))->toBe([
        ['type' => 'page', 'page' => 1],
        ['type' => 'ellipsis'],
        ['type' => 'page', 'page' => 14],
        ['type' => 'page', 'page' => 15],
        ['type' => 'page', 'page' => 16],
        ['type' => 'ellipsis'],
        ['type' => 'page', 'page' => 32],
    ]);
});

test('filter archive controls stack search above filters on mobile', function () {
    expect(matrix_get_filter_archive_controls_class_names())->toContain('flex-col-reverse')
        ->and(matrix_get_filter_archive_controls_class_names())->toContain('lg:items-center')
        ->and(matrix_get_filter_archive_controls_class_names('start'))->toContain('lg:items-start');
});

test('filter archive card grid uses one column on the smallest screens', function () {
    expect(matrix_get_filter_archive_card_grid_class_names())->toContain('grid-cols-1')
        ->and(matrix_get_filter_archive_card_grid_class_names())->toContain('sm:grid-cols-2')
        ->and(matrix_get_filter_archive_card_grid_class_names())->toContain('xl:grid-cols-3');
});

test('filter archive pagination active page uses primary blue dark with white text', function () {
    expect(matrix_build_filter_archive_pagination_active_inline_style())->toBe('border-color: #024B79; background-color: #024B79; color: #FFFFFF;');
});

test('filter archive pagination uses compact mobile item sets', function () {
    $item_sets = matrix_build_filter_archive_pagination_item_sets(15, 32);

    expect($item_sets['mobile'])->toBe([
        ['type' => 'page', 'page' => 15],
    ])->and($item_sets['desktop'])->toBe([
        ['type' => 'page', 'page' => 1],
        ['type' => 'ellipsis'],
        ['type' => 'page', 'page' => 14],
        ['type' => 'page', 'page' => 15],
        ['type' => 'page', 'page' => 16],
        ['type' => 'ellipsis'],
        ['type' => 'page', 'page' => 32],
    ]);
});

test('filter archive cards use compact mobile typography tokens', function () {
    expect(matrix_get_filter_archive_card_title_class_names())->toContain('text-[1.125rem]')
        ->and(matrix_get_filter_archive_card_title_class_names())->toContain('tracking-[-0.00675rem]')
        ->and(matrix_get_filter_archive_card_date_class_names())->toContain('text-[0.875rem]')
        ->and(matrix_get_filter_archive_card_date_class_names())->toContain('tracking-[-0.00525rem]')
        ->and(matrix_get_filter_archive_card_excerpt_class_names())->toContain('text-[0.75rem]')
        ->and(matrix_get_filter_archive_card_excerpt_class_names())->toContain('leading-4');
});
