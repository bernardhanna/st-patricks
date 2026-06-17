<?php

/**
 * Seed What We Offer, Healthcare Professionals, and Service Users mega menus (Figma 489:1673, 489:1626, 489:1509).
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-primary-mega-menus.php
 */

$menu_id = (int) (get_nav_menu_locations()['primary'] ?? 0);

if ($menu_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Primary menu location is not assigned.');
    }

    exit(1);
}

$home = home_url('/');

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

if (! function_exists('matrix_seed_find_top_level_menu_item')) {
    function matrix_seed_find_top_level_menu_item(int $menu_id, string $label): int
    {
        foreach (wp_get_nav_menu_items($menu_id) ?: [] as $item) {
            if ((int) $item->menu_item_parent !== 0) {
                continue;
            }

            if (strcasecmp((string) $item->title, $label) === 0) {
                return (int) $item->ID;
            }
        }

        return 0;
    }
}

if (! function_exists('matrix_seed_menu_children')) {
    /**
     * @param array<int, array{0: string, 1: string}> $links
     */
    function matrix_seed_menu_children(int $menu_id, int $parent_id, array $links): void
    {
        $position = 1;

        foreach ($links as $link) {
            matrix_seed_create_menu_item($menu_id, $parent_id, $link[0], $link[1], $position);
            $position++;
        }
    }
}

$menus = [
    'What We Offer' => [
        ['Inpatient Care', $home . 'inpatient-care/'],
        ['Outpatient Care - Dean Clinics', $home . 'what-we-offer/outpatient-care-dean-clinics/'],
        ['St Patrick\'s at Home', $home . 'what-we-offer/st-patricks-at-home/'],
        ['Day Programmes', $home . 'what-we-offer/day-programmes/'],
    ],
    'Healthcare Professionals' => [
        ['Refer an Adult for Inpatient Care', $home . 'healthcare-professionals/refer-an-adult-for-inpatient-care/'],
        ['Refer an Adolescent for Inpatient Care', $home . 'healthcare-professionals/refer-an-adolescent-for-inpatient-care/'],
        ['Refer to the St Patrick\'s at Home Service', $home . 'healthcare-professionals/refer-to-the-st-patricks-at-home-service/'],
        ['Refer to Outpatient Care', $home . 'healthcare-professionals/refer-for-outpatient-care/'],
        ['Refer to a Day Programme', $home . 'healthcare-professionals/refer-to-a-day-programme/'],
        ['Clinical Insights', $home . 'healthcare-professionals/clinician-insights/'],
        ['(FAQ\'s)', $home . 'healthcare-professionals/frequently-asked-questions/'],
        ['Training Centre', $home . 'healthcare-professionals/training-centre/'],
        ['Contact Numbers', $home . 'healthcare-professionals/contact-numbers/'],
        ['Webinars & Events', $home . 'healthcare-professionals/webinars-events/'],
    ],
    'Service Users and Visitors' => [
        ['Directions and Parking', $home . 'directions-and-parking/'],
        ['Your Stay in Hospital as an Adult', $home . 'service-users-and-visitors/your-stay-in-hospital-as-an-adult/'],
        ['Your Stay in Hospital as an Adolescent', $home . 'service-users-and-visitors/your-stay-in-hospital-as-an-adolescent/'],
        ['About our St Patrick\'s at Home Service', $home . 'service-users-and-visitors/about-our-st-patricks-at-home-service/'],
        ['Attending our Day Programmes', $home . 'service-users-and-visitors/attending-our-day-programmes/'],
        ['Attending a Dean Clinic', $home . 'service-users-and-visitors/attending-a-dean-clinic/'],
        ['Make a payment', $home . 'service-users-and-visitors/make-a-payment-external-link-to-stripe/'],
        ['About Your Portal', $home . 'about-your-portal/'],
        ['Service User IT Support', $home . 'service-user-it-support/'],
        ['Service User Participation', $home . 'service-users-and-visitors/service-user-participation/'],
        ['Stories and Support', $home . 'service-users-and-visitors/stories-and-support/'],
        ['About Mental Health', $home . 'service-users-and-visitors/about-mental-health/'],
        ['Medication', $home . 'service-users-and-visitors/medication/'],
        ['Feedback and Comments', $home . 'service-users-and-visitors/feedback-and-comments/'],
        ['Frequently Asked Questions (FAQ\'s)', $home . 'service-users-and-visitors/frequently-asked-questions-faqs/'],
    ],
];

foreach ($menus as $label => $links) {
    $parent_id = matrix_seed_find_top_level_menu_item($menu_id, $label);

    if ($parent_id === 0) {
        if (class_exists('WP_CLI')) {
            WP_CLI::warning('Skipping "' . $label . '": top-level menu item not found.');
        }

        continue;
    }

    matrix_seed_delete_menu_branch($menu_id, $parent_id);
    matrix_seed_menu_children($menu_id, $parent_id, $links);

    if (class_exists('WP_CLI')) {
        WP_CLI::log('Seeded ' . count($links) . ' links under "' . $label . '".');
    }
}

if (class_exists('WP_CLI')) {
    WP_CLI::success('Primary mega menus seeded for menu ' . $menu_id . '.');
}

echo "Primary mega menus seeded.\n";
