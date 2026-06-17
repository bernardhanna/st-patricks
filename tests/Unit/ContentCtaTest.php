<?php

require_once dirname(__DIR__, 2) . '/inc/link-functions.php';
require_once dirname(__DIR__, 2) . '/inc/faq-functions.php';
require_once dirname(__DIR__, 2) . '/inc/content-cta-functions.php';

test('content cta normalizes button links', function () {
    expect(function_exists('matrix_normalize_content_cta_link'))->toBeTrue();

    expect(matrix_normalize_content_cta_link([
        'title' => ' Healthcare professionals ',
        'url' => ' /healthcare-professionals/ ',
        'target' => '_self',
    ]))->toMatchArray([
        'title' => 'Healthcare professionals',
        'url' => '/healthcare-professionals/',
        'target' => '_self',
    ]);

    expect(matrix_normalize_content_cta_link([
        'title' => 'Missing URL',
        'url' => '',
    ]))->toBeNull();
});

test('content cta resolves solid and gradient backgrounds', function () {
    expect(function_exists('matrix_get_content_cta_background_style'))->toBeTrue();

    expect(matrix_get_content_cta_background_style('color', '#E9E2F7', ''))
        ->toBe('background-color: #E9E2F7;');

    expect(matrix_get_content_cta_background_style('gradient', '', 'linear-gradient(135deg, #E9E2F7 0%, #CEF2EE 100%)'))
        ->toBe('background: linear-gradient(135deg, #E9E2F7 0%, #CEF2EE 100%);');

    expect(matrix_get_content_cta_background_style('color', '', ''))
        ->toBe('background-color: #E9E2F7;');
});

test('content cta resolves inverse theme classes for dark backgrounds', function () {
    $inverse = matrix_get_content_cta_theme_classes('inverse');

    expect($inverse['heading'])->toBe('text-white')
        ->and($inverse['button'])->toContain('bg-white');
});

test('content cta resolves layout 2 image background helpers', function () {
    require_once dirname(__DIR__, 2) . '/inc/content-section-functions.php';

    expect(matrix_resolve_content_cta_layout_style('image_background'))->toBe('image_background')
        ->and(matrix_resolve_content_cta_layout_style(''))->toBe('default')
        ->and(matrix_content_cta_uses_image_background('image_background', 42))->toBeTrue()
        ->and(matrix_content_cta_uses_image_background('default', 42))->toBeFalse()
        ->and(matrix_content_cta_uses_image_background('image_background', 0))->toBeFalse();

    expect(matrix_get_content_cta_tint_background_style('#F1F3DE'))
        ->toBe('background-color: #F1F3DE;')
        ->and(matrix_get_content_cta_tint_background_style(''))
        ->toBe('background-color: #F1F3DE;');

    expect(matrix_get_content_cta_background_image_opacity_style('50'))
        ->toBe('opacity: 0.50;')
        ->and(matrix_resolve_content_cta_background_image_opacity(''))
        ->toBe(50);

    expect(matrix_get_content_cta_wrapper_class_names('image_background'))
        ->toContain('max-w-[1040px]')
        ->and(matrix_get_content_cta_wrapper_class_names('image_background'))
        ->toContain('px-4')
        ->and(matrix_get_content_cta_wrapper_class_names('image_background'))
        ->toContain('py-12')
        ->and(matrix_get_content_cta_heading_class_names('image_background', 'text-[#1E244B]'))
        ->toContain('lg:text-[32px]')
        ->and(matrix_get_content_cta_body_class_names('image_background', '[&_p]:text-[#08284B]'))
        ->toContain('[&_p]:text-base')
        ->and(matrix_get_content_cta_body_class_names('image_background', '[&_p]:text-[#08284B]'))
        ->toContain('[&_p]:font-bold')
        ->and(matrix_get_content_cta_content_row_class_names('image_background'))
        ->not->toContain('justify-between')
        ->and(matrix_get_content_cta_button_class_names('image_background', 'default'))
        ->toContain('px-8')
        ->and(matrix_get_content_cta_button_class_names('image_background', 'default'))
        ->toContain('rounded-[6px]')
        ->and(matrix_get_content_cta_button_class_names('image_background', 'default'))
        ->toContain('bg-[#024B79]');

    expect(matrix_get_content_cta_background_image_overlay_style('#024B79', 50))
        ->toContain('rgba(2, 75, 121');
});
