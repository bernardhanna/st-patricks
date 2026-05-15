<?php

/**
 * Seed About Us > Our Team (page 231) to match Figma frame 2780:3567.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-about-us-our-team.php
 */

$post_id = (int) (get_page_by_path('about-us/our-team')?->ID ?? 0);

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at about-us/our-team.');
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

$home = home_url('/');
$about_us_url = home_url('/about-us/');

$figma = [
    'hero' => 'https://www.figma.com/api/mcp/asset/a187f6c2-9652-42a8-b4c1-4cefa078d7a1',
    'team_a' => 'https://www.figma.com/api/mcp/asset/be051c00-b83e-4deb-9f8e-7173b0b2bbf8',
    'team_b' => 'https://www.figma.com/api/mcp/asset/31743bfd-2d61-4d6b-9b0a-78351693abd3',
    'team_c' => 'https://www.figma.com/api/mcp/asset/ce014dc4-090f-4092-8eac-40a367e4aa4a',
];

$hero_image_id = matrix_seed_resolve_image(
    $figma['hero'],
    'our-team-hero-2780-3567',
    'Our Team hero',
);

$team_image_ids = [
    matrix_seed_resolve_image($figma['team_a'], 'our-team-card-a', 'Team member portrait A'),
    matrix_seed_resolve_image($figma['team_b'], 'our-team-card-b', 'Team member portrait B'),
    matrix_seed_resolve_image($figma['team_c'], 'our-team-card-c', 'Team member portrait C'),
];

$card_description = '<p>Consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam.</p>';

$multidisciplinary_cards = [
    [
        'title' => "Psychiatrists (consultants and registrars)",
        'description' => $card_description,
        'card_tone' => 'teal',
        'link' => ['title' => 'Psychiatrists', 'url' => $home . 'inpatient-care/', 'target' => ''],
    ],
    [
        'title' => 'Social Workers',
        'description' => $card_description,
        'card_tone' => 'lavender',
        'link' => ['title' => 'Social Workers', 'url' => $home . 'about-us/our-team/', 'target' => ''],
    ],
    [
        'title' => 'Nurses',
        'description' => $card_description,
        'card_tone' => 'green',
        'link' => ['title' => 'Nurses', 'url' => $home . 'about-us/our-team/', 'target' => ''],
    ],
    [
        'title' => 'Occupational Therapists',
        'description' => $card_description,
        'card_tone' => 'pink',
        'link' => ['title' => 'Occupational Therapists', 'url' => $home . 'about-us/our-team/', 'target' => ''],
    ],
    [
        'title' => 'Clinical Psychologists',
        'description' => $card_description,
        'card_tone' => 'yellow',
        'link' => ['title' => 'Clinical Psychologists', 'url' => $home . 'about-us/our-team/', 'target' => ''],
    ],
    [
        'title' => 'Pharmacists',
        'description' => $card_description,
        'card_tone' => 'coral',
        'link' => ['title' => 'Pharmacists', 'url' => $home . 'about-us/our-team/', 'target' => ''],
    ],
];

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

if (! function_exists('matrix_seed_ensure_team_member')) {
    function matrix_seed_ensure_team_member(string $title, string $job_title, int $image_id, array $term_ids, string $seed_key): int
    {
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
            $post_id = (int) $existing[0]->ID;
            wp_update_post([
                'ID' => $post_id,
                'post_title' => $title,
                'post_status' => 'publish',
            ]);
        } else {
            $post_id = wp_insert_post([
                'post_type' => 'team_members',
                'post_status' => 'publish',
                'post_title' => $title,
            ]);

            if (is_wp_error($post_id) || ! $post_id) {
                return 0;
            }

            update_post_meta((int) $post_id, '_matrix_seed_key', $seed_key);
        }

        update_field('job_title', $job_title, $post_id);

        if ($image_id > 0) {
            set_post_thumbnail($post_id, $image_id);
        }

        if ($term_ids !== []) {
            wp_set_object_terms($post_id, array_map('intval', $term_ids), 'team_member_category', false);
        }

        return (int) $post_id;
    }
}

$leadership_term_id = matrix_seed_ensure_team_category('leadership', 'Leadership');
$board_term_id = matrix_seed_ensure_team_category('board-members', 'Board Members');

