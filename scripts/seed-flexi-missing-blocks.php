<?php

/**
 * Add any flexi blocks missing from the /flexi/ showcase page (ID 329).
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-flexi-missing-blocks.php
 */

$post_id = 329;

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

if (! function_exists('matrix_seed_flexi_fallback_image_id')) {
    function matrix_seed_flexi_fallback_image_id(): int
    {
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

if (! function_exists('matrix_seed_flexi_available_layouts')) {
    function matrix_seed_flexi_available_layouts(): array
    {
        $files = glob(get_template_directory() . '/template-parts/flexi/*.php') ?: [];

        return array_map(static fn ($file) => basename($file, '.php'), $files);
    }
}

$image_id = matrix_seed_flexi_fallback_image_id();
$home = home_url('/');

$section_padding = [
    ['screen_size' => 'mob', 'padding_top' => '3', 'padding_bottom' => '3'],
    ['screen_size' => 'lg', 'padding_top' => '6.25', 'padding_bottom' => '6.25'],
];

$lorem = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.</p>';

$rows = get_field('flexible_content_blocks', $post_id);
if (! is_array($rows)) {
    $rows = [];
}

$present = [];
foreach ($rows as $row) {
    $layout = $row['acf_fc_layout'] ?? '';
    if ($layout !== '') {
        $present[$layout] = true;
    }
}

$missing_layouts = array_values(array_filter(
    matrix_seed_flexi_available_layouts(),
    static fn ($layout) => ! isset($present[$layout])
));

if ($missing_layouts === []) {
    if (class_exists('WP_CLI')) {
        WP_CLI::success('Flexi page already includes all ' . count(matrix_seed_flexi_available_layouts()) . ' block layouts.');
    }

    return;
}

$demo_blocks = [
    'about' => [
        'acf_fc_layout' => 'about',
        'faded_logo' => matrix_seed_build_image_field($image_id, 'SPMHS logo watermark'),
        'heading_tag' => 'h2',
        'heading_text' => 'About us',
        'description' => $lorem,
        'image_left' => [
            'image' => matrix_seed_build_image_field($image_id, 'Team photo left'),
            'overlay_logo' => '',
            'rotate_deg' => -6,
        ],
        'image_right' => [
            'image' => matrix_seed_build_image_field($image_id, 'Team photo right'),
            'overlay_logo' => '',
        ],
        'key_points' => [
            [
                'watermark' => '',
                'value' => 95,
                'suffix' => '%',
                'title' => 'Patient satisfaction',
                'text' => 'Lorem ipsum dolor sit amet sed do eiusmod tempor incididunt',
            ],
            [
                'watermark' => '',
                'value' => 75,
                'suffix' => 'k',
                'title' => 'People supported',
                'text' => 'Lorem ipsum dolor sit amet sed do eiusmod tempor incididunt',
            ],
            [
                'watermark' => '',
                'value' => 455,
                'suffix' => '',
                'title' => 'Staff members',
                'text' => 'Lorem ipsum dolor sit amet sed do eiusmod tempor incididunt',
            ],
        ],
        'primary_cta' => ['title' => 'Careers', 'url' => home_url('/careers/'), 'target' => ''],
        'secondary_cta' => ['title' => 'About us', 'url' => home_url('/about-us/'), 'target' => ''],
        'bg_color' => '#ffffff',
        'heading_color' => '#0B0B08',
        'desc_color' => '#4A4B37',
        'divider_color' => '#5F604B',
        'value_color' => '#5F604B',
        'title_color' => '#0B0B08',
        'text_color' => '#4A4B37',
        'buttons_style' => 'solid-dark',
        'padding_settings' => $section_padding,
    ],
    'about_links_grid' => [
        'acf_fc_layout' => 'about_links_grid',
        'heading_tag' => 'h2',
        'heading_text' => 'About us',
        'intro_text' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Explore key sections of our organisation.</p>',
        'links' => [
            [
                'icon' => '',
                'image_url' => wp_get_attachment_url($image_id) ?: '',
                'title' => 'Our History',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                'link' => ['title' => 'Our History', 'url' => home_url('/about-us/our-history/'), 'target' => ''],
                'card_tone' => 'bg1',
            ],
            [
                'icon' => '',
                'image_url' => wp_get_attachment_url($image_id) ?: '',
                'title' => 'Our Team',
                'description' => 'Meet the multidisciplinary teams across our services.',
                'link' => ['title' => 'Our Team', 'url' => home_url('/about-us/our-team/'), 'target' => ''],
                'card_tone' => 'bg2',
            ],
            [
                'icon' => '',
                'image_url' => wp_get_attachment_url($image_id) ?: '',
                'title' => 'Research',
                'description' => 'Discover our research projects and publications.',
                'link' => ['title' => 'Research', 'url' => home_url('/about-us/research/'), 'target' => ''],
                'card_tone' => 'bg3',
            ],
        ],
        'bg_color' => '#F1F8F9',
        'heading_color' => '#0B0B08',
        'intro_color' => '#4A4B37',
        'columns' => '3',
        'padding_settings' => $section_padding,
    ],
    'about_us' => [
        'acf_fc_layout' => 'about_us',
        'heading' => 'About Mental Health',
        'heading_tag' => 'h2',
        'main_image' => matrix_seed_build_image_field($image_id, 'Mental health illustration'),
        'view_more_link' => ['title' => 'View more', 'url' => home_url('/service-users-and-visitors/about-mental-health/'), 'target' => ''],
        'card_1_title' => 'Addiction & Dual Diagnosis',
        'card_1_text' => 'Lorem ipsum dolor sit amet, consectetur sadipscing elitr tempor.',
        'card_1_link' => ['title' => 'Addiction & Dual Diagnosis', 'url' => $home, 'target' => ''],
        'card_2_title' => 'Anxiety',
        'card_2_text' => 'Lorem ipsum dolor sit amet, consectetur sadipscing elitr tempor.',
        'card_2_link' => ['title' => 'Anxiety', 'url' => $home, 'target' => ''],
        'card_3_title' => 'Bipolar Disorder',
        'card_3_text' => 'Lorem ipsum dolor sit amet, consectetur sadipscing elitr tempor.',
        'card_3_link' => ['title' => 'Bipolar Disorder', 'url' => $home, 'target' => ''],
        'card_4_title' => 'Eating Disorders',
        'card_4_text' => 'Lorem ipsum dolor sit amet, consectetur sadipscing elitr tempor.',
        'card_4_link' => ['title' => 'Eating Disorders', 'url' => $home, 'target' => ''],
        'card_5_title' => 'Personality Disorders',
        'card_5_text' => 'Lorem ipsum dolor sit amet, consectetur sadipscing elitr tempor.',
        'card_5_link' => ['title' => 'Personality Disorders', 'url' => $home, 'target' => ''],
        'card_6_title' => 'Schizophrenia & Psychosis',
        'card_6_text' => 'Lorem ipsum dolor sit amet, consectetur sadipscing elitr tempor.',
        'card_6_link' => ['title' => 'Schizophrenia & Psychosis', 'url' => $home, 'target' => ''],
        'background_color' => '#FFFFFF',
        'padding_settings' => $section_padding,
    ],
    'content_two' => [
        'acf_fc_layout' => 'content_two',
        'heading' => 'Stories and Support',
        'heading_tag' => 'h2',
        'description' => $lorem . $lorem,
        'hero_image' => $image_id,
        'button' => ['title' => 'See all stories', 'url' => home_url('/service-users-and-visitors/stories-and-support/'), 'target' => ''],
        'background_color' => '#ffffff',
        'padding_settings' => $section_padding,
    ],
    'counters' => [
        'acf_fc_layout' => 'counters',
        'counter_items' => [
            [
                'value' => 95,
                'suffix' => '%',
                'title' => 'Patient satisfaction',
                'description' => 'Lorem ipsum dolor sit amet, sed do eiusmod tempor incididunt',
            ],
            [
                'value' => 75,
                'suffix' => 'k',
                'title' => 'People supported annually',
                'description' => 'Lorem ipsum dolor sit amet, sed do eiusmod tempor incididunt',
            ],
            [
                'value' => 455,
                'suffix' => '',
                'title' => 'Dedicated staff members',
                'description' => 'Lorem ipsum dolor sit amet, sed do eiusmod tempor incididunt',
            ],
        ],
        'background_color' => '#0c4a6e',
        'padding_settings' => [
            ['screen_size' => 'xxs', 'padding_top' => 5, 'padding_bottom' => 5],
            ['screen_size' => 'lg', 'padding_top' => 8, 'padding_bottom' => 8],
        ],
    ],
    'cta_block' => [
        'acf_fc_layout' => 'cta_block',
        'title' => 'Child Safeguarding',
        'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Learn more about our commitment to safeguarding children and young people.',
        'button' => ['title' => 'Child Safeguarding', 'url' => home_url('/about-us/policies-and-publications/child-safeguarding-statement/'), 'target' => ''],
        'show_icon' => 1,
        'polaroid_image' => matrix_seed_build_image_field($image_id, 'Child safeguarding'),
        'faded_logo' => '',
        'watermark_logo' => '',
        'divider_color' => '#D1D5DB',
        'min_full_screen' => 0,
        'section_classes' => '',
        'padding_settings' => [
            ['screen_size' => '', 'padding_top' => '16', 'padding_bottom' => '25'],
        ],
    ],
    'key_contact_info' => [
        'acf_fc_layout' => 'key_contact_info',
        'columns' => [
            [
                'items' => [
                    [
                        'title' => 'General Enquiries',
                        'starts_open' => 1,
                        'bullet_items' => [
                            ['label' => 'Inpatient care'],
                            ['label' => 'Admissions'],
                            ['label' => 'Pharmacy'],
                        ],
                        'phone' => '01 249 3200',
                        'email' => 'hello@stpatricks.ie',
                    ],
                    [
                        'title' => 'Clinical Governance Office',
                        'starts_open' => 0,
                        'bullet_items' => [
                            ['label' => 'Complaints process'],
                            ['label' => 'Patient feedback'],
                        ],
                        'phone' => '01 249 3200',
                        'email' => 'feedback@stpatricks.ie',
                    ],
                ],
            ],
            [
                'items' => [
                    [
                        'title' => 'Referral and Assessment Service',
                        'starts_open' => 0,
                        'bullet_items' => [['label' => 'Referrals']],
                        'phone' => '01 249 3635',
                        'email' => 'referrals@stpatricks.ie',
                    ],
                ],
            ],
            [
                'items' => [
                    [
                        'title' => 'Human Resources',
                        'starts_open' => 0,
                        'bullet_items' => [['label' => 'Careers']],
                        'phone' => '01 249 3200',
                        'email' => 'careers@stpatricks.ie',
                    ],
                ],
            ],
        ],
        'section_background' => '#FFFFFF',
        'closed_panel_background' => '#FBFAF7',
        'open_panel_background' => 'linear-gradient(-79.46deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
        'padding_settings' => $section_padding,
    ],
    'partners' => [
        'acf_fc_layout' => 'partners',
        'heading_tag' => 'h2',
        'heading_text' => 'Committed to quality care, human rights, and innovation',
        'partners' => array_map(
            static fn ($index) => [
                'logo' => matrix_seed_build_image_field($image_id, 'Partner logo ' . ($index + 1)),
                'link' => '',
            ],
            range(0, 4)
        ),
        'background_color' => '#FFFFFF',
        'heading_color' => '#1E244B',
        'show_card_style' => 0,
        'padding_settings' => $section_padding,
    ],
    'related_cards' => [
        'acf_fc_layout' => 'related_cards',
        'heading_tag' => 'h2',
        'heading' => 'Related',
        'intro_text' => 'Explore related programmes and services.',
        'cards' => [
            [
                'image' => $image_id,
                'title' => 'Anxiety Disorders Programme',
                'description' => 'Assessment, treatment and aftercare for anxiety disorders.',
                'link' => [
                    'title' => 'Learn more',
                    'url' => home_url('/programmes-therapies/'),
                    'target' => '',
                ],
            ],
            [
                'image' => $image_id,
                'title' => 'Inpatient Care',
                'description' => 'Specialist inpatient mental health care across our approved centres.',
                'link' => [
                    'title' => 'Learn more',
                    'url' => home_url('/inpatient-care/'),
                    'target' => '',
                ],
            ],
            [
                'image' => $image_id,
                'title' => 'Frequently Asked Questions',
                'description' => 'Answers to common questions about our services.',
                'link' => [
                    'title' => 'Learn more',
                    'url' => home_url('/service-users-and-visitors/frequently-asked-questions-faqs/'),
                    'target' => '',
                ],
            ],
        ],
        'background_color' => '#FFFFFF',
        'columns' => '3',
        'padding_settings' => $section_padding,
    ],
    'research_project_archive' => array_merge(
        function_exists('matrix_get_research_project_archive_defaults')
            ? matrix_get_research_project_archive_defaults()
            : [],
        [
            'acf_fc_layout' => 'research_project_archive',
            'heading_tag' => 'h2',
            'heading' => 'Research Projects',
            'posts_per_page' => 12,
            'padding_settings' => $section_padding,
        ]
    ),
    'services' => [
        'acf_fc_layout' => 'services',
        'heading_tag' => 'h2',
        'heading_text' => 'Our Services',
        'cards' => [
            [
                'link' => ['title' => 'Inpatient Care', 'url' => home_url('/inpatient-care/'), 'target' => ''],
                'title' => 'Inpatient Care',
                'image' => matrix_seed_build_image_field($image_id, 'Inpatient care service'),
                'watermark' => '',
            ],
            [
                'link' => ['title' => 'Outpatient Care', 'url' => home_url('/what-we-offer/outpatient-care-dean-clinics/'), 'target' => ''],
                'title' => 'Outpatient Care',
                'image' => matrix_seed_build_image_field($image_id, 'Outpatient care service'),
                'watermark' => '',
            ],
            [
                'link' => ['title' => 'Day Programmes', 'url' => home_url('/what-we-offer/day-programmes/'), 'target' => ''],
                'title' => 'Day Programmes',
                'image' => matrix_seed_build_image_field($image_id, 'Day programmes service'),
                'watermark' => '',
            ],
        ],
        'view_more_button' => ['title' => 'View all services', 'url' => home_url('/what-we-offer/'), 'target' => ''],
        'background_color' => '#ffffff',
        'heading_color' => '#2C2C21',
        'title_color' => '#4A4B37',
        'watermark_opacity' => 0.12,
        'card_radius' => 'rounded-none',
        'card_shadow' => 1,
        'md_columns' => '2',
        'lg_columns' => '3',
        'xl_columns' => '3',
        'padding_settings' => $section_padding,
    ],
    'stories' => [
        'acf_fc_layout' => 'stories',
        'posts_per_slide' => 4,
        'max_posts' => 12,
        'show_date' => 1,
        'show_excerpt' => 0,
        'card_background_color' => '#fafaf9',
        'divider_color' => '#6FC9C0',
        'text_color' => '#0f2419',
        'date_color' => '#0f2419',
        'padding_settings' => $section_padding,
    ],
    'story_slider' => [
        'acf_fc_layout' => 'story_slider',
        'show_heading' => 1,
        'heading_tag' => 'h2',
        'heading_text' => 'Voices of hope and healing',
        'intro_text' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Real stories from our community.</p>',
        'slides' => [
            [
                'image' => $image_id,
                'has_video' => 0,
                'video_source_type' => 'youtube_vimeo',
                'video_embed_url' => '',
                'local_video_file' => '',
                'video_link' => '',
            ],
            [
                'image' => $image_id,
                'has_video' => 1,
                'video_source_type' => 'youtube_vimeo',
                'video_embed_url' => 'https://www.youtube.com/watch?v=ysz5S6PUM-U',
                'local_video_file' => '',
                'video_link' => '',
            ],
            [
                'image' => $image_id,
                'has_video' => 0,
                'video_source_type' => 'youtube_vimeo',
                'video_embed_url' => '',
                'local_video_file' => '',
                'video_link' => '',
            ],
        ],
        'bg_from' => '#F6EDE0',
        'bg_via' => '#F5F0E0',
        'bg_to' => '#F4F5DE',
        'bg_image' => '',
        'overlay_opacity' => '0.1',
        'accent_bar_color' => '#F97316',
        'quote_stroke_color' => '#ffffff',
        'nav_border_color' => '#93C5FD',
        'dot_active_color' => '#0A2540',
        'dot_inactive_color' => '#3B82F6',
        'card_radius' => 'rounded-md',
        'padding_settings' => $section_padding,
    ],
    'wysiwyg' => [
        'acf_fc_layout' => 'wysiwyg',
        'text_content' => '<h2>General content section</h2>'
            . '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>'
            . '<ul><li>First bullet point with lorem ipsum text</li><li>Second bullet point with supporting detail</li><li>Third bullet point linking to <a href="' . esc_url(home_url('/contact-us/')) . '">contact us</a></li></ul>',
        'padding_settings' => $section_padding,
    ],
];

$added = 0;

foreach ($missing_layouts as $layout) {
    if (! isset($demo_blocks[$layout])) {
        if (class_exists('WP_CLI')) {
            WP_CLI::warning('No demo data defined for layout: ' . $layout);
        }

        continue;
    }

    $rows[] = $demo_blocks[$layout];
    $added++;
}

$updated = update_field('flexible_content_blocks', $rows, $post_id);

if (! $updated) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Failed to update flexible content for page ' . $post_id);
    }

    exit(1);
}

$final_rows = get_field('flexible_content_blocks', $post_id) ?: [];
$final_layouts = array_unique(array_map(static fn ($row) => $row['acf_fc_layout'] ?? '', $final_rows));
$all_layouts = matrix_seed_flexi_available_layouts();
$still_missing = array_diff($all_layouts, $final_layouts);

if (class_exists('WP_CLI')) {
    if ($still_missing === []) {
        WP_CLI::success(sprintf(
            'Added %d missing flexi blocks to page %d. Page now has %d blocks covering all %d layouts.',
            $added,
            $post_id,
            count($final_rows),
            count($all_layouts)
        ));
    } else {
        WP_CLI::warning(sprintf(
            'Added %d blocks but %d layouts are still missing: %s',
            $added,
            count($still_missing),
            implode(', ', $still_missing)
        ));
    }
}
