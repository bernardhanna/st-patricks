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
