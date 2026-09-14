<?php

/**
 * Carers and Supporters layout from Orlaith's August draft.
 *
 * Built from carers-and-supporters.md (converted from the Word file).
 * Downloads use a content-block button. The family information series
 * sits in one Video Showcase with heading, intro, and captioned slides.
 * Stories Carousel and selected family FAQs follow the draft flexi notes.
 *
 * @param int $post_id page ID
 */
function matrix_orlaith_rebuild_carers_layout(int $post_id): void
{
    if ($post_id <= 0 || ! function_exists('update_field')) {
        return;
    }

    $permalink = static function (string $path): string {
        $path = trim($path, '/');
        $page = get_page_by_path($path, OBJECT, [
            'page',
            'mental_health',
            'care_treatment',
            'programmes_therapies',
            'get_involved',
            'locations',
        ]);

        if ($page instanceof WP_Post && $page->post_status === 'publish') {
            return (string) get_permalink($page);
        }

        $by_name = get_posts([
            'name' => basename($path),
            'post_type' => [
                'page',
                'mental_health',
                'care_treatment',
                'programmes_therapies',
                'get_involved',
                'locations',
            ],
            'post_status' => 'publish',
            'posts_per_page' => 1,
        ]);

        if ($by_name !== []) {
            return (string) get_permalink($by_name[0]);
        }

        return home_url('/' . $path . '/');
    };

    $link = static function (string $path, string $label) use ($permalink): string {
        return '<a href="' . esc_url($permalink($path)) . '">' . esc_html($label) . '</a>';
    };

    $find_image = static function (int $preferred_id, string $filename): int {
        if ($preferred_id > 0 && get_post_type($preferred_id) === 'attachment') {
            return $preferred_id;
        }

        $found = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'title' => preg_replace('/\.[^.]+$/', '', $filename),
        ]);

        return $found !== [] ? (int) $found[0] : 0;
    };

    $find_pdf = static function (int $preferred_id, string $filename): int {
        if ($preferred_id > 0 && get_post_type($preferred_id) === 'attachment') {
            return $preferred_id;
        }

        $found = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'post_mime_type' => 'application/pdf',
            'posts_per_page' => 1,
            'fields' => 'ids',
            's' => $filename,
        ]);

        return $found !== [] ? (int) $found[0] : 0;
    };

    $hero_image = $find_image(3930, 'Carers and supporters.png');
    $guide_pdf = $find_pdf(1022, 'carers-and-supporters-guide-2024');
    $guide_url = $guide_pdf > 0
        ? (string) wp_get_attachment_url($guide_pdf)
        : (function_exists('matrix_migrate_live_url')
            ? matrix_migrate_live_url('/media/3920/carers-and-supporters-guide-2024.pdf')
            : 'https://www.stpatricks.ie/media/3920/carers-and-supporters-guide-2024.pdf');

    $content = static function (string $heading, string $html, string $background, array $extra = []): array {
        $row = [
            'acf_fc_layout' => 'content',
            'heading' => $heading,
            'heading_tag' => 'h2',
            'accent_position' => 'below_heading',
            'intro_text' => '',
            'content' => $html,
            'background_type' => $background,
            'color_scheme' => 'default',
            'column_layout' => 'one_column',
            'text_width' => 'full',
            'image' => '',
        ];

        return array_merge($row, $extra);
    };

    $video_slide = static function (string $url, string $title, string $caption): array {
        return [
            'poster_image' => '',
            'video_source_type' => 'embed_url',
            'video_embed_url' => $url,
            'local_video_file' => '',
            'caption' => '<p><strong>' . esc_html($title) . '</strong></p><p>' . esc_html($caption) . '</p>',
            'cta_link' => '',
        ];
    };

    $normalize_title = static function (string $title): string {
        $title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $title = str_replace(["\u{2019}", "\u{2018}", '`'], "'", $title);

        return strtolower($title);
    };
    $faq_ids = [];
    $faq_titles = [
        'What support is available to relatives or friends of someone with a mental health difficulty?',
        'Can family members be involved in care planning?',
        'What should I do if I think a family member or friend may have a mental health difficulty?',
        'What should I do if a friend or family member is at risk and does not believe they need treatment?',
        'What should I do if I think a family member or a friend may have an eating disorder?',
        'My child who is under 18 appears to be struggling with their mental health; where can I get help?',
        "What should I say to someone who I'm concerned about?",
    ];
    $faq_lookup = [];
    foreach (get_posts([
        'post_type' => 'faqs',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
    ]) as $faq_id) {
        $faq_lookup[$normalize_title(get_the_title((int) $faq_id))] = (int) $faq_id;
    }
    foreach ($faq_titles as $title) {
        $key = $normalize_title($title);
        if (isset($faq_lookup[$key])) {
            $faq_ids[] = $faq_lookup[$key];
        }
    }

    $supporting_term = get_term_by('slug', 'service-users-supporting', 'faq_category');
    $supporting_term_id = $supporting_term instanceof WP_Term ? (int) $supporting_term->term_id : 0;

    $intro = '<p>As a family member, carer or friend of someone who is experiencing mental health difficulties, we understand that you may have many questions, queries and concerns.</p>';

    $role_html = '<p>'
        . $link('getting-help/concerned-about-yourself-or-someone-you-know', 'Seeking help for mental health')
        . ' is a brave step, and the encouragement and energy of other people can make a hugely positive impact on mental health recovery.</p>'
        . '<p>We encourage the people using our services to share information with you, as their families and friends, to help with fostering a greater sense of personal ownership of their recovery.</p>'
        . '<p>Information shared with you can help challenge negative health difficulties, as well as empowering you to recognise any symptoms suggesting a relapse in the person\'s mental health difficulties.</p>';

    $guide_html = '<p>Our Carers and Supporters Information Guide is developed for carers and family members of our service users. This guide highlights how best to support the person throughout their recovery, and gives some practical advice on how you can nurture your own wellbeing during this time.</p>'
        . '<p>While the guide will be particularly useful when a person has been '
        . $link('inpatient-care', 'admitted to hospital')
        . ', it should also be a help if they are receiving '
        . $link('what-we-offer/outpatient-care-dean-clinics', 'outpatient care in our Dean Clinics')
        . ' or '
        . $link('what-we-offer/day-programmes', 'attending a day programme')
        . '.</p>'
        . '<p>The guide was first developed by our '
        . $link('get-involved/service-user-and-supporters-council-suas', 'Service Users and Supporters Council')
        . ' (SUAS) in 2015, and was most recently updated in 2024. SUAS believes that having a guide for carers and family members that is written in Plain English will help you to better understand your loved one\'s journey.</p>';

    $video_intro = '<p>Our Social Work Department, together with our Service User Advisory Network (SUAN), developed an information series for families and carers of people living with a mental health difficulty. <em>Mental Health Recovery: A Family Perspective</em> is a series of talks which recognises the role you, as relatives and carers, play in your loved one\'s recovery, but also that you need support and care for yourself.</p>'
        . '<p>Just as every person\'s recovery pathway is unique, every family supporting someone is different. You may need different '
        . $link('mental-health', 'mental health information')
        . ' or services and practical supports, which depend on your specific circumstances; this series aims to respond to these needs. You can watch the series back below.</p>';

    $centre_html = '<p>As a family member, carer or friend of our service users, you are welcome to access our free drop-in '
        . $link('getting-help/information-centre', 'Information Centre')
        . ' (opposite the main reception in '
        . $link('locations/st-patricks-university-hospital', 'St Patrick\'s University Hospital')
        . ') for information and educational materials. The team in the Information Centre can provide information about mental health, including various diagnoses, symptoms, medications and support services.</p>';

    $rows = [
        [
            'acf_fc_layout' => 'hero_with_breadcrumbs',
            'layout_style' => $hero_image > 0 ? 'image_split' : 'title_accent',
            'show_breadcrumbs' => 1,
            'breadcrumb_source' => 'auto',
            'current_crumb_label' => 'Carers and supporters',
            'heading_tag' => 'h1',
            'heading' => 'Carers and supporters',
            'content' => $intro,
            'hero_image' => $hero_image > 0 ? $hero_image : '',
            'primary_button' => [
                'title' => '',
                'url' => '',
                'target' => '',
            ],
            'text_max_width' => 'default',
            'heading_max_width' => 'default',
            'background_color' => '#C6ECF4',
            'breadcrumb_background_color' => '#F1F8F9',
            'heading_color' => '#08284B',
            'text_color' => '#08284B',
        ],
        $content('The role of carers and supporters', $role_html, 'white'),
        $content('Carers and Supporters Information Guide', $guide_html, 'cream', [
            'primary_button' => [
                'title' => 'Download the information guide',
                'url' => $guide_url,
                'target' => '_blank',
            ],
            'primary_button_variant' => 'filled',
        ]),
        [
            'acf_fc_layout' => 'video_showcase',
            'heading_tag' => 'h2',
            'heading' => 'Family information series',
            'intro' => $video_intro,
            'layout_style' => 'feature_slider',
            'video_surface_size' => 'default',
            'text_max_width' => 'full',
            'slides' => [
                $video_slide(
                    'https://youtu.be/JKX7A0FE1LI',
                    'Information and advocacy for family recovery',
                    'Elaine Donnelly explores how involved families should be in a person\'s recovery, why carers need to look after themselves, and how the whole family system benefits.'
                ),
                $video_slide(
                    'https://youtu.be/DB56NswPSjM',
                    'Understanding trauma from a family perspective',
                    'Dr Clodagh Dowling explores how to support a loved one working through trauma, including the behaviour, pattern, and meaning of trauma.'
                ),
                $video_slide(
                    'https://youtu.be/QE5dezmkTAs',
                    'Addiction and the family',
                    'Linda Curran discusses how family members can recognise signs of addiction, communicate helpfully, and find supports.'
                ),
                $video_slide(
                    'https://youtu.be/gSzwtyXI2Ms',
                    'An overview of eating distress',
                    'Members of the Eating Disorders Programme team outline eating distress and how it can affect you in the caring role.'
                ),
                $video_slide(
                    'https://youtu.be/DTq80NIFp9E',
                    'Adolescent mental health',
                    'Noelle Meehan helps parents support their adolescent, their other children, and themselves, with strategies for helpful responses.'
                ),
                $video_slide(
                    'https://youtu.be/PCHCdNw6Okw',
                    'Understanding bipolar affective disorder',
                    'Sean Lonergan explains living with bipolar disorder and how friends, family, and carers support education, treatment, and recovery.'
                ),
                $video_slide(
                    'https://youtu.be/0Sgh5nkvD7M',
                    'Supporting a loved one with anxiety',
                    'Frank Smith looks at typical signs and symptoms of anxiety, how it can be treated, and how to support positive change.'
                ),
                $video_slide(
                    'https://youtu.be/4aP1c9GJKkQ',
                    'Minding your mental health in the caregiving role',
                    'Elaine Donnelly and Niamh Fox from Social Work talk through ways to look after your own wellbeing as a carer.'
                ),
                $video_slide(
                    'https://youtu.be/EKGUYXgLJBg',
                    'Understanding costly overcontrol',
                    'Rachel Egan and Georgina Heffernan explain harmful overcontrol, the signs, and how families can support loved ones.'
                ),
                $video_slide(
                    'https://youtu.be/sXbG11SwBbU',
                    'Navigating the journey to a diagnosis of dementia',
                    'Dr Sarah O\'Dwyer talks through memory and thinking assessments, from a first GP visit through to a diagnosis of cognitive impairment or dementia.'
                ),
                $video_slide(
                    'https://youtu.be/L2gh1iSzKbw',
                    'Supporting young adults in recovery',
                    'Elaine Murphy and Laura Pearson discuss supporting a young adult aged 18 to 25, from referral and admission through to discharge and aftercare.'
                ),
                $video_slide(
                    'https://youtu.be/BEEt6th8RbA',
                    'Medication in mental health recovery',
                    'Ciara Ní Dhubhlaing gives an overview of commonly used mental health medications, side effects, and planning a safe change or stop.'
                ),
            ],
        ],
        $content('Information Centre', $centre_html, 'white'),
        [
            'acf_fc_layout' => 'stories',
            'posts_per_slide' => 4,
            'max_posts' => 12,
            'show_date' => 1,
            'show_excerpt' => 0,
            'card_background_color' => '#fafaf9',
            'divider_color' => '#F9F1D1',
            'text_color' => '#08284B',
            'date_color' => '#08284B',
        ],
        [
            'acf_fc_layout' => 'faqs',
            'heading' => 'FAQs',
            'heading_tag' => 'h2',
            'show_heading' => 1,
            'layout_style' => 'default',
            'source_mode' => $faq_ids !== [] ? 'selected' : 'category',
            'selected_faqs' => $faq_ids,
            'selected_faq_categories' => $faq_ids === [] && $supporting_term_id > 0
                ? [$supporting_term_id]
                : [],
            'empty_state_message' => 'No FAQs are available right now.',
            'section_background' => '#FBFAF7',
            'heading_color' => '#1E244B',
            'underline_color' => '#6FC9C0',
            'item_background' => '#FFFFFF',
            'open_item_background' => 'linear-gradient(-42.77deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
            'question_color' => '#1E244B',
            'answer_color' => '#08284B',
        ],
    ];

    update_field('hero_content_blocks', [], $post_id);
    update_field('flexible_content_blocks', $rows, $post_id);
    update_post_meta($post_id, '_matrix_orlaith_carers_layout', gmdate('c'));
    if (function_exists('matrix_orlaith_set_seo')) {
        matrix_orlaith_set_seo(
            $post_id,
            'Carers and Supporters | St Patrick\'s Mental Health Services',
            'Information and resources for family members, carers and friends supporting someone receiving care at St Patrick\'s Mental Health Services.'
        );
    }

    if ($hero_image > 0) {
        set_post_thumbnail($post_id, $hero_image);
    }

    if (class_exists('Matrix_Flexible_Pages')) {
        Matrix_Flexible_Pages::set_flexible_page($post_id, true);
    }
}
