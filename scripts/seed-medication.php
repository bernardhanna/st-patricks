<?php

/**
 * Seed Medication page (Figma 3279:19636) with production copy from stpatricks.ie.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-medication.php
 */

require_once __DIR__ . '/lib/page-seed-conventions.php';
require_once get_template_directory() . '/inc/migrate-functions.php';

$post_id = matrix_seed_resolve_page_id_by_path('service-users-and-visitors/medication');

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at service-users-and-visitors/medication.');
    }

    exit(1);
}

if (! function_exists('matrix_seed_import_scraped_image')) {
    function matrix_seed_import_scraped_image(string $url, string $title, string $cache_key): int
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
                    'key' => '_matrix_seed_scraped_key',
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
        $filename = $path ? basename((string) $path) : 'scraped-image.jpg';

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

        update_post_meta($attachment_id, '_matrix_seed_scraped_key', $cache_key);
        update_post_meta($attachment_id, '_matrix_seed_scraped_url', $url);

        return (int) $attachment_id;
    }
}

if (! function_exists('matrix_seed_import_figma_image')) {
    function matrix_seed_import_figma_image(string $url, string $title, string $cache_key): int
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

        return matrix_seed_import_scraped_image($url, $title, 'figma-' . $cache_key);
    }
}

if (! function_exists('matrix_seed_med_attachment_id')) {
    function matrix_seed_med_attachment_id(string $source_path, string $fallback_url, string $cache_key, string $title): int
    {
        $attachment_id = matrix_migrate_attachment_id_for_source_path($source_path);

        if ($attachment_id > 0) {
            return $attachment_id;
        }

        return matrix_seed_import_scraped_image($fallback_url, $title, $cache_key);
    }
}

if (! function_exists('matrix_seed_med_attachment_url')) {
    function matrix_seed_med_attachment_url(string $source_path): string
    {
        $attachment_id = matrix_migrate_attachment_id_for_source_path($source_path);

        if ($attachment_id > 0) {
            $url = wp_get_attachment_url($attachment_id);

            if (is_string($url) && $url !== '') {
                return $url;
            }
        }

        $path = str_starts_with($source_path, '/') ? $source_path : '/' . $source_path;

        if (! str_contains($path, '?')) {
            $path .= '?width=400&height=218&mode=crop';
        }

        return 'https://www.stpatricks.ie' . $path;
    }
}

if (! function_exists('matrix_seed_med_grid_card')) {
    /**
     * @return array<string, mixed>
     */
    function matrix_seed_med_grid_card(string $title, string $image_path, string $url, string $tone = 'bg1'): array
    {
        return [
            'icon' => '',
            'image_url' => matrix_seed_med_attachment_url($image_path),
            'title' => $title,
            'description' => '',
            'link' => [
                'title' => $title,
                'url' => $url,
                'target' => '',
            ],
            'card_tone' => $tone,
        ];
    }
}

if (! function_exists('matrix_seed_med_post_url')) {
    function matrix_seed_med_post_url(string $slug): string
    {
        $post = get_page_by_path($slug, OBJECT, ['post', 'page']) ?: get_page_by_path($slug);

        if ($post instanceof WP_Post) {
            return get_permalink($post) ?: home_url('/' . trim($slug, '/') . '/');
        }

        return home_url('/' . trim($slug, '/') . '/');
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
                    'row_type' => 'text',
                    'icon_key' => '',
                    'icon' => '',
                    'content' => $content,
                ],
            ],
        ];
    }
}

