<?php

require_once dirname(__DIR__, 2) . '/inc/content-section-functions.php';
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

test('contact form section wrapper matches portal figma width and padding', function () {
    expect(matrix_get_contact_form_section_wrapper_class_names())->toContain('max-w-[578px]')
        ->and(matrix_get_contact_form_section_wrapper_class_names())->toContain('px-3')
        ->and(matrix_get_contact_form_section_wrapper_class_names())->toContain('lg:py-[100px]')
        ->and(matrix_get_contact_form_section_wrapper_class_names('bottom_only'))->toContain('lg:pb-[100px]')
        ->and(matrix_get_contact_form_section_wrapper_class_names('bottom_only'))->toContain('lg:pt-0');
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
