<?php

/**
 * Seed the primary nav About Us mega menu to match Figma 489:1561.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-about-us-mega-menu.php
 */

$menu_id = (int) (get_nav_menu_locations()['primary'] ?? 0);

if ($menu_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Primary menu location is not assigned.');
    }

    exit(1);
}

$home = home_url('/');

$about_us_item_id = 0;

foreach (wp_get_nav_menu_items($menu_id) ?: [] as $item) {
    if ((int) $item->menu_item_parent === 0 && stripos($item->title, 'About Us') !== false) {
        $about_us_item_id = (int) $item->ID;
        break;
    }
}

if ($about_us_item_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find top-level About Us menu item.');
    }

    exit(1);
}

if (! function_exists('matrix_seed_delete_menu_branch')) {
    function matrix_seed_delete_menu_branch(int $menu_id, int $parent_id): void
    {
        foreach (wp_get_nav_menu_items($menu_id) ?: [] as $item) {
            if ((int) $item->menu_item_parent !== $parent_id) {
                continue;
            }

            matrix_seed_delete_menu_branch($menu_id, (int) $item->ID);
            wp_delete_post((int) $item->ID, true);
        }
    }
}

matrix_seed_delete_menu_branch($menu_id, $about_us_item_id);

if (! function_exists('matrix_seed_create_menu_item')) {
    function matrix_seed_create_menu_item(int $menu_id, int $parent_id, string $title, string $url, int $position = 0): int
    {
        $item_id = wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title' => $title,
            'menu-item-url' => $url,
            'menu-item-status' => 'publish',
            'menu-item-type' => 'custom',
            'menu-item-parent-id' => $parent_id,
            'menu-item-position' => $position,
        ]);

        return is_wp_error($item_id) ? 0 : (int) $item_id;
    }
}

$top_level = [
    ['Overview', $home . 'about-us/overview/'],
    ['Our Teams', $home . 'about-us/our-team/'],
    ['Policies and Publications', $home . 'about-us/policies-and-publications/'],
    ['Careers', $home . 'careers/', [
        ['Recruitment and useful information', $home . 'recruitment-and-useful-information/'],
        ['Attending an interview', $home . 'attending-an-interview/'],
        ['Staff Wellbeing', $home . 'recruitment-and-useful-information/staff-wellbeing/'],
        ['How to get work experience', $home . 'recruitment-and-useful-information/how-to-get-work-experience/'],
        ['How to apply for a role', $home . 'recruitment-and-useful-information/how-to-apply-for-a-role/'],
    ]],
    ['Research', $home . 'about-us/research/', [
        ['Current research projects', $home . 'current-research-projects/'],
        ['Past research projects', $home . 'past-research-projects/'],
        ['Research library / SPIRE', $home . 'research-library-spire/'],
        ['Research Ethics Committee', $home . 'research-ethics-committee/'],
    ]],
    ['Advocacy', $home . 'about-us/advocacy/'],
    ['Support us', $home . 'about-us/support-us/'],
    ['Our Locations', $home . 'about-us/our-locations/'],
    ['Media Queries', $home . 'about-us/media-queries/'],
    ['Our History', $home . 'about-us/our-history/'],
    ['Our Present and Future', $home . 'about-us/our-present-and-future/', [
        ['New hospital', $home . 'new-hospital/'],
        ['Extending and enhancing our services', $home . 'extending-and-enhancing-our-services/'],
        ['National centre', $home . 'national-centre/'],
    ]],
];

$position = 1;

foreach ($top_level as $entry) {
    $title = $entry[0];
    $url = $entry[1];
    $children = $entry[2] ?? [];

    $parent_item_id = matrix_seed_create_menu_item($menu_id, $about_us_item_id, $title, $url, $position);
    $position++;

    if ($parent_item_id === 0 || $children === []) {
        continue;
    }

    $child_position = 1;

    foreach ($children as $child) {
        matrix_seed_create_menu_item($menu_id, $parent_item_id, $child[0], $child[1], $child_position);
        $child_position++;
    }
}

if (class_exists('WP_CLI')) {
    WP_CLI::success('Seeded About Us mega menu for menu ' . $menu_id . '.');
}

echo "About Us mega menu seeded.\n";
