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

test('research cards grid resolves manual cards by default', function () {
    $cards = matrix_resolve_research_cards_grid_cards([
        'cards_source' => 'manual',
        'manual_cards' => [
            ['title' => 'Manual card', 'summary' => 'Summary'],
        ],
    ]);

    expect($cards)->toHaveCount(1)
        ->and($cards[0]['title'])->toBe('Manual card');
});

test('research cards grid normalizes project posts into cards', function () {
    $card = matrix_normalize_research_cards_grid_card_from_post([
        'ID' => 42,
        'post_title' => 'Project title',
        'post_excerpt' => 'Project excerpt',
        'permalink' => 'https://example.com/research/project-title/',
        'image_id' => 7,
        'image_url' => 'https://example.com/image.jpg',
        'image_alt' => 'Project image',
    ]);

    expect($card)->not->toBeNull()
        ->and($card['title'])->toBe('Project title')
        ->and($card['summary'])->toBe('Project excerpt')
        ->and($card['link']['url'])->toBe('https://example.com/research/project-title/')
        ->and($card['image']['ID'])->toBe(7);
});

test('research cards grid builds category query args', function () {
    $args = matrix_build_research_cards_grid_query_args('category', [
        'posts_per_page' => 4,
        'selected_categories' => [17, 18],
    ]);

    expect($args['post_type'])->toBe('research_projects')
        ->and($args['posts_per_page'])->toBe(4)
        ->and($args['tax_query'][0]['taxonomy'])->toBe('research_project_category')
        ->and($args['tax_query'][0]['terms'])->toBe([17, 18]);
});

test('research cards grid resolves selected projects in order', function () {
    $cards = matrix_resolve_research_cards_grid_cards([
        'cards_source' => 'selected',
        'posts_per_page' => 2,
        'selected_projects' => [
            ['ID' => 1, 'post_title' => 'First', 'post_excerpt' => 'One', 'permalink' => 'https://example.com/one/'],
            ['ID' => 2, 'post_title' => 'Second', 'post_excerpt' => 'Two', 'permalink' => 'https://example.com/two/'],
            ['ID' => 3, 'post_title' => 'Third', 'post_excerpt' => 'Three', 'permalink' => 'https://example.com/three/'],
        ],
    ]);

    expect($cards)->toHaveCount(2)
        ->and($cards[0]['title'])->toBe('First')
        ->and($cards[1]['title'])->toBe('Second');
});
