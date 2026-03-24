<?php
/**
 * ACF Fields: Story Slider (ACF Builder)
 */

use StoutLogic\AcfBuilder\FieldsBuilder;

$story_slider = new FieldsBuilder('story_slider', [
    'label' => 'Story Slider',
]);

$story_slider
    ->addTab('content_tab', ['label' => 'Content'])
        ->addTrueFalse('show_heading', [
            'label' => 'Show Heading',
            'default_value' => 1,
            'ui' => 1,
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
                'p' => 'p',
            ],
            'default_value' => 'h2',
            'return_format' => 'value',
            'wrapper' => ['width' => 50],
            'conditional_logic' => [[['field' => 'show_heading','operator' => '==','value' => 1]]],
        ])
        ->addText('heading_text', [
            'label' => 'Heading Text',
            'default_value' => 'Voices of hope and healing',
            'wrapper' => ['width' => 50],
            'conditional_logic' => [[['field' => 'show_heading','operator' => '==','value' => 1]]],
        ])
        ->addWysiwyg('intro_text', [
            'label' => 'Intro Text',
            'media_upload' => 0,
            'tabs' => 'all',
            'toolbar' => 'basic',
            'delay' => 0,
            'instructions' => 'Optional short lead-in shown next to the slider.',
        ])
        ->addRepeater('slides', [
            'label' => 'Slides',
            'instructions' => 'Add story slides. The center card shows large; side previews are dimmed on desktop.',
            'button_label' => 'Add Slide',
            'layout' => 'row',
            'min' => 1,
            'max' => 20,
        ])
            ->addImage('image', [
                'label' => 'Image',
                'return_format' => 'id',
                'preview_size' => 'medium',
                'instructions' => 'Upload an image. Alt & title will be pulled from the media library.',
            ])
            ->addTrueFalse('has_video', [
                'label' => 'Has Video Overlay',
                'ui' => 1,
                'default_value' => 0,
            ])
            ->addSelect('video_source_type', [
                'label' => 'Video Source Type',
                'choices' => [
                    'youtube_vimeo' => 'YouTube / Vimeo URL',
                    'local' => 'Local Uploaded Video',
                    'external_link' => 'External Link (fallback)',
                ],
                'default_value' => 'youtube_vimeo',
                'return_format' => 'value',
                'conditional_logic' => [[['field' => 'has_video','operator' => '==','value' => 1]]],
            ])
            ->addUrl('video_embed_url', [
                'label' => 'YouTube / Vimeo URL',
                'instructions' => 'Paste a YouTube or Vimeo URL (for inline playback in the slider).',
                'conditional_logic' => [[
                    ['field' => 'has_video','operator' => '==','value' => 1],
                    ['field' => 'video_source_type','operator' => '==','value' => 'youtube_vimeo'],
                ]],
            ])
            ->addFile('local_video_file', [
                'label' => 'Local Video File',
                'instructions' => 'Upload an MP4, WebM, or OGG file (plays inline in the slider).',
                'return_format' => 'array',
                'mime_types' => 'mp4,webm,ogg',
                'conditional_logic' => [[
                    ['field' => 'has_video','operator' => '==','value' => 1],
                    ['field' => 'video_source_type','operator' => '==','value' => 'local'],
                ]],
            ])
            ->addLink('video_link', [
                'label' => 'External Video Link (Fallback)',
                'return_format' => 'array',
                'instructions' => 'Fallback link when using "External Link" source type (or for backwards compatibility with old data).',
                'conditional_logic' => [[
                    ['field' => 'has_video','operator' => '==','value' => 1],
                    ['field' => 'video_source_type','operator' => '==','value' => 'external_link'],
                ]],
                'allow_null' => 1,
            ])
        ->endRepeater()
    ->addTab('design_tab', ['label' => 'Design'])
        ->addText('bg_from', [
            'label' => 'Background From Color',
            'default_value' => '#F6EDE0',
        ])
        ->addText('bg_via', [
            'label' => 'Background Via Color',
            'default_value' => '#F5F0E0',
        ])
        ->addText('bg_to', [
            'label' => 'Background To Color',
            'default_value' => '#F4F5DE',
        ])
        ->addImage('bg_image', [
            'label' => 'Background Image (optional)',
            'return_format' => 'id',
            'instructions' => 'Optional overlay pattern image.',
            'preview_size' => 'medium',
        ])
        ->addText('overlay_opacity', [
            'label' => 'Overlay Opacity (0–1)',
            'default_value' => '0.1',
        ])
        ->addText('accent_bar_color', [
            'label' => 'Accent Bar Color',
            'default_value' => '#F97316',
        ])
        ->addText('quote_stroke_color', [
            'label' => 'Quote Icon Stroke Color',
            'default_value' => '#ffffff',
        ])
        ->addText('nav_border_color', [
            'label' => 'Nav Border Color',
            'default_value' => '#93C5FD',
        ])
        ->addText('dot_active_color', [
            'label' => 'Dot Active Color',
            'default_value' => '#0A2540',
        ])
        ->addText('dot_inactive_color', [
            'label' => 'Dot Inactive Color',
            'default_value' => '#3B82F6',
        ])
        ->addSelect('card_radius', [
            'label' => 'Card Border Radius',
            'choices' => [
                'rounded-none' => 'rounded-none',
                'rounded' => 'rounded',
                'rounded-md' => 'rounded-md',
                'rounded-lg' => 'rounded-lg',
                'rounded-xl' => 'rounded-xl',
                'rounded-2xl' => 'rounded-2xl',
            ],
            'default_value' => 'rounded-md',
        ])
    ->addTab('layout_tab', ['label' => 'Layout'])
        ->addRepeater('padding_settings', [
            'label' => 'Padding Settings',
            'instructions' => 'Customize padding for different screen sizes.',
            'button_label' => 'Add Screen Size Padding',
            'layout' => 'table',
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
                'instructions' => 'Set the top padding in rem.',
                'min' => 0,
                'max' => 20,
                'step' => 0.1,
                'append' => 'rem',
            ])
            ->addNumber('padding_bottom', [
                'label' => 'Padding Bottom',
                'instructions' => 'Set the bottom padding in rem.',
                'min' => 0,
                'max' => 20,
                'step' => 0.1,
                'append' => 'rem',
            ])
        ->endRepeater();

return $story_slider;
