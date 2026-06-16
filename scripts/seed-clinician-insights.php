<?php

/**
 * Seed Healthcare Professionals > Clinician Insights hub (page 267).
 *
 * Sources:
 * - https://www.stpatricks.ie/gps-referrals/online-gp-cpd
 * - Healthcare Professionals landing page content patterns
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-clinician-insights.php
 */

require_once __DIR__ . '/lib/page-seed-conventions.php';
require_once __DIR__ . '/lib/healthcare-faqs-seed.php';
require_once get_template_directory() . '/inc/migrate-functions.php';

$post_id = matrix_seed_resolve_page_id_by_path('healthcare-professionals/clinician-insights');

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at healthcare-professionals/clinician-insights.');
    }

    exit(1);
}

if (! function_exists('matrix_seed_ci_url')) {
    function matrix_seed_ci_url(string $path): string
    {
        return esc_url(home_url('/' . trim($path, '/') . '/'));
    }
}

if (! function_exists('matrix_seed_ci_attachment_url')) {
    function matrix_seed_ci_attachment_url(string $source_path, string $fallback_url = ''): string
    {
        $attachment_id = (int) matrix_migrate_attachment_id_for_source_path($source_path);

        if ($attachment_id > 0) {
            $url = wp_get_attachment_url($attachment_id);

            if (is_string($url) && $url !== '') {
                return esc_url($url);
            }
        }

        return $fallback_url !== '' ? esc_url($fallback_url) : '';
    }
}

$home = home_url('/');
$healthcare_url = home_url('/healthcare-professionals/');
$gp_cpd_url = get_permalink(3081) ?: matrix_seed_ci_url('referrals/online-gp-cpd');
$webinars_url = matrix_seed_ci_url('healthcare-professionals/webinars-events');
$training_url = matrix_seed_ci_url('healthcare-professionals/training-centre');
$faqs_url = matrix_seed_ci_url('healthcare-professionals/frequently-asked-questions');

$clinical_term_id = matrix_seed_hp_faq_ensure_term('hp-clinical', 'Clinical information and professional development', matrix_seed_hp_faq_ensure_term('healthcare-professionals', 'Healthcare Professionals'));

$hero_intro = 'We offer a wide range of mental health education supports for general practitioners (GPs) and healthcare professionals, including accredited e-learning, webinars, and clinical resources to support your practice.';

$intro_body = '<p>At St Patrick\'s Mental Health Services (SPMHS), we support GPs and referrers with continuous professional development opportunities, clinical insights, and practical resources on mental health care.</p>'
    . '<p>Explore our accredited GP CPD e-learning, GP Webinar Series, and mental health films for GPs. You can also sign up to our GP eNewsletter for updates on mental health, service developments, events and training.</p>'
    . '<p>Please note this eNewsletter cannot be delivered to @healthmail email addresses; please provide an alternative email address.</p>';

$gp_cpd_image = matrix_seed_ci_attachment_url('/media/1539/gp-portal.jpeg');
$webinar_image = matrix_seed_ci_attachment_url('/media/1530/st-patricks-mental-health-services-gp-referral-banner-min.jpg');
$training_image = matrix_seed_ci_attachment_url('/media/1869/st-patricks-mental-health-services-garden.jpg');

$resource_cards = [
    [
        'title' => 'Mental health education for GPs',
        'description' => 'Accredited e-learning, GP Webinar Series, and mental health films to support GP practice.',
        'image_url' => $gp_cpd_image,
        'link' => ['title' => 'Mental health education for GPs', 'url' => $gp_cpd_url, 'target' => ''],
        'card_tone' => 'bg1',
    ],
    [
        'title' => 'Webinars and events',
        'description' => 'Stay up to date with mental health developments, service updates, and CPD opportunities.',
        'image_url' => $webinar_image,
        'link' => ['title' => 'Webinars and events', 'url' => $webinars_url, 'target' => ''],
        'card_tone' => 'bg2',
    ],
    [
        'title' => 'Training Centre',
        'description' => 'Information on training and education opportunities for healthcare professionals.',
        'image_url' => $training_image,
        'link' => ['title' => 'Training Centre', 'url' => $training_url, 'target' => ''],
        'card_tone' => 'bg3',
    ],
];

