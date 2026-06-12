<?php

if (! function_exists('__')) {
    function __(string $text, ?string $domain = null): string
    {
        return $text;
    }
}

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
        ->and($link['title'])->toBe('Learn more')
        ->and($link['target'])->toBe('_blank');
});
