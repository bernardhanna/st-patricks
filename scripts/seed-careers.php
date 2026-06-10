<?php

/**
 * Seed Careers landing page (Figma 3279:18763) at /careers/.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-careers.php
 */

$post_id = (int) (get_page_by_path('careers')?->ID ?? 0);

if ($post_id === 0) {
    $post_id = (int) (get_page_by_path('about-us/careers')?->ID ?? 0);
}

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find Careers page (tried careers and about-us/careers).');
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

if (! function_exists('matrix_seed_attachment_url')) {
    function matrix_seed_attachment_url(int $attachment_id): string
    {
        if ($attachment_id <= 0) {
            return '';
        }

        $url = wp_get_attachment_url($attachment_id);

        return is_string($url) ? $url : '';
    }
}

if (! function_exists('matrix_seed_ensure_career_term')) {
    function matrix_seed_ensure_career_term(string $taxonomy, string $name, string $slug): int
    {
        $existing = get_term_by('slug', $slug, $taxonomy);
        if ($existing instanceof WP_Term) {
            return (int) $existing->term_id;
        }

        $created = wp_insert_term($name, $taxonomy, ['slug' => $slug]);
        if (is_wp_error($created)) {
            if (class_exists('WP_CLI')) {
                WP_CLI::warning('Could not create term ' . $taxonomy . ':' . $slug . ' - ' . $created->get_error_message());
            }

            return 0;
        }

        return (int) ($created['term_id'] ?? 0);
    }
}

if (! function_exists('matrix_seed_careers_vacancies')) {
    function matrix_seed_careers_vacancies(): void
    {
        $department_admin = matrix_seed_ensure_career_term('career_department', 'Administration', 'administration');
        $department_clinical = matrix_seed_ensure_career_term('career_department', 'Clinical', 'clinical');
        $location_dublin = matrix_seed_ensure_career_term('career_location', 'Dublin', 'dublin');
        $location_lucan = matrix_seed_ensure_career_term('career_location', 'Lucan', 'lucan');

        $vacancies = [
            ['title' => 'Receptionist / Admin Support', 'area' => 'Dean Clinic', 'department' => $department_admin, 'location' => $location_dublin],
            ['title' => 'Clinical Nurse Manager', 'area' => 'St Patrick' . chr(39) . 's University Hospital (SPUH)', 'department' => $department_clinical, 'location' => $location_dublin],
            ['title' => 'Occupational Therapist', 'area' => 'St Patrick' . chr(39) . 's Hospital Lucan', 'department' => $department_clinical, 'location' => $location_lucan],
            ['title' => 'Healthcare Assistant', 'area' => 'Willow Grove Adolescent Unit', 'department' => $department_clinical, 'location' => $location_dublin],
            ['title' => 'Psychologist', 'area' => 'Dean Clinic', 'department' => $department_clinical, 'location' => $location_dublin],
            ['title' => 'Medical Secretary', 'area' => 'Dean Clinic', 'department' => $department_admin, 'location' => $location_lucan],
            ['title' => 'Social Worker', 'area' => 'St Patrick' . chr(39) . 's University Hospital (SPUH)', 'department' => $department_clinical, 'location' => $location_dublin],
            ['title' => 'Pharmacy Technician', 'area' => 'St Patrick' . chr(39) . 's Hospital Lucan', 'department' => $department_clinical, 'location' => $location_lucan],
            ['title' => 'Facilities Coordinator', 'area' => 'Willow Grove Adolescent Unit', 'department' => $department_admin, 'location' => $location_dublin],
            ['title' => 'Speech and Language Therapist', 'area' => 'Dean Clinic', 'department' => $department_clinical, 'location' => $location_dublin],
            ['title' => 'HR Administrator', 'area' => 'St Patrick' . chr(39) . 's University Hospital (SPUH)', 'department' => $department_admin, 'location' => $location_dublin],
            ['title' => 'Dietitian', 'area' => 'St Patrick' . chr(39) . 's Hospital Lucan', 'department' => $department_clinical, 'location' => $location_lucan],
        ];

        foreach ($vacancies as $index => $vacancy) {
            $slug = sanitize_title($vacancy['title'] . '-' . ($index + 1));
            $existing = get_page_by_path($slug, OBJECT, 'careers');
            if ($existing instanceof WP_Post) {
                continue;
            }

            $career_id = wp_insert_post([
                'post_type' => 'careers',
                'post_status' => 'publish',
                'post_title' => $vacancy['title'],
                'post_name' => $slug,
                'post_content' => '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>',
                'post_excerpt' => 'Join our team in a rewarding healthcare role.',
            ], true);

            if (is_wp_error($career_id)) {
                if (class_exists('WP_CLI')) {
                    WP_CLI::warning('Could not create career post: ' . $career_id->get_error_message());
                }
                continue;
            }

            update_field('career_area', $vacancy['area'], $career_id);

            if ($vacancy['department'] > 0) {
                wp_set_object_terms($career_id, [(int) $vacancy['department']], 'career_department', false);
            }

            if ($vacancy['location'] > 0) {
                wp_set_object_terms($career_id, [(int) $vacancy['location']], 'career_location', false);
            }
        }
    }
}

