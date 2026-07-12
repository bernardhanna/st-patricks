<?php

require_once dirname(__DIR__, 2) . '/inc/newsletter-functions.php';

beforeEach(function () {
    __wp_stub('home_url', fn ($path = '') => 'https://example.com' . $path);
});

test('newsletter subtext links plain click here text to the GP eNewsletter signup', function () {
    $html = matrix_prepare_newsletter_subtext('<p>For healthcare newsletter click here</p>');

    expect($html)->toBe('<p>For healthcare newsletter <a href="https://example.com/subscribe-to-our-gp-enewsletter/">click here</a></p>');
});

test('newsletter subtext preserves existing editor links', function () {
    $html = matrix_prepare_newsletter_subtext('<p>For healthcare newsletter <a href="https://example.com/custom/">click here</a></p>');

    expect($html)->toBe('<p>For healthcare newsletter <a href="https://example.com/custom/">click here</a></p>');
});

test('newsletter subtext links click here text when other links already exist', function () {
    $html = matrix_prepare_newsletter_subtext('<p>Read <a href="https://example.com/news/">news</a> or click here</p>');

    expect($html)->toBe('<p>Read <a href="https://example.com/news/">news</a> or <a href="https://example.com/subscribe-to-our-gp-enewsletter/">click here</a></p>');
});

test('newsletter subtext ignores click here text in attributes', function () {
    $html = matrix_prepare_newsletter_subtext('<p><img alt="click here"> For healthcare newsletter click here</p>');

    expect($html)->toBe('<p><img alt="click here"> For healthcare newsletter <a href="https://example.com/subscribe-to-our-gp-enewsletter/">click here</a></p>');
});
