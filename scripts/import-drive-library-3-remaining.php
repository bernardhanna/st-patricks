<?php

/**
 * Import remaining Drive Library 3 content for:
 * - Healthcare Professionals (Involuntary admissions — other HP pages already rebuilt)
 * - Service Users (full page drafts; skip comment-only folders)
 * - What We Offer (landing/service pages + programmes_therapies CPT)
 *
 * Does not re-run homepage or overwrite carefully hand-built HP pages
 * (Make a referral / Contact numbers / Training Centre).
 *
 * Usage:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/import-drive-library-3-remaining.php
 *   wp eval-file wp-content/themes/matrix-starter/scripts/import-drive-library-3-remaining.php dry-run
 */

if (! defined('ABSPATH')) {
    exit(1);
}

define('MATRIX_DRIVE3_NO_RUN', true);

require_once __DIR__ . '/import-drive-library-3.php';

$dry_run = in_array('dry-run', array_map('strval', $GLOBALS['argv'] ?? []), true);
$library = get_template_directory() . '/old/content/SPMHS-Content-Gathering-Library 3';

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

/**
 * Prefer newer/longer content docs when multiple .docx exist.
 */
$find_docx = static function (string $folder, array $prefer_substrings = []) use ($library): string {
    $path = $library . '/' . $folder;
    if ($prefer_substrings !== []) {
        if (! is_dir($path)) {
            return matrix_drive3_find_docx($path);
        }
        $files = glob($path . '/*.docx') ?: [];
        foreach ($prefer_substrings as $needle) {
            foreach ($files as $file) {
                if (str_contains(strtolower(basename($file)), strtolower($needle)) && ! str_starts_with(basename($file), '~$')) {
                    return $file;
                }
            }
        }
    }

    return matrix_drive3_find_docx($path);
};