if (! function_exists('matrix_seed_update_careers_menu_links')) {
    function matrix_seed_update_careers_menu_links(string $careers_url): void
    {
        $menu_id = (int) (get_nav_menu_locations()['primary'] ?? 0);
        if ($menu_id === 0) {
            return;
        }

        foreach (wp_get_nav_menu_items($menu_id) ?: [] as $item) {
            if (! $item instanceof WP_Post) {
                continue;
            }

            $title = trim((string) $item->title);
            $url = trim((string) $item->url);

            if ($title === 'Careers' && str_contains($url, 'careers')) {
                update_post_meta((int) $item->ID, '_menu_item_url', esc_url_raw($careers_url));
            }
        }
    }
}

wp_update_post([
    'ID' => $post_id,
    'post_name' => 'careers',
    'post_parent' => 0,
    'post_title' => 'Careers',
]);

matrix_seed_careers_vacancies();

$home = home_url('/');
$about_us_url = home_url('/about-us/');
$careers_url = home_url('/careers/');
$vacancies_anchor = $careers_url . '#current-vacancies';

$figma = [
    'hero' => 'https://www.figma.com/api/mcp/asset/8f34e1cd-6aaf-4f96-a6ba-9979b1198d3e',
    'why_work' => 'https://www.figma.com/api/mcp/asset/ec45dcce-af00-4b9c-92a3-c7afec9b69e5',
    'staff_offer' => 'https://www.figma.com/api/mcp/asset/4990c1f6-d667-4e14-bf83-a60fa8ee1366',
    'link_1' => 'https://www.figma.com/api/mcp/asset/8bdee9da-b82c-4bf4-b0fb-e595f8f8985e',
    'link_2' => 'https://www.figma.com/api/mcp/asset/23e84be8-8656-4074-add9-c88f221a779a',
    'link_3' => 'https://www.figma.com/api/mcp/asset/71a01689-a8a2-4260-937b-5c54d88c14b7',
];

$hero_image_id = matrix_seed_resolve_image($figma['hero'], 'careers-hero-3279-18763', 'Careers hero');
$why_work_image_id = matrix_seed_resolve_image($figma['why_work'], 'careers-why-work-3279-18763', 'Why Work With Us');
$staff_offer_image_id = matrix_seed_resolve_image($figma['staff_offer'], 'careers-staff-offer-3279-18763', 'What We Offer Our Staff');
$link_image_ids = [
    matrix_seed_resolve_image($figma['link_1'], 'careers-link-card-1-3279-18763', 'Careers useful link card 1'),
    matrix_seed_resolve_image($figma['link_2'], 'careers-link-card-2-3279-18763', 'Careers useful link card 2'),
    matrix_seed_resolve_image($figma['link_3'], 'careers-link-card-3-3279-18763', 'Careers useful link card 3'),
];

$hero_intro = 'Join a team dedicated to mental health recovery and wellbeing. Explore current vacancies, learn what we offer our staff, and find useful resources for applicants.';
$why_work_intro = '<p><strong>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</strong></p>';
$why_work_body = '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>';
$staff_offer_intro = '<p><strong>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</strong></p>';
$staff_offer_body = '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>';
$faq_intro = '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.</p>';
$accordion_open_body = '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat m dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor inc <strong>consectetur adipisicing</strong>.</p>';

$section_padding = [
    ['screen_size' => 'mob', 'padding_top' => '3', 'padding_bottom' => '3'],
    ['screen_size' => 'lg', 'padding_top' => '6.25', 'padding_bottom' => '6.25'],
];

$useful_link_cards = [
    [
        'title' => 'Recruitment and Useful Information',
        'url' => home_url('/recruitment-and-useful-information/'),
        'image_id' => $link_image_ids[0],
        'tone' => 'bg1',
    ],
    [
        'title' => 'Attending an interview',
        'url' => home_url('/attending-an-interview/'),
        'image_id' => $link_image_ids[1],
        'tone' => 'bg2',
    ],
    [
        'title' => 'Staff Wellbeing',
        'url' => home_url('/recruitment-and-useful-information/staff-wellbeing/'),
        'image_id' => $link_image_ids[2],
        'tone' => 'bg3',
    ],
    [
        'title' => 'How to get work experience',
        'url' => home_url('/recruitment-and-useful-information/how-to-get-work-experience/'),
        'image_id' => $link_image_ids[0],
        'tone' => 'bg4',
    ],
    [
        'title' => 'How to apply for a role',
        'url' => home_url('/recruitment-and-useful-information/how-to-apply-for-a-role/'),
        'image_id' => $link_image_ids[1],
        'tone' => 'bg1',
    ],
    [
        'title' => 'About SPMHS',
        'url' => $about_us_url,
        'image_id' => $link_image_ids[2],
        'tone' => 'bg2',
    ],
];

