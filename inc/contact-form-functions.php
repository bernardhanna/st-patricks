<?php

function matrix_get_contact_form_defaults()
{
    return [
        'form_style' => 'your_portal',
        'background_color' => '#FBF8F3',
        'submit_label' => 'Submit to Register for Your Portal',
        'success_message' => 'Thanks! Your registration request has been sent.',
        'form_name' => 'Your Portal Registration',
        'subject' => 'Your Portal – new registration request',
        'date_of_birth_help' => 'We use your date of birth to verify your identity against our records.',
        'date_of_birth_show_info' => true,
        'privacy_policy_label' => 'Privacy Policy.',
    ];
}

function matrix_resolve_contact_form_show_date_of_birth_info($value = null)
{
    if ($value === null || $value === '') {
        return true;
    }

    return (bool) $value;
}

function matrix_get_contact_form_date_of_birth_info_icon_svg()
{
    return '<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="28" viewBox="0 0 24 28" fill="none" class="shrink-0"><path d="M11 19H13V13H11V19ZM12 11C12.2833 11 12.521 10.904 12.713 10.712C12.905 10.52 13.0007 10.2827 13 10C12.9993 9.71733 12.9033 9.48 12.712 9.288C12.5207 9.096 12.2833 9 12 9C11.7167 9 11.4793 9.096 11.288 9.288C11.0967 9.48 11.0007 9.71733 11 10C10.9993 10.2827 11.0953 10.5203 11.288 10.713C11.4807 10.9057 11.718 11.0013 12 11ZM12 24C10.6167 24 9.31667 23.7373 8.1 23.212C6.88334 22.6867 5.825 21.9743 4.925 21.075C4.025 20.1757 3.31267 19.1173 2.788 17.9C2.26333 16.6827 2.00067 15.3827 2 14C1.99933 12.6173 2.262 11.3173 2.788 10.1C3.314 8.88267 4.02633 7.82433 4.925 6.925C5.82367 6.02567 6.882 5.31333 8.1 4.788C9.318 4.26267 10.618 4 12 4C13.382 4 14.682 4.26267 15.9 4.788C17.118 5.31333 18.1763 6.02567 19.075 6.925C19.9737 7.82433 20.6863 8.88267 21.213 10.1C21.7397 11.3173 22.002 12.6173 22 14C21.998 15.3827 21.7353 16.6827 21.212 17.9C20.6887 19.1173 19.9763 20.1757 19.075 21.075C18.1737 21.9743 17.1153 22.687 15.9 23.213C14.6847 23.739 13.3847 24.0013 12 24Z" fill="#024B79"/></svg>';
}

