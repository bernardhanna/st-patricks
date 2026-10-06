<?php
use StoutLogic\AcfBuilder\FieldsBuilder;

$newsletter = new FieldsBuilder('newsletter', [
  'title'     => 'Newsletter Settings',
  'menu_slug' => 'theme-newsletter-settings',
  'position'  => 'normal',
  'post_id'   => 'option',
]);

$newsletter
  ->addTab('newsletter_main_tab', ['label' => 'Newsletter'])

  // Toggles
  ->addTrueFalse('newsletter_enable', [
    'label'         => 'Enable Newsletter Section (display)',
    'ui'            => 1,
    'default_value' => 1,
  ])
  ->addTrueFalse('newsletter_enabled', [
    'label'         => 'Enable Newsletter (AJAX subscribe)',
    'ui'            => 1,
    'default_value' => 1,
  ])
  ->addSelect('newsletter_provider', [
    'label'         => 'Newsletter provider',
    'choices'       => [
      'mailchimp' => 'Mailchimp',
      'brevo'     => 'Brevo',
    ],
    'default_value' => 'mailchimp',
    'ui'            => 1,
    'instructions'  => 'Mailchimp is used for newsletter signups. Brevo remains available for contact forms, or as a fallback here.',
  ])

  // Design
  ->addAccordion('newsletter_design_acc', ['label' => 'Design', 'open' => 1])
    ->addColorPicker('newsletter_bg_color', [
      'label'         => 'Background Color',
      'default_value' => '#001F33',
    ])
    ->addColorPicker('newsletter_accent_line_color', [
      'label'         => 'Accent Line Color',
      'default_value' => '#7ED0E0',
    ])
  ->addAccordion('newsletter_design_acc_end', ['endpoint' => 1])

  // Background Decor
  ->addAccordion('newsletter_background_acc', ['label' => 'Background Decor'])
    ->addImage('bg_logo_image', [
      'label'         => 'Faded Logo Image (Left/Bottom)',
      'return_format' => 'array',
      'preview_size'  => 'medium',
    ])
    ->addNumber('bg_logo_opacity', [
      'label' => 'Logo Opacity (0–1)', 'min' => 0, 'max' => 1, 'step' => 0.05, 'default_value' => 0.2,
    ])
    ->addImage('bg_vector_image', [
      'label'         => 'Decorative Vector (Right/Top)',
      'return_format' => 'array',
      'preview_size'  => 'medium',
    ])
    ->addNumber('bg_vector_opacity', [
      'label' => 'Vector Opacity (0–1)', 'min' => 0, 'max' => 1, 'step' => 0.05, 'default_value' => 0.03,
    ])
  ->addAccordion('newsletter_background_acc_end', ['endpoint' => 1])

  // Left content
  ->addAccordion('newsletter_left_acc', ['label' => 'Left Content'])
    ->addText('newsletter_heading', [
      'label'         => 'Heading',
      'default_value' => "Latest News, Events, and Expert advice from SPMHS",
    ])
    ->addWysiwyg('newsletter_subtext', [
      'label'         => 'Subtext (supports link)',
      'instructions'  => 'E.g., “For healthcare newsletter <a href=\'#\'>click here</a>”',
      'tabs'          => 'visual',
      'media_upload'  => 0,
      'delay'         => 1,
    ])
  ->addAccordion('newsletter_left_acc_end', ['endpoint' => 1])

  // Form settings
  ->addAccordion('newsletter_form_acc', ['label' => 'Form'])
    ->addUrl('newsletter_action', [
      'label'         => 'Form Action URL',
      'default_value' => '',
      'instructions'  => 'Leave empty to use Mailchimp/Brevo AJAX subscribe. Set only if you need a custom POST endpoint.',
    ])
    ->addText('name_label', [
      'label'         => 'Name Label',
      'default_value' => 'Full name',
    ])
    ->addText('name_placeholder', [
      'label'         => 'Name Placeholder',
      'default_value' => 'Enter your full name',
    ])
    ->addText('email_label', [
      'label'         => 'Email Label',
      'default_value' => 'Email',
    ])
    ->addText('email_placeholder', [
      'label'         => 'Email Placeholder',
      'default_value' => 'Enter email address',
    ])
    ->addText('submit_text', [
      'label'         => 'Submit Button Text',
      'default_value' => 'Subscribe',
    ])
    ->addTrueFalse('require_terms', [
      'label'         => 'Require Terms Checkbox',
      'ui'            => 1,
      'default_value' => 1,
    ])
    ->addText('terms_text_prefix', [
      'label'         => 'Terms Text (prefix)',
      'default_value' => 'By signing to our newsletter, you agree to our',
    ])
    ->addLink('terms_link', [
      'label'         => 'Terms & Conditions Link',
      'return_format' => 'array',
      'default_value' => ['url' => '#', 'title' => 'Terms & Conditions', 'target' => '_self'],
    ])
    ->addLink('privacy_link', [
      'label'         => 'Privacy Policy Link',
      'return_format' => 'array',
      'default_value' => ['url' => '#', 'title' => 'Privacy Policy', 'target' => '_self'],
    ])
  ->addAccordion('newsletter_form_acc_end', ['endpoint' => 1])

  ->addAccordion('newsletter_mailchimp_acc', ['label' => 'Mailchimp'])
    ->addText('mailchimp_api_key', [
      'label'         => 'Mailchimp API Key',
      'instructions'  => 'Prefer define(\'MATRIX_MAILCHIMP_KEY\', \'xxxx-us21\'); in wp-config.php. The suffix after the last hyphen is the datacenter.',
    ])
    ->addText('mailchimp_list_id', [
      'label'         => 'Default Mailchimp audience ID',
      'instructions'  => 'Used by the site-wide footer form. Page blocks can pick a different audience.',
    ])
    ->addTrueFalse('mailchimp_double_opt_in', [
      'label'         => 'Double opt-in (confirmation email)',
      'ui'            => 1,
      'default_value' => 1,
    ])
    ->addText('mailchimp_success_message', [
      'label'         => 'Success Message',
      'default_value' => 'Thanks — please check your email to confirm your subscription.',
    ])
    ->addText('mailchimp_error_message', [
      'label'         => 'Error Message',
      'default_value' => 'Sorry, something went wrong. Please try again.',
    ])
  ->addAccordion('newsletter_mailchimp_acc_end', ['endpoint' => 1])

  ->addAccordion('newsletter_brevo_acc', ['label' => 'Brevo (contact forms / fallback)'])
    ->addText('brevo_api_key', [
      'label'         => 'Brevo API Key',
      'instructions'  => 'Used by contact forms. Prefer define(\'MATRIX_BREVO_KEY\', \'...\'); in wp-config.php.',
    ])
    ->addText('brevo_list_ids', [
      'label'         => 'Default Brevo List IDs (comma-separated)',
      'instructions'  => 'Only used if Newsletter provider is set to Brevo.',
    ])
    ->addText('brevo_default_confirm_message', [
      'label'         => 'Default Success Message',
      'default_value' => "Thanks — you’re subscribed!",
    ])
    ->addText('brevo_error_message', [
      'label'         => 'Default Error Message',
      'default_value' => 'Sorry, something went wrong. Please try again.',
    ])
  ->addAccordion('newsletter_brevo_acc_end', ['endpoint' => 1]);

return $newsletter;
