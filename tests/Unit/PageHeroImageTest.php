<?php

require_once dirname(__DIR__, 2) . '/inc/flexible-content-functions.php';

test('attachment id normalizer accepts scalars and ACF arrays', function () {
    expect(matrix_normalize_attachment_id(42))->toBe(42)
        ->and(matrix_normalize_attachment_id('17'))->toBe(17)
        ->and(matrix_normalize_attachment_id(['ID' => 9]))->toBe(9)
        ->and(matrix_normalize_attachment_id(['id' => 8]))->toBe(8)
        ->and(matrix_normalize_attachment_id(null))->toBe(0)
        ->and(matrix_normalize_attachment_id([]))->toBe(0);
});

test('page hero image is extracted from post meta without get_field', function () {
    __wp_stub('get_post_meta', function ($post_id, $key, $single) {
        if ($key === 'flexible_content_blocks') {
            return ['content', 'hero_with_breadcrumbs', 'content'];
        }

        if ($key === 'flexible_content_blocks_1_hero_image') {
            return 1960;
        }

        return '';
    });

    expect(matrix_extract_page_hero_image_id_from_post_meta(216))->toBe(1960);
});

test('page hero image is extracted from the hero_with_breadcrumbs row', function () {
    $rows = [
        [
            'acf_fc_layout' => 'content',
            'image' => 99,
        ],
        [
            'acf_fc_layout' => 'hero_with_breadcrumbs',
            'hero_image' => ['ID' => 762],
        ],
        [
            'acf_fc_layout' => 'content',
            'image' => 762,
        ],
    ];

    expect(matrix_extract_page_hero_image_id_from_rows($rows))->toBe(762)
        ->and(matrix_extract_page_hero_image_id_from_rows([]))->toBe(0);
});

test('strip helper clears non-hero reuse of the page hero image', function () {
    $rows = [
        [
            'acf_fc_layout' => 'hero_with_breadcrumbs',
            'hero_image' => 762,
        ],
        [
            'acf_fc_layout' => 'content',
            'image' => 762,
            'background_image' => 100,
        ],
        [
            'acf_fc_layout' => 'related_cards',
            'cards' => [
                ['title' => 'A', 'image' => 762],
                ['title' => 'B', 'image' => 863],
            ],
        ],
    ];

    $result = matrix_strip_duplicate_hero_images_from_flexi_rows($rows);

    expect($result['changed'])->toBeTrue()
        ->and($result['rows'][0]['hero_image'])->toBe(762)
        ->and($result['rows'][1]['image'])->toBe('')
        ->and($result['rows'][1]['background_image'])->toBe(100)
        ->and($result['rows'][2]['cards'][0]['image'])->toBe('')
        ->and($result['rows'][2]['cards'][1]['image'])->toBe(863);
});

test('related cards helper clears only the page hero image id', function () {
    $GLOBALS['matrix_page_hero_image_ids'] = [1 => 762];

    $cards = matrix_exclude_page_hero_from_related_cards([
        ['title' => 'A', 'image_id' => 762, 'description' => '', 'link' => ['title' => 'x', 'url' => '/a', 'target' => '_self']],
        ['title' => 'B', 'image_id' => 863, 'description' => '', 'link' => ['title' => 'y', 'url' => '/b', 'target' => '_self']],
    ], 1);

    expect($cards[0]['image_id'])->toBe(0)
        ->and($cards[1]['image_id'])->toBe(863);

    unset($GLOBALS['matrix_page_hero_image_ids']);
});
