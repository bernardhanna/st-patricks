<?php

/**
 * Seed Service Users FAQ page with content from stpatricks.ie/getting-help/faqs.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-service-users-faqs.php
 */

require_once __DIR__ . '/lib/page-seed-conventions.php';
require_once get_template_directory() . '/inc/migrate-functions.php';

if (! function_exists('matrix_seed_su_faq_url')) {
    function matrix_seed_su_faq_url(string $path): string
    {
        return home_url('/' . trim($path, '/') . '/');
    }
}

if (! function_exists('matrix_seed_su_faq_link')) {
    function matrix_seed_su_faq_link(string $path, string $label): string
    {
        return '<a href="' . esc_url(matrix_seed_su_faq_url($path)) . '">' . esc_html($label) . '</a>';
    }
}

if (! function_exists('matrix_seed_su_faq_external_link')) {
    function matrix_seed_su_faq_external_link(string $url, string $label): string
    {
        return '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . esc_html($label) . '</a>';
    }
}

if (! function_exists('matrix_seed_su_faq_ensure_term')) {
    function matrix_seed_su_faq_ensure_term(string $slug, string $name, int $parent_id = 0): int
    {
        $existing = get_term_by('slug', $slug, 'faq_category');

        if ($existing instanceof WP_Term) {
            if ($parent_id > 0 && (int) $existing->parent !== $parent_id) {
                wp_update_term((int) $existing->term_id, 'faq_category', ['parent' => $parent_id]);
            }

            return (int) $existing->term_id;
        }

        $args = ['slug' => $slug];

        if ($parent_id > 0) {
            $args['parent'] = $parent_id;
        }

        $created = wp_insert_term($name, 'faq_category', $args);

        if (is_wp_error($created)) {
            return 0;
        }

        return (int) ($created['term_id'] ?? 0);
    }
}