$about_links = [];
foreach ($useful_link_cards as $card) {
    $about_links[] = [
        'icon' => '',
        'image_url' => matrix_seed_attachment_url((int) $card['image_id']),
        'title' => $card['title'],
        'description' => '',
        'link' => [
            'title' => $card['title'],
            'url' => $card['url'],
            'target' => '',
        ],
        'card_tone' => $card['tone'],
    ];
}

$flexi_rows = [
    [
        'acf_fc_layout' => 'hero_with_breadcrumbs',
        'layout_style' => 'image_split',
        'show_breadcrumbs' => 1,
        'breadcrumb_source' => 'manual',
        'manual_breadcrumbs' => [
            ['breadcrumb_link' => ['title' => 'Home', 'url' => $home, 'target' => '']],
            ['breadcrumb_link' => ['title' => 'Who we are', 'url' => $about_us_url, 'target' => '']],
        ],
        'current_crumb_label' => 'Careers',
        'heading_tag' => 'h1',
        'heading' => 'Careers',
        'content' => '<p>' . esc_html($hero_intro) . '</p>',
        'primary_button' => [
            'title' => 'View Current Vacancies',
            'url' => $vacancies_anchor,
            'target' => '',
        ],
        'hero_image' => $hero_image_id,
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Why Work With Us',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'intro_text' => $why_work_intro,
        'content' => $why_work_body,
        'image' => $why_work_image_id,
        'layout_style' => 'image_left',
        'background_type' => 'color',
        'background_color' => '#FFFFFF',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'What We Offer Our Staff',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'intro_text' => $staff_offer_intro,
        'content' => $staff_offer_body,
        'image' => $staff_offer_image_id,
        'layout_style' => 'image_right',
        'background_type' => 'color',
        'background_color' => '#FBFAF7',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'careers_archive',
        'heading' => 'Current Vacancies',
        'heading_tag' => 'h2',
        'filter_label' => 'Filter by:',
        'department_placeholder' => 'Department',
        'location_placeholder' => 'Location',
        'apply_filters_label' => 'Apply filters',
        'search_placeholder' => 'Search vacancies',
        'search_button_label' => 'Search',
        'view_detail_label' => 'View detail',
        'posts_per_page' => 10,
        'empty_state_message' => 'No vacancies matched your filters.',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'about_links_grid',
        'heading_tag' => 'h2',
        'heading_text' => 'Useful links (all placeholder/suggestions)',
        'intro_text' => '',
        'links' => $about_links,
        'bg_color' => '#E9E2F7',
        'heading_color' => '#1E244B',
        'intro_color' => '#4A4B37',
        'columns' => '3',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Frequently Asked Questions',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'intro_text' => $faq_intro,
        'content' => '',
        'image' => '',
        'layout_style' => 'image_left',
        'background_type' => 'color',
        'background_color' => '#FBFAF7',
        'padding_settings' => [
            ['screen_size' => 'mob', 'padding_top' => '3', 'padding_bottom' => '1'],
            ['screen_size' => 'lg', 'padding_top' => '6.25', 'padding_bottom' => '1'],
        ],
    ],
    [
        'acf_fc_layout' => 'content_accordion',
        'layout_style' => 'default',
        'section_background' => '#FBFAF7',
        'panel_background' => '#FFFFFF',
        'open_panel_background' => 'linear-gradient(-42.77deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
        'items' => [
            matrix_seed_accordion_item('Lorem ipsum dolor sit amet lorem consectetur.', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>'),
            matrix_seed_accordion_item('Lorem ipsum dolor sit amet consectetur.', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>'),
            matrix_seed_accordion_item('Lorem ipsum sit amet consectetur.', $accordion_open_body, true),
            matrix_seed_accordion_item('Lorem ipsum dolor sit amet lorem consectetur.', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>'),
            matrix_seed_accordion_item('Sit amet lorem consectetur.', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>'),
            matrix_seed_accordion_item('Lorem ipsum dolor sit amet consectetur.', '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>'),
        ],
        'padding_settings' => [
            ['screen_size' => 'mob', 'padding_top' => '1', 'padding_bottom' => '3'],
            ['screen_size' => 'lg', 'padding_top' => '1', 'padding_bottom' => '6.25'],
        ],
    ],
];

update_field('hero_content_blocks', [], $post_id);
update_field('flexible_content_blocks', $flexi_rows, $post_id);

matrix_seed_update_careers_menu_links($careers_url);

flush_rewrite_rules(false);

$saved_rows = get_field('flexible_content_blocks', $post_id);
$saved_count = is_array($saved_rows) ? count($saved_rows) : 0;

if (class_exists('WP_CLI')) {
    if ($saved_count === count($flexi_rows)) {
        WP_CLI::success(sprintf(
            'Seeded Careers page (%d) at /careers/ with %d flexi blocks and vacancy posts.',
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
