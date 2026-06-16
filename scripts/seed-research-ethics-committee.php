<?php

/**
 * Seed Research Ethics Committee (/research-ethics-committee/) from live-site copy.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-research-ethics-committee.php
 */

require_once get_template_directory() . '/inc/migrate-functions.php';
require_once __DIR__ . '/lib/research-page-seed-data.php';
require_once __DIR__ . '/lib/page-seed-conventions.php';

$post = get_page_by_path('research-ethics-committee');

if (! $post instanceof WP_Post) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at /research-ethics-committee/.');
    }

    exit(1);
}

$post_id = (int) $post->ID;

if (! function_exists('matrix_seed_media_url')) {
    function matrix_seed_media_url(string $path): string
    {
        return matrix_migrate_live_url($path);
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

if (! function_exists('matrix_seed_ensure_team_category')) {
    function matrix_seed_ensure_team_category(string $slug, string $name): int
    {
        $term = get_term_by('slug', $slug, 'team_member_category');

        if ($term instanceof WP_Term) {
            return (int) $term->term_id;
        }

        $created = wp_insert_term($name, 'team_member_category', ['slug' => $slug]);

        if (is_wp_error($created)) {
            return 0;
        }

        return (int) ($created['term_id'] ?? 0);
    }
}

if (! function_exists('matrix_seed_ensure_team_member')) {
    function matrix_seed_ensure_team_member(
        string $title,
        string $job_title,
        int $image_id,
        array $term_ids,
        string $seed_key,
        int $menu_order = 0
    ): int {
        $existing = get_posts([
            'post_type' => 'team_members',
            'post_status' => 'any',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => '_matrix_seed_key',
                    'value' => $seed_key,
                ],
            ],
        ]);

        if ($existing !== []) {
            $member_id = (int) $existing[0]->ID;
            wp_update_post([
                'ID' => $member_id,
                'post_title' => $title,
                'post_status' => 'publish',
                'menu_order' => $menu_order,
            ]);
        } else {
            $member_id = wp_insert_post([
                'post_type' => 'team_members',
                'post_status' => 'publish',
                'post_title' => $title,
                'menu_order' => $menu_order,
            ]);

            if (is_wp_error($member_id) || ! $member_id) {
                return 0;
            }

            update_post_meta((int) $member_id, '_matrix_seed_key', $seed_key);
        }

        update_field('job_title', $job_title, $member_id);

        if ($image_id > 0) {
            set_post_thumbnail($member_id, $image_id);
        }

        if ($term_ids !== []) {
            wp_set_object_terms($member_id, array_map('intval', $term_ids), 'team_member_category', false);
        }

        return (int) $member_id;
    }
}

$home = home_url('/');
$research_url = home_url('/research/');
$spire_url = home_url('/research/spire/');
$research_projects_url = home_url('/research-projects/');
$ethics_url = home_url('/research-ethics-committee/');
$board_governors_url = matrix_seed_media_url('/about-us/hospital-organisation');
$spuh_url = home_url('/st-patrick-s-university-hospital/');
$lucan_url = home_url('/st-patricks-lucan/');
$dean_clinics_url = home_url('/outpatient-care-dean-clinics/');
$research_strategy_url = matrix_seed_media_url('/media-centre/news/2024/april/research-update-spring-2024');
$research_publications_url = matrix_seed_media_url('/research/research-publications');
$faqs_url = home_url('/service-users-and-visitors/frequently-asked-questions-faqs/');
$referrals_url = home_url('/make-a-referral/');

$assets = matrix_get_research_page_seed_assets();
$banner_image_id = matrix_migrate_attachment_id_for_source_path(
    '/media/1675/st-patricks-mental-health-services-research-department-banner-min.jpg'
);
$hero_image_id = $banner_image_id > 0 ? $banner_image_id : $assets['hero_image_id'];

$hero_intro = 'The Research Ethics Committee (REC) reviews applications to undertake research that requires ethical approval.';

