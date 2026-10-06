<?php
use StoutLogic\AcfBuilder\FieldsBuilder;

$fields = new FieldsBuilder('scripts');

$fields
  ->addAccordion('scripts_settings_start', [
    'label' => 'Enable & Disable Scripts and Styles',
  ])
  ->addCheckbox('enabled_scripts', [
    'label'        => 'Enable Scripts and Styles',
    'instructions' => 'Select the scripts and styles you want to enable.',
    'choices'      => [
      'font_awesome'   => 'Font Awesome',
      'flowbite'       => 'Flowbite',
      'slick'          => 'Slick JS',
      'hamburger_css'  => 'Hamburgers CSS',
      'headroom'       => 'Headroom.js',
      'leaflet'        => 'Leaflet (OpenStreetMap)',
      'cloudflare_turnstile' => 'Cloudflare Turnstile',
    ],
    'default_value' => [
      'slick',
      'font_awesome',
      'hamburger_css',
      'headroom',
    ],
    'layout'       => 'vertical',
  ])
  ->addTrueFalse('enable_matrix_buggie', [
    'label'         => 'Matrix Buggie',
    'instructions'  => 'Load the Matrix Buggie tracker on the front end (async). Turn off to stop loading the script.',
    'ui'            => 1,
    'ui_on_text'    => 'Enabled',
    'ui_off_text'   => 'Disabled',
    'default_value' => 1,
  ])
  ->addAccordion('scripts_settings_end')->endpoint();

return $fields;

