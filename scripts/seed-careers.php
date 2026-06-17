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

if (! function_exists('matrix_seed_preserve_careers_page_media')) {
    function matrix_seed_preserve_careers_page_media(int $post_id, array $flexi_rows): array
    {
        $existing_rows = get_field('flexible_content_blocks', $post_id);
        if (! is_array($existing_rows) || $existing_rows === []) {
            return $flexi_rows;
        }

        foreach ($flexi_rows as $index => &$row) {
            $existing_row = $existing_rows[$index] ?? null;
            if (! is_array($existing_row) || ($row['acf_fc_layout'] ?? '') !== ($existing_row['acf_fc_layout'] ?? '')) {
                continue;
            }

            $layout = $row['acf_fc_layout'];

            if ($layout === 'hero_with_breadcrumbs' && ! empty($existing_row['hero_image'])) {
                $row['hero_image'] = $existing_row['hero_image'];
            }

            if ($layout === 'content' && ! empty($existing_row['image'])) {
                $row['image'] = $existing_row['image'];
            }

            if ($layout === 'about_links_grid' && is_array($existing_row['links'] ?? null) && is_array($row['links'] ?? null)) {
                foreach ($row['links'] as $link_index => &$link) {
                    $existing_link = $existing_row['links'][$link_index] ?? null;
                    if (! is_array($existing_link) || ($existing_link['image_url'] ?? '') === '') {
                        continue;
                    }

                    $link['image_url'] = $existing_link['image_url'];
                }
                unset($link);
            }
        }
        unset($row);

        return $flexi_rows;
    }
}

