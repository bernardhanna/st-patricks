<?php
use StoutLogic\AcfBuilder\FieldsBuilder;

$cta = new FieldsBuilder('cta_block', [
  'title' => 'CTA',
]);

$cta
  ->addTab('content', ['label' => 'Content'])
    ->addText('title', ['label' => 'Heading', 'default_value' => 'Child Safeguarding'])
    ->addTextarea('description', ['label' => 'Description', 'rows' => 4])
    ->addLink('button', [
      'label' => 'Button',
      'return_format' => 'array',
      'default_value' => ['url' => '#', 'title' => 'Child Safeguarding', 'target' => '_self'],
    ])
    ->addTrueFalse('show_icon', ['label' => 'Show Button Icon', 'default_value' => 1])

  ->addTab('media', ['label' => 'Media'])
    ->addImage('polaroid_image', ['label' => 'Polaroid Image', 'return_format' => 'array'])
    ->addImage('faded_logo', ['label' => 'Faded Logo (bg, optional)', 'return_format' => 'array'])
    ->addImage('watermark_logo', ['label' => 'Watermark (bottom-right, optional)', 'return_format' => 'array'])

  ->addTab('style', ['label' => 'Style'])
    ->addColorPicker('divider_color', ['label' => 'Divider Color', 'default_value' => '#D1D5DB'])
    ->addTrueFalse('min_full_screen', ['label' => 'Min Height Full Screen', 'default_value' => 0])
    ->addText('section_classes', ['label' => 'Extra Section Classes']);

return $cta;
