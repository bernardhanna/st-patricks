<?php

require_once dirname(__DIR__, 2) . '/inc/faq-functions.php';
require_once dirname(__DIR__, 2) . '/inc/content-section-functions.php';

test('content layout style resolves image positions and falls back to reverse layout', function () {
    expect(matrix_resolve_content_layout_style('image_left'))->toBe('image_left')
        ->and(matrix_resolve_content_layout_style('image_right'))->toBe('image_right')
        ->and(matrix_resolve_content_layout_style('', true))->toBe('image_right')
        ->and(matrix_resolve_content_layout_style('', false))->toBe('image_left');
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
        ->and(matrix_get_content_button_class_names('outline'))->toContain('bg-white')
        ->and(matrix_get_content_button_class_names('filled'))->toContain('bg-[#024B79]');
});
