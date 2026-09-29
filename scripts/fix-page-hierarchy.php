<?php
/**
 * Fix page hierarchy to match Slickplan sitemap.
 *
 * Usage:
 *   wp eval-file scripts/fix-page-hierarchy.php dry-run
 *   wp eval-file scripts/fix-page-hierarchy.php
 */

if (! defined('ABSPATH') || ! class_exists('WP_CLI')) {
    exit(1);
}

$dry_run = in_array('dry-run', $GLOBALS['argv'] ?? [], true);

// Get key parent page IDs
$about_us = get_page_by_path('about-us');
$our_present_and_future = get_page_by_path('about-us/our-present-and-future');
$our_team = get_page_by_path('about-us/our-team');
$careers = get_page_by_path('careers') ?: get_page_by_path('about-us/careers');
$healthcare_professionals = get_page_by_path('healthcare-professionals');
$service_users = get_page_by_path('service-users-and-visitors');
$what_we_offer = get_page_by_path('what-we-offer');
$your_portal = get_page_by_path('your-portal');
$recruitment = get_page_by_path('recruitment-and-useful-information');
$research = get_page_by_path('about-us/research') ?: get_page_by_path('research');

$parent_ids = [
    'about_us' => $about_us ? $about_us->ID : 0,
    'our_present_and_future' => $our_present_and_future ? $our_present_and_future->ID : 0,
    'our_team' => $our_team ? $our_team->ID : 0,
    'careers' => $careers ? $careers->ID : 0,
    'healthcare_professionals' => $healthcare_professionals ? $healthcare_professionals->ID : 0,
    'service_users' => $service_users ? $service_users->ID : 0,
    'what_we_offer' => $what_we_offer ? $what_we_offer->ID : 0,
    'your_portal' => $your_portal ? $your_portal->ID : 0,
    'recruitment' => $recruitment ? $recruitment->ID : 0,
    'research' => $research ? $research->ID : 0,
];

WP_CLI::log("Parent IDs resolved:");
foreach ($parent_ids as $key => $id) {
    WP_CLI::log("  {$key}: {$id}");
}
WP_CLI::log("");

/**
 * Pages that need to be moved to correct parents.
 * Format: 'slug-to-find' => 'parent_key'
 */
$pages_to_fix = [
    // Under "Our present and future" (about-us/our-present-and-future)
    'national-centre' => 'our_present_and_future',
    'new-hospital' => 'our_present_and_future',
    'advocacy-centre' => 'our_present_and_future',
    'academic-institute' => 'our_present_and_future',
    'traning-centre' => 'our_present_and_future',
    'extending-and-enhancing-our-services' => 'our_present_and_future',
    'partnering-with-service-users' => 'our_present_and_future',

    // Under "Our Team" (about-us/our-team)
    'psychiatrists' => 'our_team',
    'social-workers' => 'our_team',
    'nurses' => 'our_team',
    'occupational-therapists' => 'our_team',
    'psychologists' => 'our_team',
    'pharmacists' => 'our_team',

    // Under "About Us"
    'careers' => 'about_us',
    // Duplicate of extending-and-enhancing-our-services; draft + redirect instead of reparenting.
    // 'extending-our-services' => 'about_us',

    // Under "Careers" (about-us/careers)
    'recruitment-and-useful-information' => 'careers',

    // Under "Healthcare Professionals"
    'training-centre' => 'healthcare_professionals', // The one for GPs, not traning-centre typo

    // Under "Service Users and Visitors"
    'directions-and-parking' => 'service_users',
    'service-user-it-support' => 'service_users',

    // Under "Your Portal"
    'about-your-portal' => 'your_portal',
    'register-for-your-portal' => 'your_portal',
];

$stats = [
    'checked' => 0,
    'fixed' => 0,
    'not_found' => 0,
    'already_correct' => 0,
    'skipped_no_parent' => 0,
];

WP_CLI::log("Checking page hierarchy against Slickplan sitemap...\n");

