<?php

/**
 * Seed the Contact Us page (Figma Contact us - OPTION 2, node 3279:17785).
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-contact-us.php
 */

require_once __DIR__ . '/lib/page-seed-conventions.php';

if (! function_exists('matrix_seed_ensure_page')) {
    function matrix_seed_ensure_page(string $path, string $title, int $parent_id = 0, string $old_path = ''): int
    {
        $page_id = matrix_seed_resolve_page_id_by_path($path);

        if ($page_id > 0) {
            wp_update_post([
                'ID' => $page_id,
                'post_title' => $title,
                'post_parent' => $parent_id,
                'post_status' => 'publish',
            ]);
        } else {
            $segments = array_values(array_filter(explode('/', trim($path, '/'))));
            $slug = (string) array_pop($segments);
            $inserted = wp_insert_post([
                'post_type' => 'page',
                'post_status' => 'publish',
                'post_parent' => $parent_id,
                'post_name' => $slug,
                'post_title' => $title,
            ], true);

            if (is_wp_error($inserted)) {
                return 0;
            }

            $page_id = (int) $inserted;
        }

        if ($old_path !== '') {
            update_post_meta($page_id, '_matrix_migrate_old_path', trim($old_path, '/'));
        }

        return $page_id;
    }
}

if (! function_exists('matrix_seed_resolve_location_post_id')) {
    function matrix_seed_resolve_location_post_id(string $slug): int
    {
        $post = get_page_by_path($slug, OBJECT, 'locations');

        return $post instanceof WP_Post ? (int) $post->ID : 0;
    }
}

if (! function_exists('matrix_seed_contact_directory_manual_item')) {
    /**
     * @param array<int, string> $bullet_items
     * @param array<int, array{day_label: string, hours: string}> $opening_hours
     * @return array<string, mixed>
     */
    function matrix_seed_contact_directory_manual_item(
        string $title,
        string $phone = '',
        string $email = '',
        array $bullet_items = [],
        array $opening_hours = [],
        bool $starts_open = false
    ): array {
        $item = [
            'item_source' => 'manual',
            'title' => $title,
            'starts_open' => $starts_open ? 1 : 0,
            'bullet_items' => [],
            'phone' => $phone,
            'email' => $email,
            'opening_hours' => [],
        ];

        foreach ($bullet_items as $label) {
            $item['bullet_items'][] = ['label' => $label];
        }

        foreach ($opening_hours as $row) {
            if (! is_array($row)) {
                continue;
            }

            $item['opening_hours'][] = [
                'day_label' => (string) ($row['day_label'] ?? ''),
                'hours' => (string) ($row['hours'] ?? ''),
            ];
        }

        return $item;
    }
}

if (! function_exists('matrix_seed_contact_directory_location_item')) {
    /**
     * @return array<string, mixed>
     */
    function matrix_seed_contact_directory_location_item(
        string $location_slug,
        string $title_override = '',
        bool $starts_open = false
    ): array {
        return [
            'item_source' => 'location',
            'location' => matrix_seed_resolve_location_post_id($location_slug),
            'title' => $title_override,
            'starts_open' => $starts_open ? 1 : 0,
            'bullet_items' => [],
            'phone' => '',
            'email' => '',
            'opening_hours' => [],
        ];
    }
}

$home = home_url('/');
$faqs_url = home_url('/service-users-and-visitors/frequently-asked-questions-faqs/');
$our_locations_url = home_url('/about-us/our-locations/');
$directions_url = home_url('/directions-and-parking/');

$page_id = matrix_seed_ensure_page('contact-us', 'Contact us', 0, 'contact');

if ($page_id <= 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not create or resolve the contact-us page.');
    }

    exit(1);
}

$hero_content = sprintf(
    '<p>Below, you can find some of the key contacts and visiting information for our campuses and services here in St Patrick\'s Mental Health Services.</p>'
    . '<p>If you have queries about our services or mental health supports, you may find it helpful to <a href="%1$s">visit our Frequently Asked Questions page</a>.</p>'
    . '<p>If you wish to refer a patient to us, or if you have a query about a referral made for you, please contact our Referral and Assessment Service by calling <a href="tel:012493635">01 249 3635</a>.</p>',
    esc_url($faqs_url)
);

