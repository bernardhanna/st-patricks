<?php

require_once dirname(__DIR__, 2) . '/inc/link-functions.php';

function matrix_render_newsletter_template(array $fields): string
{
    __wp_stub('home_url', fn ($path = '') => 'https://example.com' . $path);
    __wp_stub('get_field', fn ($field, $post_id = false) => $fields[$field] ?? null);

    ob_start();
    require dirname(__DIR__, 2) . '/template-parts/footer/newsletter.php';

    return ob_get_clean();
}

test('newsletter subtext links healthcare click here copy in light blue', function () {
    $html = matrix_render_newsletter_template([
        'newsletter_enable' => true,
        'newsletter_subtext' => '<p>For healthcare newsletter click here</p>',
        'require_terms' => false,
    ]);

    expect($html)->toContain('href="https://example.com/subscribe-to-our-gp-enewsletter/"')
        ->and($html)->toContain('class="text-primary hover:underline"')
        ->and($html)->toContain('[&_a]:text-primary')
        ->and($html)->toContain('[&_a:hover]:underline')
        ->and($html)->toContain('<p>For healthcare newsletter <a');
});