foreach ($pages_to_fix as $slug => $parent_key) {
    $stats['checked']++;

    $target_parent_id = $parent_ids[$parent_key] ?? 0;

    if ($target_parent_id === 0) {
        WP_CLI::warning("⚠ Skipped '{$slug}': parent '{$parent_key}' not found");
        $stats['skipped_no_parent']++;
        continue;
    }

    // Find the page by slug
    $pages = get_posts([
        'post_type' => 'page',
        'post_status' => 'any',
        'name' => $slug,
        'posts_per_page' => 1,
    ]);

    if (empty($pages)) {
        // Try broader search
        $pages = get_posts([
            'post_type' => 'page',
            'post_status' => 'any',
            'posts_per_page' => -1,
        ]);
        $pages = array_filter($pages, fn($p) => $p->post_name === $slug);
        $pages = array_values($pages);
    }

    if (empty($pages)) {
        WP_CLI::log("✗ Not found: '{$slug}'");
        $stats['not_found']++;
        continue;
    }

    $page = $pages[0];
    $current_parent = (int) $page->post_parent;

    if ($current_parent === $target_parent_id) {
        WP_CLI::log("✓ Already correct: '{$page->post_title}' (ID: {$page->ID})");
        $stats['already_correct']++;
        continue;
    }

    $current_parent_title = $current_parent > 0 ? get_the_title($current_parent) : '(root)';
    $target_parent_title = get_the_title($target_parent_id);

    if ($dry_run) {
        WP_CLI::log("→ Would move: '{$page->post_title}' from '{$current_parent_title}' to '{$target_parent_title}'");
    } else {
        wp_update_post([
            'ID' => $page->ID,
            'post_parent' => $target_parent_id,
        ]);
        WP_CLI::success("✓ Moved: '{$page->post_title}' from '{$current_parent_title}' to '{$target_parent_title}'");
    }
    $stats['fixed']++;
}

WP_CLI::log("\n" . str_repeat('=', 60));
WP_CLI::log("SUMMARY" . ($dry_run ? ' (DRY RUN)' : ''));
WP_CLI::log(str_repeat('=', 60));
WP_CLI::log("Checked: {$stats['checked']}");
WP_CLI::log("Fixed/Would fix: {$stats['fixed']}");
WP_CLI::log("Already correct: {$stats['already_correct']}");
WP_CLI::log("Not found: {$stats['not_found']}");
WP_CLI::log("Skipped (no parent): {$stats['skipped_no_parent']}");

if ($dry_run && $stats['fixed'] > 0) {
    WP_CLI::log("\nRun without 'dry-run' to apply changes.");
}

// Show expected URL structure after fix
WP_CLI::log("\n" . str_repeat('=', 60));
WP_CLI::log("EXPECTED URL STRUCTURE (per Slickplan)");
WP_CLI::log(str_repeat('=', 60));
WP_CLI::log("
/about-us/
  /about-us/support-us/
  /about-us/overview/
  /about-us/our-team/
    /about-us/our-team/psychiatrists/
    /about-us/our-team/social-workers/
    /about-us/our-team/nurses/
    /about-us/our-team/occupational-therapists/
    /about-us/our-team/psychologists/
    /about-us/our-team/pharmacists/
  /about-us/policies-and-publications/
  /about-us/careers/
    /about-us/careers/recruitment-and-useful-information/
      /about-us/careers/recruitment-and-useful-information/brochures-pdfs/
      /about-us/careers/recruitment-and-useful-information/attending-an-interview/
      /about-us/careers/recruitment-and-useful-information/staff-wellbeing/
      /about-us/careers/recruitment-and-useful-information/how-to-get-work-experience/
      /about-us/careers/recruitment-and-useful-information/how-to-apply-for-a-role/
  /about-us/media-queries/
  /about-us/research/
    /about-us/research/current-research-projects/
    /about-us/research/past-research-projects/
    /about-us/research/spire/
    /about-us/research/research-ethics-committee/
  /about-us/advocacy/
  /about-us/our-history/
  /about-us/our-present-and-future/
    /about-us/our-present-and-future/national-centre/
    /about-us/our-present-and-future/new-hospital/
    /about-us/our-present-and-future/advocacy-centre/
    /about-us/our-present-and-future/academic-institute/
    /about-us/our-present-and-future/traning-centre/
    /about-us/our-present-and-future/extending-and-enhancing-our-services/
    /about-us/our-present-and-future/partnering-with-service-users/
  /about-us/our-locations/
");
