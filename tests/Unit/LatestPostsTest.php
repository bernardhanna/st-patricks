<?php

require_once dirname(__DIR__, 2) . '/inc/latest-posts-functions.php';

test('latest posts query args include selected categories and post limit', function () {
    expect(function_exists('matrix_build_latest_posts_query_args'))->toBeTrue();

    $args = matrix_build_latest_posts_query_args([3, 7], 6);

    expect($args['post_type'])->toBe('post')
        ->and($args['post_status'])->toBe('publish')
        ->and($args['posts_per_page'])->toBe(6)
        ->and($args['orderby'])->toBe('date')
        ->and($args['order'])->toBe('DESC')
        ->and($args['category__in'])->toBe([3, 7]);
});

test('latest posts query args fall back to six latest posts when no categories are selected', function () {
    $args = matrix_build_latest_posts_query_args([], 0);

    expect($args['posts_per_page'])->toBe(6)
        ->and(array_key_exists('category__in', $args))->toBeFalse();
});

test('latest posts header button uses compact ghost styling from figma', function () {
    $classes = matrix_get_latest_posts_header_button_class_names();

    expect($classes)->toContain('h-[36px]')
        ->and($classes)->toContain('text-[14px]')
        ->and($classes)->toContain('text-[#08284B]')
        ->and($classes)->toContain('bg-transparent');
});

test('latest posts card normalization returns null for invalid post id', function () {
    expect(matrix_normalize_latest_posts_card(0))->toBeNull();
});
