<?php

/**
 * Seed Directions and Parking (page 233) to match Figma frame 2780:4181.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-directions-and-parking.php
 */

$post_id = (int) (get_page_by_path('directions-and-parking')?->ID ?? 0);

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at directions-and-parking.');
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

if (! function_exists('matrix_seed_directions_row')) {
    function matrix_seed_directions_row(string $icon_key, string $content): array
    {
        return [
            'icon_key' => $icon_key,
            'icon' => '',
            'content' => $content,
        ];
    }
}

$home = home_url('/');
$locations_map_url = home_url('/about-us/our-locations/');
$service_users_url = home_url('/service-users-and-visitors/');
$faqs_url = home_url('/service-users-and-visitors/frequently-asked-questions-faqs/');

$figma_hero = 'https://www.figma.com/api/mcp/asset/551222b9-e044-495d-bd85-b1f688d1db0c';
$hero_image_id = matrix_seed_resolve_image($figma_hero, 'directions-parking-hero-2780-4181', 'Directions and parking hero');

$hero_copy = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.';

$university_rows = [
    matrix_seed_directions_row(
        'car',
        '<p><strong>Car park:</strong> There is a paid car park available at the campus entrance on Steeven\'s Lane.</p>'
    ),
    matrix_seed_directions_row(
        'map_pin',
        '<p><strong>Address:</strong> St Patrick\'s University Hospital, James\' Street, Dublin 8, D08 K7YW, Ireland <a href="https://maps.google.com/?q=St+Patrick%27s+University+Hospital+James+Street+Dublin+8" target="_blank" rel="noopener noreferrer">Get directions</a></p>'
    ),
    matrix_seed_directions_row(
        'clock',
        '<p><strong>Visiting times:</strong> 2pm to 5pm and 6pm to 8.30pm everyday</p>'
    ),
    matrix_seed_directions_row(
        'bus',
        '<p><strong>Dublin Bus:</strong> Take the G1, G2, 13 or 123 to James\' Street or the C1, C2, C3, C4, G1, 26, 52, or 145 to Heuston Station. From either of these locations, our Dublin 8 campus is a five to 10 minute walk.</p>'
    ),
    matrix_seed_directions_row(
        'train',
        '<p><strong>Rail or Luas:</strong> Heuston Station is less than a five minute walk from St Patrick\'s University Hospital. You can reach Heuston Station through a number of <a href="https://www.irishrail.ie/en-ie/station/dublin-heuston" target="_blank" rel="noopener noreferrer">Irish Rail routes</a>, or on the red line Luas, which runs every five minutes from Dublin city centre. The Luas journey is approximately five to 10 minutes from the city centre; you need to get off at the Heuston Station stop.</p>'
    ),
];

$placeholder_rows = [
    matrix_seed_directions_row(
        'map_pin',
        '<p><strong>Address:</strong> Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>'
    ),
    matrix_seed_directions_row(
        'car',
        '<p><strong>Car park:</strong> Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt.</p>'
    ),
];

$accordion_items = [
    [
        'title' => 'Getting to St Patrick\'s University Hospital',
        'starts_open' => 1,
        'content_rows' => $university_rows,
    ],
    [
        'title' => 'Getting to Willow Grove Adolescent Unit',
        'starts_open' => 0,
        'content_rows' => $placeholder_rows,
    ],
    [
        'title' => 'Getting to St Patrick\'s Hospital Lucan',
        'starts_open' => 0,
        'content_rows' => $placeholder_rows,
    ],
    [
        'title' => 'Getting to Dean Clinic St Patrick\'s',
        'starts_open' => 0,
        'content_rows' => $placeholder_rows,
    ],
    [
        'title' => 'Getting to Dean Clinic St Patrick\'s',
        'starts_open' => 0,
        'content_rows' => $placeholder_rows,
    ],
    [
        'title' => 'Getting to Dean Clinic St Patrick\'s',
        'starts_open' => 0,
        'content_rows' => $placeholder_rows,
    ],
    [
        'title' => 'Getting to Dean Clinic Lucan',
        'starts_open' => 0,
        'content_rows' => $placeholder_rows,
    ],
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
            [
                'breadcrumb_link' => [
                    'title' => 'Service users and visitors',
                    'url' => $service_users_url,
                    'target' => '',
                ],
            ],
        ],
        'current_crumb_label' => 'Directions and Parking',
        'heading_tag' => 'h1',
        'heading' => 'Directions and Parking',
        'content' => '<p>' . esc_html($hero_copy) . '</p>',
        'primary_button' => [
            'title' => 'Our Locations Map',
            'url' => $locations_map_url,
            'target' => '',
        ],
        'hero_image' => $hero_image_id,
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
    ],
    [
        'acf_fc_layout' => 'content_accordion',
        'layout_style' => 'directions_page',
        'section_background' => '#FFFFFF',
        'panel_background' => 'linear-gradient(-28.52deg, #F3EADE 3.24%, #F1F3DE 90.88%)',
        'open_panel_background' => 'linear-gradient(-75.64deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
        'icon_tile_background_color' => '#B3DBAE',
        'items' => $accordion_items,
    ],
    [
        'acf_fc_layout' => 'useful_links',
        'heading' => 'Useful links',
        'heading_tag' => 'h2',
        'variant' => 'search',
        'background_color' => '#E9E2F7',
        'heading_color' => '#1E244B',
        'link_color' => '#1E244B',
        'links' => [
            ['link' => ['title' => 'Contact Us', 'url' => home_url('/contact-us/'), 'target' => '']],
            ['link' => ['title' => 'About Us', 'url' => home_url('/about-us/'), 'target' => '']],
            ['link' => ['title' => 'About Your Portal', 'url' => home_url('/about-your-portal/'), 'target' => '']],
            ['link' => ['title' => 'What We Offer', 'url' => home_url('/what-we-offer/'), 'target' => '']],
            ['link' => ['title' => 'Careers', 'url' => home_url('/about-us/careers/'), 'target' => '']],
            ['link' => ['title' => 'Service User FAQs', 'url' => $faqs_url, 'target' => '']],
        ],
    ],
];

update_field('hero_content_blocks', [], $post_id);
update_field('flexible_content_blocks', $flexi_rows, $post_id);

$saved_rows = get_field('flexible_content_blocks', $post_id);
$saved_count = is_array($saved_rows) ? count($saved_rows) : 0;

if (class_exists('WP_CLI')) {
    if ($saved_count === count($flexi_rows)) {
        WP_CLI::success(sprintf(
            'Seeded Directions and Parking page (%d) with %d flexi blocks.',
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
