<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$faqs = new FieldsBuilder('faqs', [
    'label' => 'FAQs',
]);

$faqs
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
            'default_value' => 'FAQs',
        ])
        ->addTrueFalse('show_heading', [
            'label' => 'Show Heading',
            'instructions' => 'Disable for dedicated FAQ pages that only need the accordion list.',
            'default_value' => 1,
            'ui' => 1,
        ])
        ->addSelect('layout_style', [
            'label' => 'Layout Style',
            'instructions' => 'Use the page layout for dedicated FAQ pages with larger vertical spacing.',
            'choices' => [
                'default' => 'Default',
                'page' => 'FAQ Page',
            ],
            'default_value' => 'default',
            'ui' => 1,
        ])
        ->addSelect('source_mode', [
            'label' => 'Source Mode',
            'instructions' => 'Choose whether the block shows all FAQ posts, selected FAQ posts, or FAQs from selected categories.',
            'choices' => [
                'all' => 'All FAQs',
                'selected' => 'Selected FAQs',
                'category' => 'By FAQ Category',
            ],
            'default_value' => 'all',
        ])
        ->addRelationship('selected_faqs', [
            'label' => 'Selected FAQs',
            'post_type' => ['faqs'],
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
        ->addTaxonomy('selected_faq_categories', [
            'label' => 'Selected FAQ Categories',
            'taxonomy' => 'faq_category',
            'field_type' => 'multi_select',
            'return_format' => 'id',
            'allow_null' => 1,
            'multiple' => 1,
            'conditional_logic' => [
                [
                    [
                        'field' => 'source_mode',
                        'operator' => '==',
                        'value' => 'category',
                    ],
                ],
            ],
        ])
        ->addText('empty_state_message', [
            'label' => 'Empty State Message',
            'default_value' => 'No FAQs are available right now.',
        ])

    ->addTab('Design', ['label' => 'Design'])
        ->addText('section_background', [
            'label' => 'Section Background / Gradient',
            'instructions' => 'Use a CSS color or paste a full CSS gradient string.',
            'default_value' => '#FBFAF7',
        ])
        ->addColorPicker('heading_color', [
            'label' => 'Heading Color',
            'default_value' => '#1E244B',
        ])
        ->addColorPicker('underline_color', [
            'label' => 'Underline Color',
            'default_value' => '#6FC9C0',
        ])
        ->addText('item_background', [
            'label' => 'Closed Item Background / Gradient',
            'default_value' => '#FFFFFF',
        ])
        ->addText('open_item_background', [
            'label' => 'Open Item Background / Gradient',
            'default_value' => 'linear-gradient(135deg, #F8F6F3 0%, #F5F6ED 100%)',
        ])
        ->addColorPicker('question_color', [
            'label' => 'Question Color',
            'default_value' => '#1E244B',
        ])
        ->addColorPicker('answer_color', [
            'label' => 'Answer Color',
            'default_value' => '#08284B',
        ]);

return $faqs;