$role_body = '<p>The REC is an independent, multidisciplinary committee which reports to <a href="' . esc_url($board_governors_url) . '">our Board of Governors</a> here in SPMHS.</p>'
    . '<p>The committee is responsible for reviewing research involving human participants, their samples or their data, in order to ensure that their dignity, rights, and welfare are protected. The REC assesses the potential benefits of the research which may be balanced against the degree of risk and potential harm.</p>'
    . '<p>The REC reviews applications to undertake research in <a href="' . esc_url($spuh_url) . '">St Patrick&rsquo;s University Hospital</a>, <a href="' . esc_url($lucan_url) . '">St Patrick&rsquo;s Hospital Lucan</a>, and the <a href="' . esc_url($dean_clinics_url) . '">Dean Clinics</a>.</p>'
    . '<p>The REC works closely with the <a href="' . esc_url($research_url) . '">Academic Institute</a>, which works on both promoting research and building a strong research culture here in SPMHS. The Academic Institute is also responsible for the institutional approval of research projects, once REC approval has been achieved.</p>';

$rec_profile_image_id = (int) attachment_url_to_postid(
    content_url('uploads/2026/05/1b550473-c5ff-4a11-971d-51001791ec7a.jpg')
);

if ($rec_profile_image_id <= 0) {
    $rec_profile_image_id = 512;
}

$rec_members = [
    ['name' => 'Ms Marian Corcoran', 'job_title' => 'Layperson; Non-executive Board Director.'],
    ['name' => 'Ms Elaine Donnelly', 'job_title' => 'Head of Social Work at SPMHS.'],
    ['name' => 'Professor Frank Doyle', 'job_title' => 'Associate Professor, Department of Health Psychology, RCSI University of Medicine and Health Sciences.'],
    ['name' => 'Professor Paul Fearon (Vice-Chair)', 'job_title' => 'Medical Director of SPMHS.'],
    ['name' => 'Dr Eleisa Heron', 'job_title' => 'Biostatistical Genomics.'],
    ['name' => 'Dr Fiona Keogh', 'job_title' => 'Research Psychologist.'],
    ['name' => 'Mr Shane Kirwan', 'job_title' => 'Nursing Practice Development Coordinator at SPMHS.'],
    ['name' => 'Dr Paulina Kowalska-Beda', 'job_title' => 'Clinical Audit Facilitator at SPMHS.'],
    ['name' => 'Ms Joyce Loughnan', 'job_title' => 'Layperson; Non-executive Board Director.'],
    ['name' => 'Dr Denise Lundon', 'job_title' => 'GP.'],
    ['name' => 'Dr Cliodhna McHugh', 'job_title' => 'Layperson; Postdoctoral researcher.'],
    ['name' => 'Professor Joyce O\'Connor (Chair)', 'job_title' => 'Governor of SPMHS; Chair/Co-Founder of BlockW.'],
    ['name' => 'Ms Frankie Prendergast', 'job_title' => 'Digital Health Applications Programme Manager at SPMHS.'],
    ['name' => 'Mr John Stanley', 'job_title' => 'Barrister.'],
    ['name' => 'Mr John Woods', 'job_title' => 'Data Protection Officer at SPMHS.'],
];

$rec_team_category_id = matrix_seed_ensure_team_category('research-ethics-committee', 'Research Ethics Committee');

foreach ($rec_members as $index => $member) {
    matrix_seed_ensure_team_member(
        $member['name'],
        $member['job_title'],
        $rec_profile_image_id,
        $rec_team_category_id > 0 ? [$rec_team_category_id] : [],
        'rec-member-' . sanitize_title($member['name']),
        $index + 1
    );
}

$apply_membership_body = '<p>The REC is currently looking for service users, a psychiatrist and someone who works in occupational health to join the committee.</p>'
    . '<p>If you are interested in joining the REC, please send a letter of interest and your CV to the REC Administrator, whose contact details you can find at the end of this page.</p>';

$applications_intro = '<p>You can find information on the application process below; just click on the arrows below to see more details.</p>';

