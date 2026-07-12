<?php

require_once dirname(__DIR__, 2) . '/inc/link-functions.php';

function matrix_render_newsletter_template_for_test(array $fields): string
{
    __wp_stub('get_field', fn ($field, $post_id = false) => $fields[$field] ?? null);
    __wp_stub('home_url', fn ($path = '') => 'https://st-patricks.test' . $path);
    __wp_stub('admin_url', fn ($path = '') => 'https://st-patricks.test/wp-admin/' . ltrim((string) $path, '/'));

    ob_start();
    require dirname(__DIR__, 2) . '/template-parts/footer/newsletter.php';

    return (string) ob_get_clean();
}

test('newsletter plain click here copy renders as light blue healthcare link', function () {
    $html = matrix_render_newsletter_template_for_test([
        'newsletter_enable' => 1,
        'newsletter_subtext' => '<p>For healthcare newsletter click here</p>',
        'require_terms' => 0,
    ]);

    expect($html)->toContain(
        '<p>For healthcare newsletter <a href="https://st-patricks.test/campaigns/subscribe-to-our-gp-enewsletter/" class="text-[#7ED0E0] hover:underline">click here</a></p>'
    );
});
