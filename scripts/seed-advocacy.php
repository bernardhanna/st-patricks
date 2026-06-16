<?php

/**
 * Seed Advocacy landing page and youth advocacy sub-page.
 *
 * Sources:
 * - https://www.stpatricks.ie/advocacy
 * - https://www.stpatricks.ie/advocacy/advocacy-services
 * - https://www.stpatricks.ie/advocacy/advocacy-services/youth-advocacy
 * - https://www.stpatricks.ie/advocacy/public-education-anti-stigma-campaigns
 * - https://www.stpatricks.ie/advocacy/collaborative-efforts
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-advocacy.php
 */

require_once get_template_directory() . '/inc/migrate-functions.php';
require_once get_template_directory() . '/scripts/lib/page-seed-conventions.php';
require_once get_template_directory() . '/scripts/lib/family-lecture-series-seed.php';

$advocacy_id = matrix_seed_resolve_page_id_by_path('about-us/advocacy');
$advocacy_services_id = matrix_seed_resolve_page_id_by_path('advocacy-services');
$about_us_id = matrix_seed_resolve_page_id_by_path('about-us');
$youth_advocacy_id = matrix_seed_release_youth_advocacy_page_slug($advocacy_services_id);

if ($advocacy_id === 0 || $advocacy_services_id === 0 || $youth_advocacy_id === 0 || $about_us_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find one or more advocacy pages.');
    }

    exit(1);
}

