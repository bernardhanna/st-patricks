<?php
/**
 * Draft all workbook "Delete" items and print redirect map entries.
 *
 * Reads: old/content/deletes-from-workbook.csv
 *
 * Usage:
 *   wp eval-file scripts/draft-and-redirect-deleted-pages.php dry-run
 *   wp eval-file scripts/draft-and-redirect-deleted-pages.php
 */

if (! defined('ABSPATH') || ! class_exists('WP_CLI')) {
    exit(1);
}

$dry_run = in_array('dry-run', $GLOBALS['argv'] ?? [], true);
$csv = get_template_directory() . '/old/content/deletes-from-workbook.csv';

if (! is_readable($csv)) {
    WP_CLI::error('Missing CSV: ' . $csv);
}

/**
 * Default redirect target by spreadsheet sheet.
 *
 * @return array<string, string>
 */
function matrix_delete_sheet_default_redirects(): array
{
    return [
        'Pages' => '/',
        'Posts' => '/news-and-events/',
        'Care & Treatment' => '/what-we-offer/',
        'Careers' => '/about-us/careers/',
        'Get Involved' => '/about-us/support-us/',
        'Locations' => '/about-us/our-locations/',
        'Mental Health Conditions' => '/service-users-and-visitors/about-mental-health/',
        'Outpatient Clinics' => '/what-we-offer/outpatient-care-dean-clinics/',
        'FAQs' => '/service-users-and-visitors/frequently-asked-questions-faqs/',
        'Programmes and Therapies' => '/programmes-therapies/',
        'Referrals' => '/healthcare-professionals/',
        'Research Projects' => '/about-us/research/',
        'Team Members' => '/about-us/our-team/',
        'Webinars' => '/healthcare-professionals/webinars-events/',
    ];
}

/**
 * Path-specific redirect overrides (more precise than sheet defaults).
 *
 * @return array<string, string>
 */
