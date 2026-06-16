<?php

/**
 * Seed Get Involved CPT posts from stpatricks.ie get-involved pages.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-get-involved-cpt.php
 */

require_once __DIR__ . '/lib/page-seed-conventions.php';
require_once __DIR__ . '/lib/get-involved-seed.php';
require_once get_template_directory() . '/inc/migrate-functions.php';

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

$base_media = 'https://www.stpatricks.ie';
$hero_default_id = (int) matrix_migrate_attachment_id_for_source_path('/media/3121/get-involved-banner.png');

if ($hero_default_id <= 0) {
    $hero_default_id = matrix_seed_import_scraped_image(
        $base_media . '/media/3121/get-involved-banner.png',
        'Get Involved banner',
        'get-involved-hero-default'
    );
}

$participation_term_id = matrix_seed_get_involved_ensure_term('Service User Participation', 'service-user-participation');
$peer_support_term_id = matrix_seed_get_involved_ensure_term('Peer Support', 'peer-support');
$fundraising_term_id = matrix_seed_get_involved_ensure_term('Fundraising', 'fundraising');
$donations_term_id = matrix_seed_get_involved_ensure_term('Donations', 'donations');

$learning_hub_url = home_url('/getting-help/learning-resource-hub/');
$communications_email = 'communications@stpatricks.ie';
$engagement_email = 'sfitzharris@stpatricks.ie';