$prepare_application_body = '<p>All applicants to the REC should have undertaken a research design and methodology educational programme.</p>'
    . '<p>You should also involve your academic supervisor and onsite supervisor in preparing your application. The role of the academic supervisor is to provide support and advice for the research and to sign off on the quality of the research application. The onsite supervisor&rsquo;s role is to sign off on the necessary support and resources required within SPMHS to complete your research.</p>'
    . '<p>You should complete and get a certificate from the Research Integrity Programme, which can be accessed through our Academic Institute by emailing <a href="mailto:gdonohue@stpatricks.ie">gdonohue@stpatricks.ie</a>.</p>'
    . '<p><a href="https://hseresearch.ie/patient-and-public-involvement-in-research/" target="_blank" rel="noopener noreferrer">Patient and Public Involvement</a> (PPI) is now an established aspect of research and should be included in your process. If PPI involvement is not included, please provide reasons for this. In SPMHS, we have set up a PPI panel, which can be accessed through the <a href="' . esc_url($research_url) . '">Academic Institute</a>.</p>'
    . '<p>It is crucial that you are familiar with your legal and regulatory obligations in the field of data protection, particularly since the introduction of <a href="https://gdpr.eu/what-is-gdpr/" target="_blank" rel="noopener noreferrer">General Data Protection Regulation</a> (GDPR). The Health Research Board (HRB) offers a helpful <a href="https://www.youtube.com/watch?v=jcfSRpvxY5A" target="_blank" rel="noopener noreferrer">webinar on health research regulations</a>, along with <a href="https://www.hrb.ie/funding/gdpr-guidance-for-researchers/" target="_blank" rel="noopener noreferrer">guidelines for researchers</a>.</p>'
    . '<p>We have a <a href="' . esc_url(matrix_seed_media_url('/media/2312/spmhs-data-protection-impact-assessment-policy.docx')) . '" target="_blank" rel="noopener noreferrer">policy for completing Data Protection Impact Assessments</a> in place. You will need to complete a <a href="' . esc_url(matrix_seed_media_url('/media/2314/spmhs_dpia_threshold_test.docx')) . '">DPIA screening test</a> and you should contact the Data Protection Officer (DPO) once you have done so. The DPO can provide guidance on the additional measures you may need to take dependent on the outcome of this screening test. This should happen ahead of submitting an REC application. For this, or if you have any other queries regarding GDPR in relation to your research, please direct these to our DPO by emailing <a href="mailto:dpo@stpatricks.ie">dpo@stpatricks.ie</a>.</p>'
    . '<p>You should also review and be familiar with:</p>'
    . '<ul>'
    . '<li>the <a href="' . esc_url(matrix_seed_media_url('/media/3325/governance-and-sop-june-2021.pdf')) . '" target="_blank" rel="noopener noreferrer">REC&rsquo;s Standard Operating Procedures</a></li>'
    . '<li>the Health Service Executive&rsquo;s <a href="https://hseresearch.ie/wp-content/uploads/2023/02/HSE-National-Policy-for-Consent-in-Health-and-Social-Care-Research-compressed.pdf" target="_blank" rel="noopener noreferrer">National Policy for Consent in Health and Social Care Research</a>, preparing consent forms in line with this</li>'
    . '<li>all other hospital policies relating to research (please contact the Academic Institute for details).</li>'
    . '</ul>';