function matrix_delete_path_redirect_overrides(): array
{
    return [
        // Pages (existing map + extras)
        'advocacy-services' => '/about-us/our-present-and-future/advocacy-centre/',
        'advocacy-services/youth-advocacy' => '/human-rights-advocacy/',
        'getting-help/learning-resource-hub/anxiety-information-booklet' => '/service-users-and-visitors/',
        'getting-help/learning-resource-hub/carers-supporters-information-guide' => '/service-users-and-visitors/carers-and-supporters/',
        'getting-help/learning-resource-hub/coming-off-benzodiazepine-or-z-drugs' => '/service-users-and-visitors/',
        'getting-help/learning-resource-hub/managing-your-mental-health-in-difficult-times' => '/service-users-and-visitors/',
        'getting-help/learning-resource-hub/support-information-for-parents-and-guardians' => '/service-users-and-visitors/carers-and-supporters/',
        'getting-help/learning-resource-hub/willow-grove-adolescent-unit' => '/inpatient-adolescent-unit/',
        'getting-help' => '/service-users-and-visitors/',
        'getting-help/information-centre' => '/service-users-and-visitors/',
        'getting-help/insurance-information' => '/about-us/payment/',
        'getting-help/learning-resource-hub' => '/service-users-and-visitors/',
        'getting-help/support-information-service' => '/service-users-and-visitors/',
        'getting-help/carers-supporters/family-mental-health-series' => '/service-users-and-visitors/carers-and-supporters/',
        'get-involved' => '/about-us/support-us/',
        'get-involved/donations' => '/about-us/support-us/',
        'get-involved/fundraising' => '/about-us/support-us/',
        'get-involved/peer-support' => '/service-users-and-visitors/service-user-participation/',
        'get-involved/service-user-participation' => '/service-users-and-visitors/service-user-participation/',
        'get-involved/service-user-participation/news-for-service-users' => '/service-users-and-visitors/service-user-participation/',
        'get-involved/service-user-participation/service-user-and-supporters-council-suas' => '/service-users-and-visitors/service-user-participation/service-user-and-supporters-council/',
        'get-involved/service-user-participation/service-user-experience-survey' => '/service-users-and-visitors/feedback-and-comments/service-user-experience-survey/',
        'get-involved/news-for-service-users' => '/service-users-and-visitors/service-user-participation/',
        'get-involved/service-user-advisory-network-suan' => '/service-users-and-visitors/service-user-participation/service-user-advisory-network/',
        'get-involved/service-user-and-supporters-council-suas' => '/service-users-and-visitors/service-user-participation/service-user-and-supporters-council/',
        'get-involved/service-user-experience-survey' => '/service-users-and-visitors/feedback-and-comments/service-user-experience-survey/',
        'getinvolved' => '/about-us/support-us/',
        'referrals' => '/healthcare-professionals/',
        'make-a-referral' => '/healthcare-professionals/',
        'research' => '/about-us/research/',
        'current-research-projects' => '/about-us/research/',
        'past-research-projects' => '/about-us/research/',
        'st-patricks-lucan' => '/about-us/our-locations/',
        'st-patrick-s-university-hospital' => '/about-us/our-locations/',
        'inpatient-hospital-care' => '/inpatient-care/',
        'inpatient-hospital-care-how-to-access' => '/inpatient-care/',
        'how-to-access' => '/what-we-offer/',
        'treatment-options' => '/what-we-offer/',
        'visiting-information' => '/service-users-and-visitors/your-stay-in-hospital-as-an-adult/',
        'your-stay' => '/service-users-and-visitors/your-stay-in-hospital-as-an-adult/',
        'mental-health' => '/service-users-and-visitors/about-mental-health/',
        'yourmentalhealth' => '/service-users-and-visitors/about-mental-health/',
        'strategy-2018-2022' => '/about-us/',
        'collaborative-efforts' => '/about-us/',
        'flexi' => '/',
        'transformation-of-st-patricks-campus' => '/about-us/our-present-and-future/',
        'public-education-anti-stigma-campaigns' => '/lifewithoutstigma/',
        'shareyourexperience' => '/service-users-and-visitors/feedback-and-comments/',
        'service-users-and-visitors/attending-our-day-programmes' => '/what-we-offer/day-programmes/',

        // Care & Treatment CPT
        'care-treatment/homecare-service' => '/what-we-offer/st-patricks-at-home/',
        'care-treatment/medication' => '/service-users-and-visitors/medication/',
        'care-treatment/remote-services' => '/what-you-need-to-know-about-remote-care/',

        // Outpatient Clinics CPT
        'outpatient-clinics/about-the-dean-clinics' => '/what-we-offer/outpatient-care-dean-clinics/',

        // Referrals CPT
        'referrals/bed-vacancies' => '/healthcare-professionals/',
        'referrals/ereferral-guides' => '/healthcare-professionals/',
        'referrals/involuntary-admissions' => '/healthcare-professionals/involuntary-admissions/',
        'referrals/online-gp-cpd' => '/healthcare-professionals/training-centre/',
        'referrals/referrals-admissions' => '/healthcare-professionals/',
        'referrals/service-information' => '/healthcare-professionals/',
    ];
}

/**
 * Resolve a local path to a post ID across common post types.
 */
function matrix_delete_resolve_post_id(string $path): int
{
    $path = trim($path, '/');

    if ($path === '') {
        return 0;
    }

    // Prefer WordPress URL resolution against the local home URL (published only).
    $url = home_url('/' . $path . '/');
    $id = (int) url_to_postid($url);

    if ($id > 0) {
        return $id;
    }

    // Try hierarchical page path (any status).
    $page = get_page_by_path($path, OBJECT, ['page', 'get_involved', 'care_treatment', 'referrals', 'outpatient_clinics', 'research_projects', 'team_members', 'webinars', 'careers', 'programmes_therapies', 'mental_health', 'locations', 'faqs']);

    if ($page instanceof WP_Post) {
        return (int) $page->ID;
    }

    // Try last slug segment across public post types (includes drafts).
    $parts = explode('/', $path);
    $slug = end($parts);
    $post_types = get_post_types(['public' => true], 'names');

    $posts = get_posts([
        'name' => $slug,
        'post_type' => array_values($post_types),
        'post_status' => 'any',
        'posts_per_page' => 20,
        'suppress_filters' => true,
    ]);

    if (count($posts) === 1 && $posts[0] instanceof WP_Post) {
        return (int) $posts[0]->ID;
    }

    // Prefer exact permalink path match among candidates.
    foreach ($posts as $post) {
        if (! $post instanceof WP_Post) {
            continue;
        }

        // For drafts, get_permalink may use ?p=ID — also compare by CPT rewrite + slug.
        $permalink_path = trim((string) parse_url((string) get_permalink($post), PHP_URL_PATH), '/');

        if ($permalink_path === $path) {
            return (int) $post->ID;
        }

        $type_obj = get_post_type_object($post->post_type);
        $base = '';

        if ($type_obj && ! empty($type_obj->rewrite['slug'])) {
            $base = trim((string) $type_obj->rewrite['slug'], '/');
        }

        $candidate = $base !== '' ? $base . '/' . $post->post_name : $post->post_name;

        if ($candidate === $path) {
            return (int) $post->ID;
        }
    }

    return 0;
}

