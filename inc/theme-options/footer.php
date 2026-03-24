<?php
use StoutLogic\AcfBuilder\FieldsBuilder;

$footer = new FieldsBuilder('footer', [
  'title'     => 'Footer Settings',
  'menu_slug' => 'theme-footer-settings',
  'position'  => 'normal',
  'post_id'   => 'option',
]);

$footer
  ->addTab('footer_main_tab', ['label' => 'Footer'])

  // DESIGN
  ->addAccordion('footer_design_acc', ['label' => 'Design', 'open' => 1])
    ->addColorPicker('background_color', [
        'label'         => 'Background Color',
        'default_value' => '#F1F8F9', 
    ])
    ->addRepeater('padding_settings', [
      'label'        => 'Padding Settings',
      'instructions' => 'Customize padding for different screen sizes.',
      'button_label' => 'Add Screen Size Padding',
    ])
      ->addSelect('screen_size', [
        'label'   => 'Screen Size',
        'choices' => [
          'xxs' => 'xxs','xs' => 'xs','mob' => 'mob','sm' => 'sm','md' => 'md',
          'lg' => 'lg','xl' => 'xl','xxl' => 'xxl','ultrawide' => 'ultrawide',
        ],
      ])
      ->addNumber('padding_top', [
        'label'        => 'Padding Top', 'min' => 0, 'max' => 20, 'step' => 0.1, 'append' => 'rem',
      ])
      ->addNumber('padding_bottom', [
        'label'        => 'Padding Bottom', 'min' => 0, 'max' => 20, 'step' => 0.1, 'append' => 'rem',
      ])
    ->endRepeater()
  ->addAccordion('footer_design_acc_end', ['endpoint' => 1])

  // BRAND STRIP (faded symbols row)
  ->addAccordion('footer_brandstrip_acc', ['label' => 'Brand Strip'])
    ->addRepeater('brand_strip_logos', [
      'label'        => 'Logos Row',
      'instructions' => 'Add small logos to show faintly in the background strip.',
      'button_label' => 'Add Logo',
      'layout'       => 'row',
    ])
      ->addImage('image', [
        'label'         => 'Logo',
        'return_format' => 'array',
        'preview_size'  => 'thumbnail',
      ])
      ->addNumber('width', [
        'label' => 'Rendered Width (px)', 'min' => 20, 'max' => 400, 'step' => 1, 'default_value' => 112, // ~ w-28
      ])
      ->addNumber('height', [
        'label' => 'Rendered Height (px)', 'min' => 20, 'max' => 200, 'step' => 1, 'default_value' => 96, // ~ h-24
      ])
    ->endRepeater()
    ->addNumber('brand_strip_opacity', [
      'label' => 'Strip Opacity (0–1)', 'min' => 0, 'max' => 1, 'step' => 0.05, 'default_value' => 0.25,
    ])
  ->addAccordion('footer_brandstrip_acc_end', ['endpoint' => 1])

  // LOGO (main footer logo; falls back to custom_logo)
  ->addAccordion('footer_logo_acc', ['label' => 'Logo'])
    ->addImage('footer_logo', [
      'label'         => 'Footer Logo',
      'return_format' => 'array',
      'preview_size'  => 'medium',
    ])
    ->addImage('mobile_footer_logo', [
      'label'         => 'Mobile Footer Logo',
      'instructions'  => 'Optional: shown on mobile footer only. Falls back to Footer Logo if empty.',
      'return_format' => 'array',
      'preview_size'  => 'medium',
    ])
  ->addAccordion('footer_logo_acc_end', ['endpoint' => 1])

  // COLUMNS — About / Quick Links / Media / Careers / Contact
  ->addAccordion('footer_col_about_acc', ['label' => 'About'])
    ->addText('about_heading', ['label' => 'Heading', 'default_value' => 'About us'])
    ->addRepeater('about_links', [
      'label'        => 'About Links',
      'button_label' => 'Add Link',
      'layout'       => 'table',
    ])
      ->addLink('link', ['label' => 'Link', 'return_format' => 'array'])
    ->endRepeater()
  ->addAccordion('footer_col_about_acc_end', ['endpoint' => 1])

  ->addAccordion('footer_col_quick_acc', ['label' => 'Quick Links'])
    ->addText('quick_links_heading', ['label' => 'Heading', 'default_value' => 'Quick Links'])
    ->addRepeater('quick_links', [
      'label'        => 'Quick Links',
      'button_label' => 'Add Link',
      'layout'       => 'table',
    ])
      ->addLink('link', ['label' => 'Link', 'return_format' => 'array'])
    ->endRepeater()
  ->addAccordion('footer_col_quick_acc_end', ['endpoint' => 1])

  ->addAccordion('footer_col_media_acc', ['label' => 'Media'])
    ->addText('media_heading', ['label' => 'Heading', 'default_value' => 'Media'])
    ->addRepeater('media_links', [
      'label'        => 'Media Links',
      'button_label' => 'Add Link',
      'layout'       => 'table',
    ])
      ->addLink('link', ['label' => 'Link', 'return_format' => 'array'])
    ->endRepeater()
  ->addAccordion('footer_col_media_acc_end', ['endpoint' => 1])

  ->addAccordion('footer_col_careers_acc', ['label' => 'Careers'])
    ->addText('careers_heading', ['label' => 'Heading', 'default_value' => 'Careers'])
    ->addRepeater('careers_links', [
      'label'        => 'Careers Links',
      'button_label' => 'Add Link',
      'layout'       => 'table',
    ])
      ->addLink('link', ['label' => 'Link', 'return_format' => 'array'])
    ->endRepeater()
  ->addAccordion('footer_col_careers_acc_end', ['endpoint' => 1])

  ->addAccordion('footer_col_contact_acc', ['label' => 'Contact'])
    ->addText('contact_heading', ['label' => 'Heading', 'default_value' => 'Contact us'])
    ->addText('locations_heading', ['label' => 'Locations Subheading', 'default_value' => 'Our Locations'])
    ->addLink('contact_phone_link', [
      'label'         => 'Phone (Link Array)',
      'return_format' => 'array',
      'default_value' => ['url' => 'tel:+3531012123123', 'title' => '01 012 123 123', 'target' => '_self'],
    ])
    ->addLink('contact_email_link', [
      'label'         => 'Email (Link Array)',
      'return_format' => 'array',
      'default_value' => ['url' => 'mailto:hello@StPatrick.ie', 'title' => 'hello@StPatrick.ie', 'target' => '_self'],
    ])
  ->addAccordion('footer_col_contact_acc_end', ['endpoint' => 1])

  // SOCIAL
  ->addAccordion('footer_social_acc', ['label' => 'Social'])
    ->addText('social_heading', ['label' => 'Heading', 'default_value' => 'Social media'])
    ->addRepeater('social_links', [
      'label'        => 'Social Media Links',
      'button_label' => 'Add Social Link',
      'layout'       => 'row',
    ])
      ->addSelect('platform', [
        'label' => 'Platform',
        'choices' => [
          'twitter' => 'Twitter/X','facebook' => 'Facebook','tiktok' => 'TikTok','instagram' => 'Instagram',
          'linkedin' => 'LinkedIn','youtube' => 'YouTube','pinterest' => 'Pinterest',
        ],
        'default_value' => 'twitter',
      ])
      ->addUrl('url', ['label' => 'Profile URL'])
      ->addSelect('target', ['label' => 'Link Target', 'choices' => ['_blank' => 'New tab', '_self' => 'Same tab'], 'default_value' => '_blank'])
    ->endRepeater()
  ->addAccordion('footer_social_acc_end', ['endpoint' => 1])

  // LEGAL / COPYRIGHT
  ->addAccordion('footer_legal_acc', ['label' => 'Legal & Copyright'])
    ->addText('copyright_text', [
      'label' => 'Copyright Text',
      'default_value' => 'St Patrick Hospital © ' . date('Y'),
    ])
    ->addRepeater('legal_links', [
      'label'        => 'Legal Links',
      'button_label' => 'Add Legal Link',
      'layout'       => 'table',
    ])
      ->addLink('link', ['label' => 'Link', 'return_format' => 'array'])
    ->endRepeater()
    ->addText('developer_credit', ['label' => 'Developer Credit Label', 'default_value' => 'Designed & Developed by'])
    ->addLink('developer_credit_link', [
      'label'         => 'Developer Link',
      'return_format' => 'array',
      'default_value' => ['url' => 'https://www.matrixinternet.ie/', 'title' => 'Matrix Internet', 'target' => '_blank'],
    ])
  ->addAccordion('footer_legal_acc_end', ['endpoint' => 1]);

return $footer;
