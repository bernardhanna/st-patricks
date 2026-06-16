<?php

/**
 * Seed Service Users and Visitors > Feedback and Comments (page 285).
 *
 * Source: stpatricks.ie/about-us/policies-and-publications/clinical-governance/service-users-feedback
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-feedback-and-comments.php
 */

require_once __DIR__ . '/lib/page-seed-conventions.php';
require_once get_template_directory() . '/inc/migrate-functions.php';

$post_id = matrix_seed_resolve_page_id_by_path('service-users-and-visitors/feedback-and-comments');

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at service-users-and-visitors/feedback-and-comments.');
    }

    exit(1);
}

if (! function_exists('matrix_seed_feedback_url')) {
    function matrix_seed_feedback_url(string $path): string
    {
        return esc_url(home_url('/' . trim($path, '/') . '/'));
    }
}

if (! function_exists('matrix_seed_feedback_link')) {
    function matrix_seed_feedback_link(string $path, string $label): string
    {
        return '<a href="' . matrix_seed_feedback_url($path) . '">' . esc_html($label) . '</a>';
    }
}

$home = home_url('/');
$section_url = home_url('/service-users-and-visitors/');
$participation_url = get_permalink(1403) ?: matrix_seed_feedback_url('get-involved/service-user-participation');
$survey_url = get_permalink(1408) ?: matrix_seed_feedback_url('get-involved/service-user-participation/service-user-experience-survey');
$faqs_url = matrix_seed_feedback_url('service-users-and-visitors/frequently-asked-questions-faqs');
$child_protection_url = matrix_migrate_live_url('/about-us/policies-and-publications/child-protection-statement-of-st-patrick-s-mental-health-services');
$clinical_governance_email = 'clinicalgovernance@stpatricks.ie';
$clinical_governance_phone = '01 249 3200';

$hero_intro = 'Here, you can find information on how to provide feedback, including making a complaint, to St Patrick\'s Mental Health Services (SPMHS).';

$welcome_body = '<p>At SPMHS, we welcome feedback on all aspects of our services.</p>'
    . '<p>We recognise the importance of receiving continuous <a href="' . esc_url($participation_url) . '">feedback from our service users</a> and everyone involved in our services and activities. We are also committed to the <a href="' . esc_url($child_protection_url) . '" target="_blank" rel="noopener noreferrer">protection of children and vulnerable adults</a>.</p>'
    . '<p>Feedback enables us to continue to deliver a high quality service and to place the welfare and needs of service users at the forefront of all our activities.</p>'
    . '<p>We are committed to ensuring that all complaints and comments are given fair consideration. All feedback is provided to the most appropriate senior manager and, where appropriate, is investigated and resolved.</p>';

$how_to_body = '<p>Feedback or complaints can be made:</p>'
    . '<ul>'
    . '<li>verbally to any member of staff</li>'
    . '<li>by posting a letter to our Clinical Governance Department</li>'
    . '<li>by emailing the Clinical Governance Department</li>'
    . '<li>by phone call to the Clinical Governance Department.</li>'
    . '</ul>'
    . '<p>In addition, comment cards, which give opportunity for your feedback and/or complaints, are situated throughout our campuses. You will find comment cards throughout hospital and ward locations in '
    . matrix_seed_feedback_link('st-patrick-s-university-hospital', 'St Patrick\'s University Hospital')
    . ' and '
    . matrix_seed_feedback_link('st-patricks-lucan', 'St Patrick\'s Hospital Lucan')
    . ', and in our '
    . matrix_seed_feedback_link('outpatient-clinics/about-the-dean-clinics', 'Dean Clinics')
    . '. There is a box provided at each of these locations where you can place your completed comment card.</p>'
    . '<p>The complaints procedure is set out in the Service User Information Booklet which is given to inpatient service users on admission and also in the Service User and Family Charter of Rights and Responsibilities which is displayed throughout our hospitals and available in the Library of '
    . matrix_seed_feedback_link('your-portal/about-your-portal', 'Your Portal')
    . '.</p>';

$contact_body = '<p>If you are sending your feedback or complaint in writing, please address this to the Clinical Governance Department, St Patrick\'s University Hospital, James\'s Street, Dublin 8.</p>'
    . '<p>To submit your feedback or complaint by email, please <a href="mailto:' . esc_attr($clinical_governance_email) . '">email ' . esc_html($clinical_governance_email) . '</a>.</p>'
    . '<p>Please <a href="tel:012493200">call ' . esc_html($clinical_governance_phone) . '</a> to reach the Clinical Governance Department by phone.</p>';

