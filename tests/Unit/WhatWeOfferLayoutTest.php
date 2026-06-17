<?php

require_once dirname(__DIR__, 2) . '/inc/content-section-functions.php';
require_once dirname(__DIR__, 2) . '/inc/what-we-offer-functions.php';

test('what we offer layout style falls back to image_feature', function () {
    expect(function_exists('matrix_resolve_what_we_offer_layout_style'))->toBeTrue();

    expect(matrix_resolve_what_we_offer_layout_style('intro_two_column'))->toBe('intro_two_column')
        ->and(matrix_resolve_what_we_offer_layout_style('image_feature'))->toBe('image_feature')
        ->and(matrix_resolve_what_we_offer_layout_style(''))->toBe('image_feature')
        ->and(matrix_resolve_what_we_offer_layout_style('unknown'))->toBe('image_feature');
});

test('what we offer accent color uses explicit value first', function () {
    expect(function_exists('matrix_get_what_we_offer_accent_color'))->toBeTrue();

    $row = ['accent_color' => '#B4A8CE'];

    expect(matrix_get_what_we_offer_accent_color($row, 0))->toBe('#B4A8CE');
});

test('what we offer accent color rotates through fallback palette', function () {
    expect(matrix_get_what_we_offer_accent_color([], 0))->toBe('#6FC9C0')
        ->and(matrix_get_what_we_offer_accent_color([], 1))->toBe('#C3DBAE')
        ->and(matrix_get_what_we_offer_accent_color([], 2))->toBe('#B4A8CE')
        ->and(matrix_get_what_we_offer_accent_color([], 3))->toBe('#E4B8D6')
        ->and(matrix_get_what_we_offer_accent_color([], 4))->toBe('#6FC9C0');
});

test('what we offer intro two column icons use the existing left svg pair', function () {
    expect(function_exists('matrix_get_what_we_offer_intro_two_column_icon_urls'))->toBeTrue();

    $icons = matrix_get_what_we_offer_intro_two_column_icon_urls('http://localhost:10034/');

    expect($icons)->toBeArray()
        ->and($icons['default'])->toBe('http://localhost:10034/wp-content/uploads/2025/11/left.svg')
        ->and($icons['hover'])->toBe('http://localhost:10034/wp-content/uploads/2026/03/left.svg');
});

test('what we offer intro two column icon svg returns markup for default and hover states', function () {
    expect(function_exists('matrix_get_what_we_offer_intro_two_column_icon_svg'))->toBeTrue();

    $default = matrix_get_what_we_offer_intro_two_column_icon_svg('default', '#6FC9C0');
    $hover = matrix_get_what_we_offer_intro_two_column_icon_svg('hover', '#6FC9C0');

    expect($default)->toContain('<svg')
        ->and($default)->toContain('viewBox="0 0 32 32"')
        ->and($default)->toContain('fill="white"')
        ->and($default)->toContain('opacity="0.25"')
        ->and($hover)->toContain('<svg')
        ->and($hover)->toContain('fill="white"')
        ->and($hover)->toContain('opacity="1"');
});

test('what we offer intro two column icon background maps accent to pastel tint', function () {
    expect(function_exists('matrix_get_what_we_offer_intro_two_column_icon_background'))->toBeTrue();

    expect(matrix_get_what_we_offer_intro_two_column_icon_background('#6FC9C0'))->toBe('#CEF2EE')
        ->and(matrix_get_what_we_offer_intro_two_column_icon_background('#C3DBAE'))->toBe('#E4F4D6')
        ->and(matrix_get_what_we_offer_intro_two_column_icon_background('#B4A8CE'))->toBe('#E9E2F7')
        ->and(matrix_get_what_we_offer_intro_two_column_icon_background('#E4B8D6'))->toBe('#F9E5F2');
});

test('what we offer vertical padding supports standard and bottom only modes', function () {
    expect(matrix_get_what_we_offer_section_padding_classes('default'))->toBe('py-12 lg:py-24')
        ->and(matrix_get_what_we_offer_section_padding_classes('standard'))->toBe('py-12')
        ->and(matrix_get_what_we_offer_section_padding_classes('bottom_only'))->toBe('pt-0 pb-12 lg:pt-0 lg:pb-24');
});
