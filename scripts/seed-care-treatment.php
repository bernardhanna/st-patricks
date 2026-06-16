<?php

/**
 * Seed Care & Treatment CPT posts from stpatricks.ie care & treatment pages.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-care-treatment.php
 */

require_once __DIR__ . '/lib/page-seed-conventions.php';
require_once __DIR__ . '/lib/care-treatment-seed.php';

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

$base_media = 'https://www.stpatricks.ie';
$hero_default_id = matrix_seed_import_scraped_image(
    $base_media . '/media/3467/mental-health-services-and-information.png',
    'Mental health services and information',
    'care-treatment-hero-default'
);

$medication_term_id = matrix_seed_care_treatment_ensure_term('Medication', 'medication');
$our_services_term_id = matrix_seed_care_treatment_ensure_term('Our Services', 'our-services');

$choice_medication_url = 'https://www.choiceandmedication.org/';

if (! function_exists('matrix_seed_care_treatment_service_page_rows')) {
    /**
     * @param array<int, array{heading: string, content: string, background?: string}> $sections
     * @return array<int, array<string, mixed>>
     */
    function matrix_seed_care_treatment_service_page_rows(
        string $heading,
        string $crumb_label,
        string $intro,
        array $sections,
        int $hero_image_id
    ): array {
        $rows = [
            matrix_seed_care_treatment_hero_block([
                'section' => 'our-services',
                'heading' => $heading,
                'crumb_label' => $crumb_label,
                'intro' => '<p>' . esc_html($intro) . '</p>',
                'hero_image_id' => $hero_image_id,
            ]),
            matrix_seed_care_treatment_useful_links_block('our-services'),
        ];

        foreach ($sections as $section) {
            $rows[] = matrix_seed_care_treatment_content_block(
                $section['heading'],
                $section['content'],
                $section['background'] ?? 'white'
            );
        }

        return array_merge($rows, matrix_seed_care_treatment_footer_cta_rows());
    }
}

