<?php

/**
 * Seed Recruitment and useful information hub and child pages.
 *
 * Sources:
 * - https://www.stpatricks.ie/careers/work-with-us
 * - https://www.stpatricks.ie/careers/work-with-us/what-we-can-offer
 * - https://www.stpatricks.ie/careers/work-with-us/opportunities-for-mental-health-nurses-to-work-in-dublin
 * - https://www.stpatricks.ie/careers/work-with-us/attending-for-interview
 * - https://www.stpatricks.ie/careers/placements-and-work-experience
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-recruitment-and-useful-information.php
 */

require_once get_template_directory() . '/inc/migrate-functions.php';
require_once get_template_directory() . '/scripts/lib/page-seed-conventions.php';

$hub_id = (int) (get_page_by_path('recruitment-and-useful-information')?->ID ?? 0);
$staff_wellbeing_id = (int) (get_page_by_path('recruitment-and-useful-information/staff-wellbeing')?->ID ?? 0);
$how_to_apply_id = (int) (get_page_by_path('recruitment-and-useful-information/how-to-apply-for-a-role')?->ID ?? 0);
$how_to_get_work_experience_id = (int) (get_page_by_path('recruitment-and-useful-information/how-to-get-work-experience')?->ID ?? 0);
$careers_id = (int) (get_page_by_path('careers')?->ID ?? 0);
$attending_interview_id = (int) (get_page_by_path('careers/attending-an-interview')?->ID ?? 0);

if ($attending_interview_id === 0) {
    $attending_interview_id = (int) (get_page_by_path('attending-an-interview')?->ID ?? 0);
}

if ($hub_id === 0 || $staff_wellbeing_id === 0 || $how_to_apply_id === 0 || $how_to_get_work_experience_id === 0 || $careers_id === 0 || $attending_interview_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find one or more recruitment pages.');
    }

    exit(1);
}

wp_update_post([
    'ID' => $attending_interview_id,
    'post_parent' => $careers_id,
    'post_name' => 'attending-an-interview',
    'post_title' => 'Attending an interview',
]);

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

if (! function_exists('matrix_seed_recruitment_urls')) {
    /**
     * @return array<string, string>
     */
    function matrix_seed_recruitment_urls(): array
    {
        $home = home_url('/');

        return [
            'home' => $home,
            'about_us' => home_url('/about-us/'),
            'careers' => home_url('/careers/'),
            'vacancies' => home_url('/careers/') . '#current-vacancies',
            'hub' => get_permalink((int) (get_page_by_path('recruitment-and-useful-information')?->ID ?? 0)) ?: home_url('/recruitment-and-useful-information/'),
            'staff_wellbeing' => get_permalink((int) (get_page_by_path('recruitment-and-useful-information/staff-wellbeing')?->ID ?? 0)) ?: home_url('/recruitment-and-useful-information/staff-wellbeing/'),
            'how_to_apply' => get_permalink((int) (get_page_by_path('recruitment-and-useful-information/how-to-apply-for-a-role')?->ID ?? 0)) ?: home_url('/recruitment-and-useful-information/how-to-apply-for-a-role/'),
            'how_to_get_work_experience' => get_permalink((int) (get_page_by_path('recruitment-and-useful-information/how-to-get-work-experience')?->ID ?? 0)) ?: home_url('/recruitment-and-useful-information/how-to-get-work-experience/'),
            'attending_interview' => get_permalink((int) (get_page_by_path('careers/attending-an-interview')?->ID ?? 0))
                ?: home_url('/careers/attending-an-interview/'),
            'directions' => home_url('/directions-and-parking/'),
            'history' => home_url('/about-us/our-history/'),
            'strategy' => home_url('/about-us/our-present-and-future/'),
            'care_treatment' => home_url('/inpatient-hospital-care/'),
            'your_portal' => home_url('/your-portal/about-your-portal/'),
            'multidisciplinary_teams' => home_url('/about-us/multidisciplinary-teams/'),
            'our_team' => home_url('/about-us/our-team/'),
            'service_user_participation' => home_url('/service-users-and-visitors/service-user-participation/'),
            'advocacy' => home_url('/advocacy/'),
            'staff_nurse' => get_permalink(get_page_by_path('staff-nurse-psychiatric-adult-adolescent-services', OBJECT, 'careers')) ?: home_url('/careers/staff-nurse-psychiatric-adult-adolescent-services/'),
            'faqs' => home_url('/service-users-and-visitors/frequently-asked-questions-faqs/'),
            'referrals' => home_url('/make-a-referral-cta/'),
            'keepwell_news' => home_url('/media-centre/news/2019/march/keepwell-mark/'),
        ];
    }
}

