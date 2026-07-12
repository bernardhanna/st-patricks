<?php

require_once dirname(__DIR__, 2) . '/inc/link-functions.php';

$newsletter_functions = dirname(__DIR__, 2) . '/inc/newsletter-functions.php';
if (file_exists($newsletter_functions)) {
    require_once $newsletter_functions;
}

beforeEach(function () {
    __wp_stub('home_url', fn ($path = '') => 'https://www.stpatricks.ie' . $path);
});

test('newsletter subtext links the healthcare click here copy to the GP newsletter signup', function () {
    expect(function_exists('matrix_get_newsletter_subtext_html'))->toBeTrue();

    $html = matrix_get_newsletter_subtext_html('<p>For healthcare newsletter click here</p>');

    expect($html)
        ->toContain('<a href="https://www.stpatricks.ie/campaigns/subscribe-to-our-gp-enewsletter/">click here</a>')
        ->and($html)
        ->toContain('For healthcare newsletter');
});

test('newsletter subtext keeps existing rich text links unchanged', function () {
    expect(function_exists('matrix_get_newsletter_subtext_html'))->toBeTrue();

    $html = matrix_get_newsletter_subtext_html('<p>Read <a href="/existing/">more</a></p>');

    expect($html)
        ->toContain('<a href="/existing/">more</a>')
        ->not->toContain('subscribe-to-our-gp-enewsletter');
});
