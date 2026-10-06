<?php

/**
 * Finish Extra pages sitemap gaps + nest them under Service Users mega menu.
 *
 * - Guides to Your Portal (under Your Portal)
 * - Accessing remote services (under Service User IT Support)
 * - Appointment notifications post: publish + Aug 2025 date
 * - Service Users mega menu: nested children for Extra sitemap pages + fix broken URLs
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/fix-extra-pages-and-service-users-menu.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

if (! function_exists('matrix_orlaith_ensure_page')) {
    function matrix_orlaith_ensure_page(
        string $title,
        string $slug,
        int $parent_id,
        string $post_type = 'page',
        string $status = 'draft'
    ): int {
        if ($post_type === 'page' && $parent_id > 0) {
            $parent = get_post($parent_id);
            $path = ($parent instanceof WP_Post ? get_page_uri($parent) . '/' : '') . $slug;
            $existing = get_page_by_path($path);
            if ($existing instanceof WP_Post && $existing->post_type === 'page') {
                wp_update_post([
                    'ID' => $existing->ID,
                    'post_title' => $title,
                    'post_parent' => $parent_id,
                    'post_status' => $status,
                ]);

                return (int) $existing->ID;
            }
        }

        $found = get_posts([
            'post_type' => $post_type,
            'name' => $slug,
            'post_status' => ['publish', 'draft', 'pending', 'private'],
            'posts_per_page' => 1,
            'post_parent' => $post_type === 'page' ? $parent_id : 0,
        ]);
        if ($found !== []) {
            wp_update_post([
                'ID' => $found[0]->ID,
                'post_title' => $title,
                'post_status' => $status,
            ]);

            return (int) $found[0]->ID;
        }

        $id = wp_insert_post([
            'post_type' => $post_type,
            'post_status' => $status,
            'post_title' => $title,
            'post_name' => $slug,
            'post_parent' => $post_type === 'page' ? $parent_id : 0,
            'post_content' => '',
        ], true);

        return is_wp_error($id) ? 0 : (int) $id;
    }
}

$home = trailingslashit(home_url('/'));

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
    function matrix_seed_create_menu_item(
        int $menu_id,
        int $parent_id,
        string $title,
        string $url,
        int $position = 0,
        string $target = ''
    ): int {
        $item_id = wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title' => $title,
            'menu-item-url' => $url,
            'menu-item-status' => 'publish',
            'menu-item-type' => 'custom',
            'menu-item-parent-id' => $parent_id,
            'menu-item-position' => $position,
            'menu-item-target' => $target,
        ]);

        return is_wp_error($item_id) ? 0 : (int) $item_id;
    }
}

// ---------------------------------------------------------------------------
// 1) Guides to Your Portal
// ---------------------------------------------------------------------------
$portal_parent = get_page_by_path('your-portal');
if (! $portal_parent instanceof WP_Post) {
    WP_CLI::error('Your Portal parent page missing');
}

$guides_id = matrix_orlaith_ensure_page(
    'Guides to Your Portal',
    'guides-to-your-portal',
    (int) $portal_parent->ID,
    'page',
    'publish'
);

$guide_slides = [
    ['url' => 'https://www.youtube.com/watch?v=TRkJNaf8MDs', 'title' => 'How to find appointments'],
    ['url' => 'https://www.youtube.com/watch?v=sGcwI81iiNA', 'title' => 'How to complete forms'],
    ['url' => 'https://www.youtube.com/watch?v=krG9n9xUJuo', 'title' => 'How to use the Library'],
    ['url' => 'https://www.youtube.com/watch?v=GCCl7eVSwPE', 'title' => 'What to expect as an inpatient'],
    ['url' => 'https://www.youtube.com/watch?v=C5pZRdPHVZg', 'title' => 'How to share access'],
];

$guides_hero = matrix_orlaith_find_image(4045, 'about-your-portal')
    ?: matrix_orlaith_find_image(3919, 'getting-started-with-your-portal')
    ?: matrix_orlaith_find_image(626, 'your-portal-video-guide');

$guides_rows = [
    matrix_orlaith_hero_row(
        'Guides to Your Portal',
        '<p>Short video guides to help you get the most from Your Portal — from finding appointments to sharing access.</p>',
        $guides_hero
    ),
    matrix_orlaith_video_row(
        'Video guides',
        '<p>Watch the guides below, or browse the full <a href="https://www.youtube.com/playlist?list=PL9Qr7kXsp_qQPLDXwqVr4BfO-j4_TBFfT" target="_blank" rel="noopener noreferrer">Your Portal playlist on YouTube</a>.</p>',
        $guide_slides
    ),
    matrix_orlaith_useful_links_row([
        'About Your Portal' => 'your-portal/about-your-portal',
        'Register for Your Portal' => 'your-portal/register-for-your-portal',
        'Service User IT Support' => 'service-users-and-visitors/service-user-it-support',
    ]),
];

delete_field('flexible_content_blocks', $guides_id);
update_field('flexible_content_blocks', $guides_rows, $guides_id);
matrix_orlaith_set_seo(
    $guides_id,
    'Guides to Your Portal | St Patrick\'s Mental Health Services',
    'Video guides for using Your Portal at St Patrick\'s Mental Health Services.'
);
WP_CLI::success('Guides to Your Portal #' . $guides_id . ' ' . get_permalink($guides_id));

// ---------------------------------------------------------------------------
// 2) Accessing remote services
// ---------------------------------------------------------------------------
$suits_parent = get_page_by_path('service-users-and-visitors/service-user-it-support');
if (! $suits_parent instanceof WP_Post) {
    WP_CLI::error('Service User IT Support parent missing');
}

$remote_id = matrix_orlaith_ensure_page(
    'Accessing remote services',
    'accessing-remote-services',
    (int) $suits_parent->ID,
    'page',
    'publish'
);

$remote_source = get_field('flexible_content_blocks', 3088);
$remote_rows = [];
if (is_array($remote_source) && $remote_source !== []) {
    foreach ($remote_source as $row) {
        if (! is_array($row)) {
            continue;
        }
        $layout = (string) ($row['acf_fc_layout'] ?? '');
        if ($layout === 'hero_with_breadcrumbs') {
            $row['heading'] = 'Accessing remote services';
            $row['current_crumb_label'] = 'Accessing remote services';
            $row['heading_tag'] = 'h1';
            $row['content'] = '<p>Remote access to mental health services through phone, video or online channels from your own home.</p>';
        }
        if ($layout === 'useful_links') {
            $row = matrix_orlaith_useful_links_row([
                'What you need to know about remote care' => 'what-you-need-to-know-about-remote-care',
                'Service User IT Support' => 'service-users-and-visitors/service-user-it-support',
                'Guides to Your Portal' => 'your-portal/guides-to-your-portal',
                'About St Patrick\'s at Home' => 'service-users-and-visitors/about-our-st-patricks-at-home-service',
            ]);
        }
        $remote_rows[] = $row;
    }
}

if ($remote_rows === []) {
    $remote_rows = [
        matrix_orlaith_hero_row(
            'Accessing remote services',
            '<p>Remote access to mental health services through phone, video or online channels from your own home.</p>',
            0
        ),
        matrix_orlaith_content_row(
            'What are our remote services?',
            '<p>Our Homecare service offers elements of our inpatient programmes remotely in your own home. Day programmes and Dean Clinic appointments can also be delivered by phone, video and online technologies.</p>'
        ),
        matrix_orlaith_useful_links_row([
            'What you need to know about remote care' => 'what-you-need-to-know-about-remote-care',
            'Service User IT Support' => 'service-users-and-visitors/service-user-it-support',
        ]),
    ];
}

delete_field('flexible_content_blocks', $remote_id);
update_field('flexible_content_blocks', $remote_rows, $remote_id);
matrix_orlaith_set_seo(
    $remote_id,
    'Accessing remote services | St Patrick\'s Mental Health Services',
    'How to access St Patrick\'s remote mental health services by phone, video or online.'
);
WP_CLI::success('Accessing remote services #' . $remote_id . ' ' . get_permalink($remote_id));

// Keep draft CPT out of the way; practical page stays published.
$redirect_map_path = get_template_directory() . '/inc/data/path-redirect-map.json';
$map = json_decode((string) file_get_contents($redirect_map_path), true);
if (! is_array($map)) {
    $map = [];
}
$guides_path = 'your-portal/guides-to-your-portal';
$remote_path = 'service-users-and-visitors/service-user-it-support/accessing-remote-services';
$map['care-treatment/your-portal/guides-to-your-portal'] = '/' . $guides_path . '/';
$map['care-treatment/remote-services'] = '/' . $remote_path . '/';
$map['care-treatment/our-services/remote-services'] = '/' . $remote_path . '/';
$map['care-treatment/our-services/remote-services/practical-information-remote-services'] = '/what-you-need-to-know-about-remote-care/';
$map['about-your-portal'] = '/your-portal/about-your-portal/';
$map['service-user-it-support'] = '/service-users-and-visitors/service-user-it-support/';
$map['service-users-and-visitors/your-stay-in-hospital-as-an-adolescent'] = '/service-users-and-visitors/your-care-with-willow-grove/your-stay-in-hospital-as-an-adolescent/';
file_put_contents($redirect_map_path, json_encode($map, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
WP_CLI::log('Updated path-redirect-map.json');

// ---------------------------------------------------------------------------
// 3) Appointment notifications article
// ---------------------------------------------------------------------------
$appt_id = 1255;
$appt = get_post($appt_id);
if ($appt instanceof WP_Post) {
    wp_update_post([
        'ID' => $appt_id,
        'post_status' => 'publish',
        'post_date' => '2025-08-15 09:00:00',
        'post_date_gmt' => get_gmt_from_date('2025-08-15 09:00:00'),
        'edit_date' => true,
    ]);
    WP_CLI::success('Published appointment notifications #' . $appt_id . ' dated 2025-08-15 → ' . get_permalink($appt_id));
} else {
    WP_CLI::warning('Appointment post #1255 missing');
}

// ---------------------------------------------------------------------------
// 4) Service Users mega menu (nested Extra pages)
// ---------------------------------------------------------------------------
$menu_id = (int) (get_nav_menu_locations()['primary'] ?? 0);
if ($menu_id === 0) {
    WP_CLI::error('Primary menu not assigned');
}

$su_parent = 0;
foreach (wp_get_nav_menu_items($menu_id) ?: [] as $item) {
    if ((int) $item->menu_item_parent === 0 && strcasecmp((string) $item->title, 'Service Users and Visitors') === 0) {
        $su_parent = (int) $item->ID;
        break;
    }
}
if ($su_parent === 0) {
    WP_CLI::error('Service Users and Visitors top-level menu item not found');
}

$guides_url = (string) get_permalink($guides_id);
$remote_url = (string) get_permalink($remote_id);
$stripe = 'https://buy.stripe.com/aFa4gy8Yide50e9erjbwk00';

$su_tree = [
    ['Directions and Parking', $home . 'directions-and-parking/'],
    ['Your Stay in Hospital as an Adult', $home . 'service-users-and-visitors/your-stay-in-hospital-as-an-adult/'],
    ['Your care with Willow Grove', $home . 'service-users-and-visitors/your-care-with-willow-grove/', [
        ['Your stay in hospital as an adolescent', $home . 'service-users-and-visitors/your-care-with-willow-grove/your-stay-in-hospital-as-an-adolescent/'],
        ['Your time in homecare as an adolescent', $home . 'service-users-and-visitors/your-care-with-willow-grove/your-time-in-homecare-as-an-adolescent/'],
        ['Information for your family', $home . 'service-users-and-visitors/your-care-with-willow-grove/information-for-your-family/'],
    ]],
    ['About our St Patrick\'s at Home Service', $home . 'service-users-and-visitors/about-our-st-patricks-at-home-service/'],
    ['Attending our Day Programmes', $home . 'service-users-and-visitors/attending-our-day-programmes/'],
    ['Attending a Dean Clinic', $home . 'service-users-and-visitors/attending-a-dean-clinic/'],
    ['Make a payment', $stripe, [
        ['Understanding insurance plans', $home . 'service-users-and-visitors/understanding-insurance-plans/'],
        ['Payment for our services', $home . 'about-us/payment/'],
    ], '_blank'],
    ['About Your Portal', $home . 'your-portal/about-your-portal/', [
        ['Guides to Your Portal', $guides_url],
        ['Register for Your Portal', $home . 'your-portal/register-for-your-portal/'],
    ]],
    ['Service User IT Support', $home . 'service-users-and-visitors/service-user-it-support/', [
        ['Accessing remote services', $remote_url],
        ['What you need to know about remote care', $home . 'what-you-need-to-know-about-remote-care/'],
    ]],
    ['Service User Participation', $home . 'service-users-and-visitors/service-user-participation/', [
        ['Service User and Supporters Council', $home . 'service-users-and-visitors/service-user-participation/service-user-and-supporters-council/'],
        ['Service User Advisory Network', $home . 'service-users-and-visitors/service-user-participation/service-user-advisory-network/'],
        ['Family, Carers and Supporters Advisory Network', $home . 'service-users-and-visitors/service-user-participation/family-carers-and-supporters-advisory-network/'],
    ]],
    ['Carers and Supporters', $home . 'service-users-and-visitors/carers-and-supporters/'],
    ['Stories and Support', $home . 'service-users-and-visitors/stories-and-support/'],
    ['About Mental Health', $home . 'service-users-and-visitors/about-mental-health/', [
        ['Depression', $home . 'mental-health/depression/'],
        ['Schizophrenia', $home . 'mental-health/schizophrenia/'],
        ['Getting help and support', $home . 'getting-help/concerned-about-yourself-or-someone-you-know/'],
    ]],
    ['Medication', $home . 'service-users-and-visitors/medication/'],
    ['Feedback and Comments', $home . 'service-users-and-visitors/feedback-and-comments/', [
        ['Service User Experience Survey', $home . 'service-users-and-visitors/feedback-and-comments/service-user-experience-survey/'],
    ]],
    ['Frequently Asked Questions (FAQ\'s)', $home . 'service-users-and-visitors/frequently-asked-questions-faqs/'],
];

matrix_seed_delete_menu_branch($menu_id, $su_parent);

$position = 1;
foreach ($su_tree as $entry) {
    $title = $entry[0];
    $url = $entry[1];
    $children = [];
    $target = '';

    // Shapes: [title, url] | [title, url, children] | [title, url, children, target]
    if (isset($entry[2]) && is_array($entry[2])) {
        $children = $entry[2];
        $target = (string) ($entry[3] ?? '');
    } elseif (isset($entry[2]) && is_string($entry[2])) {
        $target = $entry[2];
    }

    $item_id = matrix_seed_create_menu_item($menu_id, $su_parent, $title, $url, $position, $target);
    $position++;

    if ($item_id === 0 || $children === []) {
        continue;
    }

    $child_pos = 1;
    foreach ($children as $child) {
        matrix_seed_create_menu_item(
            $menu_id,
            $item_id,
            $child[0],
            $child[1],
            $child_pos,
            (string) ($child[2] ?? '')
        );
        $child_pos++;
    }
}

WP_CLI::success('Service Users mega menu updated with Extra pages children');

echo "Done.\n";
echo 'guides=' . $guides_url . "\n";
echo 'remote=' . $remote_url . "\n";
echo 'appt=' . get_permalink($appt_id) . "\n";
