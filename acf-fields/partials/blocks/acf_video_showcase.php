<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$video_showcase = new FieldsBuilder('video_showcase', [
    'label' => 'Video Showcase',
]);

$video_showcase
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
            'default_value' => 'Video showcase',
        ])
        ->addWysiwyg('intro', [
            'label' => 'Intro Copy',
            'tabs' => 'all',
            'toolbar' => 'basic',
            'media_upload' => 0,
        ])
        ->addSelect('layout_style', [
            'label' => 'Layout Style',
            'choices' => [
                'feature_single' => 'Feature Single',
                'feature_slider' => 'Feature Slider',
                'compact_slider' => 'Compact Slider',
            ],
            'default_value' => 'feature_single',
        ])
        ->addSelect('video_surface_size', [
            'label' => 'Video Surface Size',
            'choices' => [
                'default' => 'Default',
                'small' => 'Small (48.625rem x 24.5rem max)',
            ],
            'default_value' => 'default',
        ])
        ->addSelect('text_max_width', [
            'label' => 'Text Max Width',
            'instructions' => 'Use full width for heading, intro, and slide captions to span the content area.',
            'choices' => [
                'default' => 'Default (narrow text column)',
                'full' => 'Full width',
            ],
            'default_value' => 'default',
        ])
        ->addRepeater('slides', [
            'label' => 'Slides',
            'button_label' => 'Add Slide',
            'layout' => 'row',
            'min' => 1,
        ])
            ->addImage('poster_image', [
                'label' => 'Poster Image',
                'return_format' => 'array',
                'preview_size' => 'medium',
            ])
            ->addSelect('video_source_type', [
                'label' => 'Video Source Type',
                'choices' => [
                    'embed_url' => 'Embed URL',
                    'local_file' => 'Local File',
                ],
                'default_value' => 'embed_url',
            ])
            ->addUrl('video_embed_url', [
                'label' => 'Video Embed URL',
                'instructions' => 'Use a YouTube or Vimeo URL.',
                'conditional_logic' => [[
                    [
                        'field' => 'video_source_type',
                        'operator' => '==',
                        'value' => 'embed_url',
                    ],
                ]],
            ])
            ->addFile('local_video_file', [
                'label' => 'Local Video File',
                'return_format' => 'array',
                'mime_types' => 'mp4,webm,ogg',
                'conditional_logic' => [[
                    [
                        'field' => 'video_source_type',
                        'operator' => '==',
                        'value' => 'local_file',
                    ],
                ]],
            ])
            ->addWysiwyg('caption', [
                'label' => 'Caption / Description',
                'tabs' => 'all',
                'toolbar' => 'basic',
                'media_upload' => 0,
            ])
            ->addLink('cta_link', [
                'label' => 'CTA Link',
                'return_format' => 'array',
            ])
        ->endRepeater()

    ->addTab('Design', ['label' => 'Design'])
        ->addText('section_background', [
            'label' => 'Section Background / Gradient',
            'instructions' => 'Use a CSS color or paste a full CSS gradient string.',
            'default_value' => 'linear-gradient(135deg, #F6EDE0 0%, #F5F0E0 48%, #F4F5DE 100%)',
        ])
        ->addSelect('vertical_padding', [
            'label' => 'Vertical Padding',
            'instructions' => 'Legacy “Standard” / “Bottom only” keep current pages unchanged. Shared options match other flexi blocks.',
            'choices' => [
                'default' => 'Top & bottom (100px desktop)',
                'no_bottom' => 'Top only (no bottom on desktop)',
                'no_top' => 'Bottom only (no top on desktop)',
                'medium' => 'Top & bottom (60px desktop)',
                'none' => 'No padding',
                'custom' => 'Custom (per breakpoint)',
                'standard' => 'Standard (48px all breakpoints, legacy)',
                'bottom_only' => 'Bottom only (legacy)',
            ],
            'default_value' => 'default',
            'ui' => 1,
        ])
        ->addRepeater('padding_settings', [
            'label' => 'Custom Padding',
            'instructions' => 'Only used when Vertical Padding is “Custom”. Enter rem values per breakpoint (e.g. 2 = 32px).',
            'button_label' => 'Add Breakpoint',
            'layout' => 'table',
            'conditional_logic' => [[
                [
                    'field' => 'vertical_padding',
                    'operator' => '==',
                    'value' => 'custom',
                ],
            ]],
        ])
            ->addSelect('screen_size', [
                'label' => 'Breakpoint',
                'choices' => [
                    'mob' => 'Mobile (mob)',
                    'sm' => 'Small (sm)',
                    'md' => 'Medium (md)',
                    'lg' => 'Large (lg)',
                    'xl' => 'Extra large (xl)',
                ],
                'default_value' => 'lg',
                'ui' => 1,
            ])
            ->addNumber('padding_top', [
                'label' => 'Top (rem)',
                'min' => 0,
                'step' => 0.01,
            ])
            ->addNumber('padding_bottom', [
                'label' => 'Bottom (rem)',
                'min' => 0,
                'step' => 0.01,
            ])
        ->endRepeater();

return $video_showcase;
