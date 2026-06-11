<?php

require_once dirname(__DIR__, 2) . '/inc/not-found-functions.php';
require_once dirname(__DIR__, 2) . '/inc/useful-links-functions.php';
require_once dirname(__DIR__, 2) . '/inc/search-results-functions.php';

test('not found heading defaults to search-style split heading', function () {
    $heading = matrix_get_not_found_heading_data('');

    expect($heading['prefix'])->toBe("We couldn\u{2019}t find a match for")
        ->and($heading['query'])->toBe('this page')
        ->and($heading['plain'])->toBe('');
});

test('not found heading uses plain custom title when provided', function () {
    $heading = matrix_get_not_found_heading_data('Custom 404 title');

    expect($heading['prefix'])->toBe('Custom 404 title')
        ->and($heading['query'])->toBe('')
        ->and($heading['plain'])->toBe('Custom 404 title');
});

test('not found page view model includes heading and useful links', function () {
    $view = matrix_prepare_not_found_page();

    expect($view['heading']['query'])->toBe('this page')
        ->and($view['useful_links']['variant'] ?? '')->toBe('search')
        ->and($view)->not->toHaveKey('breadcrumb_items');
});

test('not found acf links normalize to useful links rows', function () {
    $rows = matrix_normalize_not_found_acf_links([
        [
            'link_data' => [
                'title' => 'Contact Us',
                'url' => 'https://example.com/contact/',
                'target' => '',
            ],
        ],
    ]);

    expect($rows)->toHaveCount(1)
        ->and($rows[0]['link']['title'])->toBe('Contact Us');
});
