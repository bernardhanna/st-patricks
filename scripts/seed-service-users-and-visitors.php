<?php

/**
 * Seed Service Users and Visitors (page 243) to match Figma frame 2888:2291.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-service-users-and-visitors.php
 */

$post_id = (int) (get_page_by_path('service-users-and-visitors')?->ID ?? 0);

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at service-users-and-visitors.');
    }

    exit(1);
}

if (! function_exists('matrix_seed_import_remote_image')) {
    function matrix_seed_import_remote_image(string $url, string $title, string $cache_key): int
    {
        if ($url === '') {
            return 0;
        }

        $existing = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => '_matrix_seed_figma_key',
                    'value' => $cache_key,
                ],
            ],
        ]);

        if ($existing !== []) {
            return (int) $existing[0]->ID;
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $tmp = download_url($url, 30);

        if (is_wp_error($tmp)) {
            return 0;
        }

        $path = parse_url($url, PHP_URL_PATH);
        $filename = $path ? basename($path) : 'figma-asset.jpg';

        if (! preg_match('/\.(jpe?g|png|gif|webp|svg)$/i', $filename)) {
            $filename .= '.jpg';
        }

        $file_array = [
            'name' => sanitize_file_name($filename),
            'tmp_name' => $tmp,
        ];

        $attachment_id = media_handle_sideload($file_array, 0, $title);

        if (is_wp_error($attachment_id)) {
            @unlink($tmp);

            return 0;
        }

        update_post_meta($attachment_id, '_matrix_seed_figma_key', $cache_key);
        update_post_meta($attachment_id, '_matrix_seed_figma_url', $url);

        return (int) $attachment_id;
    }
}

if (! function_exists('matrix_seed_resolve_image')) {
    function matrix_seed_resolve_image(string $figma_url, string $cache_key, string $title): int
    {
        $id = matrix_seed_import_remote_image($figma_url, $title, $cache_key);

        if ($id > 0) {
            return $id;
        }

        $attachments = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'post_mime_type' => 'image',
            'orderby' => 'ID',
            'order' => 'DESC',
        ]);

        return $attachments !== [] ? (int) $attachments[0]->ID : 0;
    }
}

if (! function_exists('matrix_seed_build_landing_cards')) {
    /**
     * @param  array<int, array{title: string, url: string, figma: string, key: string}>  $items
     * @return array<int, array<string, mixed>>
     */
    function matrix_seed_build_landing_cards(array $items, string $key_prefix): array
    {
        $cards = [];

        foreach ($items as $index => $item) {
            $image_id = matrix_seed_resolve_image(
                $item['figma'],
                $key_prefix . '-card-' . ($index + 1),
                $item['title'] . ' card image'
            );

            $cards[] = [
                'title' => $item['title'],
                'summary' => '',
                'image' => $image_id > 0 ? $image_id : '',
                'link' => [
                    'title' => $item['title'],
                    'url' => $item['url'],
                    'target' => '',
                ],
            ];
        }

        return $cards;
    }
}

$home = home_url('/');
$section_base = home_url('/service-users-and-visitors/');
$faqs_url = home_url('/service-users-and-visitors/frequently-asked-questions-faqs/');

$figma = [
    'hero' => 'https://www.figma.com/api/mcp/asset/aa9b2fb8-2d9d-46e3-b2d3-0540a929f15b',
    'card_01' => 'https://www.figma.com/api/mcp/asset/be8684eb-9ac6-405d-be3d-f6a9589cab47',
    'card_02' => 'https://www.figma.com/api/mcp/asset/050df0f1-5842-4dfa-a998-d18b24faa508',
    'card_03' => 'https://www.figma.com/api/mcp/asset/75f555eb-8ee6-419b-bfca-aaab078934c3',
    'card_04' => 'https://www.figma.com/api/mcp/asset/d8baa8cb-efaf-4063-9a32-a5fbd8c182c6',
    'card_05' => 'https://www.figma.com/api/mcp/asset/ead40c6a-f3a9-4cad-93ab-d30bb279dfee',
    'card_06' => 'https://www.figma.com/api/mcp/asset/e32c246f-8fad-483f-9995-70f3d77b698b',
    'card_07' => 'https://www.figma.com/api/mcp/asset/54a75994-ecd7-4d64-891b-e223668ed6e4',
    'card_08' => 'https://www.figma.com/api/mcp/asset/d7b3dce7-557e-4fe8-8bc5-c16a2ffc0bd9',
    'card_09' => 'https://www.figma.com/api/mcp/asset/726b5b70-eb9a-4cf0-8d84-bd5bc92fb6e0',
    'card_10' => 'https://www.figma.com/api/mcp/asset/63dceeb1-a242-4349-bc18-88570dc2d17f',
    'card_11' => 'https://www.figma.com/api/mcp/asset/bc8660b1-eee2-493d-a634-94150893eb3b',
    'card_12' => 'https://www.figma.com/api/mcp/asset/c0c0e64b-3f49-4402-bed4-a92a8359b0e7',
    'card_13' => 'https://www.figma.com/api/mcp/asset/611d14b3-1952-42f8-98aa-50be55db8ff0',
    'card_14' => 'https://www.figma.com/api/mcp/asset/3aa4ac7f-5a00-49d0-88c3-17dc6cccdf07',
    'card_15' => 'https://www.figma.com/api/mcp/asset/9542e2c9-d4bf-4e57-b159-78762b51d9bc',
];

$hero_image_id = matrix_seed_resolve_image($figma['hero'], 'service-users-visitors-hero-2888-2291', 'Service users and visitors hero');

$hero_intro = 'Service users and visitors - is a landing page (per sitemap) that links users to all other subpages within this section. Page context goes here. Max 4 lines of text. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna.';