$submit_application_body = '<p>To submit an application, please complete the following documents and submit them by email to the REC administrator, whose contact details are at the bottom of this page:</p>'
    . '<ul>'
    . '<li><a href="' . esc_url(matrix_seed_media_url('/media/4212/standard_application_form_hserd_2025edition.docx')) . '" target="_blank" rel="noopener noreferrer">Application form</a></li>'
    . '<li><a href="' . esc_url(matrix_seed_media_url('/media/3562/research-ethics-committee-declaration-page.doc')) . '" target="_blank" rel="noopener noreferrer">Declaration page</a></li>'
    . '<li><a href="' . esc_url(matrix_seed_media_url('/media/4213/checklist.docx')) . '" target="_blank" rel="noopener noreferrer">Checklist</a></li>'
    . '<li><a href="' . esc_url(matrix_seed_media_url('/media/2314/spmhs_dpia_threshold_test.docx')) . '" target="_blank" rel="noopener noreferrer">DPIA screening test</a></li>'
    . '<li><a href="' . esc_url(matrix_seed_media_url('/media/3244/data-protection-impact-assessment-2021.pdf')) . '" target="_blank" rel="noopener noreferrer">Full DPIA (if required)</a></li>'
    . '<li>Research Integrity Cert.</li>'
    . '</ul>'
    . '<p>Where your research involves service users, staff members, or other participants, please note that a participant information sheet and a separate consent form are also needed. <a href="' . esc_url(matrix_seed_media_url('/media/2144/patient-information-leaflet-template.docx')) . '">See guidance on a participant information sheet here</a>, or <a href="' . esc_url(matrix_seed_media_url('/media/2145/sample-consent-form.doc')) . '">get guidance on the consent form here</a>.</p>'
    . '<p>Applications submitted in any other format will not be accepted.</p>'
    . '<p>The submission deadline is three weeks before the next REC meeting: you can <a href="' . esc_url(matrix_seed_media_url('/media/4211/research-ethics-committee-meeting-dates-2026.pdf')) . '" target="_blank" rel="noopener noreferrer">find upcoming REC meeting dates here</a>. This allows for an initial review of the application by the REC Administrator to see if any documents are missing or to clarify any obvious queries. All documentation is circulated to the committee members 10 days before the next REC meeting so that they have time to review it in advance of the meeting. You will be informed if your application has been accepted for review or not.</p>'
    . '<p>The REC is not in a position to review your submission if it is of a poor standard or documentation is missing. It is the responsibility of your academic supervisor to review your research application or provide suggestions on how to improve your application.</p>';

$sop_url = esc_url(matrix_seed_media_url('/media/3325/governance-and-sop-june-2021.pdf'));
$progress_report_url = esc_url(matrix_seed_media_url('/media/3327/progress-report-version-60.docx'));
$end_of_study_url = esc_url(matrix_seed_media_url('/media/3326/end-of-study-report-version-20.docx'));

$after_application_body = '<h3>Response from REC</h3>'
    . '<p>Following a REC meeting where your application is reviewed, you will receive a response from the REC within a working week. REC members may have queries that they will want you to address and approval will not be granted until you respond to these queries. This process can take anything from a few days to a number of weeks, depending on the amount of queries raised and your response time. It is good to factor this timeframe into your research process, and highlights the importance of ensuring your application is of the highest quality before you submit it.</p>'
    . '<h3>Conditions of approval</h3>'
    . '<p>All researchers who gain ethical approval will need to meet the following standard conditions.</p>'
    . '<p>REC approval is the first phase of the overall research approval process. Once ethical approval is granted, the REC engages with the Academic Institute to ensure Phase Two, which involves SPMHS management approval, is initiated. More information is available in the <a href="' . $sop_url . '" target="_blank" rel="noopener noreferrer">REC Standard Operating Procedures</a>.</p>'
    . '<p>Research must be undertaken according to the approved proposal. Any changes to the proposal must be approved before they are implemented, except where a change is made to remove immediate risk to participants.</p>'
    . '<p>An <a href="' . $progress_report_url . '" target="_blank" rel="noopener noreferrer">annual report</a> must be submitted on or before one year&rsquo;s passing of the date approval was given, and an <a href="' . $end_of_study_url . '">end-of-study report</a> submitted when research is completed or ended.</p>'
    . '<p>Ethical approval depends on ongoing compliance with applicable legal requirements, and with SPMHS policies, procedures and governance requirements. Anything that might call for a review of ethical approval of a project must be reported as soon as possible. This includes:</p>'
    . '<ul>'
    . '<li>Serious or unexpected adverse events, which should be reported within 72 hours</li>'
    . '<li>Unforeseen events that might affect continued ethical acceptability of the project.</li>'
    . '</ul>'
    . '<p>Changes to people working on the research must be reported and approved. People working on the project must:</p>'
    . '<ul>'
    . '<li>be sufficiently qualified by education, training and experience for their role, or adequately supervised</li>'
    . '<li>obtain an honorary contract from the hospital, where the person is not an employee of SPMHS</li>'
    . '<li>disclose any actual or potential conflicts of interest, including any financial or other interest or affiliation, as relevant to the project.</li>'
    . '</ul>'
    . '<p>Data and primary materials must be retained and stored in accordance with the relevant legislation and SPMHS guidelines.</p>'
    . '<p>The Principal Investigator (PI) is the lead researcher and the person with overall responsibility for the conduct of the study. When a project is being conducted by a team, the PI is the team lead. The PI is responsible for ensuring all others involved will conduct the research in accordance with the above.</p>';

