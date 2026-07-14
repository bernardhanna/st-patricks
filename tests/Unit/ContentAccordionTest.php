<?php

require_once dirname(__DIR__, 2) . '/inc/link-functions.php';
require_once dirname(__DIR__, 2) . '/inc/content-section-functions.php';
require_once dirname(__DIR__, 2) . '/inc/content-accordion-functions.php';

test('content accordion items normalize rows and choose the first flagged item as open', function () {
    expect(function_exists('matrix_normalize_content_accordion_items'))->toBeTrue();

    $result = matrix_normalize_content_accordion_items([
        [
            'title' => 'Travel and parking',
            'starts_open' => 0,
            'content_rows' => [
                [
                    'icon' => [
                        'url' => 'https://example.com/icon-one.svg',
                        'alt' => 'Car icon',
                    ],
                    'content' => '<p>Directions content.</p>',
                ],
            ],
        ],
        [
            'title' => 'Visiting hours',
            'starts_open' => 1,
            'content_rows' => [
                [
                    'icon' => [
                        'url' => 'https://example.com/icon-two.svg',
                        'alt' => 'Clock icon',
                    ],
                    'content' => '<p>Visiting hours content.</p>',
                ],
            ],
        ],
    ]);

    expect($result['initial_open_index'])->toBe(1)
        ->and($result['items'])->toHaveCount(2)
        ->and($result['items'][1]['rows'][0]['icon']['alt'])->toBe('Clock icon')
        ->and($result['items'][1]['rows'][0]['content'])->toBe('<p>Visiting hours content.</p>');
});

test('content accordion layout style resolves directions page variant', function () {
    expect(matrix_resolve_content_accordion_layout_style(''))->toBe('default')
        ->and(matrix_resolve_content_accordion_layout_style('directions_page'))->toBe('directions_page')
        ->and(matrix_resolve_content_accordion_layout_style('policies_page'))->toBe('policies_page')
        ->and(matrix_resolve_content_accordion_layout_style('unknown'))->toBe('default');
});

test('content accordion policies page normalizes pdf grid rows', function () {
    $result = matrix_normalize_content_accordion_items([
        [
            'title' => 'Placeholder Declarations and charters',
            'starts_open' => 1,
            'content_rows' => [
                [
                    'row_type' => 'text',
                    'content' => '<p>Intro copy.</p>',
                ],
                [
                    'row_type' => 'pdf_grid',
                    'pdf_documents' => [
                        [
                            'title' => 'Charter of Patient and Family Rights and Responsibilities',
                            'document_link' => [
                                'title' => 'PDF opens in a new tab',
                                'url' => 'https://example.com/sample.pdf',
                                'target' => '_blank',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ], 'policies_page');

    expect($result['items'])->toHaveCount(1)
        ->and($result['items'][0]['rows'][0]['type'])->toBe('text')
        ->and($result['items'][0]['rows'][1]['type'])->toBe('pdf_grid')
        ->and($result['items'][0]['rows'][1]['documents'][0]['title'])
        ->toBe('Charter of Patient and Family Rights and Responsibilities');
});

test('content accordion policies page normalizes link cards and external links', function () {
    $result = matrix_normalize_content_accordion_items([
        [
            'title' => 'Placeholder Strategies and reports',
            'starts_open' => 1,
            'content_rows' => [
                [
                    'row_type' => 'link_cards',
                    'link_cards' => [
                        [
                            'title' => 'If it needs a title or description',
                            'button_link' => [
                                'title' => 'External Privacy Notice',
                                'url' => 'https://example.com/privacy',
                                'target' => '_blank',
                            ],
                        ],
                    ],
                ],
                [
                    'row_type' => 'external_links',
                    'external_links' => [
                        [
                            'title' => 'External link only - no title',
                            'link' => [
                                'title' => 'External link only - no title',
                                'url' => 'https://example.com/external',
                                'target' => '_blank',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ], 'policies_page');

    expect($result['items'][0]['rows'][0]['type'])->toBe('link_cards')
        ->and($result['items'][0]['rows'][0]['cards'][0]['link']['title'])->toBe('External Privacy Notice')
        ->and($result['items'][0]['rows'][1]['type'])->toBe('external_links')
        ->and($result['items'][0]['rows'][1]['links'][0]['title'])->toBe('External link only - no title');
});

test('content accordion preserves icon keys for directions rows', function () {
    $result = matrix_normalize_content_accordion_items([
        [
            'title' => 'Getting to St Patrick\'s University Hospital',
            'starts_open' => 1,
            'content_rows' => [
                [
                    'icon_key' => 'car',
                    'content' => '<p><strong>Car park:</strong> Paid parking available.</p>',
                ],
            ],
        ],
    ]);

    expect($result['items'][0]['rows'][0]['icon_key'])->toBe('car');
});

test('content accordion directions fallback icons use centered car and bus artwork', function () {
    $car_icon = matrix_get_content_accordion_icon_svg('car');
    $bus_icon = matrix_get_content_accordion_icon_svg('bus');

    expect($car_icon)->toContain('cx="7.5" cy="16.5"')
        ->and($car_icon)->toContain('cx="16.5" cy="16.5"')
        ->and($bus_icon)->toContain('x="7" y="5.5" width="10" height="11"')
        ->and($bus_icon)->toContain('cx="8.5" cy="17.5"')
        ->and($bus_icon)->toContain('cx="15.5" cy="17.5"');
});

test('content accordion falls back to the first item when none are flagged open', function () {
    $result = matrix_normalize_content_accordion_items([
        [
            'title' => 'Getting here',
            'starts_open' => 0,
            'content_rows' => [
                [
                    'icon' => [],
                    'content' => '<p>Fallback content.</p>',
                ],
            ],
        ],
        [
            'title' => 'What to bring',
            'starts_open' => 0,
            'content_rows' => [
                [
                    'icon' => [],
                    'content' => '<p>Checklist content.</p>',
                ],
            ],
        ],
    ]);

    expect($result['initial_open_index'])->toBe(0);
});

test('directions page accordion layout stacks icon above text on mobile', function () {
    $config = matrix_get_content_accordion_layout_config('directions_page');

    expect($config['row_classes'])->toContain('flex-col')
        ->and($config['row_classes'])->toContain('lg:flex-row')
        ->and($config['icon_image_classes'])->toContain('h-12 w-12')
        ->and($config['content_classes'])->toContain('[&_p]:text-[16px]');
});

test('content accordion vertical padding supports default and bottom-only spacing', function () {
    expect(matrix_get_content_accordion_vertical_padding_classes('default', 'policies_page'))
        ->toBe('py-12 xl:py-[100px]')
        ->and(matrix_get_content_accordion_vertical_padding_classes('default', 'default'))->toBe('py-12 xl:py-[100px]')
        ->and(matrix_get_content_accordion_vertical_padding_classes('bottom_only', 'default'))
        ->toBe('pt-0 pb-12 xl:pt-0 xl:pb-[100px]')
        ->and(matrix_get_content_accordion_vertical_padding_classes('bottom_only', 'directions_page'))
        ->toBe('pt-0 pb-12 xl:pt-0 xl:pb-[100px]')
        ->and(matrix_resolve_content_accordion_vertical_padding('small_top_large_bottom'))->toBe('small_top_large_bottom')
        ->and(matrix_get_content_accordion_vertical_padding_classes('small_top_large_bottom', 'default'))
        ->toBe('pt-8 pb-[100px]');
});