$home = home_url('/');
$service_users_url = home_url('/service-users-and-visitors/');
$mdt_url = home_url('/about-us/multidisciplinary-teams/');
$maternal_url = matrix_seed_med_post_url('looking-after-maternal-mental-health');
$choice_url = 'http://www.choiceandmedication.org/spuh/';
$prevent_url = 'https://www.hse.ie/eng/about/who/cspd/ncps/medicines-management/patient-information/patient-guide-for-women-and-girls.pdf';
$medicines_ie_url = 'https://www.medicines.ie/medicines/epilim-chrono-300mg-prolonged-release-tablets-32031/educational-material-patient#tabs';
$hpra_url = 'https://www.hpra.ie/homepage/medicines/medicines-information/find-a-medicine/results/item?pano=PA0540/150/010&t=Epilim%20Chrono%20200mg%20Prolonged%20Release%20Tablets';
$learning_hub_url = home_url('/getting-help/learning-resource-hub/');
$lithium_url = home_url('/service-users-and-visitors/medication/lithium-learning-module/');
$newsletter_url = home_url('/service-users-and-visitors/medication/medication-safety-newsletter/');
$medication_cravings_url = home_url('/getting-help/learning-resource-hub/2018/february/medication-cravings/');
$benzo_url = matrix_seed_med_post_url('coming-off-benzodiazepine-or-z-drugs');
$pharmacy_url = home_url('/about-us/staff-directory/pharmacy/');
$faqs_url = home_url('/service-users-and-visitors/frequently-asked-questions-faqs/');

$medication_choices_url = matrix_seed_med_post_url('mental-health-medication-choices');
$what_to_expect_url = matrix_seed_med_post_url('what-to-expect-from-mental-health-medication');
$side_effects_url = matrix_seed_med_post_url('side-effects-of-medication');

$cravings_pdf_url = 'https://www.stpatricks.ie/media/1590/spmhs-meds-and-cravings.pdf';

$figma = [
    'video_poster' => 'https://www.figma.com/api/mcp/asset/d62b785b-08eb-4596-a2ce-320033009b27',
    'image_left' => 'https://www.figma.com/api/mcp/asset/be27514d-8a2e-426a-9991-9a803365b88b',
    'image_right' => 'https://www.figma.com/api/mcp/asset/d37a2333-262f-4977-a9e3-c1653b65e163',
];

$hero_intro = 'You may be recommended to take medication to manage your mental health symptoms and support your journey of recovery.';

$cravings_body = '<p>It can be difficult to control your weight, especially if you are on medication which can increase your appetite. Some medication can cause you to be thirsty or have a false appetite. These feelings are called cravings.</p>'
    . '<p>If you eat too many sugary or fatty foods, or drink too many sugary drinks to satisfy these cravings, you will gain weight. Instead of filling up on these foods and drinks, choose from the list below. Over time the cravings will disappear.</p>';

$mdt_intro = '<p><strong>Always ask a nurse, doctor or pharmacist – all of whom are members of your MDT – if you have any questions or queries about your medication.</strong></p>';

$mdt_body = '<p>At St Patrick\'s Mental Health Services, we are committed to giving you the information you need to help you make informed choices about your care and treatment. Your <a href="' . esc_url($mdt_url) . '">multidisciplinary team</a> (MDT) will share this information with you, including about your medication.</p>'
    . '<p>Your MDT will ensure that medication you are prescribed is the most suited and effective for you. Your medication may change over time as different circumstances can impact what is appropriate for you. For example, you may need to adapt your medication to allow for other treatments you have been prescribed; to avoid allergies or reactions; or to be suitable when <a href="' . esc_url($maternal_url) . '">planning or during pregnancy</a>.</p>'
    . '<p>If you would like to know more about the medication you are taking or to understand other medication options available to you, the independent <a href="' . esc_url($choice_url) . '" target="_blank" rel="noopener noreferrer">Choice and Medication website</a> provides clear, updated information on all the medication we use in your care.</p>';

