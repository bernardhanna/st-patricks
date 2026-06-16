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
        ]);

return $video_showcase;
