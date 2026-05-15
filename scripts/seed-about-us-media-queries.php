<?php

/**
 * Seed About Us > Media Queries (page 262) to match Figma frame 2780:3711.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-about-us-media-queries.php
 */

$post_id = (int) (get_page_by_path('about-us/media-queries')?->ID ?? 0);

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at about-us/media-queries.');
    }

    exit(1);
}

if (! function_exists('matrix_seed_import_theme_image')) {
    function matrix_seed_import_theme_image(string $relative_path, string $title): int
    {
        $file = get_template_directory() . '/' . ltrim($relative_path, '/');

        if (! is_readable($file)) {
            return 0;
        }

        $filename = basename($file);
        $existing = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => '_matrix_seed_source',
                    'value' => $relative_path,
                ],
            ],
        ]);

        if ($existing !== []) {
            return (int) $existing[0]->ID;
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $upload = wp_upload_bits($filename, null, (string) file_get_contents($file));

        if (! empty($upload['error'])) {
            return 0;
        }

        $filetype = wp_check_filetype($filename, null);
        $attachment_id = wp_insert_attachment([
            'post_mime_type' => $filetype['type'] ?: 'image/png',
            'post_title' => $title,
            'post_content' => '',
            'post_status' => 'inherit',
        ], $upload['file']);

        if (is_wp_error($attachment_id) || ! $attachment_id) {
            return 0;
        }

        $metadata = wp_generate_attachment_metadata($attachment_id, $upload['file']);
        wp_update_attachment_metadata($attachment_id, $metadata);
        update_post_meta($attachment_id, '_matrix_seed_source', $relative_path);

        return (int) $attachment_id;
    }
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
    function matrix_seed_resolve_image(string $figma_url, string $cache_key, string $title, string $theme_fallback = ''): int
    {
        $id = matrix_seed_import_remote_image($figma_url, $title, $cache_key);

        if ($id > 0) {
            return $id;
        }

        if ($theme_fallback !== '') {
            $id = matrix_seed_import_theme_image($theme_fallback, $title);

            if ($id > 0) {
                return $id;
            }
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

if (! function_exists('matrix_seed_ensure_team_category')) {
    function matrix_seed_ensure_team_category(string $slug, string $name): int
    {
        $term = get_term_by('slug', $slug, 'team_member_category');

        if ($term instanceof WP_Term) {
            return (int) $term->term_id;
        }

        $created = wp_insert_term($name, 'team_member_category', ['slug' => $slug]);

        if (is_wp_error($created)) {
            return 0;
        }

        return (int) ($created['term_id'] ?? 0);
    }
}

if (! function_exists('matrix_seed_find_team_member_by_title')) {
    function matrix_seed_find_team_member_by_title(string $title): int
    {
        $matches = get_posts([
            'post_type' => 'team_members',
            'post_status' => 'any',
            'posts_per_page' => 1,
            'title' => $title,
        ]);

        return $matches !== [] ? (int) $matches[0]->ID : 0;
    }
}

if (! function_exists('matrix_seed_ensure_spokesperson')) {
    function matrix_seed_ensure_spokesperson(
        string $title,
        string $job_title,
        string $profile_teaser,
        int $image_id,
        string $seed_key,
        int $spokespeople_term_id
    ): int {
        $existing = get_posts([
            'post_type' => 'team_members',
            'post_status' => 'any',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => '_matrix_seed_key',
                    'value' => $seed_key,
                ],
            ],
        ]);

        if ($existing !== []) {
            $member_id = (int) $existing[0]->ID;
            wp_update_post([
                'ID' => $member_id,
                'post_title' => $title,
                'post_status' => 'publish',
            ]);
        } else {
            $member_id = $title === 'Team member name'
                ? 0
                : matrix_seed_find_team_member_by_title($title);

            if ($member_id < 1) {
                $member_id = wp_insert_post([
                    'post_type' => 'team_members',
                    'post_status' => 'publish',
                    'post_title' => $title,
                ]);

                if (is_wp_error($member_id) || ! $member_id) {
                    return 0;
                }
            }

            update_post_meta((int) $member_id, '_matrix_seed_key', $seed_key);
        }

        update_field('job_title', $job_title, $member_id);
        update_field('profile_teaser', $profile_teaser, $member_id);

        if ($image_id > 0) {
            set_post_thumbnail($member_id, $image_id);
        }

        if ($spokespeople_term_id > 0) {
            wp_set_object_terms($member_id, [$spokespeople_term_id], 'team_member_category', true);
        }

        return (int) $member_id;
    }
}

