<?php

/**
 * Reseed Collaborative Efforts (/collaborative-efforts/) with designed flexi blocks.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-collaborative-efforts.php
 */

require_once get_template_directory() . '/inc/migrate-functions.php';

$post_id = (int) (get_page_by_path('collaborative-efforts')?->ID ?? 0);

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find Collaborative Efforts page.');
    }

    exit(1);
}

if (! function_exists('matrix_seed_migrate_attachment')) {
    function matrix_seed_migrate_attachment(string $source_path): int
    {
        return matrix_migrate_attachment_id_for_source_path($source_path);
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
$advocacy_url = get_permalink(271) ?: home_url('/about-us/advocacy/');
$about_url = home_url('/about-us/');
$wmhn_url = get_permalink(get_page_by_path('women-s-mental-health-network')) ?: home_url('/women-s-mental-health-network/');
$youth_advocacy_url = home_url('/youth-advocacy-service/');
$women_blog_url = home_url('/media-centre/blogs-articles/');
$first_fortnight_news = home_url('/media-centre/news/2020/december/firstfortnightfestival2021/');
$self_harm_blog = home_url('/media-centre/blogs-articles/2021/march/marking-self-injury-awareness-day/');

$section_padding = [
    ['screen_size' => 'mob', 'padding_top' => '3', 'padding_bottom' => '3'],
    ['screen_size' => 'lg', 'padding_top' => '6.25', 'padding_bottom' => '6.25'],
];

$accordion_section_padding = [
    ['screen_size' => 'mob', 'padding_top' => '1', 'padding_bottom' => '3'],
    ['screen_size' => 'lg', 'padding_top' => '1', 'padding_bottom' => '6.25'],
];

$hero_intro = 'We deeply value partnerships with others working towards the same goals.';

$shared_goals_body = '<p>At St Patrick\'s Mental Health Services (SPMHS), we are <a href="' . esc_url($about_url) . '">committed to promoting mental wellbeing</a> and mental health awareness, to <a href="https://www.nostigma.ie" target="_blank" rel="noopener noreferrer">ending stigma</a> associated with mental health difficulties, and to advancing a human rights-based approach to mental healthcare.</p>'
    . '<p>To do this, we believe a collaborative approach is essential, and place huge importance on links and partnerships with other organisations sharing our objectives, both nationally and internationally.</p>';

$projects_intro = '<p>Below, you can find out more about some of the collaborative projects and initiatives we are involved with.</p>';

$wmhn_body = '<p>We have come together with the National Women\'s Council (NWC) to develop a Women\'s Mental Health Network (WMHN). This is a network of people and organisations with a committed interest in <a href="' . esc_url($women_blog_url) . '" target="_blank" rel="noopener noreferrer">women\'s mental health</a> issues.</p>'
    . '<p>The WMHN has two aims:</p>'
    . '<ul><li>To provide a forum for information-sharing and networking</li><li>To advance interdisciplinary and multi-agency collaboration on women\'s mental health issues.</li></ul>'
    . '<p>We share information through newsletters and online updates, and organise education and networking events.</p>'
    . '<p><a href="' . esc_url($wmhn_url) . '">Learn more about the WMHN and join the network</a>.</p>';

$npc_body = '<p>In Ireland, the National Parent\'s Council (NPC) is the "only representative organisation for parents of children in primary or early education". It works to empower parents to support their children throughout their early and primary school years and believes that children should have a say in issues affecting their educational lives.</p>'
    . '<p>We have partnered with the NPC to create mental health awareness training for parents of <a href="https://www.walkinmyshoes.ie/schools/primary-school" target="_blank" rel="noopener noreferrer">primary school children</a>. This programme supports parents to encourage and promote positive mental health and wellbeing in their children. It also explores how building resilience in children helps them to manage and cope with the day-to-day stresses of life as they occur. Training takes place around the country; <a href="http://www.npc.ie/training-and-resources/training-we-offer/supporting-parents-to-support-their-childrens-mental-health-and-wellbeing" target="_blank" rel="noopener noreferrer">you can find more information here</a>.</p>'
    . '<p>For more general information about supporting your child\'s mental health and wellbeing, you may <a href="https://www.npc.ie/images/uploads/downloads/MentalHealth.PDF" target="_blank" rel="noopener noreferrer">find this brochure helpful</a>.</p>';

$mhr_body = '<p>Mental Health Reform is Ireland\'s leading national coalition on mental health, campaigning for the progressive reform of mental health services and supports in Ireland.</p>'
    . '<p>We are proud to be an associate member of Mental Health Reform and to support its important work.</p>'
    . '<p>You can <a href="https://www.mentalhealthreform.ie/about-us/" target="_blank" rel="noopener noreferrer">learn more about Mental Health Reform\'s work here</a>.</p>';

$cra_body = '<p>Founded in 1995, the Children\'s Rights Alliance unites over 100 members working together to make Ireland one of the best places in the world to be a child. It works to change the lives of all children in Ireland by making sure that their rights are respected and protected in our laws, policies and services.</p>'
    . '<p>We stand as a member of the Children\'s Rights Alliance, and proudly support its work.</p>'
    . '<p>You can <a href="https://www.childrensrights.ie/" target="_blank" rel="noopener noreferrer">learn more about the Children\'s Rights Alliance here</a>.</p>'
    . '<p>The Children\'s Rights Alliance developed a <em>Know Your Rights</em> booklet on children\'s rights with the Irish Council for Civil Liberties, and members of the SPMHS <a href="' . esc_url($youth_advocacy_url) . '">Youth Empowerment Service</a> took part in its development. <a href="https://www.childrensrights.ie/sites/default/files/submissions_reports/files/ICCL_KYR_ChildrensRights_Colour(Spreads).pdf" target="_blank" rel="noopener noreferrer">You can access the booklet here</a>.</p>';

$first_fortnight_body = '<p>First Fortnight is a charitable organisation that challenges mental health stigma and prejudice through arts and cultural action.</p>'
    . '<p>To this end, First Fortnight holds an annual, national mental health arts and cultural festival, with events taking place around the country.</p>'
    . '<p>We have been proud to be a presenting partner for the festival and have <a href="' . esc_url($first_fortnight_news) . '">hosted numerous events</a> during the festival, including film screenings, plays and <em>Cistin</em>, a night of music, poetry and storytelling.</p>'
    . '<p>You can <a href="https://www.firstfortnight.ie/" target="_blank" rel="noopener noreferrer">find more information about First Fortnight and its important work here</a>.</p>';

$pieta_body = '<p>We have been joining forces with <a href="https://www.pieta.ie/" target="_blank" rel="noopener noreferrer">Pieta</a> since 2016 to raise awareness and understanding of self-harm by holding a Self-Harm Awareness Conference to coincide with Self-injury Awareness Day.</p>'
    . '<p>You can <a href="' . esc_url($self_harm_blog) . '">look back at past conferences here</a>.</p>';

$seechange_body = '<p><a href="https://seechange.ie/" target="_blank" rel="noopener noreferrer">SeeChange</a> is the national partnership to end mental health stigma, which includes the Green Ribbon campaign during May. In May, you can pick up your green ribbon to support the campaign at participating outlets, including major Irish Rail stations, Eir and Boots Stores, AIB branches and participating libraries.</p>'
    . '<p>We are an active supporter of the SeeChange partnership, and host special events onsite during Green Ribbon month.</p>';

$ccp_body = '<p><a href="https://www.childcareinpractice.org/about-us" target="_blank" rel="noopener noreferrer">Child Care in Practice</a> is a leading international peer review journal of multidisciplinary child care practice. Publishing the best of both practice and research from all professions and disciplines involved in the provision of children\'s services, Child Care in Practice fulfils a special role in bringing together the many and varied groups which make up this vital field. Published quarterly, the journal is peer-reviewed to ensure the highest of standards.</p>'
    . '<p>We are a supporting partner of Child Care in Practice.</p>';

$gallery_paths = [
    '/media/2771/shac-website-1.png',
    '/media/2696/wmhn-event-29-11-2019-in-st-patrick-s-hospital-_17-copy.jpg',
    '/media/2702/wmhn-event-29-11-2019-in-st-patrick-s-hospital-_59.jpg',
    '/media/2445/seechange-movie-quiz-night-michael-doherty.jpg',
    '/media/2367/st-patricks-pieta-house-educate-on-self-harm.jpg',
    '/media/2449/10_01_19_cistin_st_pats_blaithin_carney_kieran_frost-0005.jpg',
    '/media/2450/10_01_19_cistin_st_pats_catherine_brophy_kieran_frost-0009.jpg',
    '/media/2451/10_01_19_cistin_st_pats_thunderclap_murphy_kieran_frost-0036.jpg',
    '/media/2452/10_01_19_cistin_st_pats_laura_ryder_ampersand_kieran_frost-0022.jpg',
    '/media/2453/10_01_19_cistin_st_pats_sacreblues_band_kieran_frost-0029.jpg',
];

$gallery_slides = [];

foreach ($gallery_paths as $path) {
    $attachment_id = matrix_seed_migrate_attachment($path);

    if ($attachment_id < 1) {
        continue;
    }

    $gallery_slides[] = [
        'image' => $attachment_id,
        'has_video' => 0,
        'video_source_type' => 'youtube_vimeo',
        'video_embed_url' => '',
        'local_video_file' => '',
        'video_link' => '',
    ];
}

$advocacy_links = [
    ['link' => ['title' => 'Human Rights Advocacy', 'url' => get_permalink(1374) ?: home_url('/human-rights-advocacy/'), 'target' => '']],
    ['link' => ['title' => 'Public Education & Anti-Stigma Campaigns', 'url' => get_permalink(get_page_by_path('public-education-anti-stigma-campaigns')) ?: home_url('/public-education-anti-stigma-campaigns/'), 'target' => '']],
    ['link' => ['title' => 'Collaborative Efforts', 'url' => get_permalink($post_id), 'target' => '']],
    ['link' => ['title' => 'Advocacy Services', 'url' => get_permalink(1321) ?: home_url('/advocacy-services/'), 'target' => '']],
];

$flexi_rows = [
    [
        'acf_fc_layout' => 'hero_with_breadcrumbs',
        'layout_style' => 'image_split',
        'show_breadcrumbs' => 1,
        'breadcrumb_source' => 'manual',
        'manual_breadcrumbs' => [
            ['breadcrumb_link' => ['title' => 'Home', 'url' => $home, 'target' => '']],
            ['breadcrumb_link' => ['title' => 'Advocacy', 'url' => $advocacy_url, 'target' => '']],
        ],
        'current_crumb_label' => 'Collaborative Efforts',
        'heading_tag' => 'h1',
        'heading' => 'Collaborative Efforts',
        'content' => '<p>' . esc_html($hero_intro) . '</p>',
        'primary_button' => '',
        'hero_image' => matrix_seed_migrate_attachment('/media/3266/partnerships-page-banner.png'),
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
        'text_max_width' => 'default',
    ],
    [
        'acf_fc_layout' => 'useful_links',
        'heading_tag' => 'h2',
        'heading' => 'In this section',
        'variant' => 'flexi',
        'links' => $advocacy_links,
        'background_color' => '#F1F8F9',
        'padding_settings' => [
            ['screen_size' => 'mob', 'padding_top' => '1.5', 'padding_bottom' => '1.5'],
            ['screen_size' => 'lg', 'padding_top' => '2', 'padding_bottom' => '2'],
        ],
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Shared goals',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'intro_text' => '',
        'content' => $shared_goals_body,
        'image' => '',
        'column_layout' => 'one_column',
        'layout_style' => 'image_left',
        'text_width' => 'wide',
        'background_type' => 'color',
        'background_color' => '#FFFFFF',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Collaborative projects and initiatives',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'intro_text' => $projects_intro,
        'content' => '',
        'image' => '',
        'column_layout' => 'one_column',
        'layout_style' => 'image_left',
        'text_width' => 'wide',
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
            matrix_seed_accordion_item('Women\'s Mental Health Network', $wmhn_body, true),
            matrix_seed_accordion_item('National Parent\'s Council training', $npc_body),
            matrix_seed_accordion_item('Mental Health Reform', $mhr_body),
            matrix_seed_accordion_item('Children\'s Rights Alliance', $cra_body),
            matrix_seed_accordion_item('First Fortnight', $first_fortnight_body),
            matrix_seed_accordion_item('Pieta', $pieta_body),
            matrix_seed_accordion_item('SeeChange', $seechange_body),
            matrix_seed_accordion_item('Child Care in Practice', $ccp_body),
        ],
        'padding_settings' => $accordion_section_padding,
    ],
];

if ($gallery_slides !== []) {
    $flexi_rows[] = [
        'acf_fc_layout' => 'story_slider',
        'show_heading' => 1,
        'heading_tag' => 'h2',
        'heading_text' => 'Partnership highlights',
        'intro_text' => '',
        'slides' => $gallery_slides,
        'section_background' => '#FFFFFF',
        'padding_settings' => $section_padding,
    ];
}

update_field('hero_content_blocks', [], $post_id);
update_field('flexible_content_blocks', $flexi_rows, $post_id);
update_post_meta($post_id, '_matrix_migrate_restyle_skip', '1');
update_post_meta($post_id, '_matrix_migrate_restyled', 'manual');

if (class_exists('WP_CLI')) {
    WP_CLI::success(sprintf(
        'Reseeded Collaborative Efforts (ID %d) with %d flexi blocks.',
        $post_id,
        count($flexi_rows)
    ));
}