function matrix_get_contact_form_date_of_birth_calendar_icon_svg()
{
    return '<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="shrink-0"><path d="M19 4H5C3.89543 4 3 4.89543 3 6V20C3 21.1046 3.89543 22 5 22H19C20.1046 22 21 21.1046 21 20V6C21 4.89543 20.1046 4 19 4Z" stroke="#024B79" stroke-width="1.5" stroke-linejoin="round"/><path d="M16 2V6M8 2V6M3 10H21" stroke="#024B79" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

function matrix_get_contact_form_phone_country_options()
{
    return [
        '+353' => 'Ireland (+353)',
        '+44' => 'United Kingdom (+44)',
        '+1' => 'United States / Canada (+1)',
        '+33' => 'France (+33)',
        '+49' => 'Germany (+49)',
    ];
}

function matrix_get_contact_form_your_portal_checkboxes($overrides = [])
{
    $defaults = [
        [
            'name' => 'consent_portal',
            'required' => true,
            'title' => 'I request to sign up to use Your Portal. *',
            'description' => 'By checking this box, you are confirming that you wish to proceed with registering for Your Portal.',
        ],
        [
            'name' => 'consent_details_correct',
            'required' => true,
            'title' => 'I confirm that the details I have provided above are correct. *',
            'description' => 'Confirming that your details are correct enables us to correctly match and verify your information in our electronic health records.',
        ],
        [
            'name' => 'consent_suits',
            'required' => true,
            'title' => 'I understand that the Service User IT Support (SUITS) service is not a medical or clinical service, and that SUITS cannot give guidance on any aspect of my mental health or medical care. The query I submit here will be sent to the SUITS team, rather than my SPMHS care team. *',
            'description' => '',
        ],
        [
            'name' => 'consent_newsletter',
            'required' => false,
            'title' => 'By signing to our newsletter, you agree to our',
            'description' => '',
            'is_privacy' => true,
        ],
    ];

    if (! is_array($overrides) || $overrides === []) {
        return $defaults;
    }

    $merged = [];
    foreach ($defaults as $index => $item) {
        $override = $overrides[$index] ?? [];
        $merged[] = array_merge($item, is_array($override) ? $override : []);
    }

    return $merged;
}

function matrix_prepare_contact_form($args = [])
{
    $defaults = matrix_get_contact_form_defaults();
    $form_style = trim((string) ($args['form_style'] ?? $defaults['form_style']));
    if ($form_style === '') {
        $form_style = 'your_portal';
    }

    $privacy_url = '';
    $privacy_link = $args['privacy_policy_link'] ?? null;
    if (is_array($privacy_link) && ! empty($privacy_link['url'])) {
        $privacy_url = (string) $privacy_link['url'];
    }

    $checkboxes = is_array($args['checkboxes'] ?? null) && $args['checkboxes'] !== []
        ? $args['checkboxes']
        : matrix_get_contact_form_your_portal_checkboxes();

    return [
        'section_id' => trim((string) ($args['section_id'] ?? '')),
        'data_block' => trim((string) ($args['data_block'] ?? '')),
        'form_style' => $form_style,
        'form_id' => 'contact-form-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid()),
        'background_color' => trim((string) ($args['background_color'] ?? '')) ?: (string) $defaults['background_color'],
        'submit_label' => trim((string) ($args['submit_label'] ?? '')) ?: (string) $defaults['submit_label'],
        'success_message' => trim((string) ($args['success_message'] ?? '')) ?: (string) $defaults['success_message'],
        'form_name' => trim((string) ($args['form_name'] ?? '')) ?: (string) $defaults['form_name'],
        'subject' => trim((string) ($args['subject'] ?? '')) ?: (string) $defaults['subject'],
        'recipient_email' => trim((string) ($args['recipient_email'] ?? '')),
        'bcc_email' => trim((string) ($args['bcc_email'] ?? '')),
        'save_to_db' => ! empty($args['save_to_db']),
        'date_of_birth_help' => trim((string) ($args['date_of_birth_help'] ?? '')) ?: (string) $defaults['date_of_birth_help'],
        'show_date_of_birth_info' => matrix_resolve_contact_form_show_date_of_birth_info($args['date_of_birth_show_info'] ?? null),
        'privacy_policy_url' => $privacy_url,
        'privacy_policy_label' => trim((string) ($args['privacy_policy_label'] ?? '')) ?: (string) $defaults['privacy_policy_label'],
        'phone_country_options' => matrix_get_contact_form_phone_country_options(),
        'checkboxes' => $checkboxes,
        'submission_uid' => function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid('form-', true),
        'wrapper_classes' => trim((string) ($args['wrapper_classes'] ?? matrix_get_contact_form_section_wrapper_class_names(
            (string) ($args['vertical_padding'] ?? 'default')
        ))),
    ];
}

function matrix_get_contact_form_form_class_names()
{
    return 'flex w-full flex-col gap-3 portal-contact-form lg:gap-6';
}

function matrix_get_contact_form_row_class_names()
{
    return 'portal-contact-form__row';
}

function matrix_get_contact_form_section_wrapper_class_names($vertical_padding = 'default')
{
    return implode(' ', array_filter([
        'mx-auto',
        'flex',
        'w-full',
        'max-w-[578px]',
        'flex-col',
        'items-center',
        'px-3',
        'lg:px-0',
        matrix_get_section_vertical_padding_classes(
            (string) $vertical_padding,
            'lg:py-[100px]',
            'lg:pb-[100px]'
        ),
    ]));
}

function matrix_get_contact_form_action_url()
{
    return function_exists('admin_url') ? admin_url('admin-post.php') : '/wp-admin/admin-post.php';
}
