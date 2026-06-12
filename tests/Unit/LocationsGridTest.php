<?php

require_once dirname(__DIR__, 2) . '/inc/locations-grid-functions.php';
require_once dirname(__DIR__, 2) . '/inc/locations-functions.php';

test('locations grid normalizes cards and trims card content', function () {
    expect(function_exists('matrix_normalize_locations_grid_cards'))->toBeTrue();

    $cards = matrix_normalize_locations_grid_cards([
        [
            'title' => '  Willow Grove Adolescent Unit  ',
            'link' => [
                'title' => 'View Willow Grove',
                'url' => 'https://example.com/willow-grove',
                'target' => '_blank',
            ],
            'image' => [
                'ID' => 99,
                'url' => 'https://example.com/image.jpg',
                'alt' => '',
                'title' => 'Willow Grove exterior',
            ],
        ],
    ]);

    expect($cards)->toHaveCount(1)
        ->and($cards[0])->toMatchArray([
            'title' => 'Willow Grove Adolescent Unit',
            'is_linked' => true,
        ])
        ->and($cards[0]['image']['alt'])->toBe('Willow Grove exterior');
});

test('locations grid leaves cards static when the link is empty', function () {
    expect(function_exists('matrix_normalize_locations_grid_cards'))->toBeTrue();

    $cards = matrix_normalize_locations_grid_cards([
        [
            'title' => 'Dean Clinic Lucan',
            'link' => [
                'title' => '',
                'url' => '',
                'target' => '_self',
            ],
            'image' => null,
        ],
    ]);

    expect($cards)->toHaveCount(1)
        ->and($cards[0]['is_linked'])->toBeFalse();
});

test('locations grid drops cards without a title', function () {
    expect(function_exists('matrix_normalize_locations_grid_cards'))->toBeTrue();

    $cards = matrix_normalize_locations_grid_cards([
        [
            'title' => '   ',
            'link' => [
                'title' => 'Broken',
                'url' => 'https://example.com',
            ],
        ],
    ]);

    expect($cards)->toBe([]);
});

test('locations grid normalizes an optional footer cta link', function () {
    expect(function_exists('matrix_normalize_locations_grid_link'))->toBeTrue();

    $link = matrix_normalize_locations_grid_link([
        'title' => ' Locations Page ',
        'url' => ' /locations/ ',
        'target' => '_self',
    ]);

    expect($link)->toMatchArray([
        'title' => 'Locations Page',
        'url' => '/locations/',
        'target' => '_self',
    ]);

    expect(matrix_normalize_locations_grid_link([
        'title' => 'Missing URL',
        'url' => '',
    ]))->toBeNull();
});

test('locations grid resolves cards from selected source mode', function () {
    $manual = matrix_resolve_locations_grid_cards('manual', [
        [
            'title' => 'Manual Card',
            'link' => ['title' => 'Manual', 'url' => 'https://example.com/manual'],
        ],
    ], []);

    expect($manual)->toHaveCount(1)
        ->and($manual[0]['title'])->toBe('Manual Card');

    expect(matrix_resolve_locations_grid_cards('locations', [], 'not-an-array'))->toBe([]);
    expect(matrix_locations_grid_cards_from_posts([]))->toBe([]);
});
