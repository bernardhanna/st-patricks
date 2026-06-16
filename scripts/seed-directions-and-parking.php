<?php

/**
 * Seed Directions and Parking (page 233) with travel and parking content
 * sourced from stpatricks.ie location pages. Preserves the existing 3-block
 * layout and only updates copy in the hero and directions accordion.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-directions-and-parking.php
 */

require_once __DIR__ . '/lib/page-seed-conventions.php';

$post_id = (int) (get_page_by_path('directions-and-parking')?->ID ?? 0);

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at directions-and-parking.');
    }

    exit(1);
}

if (! function_exists('matrix_seed_directions_row')) {
    function matrix_seed_directions_row(string $icon_key, string $content): array
    {
        return [
            'row_type' => 'text',
            'icon_key' => $icon_key,
            'icon' => false,
            'content' => $content,
        ];
    }
}

if (! function_exists('matrix_seed_directions_map_link')) {
    function matrix_seed_directions_map_link(string $label, string $url): string
    {
        return ' <a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . esc_html($label) . '</a>';
    }
}

$spuh_map = 'https://maps.app.goo.gl/VBsyyXzA1aD2YnoH8';
$lucan_map = 'https://maps.app.goo.gl/pBRGjtom1tRSbfNV9';

$spuh_bus = '<p><strong>Dublin Bus:</strong> Take the G1, G2, 13 or 123 to James\' Street or the C1, C2, C3, C4, G1, 26, 52, or 145 to Heuston Station. From either of these locations, our Dublin 8 campus is a five to 10 minute walk.</p>';

$spuh_rail = '<p><strong>Rail or Luas:</strong> Heuston Station is less than a five minute walk from St Patrick\'s University Hospital. You can reach Heuston Station through a number of <a href="https://www.irishrail.ie/en-ie/station/dublin-heuston" target="_blank" rel="noopener noreferrer">Irish Rail routes</a>, or on the red line Luas, which runs every five minutes from Dublin city centre. The Luas journey is approximately five to 10 minutes from the city centre; you need to get off at the Heuston Station stop.</p>';

