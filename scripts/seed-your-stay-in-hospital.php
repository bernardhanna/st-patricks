<?php

/**
 * Seed Your Stay in Hospital pages to match Figma frame 2888:5574 (adult layout).
 * Seeds both adult and adolescent URLs with audience-specific titles.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-your-stay-in-hospital.php
 */

require_once __DIR__ . '/lib/service-users-visitors-page-layout.php';

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

if (! function_exists('matrix_seed_build_image_field')) {
    function matrix_seed_build_image_field(int $attachment_id, string $alt): array
    {
        if ($attachment_id <= 0) {
            return [];
        }

        return [
            'ID' => $attachment_id,
            'url' => wp_get_attachment_url($attachment_id),
            'alt' => $alt,
            'title' => $alt,
        ];
    }
}

if (! function_exists('matrix_seed_accordion_item')) {
    function matrix_seed_accordion_item(string $title, string $content, bool $starts_open = false): array
    {
        return [
            'title' => $title,
            'starts_open' => $starts_open ? 1 : 0,
            'content_rows' => [
                [
                    'icon_key' => '',
                    'icon' => '',
                    'content' => $content,
                ],
            ],
        ];
    }
}

if (! function_exists('matrix_seed_build_your_stay_in_hospital_rows')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function matrix_seed_build_your_stay_in_hospital_rows(string $audience, string $image_cache_prefix): array
    {
        $audience = strtolower($audience);
        $is_adult = $audience === 'adult';
        $audience_label = $is_adult ? 'an Adult' : 'an Adolescent';
        $page_title = 'Your Stay in Hospital as ' . $audience_label;

        $home = home_url('/');
        $service_users_url = home_url('/service-users-and-visitors/');
        $programmes_url = home_url('/programmes-therapies/');

        $figma = [
            'preparing_image' => 'https://www.figma.com/api/mcp/asset/146958b9-e52d-45a1-b615-f92dd1052268',
            'video' => 'https://www.figma.com/api/mcp/asset/833ea9f3-8545-42a6-a50f-7093823048af',
        ];

        $preparing_image_id = matrix_seed_resolve_image(
            $figma['preparing_image'],
            $image_cache_prefix . '-preparing-2888-5574',
            'Preparing for your stay - ' . $audience
        );
        $video_poster_id = matrix_seed_resolve_image(
            $figma['video'],
            $image_cache_prefix . '-video-2888-5574',
            'Your stay in hospital video - ' . $audience
        );

        $hero_copy = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad mini. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur.';
        $preparing_intro = '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat.</p>';
        $preparing_body = '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat.</p>';
        $what_to_expect_intro = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad mini.</p>';
        $accordion_open_body = '<p>Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent.Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent.Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent.</p>';
        $video_intro = '<p>Videos and images section as requested. Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.</p>';

        $video_slides = [];
        foreach (range(1, 5) as $index) {
            $video_slides[] = [
                'poster_image' => matrix_seed_build_image_field($video_poster_id, 'Stay in hospital video slide ' . $index),
                'video_source_type' => 'embed_url',
                'video_embed_url' => 'https://www.youtube.com/watch?v=ysz5S6PUM-U',
                'caption' => '',
                'cta_link' => '',
            ];
        }

        $useful_links_defaults = function_exists('matrix_get_search_results_useful_links_defaults')
            ? matrix_get_search_results_useful_links_defaults()
            : ['links' => []];

        return matrix_apply_service_users_visitors_flexi_layout([
            [
                'acf_fc_layout' => 'hero_with_breadcrumbs',
                'layout_style' => 'image_split',
                'show_breadcrumbs' => 1,
                'breadcrumb_source' => 'manual',
                'manual_breadcrumbs' => [
                    ['breadcrumb_link' => ['title' => 'Home', 'url' => $home, 'target' => '']],
                    ['breadcrumb_link' => ['title' => 'Service Users and Visitors', 'url' => $service_users_url, 'target' => '']],
                ],
                'current_crumb_label' => $page_title,
                'heading_tag' => 'h1',
                'heading' => $page_title,
                'content' => '<p>' . esc_html($hero_copy) . '</p>',
                'primary_button' => [
                    'title' => 'Search Programmes and Therapies',
                    'url' => $programmes_url,
                    'target' => '',
                ],
                'hero_image' => '',
                'background_color' => '#C6ECF4',
                'breadcrumb_background_color' => '#F1F8F9',
                'heading_color' => '#08284B',
                'text_color' => '#08284B',
            ],
            [
                'acf_fc_layout' => 'content',
                'heading' => 'Preparing for your stay with us',
                'accent_position' => 'below_heading',
                'intro_text' => $preparing_intro,
                'content' => $preparing_body,
                'image' => $preparing_image_id,
                'layout_style' => 'image_left',
                'background_type' => 'color',
                'background_color' => '#FFFFFF',
            ],
            [
                'acf_fc_layout' => 'content',
                'heading' => 'What to expect',
                'accent_position' => 'below_heading',
                'intro_text' => '',
                'content' => $what_to_expect_intro,
                'image' => '',
                'layout_style' => 'image_left',
                'background_type' => 'color',
                'background_color' => '#FBFAF7',
            ],
            [
                'acf_fc_layout' => 'content_accordion',
                'section_background' => '#FBFAF7',
                'panel_background' => '#FFFFFF',
                'open_panel_background' => 'linear-gradient(-42.77deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
                'items' => [
                    matrix_seed_accordion_item('Who we Support', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>'),
                    matrix_seed_accordion_item('How day programmes can help', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>'),
                    matrix_seed_accordion_item('Placeholder', $accordion_open_body, true),
                    matrix_seed_accordion_item('Our Commitment', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>'),
                    matrix_seed_accordion_item('Sit amet lorem consectetur.', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>'),
                    matrix_seed_accordion_item('Lorem ipsum dolor sit amet consectetur.', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>'),
                ],
            ],
            [
                'acf_fc_layout' => 'video_showcase',
                'heading' => 'Title, slider',
                'intro' => $video_intro,
                'slides' => $video_slides,
                'section_background' => 'linear-gradient(-80.44deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
            ],
            [
                'acf_fc_layout' => 'useful_links',
                'heading' => 'Useful links (all placeholder/suggestions)',
                'variant' => 'search',
                'background_color' => '#E9E2F7',
                'heading_color' => '#1E244B',
                'link_color' => '#1E244B',
                'links' => $useful_links_defaults['links'] ?? [],
            ],
        ]);
    }
}

$pages = [
    [
        'path' => 'service-users-and-visitors/your-stay-in-hospital-as-an-adult',
        'audience' => 'adult',
        'cache_prefix' => 'stay-in-hospital-adult',
    ],
    [
        'path' => 'service-users-and-visitors/your-stay-in-hospital-as-an-adolescent',
        'audience' => 'adolescent',
        'cache_prefix' => 'stay-in-hospital-adolescent',
    ],
];

$seeded = 0;

foreach ($pages as $page) {
    $post_id = (int) (get_page_by_path($page['path'])?->ID ?? 0);

    if ($post_id === 0) {
        if (class_exists('WP_CLI')) {
            WP_CLI::warning('Could not find page at ' . $page['path'] . '.');
        }
        continue;
    }

    $flexi_rows = matrix_seed_build_your_stay_in_hospital_rows($page['audience'], $page['cache_prefix']);

    update_field('hero_content_blocks', [], $post_id);
    update_field('flexible_content_blocks', $flexi_rows, $post_id);

    $saved_rows = get_field('flexible_content_blocks', $post_id);
    $saved_count = is_array($saved_rows) ? count($saved_rows) : 0;

    if (class_exists('WP_CLI')) {
        if ($saved_count === count($flexi_rows)) {
            WP_CLI::success(sprintf(
                'Seeded %s page (%d) with %d flexi blocks.',
                $page['path'],
                $post_id,
                $saved_count
            ));
            $seeded++;
        } else {
            WP_CLI::warning(sprintf(
                'Updated %s (%d) but expected %d blocks, found %d.',
                $page['path'],
                $post_id,
                count($flexi_rows),
                $saved_count
            ));
        }
    }
}

if ($seeded === 0 && class_exists('WP_CLI')) {
    WP_CLI::error('No stay-in-hospital pages were seeded.');
}