$sharing_body = '<p>As in the conditions of approval, you must submit an <a href="' . $progress_report_url . '" target="_blank" rel="noopener noreferrer">annual report</a> before or at one year since the date you received approval for the research, and an <a href="' . $end_of_study_url . '" target="_blank" rel="noopener noreferrer">end-of-study report</a> following the end or completion of your research.</p>'
    . '<p>It is also expected that all researchers or research teams communicate results of their research, in line with <a href="' . esc_url($research_strategy_url) . '">SPMHS&rsquo; Research Strategy</a>; this includes <a href="' . esc_url($research_publications_url) . '">publishing the results of their research</a> once completed. Copies of publications, presentations and/or posters should be sent to the REC.</p>';

$contact_body = '<p>All queries in relation to the REC can be directed to James Braddock, REC Administrator, St Patrick&rsquo;s University Hospital, James&rsquo;s Street, Dublin 8.</p>'
    . '<ul>'
    . '<li>Phone: 01 249 3345 (9am to 5pm, Monday to Wednesday only)</li>'
    . '<li>Email: <a href="mailto:jbraddock@stpatricks.ie">jbraddock@stpatricks.ie</a></li>'
    . '</ul>';

$meeting_dates_pdf_url = matrix_seed_media_url('/media/4211/research-ethics-committee-meeting-dates-2026.pdf');

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
                    'title' => 'Research',
                    'url' => $research_url,
                    'target' => '',
                ],
            ],
        ],
        'current_crumb_label' => 'Research Ethics Committee',
        'heading_tag' => matrix_page_seed_heading(1),
        'heading' => 'Research Ethics Committee',
        'content' => '<p>' . esc_html($hero_intro) . '</p>',
        'hero_image' => $hero_image_id,
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
            ['link' => ['title' => 'Research Repository', 'url' => $spire_url, 'target' => '']],
            ['link' => ['title' => 'Research Projects', 'url' => $research_projects_url, 'target' => '']],
            ['link' => ['title' => 'Research Ethics Committee', 'url' => $ethics_url, 'target' => '']],
        ],
        'background_color' => '#F1F8F9',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Role of the REC',
        'heading_tag' => matrix_page_seed_heading(2),
        'accent_position' => 'below_heading',
        'intro_text' => '',
        'content' => $role_body,
        'image' => '',
        'column_layout' => 'one_column',
        'layout_style' => 'image_left',
        'text_width' => 'wide',
        'background_type' => 'color',
        'background_color' => '#FFFFFF',
    ],
    [
        'acf_fc_layout' => 'team_members',
        'heading_tag' => matrix_page_seed_heading(3),
        'heading' => 'See our REC members here',
        'intro' => '',
        'layout_style' => 'standard_grid',
        'source_mode' => 'category',
        'selected_team_categories' => $rec_team_category_id > 0 ? [$rec_team_category_id] : [],
        'posts_per_page' => count($rec_members),
        'section_background' => '#FFFFFF',
        'card_background_color' => '#FFFFFF',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Apply for REC membership',
        'heading_tag' => matrix_page_seed_heading(3),
        'accent_position' => 'below_heading',
        'intro_text' => '',
        'content' => $apply_membership_body,
        'image' => '',
        'column_layout' => 'one_column',
        'layout_style' => 'image_left',
        'text_width' => 'wide',
        'background_type' => 'color',
        'background_color' => '#FFFFFF',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Applications to the REC',
        'heading_tag' => matrix_page_seed_heading(2),
        'accent_position' => 'below_heading',
        'intro_text' => $applications_intro,
        'content' => '',
        'image' => '',
        'column_layout' => 'one_column',
        'layout_style' => 'image_left',
        'text_width' => 'wide',
        'background_type' => 'color',
        'background_color' => '#FBFAF7',
    ],
    [
        'acf_fc_layout' => 'content_accordion',
        'layout_style' => 'default',
        'section_background' => '#FBFAF7',
        'panel_background' => 'linear-gradient(135deg, #F6EDE0 0%, #F5F0E0 48%, #F4F5DE 100%)',
        'open_panel_background' => 'linear-gradient(135deg, #F6EDE0 0%, #F5F0E0 48%, #F4F5DE 100%)',
        'items' => [
            matrix_seed_accordion_item('What to prepare before you make an application', $prepare_application_body, true),
            matrix_seed_accordion_item('How to submit an application', $submit_application_body),
            matrix_seed_accordion_item('What to expect after your application', $after_application_body),
        ],
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Sharing your research',
        'heading_tag' => matrix_page_seed_heading(2),
        'accent_position' => 'below_heading',
        'intro_text' => '',
        'content' => $sharing_body,
        'image' => '',
        'column_layout' => 'one_column',
        'layout_style' => 'image_left',
        'text_width' => 'wide',
        'background_type' => 'color',
        'background_color' => '#FFFFFF',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Contact',
        'heading_tag' => matrix_page_seed_heading(2),
        'accent_position' => 'below_heading',
        'intro_text' => '',
        'content' => $contact_body,
        'image' => '',
        'column_layout' => 'one_column',
        'layout_style' => 'image_left',
        'text_width' => 'wide',
        'background_type' => 'color',
        'background_color' => '#FBFAF7',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Meeting dates',
        'heading_tag' => matrix_page_seed_heading(2),
        'accent_position' => 'below_heading',
        'intro_text' => '<p>Research Ethics Committee meeting dates 2026</p>',
        'content' => '',
        'document_link' => [
            'title' => 'Research Ethics Committee Meeting Dates 2026',
            'url' => $meeting_dates_pdf_url,
            'target' => '_blank',
        ],
        'image' => '',
        'column_layout' => 'one_column',
        'layout_style' => 'image_left',
        'text_width' => 'wide',
        'background_type' => 'color',
        'background_color' => '#FFFFFF',
    ],
    [
        'acf_fc_layout' => 'content_cta',
        'heading_tag' => matrix_page_seed_heading(2),
        'heading' => 'Queries',
        'body' => '<p>For general queries, please call us. For more on mental health and our services, see our frequently asked questions (FAQs).</p><p><strong>01 249 3200</strong></p>',
        'button_link' => [
            'title' => 'See our FAQs',
            'url' => $faqs_url,
            'target' => '',
        ],
        'background_type' => 'color',
        'background_color' => '#C6ECF4',
    ],
    [
        'acf_fc_layout' => 'content_cta',
        'heading_tag' => matrix_page_seed_heading(2),
        'heading' => 'Referrals',
        'body' => '<p>Contact our Referral and Assessment Service for queries regarding referrals to our services.</p><p><strong>01 249 3635</strong></p>',
        'button_link' => [
            'title' => 'See more from our referrals team',
            'url' => $referrals_url,
            'target' => '',
        ],
        'background_type' => 'color',
        'background_color' => '#CEF2EE',
    ],
];

update_field('hero_content_blocks', [], $post_id);
update_field('flexible_content_blocks', $flexi_rows, $post_id);
update_post_meta($post_id, '_matrix_migrate_restyle_skip', '1');
update_post_meta($post_id, '_matrix_migrate_restyled', 'manual');

$saved_rows = get_field('flexible_content_blocks', $post_id);
$saved_count = is_array($saved_rows) ? count($saved_rows) : 0;

if (class_exists('WP_CLI')) {
    if ($saved_count === count($flexi_rows)) {
        WP_CLI::success(sprintf(
            'Seeded /research-ethics-committee/ page (%d) with %d flexi blocks.',
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
}
