<?php

if (! function_exists('add_action')) {
    function add_action(...$args)
    {
    }
}

if (! function_exists('add_filter')) {
    function add_filter(...$args)
    {
    }
}

require_once dirname(__DIR__, 2) . '/inc/enqueue-scripts.php';

test('buggie script url is the matrix tracker snippet', function () {
    expect(matrix_buggie_script_url())->toBe('https://buggie.matrixinternet.ie/w/pk_ypg2qtwlgavacomieccztune.js');
});

test('buggie can be enabled and disabled from theme options', function () {
    __wp_stub('get_field', fn ($field, $post_id = false) => $field === 'enable_matrix_buggie' ? 1 : null);

    expect(matrix_is_buggie_enabled())->toBeTrue();

    __wp_stub('get_field', fn ($field, $post_id = false) => $field === 'enable_matrix_buggie' ? 0 : null);

    expect(matrix_is_buggie_enabled())->toBeFalse();
});