$valproate_body = '<p>It is important to be aware that the medication valproate (brand name Epilim®) can have a harmful effect on an unborn baby. The <a href="' . esc_url($prevent_url) . '" target="_blank" rel="noopener noreferrer">Prevent programme</a> aims to reduce these risks, and, if you are being prescribed medication containing valproate, your MDT will talk you through everything you need to know. If you are a woman of childbearing potential or if you are planning or going through pregnancy, you can find out more information about valproate from <a href="' . esc_url($medicines_ie_url) . '" target="_blank" rel="noopener noreferrer">Medicines.ie</a>, the <a href="' . esc_url($hpra_url) . '" target="_blank" rel="noopener noreferrer">Health Products Regulatory Authority</a> or <a href="' . esc_url($choice_url) . '" target="_blank" rel="noopener noreferrer">Choice and Medication</a>.</p>';

$supports_intro = '<p><a href="' . esc_url($learning_hub_url) . '">Visit our Learning and Resource Hub</a> for brochures on your mental health and treatment.</p>';

$benzo_body = '<p>Guidance for people who are reducing or stopping benzodiazepine or Z-drug medication, including practical tips and what to expect during the process.</p>';

$hero_image_id = matrix_seed_med_attachment_id(
    '/media/1770/st-patricks-mental-health-services-mh-medication.jpg',
    'https://www.stpatricks.ie/media/1770/st-patricks-mental-health-services-mh-medication.jpg',
    'medication-scraped-hero',
    'Medication'
);

$cravings_image_id = matrix_seed_med_attachment_id(
    '/media/1617/medication-cravings.png',
    'https://www.stpatricks.ie/media/1617/medication-cravings.png',
    'medication-cravings-image',
    'Medication and Cravings'
);

$benzo_image_id = matrix_seed_med_attachment_id(
    '/media/1601/coming-off-benzodiazepines-or-z-drugs.png',
    'https://www.stpatricks.ie/media/1601/coming-off-benzodiazepines-or-z-drugs.png',
    'medication-benzo-image',
    'Coming off Benzodiazepine or Z Drugs'
);

$video_poster_id = matrix_seed_import_figma_image(
    $figma['video_poster'],
    'Pregnancy and Valproate video poster',
    'medication-video-3279-19636'
);

$figma_image_left_id = matrix_seed_import_figma_image(
    $figma['image_left'],
    'Medication section image',
    'medication-image-left-3279-19636'
);

$figma_image_right_id = matrix_seed_import_figma_image(
    $figma['image_right'],
    'Medication section image two',
    'medication-image-right-3279-19636'
);

$cravings_pdf_link = [
    'title' => 'Medication & Cravings (324.3 kB)',
    'url' => $cravings_pdf_url,
    'target' => '_blank',
];

$safety_series_cards = [
    matrix_seed_med_grid_card(
        'Making decisions about your mental health medication',
        '/media/3643/starting-medication.png',
        $medication_choices_url,
        'bg1'
    ),
    matrix_seed_med_grid_card(
        'What to expect from mental health medication',
        '/media/3665/types-of-medication.png',
        $what_to_expect_url,
        'bg2'
    ),
    matrix_seed_med_grid_card(
        'Managing the side effects of medication',
        '/media/3666/medicines.png',
        $side_effects_url,
        'bg3'
    ),
];

$pharmacy_cards = [
    matrix_seed_med_grid_card(
        'Pharmacy',
        '/media/1727/st-patricks-mental-health-services-staff-min.jpg',
        $pharmacy_url,
        'bg3'
    ),
];

