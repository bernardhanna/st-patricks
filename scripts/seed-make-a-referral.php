<?php

/**
 * Seed the "Make a referral" page.
 *
 * Content based on:
 * - https://www.stpatricks.ie/gps-referrals/referrals-admissions
 *
 * Includes an About-Us-style links grid (about_links_grid) that links to every
 * published Referrals CPT post.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-make-a-referral.php
 */

require_once __DIR__ . '/lib/page-seed-conventions.php';

if (! function_exists('matrix_seed_make_a_referral_ensure_page')) {
    function matrix_seed_make_a_referral_ensure_page(string $slug, string $title): int
    {
        $page_id = matrix_seed_resolve_page_id_by_path($slug);

        if ($page_id > 0) {
            wp_update_post([
                'ID' => $page_id,
                'post_title' => $title,
                'post_status' => 'publish',
            ]);

            return $page_id;
        }

        $inserted = wp_insert_post([
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_parent' => 0,
            'post_name' => $slug,
            'post_title' => $title,
        ], true);

        return is_wp_error($inserted) ? 0 : (int) $inserted;
    }
}

if (! function_exists('matrix_seed_make_a_referral_grid_row')) {
    /**
     * Build the About-Links-Grid flexi row that links to the Referrals CPT posts.
     *
     * @param array<int, array<string, mixed>> $links
     * @param array<int, array<string, string>> $padding
     * @return array<string, mixed>
     */
    function matrix_seed_make_a_referral_grid_row(array $links, array $padding): array
    {
        return [
            'acf_fc_layout' => 'about_links_grid',
            'heading_tag' => 'h2',
            'heading_text' => 'Explore our referral pathways',
            'intro_text' => 'Find detailed guidance for each of our referral and admissions pathways.',
            'links' => $links,
            'bg_color' => '#F1F8F9',
            'heading_color' => '#1E244B',
            'intro_color' => '#08284B',
            'columns' => '3',
            'layout_style' => 'image_feature',
            'padding_settings' => $padding,
        ];
    }
}

if (! function_exists('matrix_seed_make_a_referral_inject_landing_grid')) {
    /**
     * Add (or refresh) the referrals grid on the /referrals/ landing page.
     *
     * @param array<int, array<string, mixed>> $links
     * @param array<int, array<string, string>> $padding
     */
    function matrix_seed_make_a_referral_inject_landing_grid(array $links, array $padding): int
    {
        $landing_id = matrix_seed_resolve_page_id_by_path('referrals');

        if ($landing_id === 0) {
            return 0;
        }

        $rows = get_field('flexible_content_blocks', $landing_id);

        if (! is_array($rows)) {
            $rows = [];
        }

        $grid_row = matrix_seed_make_a_referral_grid_row($links, $padding);

        $existing_index = null;

        foreach ($rows as $index => $row) {
            if (is_array($row) && ($row['acf_fc_layout'] ?? '') === 'about_links_grid') {
                $existing_index = $index;
                break;
            }
        }

        if ($existing_index !== null) {
            $rows[$existing_index] = $grid_row;
        } else {
            $cta_index = null;

            foreach ($rows as $index => $row) {
                if (is_array($row) && ($row['acf_fc_layout'] ?? '') === 'content_cta') {
                    $cta_index = $index;
                    break;
                }
            }

            if ($cta_index !== null) {
                array_splice($rows, $cta_index, 0, [$grid_row]);
            } else {
                $rows[] = $grid_row;
            }
        }

        update_field('flexible_content_blocks', $rows, $landing_id);

        return $landing_id;
    }
}

