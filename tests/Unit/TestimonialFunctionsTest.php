<?php

require_once dirname(__DIR__, 2) . '/inc/testimonial-functions.php';

test('manual testimonial mode returns normalized manual items', function () {
    expect(function_exists('matrix_resolve_testimonial_items'))->toBeTrue();

    $result = matrix_resolve_testimonial_items(
        'manual',
        [
            [
                'quote' => 'Manual quote',
                'author_name' => 'Tom',
                'author_title' => 'Service User',
                'card_tone' => 'mauve',
            ],
        ],
        [],
        []
    );

    expect($result)->toHaveCount(1)
        ->and($result[0]['quote'])->toBe('Manual quote')
        ->and($result[0]['author_name'])->toBe('Tom')
        ->and($result[0]['author_title'])->toBe('Service User')
        ->and($result[0]['card_tone'])->toBe('mauve');
});

test('selected testimonial mode maps post-like arrays into testimonial items', function () {
    expect(function_exists('matrix_map_testimonial_post_to_item'))->toBeTrue();

    $result = matrix_resolve_testimonial_items(
        'selected',
        [],
        [
            [
                'post_title' => 'Alice',
                'post_content' => 'Selected quote',
                'post_excerpt' => 'Supporter',
            ],
        ],
        []
    );

    expect($result)->toHaveCount(1)
        ->and($result[0]['quote'])->toBe('Selected quote')
        ->and($result[0]['author_name'])->toBe('Alice')
        ->and($result[0]['author_title'])->toBe('Supporter');
});

test('all testimonial mode alternates default tones when no tone is provided', function () {
    $result = matrix_resolve_testimonial_items(
        'all',
        [],
        [],
        [
            [
                'post_title' => 'First',
                'post_content' => 'First quote',
                'post_excerpt' => 'Role 1',
            ],
            [
                'post_title' => 'Second',
                'post_content' => 'Second quote',
                'post_excerpt' => 'Role 2',
            ],
            [
                'post_title' => 'Third',
                'post_content' => 'Third quote',
                'post_excerpt' => 'Role 3',
            ],
        ]
    );

    expect($result)->toHaveCount(3)
        ->and($result[0]['card_tone'])->toBe('lavender')
        ->and($result[1]['card_tone'])->toBe('mauve')
        ->and($result[2]['card_tone'])->toBe('lavender');
});

test('editorial featured layout groups testimonials into two-up rows plus one featured item', function () {
    $items = [
        ['quote' => 'One', 'author_name' => 'A', 'author_title' => 'Role A', 'card_tone' => 'lavender'],
        ['quote' => 'Two', 'author_name' => 'B', 'author_title' => 'Role B', 'card_tone' => 'mauve'],
        ['quote' => 'Three', 'author_name' => 'C', 'author_title' => 'Role C', 'card_tone' => 'lavender'],
        ['quote' => 'Four', 'author_name' => 'D', 'author_title' => 'Role D', 'card_tone' => 'mauve'],
    ];

    $rows = matrix_group_editorial_featured_testimonials($items);

    expect($rows)->toHaveCount(2)
        ->and($rows[0]['standard_items'])->toHaveCount(2)
        ->and($rows[0]['featured_item']['item']['quote'])->toBe('Three')
        ->and($rows[1]['standard_items'])->toHaveCount(1)
        ->and($rows[1]['featured_item'])->toBeNull();
});

test('load more button keeps dark hover text and renders without a border', function () {
    $template = file_get_contents(dirname(__DIR__, 2) . '/template-parts/flexi/testimonials.php');

    preg_match('/<button\s+.*?class="btn <\?php echo esc_attr\(\$load_more_class\); \?> (?P<classes>[^"]+)".*?style="(?P<style>[^"]+)"/s', $template, $button_matches);
    preg_match('/\.<\?php echo esc_attr\(\$load_more_class\); \?>:hover,.*?\{(?P<rules>.*?)\}/s', $template, $hover_matches);

    expect($button_matches)->not->toBeEmpty()
        ->and($button_matches['classes'])->not->toContain('border')
        ->and($button_matches['style'])->not->toContain('border-color')
        ->and($hover_matches)->not->toBeEmpty()
        ->and($hover_matches['rules'])->toContain('color: <?php echo esc_attr($button_text_color); ?> !important;')
        ->and($hover_matches['rules'])->not->toContain('#ffffff');
});
