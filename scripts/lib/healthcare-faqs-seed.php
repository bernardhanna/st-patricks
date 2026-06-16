<?php

if (! function_exists('matrix_seed_hp_faq_url')) {
    function matrix_seed_hp_faq_url(string $path): string
    {
        return home_url('/' . trim($path, '/') . '/');
    }
}

if (! function_exists('matrix_seed_hp_faq_link')) {
    function matrix_seed_hp_faq_link(string $path, string $label): string
    {
        return '<a href="' . esc_url(matrix_seed_hp_faq_url($path)) . '">' . esc_html($label) . '</a>';
    }
}

if (! function_exists('matrix_seed_hp_faq_external_link')) {
    function matrix_seed_hp_faq_external_link(string $url, string $label): string
    {
        return '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . esc_html($label) . '</a>';
    }
}

if (! function_exists('matrix_seed_hp_faq_ensure_term')) {
    function matrix_seed_hp_faq_ensure_term(string $slug, string $name, int $parent_id = 0): int
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

if (! function_exists('matrix_seed_hp_faq_ensure_post')) {
    function matrix_seed_hp_faq_ensure_post(string $title, string $content, string $seed_key, array $term_ids, int $menu_order = 0): int
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

if (! function_exists('matrix_seed_hp_faq_sections')) {
    /**
     * @return array<string, array{heading: string, items: array<int, array{title: string, content: string}>}>
     */
    function matrix_seed_hp_faq_sections(): array
    {
        $referrals_url = matrix_seed_hp_faq_url('make-a-referral');
        $insurance_url = matrix_seed_hp_faq_url('getting-help/insurance-information');
        $ereferral_url = matrix_seed_hp_faq_url('healthcare-professionals/referrals-admissions/ereferral-guides-for-socrates-healthone-and-hpm');
        $webinars_url = matrix_seed_hp_faq_url('healthcare-professionals/webinars-events');

        return [
            'referrals' => [
                'heading' => 'Referrals and admissions',
                'items' => [
                    [
                        'title' => 'How do I make a referral to SPMHS?',
                        'content' => '<p>We accept referrals for inpatient, Homecare, outpatient and day programme services. Referrals can be sent electronically through Healthlink or your GP practice management system, or by completing our referral forms and emailing them to <a href="mailto:referrals@stpatricks.ie">referrals@stpatricks.ie</a> via Healthmail.</p>'
                            . '<p>For queries about referrals, please call our Referral and Assessment Service on <a href="tel:012493635">01 249 3635</a> during office hours. Outside of these hours, please call <a href="tel:012493200">01 249 3200</a>.</p>'
                            . '<p>You can also ' . matrix_seed_hp_faq_link('make-a-referral', 'see our referral pathways here') . '.</p>',
                    ],
                    [
                        'title' => 'Can I send referrals electronically?',
                        'content' => '<p>Yes. eReferrals can be sent through ' . matrix_seed_hp_faq_external_link('https://www.healthlink.ie/', 'Healthlink') . ' or your GP practice management system, such as Socrates or HealthOne.</p>'
                            . '<p>Select “St Patrick’s Mental Health Services” from the private hospital list and choose “Psychiatric Referral Service” from the list of departments.</p>'
                            . '<p>' . matrix_seed_hp_faq_link('healthcare-professionals/referrals-admissions/ereferral-guides-for-socrates-healthone-and-hpm', 'See our step-by-step eReferral guides') . ' for Socrates, HealthOne and HPM.</p>',
                    ],
                    [
                        'title' => 'How do I submit a referral form?',
                        'content' => '<p>Completed referral forms can be sent to our Referral and Assessment Service by ' . matrix_seed_hp_faq_external_link('https://www.healthmail.ie/', 'Healthmail') . ' to <a href="mailto:referrals@stpatricks.ie">referrals@stpatricks.ie</a>.</p>'
                            . '<p>Please ensure the form is completed in full before submitting it. Our Referral and Assessment Service will either contact your patient directly or get in touch with you to discuss the referral in advance.</p>'
                            . '<p><strong>Please note that, as of 6 December 2024, we are no longer accepting referrals by fax.</strong></p>',
                    ],
                    [
                        'title' => 'How do I refer an adolescent patient?',
                        'content' => '<p>Referrals to our adolescent services can be made by GPs, CAMHS teams, paediatric or general hospitals, or psychiatrists outside of SPMHS.</p>'
                            . '<p>Our Willow Grove Adolescent Unit provides inpatient and Homecare services for young people aged 12 to 17. Community-based care is also available through our ' . matrix_seed_hp_faq_link('outpatient-clinics/adolescent-dean-clinic', 'Adolescent Dean Clinic') . ' and ' . matrix_seed_hp_faq_link('outpatient-clinics/dean-clinic-cork', 'Dean Clinic Cork') . '.</p>',
                    ],
                    [
                        'title' => 'Are there waiting lists for SPMHS services?',
                        'content' => '<p>We have waiting lists in place for some services. Our team works hard to ensure these waiting lists move as quickly as possible, but please note that there may be some time between receiving your referral and your patient beginning treatment.</p>'
                            . '<p>All referrals are carefully considered. Based on the referred person\'s needs and level of urgency, a decision is made on whether we have an appropriate service to offer and which service would most appropriately meet their needs.</p>',
                    ],
                    [
                        'title' => 'How do I contact the Referral and Assessment Service?',
                        'content' => '<p>For queries about referrals, please call our Referral and Assessment Service on <a href="tel:012493635">01 249 3635</a> during office hours (9am to 5pm, Monday to Friday).</p>'
                            . '<p>Outside of these hours, please call <a href="tel:012493200">01 249 3200</a>.</p>'
                            . '<p>You can also email completed referral forms to <a href="mailto:referrals@stpatricks.ie">referrals@stpatricks.ie</a> via Healthmail.</p>',
                    ],
                ],
            ],
            'services' => [
                'heading' => 'Services and assessments',
                'items' => [
                    [
                        'title' => 'What services can I refer patients to?',
                        'content' => '<p>We treat a wide range of mental health difficulties, including anxiety disorders, addiction and dual diagnosis, bipolar disorder, depression, eating disorders, psychosis recovery, and the mental health of young adults and older adults.</p>'
                            . '<p>You can refer to our ' . matrix_seed_hp_faq_link('inpatient-hospital-care', 'inpatient') . ', ' . matrix_seed_hp_faq_link('care-treatment/homecare-service', 'Homecare') . ', ' . matrix_seed_hp_faq_link('outpatient-clinics/about-the-dean-clinics', 'outpatient') . ' and ' . matrix_seed_hp_faq_link('programmes-therapies', 'day programme') . ' services.</p>',
                    ],
                    [
                        'title' => 'Which day programmes accept direct referrals from GPs?',
                        'content' => '<p>At SPMHS, we run a number of day programmes to support people in their mental health recovery. Most programmes can be accessed if a person is already receiving inpatient or Homecare services in SPMHS.</p>'
                            . '<p>We also accept direct referrals from GPs and other mental health services into some day programmes. You can ' . matrix_seed_hp_faq_link('programmes-therapies', 'see our programmes and therapies') . ' for more information.</p>',
                    ],
                    [
                        'title' => 'What is a Prompt Assessment of Needs (PAON) for Dean Clinic referrals?',
                        'content' => '<p>People referred to our Dean Clinic network receive a free-of-charge assessment called a Prompt Assessment of Needs (PAON) with an experienced mental health nurse.</p>'
                            . '<p>The PAON takes place from the person\'s own home using their preferred means of communication, including phone, video and online services. This allows for fast, efficient identification of needs and referral to the most appropriate programme or service.</p>'
                            . '<p>Please note that the PAON is used for Dean Clinic referrals only; referrals for inpatient and Homecare admission are assessed separately.</p>',
                    ],
                    [
                        'title' => 'What mental health difficulties does SPMHS treat?',
                        'content' => '<p>We provide comprehensive mental healthcare for adolescents and adults through inpatient, Homecare, outpatient and day programmes. Our expert teams support people experiencing anxiety disorders, addiction and dual diagnosis, bipolar disorder, depression, eating disorders, mood disorders, obsessive-compulsive disorder, psychosis recovery, and the mental health of young adults and older adults.</p>'
                            . '<p>You can ' . matrix_seed_hp_faq_link('mental-health', 'see our mental health information') . ' for more detail on conditions and supports.</p>',
                    ],
                ],
            ],
            'insurance' => [
                'heading' => 'Insurance and funding',
                'items' => [
                    [
                        'title' => 'Are SPMHS services covered by health insurance?',
                        'content' => '<p>Our services are funded through health insurance or directly by the service user. We advise you to ask patients to contact their insurance provider with their policy number to clarify their cover before admission.</p>'
                            . '<p>As we are independent from public services, the public medical card does not cover the cost of treatment in SPMHS. You can find more information on our ' . matrix_seed_hp_faq_link('getting-help/insurance-information', 'insurance and funding information page') . '.</p>',
                    ],
                    [
                        'title' => 'Can patients self-fund treatment at SPMHS?',
                        'content' => '<p>Yes. We are an independent, not-for-profit mental health service. In addition to health insurance, service users can self-fund care where appropriate.</p>'
                            . '<p>We recommend that patients contact their insurance provider to clarify cover, and ' . matrix_seed_hp_faq_link('getting-help/insurance-information', 'read our funding information') . ' for guidance you can share with them.</p>',
                    ],
                ],
            ],
            'clinical' => [
                'heading' => 'Clinical information and professional development',
                'items' => [
                    [
                        'title' => 'How can I send clinical information securely to SPMHS?',
                        'content' => '<p>We are securely linked to Healthmail, the HSE email service which enables healthcare providers to send and receive patients\' clinical information over a protected, secure connection.</p>'
                            . '<p>If you have a Healthmail email address, you can use it to safely send clinical information, such as prescriptions, to our staff. If you do not already have an account, you can ' . matrix_seed_hp_faq_external_link('https://www.healthmail.ie/registration.cfm', 'sign up for a free Healthmail address') . '.</p>',
                    ],
                    [
                        'title' => 'Where can I find CPD and webinar opportunities for GPs?',
                        'content' => '<p>We offer mental health education supports for GPs, including accredited e-learning and a GP Webinar Series presented by our clinical teams.</p>'
                            . '<p>You can ' . matrix_seed_hp_faq_link('healthcare-professionals/webinars-events', 'see our webinars and events for healthcare professionals') . ' and sign up to our GP eNewsletter for updates on mental health, service developments and training.</p>',
                    ],
                ],
            ],
        ];
    }
}

if (! function_exists('matrix_seed_hp_faqs_section_row')) {
    /**
     * @return array<string, mixed>
     */
    function matrix_seed_hp_faqs_section_row(string $heading, int $category_id): array
    {
        return [
            'acf_fc_layout' => 'faqs',
            'show_heading' => 1,
            'layout_style' => 'default',
            'heading' => $heading,
            'heading_tag' => function_exists('matrix_page_seed_heading') ? matrix_page_seed_heading(2) : 'h2',
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

if (! function_exists('matrix_seed_hp_faq_landing_ids')) {
    /**
     * @return array<int, int>
     */
    function matrix_seed_hp_faq_landing_ids(): array
    {
        $keys = [
            'hp-faq-referrals-1',
            'hp-faq-referrals-2',
            'hp-faq-services-1',
            'hp-faq-referrals-4',
            'hp-faq-insurance-1',
            'hp-faq-referrals-6',
        ];

        $ids = [];

        foreach ($keys as $seed_key) {
            $posts = get_posts([
                'post_type' => 'faqs',
                'post_status' => 'publish',
                'posts_per_page' => 1,
                'meta_query' => [
                    [
                        'key' => '_matrix_seed_key',
                        'value' => $seed_key,
                    ],
                ],
            ]);

            if ($posts !== []) {
                $ids[] = (int) $posts[0]->ID;
            }
        }

        return $ids;
    }
}
