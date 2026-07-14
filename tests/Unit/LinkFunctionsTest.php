<?php

require_once dirname(__DIR__, 2) . '/inc/link-functions.php';

beforeEach(function () {
    __wp_stub('home_url', fn ($path = '') => 'https://www.stpatricks.ie' . $path);
});

test('matrix_is_external_url detects off-site http links', function () {
    expect(matrix_is_external_url('https://www.walkinmyshoes.ie/'))->toBeTrue()
        ->and(matrix_is_external_url('https://soundcloud.com/example'))->toBeTrue()
        ->and(matrix_is_external_url('/about-us/'))->toBeFalse()
        ->and(matrix_is_external_url('mailto:hello@example.com'))->toBeFalse();
});

test('matrix_normalize_link_target opens external urls in a new tab', function () {
    expect(matrix_normalize_link_target('https://example.com/page'))->toBe('_blank')
        ->and(matrix_normalize_link_target('/about-us/'))->toBe('_self')
        ->and(matrix_normalize_link_target('https://example.com/page', '_self'))->toBe('_blank');
});

test('matrix_process_external_links_in_html adds target blank and rel', function () {
    $html = '<p>Read <a href="https://example.com/report.pdf">the report</a>.</p>';
    $processed = matrix_process_external_links_in_html($html);

    expect($processed)->toContain('target="_blank"')
        ->and($processed)->toContain('rel="noopener noreferrer"');
});

test('matrix_normalize_acf_link forces external acf links to open in a new tab', function () {
    $link = matrix_normalize_acf_link([
        'title' => 'Walk in My Shoes',
        'url' => 'https://www.walkinmyshoes.ie/',
        'target' => '_self',
    ]);

    expect($link)->toMatchArray([
        'title' => 'Walk in My Shoes',
        'url' => 'https://www.walkinmyshoes.ie/',
        'target' => '_blank',
        'rel' => 'noopener noreferrer',
    ]);
});

test('matrix_link_newsletter_subtext_click_here links plain newsletter click here text', function () {
    expect(function_exists('matrix_link_newsletter_subtext_click_here'))->toBeTrue();

    $html = matrix_link_newsletter_subtext_click_here('<p>For healthcare newsletter click here</p>');

    expect($html)->toContain('<a href="https://www.stpatricks.ie/campaigns/subscribe-to-our-gp-enewsletter/">click here</a>');
});