if (! function_exists('matrix_seed_recruitment_section_links')) {
    /**
     * @return array<int, array{link: array{title: string, url: string, target: string}}>
     */
    function matrix_seed_recruitment_section_links(): array
    {
        $urls = matrix_seed_recruitment_urls();

        return [
            ['link' => ['title' => 'Recruitment and useful information', 'url' => $urls['hub'], 'target' => '']],
            ['link' => ['title' => 'Attending an interview', 'url' => $urls['attending_interview'], 'target' => '']],
            ['link' => ['title' => 'Staff Wellbeing', 'url' => $urls['staff_wellbeing'], 'target' => '']],
            ['link' => ['title' => 'How to get work experience', 'url' => $urls['how_to_get_work_experience'], 'target' => '']],
            ['link' => ['title' => 'How to apply for a role', 'url' => $urls['how_to_apply'], 'target' => '']],
        ];
    }
}

if (! function_exists('matrix_seed_recruitment_useful_links_block')) {
    /**
     * @return array<string, mixed>
     */
    function matrix_seed_recruitment_useful_links_block(): array
    {
        return matrix_page_seed_strip_padding([
            'acf_fc_layout' => 'useful_links',
            'heading_tag' => 'h2',
            'heading' => 'In this section',
            'variant' => 'flexi',
            'links' => matrix_seed_recruitment_section_links(),
            'background_color' => '#F1F8F9',
            'heading_color' => '#1E244B',
            'link_color' => '#1E244B',
        ]);
    }
}

