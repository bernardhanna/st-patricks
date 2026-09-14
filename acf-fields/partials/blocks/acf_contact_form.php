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
                'mailing_list' => 'Mailing list / network registration',
            ],
            'default_value' => $defaults['form_style'],
            'ui' => 1,
        ])
        ->addText('heading', [
            'label' => 'Title',
            'instructions' => 'Optional. Shown above the form. Leave blank to hide the title.',
        ])
        ->addSelect('heading_tag', [
            'label' => 'Title Tag',
            'choices' => [
                'h2' => 'H2',
                'h3' => 'H3',
                'h4' => 'H4',
            ],
            'default_value' => 'h2',
        ])
        ->addWysiwyg('intro', [
            'label' => 'Intro Copy',
            'media_upload' => 0,
            'tabs' => 'all',
            'toolbar' => 'basic',
        ])
        ->addSelect('background_type', [
            'label' => 'Background',
            'instructions' => 'White or cream, matching other page sections. Use custom colour only when a one-off is needed.',
            'choices' => [
                'white' => 'White',
                'cream' => 'Cream',
                'color' => 'Custom Color',
            ],
            'default_value' => $defaults['background_type'],
            'ui' => 1,
        ])
        ->addColorPicker('background_color', [
            'label' => 'Custom Background Color',
            'default_value' => $defaults['background_color'],
            'conditional_logic' => [[
                [
                    'field' => 'background_type',
                    'operator' => '==',
                    'value' => 'color',
                ],
            ]],
        ])
        ->addSelect('vertical_padding', [
            'label' => 'Vertical Padding',
            'instructions' => 'Compact uses half the usual section spacing, with a little more padding at the top than the bottom.',
            'choices' => [
                'compact' => 'Compact (more top than bottom)',
                'full' => 'Full (100px top and bottom)',
            ],
            'default_value' => 'compact',
            'ui' => 1,
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
            'instructions' => 'Shown in a toast when the info icon is clicked.',
            'default_value' => $defaults['date_of_birth_help'],
            'rows' => 2,
            'conditional_logic' => [[
                [
                    'field' => 'form_style',
                    'operator' => '==',
                    'value' => 'your_portal',
                ],
            ]],
        ])
        ->addTrueFalse('show_role_field', [
            'label' => 'Show Role Field',
            'instructions' => 'Adds the FCS role radios (family member, carer, supporter, or other).',
            'ui' => 1,
            'default_value' => 0,
            'conditional_logic' => [[
                [
                    'field' => 'form_style',
                    'operator' => '==',
                    'value' => 'mailing_list',
                ],
            ]],
        ])
        ->addTrueFalse('date_of_birth_show_info', [
            'label' => 'Show Date Of Birth Info Icon',
            'instructions' => 'Optional. Displays the info icon and toast for the date of birth field.',
            'ui' => 1,
            'default_value' => $defaults['date_of_birth_show_info'] ? 1 : 0,
            'conditional_logic' => [[
                [
                    'field' => 'form_style',
                    'operator' => '==',
                    'value' => 'your_portal',
                ],
            ]],
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
            'instructions' => 'Optional overrides for the default consent copy. Leave empty to use the defaults for the selected form style.',
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
        ])

    ->addTab('Brevo', ['label' => 'Brevo'])
        ->addRadio('enable_brevo', [
            'label' => 'Enable Brevo',
            'instructions' => 'When on, this form also adds the person to a Brevo list. The API key stays in wp-config.php (MATRIX_BREVO_KEY) or Theme Options → Newsletter — never on the page.',
            'choices' => [
                '0' => 'Off',
                '1' => 'On',
            ],
            'default_value' => '0',
            'layout' => 'horizontal',
        ])
        ->addSelect('brevo_list_id', [
            'label' => 'Brevo List',
            'instructions' => 'Choose the list this form should subscribe to. Lists are loaded from the Brevo API.',
            'choices' => [],
            'ui' => 1,
            'allow_null' => 1,
            'return_format' => 'value',
            'conditional_logic' => [[
                [
                    'field' => 'enable_brevo',
                    'operator' => '==',
                    'value' => '1',
                ],
            ]],
        ]);

return $contact_form;
