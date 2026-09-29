<?php

/**
 * Assign existing pages to their section taxonomy terms.
 *
 * This script traverses all pages and assigns them to the appropriate
 * section taxonomy term based on their parent hierarchy.
 *
 * Usage:
 *   wp eval-file scripts/assign-page-sections.php dry-run
 *   wp eval-file scripts/assign-page-sections.php
 */

if (! defined('ABSPATH') || ! class_exists('WP_CLI')) {
    exit(1);
}

$dry_run = in_array('dry-run', $GLOBALS['argv'] ?? [], true);

WP_CLI::log('Page Section Assignment Script');
WP_CLI::log($dry_run ? '(DRY RUN - no changes will be made)' : '');
WP_CLI::log(str_repeat('=', 60));

// Ensure the taxonomy and terms exist
if (! taxonomy_exists('page_section')) {
    WP_CLI::error('page_section taxonomy does not exist. Make sure the theme is active.');
}

// Get section root pages
$section_roots = [
    'about-us' => get_page_by_path('about-us'),
    'what-we-offer' => get_page_by_path('what-we-offer'),
    'healthcare-professionals' => get_page_by_path('healthcare-professionals'),
    'service-users-and-visitors' => get_page_by_path('service-users-and-visitors'),
    'your-portal' => get_page_by_path('your-portal'),
    'contact-us' => get_page_by_path('contact-us'),
    'news-and-events' => get_page_by_path('news-and-events'),
];

// Ensure terms exist
$sections = [
    'about-us' => 'About Us',
    'what-we-offer' => 'What We Offer',
    'healthcare-professionals' => 'Healthcare Professionals',
    'service-users-and-visitors' => 'Service Users and Visitors',
    'your-portal' => 'Your Portal',
    'contact-us' => 'Contact Us',
    'news-and-events' => 'News & Events',
];

WP_CLI::log("\nEnsuring section terms exist...");

foreach ($sections as $slug => $name) {
    $term = term_exists($slug, 'page_section');

    if (! $term) {
        if ($dry_run) {
            WP_CLI::log("Would create term: {$name} ({$slug})");
        } else {
            $result = wp_insert_term($name, 'page_section', ['slug' => $slug]);

            if (is_wp_error($result)) {
                WP_CLI::warning("Failed to create term {$slug}: " . $result->get_error_message());
            } else {
                WP_CLI::success("Created term: {$name} ({$slug})");
            }
        }
    } else {
        WP_CLI::log("Term exists: {$name} ({$slug})");
    }
}

// Build a lookup of root page IDs to section slugs
$root_id_to_section = [];

foreach ($section_roots as $slug => $page) {
    if ($page instanceof WP_Post) {
        $root_id_to_section[$page->ID] = $slug;
        WP_CLI::log("Section root: {$slug} (ID: {$page->ID})");
    } else {
        WP_CLI::warning("Section root page not found: {$slug}");
    }
}

// Get all pages
$all_pages = get_pages([
    'post_status' => ['publish', 'draft', 'private', 'pending'],
    'sort_column' => 'menu_order,post_title',
    'sort_order' => 'ASC',
]);

WP_CLI::log("\nFound " . count($all_pages) . " pages to process.\n");

$stats = [
    'assigned' => 0,
    'cleared' => 0,
    'skipped' => 0,
    'errors' => 0,
];

$section_counts = [];

foreach ($all_pages as $page) {
    $page_id = (int) $page->ID;
    $page_title = $page->post_title;

    // Determine section
    $section_slug = null;

    // Check if this page is itself a section root
    if (isset($root_id_to_section[$page_id])) {
        $section_slug = $root_id_to_section[$page_id];
    } else {
        // Check ancestors
        $ancestors = get_post_ancestors($page_id);

        if (! empty($ancestors)) {
            // Find the top-level ancestor (last in the array)
            $root_id = end($ancestors);

            if (isset($root_id_to_section[$root_id])) {
                $section_slug = $root_id_to_section[$root_id];
            }
        }
    }

    // Get current terms
    $current_terms = wp_get_object_terms($page_id, 'page_section', ['fields' => 'slugs']);
    $current_slug = is_array($current_terms) && ! is_wp_error($current_terms) && ! empty($current_terms)
        ? $current_terms[0]
        : null;

    if ($section_slug === null) {
        // Page doesn't belong to any section
        if ($current_slug !== null) {
            if ($dry_run) {
                WP_CLI::log("Would clear section from: {$page_title} (ID: {$page_id})");
            } else {
                wp_set_object_terms($page_id, [], 'page_section');
                WP_CLI::log("Cleared section from: {$page_title} (ID: {$page_id})");
            }
            $stats['cleared']++;
        } else {
            $stats['skipped']++;
        }
        continue;
    }

    // Check if already correctly assigned
    if ($current_slug === $section_slug) {
        $stats['skipped']++;
        continue;
    }

    // Assign to section
    if ($dry_run) {
        WP_CLI::log("Would assign: {$page_title} (ID: {$page_id}) → {$section_slug}");
    } else {
        $result = wp_set_object_terms($page_id, $section_slug, 'page_section');

        if (is_wp_error($result)) {
            WP_CLI::warning("Failed to assign {$page_title}: " . $result->get_error_message());
            $stats['errors']++;
            continue;
        }

        WP_CLI::log("Assigned: {$page_title} (ID: {$page_id}) → {$section_slug}");
    }

    $stats['assigned']++;
    $section_counts[$section_slug] = ($section_counts[$section_slug] ?? 0) + 1;
}

// Summary
WP_CLI::log("\n" . str_repeat('=', 60));
WP_CLI::log('SUMMARY' . ($dry_run ? ' (DRY RUN)' : ''));
WP_CLI::log(str_repeat('=', 60));
WP_CLI::log("Assigned: {$stats['assigned']}");
WP_CLI::log("Cleared: {$stats['cleared']}");
WP_CLI::log("Skipped (already correct): {$stats['skipped']}");
WP_CLI::log("Errors: {$stats['errors']}");

if (! empty($section_counts)) {
    WP_CLI::log("\nPages per section:");

    foreach ($section_counts as $slug => $count) {
        WP_CLI::log("  {$slug}: {$count}");
    }
}

if ($dry_run && $stats['assigned'] > 0) {
    WP_CLI::log("\nRun without 'dry-run' to apply changes.");
}

WP_CLI::success('Done.');
