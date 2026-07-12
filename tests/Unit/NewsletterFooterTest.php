<?php

$newsletter_helpers = dirname(__DIR__, 2) . '/inc/newsletter-functions.php';

if (file_exists($newsletter_helpers)) {
    require_once $newsletter_helpers;
}

test('newsletter subtitle styles and links the healthcare newsletter prompt', function () {
    expect(function_exists('matrix_get_newsletter_subtext_class_names'))->toBeTrue()
        ->and(matrix_get_newsletter_subtext_class_names())->toContain('[&_a]:text-[#7ED0E0]')
        ->and(matrix_get_newsletter_subtext_class_names())->toContain('[&_a:hover]:underline');

    expect(function_exists('matrix_prepare_newsletter_subtext'))->toBeTrue()
        ->and(matrix_prepare_newsletter_subtext('<p>For healthcare newsletter click here</p>'))
        ->toBe('<p>For healthcare newsletter <a href="/campaigns/subscribe-to-our-gp-enewsletter/">click here</a></p>');
});