$page_map = [
    // Healthcare Professionals — Make a referral / Contact numbers / Training Centre /
    // Involuntary admissions are handled by rebuild-healthcare-professionals-from-drive.php

    // Service Users — full drafts
    ['folder' => '02-Page-content/Service Users/About Your Portal', 'path' => 'about-your-portal'],
    ['folder' => '02-Page-content/Service Users/About mental health', 'path' => 'service-users-and-visitors/about-mental-health'],
    ['folder' => '02-Page-content/Service Users/Attending a Dean Clinic', 'path' => 'service-users-and-visitors/attending-a-dean-clinic'],
    ['folder' => '02-Page-content/Service Users/Attending day programmes', 'path' => 'service-users-and-visitors/attending-day-programmes', 'prefer' => ['service user information']],
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
    [
        'folder' => '02-Page-content/Service Users/Personality disorders',
        'cpt' => 'mental_health',
        'slug' => 'personality-disorders',
        'prefer' => ['content(1)', 'page content'],
    ],
    [
        'folder' => '02-Page-content/Service Users/Schizophrenia',
        'cpt' => 'mental_health',
        'slug' => 'schizophrenia',
    ],
    [
        'folder' => '02-Page-content/Service Users/Schizophrenia',
        'path' => 'service-users-and-visitors/schizophrenia-and-psychosis',
        'status' => 'publish',
    ],
    [
        'folder' => '02-Page-content/Service Users/Service User Advisory Network',
        'path' => 'get-involved/service-user-participation/service-user-advisory-network-suan',
    ],
    [
        'folder' => '02-Page-content/Service Users/Service User Experience Surveys',
        'path' => 'service-users-and-visitors/feedback-and-comments/service-user-experience-survey',
        'prefer' => ['Service User Experience Surveys'],
    ],
    [
        'folder' => '02-Page-content/Service Users/Service User Participation',
        'path' => 'service-users-and-visitors/service-user-participation',
        'prefer' => ['Service User Participation.docx'],
    ],
    [
        'folder' => '02-Page-content/Service Users/Service User and Supporters Council (SUAS)',
        'path' => 'get-involved/service-user-participation/service-user-and-supporters-council-suas',
    ],
    ['folder' => '02-Page-content/Service Users/Stories and support', 'path' => 'service-users-and-visitors/stories-and-support'],
    ['folder' => '02-Page-content/Service Users/Young adult mental health', 'path' => 'service-users-and-visitors/young-adult-mental-health'],
    [
        'folder' => '02-Page-content/Service Users/Your care with Willow Grove',
        'path' => 'service-users-and-visitors/your-care-with-willow-grove',
    ],
    [
        'folder' => '02-Page-content/Service Users/Your stay in hospital as an adolescent',
        'path' => 'service-users-and-visitors/your-care-with-willow-grove/your-stay-in-hospital-as-an-adolescent',
    ],
    [
        'folder' => '02-Page-content/Service Users/Your time in homecare as an adolescent',
        'path' => 'service-users-and-visitors/your-care-with-willow-grove/your-time-in-homecare-as-an-adolescent',
    ],

    // What We Offer — section pages
    ['folder' => '02-Page-content/What We Offer/Inpatient care', 'path' => 'inpatient-care', 'keep' => true],
    ['folder' => '02-Page-content/What We Offer/St Patrick_s at Home', 'path' => 'what-we-offer/st-patricks-at-home', 'prefer' => ['adult and adolescent']],
    ['folder' => '02-Page-content/What We Offer/Day programmes', 'path' => 'what-we-offer/day-programmes', 'keep' => true],
    ['folder' => '02-Page-content/What We Offer/Outpatient care', 'path' => 'what-we-offer/outpatient-care-dean-clinics', 'keep' => true],
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
    'Living Well with Mild Cognitive Impairment' => ['slug' => 'living-well-with-mild-cognitive-impairment', 'title' => 'Living Well with Mild Cognitive Impairment', 'type' => 'programme', 'care' => 'day-patient-programme', 'delivery' => 'in-person'],
    'Older Adult Formulation Group' => ['slug' => 'older-adult-formulation-group', 'title' => 'Older Adult Formulation Group', 'type' => 'programme', 'care' => 'day-patient-programme', 'delivery' => 'in-person'],
    'Pathways to Wellness' => ['slug' => 'pathways-to-wellness-2', 'title' => 'Pathways to Wellness', 'type' => 'programme', 'care' => 'day-patient-programme', 'delivery' => 'hybrid'],
    'Psychosis Recovery Programme' => ['slug' => 'psychosis-recovery-programme', 'title' => 'Psychosis Recovery Programme', 'type' => 'programme', 'care' => 'inpatient-programme', 'delivery' => 'hybrid'],
    'SAGE' => ['slug' => 'sage', 'title' => 'SAGE', 'type' => 'programme', 'care' => 'day-patient-programme', 'delivery' => 'hybrid'],
    'Skills for Attention, Behaviour and Emotions for Adolescents and Families' => ['slug' => 'psychology-skills-group-for-adolescents-2', 'title' => 'Skills for Attention, Behaviour and Emotions for Adolescents and Families', 'type' => 'programme', 'care' => 'day-patient-programme', 'delivery' => 'online'],
    'Young Adult Formulation Group' => ['slug' => 'young-adult-psychology-groups', 'title' => 'Young Adult Formulation Group', 'type' => 'programme', 'care' => 'day-patient-programme', 'delivery' => 'in-person'],
    'Young Adult Programme' => ['slug' => 'young-adult-programme', 'title' => 'Young Adult Programme', 'type' => 'programme', 'care' => 'day-patient-programme', 'delivery' => 'hybrid'],
];

$mh_extra = [
    ['folder' => '02-Page-content/Service Users/Older adult mental health', 'slug' => 'older-adults'],
    ['folder' => '02-Page-content/Service Users/Young adult mental health', 'slug' => 'young-adults'],
    ['folder' => '02-Page-content/Service Users/Depression', 'slug' => 'depression'],
];

$results = [];
$log($dry_run ? 'Dry run: remaining Library 3 (HP / Service Users / What We Offer)' : 'Importing remaining Library 3 content');