$hero_content = sprintf(
    '<p>%s</p><p><a class="btn inline-flex min-h-[36px] items-center justify-center rounded-[6px] bg-[#024B79] px-3 py-2 text-[14px] font-medium leading-[24px] text-white no-underline" href="%s">Service users FAQs</a></p>',
    esc_html($hero_intro),
    esc_url($faqs_url)
);

$your_account_cards = matrix_seed_build_landing_cards([
    [
        'title' => 'About Your Portal',
        'url' => home_url('/about-your-portal/'),
        'figma' => $figma['card_01'],
        'key' => 'account',
    ],
    [
        'title' => 'Make a Payment',
        'url' => $section_base . 'make-a-payment-external-link-to-stripe/',
        'figma' => $figma['card_02'],
        'key' => 'account',
    ],
    [
        'title' => 'Service User IT Support',
        'url' => home_url('/service-user-it-support/'),
        'figma' => $figma['card_03'],
        'key' => 'account',
    ],
    [
        'title' => 'Frequently Asked Questions',
        'url' => $faqs_url,
        'figma' => $figma['card_04'],
        'key' => 'account',
    ],
], 'service-users-visitors-account');

$your_stay_cards = matrix_seed_build_landing_cards([
    [
        'title' => 'Your Stay in Hospital as an Adolescent',
        'url' => $section_base . 'your-stay-in-hospital-as-an-adolescent/',
        'figma' => $figma['card_05'],
        'key' => 'stay',
    ],
    [
        'title' => 'Your Stay in Hospital as an Adult',
        'url' => $section_base . 'your-stay-in-hospital-as-an-adult/',
        'figma' => $figma['card_06'],
        'key' => 'stay',
    ],
    [
        'title' => 'Attending our Day Programmes',
        'url' => $section_base . 'attending-our-day-programmes/',
        'figma' => $figma['card_07'],
        'key' => 'stay',
    ],
    [
        'title' => 'Attending a Dean Clinic',
        'url' => $section_base . 'attending-a-dean-clinic/',
        'figma' => $figma['card_08'],
        'key' => 'stay',
    ],
], 'service-users-visitors-stay');

$about_cards = matrix_seed_build_landing_cards([
    [
        'title' => 'About Mental Health',
        'url' => $section_base . 'about-mental-health/',
        'figma' => $figma['card_09'],
        'key' => 'about',
    ],
    [
        'title' => 'Service User Participation',
        'url' => $section_base . 'service-user-participation/',
        'figma' => $figma['card_10'],
        'key' => 'about',
    ],
    [
        'title' => "About our St Patrick's at Home Service",
        'url' => $section_base . 'about-our-st-patricks-at-home-service/',
        'figma' => $figma['card_11'],
        'key' => 'about',
    ],
    [
        'title' => 'Medication',
        'url' => $section_base . 'medication/',
        'figma' => $figma['card_12'],
        'key' => 'about',
    ],
], 'service-users-visitors-about');

$other_cards = matrix_seed_build_landing_cards([
    [
        'title' => 'Directions and Parking',
        'url' => home_url('/directions-and-parking/'),
        'figma' => $figma['card_13'],
        'key' => 'other',
    ],
    [
        'title' => 'Stories and Support',
        'url' => $section_base . 'stories-and-support/',
        'figma' => $figma['card_14'],
        'key' => 'other',
    ],
    [
        'title' => 'Feedback and Comments',
        'url' => $section_base . 'feedback-and-comments/',
        'figma' => $figma['card_15'],
        'key' => 'other',
    ],
], 'service-users-visitors-other');

$section_padding = [
    [
        'screen_size' => 'mob',
        'padding_top' => '3',
        'padding_bottom' => '3',
    ],
    [
        'screen_size' => 'lg',
        'padding_top' => '6.25',
        'padding_bottom' => '6.25',
    ],
];

$grid_defaults = [
    'heading_tag' => 'h2',
    'heading_color' => '#1E244B',
    'card_title_color' => '#1E244B',
    'padding_settings' => $section_padding,
];

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
        ],
        'current_crumb_label' => 'Service users and visitors',
        'heading_tag' => 'h1',
        'heading' => 'Service users and visitors',
        'content' => $hero_content,
        'hero_image' => $hero_image_id,
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
    ],
    array_merge($grid_defaults, [
        'acf_fc_layout' => 'research_cards_grid',
        'heading' => 'Section title placeholder/Your account',
        'cards' => $your_account_cards,
        'background_color' => '#FFFFFF',
    ]),
    array_merge($grid_defaults, [
        'acf_fc_layout' => 'research_cards_grid',
        'heading' => '(Placeholder) Your Stay',
        'cards' => $your_stay_cards,
        'background_color' => '#FBFAF7',
    ]),
    array_merge($grid_defaults, [
        'acf_fc_layout' => 'research_cards_grid',
        'heading' => 'About',
        'cards' => $about_cards,
        'background_color' => '#FFFFFF',
    ]),
    array_merge($grid_defaults, [
        'acf_fc_layout' => 'research_cards_grid',
        'heading' => 'Other',
        'cards' => $other_cards,
        'background_color' => '#FBFAF7',
    ]),
];

update_field('hero_content_blocks', [], $post_id);
update_field('flexible_content_blocks', $flexi_rows, $post_id);

$saved_rows = get_field('flexible_content_blocks', $post_id);
$saved_count = is_array($saved_rows) ? count($saved_rows) : 0;

if (class_exists('WP_CLI')) {
    if ($saved_count === count($flexi_rows)) {
        WP_CLI::success(sprintf(
            'Seeded Service Users and Visitors page (%d) with %d flexi blocks.',
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
