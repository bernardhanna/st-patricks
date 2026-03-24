<?php
use StoutLogic\AcfBuilder\FieldsBuilder;

$topbar = new FieldsBuilder('topbar', [
  'title'     => 'Top Bar',
  'menu_slug' => 'theme-topbar',
  'position'  => 'normal',
  'post_id'   => 'option',
]);

$topbar
  
    ->addRepeater('topbar_links', [
      'label'         => 'Left Menu Links',
      'instructions'  => 'Add links for the left side of the top bar.',
      'layout'        => 'row',
      'button_label'  => 'Add Link',
      'min'           => 0,
      'max'           => 6,
    ])
      ->addLink('link', [
        'label'         => 'Link',
        'return_format' => 'array',
        'default_value' => [
          'url'    => '#',
          'title'  => 'News and events',
          'target' => '_self',
        ],
      ])
    ->endRepeater()
    ->addLink('topbar_phone_link', [
      'label'         => 'Phone Link (Right)',
      'instructions'  => 'Use tel: protocol, e.g., tel:+35312493200',
      'return_format' => 'array',
      'default_value' => [
        'url'    => 'tel:+35312493200',
        'title'  => '01 249 3200',
        'target' => '_self',
      ],
    ])
    ->addColorPicker('topbar_bg_color', [
      'label'         => 'Background Color',
      'instructions'  => 'Background color for the top bar.',
      'default_value' => '#0b1b2b',
    ]);

return $topbar;
