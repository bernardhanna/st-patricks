<?php

require_once dirname(__DIR__, 2) . '/inc/timeline-functions.php';

test('timeline formats display dates from labels or event dates', function () {
    expect(matrix_format_timeline_date('1746-08-02', '2.8.1746'))->toBe('2.8.1746')
        ->and(matrix_format_timeline_date('1946-08-02', ''))->toBe('2.8.1946');
});

test('timeline normalizes items with alternating sides and optional media', function () {
    $items = matrix_normalize_timeline_items([
        [
            'side' => '',
            'event_date' => '1746-08-02',
            'event_date_label' => '2.8.1746',
            'item_heading' => '  Short heading  ',
            'item_text' => '<p>Body copy</p>',
            'image' => [
                'ID' => 12,
                'url' => 'https://example.com/image.jpg',
                'alt' => '',
                'title' => 'Milestone image',
            ],
            'cta_link' => [
                'title' => 'Supporting material CTA',
                'url' => 'https://example.com/material',
                'target' => '_blank',
            ],
        ],
        [
            'side' => 'right',
            'event_date' => '2000-08-02',
            'event_date_label' => '',
            'item_heading' => 'H3. Milestone',
            'item_text' => '',
            'cta_link' => [
                'title' => '',
                'url' => '',
            ],
        ],
    ]);

    expect($items)->toHaveCount(2)
        ->and($items[0]['side'])->toBe('left')
        ->and($items[0]['display_date'])->toBe('2.8.1746')
        ->and($items[0]['item_heading'])->toBe('Short heading')
        ->and($items[0]['has_cta'])->toBeTrue()
        ->and($items[0]['image']['alt'])->toBe('Milestone image')
        ->and($items[1]['side'])->toBe('right')
        ->and($items[1]['has_cta'])->toBeFalse();
});

test('timeline drops empty items and normalizes footer cta', function () {
    expect(matrix_normalize_timeline_items([
        [
            'item_heading' => '   ',
            'item_text' => 'ignored',
        ],
    ]))->toBe([]);

    expect(matrix_normalize_timeline_link([
        'title' => ' Our Present and Future ',
        'url' => ' /about/ ',
        'target' => '_self',
    ]))->toMatchArray([
        'title' => 'Our Present and Future',
        'url' => '/about/',
        'target' => '_self',
    ]);
});

test('timeline footer cta uses the general button hover treatment', function () {
    $template = file_get_contents(dirname(__DIR__, 2) . '/template-parts/flexi/timeline.php');

    expect($template)->toContain('btn inline-flex h-[36px]')
        ->and($template)->toContain('hover:bg-[#C3DBAE]')
        ->and($template)->toContain('hover:text-[#1E244B]')
        ->and($template)->not->toContain('hover:bg-[#08284B]');
});
