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
        'privacy_policy_label' => 'Privacy Policy.',
    ];
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
        'privacy_policy_url' => $privacy_url,
        'privacy_policy_label' => trim((string) ($args['privacy_policy_label'] ?? '')) ?: (string) $defaults['privacy_policy_label'],
        'phone_country_options' => matrix_get_contact_form_phone_country_options(),
        'checkboxes' => $checkboxes,
        'submission_uid' => function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid('form-', true),
        'wrapper_classes' => trim((string) ($args['wrapper_classes'] ?? 'mx-auto flex w-full max-w-[578px] flex-col px-5 py-12 lg:px-0 lg:py-[100px]')),
    ];
}

function matrix_get_contact_form_action_url()
{
    return function_exists('admin_url') ? admin_url('admin-post.php') : '/wp-admin/admin-post.php';
}
