<?php

/**
 * Import Drive copy from SPMHS-Content-Gathering-Library 4.
 *
 * Sheet "Drive folder" = Y / Drive URL means: pull this folder.
 * Skips comment-only docs. Does not overwrite pages that already have
 * substantial non-placeholder flexi (or programme body copy).
 *
 * Usage:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/import-drive-library-4.php
 *   wp eval-file wp-content/themes/matrix-starter/scripts/import-drive-library-4.php dry-run
 */

if (! defined('ABSPATH')) {
    exit(1);
}

define('MATRIX_DRIVE3_NO_RUN', true);

require_once __DIR__ . '/import-drive-library-3.php';
require_once __DIR__ . '/lib/page-seed-conventions.php';

$dry_run = in_array('dry-run', array_map('strval', $GLOBALS['argv'] ?? []), true);
$library = get_template_directory() . '/old/content/SPMHS-Content-Gathering-Library 4';

$log = static function (string $message): void {
    if (class_exists('WP_CLI')) {
        WP_CLI::log($message);
    }
};
$warn = static function (string $message): void {
    if (class_exists('WP_CLI')) {
        WP_CLI::warning($message);
    }
};

if (! is_dir($library)) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Missing library folder: ' . $library);
    }
    exit(1);
}

$find_docx = static function (string $folder, array $prefer_substrings = []) use ($library): string {
    $path = $library . '/' . $folder;
    if ($prefer_substrings !== []) {
        if (! is_dir($path)) {
            return matrix_drive3_find_docx($path);
        }
        $files = glob($path . '/*.docx') ?: [];
        foreach ($prefer_substrings as $needle) {
            foreach ($files as $file) {
                $base = basename($file);
                if (str_starts_with($base, '~$')) {
                    continue;
                }
                if (str_contains(strtolower($base), strtolower($needle))) {
                    return $file;
                }
            }
        }
    }

    return matrix_drive3_find_docx($path);
};

$is_comment_only = static function (string $docx): bool {
    return (bool) preg_match('/comments on migrated|already in media library|image for /i', basename($docx));
};

$is_placeholder_html = static function (string $text): bool {
    return (bool) preg_match('/draft content for client gathering|replace this copy/i', $text);
};

$page_has_real_content = static function (int $post_id) use ($is_placeholder_html): bool {
    $post = get_post($post_id);
    if (! $post instanceof WP_Post) {
        return false;
    }
    $flexi = get_field('flexible_content_blocks', $post_id);
    $blob = '';
    if (is_array($flexi)) {
        foreach ($flexi as $row) {
            $blob .= ' ' . wp_strip_all_tags((string) ($row['content'] ?? ''));
            $blob .= ' ' . wp_strip_all_tags((string) ($row['intro'] ?? ''));
        }
        if (! $is_placeholder_html($blob) && strlen(trim($blob)) > 180) {
            return true;
        }
    }
    $body = trim(wp_strip_all_tags((string) $post->post_content));
    if (strlen($body) > 180 && ! $is_placeholder_html($body)) {
        return true;
    }

    return false;
};

$resolve_post = static function (array $item): int {
    if (! empty($item['id'])) {
        return (int) $item['id'];
    }
    if (! empty($item['path'])) {
        $post_id = matrix_seed_resolve_page_id_by_path($item['path']);
        if ($post_id > 0) {
            return $post_id;
        }
        $page = get_page_by_path($item['path']);
        if ($page instanceof WP_Post) {
            return (int) $page->ID;
        }
        foreach (['page', 'get_involved'] as $type) {
            $page = get_page_by_path($item['path'], OBJECT, $type);
            if ($page instanceof WP_Post) {
                return (int) $page->ID;
            }
        }
    }
    if (! empty($item['cpt']) && ! empty($item['slug'])) {
        $found = get_posts([
            'post_type' => $item['cpt'],
            'name' => $item['slug'],
            'post_status' => 'any',
            'posts_per_page' => 1,
        ]);

        return $found !== [] ? (int) $found[0]->ID : 0;
    }

    return 0;
};