if (! function_exists('matrix_seed_make_a_referral_point_navbar_button')) {
    /**
     * Ensure the navbar "Make a referral" CTA points at the new page.
     */
    function matrix_seed_make_a_referral_point_navbar_button(string $referral_url): void
    {
        $nav_settings = get_field('navigation_settings_start', 'option');

        if (! is_array($nav_settings)) {
            $nav_settings = [];
        }

        $nav_settings['enable_search'] = $nav_settings['enable_search'] ?? 1;

        $nav_settings['looking_help_button'] = is_array($nav_settings['looking_help_button'] ?? null)
            ? $nav_settings['looking_help_button']
            : ['title' => 'Looking for help?', 'url' => home_url('/contact-us/'), 'target' => '_self'];

        $nav_settings['referral_button'] = [
            'title' => 'Make a referral',
            'url' => $referral_url,
            'target' => '_self',
        ];

        if (! is_array($nav_settings['dropdown_cta_button'] ?? null)) {
            $nav_settings['dropdown_cta_button'] = ['title' => 'Access your portal', 'url' => home_url('/your-portal/'), 'target' => '_self'];
        }

        update_field('navigation_settings_start', $nav_settings, 'option');
    }
}

if (! function_exists('matrix_seed_make_a_referral_grid_links')) {
    /**
     * Build About-Links-Grid cards from every published Referrals CPT post.
     *
     * @return array<int, array<string, mixed>>
     */
    function matrix_seed_make_a_referral_grid_links(): array
    {
        $posts = get_posts([
            'post_type' => 'referrals',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'menu_order title',
            'order' => 'ASC',
            'suppress_filters' => false,
        ]);

        $tones = ['bg1', 'bg2', 'bg3', 'bg4'];
        $links = [];
        $index = 0;

        foreach ($posts as $post) {
            if (! $post instanceof WP_Post) {
                continue;
            }

            $title = trim(get_the_title($post));

            if ($title === '') {
                continue;
            }

            $image_url = (string) get_the_post_thumbnail_url($post->ID, 'large');
            $description = trim(wp_strip_all_tags((string) get_the_excerpt($post)));

            $links[] = [
                'icon' => '',
                'image_url' => $image_url,
                'title' => $title,
                'description' => $description,
                'link' => [
                    'title' => $title,
                    'url' => (string) get_permalink($post),
                    'target' => '',
                ],
                'card_tone' => $tones[$index % count($tones)],
            ];

            $index++;
        }

        return $links;
    }
}

$home = home_url('/');
$referrals_url = home_url('/referrals/');
$referrals_admissions_url = home_url('/referrals/referrals-admissions/');
$ereferral_guides_url = home_url('/referrals/ereferral-guides/');
$healthmail_signup_url = 'https://www.healthmail.ie/';
$healthlink_url = 'https://www.healthlink.ie/';

$page_id = matrix_seed_make_a_referral_ensure_page('make-a-referral', 'Make a referral');

if ($page_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not create the Make a referral page.');
    }

    exit(1);
}

$hero_intro = "At St Patrick's Mental Health Services, we have a number of referral pathways for our mental health services and treatment programmes. Use the options below to refer a patient for inpatient, Homecare, outpatient or day programme services.";

$making_a_referral_body = '<p>We treat a wide range of mental health difficulties, including anxiety disorders, addiction and dual diagnosis, bipolar disorder, depression, eating disorders, mood disorders, obsessive&ndash;compulsive disorder, psychosis recovery, and the mental health of young adults and older adults.</p>'
    . '<p>All referrals are carefully considered. Based on the referred person&rsquo;s needs and level of urgency as outlined in the referral, a decision is made on whether we have an appropriate service to offer and, if so, which service we believe would most appropriately meet their needs.</p>'
    . '<p>If you have any queries regarding a referral to our services, please call our Referral and Assessment Service on <a href="tel:012493635">01 249 3635</a> within office hours. Outside of these hours, please call <a href="tel:012493200">01 249 3200</a>.</p>'
    . '<p>We have waiting lists in place for some services. Our team works hard to ensure these waiting lists move as quickly as possible, but please note that there may be some time between receiving your referral and your patient beginning treatment. Please be aware that we no longer accept referrals by fax.</p>';