$pages = [
    'medication' => [
        'title' => 'Medication',
        'slug' => 'medication',
        'section_term_id' => $medication_term_id,
        'excerpt' => 'Information about medication in mental health care and treatment at St Patrick\'s Mental Health Services.',
        'flexi_rows' => array_merge(
            [
                matrix_seed_care_treatment_hero_block([
                    'section' => 'medication',
                    'heading' => 'Medication',
                    'crumb_label' => 'Medication',
                    'intro' => '<p>You may be recommended to take medication to manage your mental health symptoms and support your journey of recovery.</p>',
                    'hero_image_id' => $hero_default_id,
                ]),
                matrix_seed_care_treatment_useful_links_block('medication'),
                matrix_seed_care_treatment_content_block(
                    'Making informed choices about medication',
                    '<p>At St Patrick\'s Mental Health Services, we are committed to giving you the information you need to help you make informed choices about your care and treatment. Your multidisciplinary team (MDT) will share this information with you, including about your medication.</p>'
                    . '<p><strong>Always ask a nurse, doctor or pharmacist – all of whom are members of your MDT – if you have any questions or queries about your medication.</strong></p>'
                    . '<p>Your MDT will ensure that medication you are prescribed is the most suited and effective for you. Your medication may change over time as different circumstances can impact what is appropriate for you. For example, you may need to adapt your medication to allow for other treatments you have been prescribed; to avoid allergies or reactions; or to be suitable when planning or during pregnancy.</p>'
                    . '<p>If you would like to know more about the medication you are taking or to understand other medication options available to you, the independent <a href="' . esc_url($choice_medication_url) . '" target="_blank" rel="noopener noreferrer">Choice and Medication</a> website provides clear, updated information on all the medication we use in your care.</p>'
                ),
                matrix_seed_care_treatment_content_block(
                    'Pregnancy and Valproate: Prevent programme',
                    '<p>It is important to be aware that the medication valproate (brand name Epilim®) can have a harmful effect on an unborn baby. The Prevent programme aims to reduce these risks, and, if you are being prescribed medication containing valproate, your MDT will talk you through everything you need to know.</p>'
                        . '<p>If you are a woman of childbearing potential or if you are planning or going through pregnancy, you can find out more information about valproate from <a href="https://www.medicines.ie/" target="_blank" rel="noopener noreferrer">Medicines.ie</a>, the Health Products Regulatory Authority or <a href="' . esc_url($choice_medication_url) . '" target="_blank" rel="noopener noreferrer">Choice and Medication</a>.</p>',
                    'cream'
                ),
                matrix_seed_care_treatment_content_block(
                    'See our medication safety series',
                    '<p>Get insights from our Pharmacy Department on how to make decisions about your mental health medication, what to expect from your medication, and what you should be aware of around medication safety.</p>'
                    . '<ul><li>Making decisions about your mental health medication</li><li>What to expect from mental health medication</li><li>Managing the side effects of medication</li></ul>'
                ),
                matrix_seed_care_treatment_content_block(
                    'More information and supports',
                    '<p>Visit our Learning and Resource Hub for brochures on your mental health and treatment.</p>'
                    . '<ul><li>Medication &amp; Cravings</li><li>Coming off Benzodiazepine or &lsquo;Z&rsquo; Drugs</li></ul>'
                ),
                matrix_seed_care_treatment_content_block(
                    'Meet our pharmacy team',
                    '<p>Our Pharmacy team supports service users with information and guidance about mental health medication as part of your multidisciplinary care.</p>',
                    'cream'
                ),
            ],
            matrix_seed_care_treatment_footer_cta_rows()
        ),
    ],
    'homecare-service' => [
        'title' => 'Homecare service',
        'slug' => 'homecare-service',
        'section_term_id' => $our_services_term_id,
        'excerpt' => 'High-quality mental health care and treatment delivered to service users in their own home.',
        'flexi_rows' => matrix_seed_care_treatment_service_page_rows(
            'Homecare service',
            'Homecare service',
            'Our Homecare service provides high-quality mental health care and treatment to service users in their own home.',
            [
                [
                    'heading' => 'What is Homecare?',
                    'content' => '<p>At St Patrick\'s Mental Health Services (SPMHS), our adult Homecare service delivers the mental healthcare a person needs directly to them, wherever they are in Ireland. Service users in Homecare receive comprehensive mental health support at home by engaging with their care team through video, phone or other online channels.</p>'
                        . '<p>The service takes a multidisciplinary approach, which means service users have support from a multidisciplinary team (MDT). An MDT is made up of a range of mental health professionals with different areas of expertise, who work together and with the service user to ensure all aspects of their care and recovery are looked after.</p>',
                ],
                [
                    'heading' => 'What can you expect?',
                    'content' => '<p>Homecare is designed to support the person\'s mental health recovery, while also enabling them to remain in familiar surroundings, without having to travel to hospital or being apart from their family and friends.</p>'
                        . '<p>Through Homecare, service users have 24-hour support, work with a range of mental health professionals, receive daily contact from members of their MDT, develop and progress an individual care plan, and can use our online patient platform.</p>'
                        . '<p>While in Homecare, service users take part in mental health programmes and talk therapies suited to their recovery. Any medication needed to support recovery will be reviewed and prescribed through the service, and service users have access to our Pharmacy team to discuss this.</p>',
                ],
                [
                    'heading' => 'Who is the adult Homecare service for?',
                    'content' => '<p>The Homecare service for adults is available to all age groups over the age of 18, including young adults (aged 18 to 25) and older adults (aged over 65).</p>'
                        . '<p>Referrals are reviewed by a consultant psychiatrist and a team of experienced clinicians. The adult Homecare service is covered by the main health insurers.</p>'
                        . '<p>Please note that our Willow Grove Adolescent Unit provides a separate Homecare service for young people aged 12 to 17.</p>',
                    'background' => 'cream',
                ],
                [
                    'heading' => 'How are referrals made?',
                    'content' => '<p>Referrals to Homecare can be made by GPs or a healthcare provider. If you are a GP referring your patient to Homecare, please call <a href="tel:012493635">01 249 3635</a>.</p>'
                        . '<p>For more information on Homecare or any other of our mental health services, call us on <a href="tel:012493200">01 249 3200</a>.</p>',
                ],
                [
                    'heading' => 'A note on confidentiality',
                    'content' => '<p>Please note that, in order to ensure confidentiality and comply with data protection legislation, <strong>audio or visual recording of remote engagements by any means is not permitted</strong>. In all circumstances, recording can only occur with the full, expressed and prior agreement of everyone concerned.</p>',
                    'background' => 'cream',
                ],
            ],
            $hero_default_id
        ),
    ],
    'remote-services' => [
        'title' => 'Remote Services',
        'slug' => 'remote-services',
        'section_term_id' => $our_services_term_id,
        'excerpt' => 'Remote access to mental health services through phone, video or online channels from your own home.',
        'flexi_rows' => matrix_seed_care_treatment_service_page_rows(
            'Remote Services',
            'Remote Services',
            'We offer remote access to our services through phone, video or online channels to provide the highest quality mental healthcare to you from your own home.',
            [
                [
                    'heading' => 'What are our remote services?',
                    'content' => '<p>Our Homecare service offers all the elements of our inpatient programmes, but provided to you remotely in your own home. As well as daily contact, therapy sessions and meetings with our multidisciplinary teams, this service includes arrangements with local pharmacies to safely supply you with the medication you may need as part of your treatment.</p>'
                        . '<p>Meanwhile, our services and day programmes can be delivered through phone, video and online technologies. We provide care and treatment for a range of mental health difficulties, including anxiety disorders, bipolar disorder, depression, eating disorders, psychosis and more.</p>'
                        . '<p>Remote appointments are also being provided through our community Dean Clinics, conducted over video or phone. You will be fully supported in getting set up with the technological channels for your treatment.</p>',
                ],
                [
                    'heading' => 'How can you access remote services?',
                    'content' => '<p>You can access our services through a referral from your GP, psychiatrist, the Health Service Executive, and others.</p>'
                        . '<p>If you urgently need to speak to a clinician about a referral or need immediate mental health support, please call <a href="tel:012493635">01 249 3635</a>. If you have an urgent query in relation to referrals or treatment outside of these hours, please call <a href="tel:012493200">01 249 3200</a>.</p>'
                        . '<p>All of our remote services are supported by the main health insurers.</p>',
                    'background' => 'cream',
                ],
                [
                    'heading' => 'Confidentiality',
                    'content' => '<p>Please note that, in order to adhere to data protection legislation and to ensure confidentiality, <strong>audio or visual recording of remote engagements by any means is not permitted</strong>. In all circumstances, recording can only occur with the full, expressed and prior agreement of everyone concerned.</p>',
                ],
            ],
            $hero_default_id
        ),
    ],
    'addiction-and-dual-diagnosis' => [
        'title' => 'Addiction & Dual Diagnosis',
        'slug' => 'addiction-and-dual-diagnosis',
        'section_term_id' => $our_services_term_id,
        'excerpt' => 'Outpatient, day, inpatient and aftercare services for addictions and dual diagnosis.',
        'flexi_rows' => matrix_seed_care_treatment_service_page_rows(
            'Addiction & Dual Diagnosis',
            'Addiction and Dual Diagnosis',
            'Comprehensive services for addiction and dual diagnosis at St Patrick\'s Mental Health Services.',
            [
                [
                    'heading' => 'Addiction',
                    'content' => '<p>Addictive disorders are common disorders that involve the overuse of alcohol or drugs. Alcohol consumption has risen more in Ireland than in any other country in Europe and we are currently one of the highest consumers of alcohol per head of population in the world.</p>'
                        . '<p>At present, approximately 5% of the adult population is alcohol-dependent and a further 7% is alcohol abusive. There has also been a notable rise in binge drinking among young men and young women.</p>',
                ],
                [
                    'heading' => 'Dual diagnosis',
                    'content' => '<p>Dual diagnosis is a term that indicates the presence of two medical conditions. Within mental health and psychiatry, the term dual diagnosis is used to describe the co-existence of a mental health disorder and an alcohol or drug problem.</p>'
                        . '<p>There is evidence to support that if both the addiction and the underlying psychological problem are treated, the prognosis for recovery is very good.</p>',
                    'background' => 'cream',
                ],
                [
                    'heading' => 'Treatment approaches',
                    'content' => '<p>Addictive disorders are treatable. For some individuals it is enough to give information and feedback for them to tackle the addiction themselves. For others, a full treatment programme is required.</p>'
                        . '<p>Primary therapies and groups used include Alcoholics Anonymous, AWARE, National Drugs Team, Gamblers Anonymous, Lifering, Narcotics Anonymous, Samaritans, Shine and Women\'s Aid.</p>',
                ],
                [
                    'heading' => 'Locations',
                    'content' => '<p>At St Patrick\'s Mental Health Services we provide outpatient, day, inpatient and aftercare services for addictions and dual diagnosis. The Temple Centre is our holistic treatment centre where users are provided with the appropriate level of care depending on the severity of their addiction and stage of recovery.</p>'
                        . '<p>St Patrick\'s Mental Health Services and the Temple Centre have been accredited by the Mental Health Commission in Ireland, ensuring high standards in the delivery of mental health services.</p>',
                    'background' => 'cream',
                ],
            ],
            $hero_default_id
        ),
    ],
    'anxiety-disorders-programme' => [
        'title' => 'Anxiety Disorders Programme',
        'slug' => 'anxiety-disorders-programme',
        'section_term_id' => $our_services_term_id,
        'excerpt' => 'Comprehensive assessment, treatment and aftercare for primary anxiety disorders.',
        'flexi_rows' => matrix_seed_care_treatment_service_page_rows(
            'The Anxiety Disorders Programme',
            'Anxiety Disorders Programme',
            'Anxiety is the body and mind\'s natural reaction to threat or danger. When anxiety becomes excessive or debilitating, it is considered an Anxiety Disorder.',
            [
                [
                    'heading' => 'Subtypes of Anxiety Disorder',
                    'content' => '<p>There are six recognised groups of anxiety disorders: Panic Disorder, Agoraphobia, Social Anxiety, Generalised Anxiety Disorder (GAD), Post-Traumatic Stress Disorder (PTSD), and Obsessive Compulsive Disorder (OCD).</p>'
                        . '<p>In primary anxiety disorders, the symptoms tend to have followed a set pattern over several months or years and occur independently of other mental health problems.</p>',
                ],
                [
                    'heading' => 'Anxiety Disorders Programme',
                    'content' => '<p>The Anxiety Disorders Programme was established by St Patrick\'s Mental Health Services in April 2005 and caters for a wide range of anxiety disorders. It is delivered by a multidisciplinary team offering service users a combination of cognitive behavioural therapy (CBT), psychiatry, and occupational therapy.</p>'
                        . '<p>The programme focuses on addressing the physical, psychological and behavioural aspects of the anxiety disorder using group psychotherapy based on cognitive behavioural therapy models and mindfulness and self-compassion approaches.</p>'
                        . '<p><em>Level 1</em> is a five week programme with OCD and GASPP streams. <em>Level 2</em> is an eight week closed psychotherapy programme. <em>Level 3</em> is monthly aftercare. All levels are open to both inpatient and day patients.</p>',
                    'background' => 'cream',
                ],
                [
                    'heading' => 'Locations',
                    'content' => '<p>The Programme is facilitated in the Thomson Centre, St Patrick\'s University Hospital. Specialised anxiety disorder assessments and individual cognitive behavioural psychotherapy is provided at Dean St Patrick\'s Clinic.</p>',
                ],
            ],
            $hero_default_id
        ),
    ],
    'bipolar-education-programme' => [
        'title' => 'Bipolar Education Programme',
        'slug' => 'bipolar-education-programme',
        'section_term_id' => $our_services_term_id,
        'excerpt' => 'Support and education for people living with bipolar disorder through inpatient, day patient, outpatient and aftercare services.',
        'flexi_rows' => matrix_seed_care_treatment_service_page_rows(
            'Bipolar Education Programme',
            'Bipolar Education Programme',
            'The Bipolar Recovery Programme supports people living with bipolar disorder through inpatient, day patient, outpatient and aftercare services.',
            [
                [
                    'heading' => 'Overview of the programme',
                    'content' => '<p>Bipolar disorder is a type of mood disorder which can cause people to go through extreme mood shifts and changes in their thinking, feeling and energy levels.</p>'
                        . '<p>Our Bipolar Recovery Programme is an online group programme that offers support and education for people living with bipolar disorder or symptoms linked with it. The programme uses models and principles from Cognitive Behaviour Therapy (CBT), Compassion Focused Therapy (CFT) and Mindfulness Based Stress Reduction (MBSR).</p>',
                ],
                [
                    'heading' => 'Programme elements',
                    'content' => '<ul><li><strong>Bipolar Programme Workshop</strong> – a single-session workshop for inpatients or Homecare service users.</li>'
                        . '<li><strong>Bipolar Recovery Programme</strong> – a 10-week group programme for day patients, held every Wednesday.</li>'
                        . '<li><strong>Bipolar Aftercare Programme</strong> – monthly half-day group for graduates of the recovery programme.</li>'
                        . '<li><strong>Bipolar Support Seminar</strong> – two information sessions for nominated supporters each year.</li></ul>',
                    'background' => 'cream',
                ],
                [
                    'heading' => 'How to take part',
                    'content' => '<p>GPs can refer their patients to the Bipolar Recovery Programme. An MDT can also refer inpatient service users in SPMHS to take part in the programme. The programme team will carry out an assessment to ensure that the programme best suits the person\'s needs.</p>'
                        . '<p>The programme is delivered online through videocalls on Microsoft Teams. Supports are available through our Service User IT Support service.</p>',
                ],
            ],
            $hero_default_id
        ),
    ],
    'depression-recovery-programme' => [
        'title' => 'Depression Recovery Programme',
        'slug' => 'depression-recovery-programme',
        'section_term_id' => $our_services_term_id,
        'excerpt' => 'Assessment, treatment and aftercare for people living with depression.',
        'flexi_rows' => matrix_seed_care_treatment_service_page_rows(
            'Depression Recovery Programme',
            'Depression Recovery Programme',
            'The Depression Recovery Programme offers assessment, treatment and aftercare for people living with depression.',
            [
                [
                    'heading' => 'About the programme',
                    'content' => '<p>Depression is a mood disorder which brings feelings of sadness, helplessness and hopelessness over a period of time. A person who is living with depression can have difficulties with everyday life as a result.</p>'
                        . '<p>Our Depression Recovery Programme is an online group programme designed to support and educate people living with depression. The principles of Cognitive Behaviour Therapy (CBT), Compassion-Focused Therapy (CFT), and Mindfulness-Based Stress Reduction (MBSR) guide the programme and its content.</p>',
                ],
                [
                    'heading' => 'Treatment approaches',
                    'content' => '<ul><li><strong>Depression Recovery Programme</strong> – a 10-week psychotherapy group programme delivered online as a day patient programme.</li>'
                        . '<li><strong>Depression Recovery Aftercare</strong> – a 12-month psychotherapy group meeting for a half day once a month.</li>'
                        . '<li><strong>Depression Recovery Support Seminar</strong> – two information and support sessions for nominated supporters each year.</li></ul>',
                    'background' => 'cream',
                ],
            ],
            $hero_default_id
        ),
    ],
    'eating-disorders-programme' => [
        'title' => 'Eating Disorders Programme',
        'slug' => 'eating-disorders-programme',
        'section_term_id' => $our_services_term_id,
        'excerpt' => 'Specialist inpatient, day patient, outpatient, and aftercare services for adults with eating disorders.',
        'flexi_rows' => matrix_seed_care_treatment_service_page_rows(
            'Eating Disorders Service',
            'Eating Disorders Programme',
            'Our Eating Disorders Service provides inpatient, day patient, outpatient, and aftercare services for adults with a range of eating problems.',
            [
                [
                    'heading' => 'What is the Eating Disorder Service?',
                    'content' => '<p>Our Eating Disorders Service provides specialist inpatient, outpatient, and day care options for adults aged 18 years and over with anorexia nervosa, binge eating disorders, bulimia nervosa, and other specified feeding and eating disorders (OSFED).</p>'
                        . '<p>The service is delivered by a multidisciplinary team of experienced professionals who work together to treat and support people and their carers towards recovery from an eating disorder.</p>',
                ],
                [
                    'heading' => 'Inpatient care for eating disorders',
                    'content' => '<p>Inpatient care offers a full assessment and treatment plan tailored to the service user\'s needs. It may benefit someone who has anorexia nervosa and is underweight with medical complications, or who requires a supportive environment to help manage eating disorder behaviours.</p>'
                        . '<p>Treatments include consultant-led diagnostic evaluation, CBT-E, dietetic assessment and meal management, family support, medical monitoring, meal support therapy, occupational therapy, and psychological assessments and treatments.</p>',
                    'background' => 'cream',
                ],
                [
                    'heading' => 'Day and outpatient care',
                    'content' => '<p>Day care supports people over 18 who need more support than outpatient care but do not need a full hospital admission. Programmes include the Eating Disorders Treatment Information Programme, Compassion-Focused Therapy for Eating Disorders, Eating Disorders Day Care Programme, and Binge Eating Support Programme.</p>'
                        . '<p>Outpatient care provides a full assessment, treatment, and follow-up at the Dean Clinic St Patrick\'s, suitable for adults with anorexia nervosa, bulimia nervosa, binge eating disorders, or OSFED.</p>',
                ],
                [
                    'heading' => 'Referrals to the Eating Disorders Service',
                    'content' => '<p>To access the Eating Disorders Service at SPMHS, individuals need to be referred by a GP or other healthcare professional. Upon receipt of a referral, the service user will be assessed prior to admission at the outpatient service at the Dean Clinic in St Patrick\'s University Hospital.</p>'
                        . '<p>If you have any queries regarding accessing services, please call our Referral and Assessment Service on <a href="tel:012493635">01 249 3635</a> within office hours.</p>',
                ],
            ],
            $hero_default_id
        ),
    ],
    'psychosis-recovery-programme' => [
        'title' => 'Psychosis Recovery Programme',
        'slug' => 'psychosis-recovery-programme',
        'section_term_id' => $our_services_term_id,
        'excerpt' => 'Outpatient, day patient, inpatient and aftercare services for psychosis recovery.',
        'flexi_rows' => matrix_seed_care_treatment_service_page_rows(
            'Psychosis Recovery Programme',
            'Psychosis Recovery Programme',
            'The Psychosis Recovery Service provides outpatient, day patient, inpatient and aftercare services tailored to each person\'s severity and stage of recovery.',
            [
                [
                    'heading' => 'Outpatient service',
                    'content' => '<p>The outpatient service is located at our Community Dean Clinics and includes a consultant-led multidisciplinary assessment clinic, where a full psychiatric and medical assessment of the individual is provided.</p>'
                        . '<p>Our clinic for the Early Detection of Psychosis is located in the Sandyford Dean Clinic and is for young adults who may be at high risk or in the early stages of developing psychosis.</p>',
                ],
                [
                    'heading' => 'Day service',
                    'content' => '<p>The day service is based at St Patrick\'s University Hospital, Dublin 8. We run two day programmes specifically for individuals presenting with symptoms of psychosis:</p>'
                        . '<ul><li><strong>Psychosis Recovery Programme</strong> – a three-week recovery-orientated programme providing education around psychosis, recovery strategies and CBT skills.</li>'
                        . '<li><strong>Living Through Psychosis</strong> – a one-month programme focusing on bio-psychosocial explanations of psychosis, relapse prevention and return to work or college.</li></ul>',
                    'background' => 'cream',
                ],
                [
                    'heading' => 'Inpatient service and early detection',
                    'content' => '<p>The inpatient service at St Patrick\'s University Hospital is available to those who have become medically or psychiatrically compromised by their psychosis.</p>'
                        . '<p>Our early detection clinic helps young adults at high risk or in the early stages of developing psychosis, when they have the best chance of recovery and potential to prevent further deterioration.</p>',
                ],
            ],
            $hero_default_id
        ),
    ],
    'young-adult-service' => [
        'title' => 'Young Adult Service',
        'slug' => 'young-adult-service',
        'section_term_id' => $our_services_term_id,
        'excerpt' => 'Comprehensive mental health service for young people aged 18–25.',
        'flexi_rows' => matrix_seed_care_treatment_service_page_rows(
            'Young Adult Service',
            'Young Adult Service',
            'The Young Adult Service is a comprehensive mental health service especially designed to meet the needs of young people aged 18–25.',
            [
                [
                    'heading' => 'Treatment approaches',
                    'content' => '<p>Adolescence and early adulthood is a critical time for personal development. It is also the age (before 25) when 75% of mental health difficulties develop.</p>'
                        . '<p>The service addresses anxiety, depression, bipolar disorder, psychosis, schizophrenia, substance dependence, personality disorders, and eating disorders. Recovery can be a slow and frustrating process with setbacks, but with the right help most people will recover and return to a fulfilling life.</p>',
                ],
                [
                    'heading' => 'Primary therapies and groups used',
                    'content' => '<p>The programme works closely with the young person to promote confidence and competence in working with their mental health diagnosis. Primary therapies include the Young Adult Programme and medication support.</p>'
                        . '<p>Secondary therapies and groups include Cognitive Behavioural Therapy, Recovery (WRAP®) Programme, Access to Recovery Programme, Mindfulness, Compassion-Focused Therapy, Group Radical Openness, Pathways to Wellness, Dialectical Behavioural Therapy, Psychosis Recovery Programme, Anxiety Disorders Programme, Eating Disorder Programme, and Addiction and Dual Diagnosis.</p>',
                    'background' => 'cream',
                ],
                [
                    'heading' => 'Locations',
                    'content' => '<p>The Young Adult Service consists of an inpatient programme located at St Patrick\'s University Hospital and a community clinic located at the Dean Clinic Sandyford, Co Dublin.</p>',
                ],
            ],
            $hero_default_id
        ),
    ],
    'older-adult-service' => [
        'title' => 'Older Adult Service',
        'slug' => 'older-adult-service',
        'section_term_id' => $our_services_term_id,
        'excerpt' => 'Dedicated mental health service for adults aged 65 and older.',
        'flexi_rows' => matrix_seed_care_treatment_service_page_rows(
            'Older Adult Service',
            'Older Adult Service',
            'The Mental Health of the older adults service is a dedicated service for adults aged 65 and older available on an inpatient, day care service or Dean Clinic basis.',
            [
                [
                    'heading' => 'What to expect',
                    'content' => '<p>The older adult (Evergreen) team is multidisciplinary consisting of consultant psychiatrists, registrars, nursing staff, counsellors, social workers, psychologists and occupational therapists.</p>'
                        . '<p>The mental health of older adults programme specialises in the mental healthcare of the elderly with a focus on depression and dementia in later life. The overall aims of the service are to facilitate and promote wellness and functional independence.</p>',
                ],
                [
                    'heading' => 'Groups',
                    'content' => '<p>Groups focus on the barriers to good quality of life rather than on diagnoses. Common issues addressed include coping alone, isolation, physical decline, bereavement, and retirement and loss of role.</p>'
                        . '<p>Other topics include anxiety management, relaxation skills, links to outside occupational and leisure outlets, falls prevention, and exercise for the older person. Functional skills groups include cooking, mental aerobics, memory compensation, and memory rehabilitation skills.</p>',
                    'background' => 'cream',
                ],
                [
                    'heading' => 'Who facilitates the groups?',
                    'content' => '<p>Groups are facilitated by the co-ordinator (an occupational therapist), registrars in old age psychiatry, and recreational instructors. Guest speakers and outside agencies such as Age &amp; Opportunity contribute to the programme.</p>'
                        . '<p>The Mental Health for Older Adults programme continues to evolve to take account of the diverse needs of this expanding section of our population.</p>',
                ],
            ],
            $hero_default_id
        ),
    ],
];

