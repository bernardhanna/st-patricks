<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$timeline = new FieldsBuilder('timeline', [
    'label' => 'Timeline',
]);

$timeline
    ->addTab('Content', ['label' => 'Content'])
        ->addSelect('heading_tag', [
            'label' => 'Heading Tag',
            'choices' => [
                'h1' => 'H1',
                'h2' => 'H2',
                'h3' => 'H3',
                'h4' => 'H4',
                'h5' => 'H5',
                'h6' => 'H6',
                'span' => 'Span',
                'p' => 'Paragraph',
            ],
            'default_value' => 'h2',
        ])
        ->addText('heading', [
            'label' => 'Heading',
            'default_value' => 'Our History, timeline title - medium length',
        ])
        ->addWysiwyg('intro', [
            'label' => 'Intro Copy',
            'instructions' => 'Optional supporting copy shown beneath the heading.',
            'tabs' => 'all',
            'toolbar' => 'basic',
            'media_upload' => 0,
        ])
        ->addRepeater('timeline_items', [
            'label' => 'Timeline Items',
            'instructions' => 'Add each milestone in display order. Leave Side empty to alternate automatically.',
            'button_label' => 'Add Timeline Item',
            'layout' => 'row',
            'min' => 1,
        ])
            ->addSelect('side', [
                'label' => 'Side',
                'instructions' => 'Choose which side the card appears on. Leave empty to alternate automatically.',
                'choices' => [
                    '' => 'Auto alternate',
                    'left' => 'Left',
                    'right' => 'Right',
                ],
                'allow_null' => 1,
                'ui' => 1,
            ])
            ->addDatePicker('event_date', [
                'label' => 'Event Date',
                'instructions' => 'Used for semantic <time datetime="..."> output.',
                'return_format' => 'Y-m-d',
                'display_format' => 'd.m.Y',
            ])
            ->addText('event_date_label', [
                'label' => 'Event Date Label',
                'instructions' => 'Optional display label such as 2.8.1746. Overrides the date picker value when set.',
            ])
            ->addImage('image', [
                'label' => 'Card Image',
                'instructions' => 'Optional image shown at the top of the milestone card.',
                'return_format' => 'array',
                'preview_size' => 'medium',
            ])
            ->addText('item_heading', [
                'label' => 'Card Heading',
            ])
            ->addSelect('item_heading_tag', [
                'label' => 'Card Heading Tag',
                'choices' => [
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6',
                    'span' => 'Span',
                    'p' => 'Paragraph',
                ],
                'default_value' => 'h3',
            ])
            ->addWysiwyg('item_text', [
                'label' => 'Card Text',
                'tabs' => 'all',
                'toolbar' => 'basic',
                'media_upload' => 0,
            ])
            ->addLink('cta_link', [
                'label' => 'Supporting Material CTA',
                'instructions' => 'Optional outlined button shown at the bottom of the card.',
                'return_format' => 'array',
            ])
        ->endRepeater()
        ->addLink('footer_button_link', [
            'label' => 'Footer Button Link',
            'instructions' => 'Optional filled CTA shown below the timeline.',
            'return_format' => 'array',
        ])

    ->addTab('Design', ['label' => 'Design'])
        ->addColorPicker('card_background_color', [
            'label' => 'Card Background Color',
            'default_value' => '#E4F4D6',
        ])
        ->addColorPicker('timeline_accent_color', [
            'label' => 'Timeline Accent Color',
            'default_value' => '#6FC9C0',
        ])

    ->addTab('Layout', ['label' => 'Layout'])
        ->addRepeater('padding_settings', [
            'label' => 'Padding Settings',
            'instructions' => 'Customize padding for different screen sizes.',
            'button_label' => 'Add Screen Size Padding',
        ])
            ->addSelect('screen_size', [
                'label' => 'Screen Size',
                'choices' => [
                    'xxs' => 'xxs',
                    'xs' => 'xs',
                    'mob' => 'mob',
                    'sm' => 'sm',
                    'md' => 'md',
                    'lg' => 'lg',
                    'xl' => 'xl',
                    'xxl' => 'xxl',
                    'ultrawide' => 'ultrawide',
                ],
            ])
            ->addNumber('padding_top', [
                'label' => 'Padding Top',
                'min' => 0,
                'max' => 20,
                'step' => 0.01,
                'append' => 'rem',
            ])
            ->addNumber('padding_bottom', [
                'label' => 'Padding Bottom',
                'min' => 0,
                'max' => 20,
                'step' => 0.01,
                'append' => 'rem',
            ])
        ->endRepeater();

return $timeline;
