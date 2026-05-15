<?php

/**
 * Seed Policies and Publications (page 245, Figma 3279:19393).
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-policies-and-publications.php
 */

$post_id = (int) (get_page_by_path('about-us/policies-and-publications')?->ID ?? 0);

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at about-us/policies-and-publications.');
    }

    exit(1);
}

$home = home_url('/');
$about_us_url = home_url('/about-us/');
$sample_pdf_url = home_url('/wp-content/uploads/sample.pdf');
$section_padding = [
    ['screen_size' => 'mob', 'padding_top' => '3', 'padding_bottom' => '3'],
    ['screen_size' => 'lg', 'padding_top' => '6.25', 'padding_bottom' => '6.25'],
];

$placeholder_titles = [
    'Lorem ipsum dolor sit amet lorem consectetur.',
    'Lorem ipsum dolor sit amet consectetur.',
    'Lorem ipsum sit amet consectetur.',
    'Lorem ipsum dolor sit amet lorem consectetur.',
    'Sit amet lorem consectetur.',
    'Lorem ipsum dolor sit amet consectetur.',
];

$placeholder_body = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>';

if (! function_exists('matrix_seed_policies_closed_items')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function matrix_seed_policies_closed_items(array $titles, string $body): array
    {
        $items = [];

        foreach ($titles as $title) {
            $items[] = [
                'title' => $title,
                'starts_open' => 0,
                'content_rows' => [
                    [
                        'row_type' => 'text',
                        'content' => $body,
                    ],
                ],
            ];
        }

        return $items;
    }
}

if (! function_exists('matrix_seed_policies_pdf_documents')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function matrix_seed_policies_pdf_documents(string $pdf_url): array
    {
        return [
            [
                'title' => 'Charter of Patient and Family Rights and Responsibilities',
                'document_link' => [
                    'title' => 'PDF opens in a new tab',
                    'url' => $pdf_url,
                    'target' => '_blank',
                ],
            ],
            [
                'title' => 'Charter of Patient and Family Rights and Responsibilities',
                'document_link' => [
                    'title' => 'PDF opens in a new tab',
                    'url' => $pdf_url,
                    'target' => '_blank',
                ],
            ],
        ];
    }
}

$declarations_open_rows = [
    [
        'row_type' => 'text',
        'content' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.</p>',
    ],
    [
        'row_type' => 'pdf_grid',
        'pdf_documents' => matrix_seed_policies_pdf_documents($sample_pdf_url),
    ],
];

$strategies_open_rows = [
    [
        'row_type' => 'text',
        'content' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.</p>',
    ],
    [
        'row_type' => 'link_cards',
        'link_cards' => [
            [
                'title' => 'If it needs a title or description',
                'button_link' => [
                    'title' => 'External Privacy Notice',
                    'url' => 'https://example.com/privacy-notice',
                    'target' => '_blank',
                ],
            ],
            [
                'title' => 'If it needs a title or description',
                'button_link' => [
                    'title' => 'External Privacy Notice',
                    'url' => 'https://example.com/privacy-notice',
                    'target' => '_blank',
                ],
            ],
        ],
    ],
    [
        'row_type' => 'external_links',
        'external_links' => [
            [
                'title' => 'External link only - no title',
                'link' => [
                    'title' => 'External link only - no title',
                    'url' => 'https://example.com/external-link-1',
                    'target' => '_blank',
                ],
            ],
            [
                'title' => 'External link only - no title',
                'link' => [
                    'title' => 'External link only - no title',
                    'url' => 'https://example.com/external-link-2',
                    'target' => '_blank',
                ],
            ],
            [
                'title' => 'External link only - no title',
                'link' => [
                    'title' => 'External link only - no title',
                    'url' => 'https://example.com/external-link-3',
                    'target' => '_blank',
                ],
            ],
            [
                'title' => 'External link only - no title',
                'link' => [
                    'title' => 'External link only - no title',
                    'url' => 'https://example.com/external-link-4',
                    'target' => '_blank',
                ],
            ],
        ],
    ],
];

$declarations_items = array_merge(
    [
        [
            'title' => 'Placeholder Declarations and charters',
            'starts_open' => 1,
            'content_rows' => $declarations_open_rows,
        ],
    ],
    matrix_seed_policies_closed_items($placeholder_titles, $placeholder_body)
);

$strategies_items = array_merge(
    [
        [
            'title' => 'Placeholder Strategies and reports',
            'starts_open' => 1,
            'content_rows' => $strategies_open_rows,
        ],
    ],
    matrix_seed_policies_closed_items($placeholder_titles, $placeholder_body)
);

$hero_intro = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.';

$flexi_rows = [
    [
        'acf_fc_layout' => 'hero_with_breadcrumbs',
        'layout_style' => 'image_split',
        'show_breadcrumbs' => 1,
        'breadcrumb_source' => 'manual',
        'manual_breadcrumbs' => [
            [
                'breadcrumb_link' => [
                    'title' => 'Home',
                    'url' => $home,
                    'target' => '',
                ],
            ],
            [
                'breadcrumb_link' => [
                    'title' => 'Who we are',
                    'url' => $about_us_url,
                    'target' => '',
                ],
            ],
        ],
        'current_crumb_label' => 'Policies and Publications',
        'heading_tag' => 'h1',
        'heading' => 'Policies and Publications',
        'content' => '<p>' . esc_html($hero_intro) . '</p>',
        'hero_image' => '',
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
    ],
    [
        'acf_fc_layout' => 'content_accordion',
        'layout_style' => 'policies_page',
        'section_background' => '#FFFFFF',
        'panel_background' => 'linear-gradient(-29.03deg, #F3EADE 3.24%, #F1F3DE 90.88%)',
        'open_panel_background' => 'linear-gradient(-80.97deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
        'icon_tile_background_color' => '#FFFFFF',
        'items' => $declarations_items,
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Strategies and reports',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'intro_text' => '<p><strong>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</strong></p>',
        'content' => '',
        'layout_style' => 'image_left',
        'background_type' => 'color',
        'background_color' => '#FBF8F3',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content_accordion',
        'layout_style' => 'policies_page',
        'section_background' => '#FBF8F3',
        'panel_background' => 'linear-gradient(-29.03deg, #F3EADE 3.24%, #F1F3DE 90.88%)',
        'open_panel_background' => 'linear-gradient(-80.97deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
        'icon_tile_background_color' => '#FFFFFF',
        'items' => $strategies_items,
        'padding_settings' => $section_padding,
    ],
];

update_field('hero_content_blocks', [], $post_id);
update_field('flexible_content_blocks', $flexi_rows, $post_id);

$saved_rows = get_field('flexible_content_blocks', $post_id);
$saved_count = is_array($saved_rows) ? count($saved_rows) : 0;

if (class_exists('WP_CLI')) {
    if ($saved_count === count($flexi_rows)) {
        WP_CLI::success(sprintf(
            'Seeded Policies and Publications page (%d) with %d flexi blocks.',
            $post_id,
            $saved_count
        ));
    } else {
        WP_CLI::warning(sprintf(
            'Updated page %d but expected %d blocks, found %d.',
            $post_id,
            count($flexi_rows),
            $saved_count
        ));
    }
}
