<?php

/**
 * Seed Data protection policy page from stpatricks.ie/about-us/policies-and-publications/data-protection.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-data-protection-policy.php
 */

require_once __DIR__ . '/lib/page-seed-conventions.php';
require_once get_template_directory() . '/inc/migrate-functions.php';

if (! function_exists('matrix_seed_dpp_hero')) {
    function matrix_seed_dpp_hero(string $title, string $intro): array
    {
        $config = matrix_get_utility_page_hero_config($title, $intro);

        return array_merge($config, [
            'acf_fc_layout' => 'hero_with_breadcrumbs',
            'show_breadcrumbs' => 1,
            'manual_breadcrumbs' => [
                [
                    'breadcrumb_link' => [
                        'title' => 'Home',
                        'url' => home_url('/'),
                        'target' => '',
                    ],
                ],
            ],
        ]);
    }
}

if (! function_exists('matrix_seed_dpp_media_url')) {
    function matrix_seed_dpp_media_url(string $source_path): string
    {
        $attachment_id = (int) matrix_migrate_attachment_id_for_source_path($source_path);

        if ($attachment_id > 0) {
            $url = wp_get_attachment_url($attachment_id);

            if (is_string($url) && $url !== '') {
                return esc_url($url);
            }
        }

        return esc_url(matrix_migrate_live_url($source_path));
    }
}

$post_id = (int) (get_page_by_path('data-protection-policy')?->ID ?? 0);

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at data-protection-policy.');
    }

    exit(1);
}

$privacy_policy_url = home_url('/cookie-privacy-policy/');
$paon_leaflet_url = matrix_seed_dpp_media_url('/media/1777/data-protection-information-leaflet-spmhs-prompt-assessment-of-needs-service.pdf');
$adult_leaflet_url = matrix_seed_dpp_media_url('/media/1898/data-protection-information-adult-services.pdf');
$adolescent_leaflet_url = matrix_seed_dpp_media_url('/media/1897/data-protection-information-adolesecent-services.pdf');

$hero_intro = 'St Patrick\'s Mental Health Services (SPMHS) is committed to data protection.';

$privacy_notice_body = '<p>We fully believe in the importance and upholding of your data protection rights, and we aim to be completely transparent around the policies and practices regarding our collection and use of your personal data.</p>'
    . '<p>We have a robust Privacy Notice in place, which is underpinned by our commitment to protect people\'s freedoms and rights under the General Data Protection Regulation (GDPR) of the European Union (EU), the Irish Data Protection Act 2018, and other relevant legislation.</p>'
    . '<p>Through our Privacy Notice, we intend to make clear the type of personal information that we hold and how we process (or handle) your personal data.</p>'
    . '<p><strong><a href="' . esc_url($privacy_policy_url) . '">See our Cookie &amp; Privacy policy here</a>.</strong></p>';

$data_protection_body = '<h3>Mental health assessments</h3>'
    . '<p>If we receive a referral to SPMHS for you, you may be offered a Prompt Assessment of Needs (PAON) with our Referral and Assessment Service to identify which service would be best for you. It is essential that we record this assessment in our electronic health record so that we can provide the best possible service to you.</p>'
    . '<p>We have a leaflet available which advises you of your data protection rights relating to the PAON.</p>'
    . '<p><strong><a href="' . esc_url($paon_leaflet_url) . '" target="_blank" rel="noopener noreferrer">See the PAON data protection leaflet here</a>.</strong></p>'
    . '<h3>Services</h3>'
    . '<p>If you are receiving care and treatment in SPMHS, we collect information that helps us in providing your mental healthcare. This includes information relating to your demographics, medical history, family history, lifestyle, test results and more. This applies for service users in both our adult and adolescent services.</p>'
    . '<p>We have leaflets available which give data protection information, including details of the relevant legislation and the data protection rights available to you.</p>'
    . '<p><strong><a href="' . esc_url($adult_leaflet_url) . '" target="_blank" rel="noopener noreferrer">See our data protection leaflet for adult services</a>.</strong></p>'
    . '<p><strong><a href="' . esc_url($adolescent_leaflet_url) . '" target="_blank" rel="noopener noreferrer">See our data protection leaflet for adolescent services</a>.</strong></p>';

$dpo_body = '<p>You can contact our Data Protection Officer (DPO), John Woods, with queries about data protection in SPMHS. You can contact our DPO by calling <a href="tel:012493216">01 249 3216</a>; emailing <a href="mailto:dpo@stpatricks.ie">dpo@stpatricks.ie</a>; or writing to John Woods, St Patrick\'s University Hospital, James Street, Dublin 8.</p>';

$flexi_rows = [
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Privacy notice',
        'heading_tag' => matrix_page_seed_heading(2),
        'accent_position' => 'below_heading',
        'content' => $privacy_notice_body,
        'column_layout' => 'one_column',
        'layout_style' => 'image_left',
        'text_width' => 'wide',
        'background_type' => 'color',
        'background_color' => '#FFFFFF',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Data protection',
        'heading_tag' => matrix_page_seed_heading(2),
        'accent_position' => 'below_heading',
        'content' => $data_protection_body,
        'column_layout' => 'one_column',
        'layout_style' => 'image_left',
        'text_width' => 'wide',
        'background_type' => 'color',
        'background_color' => '#FBFAF7',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Data Protection Officer',
        'heading_tag' => matrix_page_seed_heading(2),
        'accent_position' => 'below_heading',
        'content' => $dpo_body,
        'column_layout' => 'one_column',
        'layout_style' => 'image_left',
        'text_width' => 'wide',
        'background_type' => 'color',
        'background_color' => '#FFFFFF',
    ],
];

wp_update_post([
    'ID' => $post_id,
    'post_content' => '',
    'post_title' => 'Data protection policy',
]);

update_field('hero_content_blocks', [
    matrix_seed_dpp_hero('Data protection policy', $hero_intro),
], $post_id);
update_field('flexible_content_blocks', $flexi_rows, $post_id);

$saved_rows = get_field('flexible_content_blocks', $post_id);
$saved_count = is_array($saved_rows) ? count($saved_rows) : 0;

if (class_exists('WP_CLI')) {
    if ($saved_count === count($flexi_rows)) {
        WP_CLI::success(sprintf(
            'Seeded Data protection policy page (%d) with %d flexi blocks.',
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

    WP_CLI::log('Page: ' . get_permalink($post_id));
}
