<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$content_cta = new FieldsBuilder('content_cta', [
    'label' => 'Content CTA',
]);

$content_cta
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
            'default_value' => 'Are you a healthcare professional?',
        ])
        ->addWysiwyg('body', [
            'label' => 'Body Copy',
            'instructions' => 'Supporting copy shown beside the CTA button.',
            'tabs' => 'all',
            'toolbar' => 'basic',
            'media_upload' => 0,
        ])
        ->addLink('button_link', [
            'label' => 'CTA Button',
            'return_format' => 'array',
        ])

    ->addTab('Design', ['label' => 'Design'])
        ->addSelect('background_type', [
            'label' => 'Background Type',
            'choices' => [
                'color' => 'Solid Color',
                'gradient' => 'Gradient',
            ],
            'default_value' => 'color',
        ])
        ->addColorPicker('background_color', [
            'label' => 'Background Color',
            'instructions' => 'Used when Background Type is Solid Color.',
            'default_value' => '#E9E2F7',
            'conditional_logic' => [
                [
                    [
                        'field' => 'background_type',
                        'operator' => '==',
                        'value' => 'color',
                    ],
                ],
            ],
        ])
        ->addText('background_gradient', [
            'label' => 'Background Gradient',
            'instructions' => 'Paste a full CSS gradient value, e.g. linear-gradient(135deg, #E9E2F7 0%, #CEF2EE 100%).',
            'default_value' => 'linear-gradient(278deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
            'conditional_logic' => [
                [
                    [
                        'field' => 'background_type',
                        'operator' => '==',
                        'value' => 'gradient',
                    ],
                ],
            ],
        ])

    ->addTab('Layout', ['label' => 'Layout'])
        ->addRepeater('padding_settings', [
            'label' => 'Padding Settings',
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
                'step' => 0.1,
                'append' => 'rem',
            ])
            ->addNumber('padding_bottom', [
                'label' => 'Padding Bottom',
                'min' => 0,
                'max' => 20,
                'step' => 0.1,
                'append' => 'rem',
            ])
        ->endRepeater();

return $content_cta;
