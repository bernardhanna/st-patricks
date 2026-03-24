<?php
use StoutLogic\AcfBuilder\FieldsBuilder;

$about = new FieldsBuilder('about', [
  'label' => 'About Us',
]);

$about
  ->addTab('content_tab', ['label' => 'Content'])

    // Heading & Intro
    ->addImage('faded_logo', [
      'label'         => 'Faded Logo (left of heading)',
      'return_format' => 'array',
      'preview_size'  => 'medium',
    ])
    ->addSelect('heading_tag', [
      'label'   => 'Heading Tag',
      'choices' => ['h1'=>'h1','h2'=>'h2','h3'=>'h3','h4'=>'h4','h5'=>'h5','h6'=>'h6','span'=>'span','p'=>'p'],
      'default_value' => 'h2',
    ])
    ->addText('heading_text', [
      'label'         => 'Heading',
      'default_value' => 'About us',
    ])
    ->addWysiwyg('description', [
      'label'         => 'Description',
      'tabs'          => 'visual',
      'media_upload'  => 0,
      'delay'         => 0,
      'default_value' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit…',
    ])

    // Right: Images
    ->addGroup('image_left', ['label' => 'Left (rotated) Card Image'])
      ->addImage('image', [
        'label'         => 'Main Image',
        'return_format' => 'array',
        'preview_size'  => 'medium',
      ])
      ->addImage('overlay_logo', [
        'label'         => 'Overlay Logo (bottom-right)',
        'return_format' => 'array',
        'preview_size'  => 'medium',
      ])
      ->addNumber('rotate_deg', [
        'label' => 'Rotation (deg)',
        'default_value' => -6, 'step'=>1, 'min' => -20, 'max' => 20,
      ])
    ->endGroup()

    ->addGroup('image_right', ['label' => 'Right (straight) Card Image'])
      ->addImage('image', [
        'label'         => 'Main Image',
        'return_format' => 'array',
        'preview_size'  => 'medium',
      ])
      ->addImage('overlay_logo', [
        'label'         => 'Overlay Logo (bottom-right)',
        'return_format' => 'array',
        'preview_size'  => 'medium',
      ])
    ->endGroup()

    // Key Points (counters)
    ->addRepeater('key_points', [
      'label'        => 'Key Points (Counters)',
      'button_label' => 'Add Key Point',
      'layout'       => 'row',
      'min'          => 1,
      'max'          => 6,
    ])
      ->addImage('watermark', [
        'label'         => 'Faded Logo Watermark',
        'return_format' => 'array',
        'preview_size'  => 'thumbnail',
      ])
      ->addNumber('value', [
        'label' => 'Number (Target)',
        'default_value' => 95, 'min'=>0, 'step'=>1,
      ])
      ->addText('suffix', [
        'label' => 'Suffix (e.g. %, k)',
        'default_value' => '%',
      ])
      ->addText('title', [
        'label' => 'Title',
        'default_value' => 'Key point text',
      ])
      ->addTextarea('text', [
        'label' => 'Small Text',
        'new_lines' => '', 'maxlength' => 160,
        'default_value' => 'Lorem ipsum dolor sit amet sed do eiusmod tempor incididunt',
      ])
    ->endRepeater()

    // CTAs
    ->addLink('primary_cta', [
      'label' => 'Primary Button (Careers)',
      'return_format' => 'array',
      'default_value' => ['url'=>'#','title'=>'Careers','target'=>'_self'],
    ])
    ->addLink('secondary_cta', [
      'label' => 'Secondary Button (About)',
      'return_format' => 'array',
      'default_value' => ['url'=>'#','title'=>'About us','target'=>'_self'],
    ])

  ->addTab('design_tab', ['label' => 'Design'])
    ->addColorPicker('bg_color', ['label'=>'Background Color','default_value'=>'#ffffff'])
    ->addColorPicker('heading_color', ['label'=>'Heading Color','default_value'=>'#0B0B08'])
    ->addColorPicker('desc_color', ['label'=>'Description Color','default_value'=>'#4A4B37'])
    ->addColorPicker('divider_color', ['label'=>'Small Divider Color','default_value'=>'#5F604B'])
    ->addColorPicker('value_color', ['label'=>'Counter Value Color','default_value'=>'#5F604B'])
    ->addColorPicker('title_color', ['label'=>'Key Title Color','default_value'=>'#0B0B08'])
    ->addColorPicker('text_color', ['label'=>'Key Text Color','default_value'=>'#4A4B37'])
    ->addSelect('buttons_style', [
      'label'=>'Buttons Style Preset',
      'choices'=>[
        'solid-dark'    => 'Solid dark + ghost secondary',
        'solid-primary' => 'Solid primary + ghost secondary',
      ],
      'default_value'=>'solid-dark',
    ])

  ->addTab('layout_tab', ['label' => 'Layout'])
    ->addRepeater('padding_settings', [
      'label' => 'Padding Settings',
      'button_label' => 'Add Screen Size Padding',
    ])
      ->addSelect('screen_size', [
        'label'   => 'Screen Size',
        'choices' => ['xxs'=>'xxs','xs'=>'xs','mob'=>'mob','sm'=>'sm','md'=>'md','lg'=>'lg','xl'=>'xl','xxl'=>'xxl','ultrawide'=>'ultrawide'],
      ])
      ->addNumber('padding_top', ['label'=>'Padding Top','min'=>0,'max'=>20,'step'=>0.1,'append'=>'rem'])
      ->addNumber('padding_bottom', ['label'=>'Padding Bottom','min'=>0,'max'=>20,'step'=>0.1,'append'=>'rem'])
    ->endRepeater();

return $about;
