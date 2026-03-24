<?php
use StoutLogic\AcfBuilder\FieldsBuilder;

$services = new FieldsBuilder('services', [
  'label' => 'Services',
]);

$services
  ->addTab('content_tab', ['label' => 'Content'])
    ->addSelect('heading_tag', [
      'label'   => 'Section Heading Tag',
      'choices' => ['h1'=>'h1','h2'=>'h2','h3'=>'h3','h4'=>'h4','h5'=>'h5','h6'=>'h6','span'=>'span','p'=>'p'],
      'default_value' => 'h2',
    ])
    ->addText('heading_text', [
      'label'         => 'Section Heading',
      'placeholder'   => 'Our Services',
      'default_value' => 'Our Services',
    ])
    ->addRepeater('cards', [
      'label'        => 'Service Cards',
      'instructions' => 'Add each service card.',
      'button_label' => 'Add Service Card',
      'layout'       => 'row',
      'min'          => 1,
    ])
      ->addLink('link', [
        'label'         => 'Card Link',
        'return_format' => 'array',
      ])
      ->addText('title', [
        'label'         => 'Title',
        'default_value' => 'Service Title',
      ])
      ->addImage('image', [
        'label'         => 'Main Image',
        'return_format' => 'array',
        'preview_size'  => 'medium',
      ])
      ->addImage('watermark', [
        'label'         => 'Logo Watermark (optional)',
        'return_format' => 'array',
        'preview_size'  => 'medium',
      ])
    ->endRepeater()
    ->addLink('view_more_button', [
      'label'         => 'View More Button',
      'instructions'  => 'Optional CTA below the grid.',
      'return_format' => 'array',
    ])

  ->addTab('design_tab', ['label' => 'Design'])
    ->addColorPicker('background_color', [
      'label'         => 'Section Background Color',
      'default_value' => '#ffffff',
    ])
    ->addColorPicker('heading_color', [
      'label'         => 'Heading Color',
      'default_value' => '#2C2C21',
    ])
    ->addColorPicker('title_color', [
      'label'         => 'Card Title Color',
      'default_value' => '#4A4B37',
    ])
    ->addNumber('watermark_opacity', [
      'label' => 'Watermark Opacity (0–1)',
      'min' => 0, 'max' => 1, 'step' => 0.05, 'default_value' => 0.12,
    ])
    ->addSelect('card_radius', [
      'label'   => 'Card Border Radius',
      'choices' => [
        'rounded-none' => 'None',
        'rounded'      => 'Default',
        'rounded-md'   => 'md',
        'rounded-lg'   => 'lg',
        'rounded-xl'   => 'xl',
      ],
      'default_value' => 'rounded-none', // per requirement: rounded none as default
    ])
    ->addTrueFalse('card_shadow', [
      'label'         => 'Enable Card Shadow',
      'ui'            => 1,
      'default_value' => 1,
    ])

  ->addTab('layout_tab', ['label' => 'Layout'])
    ->addSelect('md_columns', [
      'label'   => 'Columns @ md',
      'choices' => ['1'=>'1','2'=>'2','3'=>'3','4'=>'4'],
      'default_value' => '2',
    ])
    ->addSelect('lg_columns', [
      'label'   => 'Columns @ lg',
      'choices' => ['1'=>'1','2'=>'2','3'=>'3','4'=>'4'],
      'default_value' => '3',
    ])
    ->addSelect('xl_columns', [
      'label'   => 'Columns @ xl',
      'choices' => ['1'=>'1','2'=>'2','3'=>'3','4'=>'4'],
      'default_value' => '3',
    ])
    ->addRepeater('padding_settings', [
      'label'        => 'Padding Settings',
      'instructions' => 'Customize padding for different screen sizes.',
      'button_label' => 'Add Screen Size Padding',
    ])
      ->addSelect('screen_size', [
        'label'   => 'Screen Size',
        'choices' => [
          'xxs'=>'xxs','xs'=>'xs','mob'=>'mob','sm'=>'sm','md'=>'md','lg'=>'lg','xl'=>'xl','xxl'=>'xxl','ultrawide'=>'ultrawide',
        ],
      ])
      ->addNumber('padding_top', [
        'label' => 'Padding Top', 'min'=>0,'max'=>20,'step'=>0.1,'append'=>'rem',
      ])
      ->addNumber('padding_bottom', [
        'label' => 'Padding Bottom', 'min'=>0,'max'=>20,'step'=>0.1,'append'=>'rem',
      ])
    ->endRepeater();

return $services;
