<?php

/**
 * Seed the canonical Cookie & Privacy policy page from migrated cookies + privacy notice content.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-cookie-privacy-policy.php
 */

require_once __DIR__ . '/lib/page-seed-conventions.php';

if (! function_exists('matrix_seed_cpp_hero')) {
    function matrix_seed_cpp_hero(string $title, string $intro): array
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

$target_id = (int) (get_page_by_path('cookie-privacy-policy')?->ID ?? 0);
$privacy_source_id = (int) (get_page_by_path('privacy-notice')?->ID ?? 0);
$cookies_source_id = (int) (get_page_by_path('cookies')?->ID ?? 0);

if ($target_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at cookie-privacy-policy.');
    }

    exit(1);
}

$privacy_rows = $privacy_source_id > 0 ? (get_field('flexible_content_blocks', $privacy_source_id) ?: []) : [];
$cookies_rows = $cookies_source_id > 0 ? (get_field('flexible_content_blocks', $cookies_source_id) ?: []) : [];

$privacy_intro = '<p>Here, you\'ll find information on your data protection rights, and policies and practices regarding our collection and use of your personal data.</p>'
    . '<p>SPMHS is an independent, not-for-profit organisation that provides quality mental health care, promotes mental health awareness, and protects the rights and integrity of those suffering from mental illness. We are regulated by the <a href="https://www.mhcirl.ie/" target="_blank" rel="noopener noreferrer">Mental Health Commission</a>.</p>'
    . '<p>All personal data in possession of SPMHS is processed in accordance with, but not limited to, the obligations of the European Union (EU) <a href="https://gdpr.eu/" target="_blank" rel="noopener noreferrer">General Data Protection Regulation</a> (GDPR) (Regulation (EU) 2016/679) and the Irish Data Protection Act 2018, which gives further effect to the GDPR in Ireland.</p>'
    . '<p>We understand that you are aware of and care about your own personal privacy interests, and we take that very seriously.</p>'
    . '<p>This Privacy Notice describes our policies and practices regarding our collection and use of your personal data and sets forth your fundamental rights.</p>'
    . '<p>We recognise that data protection is an ongoing responsibility. From time to time, we will update this Privacy Notice as we undertake new personal data practices or adopt new data protection policies.</p>'
    . '<h3>Data Protection Officer</h3>'
    . '<p>We have appointed an internal Data Protection Officer (DPO) for you to contact if you have any questions or concerns about our personal data protection policies or practices. Our DPO\'s name is John Woods and you can contact him at St Patrick\'s University Hospital, James Street, Dublin 8; by <a href="tel:012493216">calling +353 1 249 3216</a>; or by emailing <a href="mailto:dpo@stpatricks.ie">dpo@stpatricks.ie</a>.</p>';

$cookies_block = null;

foreach ($cookies_rows as $row) {
    if (($row['acf_fc_layout'] ?? '') !== 'content') {
        continue;
    }

    if (trim(strip_tags((string) ($row['content'] ?? ''))) === '') {
        continue;
    }

    $cookies_block = $row;
    break;
}

if (is_array($cookies_block)) {
    $cookies_block['heading'] = 'About cookies on this site';
    $cookies_block['heading_tag'] = matrix_page_seed_heading(2);
    $cookies_block['accent_position'] = $cookies_block['accent_position'] ?? 'below_heading';
    $cookies_block['intro_text'] = '<p><strong>Our site uses cookies and other technologies so that we can remember you and understand how you use our site.</strong></p>';
}

$privacy_body_rows = [];

foreach ($privacy_rows as $index => $row) {
    if ($index === 0 || ($row['acf_fc_layout'] ?? '') === 'related_cards') {
        continue;
    }

    if ($index === 1 && ($row['acf_fc_layout'] ?? '') === 'content') {
        $row['heading'] = 'Privacy Notice';
        $row['heading_tag'] = matrix_page_seed_heading(2);
        $row['content'] = $privacy_intro;
    }

    $privacy_body_rows[] = $row;
}

$flexi_rows = [];

if (is_array($cookies_block)) {
    $flexi_rows[] = $cookies_block;
}

$flexi_rows = array_merge($flexi_rows, $privacy_body_rows);

$hero_intro = 'Our site uses cookies and other technologies so that we can remember you and understand how you use our site. At St Patrick\'s Mental Health Services (SPMHS), we take the protection of your personal data seriously.';

wp_update_post([
    'ID' => $target_id,
    'post_content' => '',
    'post_title' => 'Cookie & Privacy policy',
]);

update_field('hero_content_blocks', [
    matrix_seed_cpp_hero('Cookie & Privacy policy', $hero_intro),
], $target_id);
update_field('flexible_content_blocks', $flexi_rows, $target_id);

$duplicate_ids = array_values(array_unique(array_filter([
    $cookies_source_id,
    (int) (get_page_by_path('cookies-cookies')?->ID ?? 0),
    $privacy_source_id,
])));

$trashed = 0;

foreach ($duplicate_ids as $duplicate_id) {
    if ($duplicate_id === $target_id || $duplicate_id < 1) {
        continue;
    }

    $duplicate = get_post($duplicate_id);

    if (! $duplicate instanceof WP_Post || $duplicate->post_status === 'trash') {
        continue;
    }

    if (wp_trash_post($duplicate_id)) {
        $trashed++;
    }
}

$saved_rows = get_field('flexible_content_blocks', $target_id);
$saved_count = is_array($saved_rows) ? count($saved_rows) : 0;

if (class_exists('WP_CLI')) {
    WP_CLI::success(sprintf(
        'Seeded Cookie & Privacy policy page (%d) with %d flexi blocks. Trashed %d duplicate page(s).',
        $target_id,
        $saved_count,
        $trashed
    ));
    WP_CLI::log('Page: ' . get_permalink($target_id));

    if ($privacy_source_id === 0) {
        WP_CLI::warning('Privacy notice source page not found; only cookies content was applied.');
    }

    if ($cookies_source_id === 0) {
        WP_CLI::warning('Cookies source page not found; only privacy content was applied.');
    }
}