$mark_drive = static function (int $post_id): void {
    update_post_meta($post_id, 'matrix_content_on_drive', 'yes');
    update_post_meta($post_id, '_matrix_drive3_import', gmdate('c'));
};

$page_map = [
    ['folder' => '01-Set-pages/About our St Patrick’s at Home Service', 'path' => 'service-users-and-visitors/about-our-st-patricks-at-home-service'],
    ['folder' => '02-Page-content/Service Users/About Your Portal', 'path' => 'your-portal/about-your-portal'],
    ['folder' => '02-Page-content/Service Users/About Your Portal', 'path' => 'about-your-portal'],
    ['folder' => '02-Page-content/Service Users/About mental health', 'path' => 'service-users-and-visitors/about-mental-health'],
    ['folder' => '02-Page-content/Service Users/Attending a Dean Clinic', 'path' => 'service-users-and-visitors/attending-a-dean-clinic', 'prefer' => ['service user information']],
    ['folder' => '02-Page-content/Service Users/Attending day programmes', 'path' => 'service-users-and-visitors/attending-day-programmes', 'prefer' => ['service user information']],
    ['folder' => '02-Page-content/Service Users/Attending day programmes', 'path' => 'service-users-and-visitors/attending-our-day-programmes', 'prefer' => ['service user information']],
    ['folder' => '02-Page-content/Service Users/Carers and Supporters', 'path' => 'service-users-and-visitors/carers-and-supporters'],
    ['folder' => '02-Page-content/Service Users/Depression', 'cpt' => 'mental_health', 'slug' => 'depression'],
    [
        'folder' => '02-Page-content/Service Users/Family, Carers and Supporters Advisory Network',
        'path' => 'service-users-and-visitors/service-user-participation/family-carers-and-supporters-advisory-network',
    ],
    ['folder' => '02-Page-content/Service Users/Frequently Asked Questions', 'path' => 'service-users-and-visitors/frequently-asked-questions-faqs', 'prefer' => ['FAQs for service users']],
    [
        'folder' => '02-Page-content/Service Users/Information for your family',
        'path' => 'service-users-and-visitors/your-care-with-willow-grove/information-for-your-family',
    ],
    ['folder' => '02-Page-content/Service Users/Older adult mental health', 'path' => 'service-users-and-visitors/older-adult-mental-health'],
    ['folder' => '02-Page-content/Service Users/Older adult mental health', 'cpt' => 'mental_health', 'slug' => 'older-adults'],
    [
        'folder' => '02-Page-content/Service Users/Personality disorders',
        'cpt' => 'mental_health',
        'slug' => 'personality-disorders',
        'prefer' => ['content(1)', 'page content'],
    ],
    ['folder' => '02-Page-content/Service Users/Psychosis', 'cpt' => 'mental_health', 'slug' => 'schizophrenia-psychosis'],
    ['folder' => '02-Page-content/Service Users/Schizophrenia', 'cpt' => 'mental_health', 'slug' => 'schizophrenia'],
    ['folder' => '02-Page-content/Service Users/Schizophrenia', 'path' => 'service-users-and-visitors/schizophrenia-and-psychosis'],
    ['folder' => '02-Page-content/Service Users/Schizophrenia', 'path' => 'service-users-and-visitors/schizophrenia'],
    [
        'folder' => '02-Page-content/Service Users/Service User Experience Surveys',
        'path' => 'service-users-and-visitors/feedback-and-comments/service-user-experience-survey',
    ],
    [
        'folder' => '02-Page-content/Service Users/Service User Participation',
        'path' => 'service-users-and-visitors/service-user-participation',
        'prefer' => ['Service User Participation.docx'],
    ],
    ['folder' => '02-Page-content/Service Users/Stories and support', 'path' => 'service-users-and-visitors/stories-and-support'],
    ['folder' => '02-Page-content/Service Users/Young adult mental health', 'path' => 'service-users-and-visitors/young-adult-mental-health'],
    ['folder' => '02-Page-content/Service Users/Young adult mental health', 'cpt' => 'mental_health', 'slug' => 'young-adults'],
    ['folder' => '02-Page-content/Service Users/Your care with Willow Grove', 'path' => 'service-users-and-visitors/your-care-with-willow-grove'],
    [
        'folder' => '02-Page-content/Service Users/Your stay in hospital as an adolescent',
        'path' => 'service-users-and-visitors/your-care-with-willow-grove/your-stay-in-hospital-as-an-adolescent',
    ],
    [
        'folder' => '02-Page-content/Service Users/Your stay in hospital as an adult',
        'path' => 'service-users-and-visitors/your-stay-in-hospital-as-an-adult',
    ],
    [
        'folder' => '02-Page-content/Service Users/Your time in homecare as an adolescent',
        'path' => 'service-users-and-visitors/your-care-with-willow-grove/your-time-in-homecare-as-an-adolescent',
    ],

    ['folder' => '02-Page-content/What We Offer/Inpatient care', 'path' => 'inpatient-care', 'keep' => true],
    ['folder' => '02-Page-content/What We Offer/St Patrick_s at Home', 'path' => 'what-we-offer/st-patricks-at-home', 'prefer' => ['adult and adolescent']],
    ['folder' => '02-Page-content/What We Offer/Day programmes', 'path' => 'what-we-offer/day-programmes', 'keep' => true],
    ['folder' => '02-Page-content/What We Offer/Outpatient care', 'path' => 'what-we-offer/outpatient-care-dean-clinics', 'keep' => true],

    ['folder' => '02-Page-content/Healthcare Professionals/Make a referral', 'path' => 'healthcare-professionals', 'keep' => true],
    ['folder' => '02-Page-content/Healthcare Professionals/Make a referral', 'path' => 'make-a-referral', 'keep' => true],
    ['folder' => '02-Page-content/Healthcare Professionals/Contact numbers', 'path' => 'healthcare-professionals/contact-numbers'],
    ['folder' => '02-Page-content/Healthcare Professionals/Involuntary admissions', 'path' => 'healthcare-professionals/involuntary-admissions'],
    ['folder' => '02-Page-content/Healthcare Professionals/Training Centre', 'path' => 'healthcare-professionals/training-centre', 'keep' => true],
    ['folder' => '02-Page-content/Healthcare Professionals/Refer an adult for inpatient care', 'path' => 'healthcare-professionals/refer-an-adult-for-inpatient-care'],
    ['folder' => '02-Page-content/Healthcare Professionals/Refer an adolescent for inpatient care', 'path' => 'healthcare-professionals/refer-an-adolescent-for-inpatient-care'],
    ['folder' => '02-Page-content/Healthcare Professionals/Refer to St Patrick_s at Home', 'path' => 'healthcare-professionals/refer-to-the-st-patricks-at-home-service'],
    ['folder' => '02-Page-content/Healthcare Professionals/Refer for Dean Clinics', 'path' => 'healthcare-professionals/refer-for-outpatient-care'],
    ['folder' => '02-Page-content/Healthcare Professionals/Refer for day services', 'path' => 'healthcare-professionals/refer-to-a-day-programme'],

    ['folder' => '02-Page-content/Contact Us/Contact Us', 'path' => 'contact-us', 'keep' => true],
    ['folder' => '02-Page-content/Contact Us/Thank you page', 'path' => 'contact-us/thank-you'],
    ['folder' => '02-Page-content/About Us/Thank you page', 'path' => 'thank-you-page'],
    ['folder' => '02-Page-content/About Us/Support Us', 'path' => 'about-us/support-us', 'prefer' => ['Support Us page.docx']],
    ['folder' => '02-Page-content/About Us/Women_s Mental Health Network (WMHN)', 'path' => 'about-us/advocacy/women-s-mental-health-network'],
    ['folder' => '02-Page-content/About Us/Apply for a role', 'path' => 'about-us/careers/recruitment-and-useful-information/how-to-apply-for-a-role'],
    ['folder' => '02-Page-content/About Us/Apply for a role', 'path' => 'recruitment-and-useful-information/how-to-apply-for-a-role'],
    ['folder' => '02-Page-content/About Us/Academic Institute', 'path' => 'about-us/our-present-and-future/academic-institute', 'prefer' => ['Academic Institute.docx']],
    ['folder' => '02-Page-content/About Us/Advocacy', 'path' => 'about-us/advocacy', 'prefer' => ['Advocacy final']],
    ['folder' => '02-Page-content/About Us/Advocacy centre', 'path' => 'about-us/our-present-and-future/advocacy-centre'],
    ['folder' => '02-Page-content/About Us/National centre', 'path' => 'about-us/our-present-and-future/national-centre'],
    ['folder' => '02-Page-content/About Us/New hospital', 'path' => 'about-us/our-present-and-future/new-hospital'],
    ['folder' => '02-Page-content/About Us/Our present and future', 'path' => 'about-us/our-present-and-future', 'prefer' => ['Our present and future page']],
    ['folder' => '02-Page-content/About Us/Partnering with service users', 'path' => 'about-us/our-present-and-future/partnering-with-service-users'],
    ['folder' => '02-Page-content/About Us/Policies and publications', 'path' => 'about-us/policies-and-publications'],
    ['folder' => '02-Page-content/About Us/Psychiatrists', 'path' => 'about-us/our-team/psychiatrists', 'prefer' => ['Psychiatrists - text']],
    ['folder' => '02-Page-content/About Us/Nurses', 'path' => 'about-us/our-team/nurses', 'prefer' => ['Nursing.docx']],
    ['folder' => '02-Page-content/About Us/Social workers', 'path' => 'about-us/our-team/social-workers', 'prefer' => ['Social worker text']],
    ['folder' => '02-Page-content/About Us/Occupational therapists', 'path' => 'about-us/our-team/occupational-therapists', 'prefer' => ['Occupational therapy page']],
    ['folder' => '02-Page-content/About Us/Clinical psychologists', 'path' => 'about-us/our-team/psychologists', 'prefer' => ['Pscyhology page', 'Psychology page']],
    ['folder' => '02-Page-content/About Us/Recruitment and useful information', 'path' => 'about-us/careers/recruitment-and-useful-information'],
    ['folder' => '02-Page-content/About Us/Staff wellbeing', 'path' => 'about-us/careers/recruitment-and-useful-information/staff-wellbeing'],
    ['folder' => '02-Page-content/About Us/Our locations', 'path' => 'about-us/our-locations', 'prefer' => ['landing page content']],
];