$home = home_url('/');
$about_us_url = home_url('/about-us/');
$contact_url = home_url('/contact-us/');
$news_url = home_url('/news-and-events/');

$figma = [
    'hero' => 'https://www.figma.com/api/mcp/asset/2fff4dfd-919a-406f-a0ae-57d778912fca',
    'responsible_reporting' => 'https://www.figma.com/api/mcp/asset/5ea65f6b-6d46-43a8-8276-d48269f7abc4',
    'portrait_a' => 'https://www.figma.com/api/mcp/asset/3da52277-6e39-4a22-ae4c-f17b2a9f2ddd',
    'portrait_b' => 'https://www.figma.com/api/mcp/asset/1b550473-c5ff-4a11-971d-51001791ec7a',
    'portrait_c' => 'https://www.figma.com/api/mcp/asset/94543d1c-af38-4d9d-a413-406541044c0f',
];

$theme_fallback = 'assets/images/about-mental-health-grid/overview.png';

$hero_image_id = matrix_seed_resolve_image(
    $figma['hero'],
    'media-queries-hero-2780-3711',
    'Media Queries hero',
    $theme_fallback
);

$responsible_reporting_image_id = matrix_seed_resolve_image(
    $figma['responsible_reporting'],
    'media-queries-responsible-reporting-2780-3749',
    'Responsible Reporting',
    $theme_fallback
);

$portrait_ids = [
    matrix_seed_resolve_image($figma['portrait_a'], 'media-queries-portrait-a', 'Spokesperson portrait A'),
    matrix_seed_resolve_image($figma['portrait_b'], 'media-queries-portrait-b', 'Spokesperson portrait B'),
    matrix_seed_resolve_image($figma['portrait_c'], 'media-queries-portrait-c', 'Spokesperson portrait C'),
];

$spokespeople_term_id = matrix_seed_ensure_team_category('spokespeople', 'Spokespeople');

$profile_teaser = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua</p>';

$spokespersons = [
    [
        'title' => 'John Creedon',
        'job_title' => 'Director of Nursing',
        'seed_key' => 'media-queries-spokesperson-john-creedon',
        'image_index' => 0,
    ],
    [
        'title' => 'Paul Gilligan',
        'job_title' => 'CEO',
        'seed_key' => 'media-queries-spokesperson-paul-gilligan',
        'image_index' => 1,
    ],
    [
        'title' => 'Professor Paul Fearon',
        'job_title' => 'Medical Director',
        'seed_key' => 'media-queries-spokesperson-paul-fearon',
        'image_index' => 2,
    ],
    [
        'title' => 'David OBrien',
        'job_title' => 'Head of Communications',
        'seed_key' => 'media-queries-spokesperson-david-obrien',
        'image_index' => 1,
    ],
    [
        'title' => 'Sarah Nolan',
        'job_title' => 'Clinical Programme Lead',
        'seed_key' => 'media-queries-spokesperson-sarah-nolan',
        'image_index' => 0,
    ],
];

foreach ($spokespersons as $spokesperson) {
    $image_id = $portrait_ids[$spokesperson['image_index']] ?? 0;

    matrix_seed_ensure_spokesperson(
        $spokesperson['title'],
        $spokesperson['job_title'],
        $profile_teaser,
        $image_id,
        $spokesperson['seed_key'],
        $spokespeople_term_id
    );
}

if ($spokespeople_term_id > 0) {
    $current_spokespeople_count = (int) (new WP_Query([
        'post_type' => 'team_members',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'tax_query' => [
            [
                'taxonomy' => 'team_member_category',
                'field' => 'term_id',
                'terms' => [$spokespeople_term_id],
            ],
        ],
    ]))->found_posts;

    $placeholder_index = 1;

    while ($current_spokespeople_count < 9) {
        while (get_posts([
            'post_type' => 'team_members',
            'post_status' => 'any',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key' => '_matrix_seed_key',
                    'value' => 'media-queries-spokesperson-placeholder-' . $placeholder_index,
                ],
            ],
        ]) !== []) {
            $placeholder_index++;
        }

        matrix_seed_ensure_spokesperson(
            'Team member name',
            'Job title',
            $profile_teaser,
            $portrait_ids[$placeholder_index % 3] ?? 0,
            'media-queries-spokesperson-placeholder-' . $placeholder_index,
            $spokespeople_term_id
        );

        $placeholder_index++;
        $current_spokespeople_count = (int) (new WP_Query([
            'post_type' => 'team_members',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'tax_query' => [
                [
                    'taxonomy' => 'team_member_category',
                    'field' => 'term_id',
                    'terms' => [$spokespeople_term_id],
                ],
            ],
        ]))->found_posts;
    }
}

