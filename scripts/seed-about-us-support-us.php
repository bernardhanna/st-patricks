<?php

/**
 * Seed About Us > Support us with content from stpatricks.ie Get Involved section.
 *
 * Sources:
 * - https://www.stpatricks.ie/get-involved
 * - https://www.stpatricks.ie/get-involved/fundraising
 * - https://www.stpatricks.ie/get-involved/donations
 * - https://www.stpatricks.ie/get-involved/service-user-participation
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-about-us-support-us.php
 */

$post_id = (int) (get_page_by_path('about-us/support-us')?->ID ?? 0);

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at about-us/support-us.');
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

$home = home_url('/');
$about_us_url = home_url('/about-us/');
$support_us_url = get_permalink($post_id) ?: home_url('/about-us/support-us/');
$faqs_url = home_url('/service-users-and-visitors/frequently-asked-questions-faqs/');
$referrals_url = home_url('/make-a-referral-cta/');
$careers_url = home_url('/about-us/careers/');
$communications_email = 'communications@stpatricks.ie';
$communications_phone = '01 249 3540';

$section_padding = [
    ['screen_size' => 'mob', 'padding_top' => '3', 'padding_bottom' => '3'],
    ['screen_size' => 'lg', 'padding_top' => '6.25', 'padding_bottom' => '6.25'],
];

$hero_image_id = matrix_seed_import_scraped_image(
    'https://www.stpatricks.ie/media/1869/st-patricks-mental-health-services-garden.jpg?width=1200&height=800&mode=crop',
    'St Patricks Mental Health Services garden',
    'support-us-scraped-hero'
);

$hero_intro = 'We believe in listening to our service users and their supporters, and have a number of networks and forums for you to give feedback and help shape all that we do.';

$ways_to_get_involved = [
    [
        'title' => 'Service User Participation',
        'description' => 'Help shape our services through service user engagement groups, advisory networks, and experience surveys.',
        'link' => ['title' => 'Service User Participation', 'url' => $support_us_url, 'target' => ''],
    ],
    [
        'title' => 'Fundraising',
        'description' => 'Support our Capital Development Programme and the Jonathan Swift Institute of Mentally Healthy Living.',
        'link' => ['title' => 'Fundraising', 'url' => $support_us_url, 'target' => ''],
    ],
    [
        'title' => 'Legacy Donations',
        'description' => 'Leave a gift in your will to help us provide the highest quality mental healthcare across Ireland.',
        'link' => ['title' => 'Legacy Donations', 'url' => $support_us_url, 'target' => ''],
    ],
];

$engagement_groups = [
    [
        'title' => 'Service User and Supporters Council (SUAS)',
        'description' => 'Our Service User and Supporters Council provides invaluable input to all that we do at SPMHS.',
    ],
    [
        'title' => 'Service User Advisory Network (SUAN)',
        'description' => 'SUAN helps ensure service user voices inform how our services are developed and delivered.',
    ],
    [
        'title' => 'Family, Carers and Supporters (FCS) Advisory Network',
        'description' => 'The FCS Advisory Network supports the involvement of families, carers and supporters in our work.',
    ],
];

$service_user_body = '<p>We believe in and consistently work towards a more inclusive system of service user representation in shaping our services.</p>'
    . '<p>We run three service user engagement groups &mdash; our Service User and Supporters Council (SUAS), Service User Advisory Network (SUAN) and Family, Carers and Supporters (FCS) Advisory Network &mdash; which provide invaluable input to all that we do here in St Patrick\'s Mental Health Services (SPMHS).</p>'
    . '<p>We also invite service users of our inpatient and Homecare services, day programmes and Dean Clinics to give feedback on their experience of care and treatment through our Service User Experience Surveys. Feedback from the surveys is reviewed by our senior management and used to inform improvements and developments.</p>'
    . '<p>If you have any questions about service user engagement in SPMHS, you can contact our Communications Department by calling <a href="tel:+35312493540">' . esc_html($communications_phone) . '</a> or by emailing <a href="mailto:' . esc_attr($communications_email) . '">' . esc_html($communications_email) . '</a>.</p>';

$fundraising_intro = '<p><strong>A gift from the will of Jonathan Swift, Dean of St Patrick\'s Cathedral, champion of the poor and oppressed, satirist and author of Gulliver\'s Travels, started a legacy which has left its mark on Irish mental healthcare to this day.</strong></p>';

