<?php

require_once dirname(__DIR__, 2) . '/inc/related-cards-functions.php';

test('related cards normalization skips invalid links and keeps valid cards', function () {
    expect(function_exists('matrix_normalize_related_cards'))->toBeTrue();

    $cards = matrix_normalize_related_cards([
        [
            'title' => 'Treatment service',
            'description' => 'Assessment and aftercare.',
            'image' => 42,
            'link' => [
                'title' => 'See more',
                'url' => 'https://example.com/programme',
                'target' => '',
            ],
        ],
        [
            'title' => 'Broken card',
            'description' => 'Should be removed.',
            'link' => [
                'title' => 'Broken',
                'url' => '#',
            ],
        ],
    ]);

    expect($cards)->toHaveCount(1)
        ->and($cards[0]['title'])->toBe('Treatment service')
        ->and($cards[0]['description'])->toBe('Assessment and aftercare.')
        ->and($cards[0]['image_id'])->toBe(42)
        ->and($cards[0]['link']['url'])->toBe('https://example.com/programme');
});

test('related card link helper defaults missing title and normalizes target', function () {
    expect(function_exists('matrix_normalize_related_card_link'))->toBeTrue();

    $link = matrix_normalize_related_card_link([
        'title' => '',
        'url' => 'https://example.com/next',
        'target' => '_blank',
    ]);

    expect($link)->not->toBeNull()
        ->and($link['title'])->toBe('Next')
        ->and($link['target'])->toBe('_blank');
});

test('related cards replace vague CTA titles with the card heading', function () {
    $cards = matrix_normalize_related_cards([
        [
            'title' => 'Anxiety supports',
            'description' => 'Overview.',
            'image' => 1,
            'link' => [
                'title' => 'Learn more',
                'url' => 'https://example.com/anxiety',
            ],
        ],
    ]);

    expect($cards)->toHaveCount(1)
        ->and($cards[0]['link']['title'])->toBe('Anxiety supports');
});

test('related cards column resolver accepts ACF labels and values', function () {
    expect(matrix_resolve_related_cards_columns('2'))->toBe('2')
        ->and(matrix_resolve_related_cards_columns('2 Columns'))->toBe('2')
        ->and(matrix_resolve_related_cards_columns('3 Columns'))->toBe('3')
        ->and(matrix_resolve_related_cards_columns(''))->toBe('3');

    expect(matrix_get_related_cards_grid_class_names('2 Columns'))->toContain('lg:grid-cols-2')
        ->and(matrix_get_related_cards_grid_class_names('2 Columns'))->toContain('sm:grid-cols-2')
        ->and(matrix_get_related_cards_grid_class_names('2 Columns'))->not->toContain('lg:grid-cols-3')
        ->and(matrix_get_related_cards_grid_class_names('3'))->toContain('lg:grid-cols-3')
        ->and(matrix_get_related_cards_grid_class_names('3'))->not->toContain('lg:grid-cols-2');
});
