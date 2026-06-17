<?php

require_once dirname(__DIR__, 2) . '/inc/locations-map-functions.php';

test('locations map normalizes opening hours rows', function () {
    $rows = matrix_normalize_location_opening_hours([
        ['day_label' => ' Mon - Fri ', 'hours' => ' 09:00 - 17:00 '],
        ['day_label' => '', 'hours' => ''],
        'invalid',
    ]);

    expect($rows)->toHaveCount(1)
        ->and($rows[0])->toMatchArray([
            'day_label' => 'Mon - Fri',
            'hours' => '09:00 - 17:00',
        ]);
});

test('locations map formats phone links without spaces', function () {
    expect(matrix_format_locations_map_phone_link('01 249 3200'))->toBe('012493200')
        ->and(matrix_format_locations_map_phone_link(''))->toBe('');
});

test('locations map drops invalid coordinates', function () {
    expect(matrix_get_location_coordinates(0))->toBeNull();
});

test('locations map section uses figma overlay layout classes', function () {
    expect(matrix_get_locations_map_section_wrapper_class_names())
        ->toContain('locations-map-section')
        ->and(matrix_get_locations_map_section_wrapper_class_names())->toContain('lg:pb-0')
        ->and(matrix_get_locations_map_wrapper_class_names())->toContain('max-w-[1018px]')
        ->and(matrix_get_locations_map_wrapper_class_names())->toContain('px-4')
        ->and(matrix_get_locations_map_header_wrapper_class_names())->toContain('pt-3.5')
        ->and(matrix_get_locations_map_header_wrapper_class_names())->toContain('pb-0')
        ->and(matrix_get_locations_map_header_wrapper_class_names())->toContain('lg:pt-16')
        ->and(matrix_get_locations_map_header_title_group_class_names())->toContain('gap-8')
        ->and(matrix_get_locations_map_directions_button_wrapper_class_names())->toContain('hidden')
        ->and(matrix_get_locations_map_directions_button_wrapper_class_names())->toContain('lg:block')
        ->and(matrix_get_locations_map_intro_wrapper_class_names())->toBe('hidden lg:block')
        ->and(matrix_get_locations_map_stage_class_names())->toContain('max-lg:bg-white')
        ->and(matrix_get_locations_map_stage_class_names())->toContain('lg:bg-[#66C2E0]')
        ->and(matrix_get_locations_map_map_class_names())->toContain('h-[402px]')
        ->and(matrix_get_locations_map_map_class_names())->toContain('lg:h-[48.8125rem]')
        ->and(matrix_get_locations_map_overlay_row_class_names())->toContain('max-lg:bg-[#CEF2EE]')
        ->and(matrix_get_locations_map_overlay_row_class_names())->toContain('max-lg:px-4')
        ->and(matrix_get_locations_map_overlay_row_class_names())->toContain('max-lg:pt-8')
        ->and(matrix_get_locations_map_overlay_row_class_names())->toContain('lg:h-[48.8125rem]')
        ->and(matrix_get_locations_map_overlay_row_class_names())->toContain('lg:w-[23.8125rem]')
        ->and(matrix_get_locations_map_overlay_row_class_names())->toContain('lg:justify-center')
        ->and(matrix_get_locations_map_panel_column_class_names())->not->toContain('max-w-[21.4375rem]')
        ->and(matrix_get_locations_map_mobile_directions_wrapper_class_names())->toContain('lg:hidden')
        ->and(matrix_get_locations_map_mobile_directions_wrapper_class_names())->toContain('mt-8')
        ->and(matrix_get_locations_map_panel_card_class_names())->toContain('h-[39.3125rem]')
        ->and(matrix_get_locations_map_panel_card_class_names())->toContain('lg:w-[23.8125rem]')
        ->and(matrix_get_locations_map_panel_scroll_class_names())->toContain('p-8')
        ->and(matrix_get_locations_map_panel_scroll_class_names())->toContain('lg:p-6')
        ->and(matrix_get_locations_map_panel_item_class_names())->toContain('gap-2.5')
        ->and(matrix_get_locations_map_location_title_class_names())->toContain('text-[18px]')
        ->and(matrix_get_locations_map_location_title_class_names())->toContain('leading-7')
        ->and(matrix_get_locations_map_panel_contact_text_class_names())->toContain('leading-7')
        ->and(matrix_get_locations_map_panel_opening_hours_heading_class_names())->toContain('font-bold')
        ->and(matrix_get_locations_map_location_divider_class_names())->toContain('border-[#80CCD9]')
        ->and(matrix_get_locations_map_location_divider_class_names())->toContain('mt-8')
        ->and(matrix_get_locations_map_location_divider_class_names())->toContain('pt-8')
        ->and(matrix_get_locations_map_panel_scrollbar_track_class_names())->toContain('lg:h-[32rem]')
        ->and(matrix_get_locations_map_panel_scrollbar_track_class_names())->toContain('bg-[#FBF8F3]')
        ->and(matrix_get_locations_map_panel_scrollbar_thumb_class_names())->toContain('bg-[#024B79]')
        ->and(matrix_get_locations_map_header_title_row_class_names())->toContain('lg:justify-between')
        ->and(matrix_get_locations_map_directions_button_wrapper_class_names())->toContain('lg:block');
});