if (! function_exists('matrix_seed_careers_vacancies')) {
    function matrix_seed_careers_vacancies(): void
    {
        $department_admin = matrix_seed_ensure_career_term('career_department', 'Administration', 'administration');
        $department_clinical = matrix_seed_ensure_career_term('career_department', 'Clinical', 'clinical');
        $location_dublin = matrix_seed_ensure_career_term('career_location', 'Dublin', 'dublin');
        $location_lucan = matrix_seed_ensure_career_term('career_location', 'Lucan', 'lucan');

        $real_vacancy_content = '<p>SPMHS is the largest independent, not-for-profit mental health service provider in Ireland, offering fantastic job opportunities in psychiatric nursing. We are hiring registered psychiatric nurses for both adult and adolescent services.</p><p>Whether you are a recent nursing graduate, returning to work after a career break, or simply looking for the next step forward in your nursing career, we welcome your application.</p>';

        $vacancies = [
            [
                'title' => 'Staff Nurse (Psychiatric) – Adult & Adolescent Services',
                'area' => 'St Patrick' . chr(39) . 's University Hospital (SPUH)',
                'department' => $department_clinical,
                'location' => $location_dublin,
                'job_type' => 'Permanent Full-Time',
                'category' => 'Nursing',
                'excerpt' => 'We are hiring registered psychiatric nurses for both adult and adolescent services.',
                'content' => $real_vacancy_content,
                'update_existing' => true,
            ],
            ['title' => 'Clinical Nurse Manager', 'area' => 'St Patrick' . chr(39) . 's University Hospital (SPUH)', 'department' => $department_clinical, 'location' => $location_dublin, 'job_type' => 'Permanent Full-Time', 'category' => 'Nursing'],
            ['title' => 'Occupational Therapist', 'area' => 'St Patrick' . chr(39) . 's Hospital Lucan', 'department' => $department_clinical, 'location' => $location_lucan, 'job_type' => 'Permanent Full-Time', 'category' => 'Health & Social Care'],
            ['title' => 'Healthcare Assistant', 'area' => 'Willow Grove Adolescent Unit', 'department' => $department_clinical, 'location' => $location_dublin, 'job_type' => 'Permanent Full-Time', 'category' => 'Healthcare Assistant'],
            ['title' => 'Psychologist', 'area' => 'Dean Clinic', 'department' => $department_clinical, 'location' => $location_dublin, 'job_type' => 'Permanent Full-Time', 'category' => 'Psychology'],
            ['title' => 'Medical Secretary', 'area' => 'Dean Clinic', 'department' => $department_admin, 'location' => $location_lucan, 'job_type' => 'Permanent Full-Time', 'category' => 'Administration'],
            ['title' => 'Social Worker', 'area' => 'St Patrick' . chr(39) . 's University Hospital (SPUH)', 'department' => $department_clinical, 'location' => $location_dublin, 'job_type' => 'Permanent Full-Time', 'category' => 'Health & Social Care'],
            ['title' => 'Pharmacy Technician', 'area' => 'St Patrick' . chr(39) . 's Hospital Lucan', 'department' => $department_clinical, 'location' => $location_lucan, 'job_type' => 'Permanent Full-Time', 'category' => 'Pharmacy'],
            ['title' => 'Facilities Coordinator', 'area' => 'Willow Grove Adolescent Unit', 'department' => $department_admin, 'location' => $location_dublin, 'job_type' => 'Permanent Full-Time', 'category' => 'Facilities'],
            ['title' => 'Speech and Language Therapist', 'area' => 'Dean Clinic', 'department' => $department_clinical, 'location' => $location_dublin, 'job_type' => 'Permanent Full-Time', 'category' => 'Health & Social Care'],
            ['title' => 'HR Administrator', 'area' => 'St Patrick' . chr(39) . 's University Hospital (SPUH)', 'department' => $department_admin, 'location' => $location_dublin, 'job_type' => 'Permanent Full-Time', 'category' => 'Administration'],
            ['title' => 'Dietitian', 'area' => 'St Patrick' . chr(39) . 's Hospital Lucan', 'department' => $department_clinical, 'location' => $location_lucan, 'job_type' => 'Permanent Full-Time', 'category' => 'Health & Social Care'],
        ];

        $placeholder_content = '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>';
        $placeholder_excerpt = 'Join our team in a rewarding healthcare role.';

        foreach ($vacancies as $index => $vacancy) {
            $slug = sanitize_title($vacancy['title'] . '-' . ($index + 1));
            $existing = get_page_by_path($slug, OBJECT, 'careers');

            if ($index === 0 && ! ($existing instanceof WP_Post)) {
                $legacy = get_posts([
                    'post_type' => 'careers',
                    'post_status' => 'publish',
                    'posts_per_page' => 1,
                    'orderby' => 'ID',
                    'order' => 'ASC',
                ]);
                $existing = $legacy[0] ?? null;
            }

            if ($existing instanceof WP_Post && ! empty($vacancy['update_existing'])) {
                wp_update_post([
                    'ID' => (int) $existing->ID,
                    'post_title' => $vacancy['title'],
                    'post_name' => sanitize_title($vacancy['title']),
                    'post_content' => $vacancy['content'] ?? $placeholder_content,
                    'post_excerpt' => $vacancy['excerpt'] ?? $placeholder_excerpt,
                ]);
                $career_id = (int) $existing->ID;
            } elseif ($existing instanceof WP_Post) {
                continue;
            } else {
                $career_id = wp_insert_post([
                    'post_type' => 'careers',
                    'post_status' => 'publish',
                    'post_title' => $vacancy['title'],
                    'post_name' => $slug,
                    'post_content' => $vacancy['content'] ?? $placeholder_content,
                    'post_excerpt' => $vacancy['excerpt'] ?? $placeholder_excerpt,
                ], true);

                if (is_wp_error($career_id)) {
                    if (class_exists('WP_CLI')) {
                        WP_CLI::warning('Could not create career post: ' . $career_id->get_error_message());
                    }
                    continue;
                }
            }

            update_field('career_area', $vacancy['area'], $career_id);
            update_field('career_job_type', (string) ($vacancy['job_type'] ?? ''), $career_id);
            update_field('career_category', (string) ($vacancy['category'] ?? ''), $career_id);

            if ($vacancy['department'] > 0) {
                wp_set_object_terms($career_id, [(int) $vacancy['department']], 'career_department', false);
            }

            if ($vacancy['location'] > 0) {
                wp_set_object_terms($career_id, [(int) $vacancy['location']], 'career_location', false);
            }

            if (! empty($vacancy['update_existing']) && function_exists('matrix_get_staff_nurse_job_description_html')) {
                update_field('career_job_description', matrix_get_staff_nurse_job_description_html(), $career_id);
                update_field('career_show_application_form', 1, $career_id);
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

$hero_intro = 'Here at St Patrick\'s Mental Health Services (SPMHS), our team works towards a society where everyone is empowered and given the opportunity to live mentally healthy lives. Our staff work across a wide variety of roles, both clinical and non-clinical.';
$why_work_intro = '<p><strong>Here at St Patrick\'s Mental Health Services (SPMHS), our team works towards a society where everyone is empowered and given the opportunity to live mentally healthy lives.</strong></p>';
$why_work_body = '<p>Our staff work across a wide variety of roles, both clinical and non-clinical.</p><p>If you are interested in working in an exciting, forward-looking environment and being at the forefront of mental healthcare, we would love to hear from you. See our latest vacancies below or <a href="mailto:hr@stpatricks.ie">email your CV to hr@stpatricks.ie</a>.</p>';
$staff_offer_intro = '<p><strong>Our staff make everything we do possible.</strong></p>';
$staff_offer_body = '<p>Our mission is to make a positive difference in the care of people experiencing mental health difficulties. We are looking for dedicated and motivated people who share our vision and can help us to achieve this goal.</p><p>We are committed to building and growing an innovative workplace where all staff are empowered and encouraged to reach their full potential. We are an equal opportunities employer.</p>';
$faq_intro = '<p>Find answers to common questions about working with us, applying for roles, and arranging placements or work experience.</p>';
$faq_apply_body = '<p>If you would like to work with us, you can see our latest vacancies on this page. If a role or area you are interested in is not currently advertised, you can email your cover letter and CV to our Human Resources (HR) Department at <a href="mailto:hr@stpatricks.ie">hr@stpatricks.ie</a>.</p><p>Please get in touch with the HR Department if you have any questions or need any help with your application: email hr@stpatricks.ie or <a href="tel:012493435">call 01 249 3435</a>. We cannot accept paper applications; all applications must be made online.</p>';
$faq_placement_body = '<p>Honorary and elective placements are usually arranged by people seeking this work experience directly with the department concerned. The documentation needed to confirm the placement is provided by Human Resources. If you would like to apply for a placement, please <a href="mailto:hr@stpatricks.ie">email your request to hr@stpatricks.ie</a>, from where it will be forwarded on to the relevant department.</p><p>Please note that these placements do not establish a relationship of employment between the person and SPMHS. We cannot offer standard employment benefits, including remuneration, to honorary or elective contracts.</p><p>We also welcome volunteers in a number of areas of our organisation. If you are interested in a volunteer role, please email hr@stpatricks.ie.</p>';

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
        'url' => home_url('/careers/attending-an-interview/'),
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
        'heading_text' => 'Useful links',
        'intro_text' => '',
        'layout_style' => 'flush_image',
        'links' => $about_links,
        'bg_color' => '#E9E2F7',
        'heading_color' => '#1E244B',
        'intro_color' => '#4A4B37',
        'card_bg_color' => '#F1F8F9',
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
            matrix_seed_accordion_item(
                'Why join St Patrick\'s Mental Health Services?',
                '<p>Our staff is our most important asset: working across diverse clinical and non-clinical roles, they support people towards mental health recovery every day.</p><p>In turn, we work to create a positive, dynamic and rewarding workplace where our staff are supported to continually learn, expand their skills, progress their careers, and enjoy satisfaction in what they do. By joining us, you can be part of a team that, with over 250 years experience, is proud of its rich history, but is always driving innovation and looking to an exciting future.</p>'
            ),
            matrix_seed_accordion_item(
                'What do we offer our staff?',
                '<p>As Ireland\'s largest independent, not-for-profit mental health service provider, we have a strong reputation for delivering high quality mental healthcare. We are continuing to grow and expand, offering many opportunities for collaborative working and career advancement.</p><p>We provide excellent pay and remuneration, including a generous contributory pension scheme and free Employee Assistance Programme. We take career development seriously, offering internal and external training, research opportunities, funding for further education, paid study leave, and opportunities for promotion and career progression.</p>'
            ),
            matrix_seed_accordion_item(
                'What wellbeing support is available?',
                '<p>We were the first hospital and first healthcare organisation in Ireland to be awarded the IBEC KeepWell Mark in 2018, in recognition of our workplace wellbeing, and have received this award every year since.</p><p>We offer an active Staff Wellbeing Committee, flexible working arrangements, opportunities for remote or hybrid working where appropriate, a subsidised canteen and onsite gym, central locations, Bike to Work and TaxSaver Commuter Ticket schemes, and an award-winning menopause workplace programme.</p>',
                true
            ),
            matrix_seed_accordion_item(
                'Who are we looking for?',
                '<p>We are always welcoming of applications from dedicated, proactive people who enjoy being part of an inclusive, progressive team and share our vision of empowering people towards mentally healthy living.</p><p>Our team works across a wide variety of clinical and non-clinical areas, with our roles spanning from entry-level to leadership and management positions.</p>'
            ),
            matrix_seed_accordion_item(
                'How do I apply for a role?',
                $faq_apply_body
            ),
            matrix_seed_accordion_item(
                'How can I arrange a work placement or work experience?',
                $faq_placement_body
            ),
        ],
        'padding_settings' => [
            ['screen_size' => 'mob', 'padding_top' => '1', 'padding_bottom' => '3'],
            ['screen_size' => 'lg', 'padding_top' => '1', 'padding_bottom' => '6.25'],
        ],
    ],
];

$flexi_rows = matrix_seed_preserve_careers_page_media($post_id, $flexi_rows);

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
