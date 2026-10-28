<?php

require_once dirname(__DIR__, 2) . '/inc/brand-palette-functions.php';

test('brand lavender matches what we offer palette', function () {
    expect(matrix_get_brand_lavender_color())->toBe('#B4A8CE');
});

test('willow grove resolver maps legacy soft lavender to brand lavender', function () {
    __wp_stub('get_post', static function ($post_id) {
        if ((int) $post_id === 261) {
            return (object) [
                'post_name' => 'your-care-with-willow-grove',
                'post_parent' => 0,
                'post_type' => 'page',
            ];
        }

        return null;
    });

    expect(matrix_resolve_willow_grove_lavender_color('#E9E2F7', 261))->toBe('#B4A8CE')
        ->and(matrix_resolve_willow_grove_lavender_color('#E8E2F7', 261))->toBe('#B4A8CE')
        ->and(matrix_resolve_willow_grove_lavender_color('#6FC9C0', 261))->toBe('#6FC9C0');
});

test('willow grove resolver leaves colors unchanged on other pages', function () {
    __wp_stub('get_post', static function ($post_id) {
        if ((int) $post_id === 212) {
            return (object) [
                'post_name' => 'inpatient-care',
                'post_parent' => 0,
                'post_type' => 'page',
            ];
        }

        return null;
    });

    expect(matrix_resolve_willow_grove_lavender_color('#E9E2F7', 212))->toBe('#E9E2F7');
});
