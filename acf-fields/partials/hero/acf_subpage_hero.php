<?php
use StoutLogic\AcfBuilder\FieldsBuilder;

$subpage_hero = new FieldsBuilder('subpage_hero', [
    'label' => 'Subpage Hero',
]);

$subpage_hero
    // Content Tab
    ->addTab('Content', ['placement' => 'top'])
        ->addSelect('heading_tag', [
            'label' => 'Heading Tag',
            'instructions' => 'Select the HTML tag for the main heading.',
            'choices' => [
                'h1' => 'H1','h2' => 'H2','h3' => 'H3',
                'h4' => 'H4','h5' => 'H5','h6' => 'H6',
                'span' => 'Span','p' => 'Paragraph',
            ],
            'default_value' => 'h1',
        ])
        ->addText('heading_text', [
            'label' => 'Heading Text',
            'instructions' => 'Enter the main heading text.',
            'placeholder' => 'Enter your heading text...',
            'default_value' => 'About us',
        ])
        ->addWysiwyg('content_text', [
            'label' => 'Content Text',
            'instructions' => 'Enter the description text that appears below the heading.',
            'default_value' => 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore.',
            'media_upload' => 0,
            'tabs' => 'all',
            'toolbar' => 'full',
        ])
        ->addImage('logo', [ // NEW
            'label'         => 'Logo (optional, shown above title)',
            'instructions'  => 'Upload a logo to display above the heading.',
            'return_format' => 'id',
            'preview_size'  => 'medium',
        ])
        ->addImage('background_image', [
            'label' => 'Background Image',
            'instructions' => 'Upload the background image for the hero section.',
            'return_format' => 'array',
            'preview_size' => 'medium',
        ])

    // Design Tab
    ->addTab('Design', ['placement' => 'top'])
        ->addColorPicker('heading_color', [
            'label' => 'Heading Text Color',
            'instructions' => 'Choose the color for the main heading text.',
            'default_value' => '#18181B',
        ])
        ->addColorPicker('text_color', [
            'label' => 'Content Text Color',
            'instructions' => 'Choose the color for the content text.',
            'default_value' => '#404040',
        ])

    // Layout Tab
    ->addTab('Layout', ['placement' => 'top'])
        ->addRepeater('padding_settings', [
            'label' => 'Padding Settings',
            'instructions' => 'Customize padding for different screen sizes.',
            'button_label' => 'Add Screen Size Padding',
            'min' => 0,
            'max' => 10,
        ])
            ->addSelect('screen_size', [
                'label' => 'Screen Size',
                'instructions' => 'Select the screen size for this padding setting.',
                'choices' => [
                    'xxs' => 'XXS (Extra Extra Small)',
                    'xs' => 'XS (Extra Small)',
                    'mob' => 'Mobile',
                    'sm' => 'SM (Small)',
                    'md' => 'MD (Medium)',
                    'lg' => 'LG (Large)',
                    'xl' => 'XL (Extra Large)',
                    'xxl' => 'XXL (Extra Extra Large)',
                    'ultrawide' => 'Ultrawide',
                ],
                'default_value' => 'lg',
            ])
            ->addNumber('padding_top', [
                'label' => 'Padding Top',
                'instructions' => 'Set the top padding in rem.',
                'min' => 0,
                'max' => 20,
                'step' => 0.1,
                'append' => 'rem',
                'default_value' => 9,
            ])
            ->addNumber('padding_bottom', [
                'label' => 'Padding Bottom',
                'instructions' => 'Set the bottom padding in rem.',
                'min' => 0,
                'max' => 20,
                'step' => 0.1,
                'append' => 'rem',
                'default_value' => 9,
            ])
        ->endRepeater();

return $subpage_hero;
