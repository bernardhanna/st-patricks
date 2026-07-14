<?php

require_once dirname(__DIR__, 2) . '/inc/link-functions.php';

function matrix_render_newsletter_template_for_test(array $fields): string
{
    __wp_stub('get_field', fn ($field, $post_id = false) => $fields[$field] ?? null);
    __wp_stub('home_url', fn ($path = '') => 'https://example.test' . $path);
    __wp_stub('admin_url', fn ($path = '') => 'https://example.test/wp-admin/' . ltrim((string) $path, '/'));

    ob_start();
    require dirname(__DIR__, 2) . '/template-parts/footer/newsletter.php';

    return ob_get_clean();
}

test('newsletter subtext links plain click here to the gp enewsletter signup', function () {
    $html = matrix_render_newsletter_template_for_test([
        'newsletter_enable' => true,
        'newsletter_heading' => 'Newsletter',
        'newsletter_subtext' => '<p>For healthcare newsletter Click here</p>',
        'newsletter_action' => '',
        'require_terms' => false,
    ]);

    expect($html)->toContain(
        '<a href="https://example.test/campaigns/subscribe-to-our-gp-enewsletter/" class="text-[#7ED0E0] hover:underline">Click here</a>'
    );
});

test('newsletter subtext keeps editor managed click here links unchanged', function () {
    $html = matrix_render_newsletter_template_for_test([
        'newsletter_enable' => true,
        'newsletter_heading' => 'Newsletter',
        'newsletter_subtext' => '<p>For healthcare newsletter <a href="https://example.test/custom">Click here</a></p>',
        'newsletter_action' => '',
        'require_terms' => false,
    ]);

    expect($html)->toContain('<a href="https://example.test/custom">Click here</a>')
        ->and($html)->not->toContain('/campaigns/subscribe-to-our-gp-enewsletter/');
});
