<?php
use StoutLogic\AcfBuilder\FieldsBuilder;

$testimonials = new FieldsBuilder('testimonials', [
  'label' => 'Testimonials',
]);

$testimonials
  ->addTab('content_tab', ['label' => 'Content'])
    ->addImage('left_logo', [
      'label'         => 'Left Logo (faded)',
      'return_format' => 'array',
      'preview_size'  => 'medium',
    ])
    ->addSelect('heading_tag', [
      'label'   => 'Heading Tag',
      'choices' => [
        'h1'=>'h1','h2'=>'h2','h3'=>'h3','h4'=>'h4','h5'=>'h5','h6'=>'h6','span'=>'span','p'=>'p',
      ],
      'default_value' => 'h2',
    ])
    ->addText('heading_text', [
      'label'         => 'Heading',
      'default_value' => "Voices of hope\nand healing",
    ])
    ->addWysiwyg('description', [
      'label'         => 'Description',
      'tabs'          => 'all',
      'media_upload'  => 0,
      'delay'         => 0,
      'default_value' => 'Every journey is unique. These stories reflect the courage, progress, and renewed well-being of individuals who trusted us to walk alongside them.',
    ])

    ->addRepeater('items', [
      'label'        => 'Testimonials',
      'instructions' => 'Add one or more testimonial cards.',
      'button_label' => 'Add Testimonial',
      'layout'       => 'row',
      'min'          => 1,
    ])
      ->addImage('photo', [
        'label'         => 'Person Image',
        'return_format' => 'array',
        'preview_size'  => 'medium',
      ])
      ->addWysiwyg('quote', [
        'label'         => 'Quote',
        'tabs'          => 'visual',
        'media_upload'  => 0,
        'delay'         => 0,
        'default_value' => '"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do lorem upsum dolor sit amet sinlis morris!"',
      ])
      ->addText('author_name', [
        'label'         => 'Author Name',
        'default_value' => 'Stephanie Green',
      ])
      ->addText('author_title', [
        'label'         => 'Author Title',
        'default_value' => 'Accountant',
      ])
    ->endRepeater()

  ->addTab('design_tab', ['label' => 'Design'])
    ->addColorPicker('background_color', [
      'label'         => 'Section Background',
      'default_value' => '#ffffff',
    ])
    ->addColorPicker('heading_color', [
      'label'         => 'Heading Color',
      'default_value' => '#0B0B08', // st-patricks-dark-bg
    ])
    ->addColorPicker('desc_color', [
      'label'         => 'Description Color',
      'default_value' => '#5F604B', // dark-olive
    ])
    ->addColorPicker('quote_color', [
      'label'         => 'Quote Text Color',
      'default_value' => '#4A4B37',
    ])
    ->addColorPicker('accent_color', [
      'label'         => 'Accent (divider/quote square)',
      'default_value' => '#7ED0E0', // st-patricks-accent-ish
    ])
    ->addColorPicker('gradient_from', [
      'label'         => 'Gradient From',
      'default_value' => '#7ED0E0',
    ])
    ->addColorPicker('gradient_to', [
      'label'         => 'Gradient To',
      'default_value' => '#3CA7B6',
    ])
    ->addSelect('card_radius', [
      'label'   => 'Card Border Radius',
      'choices' => [
        'rounded-none' => 'None',
        'rounded'      => 'rounded',
        'rounded-md'   => 'rounded-md',
        'rounded-lg'   => 'rounded-lg',
        'rounded-xl'   => 'rounded-xl',
      ],
      'default_value' => 'rounded-none', // per requirement
    ])
    ->addTrueFalse('card_shadow', [
      'label'         => 'Enable Card Shadow',
      'ui'            => 1,
      'default_value' => 1,
    ])
    ->addNumber('logo_opacity', [
      'label' => 'Left Logo Opacity (0–1)',
      'min' => 0, 'max' => 1, 'step' => 0.05, 'default_value' => 0.2,
    ])
    ->addTrueFalse('show_stack_backgrounds', [
      'label'         => 'Show Stacked Gradient Background Cards',
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

return $testimonials;
