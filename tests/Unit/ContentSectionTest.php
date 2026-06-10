<?php

require_once dirname(__DIR__, 2) . '/inc/faq-functions.php';
require_once dirname(__DIR__, 2) . '/inc/content-section-functions.php';

test('content layout style resolves image positions and falls back to reverse layout', function () {
    expect(matrix_resolve_content_layout_style('image_left'))->toBe('image_left')
        ->and(matrix_resolve_content_layout_style('image_right'))->toBe('image_right')
        ->and(matrix_resolve_content_layout_style('', true))->toBe('image_right')
        ->and(matrix_resolve_content_layout_style('', false))->toBe('image_left');
});

test('content image height mode resolves match text and fixed minimum options', function () {
    expect(matrix_resolve_content_image_height_mode('match_text'))->toBe('match_text')
        ->and(matrix_resolve_content_image_height_mode('fixed_min'))->toBe('fixed_min')
        ->and(matrix_resolve_content_image_height_mode(''))->toBe('match_text');

    expect(matrix_get_content_grid_class_names('match_text'))->toContain('lg:items-stretch')
        ->and(matrix_get_content_grid_class_names('fixed_min'))->not->toContain('lg:items-stretch');

    expect(matrix_get_content_image_class_names('fixed_min'))->toContain('lg:min-h-[19.5rem]')
        ->and(matrix_get_content_image_class_names('match_text'))->toContain('lg:h-full')
        ->and(matrix_get_content_image_class_names('match_text'))->not->toContain('lg:min-h-[19.5rem]');
});

test('content accent position resolves above and below heading', function () {
    expect(matrix_resolve_content_accent_position('below_heading'))->toBe('below_heading')
        ->and(matrix_resolve_content_accent_position('above_heading'))->toBe('above_heading')
        ->and(matrix_resolve_content_accent_position(''))->toBe('below_heading');
});

test('content background style supports preset and custom values', function () {
    expect(matrix_get_content_background_style('white'))->toBe('background-color: #FFFFFF;')
        ->and(matrix_get_content_background_style('cream'))->toBe('background-color: #FBF8F3;')
        ->and(matrix_get_content_background_style('light_blue'))->toBe('background-color: #C6ECF4;')
        ->and(matrix_get_content_background_style('color', '#E9E2F7', ''))->toContain('#E9E2F7')
        ->and(matrix_get_content_background_style('gradient', '', 'linear-gradient(135deg, #fff 0%, #000 100%)'))
        ->toContain('linear-gradient(135deg, #fff 0%, #000 100%)');
});

test('content pdf helpers expose icon and document link classes', function () {
    expect(matrix_get_content_pdf_icon_svg())->toContain('<svg')
        ->and(matrix_get_content_pdf_icon_svg())->toContain('aria-hidden="true"')
        ->and(matrix_get_content_document_link_class_names())->toContain('btn');
});

test('content button helpers normalize links and class names', function () {
    expect(matrix_normalize_content_link([
        'title' => ' Support Us ',
        'url' => 'https://example.com/support/',
    ]))->toMatchArray([
        'title' => 'Support Us',
        'url' => 'https://example.com/support/',
        'target' => '_self',
    ])
        ->and(matrix_get_content_button_class_names('outline'))->toContain('bg-transparent')
        ->and(matrix_get_content_button_class_names('outline'))->toContain('text-[#024B79]')
        ->and(matrix_get_content_button_class_names('filled'))->toContain('bg-transparent')
        ->and(matrix_get_content_button_class_names('filled'))->not->toContain('bg-[#024B79]');
});
