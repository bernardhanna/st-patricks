<?php
use StoutLogic\AcfBuilder\FieldsBuilder;

$navigationFields = new FieldsBuilder('navigation_settings');

$navigationFields
  ->addGroup('navigation_settings_start', [
      'label' => 'Navigation Settings',
  ])
    ->addTrueFalse('enable_search', [
      'label'         => 'Enable Search Icon',
      'ui'            => 1,
      'default_value' => 1,
    ])
    ->addLink('looking_help_button', [
      'label'         => '“Looking for help?” Button',
      'return_format' => 'array',
      'instructions'  => 'Shown on ≥ sm screens.',
      'default_value' => [
        'url'    => '/help',
        'title'  => 'Looking for help?',
        'target' => '_self',
      ],
    ])
    ->addLink('referral_button', [
      'label'         => '“Make a referral” Button',
      'return_format' => 'array',
      'default_value' => [
        'url'    => '/make-a-referral',
        'title'  => 'Make a referral',
        'target' => '_self',
      ],
    ])
    ->addLink('dropdown_cta_button', [
      'label'         => 'Desktop Dropdown CTA Button',
      'return_format' => 'array',
      'instructions'  => 'Optional call-to-action shown in desktop mega dropdown left panel.',
      'default_value' => [
        'url'    => '/portal',
        'title'  => 'Access your portal',
        'target' => '_self',
      ],
    ])

    /*
    | Dropdown image decorator mapping (unchanged functionality)
    */
    ->addRepeater('dropdown_images', [
      'label'        => 'Dropdown Images',
      'layout'       => 'row',
      'button_label' => 'Add Dropdown Image',
    ])
      ->addSelect('menu_item', [
        'label'   => 'Attach to menu item',
        'choices' => [], // filled dynamically via hook
        'ui'      => 1,
      ])
      ->addImage('image', [
        'label'         => 'Image',
        'return_format' => 'array',
        'preview_size'  => 'medium',
      ])
    ->endRepeater()

  ->addAccordion('navigation_settings_end')->endpoint();

return $navigationFields;
