<?php
use StoutLogic\AcfBuilder\FieldsBuilder;

$hero_slider = new FieldsBuilder('hero_slider', [
    'label' => 'Hero Slider',
]);

$hero_slider
    ->addTab('content_tab', [
        'label' => 'Content',
    ])
        ->addRepeater('slides', [
            'label' => 'Hero Slides',
            'instructions' => 'Add hero slides. If more than one slide is added, it will become a slider.',
            'button_label' => 'Add Slide',
            'layout' => 'block',
            'min' => 1,
            'max' => 5,
        ])
            ->addSelect('heading_tag', [
                'label' => 'Heading Tag',
                'choices' => [
                    'h1' => 'h1',
                    'h2' => 'h2',
                    'h3' => 'h3',
                    'h4' => 'h4',
                    'h5' => 'h5',
                    'h6' => 'h6',
                    'span' => 'span',
                    'p'   => 'p',
                ],
                'default_value' => 'h1',
            ])
            ->addText('heading_text', [
                'label' => 'Heading Text',
                'placeholder' => 'Mental health services header',
                'default_value' => 'Mental health services header',
            ])
            ->addWysiwyg('description', [
                'label' => 'Description',
                'instructions' => 'Add the description text for this slide.',
                'tabs' => 'visual',
                'media_upload' => 0,
                'delay' => 1,
                'default_value' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            ])
            ->addLink('primary_button', [
                'label' => 'Primary Button',
                'instructions' => 'The main call-to-action button.',
                'return_format' => 'array',
                'default_value' => [
                    'url' => '#',
                    'title' => 'Looking for help?',
                    'target' => '_self',
                ],
            ])
            ->addLink('secondary_button', [
                'label' => 'Secondary Button',
                'instructions' => 'The secondary call-to-action button.',
                'return_format' => 'array',
                'default_value' => [
                    'url' => '#',
                    'title' => 'Make a referral',
                    'target' => '_self',
                ],
            ])
            ->addImage('hero_image', [
                'label' => 'Hero Image',
                'instructions' => 'Upload the desktop hero image for this slide.',
                'return_format' => 'array',
                'preview_size' => 'large',
            ])
            ->addImage('mobile_optimised_image', [
                'label' => 'Mobile Optimised Image',
                'instructions' => 'Optional image used below 1024px. Falls back to Hero Image if empty.',
                'return_format' => 'array',
                'preview_size' => 'medium',
            ])
        ->endRepeater()

    ->addTab('design_tab', [
        'label' => 'Design',
    ])
        ->addColorPicker('background_color', [
            'label' => 'Background Color',
            'instructions' => 'Set the background color for the hero section.',
            'default_value' => '#C6ECF4',
        ]);

return $hero_slider;