$default_opening_hours = [
    ['day_label' => 'Mon - Fri', 'hours' => '09:00 - 20:00'],
    ['day_label' => 'Sat - Sun', 'hours' => '10:00 - 17:00'],
    ['day_label' => 'Bank Holidays', 'hours' => '10:00 - 16:00'],
];

$contact_column_1 = [
    matrix_seed_contact_directory_manual_item(
        'General Enquiries',
        '01 249 3200',
        'hello@stpatricks.ie',
        ['Inpatient care', 'Admissions', 'Pharmacy'],
        $default_opening_hours,
        true
    ),
    matrix_seed_contact_directory_manual_item(
        'Clinical Governance Office (Complaints and feedback)',
        '',
        'clinicalgovernance@stpatricks.ie'
    ),
    matrix_seed_contact_directory_location_item('st-patricks-university-hospital', "St Patrick's University Hospital (Dublin 8)"),
    matrix_seed_contact_directory_location_item('dean-clinic-cork', 'Dean Clinic Cork'),
    matrix_seed_contact_directory_location_item('dean-clinic-st-patricks', "Dean Clinic St Patrick's"),
    matrix_seed_contact_directory_manual_item('Pharmacy', '01 249 3256', 'pharmacy@stpatricks.ie'),
    matrix_seed_contact_directory_location_item('willow-grove-adolescent-unit', 'Willow Grove Adolescent Unit'),
];

$contact_column_2 = [
    matrix_seed_contact_directory_manual_item('Referral and Assessment Service', '01 249 3635'),
    matrix_seed_contact_directory_manual_item('Human Resources', '', 'hr@stpatricks.ie'),
    matrix_seed_contact_directory_location_item('st-patricks-hospital-lucan', "St Patrick's, Lucan"),
    matrix_seed_contact_directory_location_item('dean-clinic-galway', 'Dean Clinic Galway'),
    matrix_seed_contact_directory_location_item('adolescent-dean-clinic', 'Adolescent Dean Clinic'),
    matrix_seed_contact_directory_manual_item('Media queries', '01 249 3540', 'communications@stpatricks.ie'),
    matrix_seed_contact_directory_location_item('dean-clinic-lucan', 'Dean Clinic Lucan'),
];

$flexi_rows = matrix_page_seed_strip_padding_from_rows([
    [
        'acf_fc_layout' => 'hero_with_breadcrumbs',
        'layout_style' => 'breadcrumbs_only',
        'show_breadcrumbs' => 1,
        'breadcrumb_source' => 'manual',
        'manual_breadcrumbs' => [
            [
                'breadcrumb_link' => [
                    'title' => 'Home',
                    'url' => $home,
                    'target' => '',
                ],
            ],
        ],
        'current_crumb_label' => 'Contact us',
        'breadcrumb_background_color' => '#F1F8F9',
    ],
    [
        'acf_fc_layout' => 'contact_directory',
        'heading' => 'Contact us',
        'heading_tag' => matrix_page_seed_heading(1),
        'intro_text' => $hero_content,
        'auto_location_mode' => 'none',
        'columns' => [
            ['items' => $contact_column_1],
            ['items' => $contact_column_2],
        ],
        'section_background' => '#FFFFFF',
        'closed_panel_background' => '#FBFAF7',
        'open_panel_background' => 'linear-gradient(-79.46deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
    ],
    [
        'acf_fc_layout' => 'locations_map',
        'heading' => 'Find us',
        'heading_tag' => matrix_page_seed_heading(2),
        'intro_text' => '<p>Use the map below to find our campuses and clinics across Ireland. Select a location to view contact details and opening hours.</p>',
        'source_mode' => 'all',
        'directions_link' => [
            'title' => 'Directions and Parking',
            'url' => $directions_url,
            'target' => '',
        ],
        'map_center_lat' => 53.42,
        'map_center_lng' => -7.69,
        'map_zoom' => 7,
        'tile_provider' => 'jawg-lagoon',
        'tile_api_key' => '',
        'background_color' => '#FFFFFF',
    ],
]);

update_field('hero_content_blocks', [], $page_id);
update_field('flexible_content_blocks', $flexi_rows, $page_id);

if (class_exists('WP_CLI')) {
    WP_CLI::success(sprintf('Seeded Contact Us page: %s', get_permalink($page_id)));
}