$programme_map = [
    'Acceptance and Commitment Therapy' => ['slug' => 'acceptance-commitment-therapy-act', 'title' => 'Acceptance and Commitment Therapy', 'type' => 'therapy', 'care' => 'day-patient-programme', 'delivery' => 'online'],
    'Access to Recovery' => ['slug' => 'access-to-recovery-programme', 'title' => 'Access to Recovery', 'type' => 'programme', 'care' => 'day-patient-programme', 'delivery' => 'hybrid'],
    'Alcohol-Chemical Step-Down Programme' => ['slug' => 'alcohol-chemical-step-down-programme', 'title' => 'Alcohol / Chemical Step Down Programme', 'type' => 'programme', 'care' => 'inpatient-programme', 'delivery' => 'in-person'],
    'Anxiety Disorders Programme' => ['slug' => 'anxiety-disorders-programme-2', 'title' => 'Anxiety Disorders Programme', 'type' => 'programme', 'care' => 'day-patient-programme', 'delivery' => 'hybrid'],
    'Art Therapy' => ['slug' => 'art-therapy', 'title' => 'Art Therapy', 'type' => 'therapy', 'care' => 'inpatient-programme', 'delivery' => 'in-person'],
    'Bipolar Recovery Programme' => ['slug' => 'bipolar-education-programme', 'title' => 'Bipolar Recovery Programme', 'type' => 'programme', 'care' => 'day-patient-programme', 'delivery' => 'hybrid'],
    'Cognitive Behavioural Therapy (CBT)' => ['slug' => 'cognitive-behavioural-therapy', 'title' => 'Cognitive Behavioural Therapy (CBT)', 'type' => 'therapy', 'care' => 'inpatient-programme', 'delivery' => 'in-person'],
    'Compassion Focused Therapy (CFT)' => ['slug' => 'compassion-focused-therapy', 'title' => 'Compassion-Focused Therapy', 'type' => 'therapy', 'care' => 'day-patient-programme', 'delivery' => 'hybrid'],
    'CFT for Adolescents and Families' => ['slug' => 'cft-for-adolescents-and-families', 'title' => 'Compassion-Focused Therapy for Adolescents and Families', 'type' => 'therapy', 'care' => 'day-patient-programme', 'delivery' => 'hybrid'],
    'CFT for Older Adults' => ['slug' => 'cft-for-older-adults', 'title' => 'Compassion-Focused Therapy for Older Adults', 'type' => 'therapy', 'care' => 'day-patient-programme', 'delivery' => 'hybrid'],
    'Depression Recovery Programme' => ['slug' => 'depression-recovery-programme', 'title' => 'Depression Recovery Programme', 'type' => 'programme', 'care' => 'day-patient-programme', 'delivery' => 'hybrid'],
    'Electroconvulsive Therapy' => ['slug' => 'electroconvulsive-therapy', 'title' => 'Electroconvulsive Therapy (ECT)', 'type' => 'therapy', 'care' => 'inpatient-programme', 'delivery' => 'in-person'],
    'Emotion-Focused Therapy for Young Adults' => ['slug' => 'emotion-focused-therapy-for-young-adults', 'title' => 'Emotion-Focused Therapy for Young Adults', 'type' => 'therapy', 'care' => 'day-patient-programme', 'delivery' => 'in-person'],
    'General Adult Formulation Group' => ['slug' => 'general-adult-formulation-group', 'title' => 'General Adult Formulation Group', 'type' => 'programme', 'care' => 'day-patient-programme', 'delivery' => 'in-person'],
    'Living Well with Mild Cognitive Impairment' => ['slug' => 'living-well-with-mild-cognitive-impairment', 'title' => 'Living Well with Mild Cognitive Impairment', 'type' => 'programme', 'care' => 'day-patient-programme', 'delivery' => 'in-person'],
    'Older Adult Formulation Group' => ['slug' => 'older-adult-formulation-group', 'title' => 'Older Adult Formulation Group', 'type' => 'programme', 'care' => 'day-patient-programme', 'delivery' => 'in-person'],
    'Pathways to Wellness' => ['slug' => 'pathways-to-wellness-2', 'title' => 'Pathways to Wellness', 'type' => 'programme', 'care' => 'day-patient-programme', 'delivery' => 'hybrid'],
    'Psychosis Recovery Programme' => ['slug' => 'psychosis-recovery-programme', 'title' => 'Psychosis Recovery Programme', 'type' => 'programme', 'care' => 'inpatient-programme', 'delivery' => 'hybrid'],
    'SAGE' => ['slug' => 'sage', 'title' => 'SAGE', 'type' => 'programme', 'care' => 'day-patient-programme', 'delivery' => 'hybrid'],
    'Skills for Attention, Behaviour and Emotions for Adolescents and Families' => ['slug' => 'psychology-skills-group-for-adolescents-2', 'title' => 'Skills for Attention, Behaviour and Emotions for Adolescents and Families', 'type' => 'programme', 'care' => 'day-patient-programme', 'delivery' => 'online'],
    'Temple Formulation Group' => ['slug' => 'temple-formulation-group', 'title' => 'Temple Formulation Group', 'type' => 'programme', 'care' => 'day-patient-programme', 'delivery' => 'in-person'],
    'Young Adult Programme' => ['slug' => 'young-adult-programme', 'title' => 'Young Adult Programme', 'type' => 'programme', 'care' => 'day-patient-programme', 'delivery' => 'hybrid'],
];