$fundraising_body = '<p>On 19 October 1745, Swift passed away. In his will, he left his estate to establish a hospital for the psychiatrically ill. By the standards of his day, this decision was incredible. Swift was moved to ensure that St Patrick\'s would be the first hospital in Ireland and one of the first in the world to be built specifically for the care of the psychiatrically ill.</p>'
    . '<p>Throughout the years, St Patrick\'s Hospital has relied heavily on fundraising, as well as fees from service users. In recent years, the inception of the Friends of St Patrick\'s and then the formation of St Patrick\'s Mental Health Foundation have played vital roles in the life of the hospital.</p>'
    . '<h3>Jonathan Swift Institute of Mentally Healthy Living</h3>'
    . '<p>Building on the achievements of St Patrick\'s Mental Health Foundation, fundraising activities have been restructured and refocused. Since 2016, all fundraising activities have been directed to the Capital Development Project to support the further development of SPMHS into a service of excellence grounded in a human rights framework and based on a recovery model of service delivery.</p>'
    . '<p>We aim to establish the Jonathan Swift Institute of Mentally Healthy Living, a world-renowned institute that promotes mentally healthy living and raises standards for the care of those experiencing mental health difficulties. This centre will provide a hub of research, training, education and public awareness with a special focus on promoting human rights, recovery and evidence-based mental health best practice.</p>'
    . '<p>If you would like to find out more about our Capital Development Plan, please contact our Communications Department on <a href="tel:+35312493540">' . esc_html($communications_phone) . '</a>.</p>';

$legacy_body = '<p>At St Patrick\'s Mental Health Services, our aim is simple: to provide the highest quality mental healthcare to as many people experiencing mental health difficulties as possible in Ireland.</p>'
    . '<p>One way for you to help us achieve this aim is by leaving a gift in your will. Our promise to you is that we will:</p>'
    . '<ul>'
    . '<li>Treat you fairly when giving you the opportunity to leave a legacy and not intrude on your privacy by telephoning you about this way of giving</li>'
    . '<li>Never ask you the size or type of legacy if you decide to support our work in this way</li>'
    . '<li>Absolutely recognise that your own family and friends come first in your will</li>'
    . '<li>Never ask you to tell us your intentions</li>'
    . '<li>Fully understand that personal circumstances change and there may be a time when you must take St Patrick\'s Mental Health Services out of your will</li>'
    . '<li>Use your gift wisely</li>'
    . '</ul>'
    . '<p>If you would like to talk to someone or would like further information on our legacy appeal, please contact us on <a href="tel:+35312493540">' . esc_html($communications_phone) . '</a>.</p>';

$fundraising_principles = [
    'Volunteer Policy',
    'Commitment to Standards in Fundraising Practice',
    'Donor Charter',
    'Communications and Fundraising Feedback and Complaints Policy',
    'Public Compliance Statement',
    'Donor Funding Consent Form',
    'Third-Party Fundraising Event Agreement',
];

$fundraising_accordion_items = [
    [
        'title' => 'Fundraising Guiding Principles',
        'starts_open' => 1,
        'content_rows' => [
            [
                'row_type' => 'text',
                'content' => '<p>SPMHS is committed to complying with the Statement for Guiding Principles for Fundraising and has formally discussed and adopted the statement at a meeting of the board of governors. Contact us if you have any questions relating to fundraising compliance.</p>'
                    . '<p>You can learn more about the Guiding Principles of Fundraising at the <a href="https://www.charitiesinstituteireland.ie/" target="_blank" rel="noopener noreferrer">Charities Institute Ireland</a> website.</p>',
            ],
        ],
    ],
];

foreach ($fundraising_principles as $principle_title) {
    $fundraising_accordion_items[] = [
        'title' => $principle_title,
        'starts_open' => 0,
        'content_rows' => [
            [
                'row_type' => 'text',
                'content' => '<p>Please contact our Communications Department on <a href="tel:+35312493540">' . esc_html($communications_phone) . '</a> or email <a href="mailto:' . esc_attr($communications_email) . '">' . esc_html($communications_email) . '</a> for a copy of this document.</p>',
            ],
        ],
    ];
}

$group_cards = [];

