<?php

/**
 * Seed Policies and Publications from stpatricks.ie.
 *
 * Source: https://www.stpatricks.ie/about-us/policies-and-publications
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-policies-and-publications.php
 */

$post_id = (int) (get_page_by_path('about-us/policies-and-publications')?->ID ?? 0);

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at about-us/policies-and-publications.');
    }

    exit(1);
}

if (! function_exists('matrix_seed_live_url')) {
    function matrix_seed_live_url(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return 'https://www.stpatricks.ie' . (str_starts_with($path, '/') ? $path : '/' . $path);
    }
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

if (! function_exists('matrix_seed_policies_pdf_doc')) {
    function matrix_seed_policies_pdf_doc(string $title, string $path): array
    {
        return [
            'title' => $title,
            'document_link' => [
                'title' => 'PDF opens in a new tab',
                'url' => matrix_seed_live_url($path),
                'target' => '_blank',
            ],
        ];
    }
}

if (! function_exists('matrix_seed_policies_external')) {
    function matrix_seed_policies_external(string $title, string $path): array
    {
        return [
            'title' => $title,
            'link' => [
                'title' => $title,
                'url' => matrix_seed_live_url($path),
                'target' => '_blank',
            ],
        ];
    }
}

if (! function_exists('matrix_seed_policies_external_links')) {
    /**
     * @param array<int, array{0: string, 1: string}> $items
     * @return array<int, array<string, mixed>>
     */
    function matrix_seed_policies_external_links(array $items): array
    {
        $links = [];

        foreach ($items as $item) {
            $links[] = matrix_seed_policies_external($item[0], $item[1]);
        }

        return $links;
    }
}

$home = home_url('/');
$about_us_url = home_url('/about-us/');
$policies_url = get_permalink($post_id) ?: home_url('/about-us/policies-and-publications/');
$data_protection_url = home_url('/data-protection-policy/');
$privacy_notice_url = home_url('/cookie-privacy-policy/');
$our_strategy_url = home_url('/about-us/our-present-and-future/');
$faqs_url = home_url('/service-users-and-visitors/frequently-asked-questions-faqs/');
$referrals_url = home_url('/make-a-referral/');

$section_padding = [
    ['screen_size' => 'mob', 'padding_top' => '3', 'padding_bottom' => '3'],
    ['screen_size' => 'lg', 'padding_top' => '6.25', 'padding_bottom' => '6.25'],
];

$hero_image_id = matrix_seed_import_scraped_image(
    'https://www.stpatricks.ie/media/1869/st-patricks-mental-health-services-garden.jpg?width=1200&height=800&mode=crop',
    'Policies and Publications hero',
    'policies-publications-scraped-hero'
);

$hero_intro = 'See our reports, policies, and other publications. You can find our key organisational policies and statements here in St Patrick\'s Mental Health Services (SPMHS) below.';

$policies_items = [
    [
        'title' => 'Declarations and charters',
        'starts_open' => 1,
        'content_rows' => [
            [
                'row_type' => 'pdf_grid',
                'pdf_documents' => [
                    matrix_seed_policies_pdf_doc(
                        'Charter of Patient and Family Rights and Responsibilities',
                        '/media/1241/charter-of-patient-and-family-rights-and-responsibilities.pdf'
                    ),
                    matrix_seed_policies_pdf_doc(
                        'The International Declaration on Youth Mental Health',
                        '/media/1239/ymh-declaration-full-version-september-2011.pdf'
                    ),
                    matrix_seed_policies_pdf_doc(
                        'The International Declaration on Youth Mental Health Summary',
                        '/media/1250/international-declaration-on-youth-mental-health-new-leaflet-sept-2011.pdf'
                    ),
                ],
            ],
        ],
    ],
    [
        'title' => 'Hospital policies',
        'starts_open' => 0,
        'content_rows' => [
            [
                'row_type' => 'external_links',
                'external_links' => matrix_seed_policies_external_links([
                    ['External Privacy Notice', $privacy_notice_url],
                    ['Mechanical Restraint Reduction Policy', '/media/3619/mechanical-restraint-reduction-policy.pdf'],
                    ['Physical Restraint Reduction Policy', '/media/3573/physical-restraint-reduction-strategy-and-policy.pdf'],
                    ['Website Privacy Policy', $privacy_notice_url],
                ]),
            ],
            [
                'row_type' => 'text',
                'content' => '<p>You can find reports of annual activity in relation to our mechanical and physical restraint policies in the &ldquo;Strategies and reports&rdquo; section further below.</p>',
            ],
        ],
    ],
    [
        'title' => 'Child protection and safeguarding statements',
        'starts_open' => 0,
        'content_rows' => [
            [
                'row_type' => 'text',
                'content' => '<p>It is the policy of SPMHS to safeguard child welfare. You can see our full Child Protection and Welfare Statement <a href="' . esc_url(matrix_seed_live_url('/about-us/policies-and-publications/child-protection-statement-of-st-patrick-s-mental-health-services')) . '" target="_blank" rel="noopener noreferrer">here</a>.</p>'
                    . '<p>SPMHS provides mental healthcare to young people aged between 12 and 17 on an inpatient and an outpatient basis. We have developed a Child Safeguarding Statement in line with the Children First Act 2015. You can find our Child Safeguarding Statement <a href="' . esc_url(matrix_seed_live_url('/about-us/policies-and-publications/child-safeguarding-statement')) . '" target="_blank" rel="noopener noreferrer">here</a>.</p>',
            ],
            [
                'row_type' => 'external_links',
                'external_links' => matrix_seed_policies_external_links([
                    ['Child Safeguarding Statement, St Patrick\'s Mental Health Services', '/media/4215/css-overarching.pdf'],
                    ['Child Safeguarding Statement, Willow Grove Adolescent Unit', '/media/4216/css-wgau.pdf'],
                    ['Child Safeguarding Statement, Dean Clinic, Dublin', '/media/4217/css-dean-clinic-dublin.pdf'],
                    ['Child Safeguarding Statement, Dean Clinic, Cork', '/media/4218/css-dean-clinic-cork.pdf'],
                ]),
            ],
        ],
    ],
    [
        'title' => 'Pension scheme privacy notice',
        'starts_open' => 0,
        'content_rows' => [
            [
                'row_type' => 'external_links',
                'external_links' => matrix_seed_policies_external_links([
                    ['St Patrick\'s Hospital 2005 Pension Scheme - Privacy Notice', '/media/2041/st-patricks-hospital-2005-pension-scheme-privacy-notice.pdf'],
                ]),
            ],
        ],
    ],
    [
        'title' => 'Community guidelines',
        'starts_open' => 0,
        'content_rows' => [
            [
                'row_type' => 'text',
                'content' => '<p>We have community guidelines in place to help make our social media spaces open, safe and supportive: you can <a href="' . esc_url(matrix_seed_live_url('/about-us/policies-and-publications/community-guidelines')) . '" target="_blank" rel="noopener noreferrer">read these guidelines here</a>.</p>',
            ],
        ],
    ],
    [
        'title' => 'Complaints and feedback',
        'starts_open' => 0,
        'content_rows' => [
            [
                'row_type' => 'text',
                'content' => '<p>You can learn more about our feedback and complaints processes <a href="' . esc_url(matrix_seed_live_url('/about-us/policies-and-publications/clinical-governance/service-users-feedback')) . '" target="_blank" rel="noopener noreferrer">here</a>.</p>',
            ],
        ],
    ],
];

$strategies_items = [
    [
        'title' => 'Organisation strategy',
        'starts_open' => 1,
        'content_rows' => [
            [
                'row_type' => 'external_links',
                'external_links' => matrix_seed_policies_external_links([
                    ['The Future in Mind Strategy 2023-2027', $our_strategy_url],
                    ['Changing Minds. Changing Lives. Strategy 2018–2022', '/media/2264/spmh_changing_minds_changing-lives_strategy_2018-2022.pdf'],
                    ['Mental Health Matters 2013-2018', '/media/1803/mental_health_matters-2013-2018.pdf'],
                ]),
            ],
        ],
    ],
    [
        'title' => 'Annual reports',
        'starts_open' => 0,
        'content_rows' => [
            [
                'row_type' => 'external_links',
                'external_links' => matrix_seed_policies_external_links([
                    ['Annual Report 2024', '/media/4088/spmhs-annual-report-2024-final.pdf'],
                    ['Annual Report 2023', '/media/3827/annual-report-2023.pdf'],
                    ['Annual Report 2022', '/media/3679/2022-annual-report-st-patricks.pdf'],
                    ['Annual Report 2021', '/media/3477/annual-report-2021.pdf'],
                    ['Annual Report 2020', '/media/3293/annual-report-2020-st-patricks-mental-health-services.pdf'],
                    ['Annual Report 2019', '/media/2896/annual-report-2019-web-version-st-patricks-mental-health-services.pdf'],
                    ['Annual Report 2018', '/annual-report-2018'],
                    ['Annual Report 2018 (PDF)', '/media/3296/2018-annual-report-final-version.pdf'],
                    ['Annual Report 2017', '/annual-report-2017'],
                    ['Annual Report 2016', '/media/1850/spmhs-annual-report-2016pdf.pdf'],
                    ['Annual Report 2015', '/media/1878/spmhs-annual-report-2015.pdf'],
                    ['Annual Report 2014', '/media/1879/spmhs-annual-report-2014.pdf'],
                    ['Annual Report 2013', '/media/1892/spmhs_annual_-report_2013.pdf'],
                    ['Annual Report 2012', '/media/1882/annual_report_2012c.pdf'],
                    ['Annual Report 2011', '/media/1880/spuh-annual-report-2011.pdf'],
                    ['Annual Report 2010', '/media/1228/spuh-annual-report-2010.pdf'],
                    ['Annual Report 2009', '/media/1851/spuh-annual-report-2009.pdf'],
                    ['Annual Report 2008', '/media/1881/annualreport2008.pdf'],
                ]),
            ],
        ],
    ],
    [
        'title' => 'Outcome reports',
        'starts_open' => 0,
        'content_rows' => [
            [
                'row_type' => 'external_links',
                'external_links' => matrix_seed_policies_external_links([
                    ['Outcomes Report (Summary) 2024', '/media/4074/spmhs-outcomes-report-2024-summary.pdf'],
                    ['Outcomes Report (Full) 2024', '/media/4069/2024-outcomes-report.pdf'],
                    ['Outcomes Report (Summary) 2023', '/media/3828/outcomes-report-2023-summary.pdf'],
                    ['Outcomes Report (Full) 2023', '/media/3811/2023-full-outcomes-report.pdf'],
                    ['Outcomes Report (Full) 2022', '/media/3688/final-long-outcomes-report-2022.pdf'],
                    ['Outcomes Report (Summary) 2022', '/media/3686/2022-outcomes-report-summary.pdf'],
                    ['Outcomes Report (Full) 2021', '/media/3480/outcomes-report-2021-full-report.pdf'],
                    ['Outcomes Report (Summary) 2021', '/media/3478/summary-outcomes-report-2021.pdf'],
                    ['Outcomes Report (Full) 2020', '/media/3277/outcomes-report-2020-st-patricks-mental-health-services.pdf'],
                    ['Outcomes Report (Summary) 2020', '/media/3281/outcomes-report-2020-summary-st-patricks-mental-health-services.pdf'],
                    ['Outcomes Report (Full) 2019', '/media/2889/2019-outcomes-report-full-st-patricks-mental-health-services.pdf'],
                    ['Outcomes Report (Summary) 2019', '/media/2887/2019-outcomes-summary-report-st-patricks-mental-health-services.pdf'],
                    ['Outcomes Report (Full) 2018', '/media/2469/outcomes-full-version.pdf'],
                    ['Outcomes Report (Summary) 2018', '/outcomes-report-2018'],
                    ['Outcomes Report 2017', '/outcomes-report-2017'],
                    ['Outcomes Report (Full) 2015', '/media/1853/outcomes-report-2015-23816.pdf'],
                    ['Outcomes Report Summary 2015', '/media/1854/outcomes-report-2015.pdf'],
                    ['Outcomes Report (Full) 2014', '/media/1144/outcomes-report-2014.pdf'],
                    ['Outcomes Report Summary 2014', '/media/1278/summary-outcomes-report-2014.pdf'],
                    ['Outcomes Report (Full) 2013', '/media/1883/outcomes-report-2013-1.pdf'],
                    ['Outcomes Report Summary 2013', '/media/1153/spmhs_outcomes_report_summary_2013.pdf'],
                    ['Outcomes Report (Full) 2012', '/media/1893/outcomes_report_2012_full.pdf'],
                    ['Outcomes Report Summary 2012', '/media/1256/outcomes_report_2012_full.pdf'],
                    ['Outcomes Report (Full) 2011', '/media/1280/clinical-outcomes-report-2011-16-11-12-web-versionpdf.pdf'],
                    ['Outcomes Report Summary 2011', '/media/1281/spuh-outcomes-report-web.pdf'],
                ]),
            ],
        ],
    ],
    [
        'title' => 'Financial statements',
        'starts_open' => 0,
        'content_rows' => [
            [
                'row_type' => 'external_links',
                'external_links' => matrix_seed_policies_external_links([
                    ['Financial Statements 2015', '/media/1289/financial-statements-2015.pdf'],
                    ['Financial Statements 2014', '/media/1288/financial-statements-2014.pdf'],
                    ['Financial Statements 2013', '/media/1287/financial-statement-2013.pdf'],
                    ['Financial Statements 2012', '/media/1286/financial-statements-2012.pdf'],
                    ['Financial Statements 2011', '/media/1285/financial-statements-2011-web.pdf'],
                    ['Financial Statements 2010', '/media/1284/financial-statements-2010-web.pdf'],
                    ['Financial Statements 2009', '/media/1283/financial-statements-2009-web.pdf'],
                    ['Senior Management Remuneration 2013', '/media/1282/memo-on-senior-management-remuneration-2013-july-2014.pdf'],
                ]),
            ],
        ],
    ],
    [
        'title' => 'Gender pay gap reports',
        'starts_open' => 0,
        'content_rows' => [
            [
                'row_type' => 'external_links',
                'external_links' => matrix_seed_policies_external_links([
                    ['Gender Pay Gap Report 2025', '/media/4206/2025-report.pdf'],
                    ['Gender Pay Gap Report 2024', 'https://issuu.com/stpatricksmentalhealthservices/docs/2024_report'],
                    ['Gender Pay Gap Report 2023', '/media/3762/gender-pay-gap-report-2023.pdf'],
                    ['Gender Pay Gap Report 2021-2022', '/media/3571/gender-pay-gap-2021-22-report.pdf'],
                ]),
            ],
        ],
    ],
    [
        'title' => 'Mechanical and physical restraint reports',
        'starts_open' => 0,
        'content_rows' => [
            [
                'row_type' => 'text',
                'content' => '<h3>St Patrick\'s University Hospital (SPUH)</h3>',
            ],
            [
                'row_type' => 'external_links',
                'external_links' => matrix_seed_policies_external_links([
                    ['SPUH mechanical restraint annual activity review report 2025', '/media/4225/spuh-mechanical-restraint-annual-activity-review-report-2025.pdf'],
                    ['SPUH physical restraint annual activity review report 2025', '/media/4254/spuh-physical-restraint-annual-activity-review-report-2025_rev-26032026-clean-version.pdf'],
                ]),
            ],
            [
                'row_type' => 'text',
                'content' => '<h3>St Patrick\'s Hospital Lucan</h3>',
            ],
            [
                'row_type' => 'external_links',
                'external_links' => matrix_seed_policies_external_links([
                    ['Lucan hospital mechanical restraint annual activity report 2025', '/media/4223/spl-mechanical-restraint-annual-activity-review-report-2025.pdf'],
                    ['Lucan hospital physical restraint annual activity review report 2025', '/media/4226/spl-physical-restraint-annual-activity-review-report-2025.pdf'],
                ]),
            ],
            [
                'row_type' => 'text',
                'content' => '<h3>Willow Grove Adolescent Unit</h3>',
            ],
            [
                'row_type' => 'external_links',
                'external_links' => matrix_seed_policies_external_links([
                    ['Willow Grove mechanical restraint annual activity review report 2025', '/media/4222/wgau-mechanical-restraint-annual-activity-review-report-2025.pdf'],
                    ['Willow Grove physical restraint annual activity review report 2025', '/media/4227/wgau-physical-restraint-annual-activity-review-report-2025.pdf'],
                ]),
            ],
        ],
    ],
];

$flexi_rows = [
    [
        'acf_fc_layout' => 'hero_with_breadcrumbs',
        'layout_style' => 'image_split',
        'show_breadcrumbs' => 1,
        'breadcrumb_source' => 'manual',
        'manual_breadcrumbs' => [
            [
                'breadcrumb_link' => [
                    'title' => 'Home',
                    'url' => $home,
                    'target' => '',
                ],
            ],
            [
                'breadcrumb_link' => [
                    'title' => 'About Us',
                    'url' => $about_us_url,
                    'target' => '',
                ],
            ],
        ],
        'current_crumb_label' => 'Policies and Publications',
        'heading_tag' => 'h1',
        'heading' => 'Policies and Publications',
        'content' => '<p>' . esc_html($hero_intro) . '</p>',
        'hero_image' => $hero_image_id,
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
    ],
    [
        'acf_fc_layout' => 'content_accordion',
        'layout_style' => 'policies_page',
        'section_background' => '#FFFFFF',
        'panel_background' => 'linear-gradient(-29.03deg, #F3EADE 3.24%, #F1F3DE 90.88%)',
        'open_panel_background' => 'linear-gradient(-80.97deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
        'icon_tile_background_color' => '#FFFFFF',
        'items' => $policies_items,
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Strategies and reports',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'intro_text' => '',
        'content' => '',
        'layout_style' => 'image_left',
        'background_type' => 'color',
        'background_color' => '#FBF8F3',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content_accordion',
        'layout_style' => 'policies_page',
        'section_background' => '#FBF8F3',
        'panel_background' => 'linear-gradient(-29.03deg, #F3EADE 3.24%, #F1F3DE 90.88%)',
        'open_panel_background' => 'linear-gradient(-80.97deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
        'icon_tile_background_color' => '#FFFFFF',
        'items' => $strategies_items,
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'useful_links',
        'heading_tag' => 'h2',
        'heading' => 'In this section',
        'variant' => 'flexi',
        'links' => [
            ['link' => ['title' => 'Policies and Publications', 'url' => $policies_url, 'target' => '']],
            ['link' => ['title' => 'Data Protection', 'url' => $data_protection_url, 'target' => '']],
            ['link' => ['title' => 'Child Protection Statement of St Patrick\'s Mental Health Services', 'url' => matrix_seed_live_url('/about-us/policies-and-publications/child-protection-statement-of-st-patrick-s-mental-health-services'), 'target' => '_blank']],
            ['link' => ['title' => 'Child Safeguarding Statement', 'url' => matrix_seed_live_url('/about-us/policies-and-publications/child-safeguarding-statement'), 'target' => '_blank']],
            ['link' => ['title' => 'Community Guidelines', 'url' => matrix_seed_live_url('/about-us/policies-and-publications/community-guidelines'), 'target' => '_blank']],
        ],
        'background_color' => '#E9E2F7',
        'heading_color' => '#1E244B',
        'link_color' => '#1E244B',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'content_cta',
        'heading_tag' => 'h2',
        'heading' => 'Continue to…',
        'body' => '<p>Read our data protection information and privacy notices.</p>',
        'button_link' => [
            'title' => 'Data Protection',
            'url' => $data_protection_url,
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
update_post_meta($post_id, '_matrix_migrate_restyle_skip', '1');
update_post_meta($post_id, '_matrix_migrate_restyled', 'manual');

$saved_rows = get_field('flexible_content_blocks', $post_id);
$saved_count = is_array($saved_rows) ? count($saved_rows) : 0;

if ($saved_count !== count($flexi_rows)) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error(
            'Failed to update Policies and Publications page ' . $post_id
            . ' (expected ' . count($flexi_rows) . ' blocks, found ' . $saved_count . ')'
        );
    }

    exit(1);
}

if (class_exists('WP_CLI')) {
    WP_CLI::success(sprintf(
        'Seeded Policies and Publications page (%d) with %d flexi blocks from stpatricks.ie.',
        $post_id,
        $saved_count
    ));
}

echo "Seeded Policies and Publications page {$post_id} with {$saved_count} flexi blocks.\n";
