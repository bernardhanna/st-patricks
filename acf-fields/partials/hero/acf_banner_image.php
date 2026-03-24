<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$banner_image = new FieldsBuilder('banner_image', [
    'label' => 'Banner Image',
]);

$banner_image
    ->addTab('content_tab', ['label' => 'Content'])
        ->addImage('banner_image_field', [
            'label'         => 'Banner Image',
            'instructions'  => 'Upload the banner image (1920px x 240px recommended).',
            'return_format' => 'id',
            'preview_size'  => 'large',
            'required'      => 0, // make optional since we’ll use default
            'default_value' => 1776, // ← replace with your real attachment ID
        ]);

return $banner_image;
