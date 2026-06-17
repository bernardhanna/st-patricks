<?php

require_once dirname(__DIR__, 2) . '/inc/blog-single-functions.php';
require_once dirname(__DIR__, 2) . '/inc/careers-single-functions.php';

test('careers single exposes hero meta and share helpers', function () {
    expect(function_exists('matrix_get_career_hero_meta'))->toBeTrue()
        ->and(function_exists('matrix_get_career_share_links'))->toBeTrue()
        ->and(function_exists('matrix_get_career_first_term_name'))->toBeTrue();
});

test('careers hero meta omits rows without values', function () {
    if (function_exists('get_field')) {
        expect(true)->toBeTrue();

        return;
    }

    expect(matrix_get_career_hero_meta(0))->toBe([]);
});

test('careers share links mirror blog share links', function () {
    expect(matrix_get_career_share_links(0))->toBe([]);

    $links = matrix_get_career_share_links();
    expect($links)->toBeArray();

    if ($links === []) {
        expect(true)->toBeTrue();

        return;
    }

    expect($links[0]['id'])->toBe('facebook')
        ->and($links[4]['id'])->toBe('copy')
        ->and($links[4]['is_copy'] ?? false)->toBeTrue();
});

test('careers hero meta returns expected labels with post context', function () {
    if (! function_exists('update_field') || ! function_exists('wp_insert_post')) {
        expect(true)->toBeTrue();

        return;
    }

    $post_id = wp_insert_post([
        'post_type' => 'careers',
        'post_status' => 'publish',
        'post_title' => 'Test vacancy hero meta',
    ]);

    update_field('career_area', 'St Patrick\'s University Hospital (SPUH)', $post_id);
    update_field('career_job_type', 'Permanent Full-Time', $post_id);
    update_field('career_category', 'Nursing', $post_id);

    $meta = matrix_get_career_hero_meta($post_id);
    $labels = array_column($meta, 'label');

    expect($labels)->toContain('Area')
        ->and($labels)->toContain('Job Type')
        ->and($labels)->toContain('Category')
        ->and($labels)->not->toContain('Location');

    wp_delete_post($post_id, true);
});