$expect_body = '<p>The Clinical Governance Department will acknowledge receipt of your complaint within five working days.</p>'
    . '<p>Staff aim to handle complaints in an open and constructive manner. Staff are encouraged to seek an early resolution of complaints and to keep you informed of progress until a complaint is resolved.</p>'
    . '<p>Please note that, where a family member, friend or third party makes a complaint about the care and treatment provided to a service user of SPMHS, there is a requirement to receive the service user\'s consent or permission to discuss the matter with the person making the complaint.</p>'
    . '<p>If you have any queries about our feedback and complaints pathways, please get in touch with our Clinical Governance Department by calling '
    . esc_html($clinical_governance_phone)
    . ' or emailing <a href="mailto:' . esc_attr($clinical_governance_email) . '">' . esc_html($clinical_governance_email) . '</a>.</p>';

$flexi_rows = [
    [
        'acf_fc_layout' => 'hero_with_breadcrumbs',
        'layout_style' => 'image_split',
        'show_breadcrumbs' => 1,
        'breadcrumb_source' => 'manual',
        'manual_breadcrumbs' => [
            ['breadcrumb_link' => ['title' => 'Home', 'url' => $home, 'target' => '']],
            ['breadcrumb_link' => ['title' => 'Service Users and Visitors', 'url' => $section_url, 'target' => '']],
        ],
        'current_crumb_label' => 'Feedback and Comments',
        'heading_tag' => matrix_page_seed_heading(1),
        'heading' => 'Feedback and comments',
        'content' => '<p>' . esc_html($hero_intro) . '</p>',
        'primary_button' => '',
        'hero_image' => '',
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
            ['link' => ['title' => 'Service User Participation', 'url' => $participation_url, 'target' => '']],
            ['link' => ['title' => 'Service User Experience Survey', 'url' => $survey_url, 'target' => '']],
            ['link' => ['title' => 'Frequently Asked Questions', 'url' => $faqs_url, 'target' => '']],
        ],
        'background_color' => '#F1F8F9',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => '',
        'heading_tag' => matrix_page_seed_heading(2),
        'accent_position' => 'below_heading',
        'content' => $welcome_body,
        'column_layout' => 'one_column',
        'layout_style' => 'image_left',
        'text_width' => 'wide',
        'background_type' => 'color',
        'background_color' => '#FFFFFF',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'How to give feedback or make a complaint',
        'heading_tag' => matrix_page_seed_heading(2),
        'accent_position' => 'below_heading',
        'content' => $how_to_body,
        'column_layout' => 'one_column',
        'layout_style' => 'image_left',
        'text_width' => 'wide',
        'background_type' => 'color',
        'background_color' => '#FFFFFF',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Contact details',
        'heading_tag' => matrix_page_seed_heading(3),
        'accent_position' => 'below_heading',
        'content' => $contact_body,
        'column_layout' => 'one_column',
        'layout_style' => 'image_left',
        'text_width' => 'wide',
        'background_type' => 'gradient',
        'background_gradient' => 'linear-gradient(-69.76deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'What to expect when you make a complaint',
        'heading_tag' => matrix_page_seed_heading(2),
        'accent_position' => 'below_heading',
        'content' => $expect_body,
        'column_layout' => 'one_column',
        'layout_style' => 'image_left',
        'text_width' => 'wide',
        'background_type' => 'color',
        'background_color' => '#FFFFFF',
    ],
    [
        'acf_fc_layout' => 'content_cta',
        'heading_tag' => matrix_page_seed_heading(2),
        'heading' => 'Clinical Governance Department',
        'body' => '<p>Contact our Clinical Governance Department to give feedback or make a complaint.</p>'
            . '<p><strong><a href="tel:012493200">' . esc_html($clinical_governance_phone) . '</a></strong><br>'
            . '<a href="mailto:' . esc_attr($clinical_governance_email) . '">' . esc_html($clinical_governance_email) . '</a></p>',
        'button_link' => [
            'title' => 'Email Clinical Governance',
            'url' => 'mailto:' . $clinical_governance_email,
            'target' => '',
        ],
        'background_type' => 'color',
        'background_color' => '#C6ECF4',
    ],
];

update_field('hero_content_blocks', [], $post_id);
update_field('flexible_content_blocks', $flexi_rows, $post_id);

$saved_rows = get_field('flexible_content_blocks', $post_id);
$saved_count = is_array($saved_rows) ? count($saved_rows) : 0;

if (class_exists('WP_CLI')) {
    if ($saved_count === count($flexi_rows)) {
        WP_CLI::success(sprintf(
            'Seeded Feedback and Comments page (%d) with %d flexi blocks.',
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
