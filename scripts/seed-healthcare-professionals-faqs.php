<?php

/**
 * Seed Healthcare Professionals FAQ page with content from stpatricks.ie/gps-referrals.
 * Removes placeholder FAQs and displays all healthcare professional FAQs by section.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-healthcare-professionals-faqs.php
 */

require_once __DIR__ . '/lib/page-seed-conventions.php';
require_once __DIR__ . '/lib/healthcare-faqs-seed.php';
require_once get_template_directory() . '/inc/migrate-functions.php';

$page_id = (int) (get_page_by_path('healthcare-professionals/frequently-asked-questions')?->ID ?? 0);

if ($page_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at healthcare-professionals/frequently-asked-questions.');
    }

    exit(1);
}

$home = home_url('/');
$section_url = home_url('/healthcare-professionals/');
$service_users_faqs_url = home_url('/service-users-and-visitors/frequently-asked-questions-faqs/');
$referrals_url = home_url('/make-a-referral/');

$hero_image_id = (int) matrix_migrate_attachment_id_for_source_path('/media/3498/mental-health-services.png');

if ($hero_image_id <= 0) {
    $hero_image_id = (int) matrix_migrate_attachment_id_for_source_path('/media/1676/st-patricks-mental-health-services-refer-admission-banner-min.jpg');
}

$parent_term_id = matrix_seed_hp_faq_ensure_term('healthcare-professionals', 'Healthcare Professionals');
$referrals_term_id = matrix_seed_hp_faq_ensure_term('hp-referrals', 'Referrals and admissions', $parent_term_id);
$services_term_id = matrix_seed_hp_faq_ensure_term('hp-services', 'Services and assessments', $parent_term_id);
$insurance_term_id = matrix_seed_hp_faq_ensure_term('hp-insurance', 'Insurance and funding', $parent_term_id);
$clinical_term_id = matrix_seed_hp_faq_ensure_term('hp-clinical', 'Clinical information and professional development', $parent_term_id);

$term_map = [
    'referrals' => $referrals_term_id,
    'services' => $services_term_id,
    'insurance' => $insurance_term_id,
    'clinical' => $clinical_term_id,
];

$legacy_posts = get_posts([
    'post_type' => 'faqs',
    'post_status' => 'any',
    'posts_per_page' => -1,
    'meta_query' => [
        'relation' => 'OR',
        [
            'key' => '_matrix_seed_key',
            'value' => 'faq-healthcare-',
            'compare' => 'LIKE',
        ],
        [
            'key' => '_matrix_seed_key',
            'value' => 'healthcare-professionals-faq-',
            'compare' => 'LIKE',
        ],
    ],
]);

foreach ($legacy_posts as $legacy_post) {
    wp_delete_post((int) $legacy_post->ID, true);
}

$lorem_posts = get_posts([
    'post_type' => 'faqs',
    'post_status' => 'any',
    'posts_per_page' => -1,
    's' => 'Lorem ipsum',
]);

foreach ($lorem_posts as $lorem_post) {
    $terms = wp_get_object_terms((int) $lorem_post->ID, 'faq_category', ['fields' => 'slugs']);

    if (is_array($terms) && in_array('healthcare-professionals', $terms, true)) {
        wp_delete_post((int) $lorem_post->ID, true);
    }
}

$seeded_faq_count = 0;
$faq_sections = matrix_seed_hp_faq_sections();

foreach ($faq_sections as $section_key => $section) {
    $term_id = (int) ($term_map[$section_key] ?? 0);

    foreach ($section['items'] as $index => $item) {
        $seed_key = 'hp-faq-' . $section_key . '-' . ($index + 1);
        $faq_id = matrix_seed_hp_faq_ensure_post(
            $item['title'],
            $item['content'],
            $seed_key,
            [$term_id, $parent_term_id],
            $index + 1
        );

        if ($faq_id > 0) {
            $seeded_faq_count++;
        }
    }
}

$hero_intro = 'Here in St Patrick\'s Mental Health Services (SPMHS), we have gathered answers to common questions from GPs and referrers about referrals, our services, assessments, insurance and secure clinical communication.';

$page_rows = [
    [
        'acf_fc_layout' => 'hero_with_breadcrumbs',
        'layout_style' => 'image_split',
        'show_breadcrumbs' => 1,
        'breadcrumb_source' => 'manual',
        'manual_breadcrumbs' => [
            ['breadcrumb_link' => ['title' => 'Home', 'url' => $home, 'target' => '']],
            ['breadcrumb_link' => ['title' => 'Healthcare Professionals', 'url' => $section_url, 'target' => '']],
        ],
        'current_crumb_label' => 'FAQs',
        'heading_tag' => matrix_page_seed_heading(1),
        'heading' => 'Frequently asked questions for healthcare professionals',
        'content' => '<p>' . esc_html($hero_intro) . '</p>',
        'primary_button' => [
            'title' => 'Make a referral',
            'url' => $referrals_url,
            'target' => '',
        ],
        'hero_image' => $hero_image_id,
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
    ],
    matrix_seed_hp_faqs_section_row($faq_sections['referrals']['heading'], $referrals_term_id),
    matrix_seed_hp_faqs_section_row($faq_sections['services']['heading'], $services_term_id),
    matrix_seed_hp_faqs_section_row($faq_sections['insurance']['heading'], $insurance_term_id),
    matrix_seed_hp_faqs_section_row($faq_sections['clinical']['heading'], $clinical_term_id),
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Further questions',
        'heading_tag' => matrix_page_seed_heading(2),
        'accent_position' => 'below_heading',
        'content' => '<p>If you have an urgent query in relation to referrals, you can <a href="tel:012493635">call 01 249 3635</a> to reach our Referral and Assessment Service between 9am and 5pm, Monday to Friday. Outside of these hours, please <a href="tel:012493200">call 01 249 3200</a>.</p>'
            . '<p>You can also <a href="' . esc_url($service_users_faqs_url) . '">see our Service Users FAQs</a> for questions your patients may have about accessing care.</p>',
        'column_layout' => 'one_column',
        'background_type' => 'cream',
        'text_width' => 'constrained',
    ],
];

update_field('hero_content_blocks', [], $page_id);
update_field('flexible_content_blocks', $page_rows, $page_id);
update_post_meta($page_id, '_matrix_seed_key', 'healthcare-professionals-faqs-content');

if (class_exists('WP_CLI')) {
    WP_CLI::success(sprintf(
        'Seeded Healthcare Professionals FAQ page (ID %d) with %d FAQ posts across 4 sections.',
        $page_id,
        $seeded_faq_count
    ));
    WP_CLI::log('Page: ' . get_permalink($page_id));
}
