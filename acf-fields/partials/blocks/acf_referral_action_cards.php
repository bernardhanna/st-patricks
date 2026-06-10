<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$referral_action_cards = new FieldsBuilder('referral_action_cards', [
    'label' => 'Referral Action Cards',
]);

$referral_action_cards
    ->addTab('Content', ['label' => 'Content'])
        ->addText('left_title', [
            'label' => 'Left Card Title',
            'default_value' => 'Make a Referral via Healthlink',
        ])
        ->addWysiwyg('left_description', [
            'label' => 'Left Card Description',
            'instructions' => 'Supporting copy shown beneath the left card title.',
            'tabs' => 'all',
            'toolbar' => 'basic',
            'media_upload' => 0,
        ])
        ->addLink('left_button', [
            'label' => 'Left Card Button',
            'instructions' => 'CTA shown at the bottom of the left card.',
            'return_format' => 'array',
        ])
        ->addSelect('left_action_icon', [
            'label' => 'Left Card Icon',
            'choices' => [
                'external' => 'External',
                'download' => 'Download',
            ],
            'default_value' => 'external',
        ])
        ->addText('right_title', [
            'label' => 'Right Card Title',
            'default_value' => 'Download our Adult Referral Form',
        ])
        ->addWysiwyg('right_description', [
            'label' => 'Right Card Description',
            'instructions' => 'Supporting copy shown beneath the right card title.',
            'tabs' => 'all',
            'toolbar' => 'basic',
            'media_upload' => 0,
        ])
        ->addLink('right_button', [
            'label' => 'Right Card Button',
            'instructions' => 'CTA shown at the bottom of the right card.',
            'return_format' => 'array',
        ])
        ->addSelect('right_action_icon', [
            'label' => 'Right Card Icon',
            'choices' => [
                'external' => 'External',
                'download' => 'Download',
            ],
            'default_value' => 'download',
        ])

    ->addTab('Design', ['label' => 'Design'])
        ->addColorPicker('left_background_color', [
            'label' => 'Left Card Background Color',
            'default_value' => '#CEF2EE',
        ])
        ->addColorPicker('right_background_color', [
            'label' => 'Right Card Background Color',
            'default_value' => '#E4F4D6',
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

return $referral_action_cards;
