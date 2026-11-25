<?php

require_once dirname(__DIR__, 2) . '/inc/callout-bar-functions.php';

test('callout bar helper trims editor text and falls back when empty', function () {
    expect(function_exists('matrix_resolve_callout_bar_message'))->toBeTrue();

    expect(matrix_resolve_callout_bar_message('  Hello world  ', 'Fallback'))->toBe('Hello world')
        ->and(matrix_resolve_callout_bar_message('', 'Fallback'))->toBe('Fallback');
});

test('callout bar style resolves navy vs default and maps colours', function () {
    expect(matrix_resolve_callout_bar_style('navy'))->toBe('navy')
        ->and(matrix_resolve_callout_bar_style(''))->toBe('default')
        ->and(matrix_get_callout_bar_background_style('navy'))->toBe('background-color: #08284B;')
        ->and(matrix_get_callout_bar_background_style('default'))->toBe('background-color: #F1F8F9;')
        ->and(matrix_get_callout_bar_text_class_names('navy'))->toContain('text-white');
});