$pages = [
    'service-user-participation' => [
        'title' => 'Service User Participation',
        'slug' => 'service-user-participation',
        'section_term_id' => $participation_term_id,
        'excerpt' => 'Service user representation in shaping our mental health services at St Patrick\'s Mental Health Services.',
        'flexi_rows' => matrix_seed_get_involved_page_rows(
            'Service User Participation',
            'Service User Participation',
            'We believe in and consistently work towards a more inclusive system of service user representation in shaping our services.',
            'participation',
            [
                matrix_seed_get_involved_content_block(
                    'Service user engagement at SPMHS',
                    '<p>We run three service user engagement groups – our Service User and Supporters Council (SUAS), Service User Advisory Network (SUAN) and Family, Carers and Supporters (FCS) Advisory Network – which provide invaluable input to all that we do here in St Patrick\'s Mental Health Services (SPMHS).</p>'
                    . '<p>We also invite service users of our inpatient and Homecare services, day programmes and Dean Clinics to give feedback on their experience of care and treatment through our Service User Experience Surveys. Feedback from the surveys is reviewed by our senior management and used to inform improvements and developments.</p>'
                    . '<p>If you have any questions about service user engagement in SPMHS, you can contact our Communications Department by calling <a href="tel:012493540">01 249 3540</a> or by emailing <a href="mailto:' . esc_attr($communications_email) . '">' . esc_html($communications_email) . '</a>.</p>'
                ),
                [
                    'acf_fc_layout' => 'related_cards',
                    'heading_tag' => matrix_page_seed_heading(2),
                    'heading' => 'See our service user engagement groups',
                    'intro_text' => '',
                    'cards' => [
                        [
                            'image' => $hero_default_id,
                            'title' => 'SUAS',
                            'description' => 'See more on our Service User and Supporters Council.',
                            'link' => [
                                'title' => 'See more on SUAS',
                                'url' => matrix_seed_get_involved_url('service-user-and-supporters-council-suas'),
                                'target' => '',
                            ],
                        ],
                        [
                            'image' => $hero_default_id,
                            'title' => 'SUAN',
                            'description' => 'Learn about our Service User Advisory Network.',
                            'link' => [
                                'title' => 'Learn about SUAN',
                                'url' => matrix_seed_get_involved_url('service-user-advisory-network-suan'),
                                'target' => '',
                            ],
                        ],
                        [
                            'image' => $hero_default_id,
                            'title' => 'FCS network',
                            'description' => 'See the Family, Carers and Supporters Advisory Network.',
                            'link' => [
                                'title' => 'See the FCS network',
                                'url' => matrix_seed_get_involved_url('service-user-participation'),
                                'target' => '',
                            ],
                        ],
                    ],
                    'background_color' => '#FFFFFF',
                    'columns' => '3',
                ],
                matrix_seed_get_involved_content_block(
                    'News from our service user groups',
                    '<p>We share monthly updates from our service user engagement groups.</p>',
                    'cream'
                ),
            ],
            $hero_default_id
        ),
    ],
    'peer-support' => [
        'title' => 'Peer Support',
        'slug' => 'peer-support',
        'section_term_id' => $peer_support_term_id,
        'excerpt' => 'Peer-to-peer support through lived experience at St Patrick\'s Mental Health Services.',
        'flexi_rows' => matrix_seed_get_involved_page_rows(
            'Peer Support',
            'Peer Support',
            'Our Service User and Supporters Council (SUAS) launched an online peer-to-peer support service, Here 4 U, in January 2021.',
            'main',
            [
                matrix_seed_get_involved_content_block(
                    'Peer or peer-to-peer support',
                    '<p>Peer or peer-to-peer support is when people use their own experiences to help each other.</p>'
                    . '<p>Here 4 U was established to help reduce the sense of isolation many people experienced because of public health restrictions to combat the spread of COVID-19. SUAS recognised that there was a need for an online space where people who have a shared experience of mental ill-health can support one another.</p>'
                    . '<p>Here 4 U began its pilot phase in January 2021, run both by and for former service users of St Patrick\'s Mental Health Services, who were specially trained to facilitate this kind of service. The training covered facilitation roles and skills, group dynamics, non-judgmental spaces, self-care, vicarious trauma, active listening and more.</p>'
                    . '<p>Here 4 U ran for a ten-month period, coming to an end on 14 October 2021. We would like to thank our service users who were involved in establishing and running this important service, and everyone who attended it.</p>'
                ),
                matrix_seed_get_involved_content_block(
                    'Supporting your mental health',
                    '<p>Looking for information about mental health, guidance on how to stay well, or supports for your family and carers?</p>',
                    'cream'
                ),
                matrix_seed_get_involved_content_block(
                    'Find out more about SUAS',
                    '<p><a href="' . esc_url(matrix_seed_get_involved_url('service-user-and-supporters-council-suas')) . '">Service User and Supporters Council - SUAS</a></p>',
                ),
            ],
            $hero_default_id
        ),
    ],
    'fundraising' => [
        'title' => 'Fundraising',
        'slug' => 'fundraising',
        'section_term_id' => $fundraising_term_id,
        'excerpt' => 'Support our Capital Development Programme and the Jonathan Swift Institute of Mentally Healthy Living.',
        'flexi_rows' => matrix_seed_get_involved_page_rows(
            'Fundraising',
            'Fundraising',
            'A gift from the will of Jonathan Swift started a legacy which has left its mark on Irish mental healthcare to this day.',
            'main',
            [
                matrix_seed_get_involved_content_block(
                    'Jonathan Swift Institute of Mentally Healthy Living',
                    '<p>On 19 October 1745, Jonathan Swift passed away. In his will, he left his estate to establish a hospital for the psychiatrically ill. By the standards of his day, this decision was incredible.</p>'
                    . '<p>In recent years, the inception of the Friends of St Patrick\'s and then the formation of St Patrick\'s Mental Health Foundation have played vital roles in the life of the hospital. Both organisations have generously supported various projects, including outpatient department initiatives, the service user library, research and many educational activities.</p>'
                    . '<p>Since 2016, all fundraising activities have been directed to the Capital Development Project. Our aim is to establish the Jonathan Swift Institute of Mentally Healthy Living, a world-renowned institute that promotes mentally healthy living as well as raising standards and expectations for the care of those experiencing mental health difficulties across the world.</p>'
                    . '<p>If you would like to find out more about our Capital Development Plan, please contact our Communications Department on <a href="tel:012493540">01 249 3540</a>.</p>'
                ),
                matrix_seed_get_involved_content_block(
                    'Fundraising Guiding Principles',
                    '<p>SPMHS is committed to complying with the Statement for Guiding Principles for Fundraising and has formally discussed and adopted the statement at a meeting of the board of governors.</p>'
                    . '<ol><li>Volunteer Policy</li><li>Commitment to Standards in Fundraising Practice</li><li>Donor Charter</li><li>Communications and Fundraising Feedback and Complaints Policy</li><li>Public Compliance Statement</li><li>Donor Funding Consent Form</li><li>Third-Party Fundraising Event Agreement</li></ol>'
                    . '<p>You can learn more about the Guiding Principles of Fundraising at the <a href="https://www.charitiesinstituteireland.ie/" target="_blank" rel="noopener noreferrer">Charities Institute Ireland</a> website.</p>',
                    'cream'
                ),
            ],
            $hero_default_id
        ),
    ],
    'donations' => [
        'title' => 'Legacy Donations',
        'slug' => 'donations',
        'section_term_id' => $donations_term_id,
        'excerpt' => 'Leave a gift in your will to support mental healthcare in Ireland.',
        'flexi_rows' => matrix_seed_get_involved_page_rows(
            'Legacy Donations',
            'Donations',
            'At St Patrick\'s Mental Health Services, our aim is simple: to provide the highest quality mental healthcare to as many people experiencing mental health difficulties as possible in Ireland.',
            'main',
            [
                matrix_seed_get_involved_content_block(
                    'Legacy Donations',
                    '<p>One way for you to help us achieve this aim is by leaving a gift in your will.</p>'
                    . '<p>Our promise to you is that we will:</p>'
                    . '<ul><li>Treat you fairly when giving you the opportunity to leave a legacy and we promise not to intrude on your privacy by telephoning you about this way of giving</li>'
                    . '<li>Never ask you the size or type of legacy if you decide to support our work in this way</li>'
                    . '<li>Absolutely recognise that your own family and friends come first in your will</li>'
                    . '<li>Never ask you to tell us your intentions</li>'
                    . '<li>Fully understand that personal circumstances change and there may be a time when you must take St Patrick\'s Mental Health Services out of your will</li>'
                    . '<li>Use your gift wisely</li></ul>'
                    . '<p>If you would like to talk to someone or would like further information on our legacy appeal, please contact us on <a href="tel:012493540">01 249 3540</a>.</p>'
                ),
            ],
            $hero_default_id
        ),
    ],
    'service-user-and-supporters-council-suas' => [
        'title' => 'Service User and Supporters Council - SUAS',
        'slug' => 'service-user-and-supporters-council-suas',
        'section_term_id' => $participation_term_id,
        'excerpt' => 'SUAS ensures service users and their supporters are directly involved in all we do.',
        'flexi_rows' => matrix_seed_get_involved_page_rows(
            'Service User and Supporters Council - SUAS',
            'Service User and Supporters Council - SUAS',
            'Our Service User and Supporters Council (SUAS) ensures that our service users and those who support them are directly involved in all we do.',
            'participation',
            [
                matrix_seed_get_involved_content_block(
                    'What does SUAS do?',
                    '<p>The main focus of SUAS is to listen to and represent the thoughts and opinions of our service users, as well as those who support them. This ensures that their needs are at the centre of every aspect of the care and treatment that we deliver.</p>'
                    . '<p>SUAS works with our management, Board of Governors, and clinicians to capture and represent the views and perspectives of our service users and their supporters. Members of SUAS are regularly asked to review and provide input on plans being considered that will impact the experience of our service users and their supporters.</p>'
                ),
                matrix_seed_get_involved_content_block(
                    'What does becoming a member of SUAS involve?',
                    '<p>Members of SUAS are required to attend meetings on a monthly basis. These meetings take place on the first Wednesday of each month and are currently held remotely from 5.30pm to 7pm.</p>'
                    . '<p>Members are expected to read documents ahead of time that are relevant to the agenda. SUAS consults on projects and activities that may be sensitive, and members are asked to sign a confidentiality agreement when joining.</p>',
                    'cream'
                ),
                matrix_seed_get_involved_content_block(
                    'Who can become a member of SUAS?',
                    '<p>Membership of SUAS is open to people who have previously used our services as an inpatient, outpatient or day patient, and to those who support them.</p>'
                    . '<p>You need to have been discharged from inpatient care to join. You can take part in SUAS while you are attending day services or outpatient care.</p>'
                ),
                matrix_seed_get_involved_content_block(
                    'How do I join SUAS?',
                    '<p>Applying to be a member involves writing an application letter outlining your interest in joining SUAS. If you are a service user, we also require your application letter to be accompanied by a letter of support from your SPMHS consultant.</p>'
                    . '<p>SUAS is currently seeking new members to join the Council in 2026. If you have any questions about SUAS or you would like to register your interest, you can contact Siobhan Fitzharris, Service User Engagement Lead on <a href="mailto:' . esc_attr($engagement_email) . '">' . esc_html($engagement_email) . '</a>.</p>',
                    'cream'
                ),
            ],
            $hero_default_id,
            'participation'
        ),
    ],
    'service-user-advisory-network-suan' => [
        'title' => 'Service User Advisory Network - SUAN',
        'slug' => 'service-user-advisory-network-suan',
        'section_term_id' => $participation_term_id,
        'excerpt' => 'SUAN gathers service user feedback to inform how we develop our services.',
        'flexi_rows' => matrix_seed_get_involved_page_rows(
            'Service User Advisory Network - SUAN',
            'Service User Advisory Network - SUAN',
            'Our Service User Advisory Network (SUAN) is an important part of how we gather and act on our service users\' feedback.',
            'participation',
            [
                matrix_seed_get_involved_content_block(
                    'What is SUAN?',
                    '<p>We set up SUAN to enable us to consult with and seek the views and opinions of people who have used our services. SUAN also allows us to engage with people who support our service users.</p>'
                    . '<p>This ensures that the views and perspectives of our service users are included as we develop and implement our new strategic plan, <em>The Future in Mind</em>.</p>'
                ),
                matrix_seed_get_involved_content_block(
                    'What does becoming a member of SUAN involve?',
                    '<p>Members of SUAN are kept informed by email about focus groups and consultation forums that they can attend. SUAN members are also invited to join working groups and steering committees to help develop new projects.</p>'
                    . '<p>As many projects SUAN consults on may be sensitive, members are asked to sign a confidentiality agreement when joining SUAN.</p>',
                    'cream'
                ),
                matrix_seed_get_involved_content_block(
                    'How do I join SUAN?',
                    '<p>To join SUAN, complete the registration form on this page with your contact information and indicate what kinds of activities you would like to take part in as a member of SUAN.</p>'
                    . '<p>Once you have registered, you will be added to the SUAN mailing list. There is no obligation to take part in the different groups and projects, and you can opt out at any time.</p>'
                ),
                matrix_seed_get_involved_content_block(
                    'Can a family member, carer, or supporter join SUAN?',
                    '<p>We have a Family Members, Carers, and Supporters (FCS) Advisory Network for people who support our current and former service users. As a family member, carer, or supporter, you can also become a member of our <a href="' . esc_url(matrix_seed_get_involved_url('service-user-and-supporters-council-suas')) . '">Service User and Supporters Council (SUAS)</a>.</p>'
                    . '<p>If you would like more information about SUAN, please contact our Service User Engagement Lead, Siobhan Fitzharris, by calling <a href="tel:012493390">01 249 3390</a> or emailing <a href="mailto:' . esc_attr($engagement_email) . '">' . esc_html($engagement_email) . '</a>.</p>',
                    'cream'
                ),
            ],
            $hero_default_id,
            'participation'
        ),
    ],
    'news-for-service-users' => [
        'title' => 'News for Service Users',
        'slug' => 'news-for-service-users',
        'section_term_id' => $participation_term_id,
        'excerpt' => 'Monthly updates from our service user engagement groups.',
        'flexi_rows' => matrix_seed_get_involved_page_rows(
            'News for Service Users',
            'News for Service Users',
            'Here, we share news and updates from our service user engagement groups.',
            'participation',
            [
                matrix_seed_get_involved_content_block(
                    'Service user engagement at SPMHS',
                    '<p>In St Patrick\'s Mental Health Services (SPMHS), we are committed to involving service users in a meaningful way in the planning, delivery, and development of our services.</p>'
                    . '<p>Below, you\'ll find monthly updates from three of our service user engagement groups: our Service User and Supporters Council (SUAS), Service User Advisory Network (SUAN) and Family, Carers and Supporters (FCS) Advisory Network.</p>'
                    . '<p>If you have any questions about service user participation or would like to get involved, you can contact Siobhan Fitzharris, Service User Engagement Lead on <a href="mailto:' . esc_attr($engagement_email) . '">' . esc_html($engagement_email) . '</a>.</p>'
                ),
                matrix_seed_get_involved_content_block(
                    'SUAS updates',
                    '<p>SUAS is a forum established here in SPMHS to facilitate extensive service user participation, which directly informs how we as an organisation develop our services. SUAS meets remotely on the first Wednesday of each month.</p>'
                    . '<p>This year began with the election of a new SUAS Chair, Chris Miley, and Vice-Chair, Sean Blake, alongside agreement on the Council\'s goals and objectives for 2025. The CEO joined a SUAS meeting early in the year to provide members with an update on progress made in 2024 to advance the organisation\'s strategy.</p>'
                ),
                [
                    'acf_fc_layout' => 'content_accordion',
                    'layout_style' => 'default',
                    'section_background' => '#FBFAF7',
                    'panel_background' => '#FFFFFF',
                    'open_panel_background' => 'linear-gradient(-42.77deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
                    'items' => [
                        matrix_seed_get_involved_accordion_item(
                            'Improving Care and Experience',
                            '<p>The dietitian team invited SUAS members to provide feedback on proposed changes to inpatient menus, with members also taking part in ward food-tasting sessions to help maintain high standards. SUAS members continue to support the Pillars of Wellness programme by sharing recovery experiences to help ease anxieties about discharge.</p>',
                            true
                        ),
                        matrix_seed_get_involved_accordion_item(
                            'Advocacy and Rights',
                            '<p>The Advocacy Manager updated members on current activities and sought input on how to increase service user involvement in projects, submissions, and the development of the advocacy centre. Options to progress SUAS\' proposal to introduce peer support workers at SPMHS are also being explored.</p>'
                        ),
                        matrix_seed_get_involved_accordion_item(
                            'Shaping the National Centre',
                            '<p>The Communications Director and Mental Health Promotion team ran consultations with SUAS on proposed content for the new National Centre for Mentally Healthy Living. Members shared recovery insights and feedback on wellness tools, which will help shape the educational programme and visitor journey.</p>'
                        ),
                        matrix_seed_get_involved_accordion_item(
                            'SUAS activity in 2024',
                            '<p>SUAS marked its work throughout 2024 including the AGM, development of the SUAS Wall display area in SPUH, consultations on the National Centre for Mentally Healthy Living, and ongoing advocacy on Dean Clinic appointment communications and inpatient remote access preferences.</p>'
                        ),
                        matrix_seed_get_involved_accordion_item(
                            'SUAS activity in 2023',
                            '<p>Throughout 2023, SUAS drove proposals to introduce peer support workers, developed a Changing Treating Team leaflet, contributed to stigma survey development, and collaborated with researchers evaluating service user engagement structures.</p>'
                        ),
                    ],
                ],
            ],
            $hero_default_id,
            'participation'
        ),
    ],
    'service-user-experience-survey' => [
        'title' => 'Service User Experience Survey',
        'slug' => 'service-user-experience-survey',
        'section_term_id' => $participation_term_id,
        'excerpt' => 'Share feedback on your experience of care and treatment at SPMHS.',
        'flexi_rows' => matrix_seed_get_involved_page_rows(
            'Service User Experience Survey',
            'Service User Experience Survey',
            'At St Patrick\'s Mental Health Services (SPMHS), we aim to provide the highest quality of mental healthcare, promote mental health and advocate for the rights of people who experience mental health difficulties.',
            'participation',
            [
                matrix_seed_get_involved_content_block(
                    'Understanding your experience',
                    '<p>The surveys below are designed to give us a better understanding of how you view the care and treatment you have received during your engagement with SPMHS.</p>'
                    . '<p>We would be grateful if you could take a couple of minutes to complete the relevant survey. The survey does not identify you and all information provided will be treated as confidential. The information in this survey will be reviewed by senior management in SPMHS and will assist in improving and developing the services that we offer.</p>'
                ),
                matrix_seed_get_involved_content_block(
                    'Surveys',
                    '<ul><li><strong>Service user experience survey</strong> – If you recently received care and treatment as an inpatient, at home through our Homecare service, or through a combination of both.</li>'
                    . '<li><strong>Day service survey</strong> – If you have completed one of our day service programmes.</li>'
                    . '<li><strong>Dean Clinic survey</strong> – If you attended Dean Clinic appointments, either remotely or in person.</li></ul>',
                    'cream'
                ),
                matrix_seed_get_involved_content_block(
                    'Service user engagement',
                    '<p>If you\'d like to use your experiences and perspectives to inform how we develop our services, you may be interested in taking part in our service user engagement forums.</p>'
                    . '<p><a href="' . esc_url(matrix_seed_get_involved_url('service-user-participation')) . '">See more on service user engagement</a></p>'
                ),
            ],
            $hero_default_id,
            'participation'
        ),
    ],
];

