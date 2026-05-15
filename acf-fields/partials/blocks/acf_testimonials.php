<?php
use StoutLogic\AcfBuilder\FieldsBuilder;

$testimonials = new FieldsBuilder('testimonials', [
  'label' => 'Testimonials',
]);

$testimonials
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
        ->addText('heading_text', [
            'label' => 'Heading',
            'instructions' => 'Section heading shown above the testimonial grid.',
            'default_value' => 'Testimonials',
        ])
        ->addSelect('layout_style', [
            'label' => 'Layout Style',
            'instructions' => 'Choose how the testimonial cards are arranged.',
            'choices' => [
                'grid_standard' => 'Standard Grid',
                'editorial_featured' => 'Editorial Featured',
            ],
            'default_value' => 'grid_standard',
        ])
        ->addSelect('source_mode', [
            'label' => 'Source Mode',
            'instructions' => 'Choose whether testimonials come from manual entries, selected testimonial posts, or all testimonial posts.',
            'choices' => [
                'manual' => 'Manual',
                'selected' => 'Selected Testimonials',
                'all' => 'All Testimonials',
            ],
            'default_value' => 'manual',
        ])
        ->addRepeater('manual_items', [
            'label' => 'Manual Testimonials',
            'instructions' => 'Use manual items when you want exact control over the quotes shown in this block.',
            'button_label' => 'Add Testimonial',
            'layout' => 'row',
            'conditional_logic' => [
                [
                    [
                        'field' => 'source_mode',
                        'operator' => '==',
                        'value' => 'manual',
                    ],
                ],
            ],
        ])
            ->addWysiwyg('quote', [
                'label' => 'Quote',
                'tabs' => 'all',
                'media_upload' => 0,
                'toolbar' => 'basic',
                'default_value' => '<p>Through our Advocacy Committee, we respond to all relevant calls for submissions by the Dáil, Seanad and Government departments.</p>',
            ])
            ->addText('author_name', [
                'label' => 'Author Name',
                'default_value' => 'Tom',
            ])
            ->addText('author_title', [
                'label' => 'Author Title',
                'default_value' => 'Service User',
            ])
            ->addSelect('card_tone', [
                'label' => 'Card Tone',
                'choices' => [
                    'lavender' => 'Lavender',
                    'mauve' => 'Mauve',
                ],
                'default_value' => 'lavender',
            ])
        ->endRepeater()
        ->addRelationship('selected_testimonials', [
            'label' => 'Selected Testimonials',
            'instructions' => 'Choose the testimonial posts to show in this block. The selected order will be preserved.',
            'post_type' => ['testimonials'],
            'filters' => ['search'],
            'return_format' => 'object',
            'conditional_logic' => [
                [
                    [
                        'field' => 'source_mode',
                        'operator' => '==',
                        'value' => 'selected',
                    ],
                ],
            ],
        ])
        ->addSelect('footer_action_mode', [
            'label' => 'Footer Action',
            'instructions' => 'Show a load-more control, a linked button, or no footer action.',
            'choices' => [
                'load_more' => 'Load More',
                'link_button' => 'Link Button',
                'none' => 'None',
            ],
            'default_value' => 'load_more',
        ])
        ->addText('load_more_button_text', [
            'label' => 'Load More Button Text',
            'default_value' => 'Load more testimonials',
            'conditional_logic' => [
                [
                    [
                        'field' => 'footer_action_mode',
                        'operator' => '==',
                        'value' => 'load_more',
                    ],
                ],
            ],
        ])
        ->addLink('footer_button_link', [
            'label' => 'Footer Button Link',
            'instructions' => 'Used when the footer action is a linked CTA button.',
            'return_format' => 'array',
            'conditional_logic' => [
                [
                    [
                        'field' => 'footer_action_mode',
                        'operator' => '==',
                        'value' => 'link_button',
                    ],
                ],
            ],
        ])

    ->addTab('Design', ['label' => 'Design'])
        ->addImage('background_image', [
            'label' => 'Background Image',
            'instructions' => 'Optional decorative background image for the testimonials section.',
            'return_format' => 'id',
            'preview_size' => 'medium',
        ])
        ->addColorPicker('background_color', [
            'label' => 'Background Color',
            'default_value' => '#F6EDE0',
        ])
        ->addColorPicker('heading_color', [
            'label' => 'Heading Color',
            'default_value' => '#1E244B',
        ])
        ->addColorPicker('accent_color', [
            'label' => 'Heading Underline Color',
            'default_value' => '#6FC9C0',
        ])
        ->addColorPicker('quote_color', [
            'label' => 'Quote Text Color',
            'default_value' => '#08284B',
        ])
        ->addColorPicker('author_color', [
            'label' => 'Author Text Color',
            'default_value' => '#08284B',
        ])
        ->addColorPicker('card_lavender_color', [
            'label' => 'Lavender Card Color',
            'default_value' => '#B4A8CE',
        ])
        ->addColorPicker('card_mauve_color', [
            'label' => 'Mauve Card Color',
            'default_value' => '#E4B8D6',
        ])
        ->addColorPicker('card_inner_color', [
            'label' => 'Inner Card Color',
            'default_value' => '#FFFFFF',
        ])
        ->addColorPicker('button_border_color', [
            'label' => 'Button Border Color',
            'default_value' => '#024B79',
        ])
        ->addColorPicker('button_text_color', [
            'label' => 'Button Text Color',
            'default_value' => '#08284B',
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

return $testimonials;
