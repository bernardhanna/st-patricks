<?php
use StoutLogic\AcfBuilder\FieldsBuilder;

$partners = new FieldsBuilder('partners', [
  'label' => 'Partners',
]);

$partners
  ->addTab('content_tab', ['label' => 'Content'])
    ->addSelect('heading_tag', [
      'label'   => 'Heading Tag',
      'choices' => [
        'h1' => 'h1', 'h2' => 'h2', 'h3' => 'h3', 'h4' => 'h4',
        'h5' => 'h5', 'h6' => 'h6', 'span' => 'span', 'p' => 'p',
      ],
      'default_value' => 'h2',
    ])
    ->addText('heading_text', [
      'label'         => 'Heading Text',
      'placeholder'   => 'Committed to quality care, human rights, and innovation',
      'default_value' => 'Committed to quality care, human rights, and innovation',
    ])
    ->addRepeater('partners', [
      'label'        => 'Partner Logos',
      'instructions' => 'Add partner logos. Optionally wrap each logo with a link. Logos will display in a grid on desktop and as a slider on mobile.',
      'button_label' => 'Add Partner',
      'layout'       => 'row',
    ])
      ->addImage('logo', [
        'label'         => 'Logo',
        'return_format' => 'array',
        'preview_size'  => 'medium',
        'instructions'  => 'Upload a logo image (SVG/PNG recommended). Alt text and title will be pulled from media settings.',
      ])
      ->addLink('link', [
        'label'         => 'Optional Link',
        'instructions'  => 'Add a link to make the logo clickable.',
        'return_format' => 'array',
      ])
    ->endRepeater()

  ->addTab('design_tab', ['label' => 'Design'])
    ->addColorPicker('background_color', [
      'label'         => 'Background Color',
      'default_value' => '#FFFFFF',
    ])
    ->addColorPicker('heading_color', [
      'label'         => 'Heading Text Color',
      'default_value' => '#1e293b',
    ])
    ->addTrueFalse('show_card_style', [
      'label'         => 'Card Style for Logos',
      'instructions'  => 'Enable to show logos with card background, border, and shadow.',
      'ui'            => 1,
      'default_value' => 1,
    ])

  ->addTab('layout_tab', ['label' => 'Layout'])
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
        'label' => 'Padding Top', 'min' => 0, 'max' => 20, 'step' => 0.1, 'append' => 'rem',
      ])
      ->addNumber('padding_bottom', [
        'label' => 'Padding Bottom', 'min' => 0, 'max' => 20, 'step' => 0.1, 'append' => 'rem',
      ])
    ->endRepeater();

return $partners;