$accordion_items = [
    [
        'title' => 'eReferrals',
        'starts_open' => 1,
        'content_rows' => [
            [
                'row_type' => 'text',
                'content' => '<p>eReferrals can be sent electronically through Healthlink or your GP Practice Management System, such as Socrates or HealthOne.</p>'
                    . '<p>To submit an eReferral:</p>'
                    . '<ol><li>Log into the relevant system.</li><li>Select &ldquo;St Patrick&rsquo;s Mental Health Services&rdquo; from the private hospital list.</li><li>Choose &ldquo;Psychiatric Referral Service&rdquo; from the list of departments.</li></ol>'
                    . '<p>After this is completed, our Referral and Assessment Service team will respond. For more information, see our <a href="' . esc_url($ereferral_guides_url) . '">step-by-step guide to eReferrals</a>.</p>',
            ],
        ],
    ],
    [
        'title' => 'Referral forms',
        'starts_open' => 0,
        'content_rows' => [
            [
                'row_type' => 'text',
                'content' => '<p>We provide referral forms for our services. Please ensure to complete the form in full before submitting it.</p>'
                    . '<p>You can send completed referral forms to our Referral and Assessment Service via Healthmail, the secure clinical email system from the Health Service Executive (HSE). To submit a form through Healthmail, please email it to <a href="mailto:referrals@stpatricks.ie">referrals@stpatricks.ie</a>.</p>'
                    . '<p>Our Referral and Assessment Service will either contact your patient directly or get in touch with you to discuss the referral in advance. For further enquiries, you can contact the Referral and Assessment Service on <a href="tel:012493635">01 249 3635</a>.</p>',
            ],
        ],
    ],
    [
        'title' => 'Day programmes accepting direct referrals',
        'starts_open' => 0,
        'content_rows' => [
            [
                'row_type' => 'text',
                'content' => '<p>We run a number of day programmes to support people in their mental health recovery. Our programmes aim to help service users develop skills and techniques to deal with the mental health concerns unique to them.</p>'
                    . '<p>We have an overview of all day programmes accepting direct referrals from GPs and other mental healthcare professionals available in our direct referral programme brochure.</p>',
            ],
        ],
    ],
    [
        'title' => 'Sending clinical information',
        'starts_open' => 0,
        'content_rows' => [
            [
                'row_type' => 'text',
                'content' => '<p>We are securely linked to Healthmail, the HSE&rsquo;s email service which enables healthcare providers to send and receive patients&rsquo; clinical information over a protected, secure connection.</p>'
                    . '<p>If you have a Healthmail email address, you can use it to safely and privately send clinical information, such as prescriptions, to our staff. If you do not already have an account, you can <a href="' . esc_url($healthmail_signup_url) . '" target="_blank" rel="noopener noreferrer">sign up for a free, secure Healthmail email address</a>.</p>',
            ],
        ],
    ],
    [
        'title' => 'Learning more about our assessments',
        'starts_open' => 0,
        'content_rows' => [
            [
                'row_type' => 'text',
                'content' => '<p>We work to make our referral process as easy as possible. People who are referred to our Dean Clinic network receive a free-of-charge assessment, called a Prompt Assessment of Needs (PAON), with an experienced mental health nurse.</p>'
                    . '<p>They access a PAON from their own home, using their preferred means of communication. This includes phone, video and online services, allowing for fast, efficient identification of people&rsquo;s needs and referral to the most appropriate programme or service.</p>'
                    . '<p>Please note that the PAON is used for Dean Clinic referrals only; referrals for inpatient and Homecare admission are assessed separately. Our admissions policy and procedures are governed by the Mental Health Act 2001.</p>',
            ],
        ],
    ],
];

$section_padding = [
    ['screen_size' => 'mob', 'padding_top' => '3', 'padding_bottom' => '3'],
    ['screen_size' => 'lg', 'padding_top' => '6.25', 'padding_bottom' => '6.25'],
];

$grid_links = matrix_seed_make_a_referral_grid_links();

