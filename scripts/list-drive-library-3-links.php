<?php

/**
 * Print staging URLs + source Word docs for the Library 3 import.
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/list-drive-library-3-links.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

$staging = 'https://st-patricks.s1.matrix-test.com';
$library = get_template_directory() . '/old/content/SPMHS-Content-Gathering-Library 3';

$to_staging = static function (string $url) use ($staging): string {
    $parts = wp_parse_url($url);
    $path = isset($parts['path']) ? (string) $parts['path'] : '/';
    $query = isset($parts['query']) && $parts['query'] !== '' ? '?' . $parts['query'] : '';

    return rtrim($staging, '/') . $path . $query;
};

$find_docx = static function (string $folder) use ($library): array {
    if (! is_dir($folder)) {
        $parent = dirname($folder);
        $needle = str_replace(["\u{2019}", "\u{2018}", "'"], "'", basename($folder));
        if (is_dir($parent)) {
            foreach (scandir($parent) ?: [] as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }
                $norm = str_replace(["\u{2019}", "\u{2018}", "'"], "'", $entry);
                if (strcasecmp($norm, $needle) === 0) {
                    $folder = $parent . '/' . $entry;
                    break;
                }
            }
        }
    }
    if (! is_dir($folder)) {
        return ['', ''];
    }
    $preferred = [];
    $fallback = [];
    foreach (glob($folder . '/*.docx') ?: [] as $file) {
        $base = basename($file);
        if (str_starts_with($base, '~$')) {
            continue;
        }
        if (preg_match('/layout|comments on migrated|already in media library/i', $base)) {
            $fallback[] = $file;
            continue;
        }
        $preferred[] = $file;
    }
    $docx = $preferred[0] ?? $fallback[0] ?? '';
    if ($docx === '') {
        return ['', ''];
    }
    $rel = 'SPMHS-Content-Gathering-Library 3/' . ltrim(str_replace($library . '/', '', $docx), '/');

    return [basename($docx), $rel];
};

$page_permalink = static function (string $path) use ($to_staging): string {
    if ($path === 'home') {
        $id = (int) get_option('page_on_front');

        return $id > 0 ? $to_staging((string) get_permalink($id)) : 'MISSING';
    }
    $page = get_page_by_path($path);

    return $page instanceof WP_Post ? $to_staging((string) get_permalink($page)) : 'MISSING';
};

$cpt_permalink = static function (string $post_type, string $slug) use ($to_staging): string {
    $found = get_posts([
        'post_type' => $post_type,
        'name' => $slug,
        'post_status' => 'any',
        'posts_per_page' => 1,
    ]);

    return $found !== [] ? $to_staging((string) get_permalink($found[0])) : 'MISSING';
};

$rows = [];
$add = static function (string $section, string $title, string $url, string $doc, string $rel) use (&$rows): void {
    $rows[] = compact('section', 'title', 'url', 'doc', 'rel');
};

$pages = [
    ['Homepage', 'Homepage', 'home', '01-Set-pages/Homepage'],
    ['About Us', 'Support us', 'about-us/support-us', '02-Page-content/About Us/Support Us'],
    ['About Us', 'Psychiatrists', 'about-us/psychiatrists', '02-Page-content/About Us/Psychiatrists'],
    ['About Us', 'Social workers', 'about-us/social-workers', '02-Page-content/About Us/Social workers'],
    ['About Us', 'Nurses', 'about-us/nurses', '02-Page-content/About Us/Nurses'],
    ['About Us', 'Occupational therapists', 'about-us/occupational-therapists', '02-Page-content/About Us/Occupational therapists'],
    ['About Us', 'Psychologists', 'about-us/psychologists', '02-Page-content/About Us/Clinical psychologists'],
    ['About Us', 'Recruitment and useful information', 'recruitment-and-useful-information', '02-Page-content/About Us/Recruitment and useful information'],
    ['About Us', 'Staff wellbeing', 'recruitment-and-useful-information/staff-wellbeing', '02-Page-content/About Us/Staff wellbeing'],
    ['About Us', 'How to apply for a role', 'recruitment-and-useful-information/how-to-apply-for-a-role', '02-Page-content/About Us/Apply for a role'],
    ['About Us', 'Thank you page', 'thank-you-page', '02-Page-content/About Us/Thank you page'],
    ['About Us', 'Our locations', 'about-us/our-locations', '02-Page-content/About Us/Our locations'],
    ['About Us', 'Academic Institute', 'academic-institute', '02-Page-content/About Us/Academic Institute'],
    ['About Us', 'Training Centre', 'healthcare-professionals/training-centre', '02-Page-content/About Us/Training Centre'],
    ['About Us', 'Extending our services', 'about-us/our-present-and-future/extending-and-enhancing-our-services', '02-Page-content/About Us/Extending our services'],
    ['About Us', 'National centre', 'national-centre', '02-Page-content/About Us/National centre'],
    ['About Us', 'New hospital', 'new-hospital', '02-Page-content/About Us/New hospital'],
    ['About Us', 'Advocacy', 'about-us/advocacy', '02-Page-content/About Us/Advocacy'],
    ['About Us', 'Advocacy centre', 'advocacy-centre', '02-Page-content/About Us/Advocacy centre'],
    ['About Us', 'Our present and future', 'about-us/our-present-and-future', '02-Page-content/About Us/Our present and future'],
    ['About Us', 'Partnering with service users', 'about-us/partnering-with-service-users', '02-Page-content/About Us/Partnering with service users'],
    ['About Us', 'Policies and publications', 'about-us/policies-and-publications', '02-Page-content/About Us/Policies and publications'],
    ['What we offer', 'Inpatient care', 'inpatient-care', '02-Page-content/What We Offer/Inpatient care'],
    ['What we offer', "St Patrick's at Home", 'what-we-offer/st-patricks-at-home', '02-Page-content/What We Offer/St Patrick_s at Home'],
    ['What we offer', 'Day programmes', 'what-we-offer/day-programmes', '02-Page-content/What We Offer/Day programmes'],
    ['What we offer', 'Outpatient care — Dean Clinics', 'what-we-offer/outpatient-care-dean-clinics', '02-Page-content/What We Offer/Outpatient care'],
    ['Healthcare professionals', 'Make a referral', 'make-a-referral', '02-Page-content/Healthcare Professionals/Make a referral'],
    ['Healthcare professionals', 'Contact numbers', 'healthcare-professionals/contact-numbers', '02-Page-content/Healthcare Professionals/Contact numbers'],
    ['Service users', 'About Your Portal', 'about-your-portal', '02-Page-content/Service Users/About Your Portal'],
    ['Service users', 'About mental health', 'service-users-and-visitors/about-mental-health', '02-Page-content/Service Users/About mental health'],
    ['Service users', 'Attending a Dean Clinic', 'service-users-and-visitors/attending-a-dean-clinic', '02-Page-content/Service Users/Attending a Dean Clinic'],
    ['Service users', 'Attending day programmes', 'service-users-and-visitors/attending-day-programmes', '02-Page-content/Service Users/Attending day programmes'],
    ['Service users', 'FAQs', 'service-users-and-visitors/frequently-asked-questions-faqs', '02-Page-content/Service Users/Frequently Asked Questions'],
    ['Service users', 'Older adult mental health', 'service-users-and-visitors/older-adult-mental-health', '02-Page-content/Service Users/Older adult mental health'],
    ['Service users', 'Young adult mental health', 'service-users-and-visitors/young-adult-mental-health', '02-Page-content/Service Users/Young adult mental health'],
    ['Service users', 'Stories and support', 'service-users-and-visitors/stories-and-support', '02-Page-content/Service Users/Stories and support'],
    ['Service users', 'Service User Participation', 'service-users-and-visitors/service-user-participation', '02-Page-content/Service Users/Service User Participation'],
    ['Service users', "About our St Patrick's at Home Service", 'service-users-and-visitors/about-our-st-patricks-at-home-service', "01-Set-pages/About our St Patrick's at Home Service"],
];

foreach ($pages as [$section, $title, $path, $folder]) {
    [$doc, $rel] = $find_docx($library . '/' . $folder);
    $add($section, $title, $page_permalink($path), $doc, $rel);
}

$mh = [
    ['Mental health', 'Personality disorders', 'personality-disorders', '02-Page-content/Service Users/Personality disorders'],
    ['Mental health', 'Older adults', 'older-adults', '02-Page-content/Service Users/Older adult mental health'],
    ['Mental health', 'Young adults', 'young-adults', '02-Page-content/Service Users/Young adult mental health'],
];
foreach ($mh as [$section, $title, $slug, $folder]) {
    [$doc, $rel] = $find_docx($library . '/' . $folder);
    $add($section, $title, $cpt_permalink('mental_health', $slug), $doc, $rel);
}

$programmes = [
    'Acceptance and Commitment Therapy' => 'acceptance-commitment-therapy-act',
    'Access to Recovery' => 'access-to-recovery-programme',
    'Alcohol-Chemical Step-Down Programme' => 'alcohol-chemical-step-down-programme',
    'Anxiety Disorders Programme' => 'anxiety-disorders-programme-2',
    'Art Therapy' => 'art-therapy',
    'Bipolar Recovery Programme' => 'bipolar-education-programme',
    'Cognitive Behavioural Therapy (CBT)' => 'cognitive-behavioural-therapy',
    'Compassion Focused Therapy (CFT)' => 'compassion-focused-therapy',
    'CFT for Adolescents and Families' => 'cft-for-adolescents-and-families',
    'CFT for Older Adults' => 'cft-for-older-adults',
    'Depression Recovery Programme' => 'depression-recovery-programme',
    'Electroconvulsive Therapy' => 'electroconvulsive-therapy',
    'Emotion-Focused Therapy for Young Adults' => 'emotion-focused-therapy-for-young-adults',
    'Living Well with Mild Cognitive Impairment' => 'living-well-with-mild-cognitive-impairment',
    'Older Adult Formulation Group' => 'older-adult-formulation-group',
    'Pathways to Wellness' => 'pathways-to-wellness-2',
    'Psychosis Recovery Programme' => 'psychosis-recovery-programme',
    'SAGE' => 'sage',
    'Skills for Attention, Behaviour and Emotions for Adolescents and Families' => 'psychology-skills-group-for-adolescents-2',
    'Young Adult Formulation Group' => 'young-adult-psychology-groups',
    'Young Adult Programme' => 'young-adult-programme-2',
];
foreach ($programmes as $folder_name => $slug) {
    $url = $cpt_permalink('programmes_therapies', $slug);
    $title = $folder_name;
    $found = get_posts([
        'post_type' => 'programmes_therapies',
        'name' => $slug,
        'post_status' => 'any',
        'posts_per_page' => 1,
    ]);
    if ($found !== []) {
        $title = $found[0]->post_title;
    }
    [$doc, $rel] = $find_docx($library . '/02-Page-content/What We Offer/' . $folder_name);
    $add('Programmes & therapies', $title, $url, $doc, $rel);
}

$out = get_template_directory() . '/old/content/DRIVE-LIBRARY-3-PAGE-LINKS.md';
$md = "# Drive Library 3 — page links and source docs\n\n";
$md .= "Staging: {$staging}\n";
$md .= "Source dump: `old/content/SPMHS-Content-Gathering-Library 3`\n\n";

$current = '';
foreach ($rows as $row) {
    if ($row['section'] !== $current) {
        $current = $row['section'];
        $md .= "## {$current}\n\n";
    }
    $md .= "- **{$row['title']}**\n";
    $md .= "  - Page: {$row['url']}\n";
    $md .= '  - Doc: ' . ($row['doc'] !== '' ? $row['doc'] : 'not found') . "\n";
    if ($row['rel'] !== '') {
        $md .= "  - Path: `{$row['rel']}`\n";
    }
    $md .= "\n";
}

file_put_contents($out, $md);

if (class_exists('WP_CLI')) {
    WP_CLI::log($md);
    WP_CLI::success('Wrote ' . $out);
}