$flexi_rows = matrix_page_seed_strip_padding_from_rows([
    [
        'acf_fc_layout' => 'hero_with_breadcrumbs',
        'layout_style' => 'image_split',
        'show_breadcrumbs' => 1,
        'breadcrumb_source' => 'manual',
        'manual_breadcrumbs' => [
            ['breadcrumb_link' => ['title' => 'Home', 'url' => $home, 'target' => '']],
            ['breadcrumb_link' => ['title' => 'Service Users and Visitors', 'url' => $service_users_url, 'target' => '']],
        ],
        'current_crumb_label' => 'Medication',
        'heading_tag' => matrix_page_seed_heading(1),
        'heading' => 'Medication',
        'content' => '<p>' . esc_html($hero_intro) . '</p>',
        'primary_button' => '',
        'hero_image' => $hero_image_id,
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Medication & Cravings',
        'heading_tag' => matrix_page_seed_heading(2),
        'accent_position' => 'below_heading',
        'intro_text' => '',
        'content' => $cravings_body,
        'document_link' => $cravings_pdf_link,
        'image' => $cravings_image_id,
        'layout_style' => 'image_left',
        'background_type' => 'color',
        'background_color' => '#FFFFFF',
    ],
    [
        'acf_fc_layout' => 'video_showcase',
        'heading_tag' => matrix_page_seed_heading(2),
        'heading' => 'Pregnancy and Valproate: Prevent programme',
        'intro' => $valproate_body,
        'layout_style' => 'feature_single',
        'slides' => [
            [
                'poster_image' => $video_poster_id,
                'video_source_type' => 'embed_url',
                'video_embed_url' => 'https://www.youtube.com/watch?v=9fTMEZW03lg',
                'caption' => '',
                'cta_link' => '',
            ],
        ],
        'section_background' => 'linear-gradient(-76.52deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => '',
        'heading_tag' => matrix_page_seed_heading(2),
        'accent_position' => 'below_heading',
        'intro_text' => $mdt_intro,
        'content' => $mdt_body,
        'image' => '',
        'layout_style' => 'image_left',
        'column_layout' => 'one_column',
        'text_width' => 'wide',
        'background_type' => 'color',
        'background_color' => '#FFFFFF',
    ],
    [
        'acf_fc_layout' => 'about_links_grid',
        'heading_tag' => matrix_page_seed_heading(2),
        'heading_text' => 'See our medication safety series',
        'intro_text' => '<p>Get insights from our Pharmacy Department on how to make decisions about your mental health medication, what to expect from your medication, and what you should be aware of around medication safety.</p>',
        'layout_style' => 'image_feature',
        'links' => $safety_series_cards,
        'bg_color' => '#FFFFFF',
        'heading_color' => '#1E244B',
        'intro_color' => '#08284B',
        'columns' => '3',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'More information and supports',
        'heading_tag' => matrix_page_seed_heading(2),
        'accent_position' => 'below_heading',
        'intro_text' => $supports_intro,
        'content' => '<p>Our brochures explain how to manage cravings linked to medication and offer practical tips for healthier choices.</p>',
        'document_link' => $cravings_pdf_link,
        'image' => $figma_image_left_id > 0 ? $figma_image_left_id : $cravings_image_id,
        'layout_style' => 'image_left',
        'background_type' => 'color',
        'background_color' => '#FBF8F3',
        'primary_button' => [
            'title' => 'Medication & Cravings',
            'url' => $medication_cravings_url,
            'target' => '',
        ],
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Coming off Benzodiazepine or \'Z\' Drugs',
        'heading_tag' => matrix_page_seed_heading(2),
        'accent_position' => 'below_heading',
        'intro_text' => '',
        'content' => $benzo_body,
        'image' => $figma_image_right_id > 0 ? $figma_image_right_id : $benzo_image_id,
        'layout_style' => 'image_right',
        'background_type' => 'color',
        'background_color' => '#FFFFFF',
        'primary_button' => [
            'title' => 'Read the brochure',
            'url' => $benzo_url,
            'target' => '',
        ],
    ],
    [
        'acf_fc_layout' => 'video_showcase',
        'heading_tag' => matrix_page_seed_heading(2),
        'heading' => '',
        'intro' => '',
        'layout_style' => 'feature_single',
        'slides' => [
            [
                'poster_image' => '',
                'video_source_type' => 'embed_url',
                'video_embed_url' => 'https://www.youtube.com/watch?v=BEEt6th8RbA',
                'caption' => '',
                'cta_link' => '',
            ],
        ],
        'section_background' => '#FFFFFF',
    ],
    [
        'acf_fc_layout' => 'about_links_grid',
        'heading_tag' => matrix_page_seed_heading(2),
        'heading_text' => 'Meet our pharmacy team',
        'intro_text' => '',
        'layout_style' => 'image_feature',
        'links' => $pharmacy_cards,
        'bg_color' => '#FBFAF7',
        'heading_color' => '#1E244B',
        'intro_color' => '#08284B',
        'columns' => '1',
    ],
    [
        'acf_fc_layout' => 'useful_links',
        'heading_tag' => matrix_page_seed_heading(2),
        'heading' => 'In this section',
        'variant' => 'flexi',
        'links' => [
            ['link' => ['title' => 'Medication', 'url' => home_url('/service-users-and-visitors/medication/'), 'target' => '']],
            ['link' => ['title' => 'Lithium Learning Module', 'url' => $lithium_url, 'target' => '']],
            ['link' => ['title' => 'Medication Safety Newsletter', 'url' => $newsletter_url, 'target' => '']],
        ],
        'background_color' => '#E9E2F7',
        'heading_color' => '#1E244B',
        'link_color' => '#1E244B',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Frequently Asked Questions',
        'heading_tag' => matrix_page_seed_heading(2),
        'accent_position' => 'below_heading',
        'intro_text' => '',
        'content' => '',
        'image' => '',
        'layout_style' => 'image_left',
        'background_type' => 'color',
        'background_color' => '#FBFAF7',
    ],
    [
        'acf_fc_layout' => 'content_accordion',
        'layout_style' => 'default',
        'section_background' => '#FBFAF7',
        'panel_background' => '#FFFFFF',
        'open_panel_background' => 'linear-gradient(-42.77deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
        'items' => [
            matrix_seed_accordion_item(
                'Who can I ask about my medication?',
                '<p>Always ask a nurse, doctor or pharmacist – all of whom are members of your multidisciplinary team (MDT) – if you have any questions or queries about your medication.</p>'
            ),
            matrix_seed_accordion_item(
                'Where can I find independent information about my medication?',
                '<p>The <a href="' . esc_url($choice_url) . '" target="_blank" rel="noopener noreferrer">Choice and Medication website</a> provides clear, updated information on all the medication we use in your care.</p>'
            ),
            matrix_seed_accordion_item(
                'What should I know about valproate and pregnancy?',
                $valproate_body,
                true
            ),
            matrix_seed_accordion_item(
                'Where can I find more brochures and supports?',
                '<p><a href="' . esc_url($learning_hub_url) . '">Visit our Learning and Resource Hub</a> for brochures on your mental health and treatment, including guidance on medication and cravings.</p>'
            ),
        ],
    ],
    [
        'acf_fc_layout' => 'content_cta',
        'heading_tag' => matrix_page_seed_heading(2),
        'heading' => 'Queries',
        'body' => '<p>For general queries, please call us. For more on mental health and our services, see our frequently asked questions (FAQs).</p><p><strong>01 249 3200</strong></p>',
        'button_link' => [
            'title' => 'See our FAQs',
            'url' => $faqs_url,
            'target' => '',
        ],
        'background_type' => 'color',
        'background_color' => '#C6ECF4',
    ],
]);

update_post_meta($post_id, '_matrix_migrate_old_path', '/care-treatment/medication');
update_field('hero_content_blocks', [], $post_id);
update_field('flexible_content_blocks', $flexi_rows, $post_id);

$saved_rows = get_field('flexible_content_blocks', $post_id);
$saved_count = is_array($saved_rows) ? count($saved_rows) : 0;

if (class_exists('WP_CLI')) {
    if ($saved_count === count($flexi_rows)) {
        WP_CLI::success(sprintf(
            'Seeded Medication page (%d) with %d flexi blocks (Figma 3279:19636 layout + production copy).',
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
