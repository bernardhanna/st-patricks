<?php

/**
 * Seed St Patrick's at Home page (what-we-offer/st-patricks-at-home) from
 * stpatricks.ie/care-treatment/our-services/homecare-service.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-st-patricks-at-home.php
 */

require_once __DIR__ . '/lib/page-seed-conventions.php';
require_once get_template_directory() . '/inc/migrate-functions.php';

$post_id = (int) (get_page_by_path('what-we-offer/st-patricks-at-home')?->ID ?? 0);

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at what-we-offer/st-patricks-at-home.');
    }

    exit(1);
}

if (! function_exists('matrix_seed_stah_import_image')) {
    function matrix_seed_stah_import_image(string $url, string $title, string $cache_key): int
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
        $filename = $path ? basename((string) $path) : 'homecare-image.jpg';

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

if (! function_exists('matrix_seed_stah_build_image_field')) {
    function matrix_seed_stah_build_image_field(int $attachment_id, string $alt): array
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

if (! function_exists('matrix_seed_stah_url')) {
    function matrix_seed_stah_url(string $path): string
    {
        $path = trim($path, '/');
        $resolved_id = url_to_postid(home_url('/' . $path . '/'));

        if ($resolved_id > 0) {
            return (string) get_permalink($resolved_id);
        }

        $page_id = matrix_seed_resolve_page_id_by_path($path);

        if ($page_id > 0) {
            return (string) get_permalink($page_id);
        }

        return home_url('/' . $path . '/');
    }
}

if (! function_exists('matrix_seed_stah_link')) {
    function matrix_seed_stah_link(string $label, string $path): string
    {
        return '<a href="' . esc_url(matrix_seed_stah_url($path)) . '">' . esc_html($label) . '</a>';
    }
}

if (! function_exists('matrix_seed_stah_content_block')) {
    /**
     * @return array<string, mixed>
     */
    function matrix_seed_stah_content_block(
        string $heading,
        string $content,
        int $image_id = 0,
        string $layout_style = 'image_left',
        string $background_type = 'white'
    ): array {
        $has_image = $image_id > 0;

        return matrix_page_seed_strip_padding([
            'acf_fc_layout' => 'content',
            'heading' => $heading,
            'heading_tag' => matrix_page_seed_heading(2),
            'accent_position' => 'below_heading',
            'intro_text' => '',
            'content' => $content,
            'image' => $has_image ? $image_id : '',
            'column_layout' => $has_image ? 'two_column' : 'one_column',
            'layout_style' => $has_image ? $layout_style : 'image_left',
            'image_height_mode' => 'match_text',
            'text_width' => $has_image ? 'constrained' : 'full',
            'background_type' => $background_type === 'cream' ? 'gradient' : 'color',
            'background_color' => $background_type === 'cream' ? '#FFFFFF' : '#FFFFFF',
            'background_gradient' => $background_type === 'cream'
                ? 'linear-gradient(278deg, #F8F6F3 3.24%, #F5F6ED 90.88%)'
                : '',
        ]);
    }
}

$home = home_url('/');
$what_we_offer_url = home_url('/what-we-offer/');
$faqs_url = matrix_seed_stah_url('service-users-and-visitors/frequently-asked-questions-faqs');
$referrals_url = matrix_seed_stah_url('make-a-referral');
$about_at_home_url = matrix_seed_stah_url('service-users-and-visitors/about-our-st-patricks-at-home-service');
$brochure_url = matrix_migrate_resolve_media_url('/media/4040/spmhs-adult-homecare-for-service-users.pdf');
$youtube_url = 'https://www.youtube.com/watch?v=YPTjsak_fw8';

$scraped_images = [
    'hero' => 'https://www.stpatricks.ie/media/2800/homecare-service-banner-image.png',
    'feature' => 'https://www.stpatricks.ie/media/2801/homecare-service-feature-page.png?width=610&height=332&mode=crop',
    'adult' => 'https://www.stpatricks.ie/media/3659/adult-homecare-service.png?width=400&height=218&mode=crop',
];

$hero_image_id = matrix_seed_stah_import_image($scraped_images['hero'], 'Homecare service banner', 'stah-homecare-hero');
$feature_image_id = matrix_seed_stah_import_image($scraped_images['feature'], 'Homecare service feature', 'stah-homecare-feature');
$adult_image_id = matrix_seed_stah_import_image($scraped_images['adult'], 'Adult Homecare service', 'stah-homecare-adult');

$hero_intro = 'Our Homecare service provides high-quality mental health care and treatment to service users in their own home.';

$what_is_homecare = '<p>At St Patrick\'s Mental Health Services (SPMHS), our adult Homecare service delivers the mental healthcare a person needs directly to them, wherever they are in Ireland. Service users in Homecare receive comprehensive mental health support at home by engaging with their care team through video, phone or other online channels.</p>'
    . '<p>The service takes a multidisciplinary approach, which means service users have support from a multidisciplinary team (MDT). An MDT is made up of a range of mental health professionals with different areas of expertise, who work together and with the service user to ensure all aspects of their care and recovery are looked after.</p>';

$what_to_expect = '<p>Homecare is designed to support the person\'s mental health recovery, while also enabling them to remain in familiar surroundings, without having to travel to hospital or being apart from their family and friends.</p>'
    . '<p>Through Homecare, service users:</p>'
    . '<ul>'
    . '<li>have 24-hour support</li>'
    . '<li>work with a range of mental health professionals</li>'
    . '<li>receive daily contact from members of their MDT</li>'
    . '<li>develop and progress an individual care plan with their MDT</li>'
    . '<li>get the opportunity to meet others who are experiencing mental health difficulties to learn from and support each other</li>'
    . '<li>can use our ' . matrix_seed_stah_link('online patient platform', 'your-portal') . '.</li>'
    . '</ul>'
    . '<p>While in Homecare, service users take part in mental health programmes and talk therapies suited to their recovery. This may include group sessions held online with other people receiving treatment, as well as individual therapy sessions with different members of their MDT. The programmes and services they take part in will be identified by their MDT to best meet their specific needs.</p>'
    . '<p>We also offer a wide range of recovery-focused social and recreational activities that service users can take part in remotely. These include mental health information sessions, as well as leisure and wellbeing activities like yoga, art therapy, relaxation exercises and mindfulness.</p>'
    . '<p>Any ' . matrix_seed_stah_link('medication needed to support recovery', 'care-treatment/medication') . ' will be reviewed and prescribed through the service, and service users have access to our Pharmacy team to discuss this. We can liaise with local pharmacies to arrange for medication service users have been prescribed to be delivered to them.</p>'
    . '<p>Service users are invited to use ' . matrix_seed_stah_link('Your Portal', 'about-your-portal') . ', our online platform which enables them to view and share their own health-related information and access mental health information and supports.</p>'
    . '<p>If you have been referred to or are preparing to begin our Homecare service, you can get practical information about the service and learn more about what to expect from Homecare in our ' . matrix_seed_stah_link('About our St Patrick\'s at Home Service', 'service-users-and-visitors/about-our-st-patricks-at-home-service') . ' section.</p>';

$who_is_for = '<p>The Homecare service for adults is available to all age groups over the age of 18, including young adults (aged 18 to 25) and older adults (aged over 65).</p>'
    . '<p>Referrals are reviewed by a consultant psychiatrist and a team of experienced clinicians. If this review suggests the person is best suited for Homecare, they will have the opportunity to discuss this option in full with our team, and receive guidance and instructions before they access the service.</p>'
    . '<p>The adult Homecare service is covered by the main health insurers. If you have a question about health insurance, you can refer to our ' . matrix_seed_stah_link('health insurance information', 'getting-help/insurance-information') . '.</p>'
    . '<p>Please note that our Willow Grove Adolescent Unit provides a separate Homecare service for young people aged 12 to 17. See more on ' . matrix_seed_stah_link('adolescent Homecare', 'adolescent-mental-health-services') . ' here.</p>';

$referrals_body = '<p>Referrals to Homecare can be made by GPs or a healthcare provider.</p>'
    . '<p>If you are a GP referring your patient to Homecare, please call <a href="tel:012493635">01 249 3635</a>.</p>'
    . '<p>For more information on Homecare or any other of our mental health services, call us on <a href="tel:012493200">01 249 3200</a>.</p>';

$confidentiality = '<p>Please note that, in order to ensure confidentiality and comply with data protection legislation, <strong>audio or visual recording of remote engagements by any means is not permitted</strong>. In all circumstances, recording can only occur with the full, expressed and prior agreement of everyone concerned.</p>';

$section_padding = [
    ['screen_size' => 'mob', 'padding_top' => '3', 'padding_bottom' => '3'],
    ['screen_size' => 'lg', 'padding_top' => '6.25', 'padding_bottom' => '6.25'],
];

$flexi_rows = [
    [
        'acf_fc_layout' => 'hero_with_breadcrumbs',
        'layout_style' => 'image_split',
        'show_breadcrumbs' => 1,
        'breadcrumb_source' => 'manual',
        'manual_breadcrumbs' => [
            ['breadcrumb_link' => ['title' => 'Home', 'url' => $home, 'target' => '']],
            ['breadcrumb_link' => ['title' => 'What we offer', 'url' => $what_we_offer_url, 'target' => '']],
        ],
        'current_crumb_label' => "St Patrick's at Home",
        'heading_tag' => matrix_page_seed_heading(1),
        'heading' => "St Patrick's at Home",
        'content' => '<p>' . esc_html($hero_intro) . '</p>',
        'hero_image' => $hero_image_id,
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
        'padding_settings' => $section_padding,
    ],
    matrix_seed_stah_content_block('What is Homecare?', $what_is_homecare, $feature_image_id, 'image_left'),
    matrix_seed_stah_content_block('What can you expect?', $what_to_expect, $adult_image_id, 'image_right'),
    matrix_page_seed_strip_padding([
        'acf_fc_layout' => 'video_showcase',
        'heading_tag' => matrix_page_seed_heading(2),
        'heading' => 'Watch our Homecare video',
        'intro' => '<p>This video gives an overview of our adult Homecare service.</p>',
        'layout_style' => 'feature_single',
        'slides' => [
            [
                'poster_image' => matrix_seed_stah_build_image_field($feature_image_id, 'Homecare service video'),
                'video_source_type' => 'embed_url',
                'video_embed_url' => $youtube_url,
                'caption' => '<p>Watch the video to learn more about how our Homecare service supports people in their own homes.</p>',
                'cta_link' => [
                    'title' => 'Homecare brochure for service users',
                    'url' => $brochure_url,
                    'target' => '_blank',
                ],
            ],
        ],
        'section_background' => '#FFFFFF',
    ]),
    matrix_seed_stah_content_block('Who is the adult Homecare service for?', $who_is_for, $adult_image_id, 'image_left', 'cream'),
    matrix_seed_stah_content_block('How are referrals made?', $referrals_body, $feature_image_id, 'image_right'),
    matrix_page_seed_strip_padding([
        'acf_fc_layout' => 'content_cta',
        'heading_tag' => matrix_page_seed_heading(2),
        'heading' => 'See more of what to expect from Homecare',
        'body' => '<p>Find practical information about being referred, admitted and receiving care through our Homecare service.</p>',
        'button_link' => [
            'title' => 'Your time in Homecare',
            'url' => $about_at_home_url,
            'target' => '',
        ],
        'background_type' => 'color',
        'background_color' => '#CEF2EE',
    ]),
    matrix_seed_stah_content_block('A note on confidentiality', $confidentiality, 0, 'image_left', 'cream'),
    matrix_page_seed_strip_padding([
        'acf_fc_layout' => 'content_cta',
        'heading_tag' => matrix_page_seed_heading(2),
        'heading' => 'Queries',
        'body' => '<p>For general queries, please call us. For more on mental health and our services, see our <a href="' . esc_url($faqs_url) . '">frequently asked questions (FAQs)</a>.</p>',
        'button_link' => [
            'title' => '01 249 3200',
            'url' => 'tel:012493200',
            'target' => '',
        ],
        'background_type' => 'color',
        'background_color' => '#CEF2EE',
    ]),
    matrix_page_seed_strip_padding([
        'acf_fc_layout' => 'content_cta',
        'heading_tag' => matrix_page_seed_heading(2),
        'heading' => 'Referrals',
        'body' => '<p>Contact our Referral and Assessment Service for queries regarding referrals to our services. <a href="' . esc_url($referrals_url) . '">See more from our referrals team</a>.</p>',
        'button_link' => [
            'title' => '01 249 3635',
            'url' => 'tel:012493635',
            'target' => '',
        ],
        'background_type' => 'color',
        'background_color' => '#E9E2F7',
    ]),
];

update_field('hero_content_blocks', [], $post_id);
update_field('flexible_content_blocks', $flexi_rows, $post_id);
update_post_meta($post_id, '_matrix_seed_key', 'st-patricks-at-home');

$saved_rows = get_field('flexible_content_blocks', $post_id);
$saved_count = is_array($saved_rows) ? count($saved_rows) : 0;

if (class_exists('WP_CLI')) {
    if ($saved_count === count($flexi_rows)) {
        WP_CLI::success(sprintf(
            "Seeded St Patrick's at Home page (%d) with %d flexi blocks.",
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