$duplicate_seed_posts = get_posts([
    'post_type' => 'team_members',
    'post_status' => 'any',
    'posts_per_page' => -1,
    'meta_query' => [
        [
            'key' => '_matrix_seed_key',
            'value' => 'media-queries-spokesperson-',
            'compare' => 'LIKE',
        ],
    ],
]);

foreach ($duplicate_seed_posts as $duplicate_post) {
    if ($duplicate_post->post_title === 'Team member name') {
        continue;
    }

    $duplicate_id = (int) $duplicate_post->ID;
    $canonical_id = matrix_seed_find_team_member_by_title($duplicate_post->post_title);

    if ($canonical_id > 0 && $canonical_id !== $duplicate_id) {
        wp_delete_post($duplicate_id, true);
    }
}

$lorem_hero = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo.';

$hero_content = sprintf(
    '<p>%s</p><p><a class="btn inline-flex min-h-[36px] items-center justify-center rounded-[6px] bg-[#024B79] px-3 py-2 text-[14px] font-medium leading-[24px] text-white no-underline" href="%s">Contact Us</a></p>',
    esc_html($lorem_hero),
    esc_url($contact_url)
);

$intro_paragraph = '<p><strong>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut wisi enim ad minim veniam.</strong></p>';
$body_paragraph = '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat.</p>';

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

$news_term = get_term_by('slug', 'news', 'category');
$news_term_id = $news_term instanceof WP_Term ? (int) $news_term->term_id : 0;

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
                    'title' => 'About Us',
                    'url' => $about_us_url,
                    'target' => '',
                ],
            ],
        ],
        'current_crumb_label' => 'Media Queries',
        'heading_tag' => 'h1',
        'heading' => 'Media Queries',
        'content' => $hero_content,
        'hero_image' => $hero_image_id,
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
    ],
    [
        'acf_fc_layout' => 'team_members',
        'heading' => 'Spokespeople',
        'heading_tag' => 'h2',
        'layout_style' => 'spokespeople_grid',
        'source_mode' => 'category',
        'selected_team_categories' => $spokespeople_term_id > 0 ? [$spokespeople_term_id] : [],
        'posts_per_page' => 9,
        'section_background' => '#FFFFFF',
        'spokespeople_card_background_color' => '#FBFAF7',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'latest_posts',
        'heading' => 'Recent Press Releases',
        'heading_tag' => 'h2',
        'selected_categories' => $news_term_id > 0 ? [$news_term_id] : [],
        'header_button_link' => [
            'title' => 'View all press releases',
            'url' => $news_url,
            'target' => '',
        ],
        'empty_state_message' => 'No press releases are available yet.',
        'background_color' => '#FBFAF7',
        'heading_color' => '#1E244B',
        'card_title_color' => '#1E244B',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Responsible Reporting',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'intro_text' => $intro_paragraph,
        'content' => $body_paragraph,
        'layout_style' => 'image_left',
        'background_type' => 'gradient',
        'background_gradient' => 'linear-gradient(-70.18deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
        'image' => $responsible_reporting_image_id,
        'padding_settings' => $section_padding,
    ],
];

update_field('hero_content_blocks', [], $post_id);
update_field('flexible_content_blocks', $flexi_rows, $post_id);

$saved_rows = get_field('flexible_content_blocks', $post_id);
$saved_count = is_array($saved_rows) ? count($saved_rows) : 0;

$spokespeople_count = $spokespeople_term_id > 0
    ? (int) (new WP_Query([
        'post_type' => 'team_members',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'tax_query' => [
            [
                'taxonomy' => 'team_member_category',
                'field' => 'term_id',
                'terms' => [$spokespeople_term_id],
            ],
        ],
    ]))->found_posts
    : 0;

if (class_exists('WP_CLI')) {
    if ($saved_count === count($flexi_rows)) {
        WP_CLI::success(sprintf(
            'Seeded Media Queries page (%d) with %d flexi blocks and %d spokespeople.',
            $post_id,
            $saved_count,
            $spokespeople_count
        ));
    } else {
        WP_CLI::warning(sprintf(
            'Updated page %d but expected %d blocks, found %d. Spokespeople count: %d.',
            $post_id,
            count($flexi_rows),
            $saved_count,
            $spokespeople_count
        ));
    }
}
