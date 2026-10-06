<?php

require_once dirname(__DIR__, 2) . '/inc/link-functions.php';
require_once dirname(__DIR__, 2) . '/inc/newsletter-functions.php';
require_once dirname(__DIR__, 2) . '/inc/forms/brevo-functions.php';

function matrix_render_newsletter_template_for_test(array $fields, array $args = []): string
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
        '<a href="https://example.test/campaigns/subscribe-to-our-gp-enewsletter/" class="text-[#C6ECF4] hover:underline">subscribe to our GP e-newsletter<span class="sr-only"> (healthcare professionals)</span></a>'
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

    expect($html)->toContain('<a href="https://example.test/custom">subscribe to our GP e-newsletter</a>')
        ->and($html)->not->toContain('>Click here</a>');
});

test('newsletter terms links use the light aqua colour at 12px', function () {
    $html = matrix_render_newsletter_template_for_test([
        'newsletter_enable' => true,
        'newsletter_heading' => 'Newsletter',
        'newsletter_subtext' => '',
        'newsletter_action' => '',
        'require_terms' => true,
        'terms_link' => ['url' => 'https://example.test/terms', 'title' => 'Terms & Conditions', 'target' => '_self'],
        'privacy_link' => ['url' => 'https://example.test/privacy', 'title' => 'Privacy Policy', 'target' => '_self'],
    ]);

    expect($html)->toContain('class="text-[12px] font-medium leading-4 text-[#C6ECF4] hover:underline"')
        ->and($html)->toContain('Terms &amp; Conditions');
});

test('newsletter email placeholder replaces inaccessible joeblogs copy', function () {
    $html = matrix_render_newsletter_template_for_test([
        'newsletter_enable' => true,
        'newsletter_heading' => 'Newsletter',
        'newsletter_subtext' => '',
        'newsletter_action' => '',
        'email_placeholder' => 'Joeblogs@mail.com',
        'require_terms' => false,
    ]);

    expect($html)->toContain('placeholder="Enter email address"')
        ->and($html)->not->toContain('Joeblogs@mail.com');
});

test('newsletter block can override copy and sign a mailchimp audience without healthcare click here', function () {
    $html = matrix_render_newsletter_template_for_test([
        'newsletter_enable' => false,
        'newsletter_provider' => 'mailchimp',
        'newsletter_heading' => 'Latest News',
        'newsletter_subtext' => '<p>For healthcare newsletter click here</p>',
        'newsletter_action' => '',
        'require_terms' => false,
    ], [
        'force' => true,
        'heading' => 'Sign-up to get the GP Newsletter',
        'subtext' => '<p>Our quarterly e-Newsletter is tailored to GPs and healthcare professionals.</p>',
        'list_id' => 'gpnlst1234',
        'link_healthcare_signup' => false,
    ]);

    $signature = matrix_newsletter_list_signature('gpnlst1234', 'mailchimp');

    expect($html)->toContain('Sign-up to get the GP Newsletter')
        ->and($html)->toContain('Our quarterly e-Newsletter is tailored to GPs and healthcare professionals.')
        ->and($html)->not->toContain('subscribe to our GP e-newsletter')
        ->and($html)->toContain('name="_cfg_newsletter_list_id" value="gpnlst1234"')
        ->and($html)->toContain('name="_cfg_newsletter_sig" value="' . $signature . '"')
        ->and($html)->not->toContain('_cfg_brevo_list_id');
});

test('matrix_page_has_newsletter_block detects a newsletter flexi row', function () {
    __wp_stub('get_field', function ($field, $post_id = false) {
        if ($field === 'flexible_content_blocks' && (int) $post_id === 1377) {
            return [
                ['acf_fc_layout' => 'hero_with_breadcrumbs'],
                ['acf_fc_layout' => 'newsletter', 'heading' => 'GP Newsletter'],
            ];
        }

        return null;
    });

    expect(matrix_page_has_newsletter_block(1377))->toBeTrue()
        ->and(matrix_page_has_newsletter_block(12))->toBeFalse();
});