foreach ($page_map as $item) {
    $folder_rel = $item['folder'];
    $folder = $library . '/' . $folder_rel;
    $docx = $find_docx($folder_rel, $item['prefer'] ?? []);
    $label = $item['path'] ?? (($item['cpt'] ?? '') . '/' . ($item['slug'] ?? ''));

    if ($docx === '') {
        $warn('No docx in ' . $folder_rel);
        $results[] = $label . ': no-docx';
        continue;
    }

    $post_id = 0;
    if (! empty($item['path'])) {
        $post_id = matrix_seed_resolve_page_id_by_path($item['path']);
        if ($post_id === 0) {
            $page = get_page_by_path($item['path']);
            $post_id = $page instanceof WP_Post ? (int) $page->ID : 0;
        }
    } elseif (! empty($item['cpt']) && ! empty($item['slug'])) {
        $found = get_posts([
            'post_type' => $item['cpt'],
            'name' => $item['slug'],
            'post_status' => 'any',
            'posts_per_page' => 1,
        ]);
        $post_id = $found !== [] ? (int) $found[0]->ID : 0;
    }

    if ($post_id <= 0) {
        $warn('Missing WP destination for ' . $label);
        $results[] = $label . ': missing-page';
        continue;
    }

    if ($dry_run) {
        $log('[dry-run] ' . $label . ' <- ' . basename($docx) . ' (#' . $post_id . ')');
        $results[] = $label . ': dry-run #' . $post_id;
        continue;
    }

    $status = matrix_drive3_apply_page($post_id, $docx, $folder, [
        'keep' => ! empty($item['keep']),
        'builder' => true,
        'status' => (string) ($item['status'] ?? 'publish'),
    ]);
    $log($label . ' #' . $post_id . ' ' . $status);
    $results[] = $label . ': ' . $status;
}

$offer_root = $library . '/02-Page-content/What We Offer';
foreach ($programme_map as $folder_name => $spec) {
    $folder = $offer_root . '/' . $folder_name;
    $docx = matrix_drive3_find_docx($folder);
    if ($docx === '') {
        $warn('Programme missing docx: ' . $folder_name);
        $results[] = 'programme/' . $spec['slug'] . ': no-docx';
        continue;
    }
    if ($dry_run) {
        $log('[dry-run] programme ' . $spec['title'] . ' <- ' . basename($docx));
        $results[] = 'programme/' . $spec['slug'] . ': dry-run';
        continue;
    }
    $status = matrix_drive3_apply_programme($spec, $docx, $folder);
    $log('programme ' . $spec['title'] . ' ' . $status);
    $results[] = 'programme/' . $spec['slug'] . ': ' . $status;
}

foreach ($mh_extra as $mh) {
    $found = get_posts([
        'post_type' => 'mental_health',
        'name' => $mh['slug'],
        'post_status' => 'any',
        'posts_per_page' => 1,
    ]);
    $docx = matrix_drive3_find_docx($library . '/' . $mh['folder']);
    if ($found === [] || $docx === '') {
        $results[] = 'mental_health/' . $mh['slug'] . ': missing';
        continue;
    }
    if ($dry_run) {
        $results[] = 'mental_health/' . $mh['slug'] . ': dry-run #' . $found[0]->ID;
        continue;
    }
    matrix_drive3_apply_page((int) $found[0]->ID, $docx, $library . '/' . $mh['folder'], [
        'builder' => true,
        'status' => 'publish',
    ]);
    $results[] = 'mental_health/' . $mh['slug'] . ': updated';
}

$log('');
$log('Skipped (no full draft / comment-only / empty):');
$log('- HP: Clinician insights, FAQs, landing, Webinars, refer-* pathways');
$log('- SU: About St Patrick_s at Home, Medication, Schizophrenia and psychosis (empty), Your stay as adult');
$log('- SU comments-only: Addiction, Bipolar, Eating disorders');
$log('- WWO empty: General Adult Formulation, Memory Clinic, Programmes and therapies, What we offer landing');
$log('- HP already rebuilt: Make a referral, Contact numbers, Training Centre');

$log('');
$log('Remaining Library 3 import summary');
foreach ($results as $line) {
    $log(' - ' . $line);
}

if (class_exists('WP_CLI')) {
    WP_CLI::success($dry_run ? 'Dry run finished.' : 'Remaining Library 3 content imported.');
}