$accordion_items = [
    [
        'title' => 'Getting to St Patrick\'s University Hospital',
        'starts_open' => 1,
        'content_rows' => [
            matrix_seed_directions_row(
                'car',
                '<p><strong>Car park:</strong> There is a paid car park available at the campus entrance on Steeven\'s Lane.</p>'
            ),
            matrix_seed_directions_row(
                'map_pin',
                '<p><strong>Address:</strong> St Patrick\'s University Hospital, James\' Street, Dublin 8, D08 K7YW, Ireland.'
                . matrix_seed_directions_map_link('Get directions', $spuh_map)
                . '</p>'
            ),
            matrix_seed_directions_row(
                'clock',
                '<p><strong>Visiting times:</strong> 2pm to 5pm and 6pm to 8.30pm everyday</p>'
            ),
            matrix_seed_directions_row('bus', $spuh_bus),
            matrix_seed_directions_row('train', $spuh_rail),
        ],
    ],
    [
        'title' => 'Getting to Willow Grove Adolescent Unit',
        'starts_open' => 0,
        'content_rows' => [
            matrix_seed_directions_row(
                'map_pin',
                '<p><strong>Address:</strong> Willow Grove Adolescent Unit is on the St Patrick\'s University Hospital campus at James\' Street, Dublin 8, D08 K7YW, Ireland.'
                . matrix_seed_directions_map_link('Get directions', $spuh_map)
                . '</p>'
            ),
            matrix_seed_directions_row(
                'car',
                '<p><strong>Car park:</strong> There is a paid car park available at the campus entrance on Steeven\'s Lane.</p>'
            ),
            matrix_seed_directions_row(
                'clock',
                '<p><strong>Visiting times:</strong> Contact Willow Grove for visiting information.</p>'
            ),
            matrix_seed_directions_row('bus', $spuh_bus),
            matrix_seed_directions_row('train', $spuh_rail),
        ],
    ],
    [
        'title' => 'Getting to St Patrick\'s Hospital Lucan',
        'starts_open' => 0,
        'content_rows' => [
            matrix_seed_directions_row(
                'map_pin',
                '<p><strong>Address:</strong> St Patrick\'s Hospital Lucan (St Edmundsbury), Old Lucan Road, Lucan, County Dublin, Ireland.'
                . matrix_seed_directions_map_link('Get directions', $lucan_map)
                . '</p>'
            ),
            matrix_seed_directions_row(
                'car',
                '<p><strong>Car park:</strong> Limited free car parking is available in the hospital grounds.</p>'
            ),
            matrix_seed_directions_row(
                'clock',
                '<p><strong>Visiting times:</strong> 2pm to 5pm and 6pm to 8.30pm everyday</p>'
            ),
            matrix_seed_directions_row(
                'bus',
                '<p><strong>Dublin Bus:</strong> Routes C3 and C4 travel between Maynooth and Dublin city centre and stop close to the hospital grounds. Local routes L54 and P29 also stop nearby. It takes a five to 10 minute walk to reach the hospital from the bus stops.</p>'
            ),
        ],
    ],
    [
        'title' => 'Getting to Dean Clinic St Patrick\'s',
        'starts_open' => 0,
        'content_rows' => [
            matrix_seed_directions_row(
                'map_pin',
                '<p><strong>Address:</strong> The Dean Clinic St Patrick\'s is on the St Patrick\'s University Hospital campus at Steeven\'s Lane, James\' Street, Dublin 8.'
                . matrix_seed_directions_map_link('Get directions', $spuh_map)
                . ' When you arrive, the clinic is the green building at the back of the car park, facing the main entrance gates.</p>'
            ),
            matrix_seed_directions_row(
                'car',
                '<p><strong>Car park:</strong> There is a paid car park available at the SPUH campus.</p>'
            ),
            matrix_seed_directions_row('bus', $spuh_bus),
            matrix_seed_directions_row('train', $spuh_rail),
        ],
    ],
    [
        'title' => 'Getting to Dean Clinic Cork',
        'starts_open' => 0,
        'content_rows' => [
            matrix_seed_directions_row(
                'map_pin',
                '<p><strong>Address:</strong> Dean Clinic Cork, Building 2000, City Gate, Mahon, County Cork, Ireland.</p>'
            ),
            matrix_seed_directions_row(
                'bus',
                '<p><strong>Bus:</strong> The 215 bus from Cork city centre stops within walking distance of City Gate, Mahon.</p>'
            ),
            matrix_seed_directions_row(
                'car',
                '<p><strong>Car park:</strong> Very limited free parking is available with a maximum stay of three hours; clamping is in operation. There is no parking for the clinic in the nearby underground car park.</p>'
            ),
        ],
    ],
    [
        'title' => 'Getting to Dean Clinic Galway',
        'starts_open' => 0,
        'content_rows' => [
            matrix_seed_directions_row(
                'map_pin',
                '<p><strong>Address:</strong> Dean Clinic Galway, Merchant\'s Square, Merchant\'s Road, Galway City, Ireland.</p>'
            ),
            matrix_seed_directions_row(
                'car',
                '<p><strong>Car park:</strong> There is a paid car park available near the clinic.</p>'
            ),
            matrix_seed_directions_row(
                'bus',
                '<p><strong>Bus:</strong> Several bus routes pass close to the clinic; all routes to Eyre Square are within a 15 minute walk.</p>'
            ),
            matrix_seed_directions_row(
                'train',
                '<p><strong>Rail:</strong> Galway train station is approximately a 10 minute walk from the clinic.</p>'
            ),
        ],
    ],
    [
        'title' => 'Getting to Dean Clinic Lucan',
        'starts_open' => 0,
        'content_rows' => [
            matrix_seed_directions_row(
                'map_pin',
                '<p><strong>Address:</strong> The Dean Clinic Lucan is based at the second entrance to St Patrick\'s Hospital Lucan.'
                . matrix_seed_directions_map_link('Get directions', $lucan_map)
                . '</p>'
            ),
            matrix_seed_directions_row(
                'car',
                '<p><strong>Car park:</strong> There is a car park available at the clinic.</p>'
            ),
            matrix_seed_directions_row(
                'bus',
                '<p><strong>Dublin Bus:</strong> Routes C3 and C4 travel between Dublin city centre and Maynooth, and local routes L54 and P29 pass nearby. The bus stops are a short walk from the clinic.</p>'
            ),
        ],
    ],
];

$hero_intro = 'Find directions, parking and public transport information for our hospitals and Dean Clinics across Ireland.';

$rows = get_field('flexible_content_blocks', $post_id);

if (! is_array($rows) || $rows === []) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Directions and Parking page has no flexible content blocks to update.');
    }

    exit(1);
}

foreach ($rows as &$row) {
    $layout = $row['acf_fc_layout'] ?? '';

    if ($layout === 'hero_with_breadcrumbs') {
        $row['content'] = '<p>' . esc_html($hero_intro) . '</p>';
    }

    if ($layout === 'content_accordion' && ($row['layout_style'] ?? '') === 'directions_page') {
        $row['items'] = $accordion_items;
    }
}
unset($row);

update_field('hero_content_blocks', [], $post_id);
update_field('flexible_content_blocks', $rows, $post_id);
update_post_meta($post_id, '_matrix_seed_key', 'directions-and-parking-content');

if (class_exists('WP_CLI')) {
    WP_CLI::success(sprintf(
        'Updated Directions and Parking page (%d) with travel content for %d locations.',
        $post_id,
        count($accordion_items)
    ));
}
