<?php

require_once dirname(__DIR__, 2) . '/inc/about-links-grid-functions.php';

test('about links grid tone backgrounds match figma palette', function () {
    $backgrounds = matrix_get_about_links_grid_tone_backgrounds();

    expect($backgrounds['bg1'])->toBe('#D9F0F4')
        ->and($backgrounds['bg2'])->toBe('#E2EBCF')
        ->and($backgrounds['bg3'])->toBe('#E5E1F3')
        ->and($backgrounds['bg4'])->toBe('#F3DDE8');
});

test('about links grid column count resolves stored values and labels', function () {
    expect(matrix_resolve_about_links_grid_columns('3'))->toBe('3')
        ->and(matrix_resolve_about_links_grid_columns('3 Columns'))->toBe('3')
        ->and(matrix_resolve_about_links_grid_columns('2 Columns'))->toBe('2')
        ->and(matrix_resolve_about_links_grid_columns(''))->toBe('3');
});

test('about links grid layout style resolves to supported values', function () {
    expect(matrix_resolve_about_links_grid_layout_style('compact_row'))->toBe('compact_row')
        ->and(matrix_resolve_about_links_grid_layout_style('flush_image'))->toBe('flush_image')
        ->and(matrix_resolve_about_links_grid_layout_style('image_feature'))->toBe('image_feature')
        ->and(matrix_resolve_about_links_grid_layout_style(''))->toBe('image_feature')
        ->and(matrix_resolve_about_links_grid_layout_style('invalid'))->toBe('image_feature');
});

test('about links grid layout helpers resolve card partial and backgrounds', function () {
    expect(matrix_get_about_links_grid_card_partial('flush_image'))
        ->toBe('template-parts/flexi/partials/about-links-grid-card-flush-image')
        ->and(matrix_get_about_links_grid_grid_class_names('flush_image', '3'))
        ->toContain('lg:grid-cols-3')
        ->and(matrix_get_about_links_grid_card_background('flush_image', 'bg3', '#F1F8F9'))
        ->toBe('#F1F8F9')
        ->and(matrix_get_about_links_grid_card_background('image_feature', 'bg3', '#F1F8F9'))
        ->toBe('#E5E1F3');
});

test('about links grid card footer resolves tone with fallback', function () {
    expect(matrix_get_about_links_grid_card_footer_background('bg3'))->toBe('#E5E1F3')
        ->and(matrix_get_about_links_grid_card_footer_background('unknown', '#F1F8F9'))->toBe('#F1F8F9');
});