$sheet_defaults = matrix_delete_sheet_default_redirects();
$path_overrides = matrix_delete_path_redirect_overrides();

$handle = fopen($csv, 'r');
$header = fgetcsv($handle);
$rows = [];

while (($data = fgetcsv($handle)) !== false) {
    $row = array_combine($header, array_pad($data, count($header), ''));
    $rows[] = $row;
}

fclose($handle);

WP_CLI::log(($dry_run ? '[DRY RUN] ' : '') . 'Processing ' . count($rows) . ' Delete rows…');

$stats = [
    'drafted' => 0,
    'already_draft' => 0,
    'not_found' => 0,
    'errors' => 0,
];
$redirects = [];
$by_sheet = [];

foreach ($rows as $row) {
    $sheet = (string) ($row['sheet'] ?? '');
    $title = (string) ($row['title'] ?? '');
    $path = trim((string) ($row['path'] ?? ''), '/');
    $by_sheet[$sheet] = ($by_sheet[$sheet] ?? 0) + 1;

    if ($path === '') {
        WP_CLI::warning("Skip (empty path): {$title}");
        $stats['not_found']++;
        continue;
    }

    $redirect = $path_overrides[$path]
        ?? ($sheet_defaults[$sheet] ?? '/');

    $redirects[$path] = $redirect;

    $post_id = matrix_delete_resolve_post_id($path);

    if ($post_id <= 0) {
        WP_CLI::warning("Not found: [{$sheet}] {$title} (/{$path}/)");
        $stats['not_found']++;
        continue;
    }

    $post = get_post($post_id);
    $status = $post ? (string) $post->post_status : '';
    $type = $post ? (string) $post->post_type : '';

    if (in_array($status, ['draft', 'trash'], true)) {
        WP_CLI::log("Already {$status}: [{$sheet}] {$title} (ID {$post_id}, {$type}) → {$redirect}");
        $stats['already_draft']++;
        continue;
    }

    if ($dry_run) {
        WP_CLI::log("Would draft: [{$sheet}] {$title} (ID {$post_id}, {$type}) → {$redirect}");
        $stats['drafted']++;
        continue;
    }

    $result = wp_update_post([
        'ID' => $post_id,
        'post_status' => 'draft',
    ], true);

    if (is_wp_error($result)) {
        WP_CLI::warning("Error drafting {$title}: " . $result->get_error_message());
        $stats['errors']++;
        continue;
    }

    WP_CLI::success("Drafted: [{$sheet}] {$title} (ID {$post_id}, {$type}) → {$redirect}");
    $stats['drafted']++;
}

WP_CLI::log("\n" . str_repeat('=', 60));
WP_CLI::log('SUMMARY' . ($dry_run ? ' (DRY RUN)' : ''));
WP_CLI::log(str_repeat('=', 60));
WP_CLI::log('By sheet:');

foreach ($by_sheet as $sheet => $count) {
    WP_CLI::log("  {$sheet}: {$count}");
}

WP_CLI::log("Drafted/would draft: {$stats['drafted']}");
WP_CLI::log("Already draft/trash: {$stats['already_draft']}");
WP_CLI::log("Not found: {$stats['not_found']}");
WP_CLI::log("Errors: {$stats['errors']}");

// Persist redirect map JSON for theme merge.
$redirect_json = get_template_directory() . '/old/content/delete-redirect-map.json';

if (! $dry_run) {
    file_put_contents($redirect_json, wp_json_encode($redirects, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    WP_CLI::log('Wrote ' . $redirect_json);
}

WP_CLI::log("\nRedirect entries (" . count($redirects) . "):");

ksort($redirects);

foreach ($redirects as $from => $to) {
    echo "            '{$from}' => '{$to}',\n";
}

WP_CLI::success('Done.');
