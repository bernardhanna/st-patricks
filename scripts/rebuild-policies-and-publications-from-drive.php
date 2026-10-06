<?php

/**
 * Rebuild About Us > Policies and publications from Drive Library 3.
 *
 * Sources:
 * - Policies and publications - Matrix.docx
 * - Policies and publications.png
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/rebuild-policies-and-publications-from-drive.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$post_id = (int) (get_page_by_path('about-us/policies-and-publications')?->ID ?? 0);
if ($post_id === 0) {
    WP_CLI::error('Could not find about-us/policies-and-publications');
}

$home = untrailingslashit(home_url('/'));
$live = 'https://www.stpatricks.ie';

$p = static function (string $html): string {
    return '<p>' . $html . '</p>';
};
$a = static function (string $url, string $label, string $target = ''): string {
    $attrs = ' href="' . esc_url($url) . '"';
    if ($target !== '') {
        $attrs .= ' target="' . esc_attr($target) . '" rel="noopener noreferrer"';
    }

    return '<a' . $attrs . '>' . esc_html($label) . '</a>';
};
$ul = static function (array $items): string {
    $html = '<ul>';
    foreach ($items as $item) {
        $html .= '<li>' . $item . '</li>';
    }

    return $html . '</ul>';
};
$media = static function (string $path) use ($live): string {
    if (str_starts_with($path, 'http')) {
        return $path;
    }

    return $live . (str_starts_with($path, '/') ? $path : '/' . $path);
};
$pdf = static function (string $label, string $path) use ($a, $media): string {
    return $a($media($path), $label, '_blank');
};

// Prefer an already-imported Drive source; avoid re-sideloading the ~6MB PNG
// (WordPress image-size generation can hang on this file in local).
$img_id = 0;
$existing_drive = get_posts([
    'post_type' => 'attachment',
    'post_status' => 'inherit',
    'posts_per_page' => 1,
    'fields' => 'ids',
    'meta_query' => [[
        'key' => '_matrix_drive_source',
        'value' => 'Policies and publications.png',
    ]],
]);
if ($existing_drive !== []) {
    $img_id = (int) $existing_drive[0];
}
if ($img_id <= 0) {
    $img_id = (int) get_post_thumbnail_id($post_id);
}
if ($img_id <= 0) {
    $img_id = 4037;
}

$urls = [
    'strategy' => $home . '/about-us/our-present-and-future/',
    'data_protection' => $home . '/data-protection-policy/',
    'privacy' => $home . '/cookie-privacy-policy/',
    'child_protection' => $live . '/about-us/policies-and-publications/child-protection-statement-of-st-patrick-s-mental-health-services',
    'child_safeguarding' => $live . '/about-us/policies-and-publications/child-safeguarding-statement',
    'community' => $live . '/about-us/policies-and-publications/community-guidelines',
    'data_protection_live' => $live . '/about-us/policies-and-publications/data-protection',
];

$hero_intro = $p("Here in St Patrick's Mental Health Services (SPMHS), we are committed to meeting the highest standards in how we operate and how our services are governed. You can find out more about the measures we have in place and the steps we take to monitor our outcomes in our policies, reports and publications.");

$policies_accordion = [
    'Service user charter' => $p('We are committed to delivering an effective mental healthcare service in an environment that fosters mutual respect for the rights and dignity of all. Our Charter of Service User and Family Rights and Responsibilities helps us achieve this goal.')
        . $p($pdf('See the service user charter here', '/media/1241/charter-of-patient-and-family-rights-and-responsibilities.pdf') . '.'),
    'Child protection and safeguarding' => $p('At SPMHS, child welfare is of the highest concern, and we take all possible care in protecting children from harm and neglect. You can '
            . $a($urls['child_protection'], 'see our full Child Protection and Welfare Statement here', '_blank')
            . '.')
        . $p('We provide mental healthcare to young people aged between 12 and 17. We have developed a Child Safeguarding Statement in line with the Children First Act 2015. You can '
            . $a($urls['child_safeguarding'], 'find our Child Safeguarding Statement here', '_blank')
            . '.')
        . $ul([
            $pdf("Child Safeguarding Statement, St Patrick's Mental Health Services", '/media/4215/css-overarching.pdf'),
            $pdf('Child Safeguarding Statement, Willow Grove Adolescent Unit', '/media/4216/css-wgau.pdf'),
            $pdf('Child Safeguarding Statement, Dean Clinic, Dublin', '/media/4217/css-dean-clinic-dublin.pdf'),
            $pdf('Child Safeguarding Statement, Dean Clinic, Cork', '/media/4218/css-dean-clinic-cork.pdf'),
        ]),
    'Hospital policies' => $p('We have a wide range of internal policies and protocols to ensure we achieve and maintain the highest standards in all aspects of our services and in our service users’ experience of care. If you have any questions about our policies and processes, please '
        . $a('mailto:clinicalgovernance@stpatricks.ie', 'email clinicalgovernance@stpatricks.ie')
        . '.'),
    'Data protection' => $p('We fully believe in the importance and upholding of our service users’ data protection rights, and we aim to be completely transparent around how and why we collect and use personal data. You can '
            . $a($urls['data_protection'], 'learn more about data protection in SPMHS here')
            . '. You can also '
            . $a($urls['privacy'], 'see our privacy policy here')
            . '; this outlines our position and practices around the collection and use of personal data.'),
    'Community guidelines' => $p('We have community guidelines in place to help make our digital and social media spaces open, safe and supportive: you can '
        . $a($urls['community'], 'read these guidelines here', '_blank')
        . '.'),
    'Pension scheme' => $p('Our '
        . $pdf('pension scheme privacy notice is available here', '/media/2041/st-patricks-hospital-2005-pension-scheme-privacy-notice.pdf')
        . '.'),
];

$reports_accordion = [
    'Our strategy' => $p('The Future in Mind is our organisational strategy for 2023 to 2027. It stays true to our founding principles, while committing us to developing new services and promoting mental health awareness. The strategy was developed in consultation with service users, staff, our Board of Governors and other key stakeholders. '
        . $a($urls['strategy'], 'See more on our strategy here')
        . '.'),
    'Annual Reports' => $p('Our Annual Reports reflect on our yearly activity and show that we continue to occupy a distinctive and essential role within Ireland’s mental healthcare landscape. You can find our most recent Annual Reports below. If you would like a copy of an Annual Report for an earlier year, please email '
            . $a('mailto:communications@stpatricks.ie', 'communications@stpatricks.ie')
            . '.')
        . $ul([
            $a('https://issuu.com/stpatricksmentalhealthservices/docs/2025_annual_report_and_financial_statements_-_st_p', 'Annual Report 2025', '_blank'),
            $pdf('Annual Report 2024', '/media/4088/spmhs-annual-report-2024-final.pdf'),
            $pdf('Annual Report 2023', '/media/3827/annual-report-2023.pdf'),
            $pdf('Annual Report 2022', '/media/3679/2022-annual-report-st-patricks.pdf'),
            $pdf('Annual Report 2021', '/media/3477/annual-report-2021.pdf'),
        ]),
    'Outcomes Reports' => $p('Every year, we publish an Outcomes Report to assess and evaluate our clinical programmes, care pathways, governance and service user experiences. We produce a summary version, which gives highlights of the analysis of clinical outcomes for a number of our programmes, along with a full report of detailed information. You can find both the full and summary versions for our most recent Outcomes Reports below. If you would like a copy of an Outcomes Report for an earlier year, please email '
            . $a('mailto:communications@stpatricks.ie', 'communications@stpatricks.ie')
            . '.')
        . $ul([
            $a('https://issuu.com/stpatricksmentalhealthservices/docs/outcomes_summary_report_2025_-_st_patrick_s_mental', 'Outcomes Report (Summary) 2025', '_blank'),
            $a('https://issuu.com/stpatricksmentalhealthservices/docs/2025_outcomes_report_-_full', 'Outcomes Report (Full) 2025', '_blank'),
            $pdf('Outcomes Report (Summary) 2024', '/media/4074/spmhs-outcomes-report-2024-summary.pdf'),
            $pdf('Outcomes Report (Full) 2024', '/media/4069/2024-outcomes-report.pdf'),
            $pdf('Outcomes Report (Summary) 2023', '/media/3828/outcomes-report-2023-summary.pdf'),
            $pdf('Outcomes Report (Full) 2023', '/media/3811/2023-full-outcomes-report.pdf'),
            $pdf('Outcomes Report (Full) 2022', '/media/3688/final-long-outcomes-report-2022.pdf'),
            $pdf('Outcomes Report (Summary) 2022', '/media/3686/2022-outcomes-report-summary.pdf'),
            $pdf('Outcomes Report (Full) 2021', '/media/3480/outcomes-report-2021-full-report.pdf'),
            $pdf('Outcomes Report (Summary) 2021', '/media/3478/summary-outcomes-report-2021.pdf'),
        ]),
    'Gender pay gap reports' => $p('We recognise that our employees are central to the success of our organisation. We will continue to be an equal opportunities employer, both in terms of access to employment and progression in the organisation. We produce a Gender Pay Gap Report each year in line with the Gender Pay Gap Information Act, sharing key metrics on gender pay results and helping us to act where needed. You can see our most recent Gender Pay Gap Reports below.')
        . $ul([
            $pdf('Gender Pay Gap Report 2025', '/media/4206/2025-report.pdf'),
            $a('https://issuu.com/stpatricksmentalhealthservices/docs/2024_report', 'Gender Pay Gap Report 2024', '_blank'),
            $pdf('Gender Pay Gap Report 2023', '/media/3762/gender-pay-gap-report-2023.pdf'),
            $pdf('Gender Pay Gap Report 2021-2022', '/media/3571/gender-pay-gap-2021-22-report.pdf'),
        ]),
    'Mechanical and physical restraint reports' => $p('In keeping with our Charter of Service Users Rights, our internal policies, and relevant rules or codes of practice from the Mental Health Commission, we promote service users’ right to be free from restraint, balanced with protecting the safety of service users, staff and visitors. We have physical restraint and mechanical restraint reduction strategies and policies in place for this. You can see our '
            . $pdf('physical restraint reduction policy here', '/media/3573/physical-restraint-reduction-strategy-and-policy.pdf')
            . ' and our '
            . $pdf('mechanical restraint reduction policy here', '/media/3619/mechanical-restraint-reduction-policy.pdf')
            . '.')
        . $p('You can find reports of annual activity in relation to these policies below.')
        . '<p><strong>St Patrick\'s University Hospital (SPUH)</strong></p>'
        . $ul([
            $pdf('SPUH mechanical restraint annual activity report 2025', '/media/4225/spuh-mechanical-restraint-annual-activity-review-report-2025.pdf'),
            $pdf('SPUH physical restraint annual activity report 2025', '/media/4254/spuh-physical-restraint-annual-activity-review-report-2025_rev-26032026-clean-version.pdf'),
        ])
        . '<p><strong>St Patrick\'s Hospital Lucan</strong></p>'
        . $ul([
            $pdf('Lucan hospital mechanical restraint annual activity report 2025', '/media/4223/spl-mechanical-restraint-annual-activity-review-report-2025.pdf'),
            $pdf('Lucan hospital physical restraint annual activity report 2025', '/media/4226/spl-physical-restraint-annual-activity-review-report-2025.pdf'),
        ])
        . '<p><strong>Willow Grove Adolescent Unit</strong></p>'
        . $ul([
            $pdf('Willow Grove mechanical restraint annual activity report 2025', '/media/4222/wgau-mechanical-restraint-annual-activity-review-report-2025.pdf'),
            $pdf('Willow Grove physical restraint annual activity report 2025', '/media/4227/wgau-physical-restraint-annual-activity-review-report-2025.pdf'),
        ]),
];

$flexi = [
    matrix_orlaith_hero_row('Policies and publications', $hero_intro, $img_id),
    matrix_orlaith_accordion_row($policies_accordion, 'default', 'Our policies and charters'),
    matrix_orlaith_accordion_row($reports_accordion, 'default', 'Strategies and reports'),
    matrix_orlaith_useful_links_row([
        'About us' => 'about-us',
        'Our team' => 'about-us/our-team',
        'Clinical governance' => 'service-users-and-visitors/feedback-and-comments',
        'Feedback and complaints' => 'service-users-and-visitors/feedback-and-comments',
        'Service user participation' => 'service-users-and-visitors/service-user-participation',
        'Media queries' => 'about-us/media-queries',
    ]),
];

wp_update_post([
    'ID' => $post_id,
    'post_title' => 'Policies and publications',
]);

update_post_meta($post_id, '_yoast_wpseo_title', "Policies and Publications | St Patrick's Mental Health Services");
update_post_meta($post_id, '_yoast_wpseo_metadesc', "See policies, reports and publications from St Patrick's Mental Health Services aimed at meeting the highest standards in mental healthcare.");

update_field('flexible_content_blocks', $flexi, $post_id);
if ($img_id > 0) {
    set_post_thumbnail($post_id, $img_id);
}

WP_CLI::success(sprintf(
    'Rebuilt Policies and publications #%d → %s',
    $post_id,
    get_permalink($post_id)
));

foreach ((array) get_field('flexible_content_blocks', $post_id) as $i => $row) {
    $layout = (string) ($row['acf_fc_layout'] ?? '?');
    $heading = wp_strip_all_tags((string) ($row['heading'] ?? ''));
    $extra = '';
    if ($layout === 'useful_links' && ! empty($row['links'])) {
        $extra = ' | links=' . count($row['links']);
    }
    if ($layout === 'content_accordion' && ! empty($row['items'])) {
        $extra = ' | items=' . count($row['items']);
    }
    WP_CLI::log("[{$i}] {$layout} {$heading}{$extra}");
}