$results = [];
$log($dry_run ? 'Dry run: Library 4 (Y / Drive folders)' : 'Importing Library 4 (Y / Drive folders)');

foreach ($page_map as $item) {
    $folder_rel = $item['folder'];
    $folder = $library . '/' . $folder_rel;
    $docx = $find_docx($folder_rel, $item['prefer'] ?? []);
    $label = $item['path'] ?? (($item['cpt'] ?? '') . '/' . ($item['slug'] ?? ''));

    if ($docx === '' || $is_comment_only($docx)) {
        $results[] = $label . ': skipped-docx';
        continue;
    }

    $post_id = $resolve_post($item);
    if ($post_id <= 0) {
        $warn('Missing WP destination for ' . $label);
        $results[] = $label . ': missing-page';
        continue;
    }

    if ($page_has_real_content($post_id)) {
        if (! $dry_run) {
            $mark_drive($post_id);
        }
        $results[] = $label . ': already-has-content #' . $post_id;
        continue;
    }

    if ($dry_run) {
        $log('[dry-run] ' . $label . ' <- ' . basename($docx) . ' (#' . $post_id . ')');
        $results[] = $label . ': will-import #' . $post_id;
        continue;
    }

    $status = matrix_drive3_apply_page($post_id, $docx, $folder, [
        'keep' => ! empty($item['keep']),
        'builder' => true,
        'status' => (string) ($item['status'] ?? 'publish'),
    ]);
    $mark_drive($post_id);
    $log($label . ' #' . $post_id . ' ' . $status);
    $results[] = $label . ': ' . $status . ' #' . $post_id;
}

