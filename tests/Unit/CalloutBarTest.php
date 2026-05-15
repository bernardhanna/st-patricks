<?php

require_once dirname(__DIR__, 2) . '/inc/callout-bar-functions.php';

test('callout bar helper trims editor text and falls back when empty', function () {
    expect(function_exists('matrix_resolve_callout_bar_message'))->toBeTrue();

    expect(matrix_resolve_callout_bar_message('  Hello world  ', 'Fallback'))->toBe('Hello world')
        ->and(matrix_resolve_callout_bar_message('', 'Fallback'))->toBe('Fallback');
});