if (! function_exists('matrix_seed_recruitment_cta_rows')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function matrix_seed_recruitment_cta_rows(): array
    {
        $urls = matrix_seed_recruitment_urls();

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

if (! function_exists('matrix_seed_recruitment_hero_block')) {
    /**
     * @param array<int, array{breadcrumb_link: array{title: string, url: string, target: string}}> $breadcrumbs
     * @return array<string, mixed>
     */
    function matrix_seed_recruitment_hero_block(
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

if (! function_exists('matrix_seed_recruitment_content_block')) {
    /**
     * @return array<string, mixed>
     */
    function matrix_seed_recruitment_content_block(
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

if (! function_exists('matrix_seed_recruitment_hub_rows')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function matrix_seed_recruitment_hub_rows(): array
    {
        $urls = matrix_seed_recruitment_urls();

        $breadcrumbs = [
            ['breadcrumb_link' => ['title' => 'Home', 'url' => $urls['home'], 'target' => '']],
            ['breadcrumb_link' => ['title' => 'Who we are', 'url' => $urls['about_us'], 'target' => '']],
            ['breadcrumb_link' => ['title' => 'Careers', 'url' => $urls['careers'], 'target' => '']],
        ];

        $hero_intro = 'At St Patrick\'s Mental Health Services (SPMHS), we are looking for talented and enthusiastic people to join our team and make a difference in mental healthcare.';

        $why_join_intro = '<p><strong>Our staff is our most important asset: working across diverse clinical and non-clinical roles, they support people towards mental health recovery every day.</strong></p>';
        $why_join_body = '<p>In turn, we work to create a positive, dynamic and rewarding workplace where our staff are supported to continually learn, expand their skills, progress their careers, and enjoy satisfaction in what they do.</p>'
            . '<p>By joining us, you can be part of a team that, with over 250 years experience, is proud of its <a href="' . esc_url($urls['history']) . '">rich history</a>, but is always <a href="' . esc_url($urls['strategy']) . '">driving innovation</a> and looking to an exciting future.</p>';

        $offer_intro = '<p>As Ireland\'s largest independent, not-for-profit mental health service provider, we have a strong reputation for <a href="' . esc_url($urls['care_treatment']) . '">delivering high quality mental healthcare</a>. We are continuing to grow and expand, offering many opportunities for collaborative working and career advancement.</p>';
        $offer_body = '<h3>We provide excellent pay and remuneration.</h3>'
            . '<p>We offer many employee benefits, including a generous contributory pension scheme and free Employee Assistance Programme.</p>'
            . '<h3>We take career development seriously.</h3>'
            . '<p>We recognise our staff\'s achievements and support our team to reach their career goals. We provide:</p>'
            . '<ul>'
            . '<li>internal and external training and professional development</li>'
            . '<li>research opportunities</li>'
            . '<li>funding for further education in relevant areas</li>'
            . '<li>paid study leave</li>'
            . '<li>opportunities for promotion and career progression</li>'
            . '<li>staff achievement and service awards.</li>'
            . '</ul>';

        $wellbeing_intro = '<p><strong>We place importance on employee wellbeing.</strong></p>';
        $wellbeing_body = '<p>We were the first hospital and first healthcare organisation in Ireland to be <a href="' . esc_url($urls['keepwell_news']) . '">awarded the IBEC KeepWell Mark</a> in 2018, in recognition of our workplace wellbeing, and have received this award every year since.</p>'
            . '<p>We offer:</p>'
            . '<ul>'
            . '<li>an active Staff Wellbeing Committee</li>'
            . '<li>flexible working arrangements</li>'
            . '<li>opportunities for remote or hybrid working where appropriate</li>'
            . '<li>a subsidised canteen and onsite gym</li>'
            . '<li>central locations</li>'
            . '<li>Bike to Work and TaxSaver Commuter Ticket schemes</li>'
            . '<li>an award-winning menopause workplace programme.</li>'
            . '</ul>'
            . '<p><a href="' . esc_url($urls['staff_wellbeing']) . '">Learn more about staff wellbeing at SPMHS</a>.</p>';

        $who_intro = '';
        $who_body = '<p>We are always welcoming of applications from dedicated, proactive people who enjoy being part of an inclusive, progressive team and share our vision of empowering people towards mentally healthy living.</p>'
            . '<p>Our team works across a wide variety of clinical and non-clinical areas, with our roles spanning from entry-level to leadership and management positions.</p>'
            . '<p>If you would like to work with us, you can <a href="' . esc_url($urls['vacancies']) . '">see our latest vacancies here</a>. If a role or area you are interested in is not currently advertised, you can email your cover letter and CV to our Human Resources (HR) Department at <a href="mailto:hr@stpatricks.ie">hr@stpatricks.ie</a>.</p>'
            . '<p>Please get in touch with the HR Department if you have any questions or need any help with your application: email hr@stpatricks.ie or <a href="tel:012493435">call 01 249 3435</a>. We cannot accept paper applications; all applications must be made online.</p>';

        $areas_body = '<ul>'
            . '<li>Administration</li><li>Finance</li><li>Communications</li><li>Environmental Services</li>'
            . '<li>ICT</li><li>Human Resources</li><li>Nursing</li><li>Occupational Therapy</li>'
            . '<li>Pharmacy</li><li>Psychology</li><li>Psychiatry</li><li>Research</li><li>Social Work</li>'
            . '</ul>'
            . '<p><a href="' . esc_url($urls['vacancies']) . '">See our current vacancies</a></p>';

        $link_cards = [
            [
                'title' => 'Staff Wellbeing',
                'description' => 'Find out how we support employee wellbeing, from the IBEC KeepWell Mark to our Staff Wellbeing Committee.',
                'url' => $urls['staff_wellbeing'],
                'image_id' => matrix_seed_migrate_attachment('/media/3445/staff-enjoying-break-time-at-st-patricks.png'),
                'tone' => 'bg1',
            ],
            [
                'title' => 'How to apply for a role',
                'description' => 'Guidance on applying online, contacting HR, and what we look for in candidates.',
                'url' => $urls['how_to_apply'],
                'image_id' => matrix_seed_migrate_attachment('/media/3440/apply-for-a-job-with-st-patricks.png'),
                'tone' => 'bg2',
            ],
            [
                'title' => 'Attending an interview',
                'description' => 'Prepare for your interview and learn more about joining our team.',
                'url' => $urls['attending_interview'],
                'image_id' => matrix_seed_migrate_attachment('/media/3441/attend-an-interview-at-st-patricks.png'),
                'tone' => 'bg3',
            ],
            [
                'title' => 'How to get work experience',
                'description' => 'Information on honorary, elective and nursing student placements.',
                'url' => $urls['how_to_get_work_experience'],
                'image_id' => matrix_seed_migrate_attachment('/media/3433/training-and-education-in-st-patricks-mental-health-services.png'),
                'tone' => 'bg4',
            ],
            [
                'title' => 'Current vacancies',
                'description' => 'Browse our latest career opportunities at SPMHS.',
                'url' => $urls['vacancies'],
                'image_id' => matrix_seed_migrate_attachment('/media/3448/recruitment-in-mental-healthcare-at-st-patricks.png'),
                'tone' => 'bg1',
            ],
            [
                'title' => 'Careers',
                'description' => 'Return to the main Careers page.',
                'url' => $urls['careers'],
                'image_id' => matrix_seed_migrate_attachment('/media/3432/staff-in-st-patricks-mental-health-services.png'),
                'tone' => 'bg2',
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

        $rows = [
            matrix_seed_recruitment_hero_block(
                'Recruitment and useful information',
                $hero_intro,
                $breadcrumbs,
                matrix_seed_migrate_attachment('/media/3448/recruitment-in-mental-healthcare-at-st-patricks.png'),
                [
                    'title' => 'View current vacancies',
                    'url' => $urls['vacancies'],
                    'target' => '',
                ]
            ),
            matrix_seed_recruitment_useful_links_block(),
            matrix_seed_recruitment_content_block(
                'Why join us?',
                $why_join_intro,
                $why_join_body,
                matrix_seed_migrate_attachment('/media/3419/patient-with-nurse-website.png'),
                'image_right',
                '#FFFFFF'
            ),
            matrix_seed_recruitment_content_block(
                'What do we offer our staff?',
                $offer_intro,
                $offer_body,
                matrix_seed_migrate_attachment('/media/3433/training-and-education-in-st-patricks-mental-health-services.png'),
                'image_left',
                '#FBFAF7'
            ),
            matrix_seed_recruitment_content_block(
                'Employee wellbeing',
                $wellbeing_intro,
                $wellbeing_body,
                matrix_seed_migrate_attachment('/media/3412/nurses-outside-the-gym-website.png'),
                'image_right',
                '#FFFFFF'
            ),
            matrix_seed_recruitment_content_block(
                'Who are we looking for?',
                $who_intro,
                $who_body,
                0,
                'image_left',
                '#FBFAF7'
            ),
            matrix_seed_recruitment_content_block(
                'Areas our staff work in',
                '',
                $areas_body,
                matrix_seed_migrate_attachment('/media/3409/nurses-outside-laughing.png'),
                'image_left',
                '#FFFFFF'
            ),
            matrix_page_seed_strip_padding([
                'acf_fc_layout' => 'about_links_grid',
                'heading_tag' => 'h2',
                'heading_text' => 'Useful links',
                'intro_text' => '<p>Explore more information about working with SPMHS.</p>',
                'links' => $about_links,
                'bg_color' => '#E9E2F7',
                'heading_color' => '#1E244B',
                'intro_color' => '#4A4B37',
                'columns' => '3',
            ]),
        ];

        return array_merge($rows, matrix_seed_recruitment_cta_rows());
    }
}

if (! function_exists('matrix_seed_staff_wellbeing_rows')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function matrix_seed_staff_wellbeing_rows(): array
    {
        $urls = matrix_seed_recruitment_urls();

        $breadcrumbs = [
            ['breadcrumb_link' => ['title' => 'Home', 'url' => $urls['home'], 'target' => '']],
            ['breadcrumb_link' => ['title' => 'Who we are', 'url' => $urls['about_us'], 'target' => '']],
            ['breadcrumb_link' => ['title' => 'Careers', 'url' => $urls['careers'], 'target' => '']],
            ['breadcrumb_link' => ['title' => 'Recruitment and useful information', 'url' => $urls['hub'], 'target' => '']],
        ];

        $hero_intro = 'We aim to maintain a warm, dynamic workplace where our staff can enjoy career growth, job satisfaction and support for their own wellbeing.';

        $values_intro = '<p><strong>We value our staff and work to create a positive, forward-thinking environment for our team.</strong></p>';
        $values_body = '<p>We offer:</p>'
            . '<ul>'
            . '<li><strong>Innovation:</strong> we are at the cutting edge of mental healthcare, including being the first healthcare organisation in Ireland to introduce an electronic health record (EHR) and <a href="' . esc_url($urls['your_portal']) . '">online patient portal</a> for our service users</li>'
            . '<li><strong>Collaboration:</strong> we take a <a href="' . esc_url($urls['multidisciplinary_teams']) . '">multidisciplinary approach to our work</a>, with our staff working closely together as well as engaging with our service users to actively involve them in all that we do</li>'
            . '<li><strong>Transformation:</strong> we put a focus on training, education and career development opportunities for our staff to support their promotion and career advancement</li>'
            . '<li><strong>Satisfaction:</strong> our staff work across <a href="' . esc_url($urls['care_treatment']) . '">diverse services</a> and in varied, rewarding roles, making a real impact in the lives of people going through mental health difficulties.</li>'
            . '</ul>';

        $benefits_body = '<p>We offer you many employee benefits, including:</p>'
            . '<ul>'
            . '<li>excellent salaries and remuneration packages</li>'
            . '<li>pension scheme</li>'
            . '<li>generous annual leave</li>'
            . '<li>ongoing training</li>'
            . '<li>paid study leave and funding for further education</li>'
            . '<li>flexible working arrangements, with remote and hybrid working opportunities</li>'
            . '<li>support for career advancement and professional development</li>'
            . '<li>a subsidised canteen and onsite gym</li>'
            . '<li>central locations with free onsite parking or Bike to Work and Taxsaver Commuter Ticket schemes</li>'
            . '<li>an award-winning menopause workplace programme.</li>'
            . '</ul>';

        $ethos_body = '<p>Ever since we were <a href="' . esc_url($urls['history']) . '">founded by Jonathan Swift more than 250 years ago</a>, our ethos has been grounded in human rights: we believe everyone has the right to a mentally healthy life, including our employees. As well as many <a href="' . esc_url($urls['advocacy']) . '">mental health awareness campaigns</a> that you can get involved in, our active Staff Wellbeing Committee and free Employee Assistance Programme offer events, activities and supports to make your own wellbeing a priority too.</p>';

        $keepwell_body = '<p><img src="' . esc_url(matrix_seed_attachment_url(matrix_seed_migrate_attachment('/media/2293/ibec-keep-well-mark.png'))) . '" alt="Accredited in Workplace Wellbeing - by the IBEC Keep Well Mark" width="246" height="108" loading="lazy" /></p>'
            . '<p>In 2018, we became the first hospital and the first healthcare organisation in Ireland to be awarded the <a href="https://www.ibec.ie/employer-hub/corporate-wellness/the-keepwell-mark-public-page" target="_blank" rel="noopener noreferrer">KeepWell Mark from IBEC</a>, and have been awarded this every year since. IBEC\'s KeepWell Mark is a workplace wellbeing accreditation that helps organisations prove their commitment to improving the lives of those who work for them.</p>';

        $nursing_body = '<p>We aim to create a warm, inviting and supportive working environment. As a nurse, you can enjoy the use of our recreational areas for nursing staff, including showers and games areas; onsite gym; and subsidised canteen.</p>'
            . '<p>Our active Staff Wellbeing Committee runs many different events and activities where you can meet colleagues and prioritise your own wellness, while our free Employee Assistance Programme provides further opportunities for support. Our menopause workplace programme is also award-winning.</p>'
            . '<p><a href="' . esc_url($urls['staff_nurse']) . '">See our mental health nursing career opportunities</a>.</p>';

        return array_merge([
            matrix_seed_recruitment_hero_block(
                'Staff Wellbeing',
                $hero_intro,
                $breadcrumbs,
                matrix_seed_migrate_attachment('/media/3445/staff-enjoying-break-time-at-st-patricks.png')
            ),
            matrix_seed_recruitment_useful_links_block(),
            matrix_seed_recruitment_content_block(
                'What we offer our team',
                $values_intro,
                $values_body,
                matrix_seed_migrate_attachment('/media/3449/staff-engagement-at-st-patricks-mental-health-services.png'),
                'image_right',
                '#FFFFFF'
            ),
            matrix_seed_recruitment_content_block(
                'Employee benefits',
                '<p><strong>By joining us, you become part of Ireland\'s largest independent, not-for-profit mental health service provider, bringing lots of opportunities for your career.</strong></p>',
                $benefits_body,
                matrix_seed_migrate_attachment('/media/3409/nurses-outside-laughing.png'),
                'image_left',
                '#FBFAF7'
            ),
            matrix_seed_recruitment_content_block(
                'Supporting your wellbeing',
                '',
                $ethos_body,
                matrix_seed_migrate_attachment('/media/3412/nurses-outside-the-gym-website.png'),
                'image_right',
                '#FFFFFF'
            ),
            matrix_seed_recruitment_content_block(
                'IBEC KeepWell Mark',
                '',
                $keepwell_body,
                0,
                'image_left',
                '#FBFAF7'
            ),
            matrix_seed_recruitment_content_block(
                'Wellbeing for nursing staff',
                '',
                $nursing_body,
                matrix_seed_migrate_attachment('/media/3418/one-nurse-at-gym-website.png') ?: matrix_seed_migrate_attachment('/media/3412/nurses-outside-the-gym-website.png'),
                'image_left',
                '#FFFFFF'
            ),
            matrix_page_seed_strip_padding([
                'acf_fc_layout' => 'content_cta',
                'heading_tag' => 'h2',
                'heading' => 'Interested in joining our team?',
                'body' => '<p>See our current vacancies or find out how to apply for a role with SPMHS.</p>',
                'button_link' => [
                    'title' => 'View current vacancies',
                    'url' => $urls['vacancies'],
                    'target' => '',
                ],
                'background_type' => 'color',
                'background_color' => '#CEF2EE',
            ]),
        ], matrix_seed_recruitment_cta_rows());
    }
}

if (! function_exists('matrix_seed_how_to_apply_rows')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function matrix_seed_how_to_apply_rows(): array
    {
        $urls = matrix_seed_recruitment_urls();

        $breadcrumbs = [
            ['breadcrumb_link' => ['title' => 'Home', 'url' => $urls['home'], 'target' => '']],
            ['breadcrumb_link' => ['title' => 'Who we are', 'url' => $urls['about_us'], 'target' => '']],
            ['breadcrumb_link' => ['title' => 'Careers', 'url' => $urls['careers'], 'target' => '']],
            ['breadcrumb_link' => ['title' => 'Recruitment and useful information', 'url' => $urls['hub'], 'target' => '']],
        ];

        $hero_intro = 'Find out who we are looking for, how to submit your application, and what to expect when applying for a role at SPMHS.';

        $who_body = '<p>We are always welcoming of applications from dedicated, proactive people who enjoy being part of an inclusive, progressive team and share our vision of empowering people towards mentally healthy living.</p>'
            . '<p>Our team works across a wide variety of clinical and non-clinical areas, with our roles spanning from entry-level to leadership and management positions.</p>';

        $apply_body = '<p>If you would like to work with us, you can <a href="' . esc_url($urls['vacancies']) . '">see our latest vacancies here</a>. When you find a suitable role, select <strong>View detail</strong> to read the full job description and complete the online application form.</p>'
            . '<p>If a role or area you are interested in is not currently advertised, you can email your cover letter and CV to our Human Resources (HR) Department at <a href="mailto:hr@stpatricks.ie">hr@stpatricks.ie</a>.</p>'
            . '<p>Please get in touch with the HR Department if you have any questions or need any help with your application: email hr@stpatricks.ie or <a href="tel:012493435">call 01 249 3435</a>. We cannot accept paper applications; all applications must be made online.</p>';

        $nursing_intro = '<p><strong>We are hiring registered psychiatric nurses for both adult and adolescent services.</strong></p>';
        $nursing_body = '<p>Whether you are a recent nursing graduate, returning to work after a career break, or simply looking for the next step forward in your nursing career, we welcome your application.</p>'
            . '<p>By becoming part of our nursing team, you can receive a permanent post and automatically join our enhanced nurse pay scale with a minimum starting salary of €46,930. In addition to your basic pay, you will also receive a sign-on bonus of €3,000 (subject to six months\' service) and a ward location allowance of €1,995.</p>'
            . '<p>Successful applicants will need to:</p>'
            . '<ul>'
            . '<li>be registered or eligible to enrol with the psychiatric register of the Nursing and Midwifery Board of Ireland (NMBI)</li>'
            . '<li>have an excellent level of clinical knowledge</li>'
            . '<li>prove an ability to establish meaningful working relationships within an organisation and with service users.</li>'
            . '</ul>'
            . '<p>Please note that post-registration experience is desirable. Current intern nurses due to qualify this year are welcome to apply. If you are a citizen from outside the European Economic Area (EEA), we can offer an employment permit at no cost to you.</p>'
            . '<p><a href="' . esc_url($urls['staff_nurse']) . '">View the Staff Nurse vacancy and apply online</a>.</p>';

        $agency_body = '<p>Please also be aware that we only work with recruitment agencies on rare occasions. In these cases, where we specify we will accept CVs from recruitment agencies, we only do so from those agencies engaging in ethical practice. Charging candidates for placements is not accepted practice: we do not expect recruitment agencies to do this and do not condone this behaviour.</p>'
            . '<p>All successful applicants will be placed on a panel and job offers will be made based on our requirements. SPMHS is an equal opportunities employer.</p>';

        return array_merge([
            matrix_seed_recruitment_hero_block(
                'How to apply for a role',
                $hero_intro,
                $breadcrumbs,
                matrix_seed_migrate_attachment('/media/3440/apply-for-a-job-with-st-patricks.png'),
                [
                    'title' => 'View current vacancies',
                    'url' => $urls['vacancies'],
                    'target' => '',
                ]
            ),
            matrix_seed_recruitment_useful_links_block(),
            matrix_seed_recruitment_content_block(
                'Who are we looking for?',
                '',
                $who_body,
                matrix_seed_migrate_attachment('/media/3442/nursing-staff-from-st-patricks.png'),
                'image_right',
                '#FFFFFF'
            ),
            matrix_seed_recruitment_content_block(
                'Applying for a role',
                '',
                $apply_body,
                matrix_seed_migrate_attachment('/media/3440/apply-for-a-job-with-st-patricks.png'),
                'image_left',
                '#FBFAF7'
            ),
            matrix_seed_recruitment_content_block(
                'Opportunities for psychiatric nurses',
                $nursing_intro,
                $nursing_body,
                matrix_seed_migrate_attachment('/media/3435/nurse-in-st-patricks-mental-health-services.png') ?: matrix_seed_migrate_attachment('/media/3419/patient-with-nurse-website.png'),
                'image_right',
                '#FFFFFF'
            ),
            matrix_seed_recruitment_content_block(
                'Recruitment agencies and equal opportunities',
                '',
                $agency_body,
                0,
                'image_left',
                '#FBFAF7'
            ),
            matrix_page_seed_strip_padding([
                'acf_fc_layout' => 'content_cta',
                'heading_tag' => 'h2',
                'heading' => 'Preparing for interview',
                'body' => '<p>If you have applied for a role, find out how to prepare for your interview with SPMHS.</p>',
                'button_link' => [
                    'title' => 'Attending an interview',
                    'url' => $urls['attending_interview'],
                    'target' => '',
                ],
                'background_type' => 'color',
                'background_color' => '#CEF2EE',
            ]),
        ], matrix_seed_recruitment_cta_rows());
    }
}

if (! function_exists('matrix_seed_attending_interview_rows')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function matrix_seed_attending_interview_rows(): array
    {
        $urls = matrix_seed_recruitment_urls();

        $breadcrumbs = [
            ['breadcrumb_link' => ['title' => 'Home', 'url' => $urls['home'], 'target' => '']],
            ['breadcrumb_link' => ['title' => 'Who we are', 'url' => $urls['about_us'], 'target' => '']],
            ['breadcrumb_link' => ['title' => 'Careers', 'url' => $urls['careers'], 'target' => '']],
        ];

        $hero_intro = 'If you are invited to interview for a position with us at St Patrick\'s Mental Health Services, we share all you need to know.';

        $panel_body = '<p>All our interviews are conducted by a panel. Our panels can include a member of our Human Resources (HR) Department, staff from the department that the role being interviewed for is based in, operational management, and/or a service user representative. You will be informed of who will be sitting on your interview panel ahead of time.</p>'
            . '<p>Some positions may have more than one interview round, and others may require the completion of a task as part of the interview process.</p>';

        $prepare_body = '<p>If attending for interview, you should ensure that you are:</p>'
            . '<ul>'
            . '<li>familiar with the job description and person specification of the post you have applied for</li>'
            . '<li>punctual and ready to take part in your interview on time</li>'
            . '<li>dressed appropriately for an interview.</li>'
            . '</ul>'
            . '<p>Please let us know in advance if any specific arrangements are needed to facilitate you in attending an interview.</p>'
            . '<p>During the interview, we welcome any questions you may have about our organisation, the position you are interviewing for, and/or your career prospects with us.</p>';

        $location_body = '<p>Our interviews can take place online or in-person. A member of our HR Department will make arrangements with you for your interview.</p>'
            . '<p>Online interviews take place over videocall, using Microsoft Teams. You will be sent a link to join a call at your appointed interview time.</p>'
            . '<p>In-person interviews are usually held on our main campus at St Patrick\'s University Hospital (SPUH) in Dublin 8. If you are attending an in-person interview, SPUH is very easily accessible: it is a five minute walk from Heuston Station if you are travelling by train or LUAS, and it is also well-served by a number of Dublin Bus and intercounty bus routes. Visitor car parking is also available if you are driving. You can <a href="' . esc_url($urls['directions']) . '">get directions to SPUH here</a>.</p>'
            . '<p>If, for any reason, you are unable to attend a scheduled interview, we ask that you let our HR Department know in advance by emailing <a href="mailto:hr@stpatricks.ie">hr@stpatricks.ie</a> or calling <a href="tel:012493435">01 249 3435</a>.</p>';

        return array_merge([
            matrix_seed_recruitment_hero_block(
                'Attending an interview',
                $hero_intro,
                $breadcrumbs,
                matrix_seed_migrate_attachment('/media/3441/attend-an-interview-at-st-patricks.png'),
                [
                    'title' => 'View current vacancies',
                    'url' => $urls['vacancies'],
                    'target' => '',
                ]
            ),
            matrix_seed_recruitment_useful_links_block(),
            matrix_seed_recruitment_content_block(
                'Who will be interviewing me?',
                '',
                $panel_body,
                0,
                'image_left',
                '#FFFFFF'
            ),
            matrix_seed_recruitment_content_block(
                'What should I prepare for an interview?',
                '',
                $prepare_body,
                matrix_seed_migrate_attachment('/media/3441/attend-an-interview-at-st-patricks.png'),
                'image_left',
                '#FBFAF7'
            ),
            matrix_seed_recruitment_content_block(
                'Where do interviews take place?',
                '',
                $location_body,
                matrix_seed_migrate_attachment('/media/3416/outside-hospital-entrance-featured-image-570-310-px.png'),
                'image_right',
                '#FFFFFF'
            ),
            matrix_page_seed_strip_padding([
                'acf_fc_layout' => 'content_cta',
                'heading_tag' => 'h2',
                'heading' => 'What we can offer',
                'body' => '<p>We provide generous employee benefits and value staff wellbeing. Find out more about working with SPMHS.</p>',
                'button_link' => [
                    'title' => 'Recruitment and useful information',
                    'url' => $urls['hub'],
                    'target' => '',
                ],
                'background_type' => 'color',
                'background_color' => '#CEF2EE',
            ]),
            matrix_page_seed_strip_padding([
                'acf_fc_layout' => 'content_cta',
                'heading_tag' => 'h2',
                'heading' => 'Learn more about us',
                'body' => '<p>Discover our history, strategy and the work we do across mental healthcare in Ireland.</p>',
                'button_link' => [
                    'title' => 'About us',
                    'url' => $urls['about_us'],
                    'target' => '',
                ],
                'background_type' => 'color',
                'background_color' => '#C6ECF4',
            ]),
        ], matrix_seed_recruitment_cta_rows());
    }
}

if (! function_exists('matrix_seed_how_to_get_work_experience_rows')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function matrix_seed_how_to_get_work_experience_rows(): array
    {
        $urls = matrix_seed_recruitment_urls();

        $breadcrumbs = [
            ['breadcrumb_link' => ['title' => 'Home', 'url' => $urls['home'], 'target' => '']],
            ['breadcrumb_link' => ['title' => 'Who we are', 'url' => $urls['about_us'], 'target' => '']],
            ['breadcrumb_link' => ['title' => 'Careers', 'url' => $urls['careers'], 'target' => '']],
            ['breadcrumb_link' => ['title' => 'Recruitment and useful information', 'url' => $urls['hub'], 'target' => '']],
        ];

        $hero_intro = 'St Patrick\'s Mental Health Services (SPMHS) facilitates honorary and elective mental healthcare placements where possible.';

        $placements_body = '<p>Honorary or elective placements in mental healthcare provide an opportunity for a person to gain insight into working in mental health or to fulfil their academic requirements. Some of the placements we facilitate include roles in nursing, social work, psychology and more.</p>'
            . '<p>Please note that these placements do not establish a relationship of employment between the person and SPMHS. We cannot offer standard employment benefits, including remuneration, to honorary or elective contracts.</p>';

        $apply_body = '<p>Honorary and elective placements are usually arranged by people seeking this work experience directly with the department concerned. The documentation needed to confirm the placement is provided by Human Resources.</p>'
            . '<p>If you would like to apply for a placement, please <a href="mailto:hr@stpatricks.ie">email your request to hr@stpatricks.ie</a>, from where it will be forwarded on to the relevant department.</p>'
            . '<p>In addition, we welcome volunteers in a number of areas of our organisation, such as involvement in our <a href="' . esc_url($urls['service_user_participation']) . '">service user and supporter networks</a>. If you are interested in a volunteer role, please email <a href="mailto:hr@stpatricks.ie">hr@stpatricks.ie</a>.</p>';

        $nursing_intro = '<p><strong>We offer placements for nursing students.</strong></p>';
        $nursing_body = '<p>If you\'re interested to learn more, take a look at the different areas our nurses work in across SPMHS.</p>'
            . '<p><a href="' . esc_url($urls['our_team']) . '">Meet our nursing team</a>.</p>';

        return array_merge([
            matrix_seed_recruitment_hero_block(
                'How to get work experience',
                $hero_intro,
                $breadcrumbs,
                matrix_seed_migrate_attachment('/media/3433/training-and-education-in-st-patricks-mental-health-services.png')
            ),
            matrix_seed_recruitment_useful_links_block(),
            matrix_seed_recruitment_content_block(
                'Placements and work experience',
                '',
                $placements_body,
                0,
                'image_left',
                '#FFFFFF'
            ),
            matrix_seed_recruitment_content_block(
                'Applying for work experience',
                '',
                $apply_body,
                matrix_seed_migrate_attachment('/media/3444/nursing-students-st-patricks.png'),
                'image_left',
                '#FBFAF7'
            ),
            matrix_seed_recruitment_content_block(
                'Are you a nursing student?',
                $nursing_intro,
                $nursing_body,
                matrix_seed_migrate_attachment('/media/3436/student-nurses.png'),
                'image_right',
                '#FFFFFF'
            ),
            matrix_page_seed_strip_padding([
                'acf_fc_layout' => 'content_cta',
                'heading_tag' => 'h2',
                'heading' => 'Looking for a job with SPMHS?',
                'body' => '<p>Explore our current vacancies and find out how to apply for a role with us.</p>',
                'button_link' => [
                    'title' => 'View current vacancies',
                    'url' => $urls['vacancies'],
                    'target' => '',
                ],
                'background_type' => 'color',
                'background_color' => '#CEF2EE',
            ]),
            matrix_page_seed_strip_padding([
                'acf_fc_layout' => 'content_cta',
                'heading_tag' => 'h2',
                'heading' => 'Learn more about our organisation',
                'body' => '<p>Find out about our history, strategy and multidisciplinary teams.</p>',
                'button_link' => [
                    'title' => 'About us',
                    'url' => $urls['about_us'],
                    'target' => '',
                ],
                'background_type' => 'color',
                'background_color' => '#C6ECF4',
            ]),
        ], matrix_seed_recruitment_cta_rows());
    }
}

if (! function_exists('matrix_seed_save_recruitment_page')) {
    function matrix_seed_save_recruitment_page(int $post_id, array $flexi_rows, string $title = ''): void
    {
        if ($title !== '') {
            wp_update_post([
                'ID' => $post_id,
                'post_title' => $title,
            ]);
        }

        update_field('hero_content_blocks', [], $post_id);
        update_field('flexible_content_blocks', $flexi_rows, $post_id);
        update_post_meta($post_id, '_matrix_migrate_restyle_skip', '1');
        update_post_meta($post_id, '_matrix_migrate_restyled', 'manual');
    }
}

matrix_seed_save_recruitment_page($hub_id, matrix_seed_recruitment_hub_rows(), 'Recruitment and useful information');
matrix_seed_save_recruitment_page($staff_wellbeing_id, matrix_seed_staff_wellbeing_rows());
matrix_seed_save_recruitment_page($how_to_apply_id, matrix_seed_how_to_apply_rows());
matrix_seed_save_recruitment_page($attending_interview_id, matrix_seed_attending_interview_rows());
matrix_seed_save_recruitment_page($how_to_get_work_experience_id, matrix_seed_how_to_get_work_experience_rows());

flush_rewrite_rules(false);

if (class_exists('WP_CLI')) {
    WP_CLI::success(sprintf(
        'Seeded recruitment pages: hub (ID %d), staff wellbeing (ID %d), how to apply (ID %d), attending an interview (ID %d), how to get work experience (ID %d).',
        $hub_id,
        $staff_wellbeing_id,
        $how_to_apply_id,
        $attending_interview_id,
        $how_to_get_work_experience_id
    ));
}
