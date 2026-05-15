<?php

require_once dirname(__DIR__, 2) . '/inc/webinars-archive-functions.php';

test('webinars archive resolves request state against allowed webinar types', function () {
    expect(function_exists('matrix_resolve_webinars_archive_state'))->toBeTrue();

    $state = matrix_resolve_webinars_archive_state(
        [
            'webinar_type' => 'events',
            'webinar_search' => '  gp webinar  ',
            'webinar_page' => '3',
        ],
        ['events', 'webinars'],
        10
    );

    expect($state['type'])->toBe('events')
        ->and($state['search'])->toBe('gp webinar')
        ->and($state['paged'])->toBe(3)
        ->and($state['posts_per_page'])->toBe(10);
});

test('webinars archive falls back to all when the type is not allowed', function () {
    $state = matrix_resolve_webinars_archive_state(
        [
            'webinar_type' => 'other',
            'webinar_search' => '',
            'webinar_page' => '0',
        ],
        ['events', 'webinars'],
        10
    );

    expect($state['type'])->toBe('all')
        ->and($state['search'])->toBe('')
        ->and($state['paged'])->toBe(1)
        ->and($state['posts_per_page'])->toBe(10);
});

test('webinars archive builds query args for type search and paging', function () {
    expect(function_exists('matrix_build_webinars_archive_query_args'))->toBeTrue();

    $args = matrix_build_webinars_archive_query_args([
        'type' => 'webinars',
        'search' => 'mental health act',
        'paged' => 2,
        'posts_per_page' => 10,
    ], [
        'webinars' => 31,
        'events' => 32,
    ]);

    expect($args['post_type'])->toBe('webinars')
        ->and($args['post_status'])->toBe('publish')
        ->and($args['posts_per_page'])->toBe(10)
        ->and($args['paged'])->toBe(2)
        ->and($args['s'])->toBe('mental health act')
        ->and($args['tax_query'][0]['taxonomy'])->toBe('webinar_type')
        ->and($args['tax_query'][0]['terms'])->toBe([31]);
});

test('webinars archive preserves filters in pagination urls', function () {
    expect(function_exists('matrix_build_webinars_archive_page_url'))->toBeTrue();

    $url = matrix_build_webinars_archive_page_url(
        'http://localhost:10034/webinars/',
        [
            'type' => 'events',
            'search' => 'gp',
            'paged' => 1,
        ],
        4
    );

    expect($url)->toContain('webinar_type=events')
        ->and($url)->toContain('webinar_search=gp')
        ->and($url)->toContain('webinar_page=4');
});

test('webinars archive returns term-based card styles', function () {
    expect(function_exists('matrix_get_webinars_archive_card_theme'))->toBeTrue();

    expect(matrix_get_webinars_archive_card_theme('events')['card_background'])->toBe('#E4F4D6')
        ->and(matrix_get_webinars_archive_card_theme('webinars')['card_background'])->toBe('#E9E2F7')
        ->and(matrix_get_webinars_archive_card_theme('all')['card_background'])->toBe('#E9E2F7');
});