$created = 0;
$updated = 0;

foreach ($pages as $seed_key => $page) {
    $existing = get_posts([
        'post_type' => 'care_treatment',
        'post_status' => 'any',
        'posts_per_page' => 1,
        'name' => $page['slug'],
        'meta_query' => [
            [
                'key' => '_matrix_seed_key',
                'value' => $seed_key,
            ],
        ],
    ]);

    $post_id = matrix_seed_ensure_care_treatment([
        'seed_key' => $seed_key,
        'slug' => $page['slug'],
        'title' => $page['title'],
        'excerpt' => $page['excerpt'],
        'section_term_id' => $page['section_term_id'],
        'featured_image_id' => $hero_default_id,
        'flexi_rows' => $page['flexi_rows'],
    ]);

    if ($post_id < 1) {
        WP_CLI::warning('Failed to seed care & treatment page: ' . $page['title']);
        continue;
    }

    if ($existing !== []) {
        $updated++;
        WP_CLI::log('Updated: ' . $page['title'] . ' (ID ' . $post_id . ') → ' . matrix_seed_care_treatment_url($page['slug']));
    } else {
        $created++;
        WP_CLI::log('Created: ' . $page['title'] . ' (ID ' . $post_id . ') → ' . matrix_seed_care_treatment_url($page['slug']));
    }
}

flush_rewrite_rules(false);

WP_CLI::success(sprintf(
    'Care & Treatment seed complete. Created %d, updated %d. Total pages: %d.',
    $created,
    $updated,
    count($pages)
));
