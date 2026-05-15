<?php

require_once dirname(__DIR__, 2) . '/inc/contact-form-functions.php';

test('contact form exposes your portal defaults', function () {
    $defaults = matrix_get_contact_form_defaults();

    expect($defaults['form_style'])->toBe('your_portal')
        ->and($defaults['background_color'])->toBe('#FBF8F3')
        ->and($defaults['submit_label'])->toContain('Your Portal');
});

test('contact form prepares portal checkbox schema', function () {
    $form = matrix_prepare_contact_form([
        'form_style' => 'your_portal',
        'recipient_email' => 'portal@example.com',
    ]);

    expect($form['form_style'])->toBe('your_portal')
        ->and($form['recipient_email'])->toBe('portal@example.com')
        ->and($form['checkboxes'])->toHaveCount(4)
        ->and($form['checkboxes'][0]['name'])->toBe('consent_portal')
        ->and($form['submission_uid'])->not->toBe('');
});

test('contact form action url targets admin post handler', function () {
    $url = matrix_get_contact_form_action_url();

    expect($url)->toContain('admin-post.php');
});
