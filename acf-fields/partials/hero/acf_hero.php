<?php
use StoutLogic\AcfBuilder\FieldsBuilder;

$hero = new FieldsBuilder('hero', [
  'label' => 'Hero',
]);

$hero
  ->addTab('content_tab', ['label' => 'Content'])
    ->addSelect('heading_tag', [
      'label'   => 'Heading Tag',
      'choices' => [
        'h1'=>'h1','h2'=>'h2','h3'=>'h3','h4'=>'h4','h5'=>'h5','h6'=>'h6','span'=>'span','p'=>'p',
      ],
      'default_value' => 'h1',
    ])
    ->addText('heading_text', [
      'label'         => 'Heading',
      'placeholder'   => 'Mental health services header',
      'default_value' => 'Mental health services header',
    ])
    ->addWysiwyg('description', [
      'label'        => 'Description',
      'tabs'         => 'visual',
      'media_upload' => 0,
      'delay'        => 1,
      'default_value'=> 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
    ])
    ->addLink('primary_button', [
      'label'         => 'Primary Button (Left)',
      'return_format' => 'array',
      'default_value' => ['url'=>'#','title'=>'Looking for help?','target'=>'_self'],
    ])
    ->addLink('secondary_button', [
      'label'         => 'Secondary Button (Right)',
      'return_format' => 'array',
      'default_value' => ['url'=>'#','title'=>'Make a referral','target'=>'_self'],
    ])
    ->addImage('hero_image', [
      'label'         => 'Right-side Image',
      'return_format' => 'array',
      'preview_size'  => 'large',
    ])

  ->addTab('design_tab', ['label' => 'Design'])
    ->addColorPicker('background_color', [
      'label'         => 'Background Color',
      'default_value' => '#C8E3F7', // primary-blue-light-ish fallback
    ])
    ->addColorPicker('heading_color', [
      'label'         => 'Heading Color',
      'default_value' => '#2C2C21', // dark-olive
    ])
    ->addColorPicker('text_color', [
      'label'         => 'Body Text Color',
      'default_value' => '#2C2C21',
    ])
    ->addRepeater('decor_images', [
      'label'        => 'Background Decorative Images',
      'instructions' => 'Add any number of background images with position/opacity/visibility.',
      'button_label' => 'Add Decorative Image',
      'layout'       => 'row',
    ])
      ->addImage('image', [
        'label'         => 'Image',
        'return_format' => 'array',
        'preview_size'  => 'medium',
      ])
      ->addText('position_classes', [
        'label'         => 'Position/Size Classes',
        'instructions'  => 'e.g. absolute right-0 top-1/2 translate-x-1/4 -translate-y-1/2 w-96 h-auto',
        'default_value' => 'absolute right-0 top-1/2 translate-x-1/4 -translate-y-1/2 w-96 h-auto',
      ])
      ->addNumber('opacity', [
        'label' => 'Opacity (0–1)',
        'min'   => 0, 'max' => 1, 'step' => 0.05, 'default_value' => 0.2,
      ])
      ->addTrueFalse('show_md', [
        'label'         => 'Show on md+',
        'ui'            => 1,
        'default_value' => 1,
      ])
      ->addTrueFalse('show_lg', [
        'label'         => 'Show on lg+ (hide below lg)',
        'ui'            => 1,
        'default_value' => 0,
      ])
    ->endRepeater()
    ->addTrueFalse('show_pager', [
      'label'         => 'Show Pager/Dots',
      'ui'            => 1,
      'default_value' => 1,
    ])
    ->addNumber('pager_count', [
      'label'         => 'Dots Count',
      'default_value' => 5, 'min' => 1, 'max' => 10,
      'conditional_logic' => [['field' => 'show_pager', 'operator' => '==', 'value' => 1]],
    ])
    ->addNumber('pager_active_index', [
      'label'         => 'Active Dot Index (1-based)',
      'default_value' => 1, 'min' => 1, 'max' => 10,
      'conditional_logic' => [['field' => 'show_pager', 'operator' => '==', 'value' => 1]],
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
        'label'=>'Padding Top','min'=>0,'max'=>20,'step'=>0.1,'append'=>'rem',
      ])
      ->addNumber('padding_bottom', [
        'label'=>'Padding Bottom','min'=>0,'max'=>20,'step'=>0.1,'append'=>'rem',
      ])
    ->endRepeater();

return $hero;
