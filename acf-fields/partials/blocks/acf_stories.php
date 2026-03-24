<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$stories = new FieldsBuilder('stories', [
    'label' => 'Stories Carousel',
]);

$stories
    ->addTab('Content', ['label' => 'Content'])
    ->addNumber('posts_per_slide', [
        'label' => 'Posts Per Slide',
        'instructions' => 'Number of blog posts to show per slide.',
        'default_value' => 4,
        'min' => 1,
        'max' => 6,
        'step' => 1,
    ])
    ->addNumber('max_posts', [
        'label' => 'Maximum Posts',
        'instructions' => 'Maximum number of blog posts to display in total.',
        'default_value' => 20,
        'min' => 1,
        'max' => 50,
        'step' => 1,
    ])
    ->addTrueFalse('show_date', [
        'label' => 'Show Post Date',
        'instructions' => 'Display the publication date for each post.',
        'default_value' => 1,
        'ui' => 1,
    ])
    ->addTrueFalse('show_excerpt', [
        'label' => 'Show Post Excerpt',
        'instructions' => 'Display a short excerpt for each post.',
        'default_value' => 0,
        'ui' => 1,
    ])

    ->addTab('Design', ['label' => 'Design'])
    ->addColorPicker('card_background_color', [
        'label' => 'Card Background Color',
        'instructions' => 'Background color for the story cards.',
        'default_value' => '#fafaf9',
    ])
    ->addColorPicker('divider_color', [
        'label' => 'Divider Color',
        'instructions' => 'Color for the decorative divider line in each card.',
        'default_value' => '#c7d2fe',
    ])
    ->addColorPicker('text_color', [
        'label' => 'Text Color',
        'instructions' => 'Color for the story titles and content.',
        'default_value' => '#0f2419',
    ])
    ->addColorPicker('date_color', [
        'label' => 'Date Color',
        'instructions' => 'Color for the publication dates.',
        'default_value' => '#0f2419',
    ])

    ->addTab('Layout', ['label' => 'Layout'])
    ->addRepeater('padding_settings', [
        'label' => 'Padding Settings',
        'instructions' => 'Customize padding for different screen sizes.',
        'button_label' => 'Add Screen Size Padding',
        'layout' => 'table',
    ])
        ->addSelect('screen_size', [
            'label' => 'Screen Size',
            'instructions' => 'Select the screen size for this padding setting.',
            'choices' => [
                'xxs' => 'XXS (320px+)',
                'xs' => 'XS (480px+)',
                'mob' => 'Mobile (575px+)',
                'sm' => 'Small (640px+)',
                'md' => 'Medium (768px+)',
                'lg' => 'Large (1100px+)',
                'xl' => 'XL (1280px+)',
                'xxl' => 'XXL (1440px+)',
                'ultrawide' => 'Ultrawide (1920px+)',
            ],
            'default_value' => 'md',
        ])
        ->addNumber('padding_top', [
            'label' => 'Padding Top',
            'instructions' => 'Set the top padding in rem units.',
            'min' => 0,
            'max' => 20,
            'step' => 0.1,
            'append' => 'rem',
            'default_value' => 6,
        ])
        ->addNumber('padding_bottom', [
            'label' => 'Padding Bottom',
            'instructions' => 'Set the bottom padding in rem units.',
            'min' => 0,
            'max' => 20,
            'step' => 0.1,
            'append' => 'rem',
            'default_value' => 6,
        ])
    ->endRepeater();

return $stories;
