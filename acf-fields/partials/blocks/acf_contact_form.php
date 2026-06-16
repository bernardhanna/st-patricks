<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$defaults = matrix_get_contact_form_defaults();

$contact_form = new FieldsBuilder('contact_form', [
    'label' => 'Contact Form',
]);

$contact_form
    ->addTab('Content', ['label' => 'Content'])
        ->addSelect('form_style', [
            'label' => 'Form Style',
            'choices' => [
                'your_portal' => 'Your Portal Registration',
            ],
            'default_value' => $defaults['form_style'],
            'ui' => 1,
        ])
        ->addColorPicker('background_color', [
            'label' => 'Background Color',
            'default_value' => $defaults['background_color'],
        ])
        ->addText('submit_label', [
            'label' => 'Submit Button Label',
            'default_value' => $defaults['submit_label'],
        ])
        ->addText('success_message', [
            'label' => 'Success Message',
            'default_value' => $defaults['success_message'],
        ])
        ->addTextarea('date_of_birth_help', [
            'label' => 'Date Of Birth Help Text',
            'default_value' => $defaults['date_of_birth_help'],
            'rows' => 2,
        ])
        ->addLink('privacy_policy_link', [
            'label' => 'Privacy Policy Link',
            'return_format' => 'array',
        ])
        ->addText('privacy_policy_label', [
            'label' => 'Privacy Policy Link Label',
            'default_value' => $defaults['privacy_policy_label'],
        ])
        ->addRepeater('consent_items', [
            'label' => 'Consent Checkboxes',
            'instructions' => 'Optional overrides for the default Your Portal consent copy. Leave empty to use defaults.',
            'min' => 0,
            'max' => 4,
            'layout' => 'block',
            'button_label' => 'Add Consent Item',
        ])
            ->addText('title', [
                'label' => 'Checkbox Title',
            ])
            ->addTextarea('description', [
                'label' => 'Supporting Text',
                'rows' => 3,
            ])
            ->addTrueFalse('required', [
                'label' => 'Required',
                'ui' => 1,
            ])
        ->endRepeater()

    ->addTab('Email', ['label' => 'Email'])
        ->addText('form_name', [
            'label' => 'Form Name',
            'instructions' => 'Used in notification emails and saved entries.',
            'default_value' => $defaults['form_name'],
        ])
        ->addText('email_subject', [
            'label' => 'Email Subject',
            'default_value' => $defaults['subject'],
        ])
        ->addEmail('recipient_email', [
            'label' => 'Recipient Email',
            'instructions' => 'Optional. Falls back to the WordPress admin email.',
        ])
        ->addText('bcc_email', [
            'label' => 'BCC Email(s)',
            'instructions' => 'Optional. Comma-separated list.',
        ])
        ->addTrueFalse('save_to_db', [
            'label' => 'Save Submissions To Database',
            'instructions' => 'Stores entries in Form Entries for review in wp-admin.',
            'ui' => 1,
            'default_value' => 1,
        ]);

return $contact_form;
