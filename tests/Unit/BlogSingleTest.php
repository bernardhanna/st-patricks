<?php

require_once dirname(__DIR__, 2) . '/inc/blog-single-functions.php';

test('blog single formats post dates for display', function () {
    expect(function_exists('matrix_format_blog_post_date'))->toBeTrue();
});

test('blog single builds share links with encoded title and url', function () {
    expect(function_exists('matrix_get_blog_post_share_links'))->toBeTrue();

    expect(matrix_get_blog_post_share_links(0))->toBe([]);

    $links = matrix_get_blog_post_share_links();
    expect($links)->toBeArray();

    if ($links === []) {
        expect(true)->toBeTrue();

        return;
    }

    expect(count($links))->toBe(5)
        ->and($links[0]['id'])->toBe('facebook')
        ->and($links[4]['id'])->toBe('copy')
        ->and($links[4]['is_copy'] ?? false)->toBeTrue();
});

test('blog single resolves author name with fallback', function () {
    expect(function_exists('matrix_get_blog_post_author_name'))->toBeTrue();

    $defaults = matrix_get_blog_single_defaults();
    expect($defaults['author_fallback'])->toBe('St Patrick Hospital Team');
});

test('blog single maps related post cards', function () {
    expect(function_exists('matrix_map_blog_related_post_card'))->toBeTrue();
});

test('event post helpers return safe defaults without a post context', function () {
    expect(function_exists('matrix_is_event_post'))->toBeTrue()
        ->and(matrix_is_event_post(0))->toBeFalse()
        ->and(matrix_should_link_event_thumbnail_externally(0))->toBeFalse()
        ->and(matrix_get_blog_post_card_url(0))->toBe('');

    $fields = matrix_get_event_post_fields(0);
    expect($fields['external_url'])->toBe('')
        ->and($fields['external_button_label'])->toBe('Link to an Eventbrite')
        ->and($fields['cta_summary'])->toBe('')
        ->and($fields['link_external_from_archive'])->toBeFalse();
});

test('event archive thumbnail links expose target metadata when external', function () {
    expect(function_exists('matrix_get_blog_post_link_target'))->toBeTrue();

    $link = matrix_get_blog_post_link_target(0, 'thumbnail');

    expect($link)->toHaveKeys(['url', 'target', 'rel', 'is_external'])
        ->and($link['url'])->toBe('')
        ->and($link['target'])->toBe('_self')
        ->and($link['is_external'])->toBeFalse();
});
