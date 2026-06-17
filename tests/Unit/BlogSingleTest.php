<?php

require_once dirname(__DIR__, 2) . '/inc/migrate-functions.php';
require_once dirname(__DIR__, 2) . '/inc/blog-single-functions.php';

test('blog single formats post dates for display', function () {
    expect(function_exists('matrix_format_blog_post_date'))->toBeTrue()
        ->and(matrix_get_post_date_display_format())->toBe('d/m/y');
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

test('migrated post content formatter removes duplicate umbraco headings', function () {
    expect(function_exists('matrix_format_migrated_post_content'))->toBeTrue();

    $html = '<div class="section-head hide-for-side"><h2>Section title</h2></div>'
        . '<h3 class="hide-for-main">Section title</h3>'
        . '<p>Body copy</p>'
        . '<ul><li>One</li><li>Two</li></ul>';

    $formatted = matrix_format_migrated_post_content($html);

    expect($formatted)->toContain('<h2>Section title</h2>')
        ->and($formatted)->not->toContain('hide-for-main')
        ->and($formatted)->not->toContain('section-head')
        ->and($formatted)->toContain('<ul><li>One</li>');
});

test('migrated post content formatter removes leading figure when it matches featured image', function () {
    expect(function_exists('matrix_remove_leading_duplicate_featured_image_from_content'))->toBeTrue();

    $html = '<figure><img src="https://example.com/wp-content/uploads/2026/06/4.png" alt=""></figure>'
        . '<p class="intro">Intro paragraph</p>'
        . '<p>Body copy</p>';

    $formatted = matrix_remove_leading_duplicate_featured_image_from_content(
        $html,
        1227,
        'https://example.com/wp-content/uploads/2026/06/4.png'
    );

    expect($formatted)->not->toContain('<figure>')
        ->and($formatted)->toContain('<p class="intro">Intro paragraph</p>')
        ->and($formatted)->toContain('<p>Body copy</p>');
});

test('migrated post content formatter keeps leading figure when image differs from featured image', function () {
    $html = '<figure><img src="https://example.com/wp-content/uploads/2026/06/other.png" alt=""></figure>'
        . '<p>Body copy</p>';

    $formatted = matrix_remove_leading_duplicate_featured_image_from_content(
        $html,
        1227,
        'https://example.com/wp-content/uploads/2026/06/4.png'
    );

    expect($formatted)->toContain('<figure>')
        ->and($formatted)->toContain('other.png');
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

test('event archive card links expose target metadata when external', function () {
    expect(function_exists('matrix_get_blog_post_link_target'))->toBeTrue()
        ->and(function_exists('matrix_should_link_event_archive_externally'))->toBeTrue();

    $link = matrix_get_blog_post_link_target(0, 'archive');

    expect($link)->toHaveKeys(['url', 'target', 'rel', 'is_external'])
        ->and($link['url'])->toBe('')
        ->and($link['target'])->toBe('_self')
        ->and($link['is_external'])->toBeFalse();
});
