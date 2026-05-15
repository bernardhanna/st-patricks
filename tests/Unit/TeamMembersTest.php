<?php

require_once dirname(__DIR__, 2) . '/inc/team-members-functions.php';

test('selected team member mode maps posts into standard grid items', function () {
    expect(function_exists('matrix_resolve_team_member_items'))->toBeTrue();

    $items = matrix_resolve_team_member_items(
        'selected',
        [
            [
                'ID' => 101,
                'post_title' => 'John Creedon',
                'job_title' => 'Director of Nursing',
                'profile_teaser' => '<p>Leadership profile.</p>',
                'permalink' => '/team/john-creedon',
                'image' => [
                    'ID' => 55,
                    'url' => 'https://example.com/john.jpg',
                    'alt' => 'John Creedon portrait',
                ],
            ],
        ],
        [],
        'standard_grid'
    );

    expect($items)->toHaveCount(1)
        ->and($items[0]['name'])->toBe('John Creedon')
        ->and($items[0]['job_title'])->toBe('Director of Nursing')
        ->and($items[0]['profile_teaser'])->toBe('<p>Leadership profile.</p>')
        ->and($items[0]['permalink'])->toBe('/team/john-creedon')
        ->and($items[0]['image']['url'])->toBe('https://example.com/john.jpg')
        ->and($items[0]['show_arrow'])->toBeTrue();
});

test('category team member mode keeps teaser content for spokespeople layout', function () {
    $items = matrix_resolve_team_member_items(
        'category',
        [],
        [
            [
                'ID' => 202,
                'post_title' => 'Paul Gilligan',
                'job_title' => 'CEO',
                'profile_teaser' => '<p>Public spokesperson biography.</p>',
                'permalink' => '/team/paul-gilligan',
                'image' => [
                    'ID' => 56,
                    'url' => 'https://example.com/paul.jpg',
                    'alt' => 'Paul Gilligan portrait',
                ],
            ],
        ],
        'spokespeople_grid'
    );

    expect($items)->toHaveCount(1)
        ->and($items[0]['layout_style'])->toBe('spokespeople_grid')
        ->and($items[0]['profile_teaser'])->toBe('<p>Public spokesperson biography.</p>')
        ->and($items[0]['show_arrow'])->toBeFalse();
});