$existing_leadership_count = $leadership_term_id > 0
    ? (int) (new WP_Query([
        'post_type' => 'team_members',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'tax_query' => [
            [
                'taxonomy' => 'team_member_category',
                'field' => 'term_id',
                'terms' => [$leadership_term_id],
            ],
        ],
        'meta_query' => [
            [
                'key' => '_matrix_seed_key',
                'compare' => 'NOT EXISTS',
            ],
        ],
    ]))->found_posts
    : 0;

$senior_target = 9;
$senior_placeholders_needed = max(0, $senior_target - $existing_leadership_count);

for ($index = 1; $index <= $senior_placeholders_needed; $index++) {
    $image_id = $team_image_ids[($index - 1) % count($team_image_ids)] ?? 0;
    matrix_seed_ensure_team_member(
        'Team member name',
        'Job title',
        $image_id,
        $leadership_term_id > 0 ? [$leadership_term_id] : [],
        'our-team-senior-placeholder-' . $index
    );
}

for ($index = 1; $index <= 12; $index++) {
    $image_id = $team_image_ids[($index - 1) % count($team_image_ids)] ?? 0;
    matrix_seed_ensure_team_member(
        'Team member name',
        'Job title',
        $image_id,
        $board_term_id > 0 ? [$board_term_id] : [],
        'our-team-board-placeholder-' . $index
    );
}

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
        'current_crumb_label' => 'Our team',
        'heading_tag' => 'h1',
        'heading' => 'Our Team',
        'content' => '<p>We believe a collaborative approach is essential, and place huge importance on links and partnerships with other organisations sharing our objectives, both nationally and internationally.</p>',
        'hero_image' => $hero_image_id,
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
    ],
    [
        'acf_fc_layout' => 'multidisciplinary_team_grid',
        'heading_tag' => 'h2',
        'heading' => 'Our Multidisciplinary Teams',
        'intro' => '<p>Introductory text explaining what a Multidisciplinary Team is - 4 lines max invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Introductory text explaining what a Multidisciplinary Team is - 4 lines max invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores.</p>',
        'cards' => $multidisciplinary_cards,
        'background_color' => '#FFFFFF',
        'padding_settings' => [
            [
                'screen_size' => 'lg',
                'padding_top' => 6.25,
                'padding_bottom' => 6.25,
            ],
        ],
    ],
    [
        'acf_fc_layout' => 'team_members',
        'heading_tag' => 'h2',
        'heading' => 'Our Senior Management Team',
        'intro' => '<p>We offer inpatient care in three approved centres:</p>',
        'layout_style' => 'standard_grid',
        'source_mode' => 'category',
        'selected_team_categories' => $leadership_term_id > 0 ? [$leadership_term_id] : [],
        'posts_per_page' => 9,
        'section_background' => '#FBFAF7',
        'card_background_color' => '#FFFFFF',
        'padding_settings' => [
            [
                'screen_size' => 'lg',
                'padding_top' => 6.25,
                'padding_bottom' => 6.25,
            ],
        ],
    ],
    [
        'acf_fc_layout' => 'team_members',
        'heading_tag' => 'h2',
        'heading' => 'Our Board Members',
        'intro' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit</p>',
        'layout_style' => 'standard_grid',
        'source_mode' => 'category',
        'selected_team_categories' => $board_term_id > 0 ? [$board_term_id] : [],
        'posts_per_page' => 12,
        'section_background' => 'linear-gradient(-84.09615582680794deg, rgb(248, 246, 243) 3.2353%, rgb(245, 246, 237) 90.882%)',
        'card_background_color' => '#FFFFFF',
        'padding_settings' => [
            [
                'screen_size' => 'lg',
                'padding_top' => 6.25,
                'padding_bottom' => 6.25,
            ],
        ],
    ],
];

update_field('hero_content_blocks', [], $post_id);
update_field('flexible_content_blocks', $flexi_rows, $post_id);

$saved_rows = get_field('flexible_content_blocks', $post_id);
$saved_count = is_array($saved_rows) ? count($saved_rows) : 0;

if ($saved_count !== count($flexi_rows)) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error(
            'Failed to update Our Team page ' . $post_id
            . ' (expected ' . count($flexi_rows) . ' blocks, found ' . $saved_count . ')'
        );
    }

    exit(1);
}

$message = 'Seeded Our Team (page ' . $post_id . ') with ' . count($flexi_rows) . ' flexi blocks matching Figma 2780:3567.';

if (class_exists('WP_CLI')) {
    WP_CLI::success($message);
}

echo $message . "\n";