$flexi_rows = [
    [
        'acf_fc_layout' => 'hero_with_breadcrumbs',
        'layout_style' => 'image_split',
        'show_breadcrumbs' => 1,
        'breadcrumb_source' => 'manual',
        'manual_breadcrumbs' => [
            ['breadcrumb_link' => ['title' => 'Home', 'url' => $home, 'target' => '']],
        ],
        'current_crumb_label' => 'Make a referral',
        'heading_tag' => 'h1',
        'heading' => 'Make a referral',
        'content' => '<p>' . esc_html($hero_intro) . '</p>',
        'primary_button' => [
            'title' => 'Make an eReferral',
            'url' => $referrals_admissions_url,
            'target' => '',
        ],
        'hero_image' => '',
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
    ],
    [
        'acf_fc_layout' => 'referral_action_cards',
        'left_title' => 'Refer an adult',
        'left_description' => '<p>Refer electronically via Healthlink or your GP Practice IT Management System, or download and complete our adult referral form and submit it via Healthmail to referrals@stpatricks.ie.</p>',
        'left_button' => [
            'title' => 'Refer via Healthlink',
            'url' => $healthlink_url,
            'target' => '_blank',
        ],
        'left_action_icon' => 'external',
        'right_title' => 'Refer an adolescent',
        'right_description' => '<p>Refer electronically via Healthlink or your GP Practice IT Management System, or download and complete our adolescent referral form and submit it via Healthmail to referrals@stpatricks.ie.</p>',
        'right_button' => [
            'title' => 'Refer via Healthlink',
            'url' => $healthlink_url,
            'target' => '_blank',
        ],
        'right_action_icon' => 'external',
        'left_background_color' => '#CEF2EE',
        'right_background_color' => '#E4F4D6',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Making a referral',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'intro_text' => '<p>We accept referrals for inpatient, Homecare, outpatient and day programme services. You can use the channels below to refer to our services.</p>',
        'content' => $making_a_referral_body,
        'background_type' => 'white',
        'column_layout' => 'one_column',
        'text_width' => 'full',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content_accordion',
        'layout_style' => 'default',
        'items' => $accordion_items,
        'section_background' => '#FBFAF7',
        'panel_background' => 'linear-gradient(135deg, #F6EDE0 0%, #F5F0E0 48%, #F4F5DE 100%)',
        'open_panel_background' => 'linear-gradient(135deg, #F6EDE0 0%, #F5F0E0 48%, #F4F5DE 100%)',
        'icon_tile_background_color' => '#FFFFFF',
        'vertical_padding' => 'default',
    ],
    matrix_seed_make_a_referral_grid_row($grid_links, $section_padding),
    [
        'acf_fc_layout' => 'content_cta',
        'heading_tag' => 'h2',
        'heading' => 'Get information about referrals and services',
        'body' => '<p>Our Referral and Assessment Service team can help GPs and other healthcare professionals with queries about our services and referrals.</p><p><strong><a href="tel:012493635">01 249 3635</a></strong></p>',
        'button_link' => [
            'title' => 'Contact our referrals team',
            'url' => $referrals_admissions_url,
            'target' => '',
        ],
        'layout_style' => 'default',
        'background_type' => 'color',
        'background_color' => '#C6ECF4',
    ],
];

update_field('hero_content_blocks', [], $page_id);
update_field('flexible_content_blocks', $flexi_rows, $page_id);

$page_url = (string) get_permalink($page_id);

matrix_seed_make_a_referral_point_navbar_button($page_url);
$landing_id = matrix_seed_make_a_referral_inject_landing_grid($grid_links, $section_padding);

$saved_rows = get_field('flexible_content_blocks', $page_id);
$saved_count = is_array($saved_rows) ? count($saved_rows) : 0;

if (class_exists('WP_CLI')) {
    if ($saved_count === count($flexi_rows)) {
        WP_CLI::success(sprintf(
            'Seeded Make a referral page (%d) with %d flexi blocks and %d referral grid cards.',
            $page_id,
            $saved_count,
            count($grid_links)
        ));
        WP_CLI::log('Page: ' . $page_url);
        WP_CLI::log('Navbar "Make a referral" button -> ' . $page_url);

        if ($landing_id > 0) {
            WP_CLI::log('Added referrals grid to landing: ' . get_permalink($landing_id));
        } else {
            WP_CLI::warning('Referrals landing page (/referrals/) not found; grid not added there.');
        }
    } else {
        WP_CLI::warning(sprintf(
            'Updated page %d but expected %d blocks, found %d.',
            $page_id,
            count($flexi_rows),
            $saved_count
        ));
    }
}
