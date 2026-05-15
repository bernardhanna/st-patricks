<?php

require_once dirname(__DIR__, 2) . '/inc/hero-functions.php';

test('manual breadcrumb mode returns manual items and current label', function () {
    expect(function_exists('matrix_resolve_hero_breadcrumbs'))->toBeTrue();

    $manual_items = [
        [
            'title' => 'Home',
            'url' => 'https://example.com/',
            'target' => '',
        ],
        [
            'title' => 'About Us',
            'url' => 'https://example.com/about-us/',
            'target' => '',
        ],
    ];

    $result = matrix_resolve_hero_breadcrumbs(
        'manual',
        $manual_items,
        'Healthcare professionals',
        [
            'items' => [
                ['title' => 'Ignored', 'url' => 'https://example.com/ignored/', 'target' => ''],
            ],
            'current_label' => 'Ignored current',
        ]
    );

    expect($result['items'])->toBe($manual_items)
        ->and($result['current_label'])->toBe('Healthcare professionals');
});

test('hero layout style defaults to image_split', function () {
    expect(matrix_resolve_hero_with_breadcrumbs_layout_style(''))->toBe('image_split')
        ->and(matrix_resolve_hero_with_breadcrumbs_layout_style('image_split'))->toBe('image_split')
        ->and(matrix_resolve_hero_with_breadcrumbs_layout_style('unknown'))->toBe('image_split');
});

test('hero layout style resolves title_accent', function () {
    expect(matrix_resolve_hero_with_breadcrumbs_layout_style('title_accent'))->toBe('title_accent');
});

test('hero layout style resolves register_intro', function () {
    expect(matrix_resolve_hero_with_breadcrumbs_layout_style('register_intro'))->toBe('register_intro');
});

test('hero external link icon helper returns accessible svg markup', function () {
    expect(function_exists('matrix_get_hero_external_link_icon_svg'))->toBeTrue()
        ->and(matrix_get_hero_external_link_icon_svg())->toContain('<svg')
        ->and(matrix_get_hero_external_link_icon_svg())->toContain('aria-hidden="true"');
});

test('auto breadcrumb mode falls back to auto breadcrumb data', function () {
    expect(function_exists('matrix_resolve_hero_breadcrumbs'))->toBeTrue();

    $auto_items = [
        [
            'title' => 'Home',
            'url' => 'https://example.com/',
            'target' => '',
        ],
        [
            'title' => 'Flexi',
            'url' => 'https://example.com/flexi/',
            'target' => '',
        ],
    ];

    $result = matrix_resolve_hero_breadcrumbs(
        'auto',
        [],
        '',
        [
            'items' => $auto_items,
            'current_label' => 'Hero demo',
        ]
    );

    expect($result['items'])->toBe($auto_items)
        ->and($result['current_label'])->toBe('Hero demo');
});