if (! function_exists('matrix_seed_su_faq_ensure_post')) {
    function matrix_seed_su_faq_ensure_post(string $title, string $content, string $seed_key, array $term_ids, int $menu_order = 0): int
    {
        $existing = get_posts([
            'post_type' => 'faqs',
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
            $faq_id = (int) $existing[0]->ID;
            wp_update_post([
                'ID' => $faq_id,
                'post_title' => $title,
                'post_content' => $content,
                'post_status' => 'publish',
                'menu_order' => $menu_order,
            ]);
        } else {
            $faq_id = (int) wp_insert_post([
                'post_type' => 'faqs',
                'post_status' => 'publish',
                'post_title' => $title,
                'post_content' => $content,
                'menu_order' => $menu_order,
            ]);

            if ($faq_id < 1) {
                return 0;
            }

            update_post_meta($faq_id, '_matrix_seed_key', $seed_key);
        }

        if ($term_ids !== []) {
            wp_set_object_terms($faq_id, array_map('intval', $term_ids), 'faq_category', false);
        }

        return $faq_id;
    }
}

if (! function_exists('matrix_seed_su_faqs_section_row')) {
  /**
   * @return array<string, mixed>
   */
    function matrix_seed_su_faqs_section_row(string $heading, int $category_id): array
    {
        return [
            'acf_fc_layout' => 'faqs',
            'show_heading' => 1,
            'layout_style' => 'default',
            'heading' => $heading,
            'heading_tag' => matrix_page_seed_heading(2),
            'source_mode' => 'category',
            'selected_faq_categories' => [$category_id],
            'section_background' => '#FBFAF7',
            'item_background' => '#FFFFFF',
            'open_item_background' => 'linear-gradient(-42.77deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
            'question_color' => '#1E244B',
            'answer_color' => '#08284B',
            'padding_settings' => [
                ['screen_size' => 'mob', 'padding_top' => '2', 'padding_bottom' => '2'],
                ['screen_size' => 'lg', 'padding_top' => '4', 'padding_bottom' => '4'],
            ],
        ];
    }
}

$home = home_url('/');
$section_url = home_url('/service-users-and-visitors/');
$healthcare_faqs_url = home_url('/healthcare-professionals/frequently-asked-questions/');
$page_id = (int) (get_page_by_path('service-users-and-visitors/frequently-asked-questions-faqs')?->ID ?? 286);

$hero_image_id = (int) matrix_migrate_attachment_id_for_source_path('/media/3498/mental-health-services.png');

if ($hero_image_id <= 0) {
    $hero_image_id = (int) matrix_migrate_attachment_id_for_source_path('/media/3121/get-involved-banner.png');
}

$parent_term_id = matrix_seed_su_faq_ensure_term('service-users-and-visitors', 'Service Users and Visitors');
$referrals_term_id = matrix_seed_su_faq_ensure_term('service-users-referrals', 'Questions about referrals', $parent_term_id);
$treatments_term_id = matrix_seed_su_faq_ensure_term('service-users-treatments', 'Questions about treatments and services', $parent_term_id);
$mental_health_term_id = matrix_seed_su_faq_ensure_term('service-users-mental-health', 'Questions about your mental health', $parent_term_id);
$supporting_term_id = matrix_seed_su_faq_ensure_term('service-users-supporting', 'Questions about supporting other people', $parent_term_id);

$legacy_posts = get_posts([
    'post_type' => 'faqs',
    'post_status' => 'any',
    'posts_per_page' => -1,
    'meta_query' => [
        [
            'key' => '_matrix_seed_key',
            'value' => 'faq-service-users-',
            'compare' => 'LIKE',
        ],
    ],
]);

foreach ($legacy_posts as $legacy_post) {
    wp_delete_post((int) $legacy_post->ID, true);
}

$faq_sections = [
    'referrals' => [
        'term_id' => $referrals_term_id,
        'items' => [
            [
                'title' => 'How do I get referred for an adult inpatient stay?',
                'content' => '<p>To receive ' . matrix_seed_su_faq_link('inpatient-hospital-care', 'inpatient care') . ' here in SPMHS, in most cases, you will need to arrange an appointment with your GP. If, following assessment, your GP finds that you would benefit from one of our services, they can refer you to SPMHS.</p>'
                    . '<p>In some circumstances, if you are under the care of a psychiatrist outside of SPMHS, they may refer you to SPMHS. If you are already receiving care from a SPMHS psychiatrist, they may also refer you for an inpatient admission.</p>'
                    . '<p>When we receive your referral, a member of our Referral and Assessment Service team will contact you to discuss your referral. Your referral will then be assessed by a consultant psychiatrist in SPMHS to identify the most appropriate service for you, which may be an inpatient admission.</p>'
                    . '<p>Please note that the referral process outlined above applies to adults only; you can find more information on referrals for adolescents below.</p>',
            ],
            [
                'title' => 'How do I get referred for the adult Homecare service?',
                'content' => '<p>Through our ' . matrix_seed_su_faq_link('care-treatment/homecare-service', 'Homecare service') . ', we deliver high quality mental healthcare to service users in their own homes.</p>'
                    . '<p>To be admitted to the adult Homecare service, in general, you would first need to be assessed by your GP who can then refer you to SPMHS if they feel you need care and treatment for your mental health.</p>'
                    . '<p>A member of our Referral and Assessment Service team will contact you after we receive your referral to discuss this with you. A consultant psychiatrist here in SPMHS will then assess your referral to identify which service may be best for you, which may include the Homecare service.</p>'
                    . '<p>Please note that there may be situations where a physical admission or inpatient stay may be needed prior to Homecare. The information above applies to referrals for people aged 18 and over only.</p>',
            ],
            [
                'title' => 'How do I get referred for a Dean Clinic (outpatient) assessment?',
                'content' => '<p>The ' . matrix_seed_su_faq_link('outpatient-clinics/about-the-dean-clinics', 'Dean Clinics') . ' are our community or outpatient mental health clinics. The Dean Clinics offer mental health assessment, outpatient appointments, and follow-up care after inpatient or Homecare admission.</p>'
                    . '<p>To access our Dean Clinics, you generally first need to visit your GP, who can refer you to SPMHS if their assessment finds that you would benefit from mental healthcare.</p>'
                    . '<p>If you are in our inpatient or Homecare services, a member of your multidisciplinary team may also refer you to the Dean Clinics for follow-up care.</p>',
            ],
            [
                'title' => 'How can referrals for adolescent services be made?',
                'content' => '<p>We provide a range of mental health services for young people aged under 18. Our Willow Grove Adolescent Unit offers ' . matrix_seed_su_faq_link('adolescent-mental-health-services', 'inpatient and Homecare services to young people') . ' between the ages of 12 and 17.</p>'
                    . '<p>Through our ' . matrix_seed_su_faq_link('outpatient-clinics/adolescent-dean-clinic', 'Adolescent Dean Clinic') . ' and our ' . matrix_seed_su_faq_link('outpatient-clinics/dean-clinic-cork', 'Dean Clinic Cork') . ', we also offer assessment and therapy tailored to the young person\'s needs.</p>'
                    . '<p>Referrals to our adolescent services can be made by GPs, CAMHS teams, paediatric or general hospitals, or by psychiatrists outside of SPMHS.</p>',
            ],
            [
                'title' => 'How do I get a referral for day programmes?',
                'content' => '<p>We have a wide range of day programmes; you can ' . matrix_seed_su_faq_link('programmes-therapies', 'get information on our day programmes here') . '.</p>'
                    . '<p>Most of our day programmes can be accessed if you are already receiving inpatient or Homecare services in SPMHS. We also accept direct referrals from GPs and other mental health services into some of our day programmes.</p>'
                    . '<p>If you are not currently receiving care in SPMHS, you will need a referral from your GP. Please note that our day programmes are open to adults over 18 only.</p>',
            ],
            [
                'title' => 'Can I get referred to a clinical or counselling psychologist?',
                'content' => '<p>Here in SPMHS, our Psychology Department is an integral part of our multidisciplinary approach to care delivery. Access to services from our Psychology Department is facilitated through comprehensive multidisciplinary assessment. Therefore, we cannot accept referrals directly to the psychologists in our Psychology Department.</p>'
                    . '<p>We are accepting referrals directly from GPs for some group psychology programmes. If you are looking for an individual psychology service specifically, please discuss this with your GP.</p>',
            ],
        ],
    ],
    'treatments' => [
        'term_id' => $treatments_term_id,
        'items' => [
            [
                'title' => 'What are the costs of treatment at SPMHS?',
                'content' => '<p>We are an independent, not-for-profit mental health service. Our services are funded through health insurance or directly funded by the service user.</p>'
                    . '<p>If you have health insurance, we advise you to contact your insurance provider with your policy number to clarify your cover. Our service users can also self-fund care.</p>'
                    . '<p>As we are independent from public services, the public medical card does not cover the cost of treatment in SPMHS. You can ' . matrix_seed_su_faq_link('getting-help/insurance-information', 'see our funding information here') . '.</p>',
            ],
            [
                'title' => 'What is Cognitive Behavioural Therapy (CBT) and how do I access CBT in SPMHS?',
                'content' => '<p>CBT is an approach to psychology which is based on scientific principles. It focuses on the relationship between thoughts, feelings and behaviours. Research shows CBT to be effective for a wide range of mental health difficulties.</p>'
                    . '<p>To access our CBT services, you will need an appointment with your GP who can send a referral to our Referral and Assessment Service. CBT services are available to adults aged 18 or over only.</p>',
            ],
            [
                'title' => 'Does SPMHS provide an Attention Deficit Hyperactivity Disorder (ADHD) service?',
                'content' => '<p>We do not provide ADHD assessment or treatment for adults; however, we do provide assessment and treatment for ADHD for young people aged under 18.</p>'
                    . '<p>The ' . matrix_seed_su_faq_external_link('https://www.adhdireland.ie/', 'ADHD Ireland website') . ' may be useful for sourcing services and private clinicians who provide such assessments.</p>',
            ],
            [
                'title' => 'Does SPMHS provide a Gender Identity Disorders Assessment?',
                'content' => '<p>We do not provide assessment and/or treatment for gender identity disorder for adults or adolescents.</p>'
                    . '<p>You may find the ' . matrix_seed_su_faq_external_link('https://www.teni.ie/', 'Transgender Equality Network Ireland website') . ' a useful source of further information.</p>',
            ],
            [
                'title' => 'Does SPMHS provide an Autistic Spectrum Disorder (ASD) assessment or service?',
                'content' => '<p>We do not provide assessment and/or treatment for ASD for adults or adolescents.</p>'
                    . '<p>Please visit the ' . matrix_seed_su_faq_external_link('https://www.aspireireland.ie/', 'ASPIRE Ireland website') . ' to find services and private clinicians who provide ASD assessments.</p>',
            ],
            [
                'title' => 'Does SPMHS provide medico-legal reports as a standalone service?',
                'content' => '<p>We do not provide medico-legal reports as a standalone service.</p>',
            ],
            [
                'title' => 'Does SPMHS provide Disability Access Route to Education (DARE) reports?',
                'content' => '<p>We do not provide standalone DARE assessments or reports for adults or adolescents.</p>'
                    . '<p>In some circumstances, we provide DARE reports for people aged under 18 who have attended our mental health services. Please ' . matrix_seed_su_faq_external_link('https://accesscollege.ie/dare', 'visit the DARE website') . ' for more information.</p>',
            ],
        ],
    ],
    'mental-health' => [
        'term_id' => $mental_health_term_id,
        'items' => [
            [
                'title' => 'What should I do if I think I may have a mental health difficulty?',
                'content' => '<p>If you are worried about your mental health, please know that there are supports available and that recovery is possible.</p>'
                    . '<p>Start by talking to someone you trust, like a family member or friend. You should visit your GP and explain how you are feeling to them.</p>'
                    . '<p>If you are in a crisis and need urgent help, please contact your GP or your out-of-hours GP. If you cannot get in touch with them, please go to the Emergency Department of your nearest general hospital, or call the emergency services on 999 or 112 in the Republic of Ireland.</p>',
            ],
            [
                'title' => 'What should I do if I have questions about my medication?',
                'content' => '<p>If you or someone you know has any questions or concerns regarding mental health medication, the independent ' . matrix_seed_su_faq_external_link('https://www.choiceandmedication.org/spuh/', 'Choice and Medication website') . ' is a very helpful source of information.</p>'
                    . '<p>Additionally, you could contact the GP or psychiatrist who prescribed the medication, or speak with the pharmacist who dispensed the medication.</p>'
                    . '<p>It is important not to change or alter your medication without first seeking advice from your medical professional or treating team. You might also find it helpful to see our ' . matrix_seed_su_faq_link('care-treatment/medication', 'medication information') . '.</p>',
            ],
            [
                'title' => 'Are there any mental health helplines I can contact?',
                'content' => '<p>There are a number of mental health helplines in Ireland which you can contact at any time of day or night. These include helplines from the HSE, Samaritans, and Pieta.</p>'
                    . '<p>You can ' . matrix_seed_su_faq_link('getting-help/concerned-about-yourself-or-someone-you-know', 'get information and contact details for these helplines here') . '.</p>',
            ],
        ],
    ],
    'supporting' => [
        'term_id' => $supporting_term_id,
        'items' => [
            [
                'title' => 'What should I do if I think a family member or friend may have a mental health difficulty?',
                'content' => '<p>If you are concerned that someone you know may have a mental health difficulty, it is important to remember that there is help available.</p>'
                    . '<p>Encourage the person to talk to someone, to be informed and to get support. You should encourage the person to make an appointment to see their GP.</p>'
                    . '<p>If the person is in a crisis situation and can\'t get in touch with a GP, go with them to the Emergency Department of your nearest general hospital, or contact the emergency services by phoning 999 or 112.</p>',
            ],
            [
                'title' => 'What should I do if I think a family member or a friend may have an eating disorder?',
                'content' => '<p>If you are concerned that someone you know has an eating disorder, it is best to seek help through their GP, who may make a referral to a mental health team.</p>'
                    . '<p>You can ' . matrix_seed_su_faq_link('care-treatment/eating-disorders-programme', 'find out more here about our Eating Disorder Service') . '. You might also find it helpful to contact ' . matrix_seed_su_faq_external_link('https://www.bodywhys.ie/', 'Bodywhys') . '.</p>',
            ],
            [
                'title' => 'What support is available to relatives or friends of someone with a mental health difficulty?',
                'content' => '<p>The experience of supporting someone else through a mental health difficulty can be challenging. It is important that you acknowledge your own need for support.</p>'
                    . '<p>It may be possible to access support groups or family therapy through the team treating your loved one. You might find it helpful to ' . matrix_seed_su_faq_link('getting-help/learning-resource-hub', 'visit our Learning and Resource Hub') . '.</p>',
            ],
            [
                'title' => 'What should I do if a friend or family member is at risk and does not believe they need treatment?',
                'content' => '<p>If you are concerned about a loved one who has signs of a mental health difficulty, but does not recognise or acknowledge it, the person may be referred for an involuntary admission under the Mental Health Act.</p>'
                    . '<p>There are specific guidelines in place around involuntary admissions. The Mental Health Commission has produced guidance for family members and friends on the involuntary admission process.</p>',
            ],
            [
                'title' => 'My child who is under 18 appears to be struggling with their mental health; where can I get help?',
                'content' => '<p>If you think your child needs help, your first point of contact should be with your GP. The GP can discuss mental health treatment options with you and your child.</p>'
                    . '<p>Here in SPMHS, Willow Grove is our adolescent unit for young people aged 12 to 17. You can ' . matrix_seed_su_faq_link('adolescent-mental-health-services', 'learn more about Willow Grove here') . '.</p>'
                    . '<p>We also provide community-based care through our ' . matrix_seed_su_faq_link('outpatient-clinics/dean-clinic-cork', 'Dean Clinic Cork') . ' and ' . matrix_seed_su_faq_link('outpatient-clinics/adolescent-dean-clinic', 'Adolescent Dean Clinic') . ' in Dublin.</p>',
            ],
            [
                'title' => 'What should I say to someone who I’m concerned about?',
                'content' => '<p>Sometimes, it is hard to know what to say when speaking to a loved one about mental health. Remember that being a compassionate listener is much more important than giving advice.</p>'
                    . '<h3>Ways to start the conversation</h3><ul><li>I have been feeling concerned about you lately.</li><li>Recently, I have noticed some differences in you and wondered how you are doing.</li><li>I wanted to check in with you because you have seemed pretty down lately.</li></ul>'
                    . '<h3>Questions you can ask</h3><ul><li>When did you begin feeling like this?</li><li>Did something happen that made you start feeling this way?</li><li>How can I best support you right now?</li><li>Have you thought about getting help?</li></ul>'
                    . '<h3>What you can say that helps</h3><ul><li>You are not alone in this. I\'m here for you.</li><li>You may not believe it now, but the way you\'re feeling will change.</li><li>I may not be able to understand exactly how you feel, but I care about you and want to help.</li><li>Tell me what I can do now to help you.</li></ul>'
                    . '<h3>What to avoid saying</h3><ul><li>It\'s all in your head.</li><li>We all go through times like this.</li><li>Look on the bright side.</li><li>Just snap out of it.</li><li>What\'s wrong with you?</li></ul>',
            ],
        ],
    ],
];

$seeded_faq_count = 0;

foreach ($faq_sections as $section_key => $section) {
    foreach ($section['items'] as $index => $item) {
        $seed_key = 'su-faq-' . $section_key . '-' . ($index + 1);
        $faq_id = matrix_seed_su_faq_ensure_post(
            $item['title'],
            $item['content'],
            $seed_key,
            [(int) $section['term_id'], $parent_term_id],
            $index + 1
        );

        if ($faq_id > 0) {
            $seeded_faq_count++;
        }
    }
}

$hero_intro = 'Here in St Patrick\'s Mental Health Services (SPMHS), our team has gathered answers to some of the most common questions we are asked about our services and mental health supports. If you are looking for advice or have questions about our services or mental health, the information below aims to provide useful guidance and a helpful starting point.';

$page_rows = [
    [
        'acf_fc_layout' => 'hero_with_breadcrumbs',
        'layout_style' => 'image_split',
        'show_breadcrumbs' => 1,
        'breadcrumb_source' => 'manual',
        'manual_breadcrumbs' => [
            ['breadcrumb_link' => ['title' => 'Home', 'url' => $home, 'target' => '']],
            ['breadcrumb_link' => ['title' => 'Service Users and Visitors', 'url' => $section_url, 'target' => '']],
        ],
        'current_crumb_label' => 'FAQs',
        'heading_tag' => matrix_page_seed_heading(1),
        'heading' => 'Frequently asked questions about mental health supports',
        'content' => '<p>' . esc_html($hero_intro) . '</p>',
        'primary_button' => [
            'title' => 'See Healthcare Professionals FAQs',
            'url' => $healthcare_faqs_url,
            'target' => '',
        ],
        'hero_image' => $hero_image_id,
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
    ],
    matrix_seed_su_faqs_section_row('Questions about referrals', $referrals_term_id),
    matrix_seed_su_faqs_section_row('Questions about treatments and services', $treatments_term_id),
    matrix_seed_su_faqs_section_row('Questions about your mental health', $mental_health_term_id),
    matrix_seed_su_faqs_section_row('Questions about supporting other people', $supporting_term_id),
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Further questions',
        'heading_tag' => matrix_page_seed_heading(2),
        'accent_position' => 'below_heading',
        'content' => '<p>If you have an urgent query in relation to referrals, you can <a href="tel:012493635">call 01 249 3635</a> to reach our Referral and Assessment Service between 9am and 5pm, Monday to Friday. Outside of these hours, please <a href="tel:012493200">call 01 249 3200</a>.</p>'
            . '<p>If you have more questions that don\'t relate to a referral, you can <a href="tel:012493200">call us on 01 249 3200</a>.</p>',
        'column_layout' => 'one_column',
        'background_type' => 'cream',
        'text_width' => 'constrained',
    ],
];

if ($page_id > 0 && function_exists('update_field')) {
    update_field('hero_content_blocks', [], $page_id);
    update_field('flexible_content_blocks', $page_rows, $page_id);
}

WP_CLI::success(sprintf(
    'Seeded Service Users FAQ page (ID %d) with %d FAQ posts across 4 sections.',
    $page_id,
    $seeded_faq_count
));
WP_CLI::log('Page: ' . get_permalink($page_id));