test('locations map resolves jawg lagoon provider by default', function () {
    expect(matrix_resolve_locations_map_tile_provider(null))->toBe('jawg-lagoon')
        ->and(matrix_resolve_locations_map_tile_provider(''))->toBe('jawg-lagoon')
        ->and(matrix_resolve_locations_map_tile_provider('jawg-lagoon'))->toBe('jawg-lagoon')
        ->and(matrix_resolve_locations_map_tile_provider('invalid'))->toBe('jawg-lagoon');
});

test('locations map ireland bounds exclude uk mainland', function () {
    $bounds = matrix_get_locations_map_ireland_bounds();

    expect($bounds['east'])->toBeLessThan(-5.9)
        ->and($bounds['west'])->toBeLessThan(-10.0)
        ->and($bounds['north'])->toBeGreaterThan(55.0)
        ->and($bounds['south'])->toBeLessThan(52.0);
});

test('locations map jawg tiles add modifier class', function () {
    expect(matrix_get_locations_map_map_class_names('jawg-lagoon'))
        ->toContain('locations-map-leaflet--jawg')
        ->and(matrix_get_locations_map_map_class_names('osm'))->not->toContain('locations-map-leaflet--jawg');
});

test('locations map uses figma-inspired water and tile tint', function () {
    expect(matrix_get_locations_map_water_color())->toBe('#66C2E0')
        ->and(matrix_get_locations_map_land_tint_color())->toBe('#B8EBD0')
        ->and(matrix_get_locations_map_tile_filter())->toContain('hue-rotate(34deg)');
});

test('locations map panel uses figma gradient background', function () {
    expect(matrix_get_locations_map_panel_background_style())
        ->toContain('linear-gradient(278deg, #F6EDE0 3.24%, #F4F5DE 90.88%)');
});

test('locations map contact icons match figma artwork', function () {
    expect(matrix_get_locations_map_phone_icon_svg())
        ->toContain('stroke="#020617"')
        ->and(matrix_get_locations_map_email_icon_svg())->toContain('stroke="#020617"')
        ->and(matrix_get_locations_map_address_icon_svg())->toContain('stroke="#020617"');
});

test('locations map pin icon matches figma marker artwork', function () {
    expect(matrix_get_locations_map_pin_icon_svg())
        ->toContain('#6FC9C0')
        ->and(matrix_get_locations_map_pin_icon_svg())->toContain('#020617')
        ->and(matrix_get_locations_map_pin_icon_svg())->toContain('width="24"');
});

test('locations map intro visibility ignores empty markup', function () {
    expect(matrix_locations_map_has_visible_intro('<p>Find our campuses below.</p>'))->toBeTrue()
        ->and(matrix_locations_map_has_visible_intro('<p></p>'))->toBeFalse()
        ->and(matrix_locations_map_has_visible_intro(''))->toBeFalse();
});