$offer_root = $library . '/02-Page-content/What We Offer';
foreach ($programme_map as $folder_name => $spec) {
    $folder = $offer_root . '/' . $folder_name;
    $docx = matrix_drive3_find_docx($folder);
    if ($docx === '' || $is_comment_only($docx)) {
        $results[] = 'programme/' . $spec['slug'] . ': skipped-docx';
        continue;
    }
    $post_id = matrix_drive3_ensure_programme($spec);
    if ($post_id <= 0) {
        $results[] = 'programme/' . $spec['slug'] . ': missing';
        continue;
    }
    if ($page_has_real_content($post_id)) {
        if (! $dry_run) {
            $mark_drive($post_id);
        }
        $results[] = 'programme/' . $spec['slug'] . ': already-has-content #' . $post_id;
        continue;
    }
    if ($dry_run) {
        $results[] = 'programme/' . $spec['slug'] . ': will-import';
        continue;
    }
    $status = matrix_drive3_apply_programme($spec, $docx, $folder);
    $mark_drive($post_id);
    $results[] = 'programme/' . $spec['slug'] . ': ' . $status . ' #' . $post_id;
}

$log('');
$log('Library 4 import summary');
$counts = ['imported' => 0, 'already' => 0, 'missing' => 0, 'skipped' => 0, 'other' => 0];
foreach ($results as $line) {
    if (str_contains($line, 'already-has-content')) {
        $counts['already']++;
    } elseif (str_contains($line, 'missing')) {
        $counts['missing']++;
    } elseif (str_contains($line, 'skipped')) {
        $counts['skipped']++;
    } elseif (str_contains($line, 'updated') || str_contains($line, 'will-import')) {
        $counts['imported']++;
    } else {
        $counts['other']++;
    }
}
$log(sprintf(
    'imported/will:%d already:%d missing:%d skipped:%d other:%d',
    $counts['imported'],
    $counts['already'],
    $counts['missing'],
    $counts['skipped'],
    $counts['other']
));
foreach ($results as $line) {
    if (! str_contains($line, 'already-has-content')) {
        $log(' - ' . $line);
    }
}

if (class_exists('WP_CLI')) {
    WP_CLI::success($dry_run ? 'Dry run finished.' : 'Library 4 content imported.');
}
