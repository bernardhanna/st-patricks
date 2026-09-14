<?php

require_once dirname(__DIR__, 2) . '/inc/content-section-functions.php';
require_once dirname(__DIR__, 2) . '/inc/contact-form-functions.php';

test('contact form exposes your portal defaults', function () {
    $defaults = matrix_get_contact_form_defaults();

    expect($defaults['form_style'])->toBe('your_portal')
        ->and($defaults['background_type'])->toBe('cream')
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

test('contact form section wrapper matches portal figma width and padding', function () {
    expect(matrix_get_contact_form_section_wrapper_class_names())->toContain('max-w-[578px]')
        ->and(matrix_get_contact_form_section_wrapper_class_names())->toContain('px-3')
        ->and(matrix_get_contact_form_section_wrapper_class_names())->toContain('pt-8')
        ->and(matrix_get_contact_form_section_wrapper_class_names())->toContain('pb-6')
        ->and(matrix_get_contact_form_section_wrapper_class_names())->toContain('lg:pt-[72px]')
        ->and(matrix_get_contact_form_section_wrapper_class_names())->toContain('lg:pb-[50px]')
        ->and(matrix_get_contact_form_section_wrapper_class_names('full'))->toContain('lg:py-[100px]')
        ->and(matrix_get_contact_form_section_wrapper_class_names('bottom_only'))->toContain('lg:pb-[50px]')
        ->and(matrix_get_contact_form_section_wrapper_class_names('bottom_only'))->toContain('lg:pt-0')
        ->and(matrix_get_contact_form_section_wrapper_class_names('compact', 'mailing_list'))->toContain('max-w-[578px]');
});

test('contact form mobile layout matches figma register spacing', function () {
    expect(matrix_get_contact_form_form_class_names())->toContain('gap-3')
        ->and(matrix_get_contact_form_form_class_names())->toContain('lg:gap-6')
        ->and(matrix_get_contact_form_row_class_names())->toBe('portal-contact-form__row');
});

test('contact form exposes date of birth info icon and optional toggle', function () {
    expect(function_exists('matrix_get_contact_form_date_of_birth_info_icon_svg'))->toBeTrue()
        ->and(matrix_get_contact_form_date_of_birth_info_icon_svg())->toContain('<svg')
        ->and(matrix_get_contact_form_date_of_birth_info_icon_svg())->toContain('fill="#024B79"')
        ->and(function_exists('matrix_get_contact_form_date_of_birth_calendar_icon_svg'))->toBeTrue()
        ->and(matrix_get_contact_form_date_of_birth_calendar_icon_svg())->toContain('stroke="#024B79"')
        ->and(matrix_resolve_contact_form_show_date_of_birth_info(null))->toBeTrue()
        ->and(matrix_resolve_contact_form_show_date_of_birth_info(0))->toBeFalse();

    $form = matrix_prepare_contact_form([
        'date_of_birth_show_info' => 0,
    ]);

    expect($form['show_date_of_birth_info'])->toBeFalse()
        ->and($form['date_of_birth_help'])->not->toBe('');
});

test('contact form mailing list style uses name email and mailing list consents', function () {
    expect(matrix_resolve_contact_form_style('mailing_list'))->toBe('mailing_list')
        ->and(matrix_resolve_contact_form_style('unknown'))->toBe('your_portal');

    $form = matrix_prepare_contact_form([
        'form_style' => 'mailing_list',
        'heading' => 'Join SUAN',
        'intro' => '<p>You will receive emails regarding updates from the network.</p>',
        'recipient_email' => 'sfitzharris@stpatricks.ie',
        'privacy_policy_link' => [
            'url' => 'https://example.com/privacy',
        ],
    ]);

    expect($form['form_style'])->toBe('mailing_list')
        ->and($form['heading'])->toBe('Join SUAN')
        ->and($form['heading_tag'])->toBe('h2')
        ->and($form['intro'])->toContain('updates from the network')
        ->and($form['recipient_email'])->toBe('sfitzharris@stpatricks.ie')
        ->and($form['privacy_policy_url'])->toBe('https://example.com/privacy')
        ->and($form['checkboxes'])->toHaveCount(2)
        ->and($form['checkboxes'][0]['name'])->toBe('consent_mailing_list')
        ->and($form['checkboxes'][1]['name'])->toBe('consent_privacy')
        ->and($form['checkboxes'][1]['is_privacy'])->toBeTrue()
        ->and($form['wrapper_classes'])->toContain('max-w-[578px]')
        ->and($form['fields_wrapper_classes'])->toContain('max-w-[578px]')
        ->and($form['show_role_field'])->toBeFalse();
});

test('contact form mailing list can include fcs role radios', function () {
    $form = matrix_prepare_contact_form([
        'form_style' => 'mailing_list',
        'show_role_field' => 1,
    ]);

    expect($form['show_role_field'])->toBeTrue()
        ->and($form['role_label'])->toContain('best describes your role')
        ->and($form['role_options'])->toHaveCount(4)
        ->and($form['role_options'][0])->toContain('Family member')
        ->and($form['role_options'][3])->toBe('Other');
});

test('contact form background can be white cream or custom', function () {
    expect(matrix_resolve_contact_form_background_type('white'))->toBe('white')
        ->and(matrix_get_contact_form_background_color('white'))->toBe('#FFFFFF')
        ->and(matrix_get_contact_form_background_color('cream'))->toBe('#FBF8F3')
        ->and(matrix_get_contact_form_background_color('color', '#C6ECF4'))->toBe('#C6ECF4');

    $white = matrix_prepare_contact_form([
        'background_type' => 'white',
    ]);
    $legacy_white = matrix_prepare_contact_form([
        'background_color' => '#FFFFFF',
    ]);

    expect($white['background_type'])->toBe('white')
        ->and($white['background_color'])->toBe('#FFFFFF')
        ->and($legacy_white['background_type'])->toBe('white')
        ->and($legacy_white['background_color'])->toBe('#FFFFFF');
});

test('contact form can enable a signed brevo list without turning it on by default', function () {
    expect(matrix_resolve_contact_form_enable_brevo(null))->toBeFalse()
        ->and(matrix_resolve_contact_form_enable_brevo('0'))->toBeFalse()
        ->and(matrix_resolve_contact_form_enable_brevo('1'))->toBeTrue()
        ->and(matrix_resolve_contact_form_brevo_list_id('42'))->toBe(42);

    $off = matrix_prepare_contact_form([
        'form_style' => 'mailing_list',
    ]);
    $on = matrix_prepare_contact_form([
        'form_style' => 'mailing_list',
        'enable_brevo' => '1',
        'brevo_list_id' => '18',
    ]);

    expect($off['enable_brevo'])->toBeFalse()
        ->and($off['brevo_list_id'])->toBe(0)
        ->and($on['enable_brevo'])->toBeTrue()
        ->and($on['brevo_list_id'])->toBe(18);
});
