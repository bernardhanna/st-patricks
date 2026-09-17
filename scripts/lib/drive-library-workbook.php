<?php

/**
 * Drive library helpers for the sitemap content workbook.
 *
 * Maps WP pages/CPTs to folders in the shared SPMHS Content Gathering Library:
 * https://drive.google.com/drive/folders/19x_kP3NV29kzesNI81ob9XFB9dTUctRk
 */

if (! defined('MATRIX_WORKBOOK_DRIVE_LIBRARY_ROOT_ID')) {
    define('MATRIX_WORKBOOK_DRIVE_LIBRARY_ROOT_ID', '19x_kP3NV29kzesNI81ob9XFB9dTUctRk');
}

if (! defined('MATRIX_WORKBOOK_CONTENT_ON_DRIVE_META_KEY')) {
    define('MATRIX_WORKBOOK_CONTENT_ON_DRIVE_META_KEY', 'matrix_content_on_drive');
}

if (! function_exists('matrix_workbook_drive_folder_ids_path')) {
    function matrix_workbook_drive_folder_ids_path(): string
    {
        $theme_dir = function_exists('get_template_directory')
            ? get_template_directory()
            : dirname(__DIR__);

        return $theme_dir . '/old/content/drive-library-folder-ids.json';
    }
}

if (! function_exists('matrix_workbook_drive_folder_ids')) {
    /**
     * @return array{root_id: string, root_url: string, folders: array<string, string>}
     */
    function matrix_workbook_drive_folder_ids(): array
    {
        static $cached = null;

        if (is_array($cached)) {
            return $cached;
        }

        $path = matrix_workbook_drive_folder_ids_path();
        $fallback = [
            'root_id' => MATRIX_WORKBOOK_DRIVE_LIBRARY_ROOT_ID,
            'root_url' => 'https://drive.google.com/drive/folders/' . MATRIX_WORKBOOK_DRIVE_LIBRARY_ROOT_ID,
            'folders' => ['' => MATRIX_WORKBOOK_DRIVE_LIBRARY_ROOT_ID],
        ];

        if (! is_readable($path)) {
            $cached = $fallback;

            return $cached;
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        if (! is_array($decoded) || ! isset($decoded['folders']) || ! is_array($decoded['folders'])) {
            $cached = $fallback;

            return $cached;
        }

        $cached = [
            'root_id' => (string) ($decoded['root_id'] ?? MATRIX_WORKBOOK_DRIVE_LIBRARY_ROOT_ID),
            'root_url' => (string) ($decoded['root_url'] ?? ('https://drive.google.com/drive/folders/' . MATRIX_WORKBOOK_DRIVE_LIBRARY_ROOT_ID)),
            'folders' => array_map('strval', $decoded['folders']),
        ];

        return $cached;
    }
}

if (! function_exists('matrix_workbook_drive_page_folder_map')) {
    /**
     * WP page path or "cpt:post_type:slug" → Drive relative folder path.
     *
     * @return array<string, string>
     */
    function matrix_workbook_drive_page_folder_map(): array
    {
        $pages = [
            'home' => '01-Set-pages/Homepage',
            'thank-you-page' => '02-Page-content/About Us/Thank you page',
            'about-us/support-us' => '02-Page-content/About Us/Support Us',
            'about-us/our-team/psychiatrists' => '02-Page-content/About Us/Psychiatrists',
            'about-us/psychiatrists' => '02-Page-content/About Us/Psychiatrists',
            'about-us/our-team/social-workers' => '02-Page-content/About Us/Social workers',
            'about-us/social-workers' => '02-Page-content/About Us/Social workers',
            'about-us/our-team/nurses' => '02-Page-content/About Us/Nurses',
            'about-us/nurses' => '02-Page-content/About Us/Nurses',
            'about-us/our-team/occupational-therapists' => '02-Page-content/About Us/Occupational therapists',
            'about-us/occupational-therapists' => '02-Page-content/About Us/Occupational therapists',
            'about-us/our-team/psychologists' => '02-Page-content/About Us/Clinical psychologists',
            'about-us/psychologists' => '02-Page-content/About Us/Clinical psychologists',
            'about-us/careers/recruitment-and-useful-information' => '02-Page-content/About Us/Recruitment and useful information',
            'recruitment-and-useful-information' => '02-Page-content/About Us/Recruitment and useful information',
            'about-us/careers/recruitment-and-useful-information/staff-wellbeing' => '02-Page-content/About Us/Staff wellbeing',
            'recruitment-and-useful-information/staff-wellbeing' => '02-Page-content/About Us/Staff wellbeing',
            'about-us/careers/recruitment-and-useful-information/how-to-apply-for-a-role' => '02-Page-content/About Us/Apply for a role',
            'recruitment-and-useful-information/how-to-apply-for-a-role' => '02-Page-content/About Us/Apply for a role',
            'about-us/our-locations' => '02-Page-content/About Us/Our locations',
            'about-us/our-present-and-future/academic-institute' => '02-Page-content/About Us/Academic Institute',
            'academic-institute' => '02-Page-content/About Us/Academic Institute',
            'healthcare-professionals/training-centre' => '02-Page-content/About Us/Training Centre',
            'about-us/our-present-and-future/extending-and-enhancing-our-services' => '02-Page-content/About Us/Extending our services',
            'about-us/our-present-and-future/national-centre' => '02-Page-content/About Us/National centre',
            'national-centre' => '02-Page-content/About Us/National centre',
            'about-us/our-present-and-future/new-hospital' => '02-Page-content/About Us/New hospital',
            'new-hospital' => '02-Page-content/About Us/New hospital',
            'about-us/advocacy' => '02-Page-content/About Us/Advocacy',
            'about-us/our-present-and-future/advocacy-centre' => '02-Page-content/About Us/Advocacy centre',
            'advocacy-centre' => '02-Page-content/About Us/Advocacy centre',
            'about-us/our-present-and-future' => '02-Page-content/About Us/Our present and future',
            'about-us/our-present-and-future/partnering-with-service-users' => '02-Page-content/About Us/Partnering with service users',
            'about-us/partnering-with-service-users' => '02-Page-content/About Us/Partnering with service users',
            'about-us/policies-and-publications' => '02-Page-content/About Us/Policies and publications',
            'about-us/careers' => '02-Page-content/About Us/Careers',
            'about-us/media-queries' => '02-Page-content/About Us/Media queries',
            'inpatient-care' => '02-Page-content/What We Offer/Inpatient care',
            'what-we-offer/st-patricks-at-home' => '02-Page-content/What We Offer/St Patrick_s at Home',
            'what-we-offer/day-programmes' => '02-Page-content/What We Offer/Day programmes',
            'what-we-offer/outpatient-care-dean-clinics' => '02-Page-content/What We Offer/Outpatient care',
            'healthcare-professionals' => '02-Page-content/Healthcare Professionals/Make a referral',
            'make-a-referral' => '02-Page-content/Healthcare Professionals/Make a referral',
            'healthcare-professionals/contact-numbers' => '02-Page-content/Healthcare Professionals/Contact numbers',
            'your-portal/about-your-portal' => '02-Page-content/Service Users/About Your Portal',
            'about-your-portal' => '02-Page-content/Service Users/About Your Portal',
            'service-users-and-visitors/about-mental-health' => '02-Page-content/Service Users/About mental health',
            'service-users-and-visitors/attending-a-dean-clinic' => '02-Page-content/Service Users/Attending a Dean Clinic',
            'service-users-and-visitors/attending-day-programmes' => '02-Page-content/Service Users/Attending day programmes',
            'service-users-and-visitors/attending-our-day-programmes' => '02-Page-content/Service Users/Attending day programmes',
            'service-users-and-visitors/frequently-asked-questions-faqs' => '02-Page-content/Service Users/Frequently Asked Questions',
            'service-users-and-visitors/older-adult-mental-health' => '02-Page-content/Service Users/Older adult mental health',
            'service-users-and-visitors/young-adult-mental-health' => '02-Page-content/Service Users/Young adult mental health',
            'service-users-and-visitors/stories-and-support' => '02-Page-content/Service Users/Stories and support',
            'service-users-and-visitors/service-user-participation' => '02-Page-content/Service Users/Service User Participation',
            'service-users-and-visitors/about-our-st-patricks-at-home-service' => "01-Set-pages/About our St Patrick's at Home Service",
            'service-users-and-visitors/carers-and-supporters' => '02-Page-content/Service Users/Carers and Supporters',
            'service-users-and-visitors/service-user-participation/family-carers-and-supporters-advisory-network' => '02-Page-content/Service Users/Family, Carers and Supporters Advisory Network',
            'service-users-and-visitors/your-care-with-willow-grove/information-for-your-family' => '02-Page-content/Service Users/Information for your family',
            'service-users-and-visitors/schizophrenia' => '02-Page-content/Service Users/Schizophrenia',
            'service-users-and-visitors/schizophrenia-and-psychosis' => '02-Page-content/Service Users/Schizophrenia',
            'service-users-and-visitors/feedback-and-comments/service-user-experience-survey' => '02-Page-content/Service Users/Service User Experience Surveys',
            'service-users-and-visitors/service-user-participation/service-user-advisory-network' => '02-Page-content/Service Users/Service User Advisory Network',
            'service-users-and-visitors/service-user-participation/service-user-and-supporters-council' => '02-Page-content/Service Users/Service User and Supporters Council (SUAS)',
            'service-users-and-visitors/your-care-with-willow-grove' => '02-Page-content/Service Users/Your care with Willow Grove',
            'service-users-and-visitors/your-care-with-willow-grove/your-stay-in-hospital-as-an-adolescent' => '02-Page-content/Service Users/Your stay in hospital as an adolescent',
            'service-users-and-visitors/your-care-with-willow-grove/your-time-in-homecare-as-an-adolescent' => '02-Page-content/Service Users/Your time in homecare as an adolescent',
            'contact-us' => '02-Page-content/Contact Us/Contact Us',
        ];

        $programmes = [
            'acceptance-commitment-therapy-act' => 'Acceptance and Commitment Therapy',
            'access-to-recovery-programme' => 'Access to Recovery',
            'alcohol-chemical-step-down-programme' => 'Alcohol-Chemical Step-Down Programme',
            'anxiety-disorders-programme-2' => 'Anxiety Disorders Programme',
            'art-therapy' => 'Art Therapy',
            'bipolar-education-programme' => 'Bipolar Recovery Programme',
            'cognitive-behavioural-therapy' => 'Cognitive Behavioural Therapy (CBT)',
            'compassion-focused-therapy' => 'Compassion Focused Therapy (CFT)',
            'cft-for-adolescents-and-families' => 'CFT for Adolescents and Families',
            'cft-for-older-adults' => 'CFT for Older Adults',
            'depression-recovery-programme' => 'Depression Recovery Programme',
            'electroconvulsive-therapy' => 'Electroconvulsive Therapy',
            'emotion-focused-therapy-for-young-adults' => 'Emotion-Focused Therapy for Young Adults',
            'living-well-with-mild-cognitive-impairment' => 'Living Well with Mild Cognitive Impairment',
            'older-adult-formulation-group' => 'Older Adult Formulation Group',
            'pathways-to-wellness' => 'Pathways to Wellness',
            'pathways-to-wellness-2' => 'Pathways to Wellness',
            'psychosis-recovery-programme' => 'Psychosis Recovery Programme',
            'sage' => 'SAGE',
            'psychology-skills-group-for-adolescents-2' => 'Skills for Attention, Behaviour and Emotions for Adolescents and Families',
            'young-adult-psychology-groups' => 'Young Adult Formulation Group',
            'young-adult-programme' => 'Young Adult Programme',
            'young-adult-programme-2' => 'Young Adult Programme',
        ];

        foreach ($programmes as $slug => $folder_name) {
            $pages['cpt:programmes_therapies:' . $slug] = '02-Page-content/What We Offer/' . $folder_name;
        }

        $mental_health = [
            'personality-disorders' => '02-Page-content/Service Users/Personality disorders',
            'older-adults' => '02-Page-content/Service Users/Older adult mental health',
            'young-adults' => '02-Page-content/Service Users/Young adult mental health',
            'depression' => '02-Page-content/Service Users/Depression',
            'schizophrenia' => '02-Page-content/Service Users/Schizophrenia',
        ];

        foreach ($mental_health as $slug => $folder) {
            $pages['cpt:mental_health:' . $slug] = $folder;
        }

        return $pages;
    }
}

if (! function_exists('matrix_workbook_drive_lookup_keys_for_post')) {
    /**
     * @return array<int, string>
     */
    function matrix_workbook_drive_lookup_keys_for_post(WP_Post $post): array
    {
        $keys = [];

        if ($post->post_type === 'page') {
            $front_id = (int) get_option('page_on_front');

            if ((int) $post->ID === $front_id) {
                $keys[] = 'home';
            }

            $uri = get_page_uri($post);

            if (is_string($uri) && $uri !== '') {
                $keys[] = trim($uri, '/');
            }

            if ($post->post_name !== '') {
                $keys[] = $post->post_name;
            }
        } else {
            $keys[] = 'cpt:' . $post->post_type . ':' . $post->post_name;
            $keys[] = $post->post_name;
        }

        return array_values(array_unique(array_filter($keys)));
    }
}

if (! function_exists('matrix_workbook_drive_folder_for_post')) {
    /**
     * @return array{url: string, path: string, label: string}
     */
    function matrix_workbook_drive_folder_for_post(WP_Post $post): array
    {
        $ids = matrix_workbook_drive_folder_ids();
        $map = matrix_workbook_drive_page_folder_map();
        $folder_path = '';

        foreach (matrix_workbook_drive_lookup_keys_for_post($post) as $key) {
            if (isset($map[$key])) {
                $folder_path = $map[$key];
                break;
            }
        }

        if ($folder_path === '') {
            return [
                'url' => '',
                'path' => '',
                'label' => '',
            ];
        }

        // Crawl map may use slightly different punctuation (apostrophes / underscores).
        $normalize = static function (string $path): string {
            $path = str_replace(["\u{2019}", "\u{2018}", '`'], "'", $path);
            $path = str_replace('_', "'", $path);

            return $path;
        };

        $candidates = array_unique([
            $folder_path,
            $normalize($folder_path),
            str_replace("'", '_', $normalize($folder_path)),
            str_replace("'", "\u{2019}", $normalize($folder_path)),
        ]);

        $folder_id = '';
        $folders = $ids['folders'];
        $normalized_index = [];

        foreach ($folders as $path => $id) {
            $normalized_index[$normalize((string) $path)] = [(string) $path, (string) $id];
        }

        foreach ($candidates as $candidate) {
            if (isset($folders[$candidate])) {
                $folder_id = $folders[$candidate];
                $folder_path = $candidate;
                break;
            }

            $normalized = $normalize($candidate);

            if (isset($normalized_index[$normalized])) {
                [$folder_path, $folder_id] = $normalized_index[$normalized];
                break;
            }
        }

        if ($folder_id === '') {
            return [
                'url' => '',
                'path' => $folder_path,
                'label' => '',
            ];
        }

        return [
            'url' => 'https://drive.google.com/drive/folders/' . $folder_id,
            'path' => $folder_path,
            'label' => basename(str_replace('\\', '/', $folder_path)),
        ];
    }
}

if (! function_exists('matrix_workbook_content_on_drive_options')) {
    /**
     * @return array<int, string>
     */
    function matrix_workbook_content_on_drive_options(): array
    {
        return ['Yes', 'No'];
    }
}

if (! function_exists('matrix_workbook_content_on_drive_label')) {
    /**
     * Yes / No / blank. Prefers explicit meta; otherwise Yes when Drive import meta exists.
     */
    function matrix_workbook_content_on_drive_label(int $post_id): string
    {
        $stored = strtolower(trim((string) get_post_meta($post_id, MATRIX_WORKBOOK_CONTENT_ON_DRIVE_META_KEY, true)));

        if ($stored === 'yes') {
            return 'Yes';
        }

        if ($stored === 'no') {
            return 'No';
        }

        if (get_post_meta($post_id, '_matrix_drive3_import', true) !== '') {
            return 'Yes';
        }

        return '';
    }
}

if (! function_exists('matrix_workbook_apply_yes_no_controls')) {
    /**
     * Attach a Yes/No dropdown (and light colouring) to a column.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     */
    function matrix_workbook_apply_yes_no_controls($sheet, int $last_row, string $column): void
    {
        if ($last_row < 2) {
            return;
        }

        if (! class_exists(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::class)) {
            return;
        }

        $options = matrix_workbook_content_on_drive_options();
        $validation = $sheet->getCell($column . '2')->getDataValidation();
        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $validation->setAllowBlank(true);
        $validation->setShowDropDown(true);
        $validation->setShowInputMessage(true);
        $validation->setPromptTitle('Content on Drive');
        $validation->setPrompt('Yes = content came from Google Drive. No = used the form / other.');
        $validation->setShowErrorMessage(true);
        $validation->setFormula1('"' . implode(',', $options) . '"');
        $validation->setSqref($column . '2:' . $column . $last_row);

        $conditionals = [];

        $yes = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $yes->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
        $yes->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_EQUAL);
        $yes->addCondition('"Yes"');
        $yes->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $yes->getStyle()->getFill()->getStartColor()->setRGB('D4EDDA');
        $yes->getStyle()->getFont()->getColor()->setRGB('155724');
        $yes->getStyle()->getFont()->setBold(true);
        $conditionals[] = $yes;

        $no = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $no->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
        $no->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_EQUAL);
        $no->addCondition('"No"');
        $no->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $no->getStyle()->getFill()->getStartColor()->setRGB('F8D7DA');
        $no->getStyle()->getFont()->getColor()->setRGB('721C24');
        $no->getStyle()->getFont()->setBold(true);
        $conditionals[] = $no;

        $range = $column . '2:' . $column . $last_row;
        $sheet->getStyle($range)->setConditionalStyles($conditionals);
        $sheet->getStyle($range)->applyFromArray([
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);
    }
}
