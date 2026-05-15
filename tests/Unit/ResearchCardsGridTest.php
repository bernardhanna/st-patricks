<?php

require_once dirname(__DIR__, 2) . '/inc/research-cards-grid-functions.php';

test('research cards grid normalizes valid cards and skips incomplete rows', function () {
    expect(function_exists('matrix_normalize_research_cards_grid_cards'))->toBeTrue();

    $cards = matrix_normalize_research_cards_grid_cards([
        [
            'title' => '  View Ethics Guidance  ',
            'summary' => '  Supporting copy for the first card.  ',
            'image' => ['url' => 'https://example.com/one.jpg', 'alt' => 'Ethics guidance'],
            'link' => ['url' => 'https://example.com/guidance', 'title' => 'Read more', 'target' => '_blank'],
        ],
        [
            'title' => '   ',
            'summary' => 'This row should be ignored.',
        ],
    ]);

    expect($cards)->toHaveCount(1)
        ->and($cards[0]['title'])->toBe('View Ethics Guidance')
        ->and($cards[0]['summary'])->toBe('Supporting copy for the first card.')
        ->and($cards[0]['link']['url'])->toBe('https://example.com/guidance')
        ->and($cards[0]['link']['target'])->toBe('_blank')
        ->and($cards[0]['image']['url'])->toBe('https://example.com/one.jpg');
});

test('research cards grid link helper returns null for incomplete links', function () {
    expect(matrix_normalize_research_cards_grid_link(null))->toBeNull()
        ->and(matrix_normalize_research_cards_grid_link(['title' => 'Missing URL']))->toBeNull();
});

test('research cards grid derives a sensible card title tag from the section heading tag', function () {
    expect(function_exists('matrix_get_research_cards_grid_card_title_tag'))->toBeTrue()
        ->and(matrix_get_research_cards_grid_card_title_tag('h1'))->toBe('h2')
        ->and(matrix_get_research_cards_grid_card_title_tag('h4'))->toBe('h5')
        ->and(matrix_get_research_cards_grid_card_title_tag('h5'))->toBe('h6')
        ->and(matrix_get_research_cards_grid_card_title_tag('h6'))->toBe('p')
        ->and(matrix_get_research_cards_grid_card_title_tag('span'))->toBe('p')
        ->and(matrix_get_research_cards_grid_card_title_tag('invalid'))->toBe('p');
});

test('research cards grid link helper falls back to learn more when title is blank', function () {
    $link = matrix_normalize_research_cards_grid_link([
        'url' => 'https://example.com/research',
        'title' => '   ',
    ]);

    expect($link)->not->toBeNull()
        ->and($link['title'])->toBe('Learn more');
});