foreach ($engagement_groups as $group) {
    $group_cards[] = [
        'icon' => '',
        'image_url' => '',
        'title' => $group['title'],
        'description' => $group['description'],
        'link' => '',
        'card_tone' => 'bg1',
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
            ['breadcrumb_link' => ['title' => 'About Us', 'url' => $about_us_url, 'target' => '']],
        ],
        'current_crumb_label' => 'Support us',
        'heading_tag' => 'h1',
        'heading' => 'Support us',
        'content' => '<p>' . esc_html($hero_intro) . '</p>',
        'hero_image' => $hero_image_id,
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
    ],
    [
        'acf_fc_layout' => 'about_links_grid',
        'heading_tag' => 'h2',
        'heading_text' => 'Ways to get involved',
        'intro_text' => '<p>There are many ways to support St Patrick\'s Mental Health Services, from sharing your experience to fundraising and legacy giving.</p>',
        'links' => $ways_to_get_involved,
        'bg_color' => '#F1F8F9',
        'heading_color' => '#0B0B08',
        'intro_color' => '#4A4B37',
        'columns' => '3',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Service user engagement',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'intro_text' => '',
        'content' => $service_user_body,
        'column_layout' => 'one_column',
        'background_type' => 'white',
        'text_width' => 'constrained',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'about_links_grid',
        'heading_tag' => 'h2',
        'heading_text' => 'See our service user engagement groups',
        'intro_text' => '',
        'links' => $group_cards,
        'bg_color' => '#FFFFFF',
        'heading_color' => '#0B0B08',
        'intro_color' => '#4A4B37',
        'columns' => '3',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Fundraising',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'intro_text' => $fundraising_intro,
        'content' => $fundraising_body,
        'column_layout' => 'one_column',
        'background_type' => 'cream',
        'text_width' => 'constrained',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content_accordion',
        'layout_style' => 'default',
        'items' => $fundraising_accordion_items,
        'section_background' => '#FFFFFF',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Legacy Donations',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'intro_text' => '<p><strong>One way for you to help us achieve our aim is by leaving a gift in your will.</strong></p>',
        'content' => $legacy_body,
        'column_layout' => 'one_column',
        'background_type' => 'white',
        'text_width' => 'constrained',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content_cta',
        'heading_tag' => 'h2',
        'heading' => 'Interested in joining our team?',
        'body' => '<p>Find out more about career opportunities at St Patrick\'s Mental Health Services.</p>',
        'button_link' => [
            'title' => 'Work with us',
            'url' => $careers_url,
            'target' => '',
        ],
        'background_type' => 'color',
        'background_color' => '#CEF2EE',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content_cta',
        'heading_tag' => 'h2',
        'heading' => 'Queries',
        'body' => '<p>For general queries, please call us. For more on mental health and our services, see our frequently asked questions (FAQs).</p><p><strong>01 249 3200</strong></p>',
        'button_link' => [
            'title' => 'See our FAQs',
            'url' => $faqs_url,
            'target' => '',
        ],
        'background_type' => 'color',
        'background_color' => '#C6ECF4',
        'padding_settings' => [
            ['screen_size' => 'mob', 'padding_top' => '3', 'padding_bottom' => '1.5'],
            ['screen_size' => 'lg', 'padding_top' => '6.25', 'padding_bottom' => '1.5'],
        ],
    ],
    [
        'acf_fc_layout' => 'content_cta',
        'heading_tag' => 'h2',
        'heading' => 'Referrals',
        'body' => '<p>Contact our Referral and Assessment Service for queries regarding referrals to our services.</p><p><strong>01 249 3635</strong></p>',
        'button_link' => [
            'title' => 'See more from our referrals team',
            'url' => $referrals_url,
            'target' => '',
        ],
        'background_type' => 'color',
        'background_color' => '#CEF2EE',
        'padding_settings' => [
            ['screen_size' => 'mob', 'padding_top' => '1.5', 'padding_bottom' => '3'],
            ['screen_size' => 'lg', 'padding_top' => '1.5', 'padding_bottom' => '6.25'],
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
            'Failed to update Support us page ' . $post_id
            . ' (expected ' . count($flexi_rows) . ' blocks, found ' . $saved_count . ')'
        );
    }

    exit(1);
}

$message = sprintf(
    'Seeded Support us (page %d) with %d flexi blocks from stpatricks.ie Get Involved content.',
    $post_id,
    count($flexi_rows)
);

if (class_exists('WP_CLI')) {
    WP_CLI::success($message);
}

echo $message . "\n";