$created = 0;
$updated = 0;

foreach ($pages as $seed_key => $page) {
    $existing = get_posts([
        'post_type' => 'get_involved',
        'post_status' => 'any',
        'posts_per_page' => 1,
        'meta_query' => [
            [
                'key' => '_matrix_seed_key',
                'value' => $seed_key,
            ],
        ],
    ]);

    $post_id = matrix_seed_ensure_get_involved([
        'seed_key' => $seed_key,
        'slug' => $page['slug'],
        'title' => $page['title'],
        'excerpt' => $page['excerpt'],
        'section_term_id' => $page['section_term_id'],
        'featured_image_id' => $hero_default_id,
        'flexi_rows' => $page['flexi_rows'],
    ]);

    if ($post_id < 1) {
        WP_CLI::warning('Failed to seed get involved page: ' . $page['title']);
        continue;
    }

    if ($existing !== []) {
        $updated++;
        WP_CLI::log('Updated: ' . $page['title'] . ' (ID ' . $post_id . ') → ' . matrix_seed_get_involved_url($page['slug']));
    } else {
        $created++;
        WP_CLI::log('Created: ' . $page['title'] . ' (ID ' . $post_id . ') → ' . matrix_seed_get_involved_url($page['slug']));
    }
}

flush_rewrite_rules(false);

WP_CLI::success(sprintf(
    'Get Involved CPT seed complete. Created %d, updated %d. Total pages: %d.',
    $created,
    $updated,
    count($pages)
));