if (! function_exists('matrix_seed_migrate_attachment')) {
    function matrix_seed_migrate_attachment(string $source_path): int
    {
        return matrix_migrate_attachment_id_for_source_path($source_path);
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

if (! function_exists('matrix_seed_post_url_by_old_path')) {
    function matrix_seed_post_url_by_old_path(string $old_path, string $fallback_slug = ''): string
    {
        return matrix_family_lecture_post_url($old_path, $fallback_slug);
    }
}

if (! function_exists('matrix_seed_family_lecture_series_cards')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function matrix_seed_family_lecture_series_cards(): array
    {
        $cards = [];

        foreach (matrix_family_lecture_series_items() as $item) {
            $cards[] = [
                'image' => matrix_seed_migrate_attachment($item['image']),
                'title' => $item['title'],
                'description' => $item['description'],
                'link' => [
                    'title' => $item['title'],
                    'url' => matrix_family_lecture_post_url($item['old_path'], $item['slug']),
                    'target' => '',
                ],
            ];
        }

        return $cards;
    }
}

if (! function_exists('matrix_seed_advocacy_urls')) {
    /**
     * @return array<string, string>
     */
    function matrix_seed_advocacy_urls(): array
    {
        $home = home_url('/');

        $youth_page_id = matrix_seed_resolve_page_id_by_path('advocacy-services/youth-advocacy');

        return [
            'home' => $home,
            'about_us' => home_url('/about-us/'),
            'advocacy' => get_permalink(matrix_seed_resolve_page_id_by_path('about-us/advocacy')) ?: home_url('/about-us/advocacy/'),
            'human_rights' => get_permalink(matrix_seed_resolve_page_id_by_path('human-rights-advocacy')) ?: home_url('/human-rights-advocacy/'),
            'public_education' => get_permalink(matrix_seed_resolve_page_id_by_path('public-education-anti-stigma-campaigns')) ?: home_url('/public-education-anti-stigma-campaigns/'),
            'collaborative_efforts' => get_permalink(matrix_seed_resolve_page_id_by_path('collaborative-efforts')) ?: home_url('/collaborative-efforts/'),
            'advocacy_services' => get_permalink(matrix_seed_resolve_page_id_by_path('advocacy-services')) ?: home_url('/advocacy-services/'),
            'youth_advocacy' => $youth_page_id > 0
                ? (get_permalink($youth_page_id) ?: home_url('/advocacy-services/youth-advocacy/'))
                : home_url('/advocacy-services/youth-advocacy/'),
            'family_lecture_adolescent' => matrix_seed_post_url_by_old_path(
                'media-centre/events/2022/february/family-lecture-series-adolescent-mental-health',
                'family-lecture-series-adolescent-mental-health'
            ),
            'child_mental_health_blog' => home_url('/no-stigma-child-mental-health/'),
            'service_user_participation' => home_url('/service-users-and-visitors/service-user-participation/'),
            'faqs' => home_url('/service-users-and-visitors/frequently-asked-questions-faqs/'),
            'referrals' => home_url('/make-a-referral/'),
            'no_stigma' => 'https://www.nostigma.ie',
        ];
    }
}

if (! function_exists('matrix_seed_advocacy_section_links')) {
    /**
     * @return array<int, array{link: array{title: string, url: string, target: string}}>
     */
    function matrix_seed_advocacy_section_links(): array
    {
        $urls = matrix_seed_advocacy_urls();

        return [
            ['link' => ['title' => 'Human Rights Advocacy', 'url' => $urls['human_rights'], 'target' => '']],
            ['link' => ['title' => 'Public Education & Anti-Stigma Campaigns', 'url' => $urls['public_education'], 'target' => '']],
            ['link' => ['title' => 'Collaborative Efforts', 'url' => $urls['collaborative_efforts'], 'target' => '']],
            ['link' => ['title' => 'Advocacy Services', 'url' => $urls['advocacy_services'], 'target' => '']],
        ];
    }
}

if (! function_exists('matrix_seed_advocacy_services_section_links')) {
    /**
     * @return array<int, array{link: array{title: string, url: string, target: string}}>
     */
    function matrix_seed_advocacy_services_section_links(): array
    {
        $urls = matrix_seed_advocacy_urls();

        return [
            ['link' => ['title' => 'Advocacy Services', 'url' => $urls['advocacy_services'], 'target' => '']],
            ['link' => ['title' => 'Youth Advocacy', 'url' => $urls['youth_advocacy'], 'target' => '']],
        ];
    }
}

if (! function_exists('matrix_seed_advocacy_useful_links_block')) {
    /**
     * @return array<string, mixed>
     */
    function matrix_seed_advocacy_useful_links_block(): array
    {
        return matrix_page_seed_strip_padding([
            'acf_fc_layout' => 'useful_links',
            'heading_tag' => 'h2',
            'heading' => 'In this section',
            'variant' => 'flexi',
            'links' => matrix_seed_advocacy_section_links(),
            'background_color' => '#F1F8F9',
            'heading_color' => '#1E244B',
            'link_color' => '#1E244B',
        ]);
    }
}

if (! function_exists('matrix_seed_advocacy_services_useful_links_block')) {
    /**
     * @return array<string, mixed>
     */
    function matrix_seed_advocacy_services_useful_links_block(): array
    {
        return matrix_page_seed_strip_padding([
            'acf_fc_layout' => 'useful_links',
            'heading_tag' => 'h2',
            'heading' => 'In this section',
            'variant' => 'flexi',
            'links' => matrix_seed_advocacy_services_section_links(),
            'background_color' => '#F1F8F9',
            'heading_color' => '#1E244B',
            'link_color' => '#1E244B',
        ]);
    }
}

if (! function_exists('matrix_seed_advocacy_cta_rows')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function matrix_seed_advocacy_cta_rows(): array
    {
        $urls = matrix_seed_advocacy_urls();

        return matrix_page_seed_strip_padding_from_rows([
            [
                'acf_fc_layout' => 'content_cta',
                'heading_tag' => 'h2',
                'heading' => 'Queries',
                'body' => '<p>For general queries, please call us. For more on mental health and our services, see our frequently asked questions (FAQs).</p><p><strong>01 249 3200</strong></p>',
                'button_link' => [
                    'title' => 'See our FAQs',
                    'url' => $urls['faqs'],
                    'target' => '',
                ],
                'background_type' => 'color',
                'background_color' => '#C6ECF4',
            ],
            [
                'acf_fc_layout' => 'content_cta',
                'heading_tag' => 'h2',
                'heading' => 'Referrals',
                'body' => '<p>Contact our Referral and Assessment Service for queries regarding referrals to our services.</p><p><strong>01 249 3635</strong></p>',
                'button_link' => [
                    'title' => 'See more from our referrals team',
                    'url' => $urls['referrals'],
                    'target' => '',
                ],
                'background_type' => 'color',
                'background_color' => '#CEF2EE',
            ],
        ]);
    }
}

if (! function_exists('matrix_seed_advocacy_hero_block')) {
    /**
     * @param array<int, array{breadcrumb_link: array{title: string, url: string, target: string}}> $breadcrumbs
     * @return array<string, mixed>
     */
    function matrix_seed_advocacy_hero_block(
        string $heading,
        string $intro,
        array $breadcrumbs,
        int $hero_image_id,
        array $primary_button = []
    ): array {
        return matrix_page_seed_strip_padding([
            'acf_fc_layout' => 'hero_with_breadcrumbs',
            'layout_style' => 'image_split',
            'show_breadcrumbs' => 1,
            'breadcrumb_source' => 'manual',
            'manual_breadcrumbs' => $breadcrumbs,
            'current_crumb_label' => $heading,
            'heading_tag' => 'h1',
            'heading' => $heading,
            'content' => '<p>' . esc_html($intro) . '</p>',
            'primary_button' => $primary_button,
            'hero_image' => $hero_image_id,
            'background_color' => '#C6ECF4',
            'breadcrumb_background_color' => '#F1F8F9',
            'heading_color' => '#08284B',
            'text_color' => '#08284B',
        ]);
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

if (! function_exists('matrix_seed_advocacy_video_showcase_block')) {
    /**
     * @return array<string, mixed>
     */
    function matrix_seed_advocacy_video_showcase_block(int $poster_image_id, string $youtube_url): array
    {
        return matrix_page_seed_strip_padding([
            'acf_fc_layout' => 'video_showcase',
            'heading_tag' => 'h2',
            'heading' => '',
            'intro' => '',
            'layout_style' => 'feature_single',
            'slides' => [
                [
                    'poster_image' => matrix_seed_build_image_field($poster_image_id, 'Youth Advocacy video'),
                    'video_source_type' => 'embed_url',
                    'video_embed_url' => $youtube_url,
                    'caption' => '',
                    'cta_link' => '',
                ],
            ],
            'section_background' => 'linear-gradient(135deg, #F6EDE0 0%, #F5F0E0 48%, #F4F5DE 100%)',
        ]);
    }
}

if (! function_exists('matrix_seed_advocacy_content_block')) {
    /**
     * @return array<string, mixed>
     */
    function matrix_seed_advocacy_content_block(
        string $heading,
        string $intro_text,
        string $content,
        int $image_id = 0,
        string $layout_style = 'image_left',
        string $background_color = '#FFFFFF'
    ): array {
        $has_image = $image_id > 0;

        return matrix_page_seed_strip_padding([
            'acf_fc_layout' => 'content',
            'heading' => $heading,
            'heading_tag' => 'h2',
            'accent_position' => 'below_heading',
            'intro_text' => $intro_text,
            'content' => $content,
            'image' => $has_image ? $image_id : '',
            'column_layout' => $has_image ? 'two_column' : 'one_column',
            'layout_style' => $has_image ? $layout_style : 'image_left',
            'image_height_mode' => 'match_text',
            'text_width' => $has_image ? 'constrained' : 'full',
            'background_type' => 'color',
            'background_color' => $background_color,
        ]);
    }
}

if (! function_exists('matrix_seed_advocacy_landing_rows')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function matrix_seed_advocacy_landing_rows(): array
    {
        $urls = matrix_seed_advocacy_urls();

        $breadcrumbs = [
            ['breadcrumb_link' => ['title' => 'Home', 'url' => $urls['home'], 'target' => '']],
            ['breadcrumb_link' => ['title' => 'Who we are', 'url' => $urls['about_us'], 'target' => '']],
        ];

        $hero_intro = 'We are committed to advocating at a national level for a society in which the rights of those experiencing mental health difficulties are acknowledged and in which the stigma attached to mental health issues is combatted.';

        $intro_body = '<p>To address this issue and increase awareness of mental health, we produce and distribute information leaflets on the subject, while we also use print and broadcast media interviews to advocate on behalf of those with mental health difficulties.</p>'
            . '<p>We have continued to develop constructive relationships with the relevant authorities such as the Department of Health, the Health Service Executive and the Mental Health Commission. In conjunction with three other organisations, we helped to form the Independent Health Service Providers\' Group, later becoming its representative on the Independent Monitoring Group (IMG) which champions the Vision for Change initiative.</p>'
            . '<p>The Mental Health Commission has recommended that creative ways of involving the independent/private sector in public sector (mental health) projects should be explored. For our part, we will continue to explore every available avenue in our drive to promote a better public understanding of mental health in Ireland and we have established links with a number of advocacy and service user groups towards this end.</p>';

        $link_cards = [
            [
                'title' => 'Human rights advocacy',
                'description' => 'See more on human rights advocacy',
                'url' => $urls['human_rights'],
                'image_id' => matrix_seed_migrate_attachment('/media/1782/st-patricks-mental-health-services-advocay.jpg'),
                'tone' => 'bg1',
            ],
            [
                'title' => 'Public awareness campaigns',
                'description' => 'Learn more about our campaigns',
                'url' => $urls['public_education'],
                'image_id' => matrix_seed_migrate_attachment('/media/1783/st-patricks-mental-health-multidisciplinary-advocacy.jpg'),
                'tone' => 'bg2',
            ],
            [
                'title' => 'Collaborative efforts',
                'description' => 'Explore our mental health partnerships',
                'url' => $urls['collaborative_efforts'],
                'image_id' => matrix_seed_migrate_attachment('/media/3266/partnerships-page-banner.png'),
                'tone' => 'bg3',
            ],
            [
                'title' => 'Advocacy services',
                'description' => 'Learn about self-advocacy and independent advocacy',
                'url' => $urls['advocacy_services'],
                'image_id' => matrix_seed_migrate_attachment('/media/3707/youth-advocacy.png'),
                'tone' => 'bg4',
            ],
            [
                'title' => 'Service user participation',
                'description' => 'See more about service user engagement',
                'url' => $urls['service_user_participation'],
                'image_id' => matrix_seed_migrate_attachment('/media/1734/st-patricks-mental-health-services-suas.jpg'),
                'tone' => 'bg1',
            ],
        ];

        $about_links = [];
        foreach ($link_cards as $card) {
            $about_links[] = [
                'icon' => '',
                'image_url' => matrix_seed_attachment_url((int) $card['image_id']),
                'title' => $card['title'],
                'description' => $card['description'],
                'link' => [
                    'title' => $card['title'],
                    'url' => $card['url'],
                    'target' => '',
                ],
                'card_tone' => $card['tone'],
            ];
        }

        return array_merge([
            matrix_seed_advocacy_hero_block(
                'Advocacy',
                $hero_intro,
                $breadcrumbs,
                matrix_seed_migrate_attachment('/media/1732/st-patricks-mental-health-services-advocay-banner-min.jpg')
            ),
            matrix_seed_advocacy_useful_links_block(),
            matrix_seed_advocacy_content_block(
                'Our advocacy work',
                '',
                $intro_body,
                0,
                'image_left',
                '#FFFFFF'
            ),
            matrix_page_seed_strip_padding([
                'acf_fc_layout' => 'about_links_grid',
                'heading_tag' => 'h2',
                'heading_text' => 'Explore advocacy at SPMHS',
                'intro_text' => '',
                'links' => $about_links,
                'bg_color' => '#E9E2F7',
                'heading_color' => '#1E244B',
                'intro_color' => '#4A4B37',
                'columns' => '3',
            ]),
            matrix_page_seed_strip_padding([
                'acf_fc_layout' => 'content_cta',
                'heading_tag' => 'h2',
                'heading' => 'Help to end mental health stigma',
                'body' => '<p>Support our #NoStigma campaign and help reimagine a society without mental health stigma and discrimination.</p>',
                'button_link' => [
                    'title' => 'No Stigma',
                    'url' => $urls['no_stigma'],
                    'target' => '_blank',
                ],
                'background_type' => 'color',
                'background_color' => '#CEF2EE',
            ]),
        ], matrix_seed_advocacy_cta_rows());
    }
}

if (! function_exists('matrix_seed_youth_advocacy_rows')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function matrix_seed_youth_advocacy_rows(): array
    {
        $urls = matrix_seed_advocacy_urls();

        $breadcrumbs = [
            ['breadcrumb_link' => ['title' => 'Home', 'url' => $urls['home'], 'target' => '']],
            ['breadcrumb_link' => ['title' => 'Who we are', 'url' => $urls['about_us'], 'target' => '']],
            ['breadcrumb_link' => ['title' => 'Advocacy', 'url' => $urls['advocacy'], 'target' => '']],
            ['breadcrumb_link' => ['title' => 'Advocacy Services', 'url' => $urls['advocacy_services'], 'target' => '']],
        ];

        $hero_intro = 'Our Willow Grove Adolescent Unit supports youth advocacy to ensure young people\'s voices are heard in all aspects of their care.';

        $youth_body = '<p>In Willow Grove, we believe youth advocacy is crucial to help people help themselves and to develop strength and resilience.</p>'
            . '<p>Youth advocacy is a process of supporting and enabling young people to:</p>'
            . '<ul>'
            . '<li>express their views and concerns</li>'
            . '<li>access information and services</li>'
            . '<li>defend and promote their rights and responsibilities.</li>'
            . '</ul>'
            . '<p>As part of this, all young people receiving inpatient care and treatment in Willow Grove can access an independent advocate through Youth Advocacy Programmes Ireland (YAP).</p>'
            . '<p>YAP aims to empower young people and their families and to address the needs of the young person within their family and local community. It supports young people to reach their goals by making the improvements they consider necessary.</p>';

        $advocate_intro_body = '<p>An independent advocate is someone who provides advocacy support when you need it and who is separate to the supports or services you are accessing.</p>'
            . '<p>Having an independent advocate can be helpful in all kinds of situations where you may:</p>'
            . '<ul>'
            . '<li>find it difficult to make your views known</li>'
            . '<li>wish to discuss your options</li>'
            . '<li>need help or advice with decision-making</li>'
            . '<li>need other people to listen to you and take your views into account.</li>'
            . '</ul>';

        $advocate_role_body = '<p>For example, an independent advocate will:</p>'
            . '<ul>'
            . '<li>listen to your views and concerns</li>'
            . '<li>help you access information</li>'
            . '<li>help you to explore your options and rights, without advising you in any way</li>'
            . '<li>give you information to make informed decisions</li>'
            . '<li>help you contact relevant people, or contact them for you</li>'
            . '<li>accompany and support you in meetings and appointments.</li>'
            . '</ul>'
            . '<p>An advocate will not give you their personal opinion, solve problems or make decisions for you, or make judgements about you.</p>';

        $youth_advocacy_image_id = matrix_seed_migrate_attachment('/media/3707/youth-advocacy.png');

        $yap_body = '<p>YAP offers an independent mental health advocate service in Willow Grove. This takes place:</p>'
            . '<ul>'
            . '<li>in group format every fortnight</li>'
            . '<li>through individual sessions when required or requested by the young person.</li>'
            . '</ul>'
            . '<p>Through this service, an independent advocate can support you to understand the service provided to you; to strengthen your participation in your care and treatment; to express your views; and to make informed decisions.</p>'
            . '<p>YAP is separate to Willow Grove and St Patrick\'s Mental Health Services. This means you can be reassured that it is an unbiased and non-judgmental service. Anything you discuss is confidential between you and the advocate, unless you consent to the advocate discussing it with a named person. If the advocate has any concerns about your safety or the safety of others, they must let a member of your team know, and you will, of course, be informed of this at the time.</p>';

        $access_body = '<p>If you would like to be referred to an independent advocate through YAP, you can refer yourself to the advocate directly. The advocate attends Willow Grove every two weeks, and you can meet them as little or as often as you like.</p>'
            . '<p>You can also ask the Willow Grove team for more information about the independent advocacy service. You can also ask the team to contact the advocacy service if you need an individual session with the advocate.</p>';

        return array_merge([
            matrix_seed_advocacy_hero_block(
                'Youth Advocacy',
                $hero_intro,
                $breadcrumbs,
                matrix_seed_migrate_attachment('/media/3707/youth-advocacy.png')
            ),
            matrix_seed_advocacy_services_useful_links_block(),
            matrix_seed_advocacy_content_block(
                'Youth advocacy at Willow Grove',
                '',
                $youth_body,
                0,
                'image_left',
                '#FFFFFF'
            ),
            matrix_seed_advocacy_video_showcase_block(
                $youth_advocacy_image_id,
                'https://www.youtube.com/watch?v=KyUgzBV3T5k'
            ),
            matrix_seed_advocacy_content_block(
                'What is an independent advocate?',
                '',
                $advocate_intro_body,
                $youth_advocacy_image_id,
                'image_left',
                '#FBFAF7'
            ),
            matrix_seed_advocacy_content_block(
                '',
                '',
                $advocate_role_body,
                $youth_advocacy_image_id,
                'image_right',
                '#FFFFFF'
            ),
            matrix_seed_advocacy_content_block(
                'How does the YAP service work in Willow Grove?',
                '',
                $yap_body,
                0,
                'image_left',
                '#FFFFFF'
            ),
            matrix_seed_advocacy_content_block(
                'How can you access the YAP service?',
                '',
                $access_body,
                matrix_seed_migrate_attachment('/media/1734/st-patricks-mental-health-services-suas.jpg'),
                'image_right',
                '#FBFAF7'
            ),
            matrix_page_seed_strip_padding([
                'acf_fc_layout' => 'related_cards',
                'heading_tag' => 'h2',
                'heading' => 'Related',
                'intro_text' => '',
                'cards' => array_merge(matrix_seed_family_lecture_series_cards(), [
                    [
                        'image' => matrix_seed_migrate_attachment('/media/1734/st-patricks-mental-health-services-suas.jpg'),
                        'title' => 'How can we support children and young people\'s mental health?',
                        'description' => 'Read practical guidance on supporting young people\'s mental health.',
                        'link' => [
                            'title' => 'How can we support children and young people\'s mental health?',
                            'url' => $urls['child_mental_health_blog'],
                            'target' => '',
                        ],
                    ],
                ]),
                'background_color' => '#FBFAF7',
                'columns' => '3',
            ]),
        ], matrix_seed_advocacy_cta_rows());
    }
}

if (! function_exists('matrix_seed_advocacy_patch_subpage_breadcrumbs')) {
    function matrix_seed_advocacy_patch_subpage_breadcrumbs(int $post_id, string $advocacy_url, bool $include_about_us = false): void
    {
        $rows = get_field('flexible_content_blocks', $post_id);

        if (! is_array($rows) || $rows === []) {
            return;
        }

        $about_us_url = home_url('/about-us/');
        $changed = false;

        foreach ($rows as $row_index => $row) {
            if (($row['acf_fc_layout'] ?? '') !== 'hero_with_breadcrumbs') {
                continue;
            }

            $manual = $row['manual_breadcrumbs'] ?? [];

            if (! is_array($manual)) {
                continue;
            }

            foreach ($manual as $crumb_index => $crumb) {
                $title = trim((string) ($crumb['breadcrumb_link']['title'] ?? ''));

                if ($title === 'Advocacy') {
                    $rows[$row_index]['manual_breadcrumbs'][$crumb_index]['breadcrumb_link']['url'] = $advocacy_url;
                    $changed = true;
                }

                if ($include_about_us && $title === 'Who we are') {
                    $rows[$row_index]['manual_breadcrumbs'][$crumb_index]['breadcrumb_link']['url'] = $about_us_url;
                    $changed = true;
                }
            }
        }

        if (! $changed) {
            return;
        }

        update_field('flexible_content_blocks', $rows, $post_id);
    }
}

if (! function_exists('matrix_seed_advocacy_patch_useful_links')) {
    function matrix_seed_advocacy_patch_useful_links(int $post_id): void
    {
        $rows = get_field('flexible_content_blocks', $post_id);

        if (! is_array($rows) || $rows === []) {
            return;
        }

        foreach ($rows as $index => $row) {
            if (($row['acf_fc_layout'] ?? '') !== 'useful_links') {
                continue;
            }

            $rows[$index] = matrix_seed_advocacy_useful_links_block();
            update_field('flexible_content_blocks', $rows, $post_id);

            return;
        }
    }
}

if (! function_exists('matrix_seed_advocacy_patch_public_education_youth_card')) {
    function matrix_seed_advocacy_patch_public_education_youth_card(int $post_id, string $youth_advocacy_url): void
    {
        $rows = get_field('flexible_content_blocks', $post_id);

        if (! is_array($rows) || $rows === []) {
            return;
        }

        foreach ($rows as $index => $row) {
            if (($row['acf_fc_layout'] ?? '') !== 'related_cards') {
                continue;
            }

            $cards = $row['cards'] ?? [];

            if (! is_array($cards) || $cards === []) {
                continue;
            }

            foreach ($cards as $card_index => $card) {
                if (stripos((string) ($card['title'] ?? ''), 'youth') === false) {
                    continue;
                }

                $cards[$card_index]['link'] = [
                    'title' => (string) ($card['title'] ?? 'Youth Advocacy'),
                    'url' => $youth_advocacy_url,
                    'target' => '',
                ];
            }

            $rows[$index]['cards'] = $cards;
            update_field('flexible_content_blocks', $rows, $post_id);

            return;
        }
    }
}

if (! function_exists('matrix_seed_advocacy_patch_advocacy_services_related_card')) {
    function matrix_seed_advocacy_patch_advocacy_services_related_card(int $post_id, string $youth_advocacy_url): void
    {
        $rows = get_field('flexible_content_blocks', $post_id);

        if (! is_array($rows) || $rows === []) {
            return;
        }

        $youth_image_id = matrix_seed_migrate_attachment('/media/3707/youth-advocacy.png');

        foreach ($rows as $index => $row) {
            if (($row['acf_fc_layout'] ?? '') !== 'related_cards') {
                continue;
            }

            $cards = $row['cards'] ?? [];

            if (! is_array($cards) || $cards === []) {
                continue;
            }

            foreach ($cards as $card_index => $card) {
                if (stripos((string) ($card['title'] ?? ''), 'youth') === false) {
                    continue;
                }

                $cards[$card_index]['image'] = $youth_image_id;
                $cards[$card_index]['description'] = 'Learn about youth advocacy at Willow Grove.';
                $cards[$card_index]['link'] = [
                    'title' => 'Youth Advocacy',
                    'url' => $youth_advocacy_url,
                    'target' => '',
                ];
            }

            $rows[$index]['cards'] = $cards;
            update_field('flexible_content_blocks', $rows, $post_id);

            return;
        }
    }
}

if (! function_exists('matrix_seed_save_advocacy_page')) {
    function matrix_seed_save_advocacy_page(int $post_id, array $flexi_rows): void
    {
        update_field('hero_content_blocks', [], $post_id);
        update_field('flexible_content_blocks', $flexi_rows, $post_id);
        update_post_meta($post_id, '_matrix_migrate_restyle_skip', '1');
        update_post_meta($post_id, '_matrix_migrate_restyled', 'manual');
    }
}

$urls = matrix_seed_advocacy_urls();

matrix_seed_save_advocacy_page($advocacy_id, matrix_seed_advocacy_landing_rows());
matrix_seed_save_advocacy_page($youth_advocacy_id, matrix_seed_youth_advocacy_rows());

foreach ([$advocacy_services_id, 1374, 1375, 1323] as $subpage_id) {
    matrix_seed_advocacy_patch_subpage_breadcrumbs((int) $subpage_id, $urls['advocacy']);
    matrix_seed_advocacy_patch_useful_links((int) $subpage_id);
}

matrix_seed_advocacy_patch_advocacy_services_related_card($advocacy_services_id, $urls['youth_advocacy']);
matrix_seed_advocacy_patch_public_education_youth_card(1375, $urls['youth_advocacy']);

if (class_exists('WP_CLI')) {
    WP_CLI::success(sprintf(
        'Seeded advocacy landing (ID %d) and youth advocacy (ID %d). Updated breadcrumbs on advocacy sub-pages.',
        $advocacy_id,
        $youth_advocacy_id
    ));
}