$flexi_rows = [
    [
        'acf_fc_layout' => 'hero_with_breadcrumbs',
        'layout_style' => 'image_split',
        'show_breadcrumbs' => 1,
        'breadcrumb_source' => 'manual',
        'manual_breadcrumbs' => [
            ['breadcrumb_link' => ['title' => 'Home', 'url' => $home, 'target' => '']],
            ['breadcrumb_link' => ['title' => 'Healthcare Professionals', 'url' => $healthcare_url, 'target' => '']],
        ],
        'current_crumb_label' => 'Clinician Insights',
        'heading_tag' => matrix_page_seed_heading(1),
        'heading' => 'Clinician insights',
        'content' => '<p>' . esc_html($hero_intro) . '</p>',
        'primary_button' => [
            'title' => 'Mental health education for GPs',
            'url' => $gp_cpd_url,
            'target' => '',
        ],
        'hero_image' => (int) matrix_migrate_attachment_id_for_source_path('/media/1539/gp-portal.jpeg'),
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
    ],
    [
        'acf_fc_layout' => 'useful_links',
        'heading_tag' => matrix_page_seed_heading(2),
        'heading' => 'In this section',
        'variant' => 'flexi',
        'links' => [
            ['link' => ['title' => 'Mental health education for GPs', 'url' => $gp_cpd_url, 'target' => '']],
            ['link' => ['title' => 'Webinars and events', 'url' => $webinars_url, 'target' => '']],
            ['link' => ['title' => 'Training Centre', 'url' => $training_url, 'target' => '']],
            ['link' => ['title' => 'Frequently Asked Questions', 'url' => $faqs_url, 'target' => '']],
        ],
        'background_color' => '#F1F8F9',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Clinical information and professional development',
        'heading_tag' => matrix_page_seed_heading(2),
        'accent_position' => 'below_heading',
        'content' => $intro_body,
        'column_layout' => 'one_column',
        'layout_style' => 'image_left',
        'text_width' => 'wide',
        'background_type' => 'color',
        'background_color' => '#FFFFFF',
    ],
    [
        'acf_fc_layout' => 'about_links_grid',
        'heading_tag' => matrix_page_seed_heading(2),
        'heading_text' => 'Explore clinician resources',
        'intro_text' => '',
        'links' => $resource_cards,
        'bg_color' => '#F1F8F9',
        'heading_color' => '#0B0B08',
        'intro_color' => '#4A4B37',
        'columns' => '3',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Webinars and events for healthcare professionals',
        'heading_tag' => matrix_page_seed_heading(2),
        'accent_position' => 'below_heading',
        'content' => '<p>Stay up to date with mental health developments, service updates, and continuous professional development opportunities through our webinars and events for GPs and referrers.</p>'
            . '<p>Sign up to our GP eNewsletter for information about mental health, service updates, events and training.</p>',
        'primary_button' => [
            'title' => 'See webinars and events',
            'url' => $webinars_url,
            'target' => '',
        ],
        'primary_button_variant' => 'filled',
        'layout_style' => 'image_right',
        'background_type' => 'gradient',
        'background_gradient' => 'linear-gradient(-69.76deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
        'image' => (int) matrix_migrate_attachment_id_for_source_path('/media/1530/st-patricks-mental-health-services-gp-referral-banner-min.jpg'),
    ],
    matrix_seed_hp_faqs_section_row('Clinical information and professional development', $clinical_term_id),
    [
        'acf_fc_layout' => 'content_cta',
        'heading_tag' => matrix_page_seed_heading(2),
        'heading' => 'Need more information?',
        'body' => '<p>For queries about referrals, services, or clinical information, visit our Frequently Asked Questions page or contact our Referral and Assessment Service on <strong>01 249 3635</strong>.</p>',
        'button_link' => [
            'title' => 'See healthcare professional FAQs',
            'url' => $faqs_url,
            'target' => '',
        ],
        'background_type' => 'color',
        'background_color' => '#CEF2EE',
    ],
];

update_field('hero_content_blocks', [], $post_id);
update_field('flexible_content_blocks', $flexi_rows, $post_id);

$saved_rows = get_field('flexible_content_blocks', $post_id);
$saved_count = is_array($saved_rows) ? count($saved_rows) : 0;

if (class_exists('WP_CLI')) {
    if ($saved_count === count($flexi_rows)) {
        WP_CLI::success(sprintf(
            'Seeded Clinician Insights page (%d) with %d flexi blocks.',
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
