<?php

function matrix_get_locations_map_pin_icon_svg(): string
{
    static $svg = null;

    if ($svg !== null) {
        return $svg;
    }

    $base = function_exists('get_template_directory')
        ? get_template_directory()
        : dirname(__DIR__);

    $path = $base . '/assets/svg/locations-map-pin.svg';

    if (! is_readable($path)) {
        return '';
    }

    $svg = (string) file_get_contents($path);

    return $svg;
}

function matrix_get_locations_map_phone_icon_svg(): string
{
    return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M22.0014 16.92V19.92C22.0025 20.1985 21.9455 20.4741 21.8339 20.7293C21.7223 20.9845 21.5587 21.2136 21.3535 21.4018C21.1483 21.5901 20.906 21.7335 20.6421 21.8227C20.3783 21.9119 20.0988 21.945 19.8214 21.92C16.7442 21.5856 13.7884 20.5341 11.1914 18.85C8.77523 17.3146 6.72673 15.2661 5.1914 12.85C3.50138 10.2412 2.44964 7.27097 2.1214 4.17997C2.09641 3.90344 2.12927 3.62474 2.2179 3.3616C2.30652 3.09846 2.44897 2.85666 2.63616 2.6516C2.82336 2.44653 3.0512 2.28268 3.30519 2.1705C3.55917 2.05831 3.83374 2.00024 4.1114 1.99997H7.1114C7.5967 1.9952 8.06719 2.16705 8.43516 2.48351C8.80313 2.79996 9.04348 3.23942 9.1114 3.71997C9.23802 4.68004 9.47285 5.6227 9.8114 6.52997C9.94594 6.8879 9.97506 7.27689 9.8953 7.65086C9.81555 8.02482 9.63026 8.36809 9.3614 8.63998L8.0914 9.90997C9.51495 12.4135 11.5879 14.4864 14.0914 15.91L15.3614 14.64C15.6333 14.3711 15.9766 14.1858 16.3505 14.1061C16.7245 14.0263 17.1135 14.0554 17.4714 14.19C18.3787 14.5285 19.3213 14.7634 20.2814 14.89C20.7672 14.9585 21.2108 15.2032 21.5279 15.5775C21.8451 15.9518 22.0136 16.4296 22.0014 16.92Z" stroke="#020617" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M14.0508 2C16.089 2.21477 17.993 3.1188 19.4476 4.56258C20.9023 6.00636 21.8207 7.90341 22.0508 9.94" stroke="#020617" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M14.0508 6C15.0343 6.19394 15.9368 6.67903 16.6412 7.39231C17.3455 8.10559 17.8192 9.01413 18.0008 10" stroke="#020617" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

function matrix_get_locations_map_email_icon_svg(): string
{
    return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 4H4C2.89543 4 2 4.89543 2 6V18C2 19.1046 2.89543 20 4 20H20C21.1046 20 22 19.1046 22 18V6C22 4.89543 21.1046 4 20 4Z" stroke="#020617" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M22 7L13.03 12.7C12.7213 12.8934 12.3643 12.996 12 12.996C11.6357 12.996 11.2787 12.8934 10.97 12.7L2 7" stroke="#020617" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

function matrix_get_locations_map_address_icon_svg(): string
{
    return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 10C20 16 12 22 12 22C12 22 4 16 4 10C4 7.87827 4.84285 5.84344 6.34315 4.34315C7.84344 2.84285 9.87827 2 12 2C14.1217 2 16.1566 2.84285 17.6569 4.34315C19.1571 5.84344 20 7.87827 20 10Z" stroke="#020617" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 13C13.6569 13 15 11.6569 15 10C15 8.34315 13.6569 7 12 7C10.3431 7 9 8.34315 9 10C9 11.6569 10.3431 13 12 13Z" stroke="#020617" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

function matrix_get_locations_map_panel_icon_wrapper_class_names(): string
{
    return 'flex size-6 shrink-0 items-center justify-center';
}

function matrix_get_locations_map_section_wrapper_class_names(): string
{
    return 'locations-map-section bg-white pb-12 lg:pb-0';
}

function matrix_get_locations_map_wrapper_class_names(): string
{
    return implode(' ', [
        'mx-auto',
        'w-full',
        'max-w-[1018px]',
        'px-4',
        'lg:px-5',
        'xl:px-0',
    ]);
}

function matrix_get_locations_map_header_wrapper_class_names(): string
{
    return matrix_get_locations_map_wrapper_class_names() . ' pb-0 pt-3.5 lg:pb-12 lg:pt-16';
}

function matrix_get_locations_map_header_class_names(): string
{
    return 'flex w-full flex-col gap-8 max-lg:gap-0';
}

function matrix_get_locations_map_header_title_row_class_names(): string
{
    return 'flex w-full flex-col gap-6 max-lg:gap-0 lg:flex-row lg:items-start lg:justify-between lg:gap-8';
}

function matrix_get_locations_map_header_title_group_class_names(): string
{
    return 'flex min-w-0 flex-1 flex-col gap-8 max-lg:pb-2.5 lg:gap-4';
}

function matrix_get_locations_map_directions_button_wrapper_class_names(): string
{
    return 'hidden shrink-0 self-start lg:block';
}

function matrix_get_locations_map_intro_wrapper_class_names(): string
{
    return 'hidden lg:block';
}

function matrix_get_locations_map_directions_link_class_names(): string
{
    return 'btn inline-flex h-[36px] items-center justify-center rounded-[6px] border border-[#024B79] bg-white px-4 font-primary text-[14px] font-medium leading-[24px] text-[#08284B] transition-colors hover:bg-[#024B79]/5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]';
}

/** @deprecated Use matrix_get_locations_map_directions_button_wrapper_class_names(). */
function matrix_get_locations_map_directions_row_class_names(): string
{
    return matrix_get_locations_map_directions_button_wrapper_class_names();
}

function matrix_get_locations_map_heading_class_names(): string
{
    return 'font-primary text-[24px] font-semibold leading-[28px] tracking-[-0.18px] text-[#1E244B] lg:text-[30px] lg:leading-[36px] lg:tracking-[-0.225px]';
}

function matrix_get_locations_map_intro_class_names(): string
{
    if (! function_exists('matrix_get_content_rich_text_wrapper_class_names')) {
        return 'wp_editor w-full font-primary text-[16px] font-medium leading-[28px] text-[#08284B]';
    }

    return matrix_get_content_rich_text_wrapper_class_names('medium', 'w-full', 'default');
}

function matrix_resolve_locations_map_tile_provider($provider): string
{
    $provider = (string) ($provider ?: 'jawg-lagoon');
    $allowed = ['jawg-lagoon', 'jawg-light', 'jawg-dark', 'osm'];

    return in_array($provider, $allowed, true) ? $provider : 'jawg-lagoon';
}

function matrix_get_locations_map_tile_api_key($block_key = ''): string
{
    $key = trim((string) $block_key);

    if ($key !== '') {
        return $key;
    }

    if (function_exists('get_field')) {
        $option_key = get_field('jawg_access_token', 'option');

        if (is_string($option_key) && trim($option_key) !== '') {
            return trim($option_key);
        }
    }

    $env_key = getenv('JAWG_ACCESS_TOKEN');

    return is_string($env_key) ? trim($env_key) : '';
}

/**
 * @return array{south: float, west: float, north: float, east: float}
 */
function matrix_get_locations_map_ireland_bounds(): array
{
    return [
        'south' => 51.40,
        'west' => -10.50,
        'north' => 55.40,
        'east' => -5.95,
    ];
}

function matrix_locations_map_uses_jawg_tiles(string $tile_provider): bool
{
    return str_starts_with($tile_provider, 'jawg-');
}

function matrix_get_locations_map_water_color(): string
{
    return '#66C2E0';
}

function matrix_get_locations_map_land_tint_color(): string
{
    return '#B8EBD0';
}

function matrix_get_locations_map_tile_filter(): string
{
    return 'hue-rotate(34deg) saturate(1.35) brightness(1.04) contrast(0.82)';
}

function matrix_get_locations_map_stage_class_names(): string
{
    return 'locations-map-stage relative w-full max-lg:flex max-lg:flex-col max-lg:bg-white lg:bg-[#66C2E0]';
}

/** @deprecated Map stage is full-bleed; panel aligns via overlay row inset. */
function matrix_get_locations_map_stage_inner_class_names(): string
{
    return 'relative mx-auto w-full max-w-[1018px]';
}

function matrix_get_locations_map_map_shell_class_names(): string
{
    return 'relative z-0 w-full shrink-0 max-lg:left-1/2 max-lg:w-screen max-lg:max-w-none max-lg:-translate-x-1/2 max-lg:bg-[#66C2E0]';
}

function matrix_get_locations_map_map_class_names(string $tile_provider = 'jawg-lagoon'): string
{
    $classes = 'locations-map-leaflet relative h-[402px] w-full overflow-hidden lg:h-[48.8125rem]';

    if (matrix_locations_map_uses_jawg_tiles($tile_provider)) {
        $classes .= ' locations-map-leaflet--jawg';
    }

    return $classes;
}

function matrix_get_locations_map_overlay_row_class_names(): string
{
    return implode(' ', [
        'relative',
        'z-10',
        'w-full',
        'max-lg:bg-[#CEF2EE]',
        'max-lg:px-4',
        'max-lg:pt-8',
        'max-lg:pb-12',
        'lg:pointer-events-none',
        'lg:absolute',
        'lg:inset-y-0',
        'lg:left-[max(1.25rem,calc((100%-63.625rem)/2))]',
        'lg:flex',
        'lg:h-[48.8125rem]',
        'lg:w-[23.8125rem]',
        'lg:flex-col',
        'lg:justify-center',
        'lg:bg-transparent',
        'lg:px-0',
        'lg:pb-0',
        'lg:pt-0',
    ]);
}

function matrix_get_locations_map_panel_column_class_names(): string
{
    return 'pointer-events-auto flex w-full flex-col lg:w-[23.8125rem]';
}

function matrix_get_locations_map_mobile_directions_wrapper_class_names(): string
{
    return 'mt-8 flex w-full justify-start lg:hidden';
}

function matrix_get_locations_map_panel_background_style(): string
{
    return 'background: linear-gradient(278deg, #F6EDE0 3.24%, #F4F5DE 90.88%);';
}

function matrix_get_locations_map_panel_card_class_names(): string
{
    return 'locations-map-panel-card relative flex h-[39.3125rem] max-h-[39.3125rem] w-full flex-col overflow-hidden rounded-[4px] border border-[#E7E5E0] bg-white lg:w-[23.8125rem] lg:border-0';
}

function matrix_get_locations_map_panel_scroll_class_names(): string
{
    return 'locations-map-panel flex h-full w-full flex-col items-start gap-2.5 overflow-y-auto p-8 pr-10 lg:p-6 lg:pr-8';
}

function matrix_get_locations_map_panel_item_class_names(): string
{
    return 'locations-map-panel__item flex w-full flex-col items-start gap-2.5';
}

function matrix_get_locations_map_panel_contact_row_class_names(): string
{
    return 'flex items-start gap-2.5';
}

function matrix_get_locations_map_panel_contact_text_class_names(): string
{
    return 'font-primary text-[16px] font-medium leading-7 text-[#08284B] transition-colors hover:text-[#024B79] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]';
}

function matrix_get_locations_map_panel_contact_block_class_names(): string
{
    return 'flex w-full flex-col gap-2.5';
}

function matrix_get_locations_map_panel_opening_hours_heading_class_names(): string
{
    return 'font-primary text-[16px] font-bold leading-7 text-[#08284B]';
}

function matrix_get_locations_map_panel_opening_hours_grid_class_names(): string
{
    return 'grid w-full grid-cols-[minmax(0,1fr)_minmax(0,1fr)] gap-x-6 gap-y-1';
}

function matrix_get_locations_map_panel_opening_hours_label_class_names(): string
{
    return 'font-primary text-[16px] font-medium leading-7 text-[#08284B]';
}

/** @deprecated Use matrix_get_locations_map_stage_class_names(). */
function matrix_get_locations_map_body_grid_class_names(): string
{
    return matrix_get_locations_map_stage_class_names();
}

/** @deprecated Use matrix_get_locations_map_map_shell_class_names(). */
function matrix_get_locations_map_map_column_class_names(): string
{
    return matrix_get_locations_map_map_shell_class_names();
}

/** @deprecated Use matrix_get_locations_map_wrapper_class_names(). */
function matrix_get_locations_map_inner_wrapper_class_names(): string
{
    return matrix_get_locations_map_wrapper_class_names();
}

/** @deprecated Use matrix_get_locations_map_overlay_row_class_names(). */
function matrix_get_locations_map_panel_section_class_names(): string
{
    return matrix_get_locations_map_overlay_row_class_names();
}

function matrix_get_locations_map_location_title_class_names(): string
{
    return 'font-primary text-[18px] font-semibold leading-7 text-[#08284B]';
}

function matrix_get_locations_map_location_divider_class_names(): string
{
    return 'mt-8 border-t border-[#80CCD9] pt-8';
}

function matrix_get_locations_map_panel_scrollbar_class_names(): string
{
    return 'locations-map-panel__scrollbar pointer-events-none absolute bottom-8 right-2 top-8 z-[1] flex w-3 flex-col lg:bottom-auto lg:top-1/2 lg:-translate-y-1/2';
}

function matrix_get_locations_map_panel_scrollbar_track_class_names(): string
{
    return 'locations-map-panel__scrollbar-track relative h-full w-3 rounded-[0.375rem] bg-[#FBF8F3] lg:h-[32rem]';
}

function matrix_get_locations_map_panel_scrollbar_thumb_class_names(): string
{
    return 'locations-map-panel__scrollbar-thumb absolute left-0 top-0 w-3 rounded-[0.375rem] bg-[#024B79]';
}

function matrix_get_locations_map_button_class_names(): string
{
    return 'btn inline-flex h-[36px] w-fit min-w-[175px] items-center justify-center whitespace-nowrap rounded-[6px] border border-[#024B79] px-3 text-[14px] font-medium leading-[24px] text-[#08284B] transition-colors hover:bg-[#024B79]/5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]';
}

function matrix_get_locations_map_button_wrapper_class_names(): string
{
    return 'mt-6 flex w-full shrink-0 justify-start';
}

function matrix_locations_map_has_visible_intro($intro_text): bool
{
    if (! is_string($intro_text)) {
        return false;
    }

    $intro_text = trim(function_exists('wp_strip_all_tags') ? wp_strip_all_tags($intro_text) : strip_tags($intro_text));

    return $intro_text !== '';
}

function matrix_normalize_location_opening_hours($rows): array
{
    if (! is_array($rows)) {
        return [];
    }

    $normalized = [];

    foreach ($rows as $row) {
        if (! is_array($row)) {
            continue;
        }

        $day_label = trim((string) ($row['day_label'] ?? ''));
        $hours = trim((string) ($row['hours'] ?? ''));

        if ($day_label === '' && $hours === '') {
            continue;
        }

        $normalized[] = [
            'day_label' => $day_label,
            'hours' => $hours,
        ];
    }

    return $normalized;
}

/**
 * @return array{lat: float, lng: float}|null
 */
function matrix_get_location_coordinates(int $post_id): ?array
{
    if ($post_id <= 0) {
        return null;
    }

    $lat_raw = get_post_meta($post_id, 'latitude', true);
    $lng_raw = get_post_meta($post_id, 'longitude', true);

    if ($lat_raw === '' || $lat_raw === null) {
        $lat_raw = get_field('latitude', $post_id);
    }

    if ($lng_raw === '' || $lng_raw === null) {
        $lng_raw = get_field('longitude', $post_id);
    }

    $lat = is_string($lat_raw) ? str_replace(',', '.', trim($lat_raw)) : $lat_raw;
    $lng = is_string($lng_raw) ? str_replace(',', '.', trim($lng_raw)) : $lng_raw;

    if ($lat === '' || $lng === '' || ! is_numeric($lat) || ! is_numeric($lng)) {
        return null;
    }

    return [
        'lat' => (float) $lat,
        'lng' => (float) $lng,
    ];
}

function matrix_location_is_visible_on_contact_map(int $post_id): bool
{
    if ($post_id <= 0) {
        return false;
    }

    $show = get_field('show_on_contact_map', $post_id);

    if ($show === null || $show === '') {
        return true;
    }

    return $show === 1 || $show === '1' || $show === true;
}

/**
 * @return array<string, mixed>|null
 */
function matrix_get_location_map_payload(int $post_id): ?array
{
    if ($post_id <= 0 || get_post_type($post_id) !== 'locations') {
        return null;
    }

    if (! matrix_location_is_visible_on_contact_map($post_id)) {
        return null;
    }

    $coords = matrix_get_location_coordinates($post_id);

    if ($coords === null) {
        return null;
    }

    $title = trim(get_the_title($post_id));

    if ($title === '') {
        return null;
    }

    $phone = trim((string) get_field('phone', $post_id));
    $email = trim((string) get_field('email', $post_id));
    $address = trim((string) get_field('address', $post_id));

    if ($address === '') {
        $address = trim((string) get_the_excerpt($post_id));
    }

    return [
        'id' => $post_id,
        'title' => $title,
        'lat' => $coords['lat'],
        'lng' => $coords['lng'],
        'phone' => $phone,
        'email' => $email,
        'address' => $address,
        'opening_hours' => matrix_normalize_location_opening_hours(get_field('opening_hours', $post_id)),
        'url' => (string) get_permalink($post_id),
    ];
}

/**
 * @param mixed $posts
 * @return array<int, array<string, mixed>>
 */
function matrix_locations_map_markers_from_posts($posts): array
{
    if (! is_array($posts)) {
        return [];
    }

    $markers = [];

    foreach ($posts as $post) {
        $post_id = $post instanceof WP_Post ? (int) $post->ID : (int) $post;
        $payload = matrix_get_location_map_payload($post_id);

        if ($payload !== null) {
            $markers[] = $payload;
        }
    }

    return $markers;
}

/**
 * @param mixed $selected_locations
 * @return array<int, array<string, mixed>>
 */
function matrix_resolve_locations_map_markers(string $source_mode, $selected_locations): array
{
    if ($source_mode === 'selected') {
        return matrix_locations_map_markers_from_posts($selected_locations);
    }

    $location_ids = get_posts([
        'post_type' => 'locations',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'title',
        'order' => 'ASC',
        'fields' => 'ids',
    ]);

    return matrix_locations_map_markers_from_posts($location_ids);
}

function matrix_format_locations_map_phone_link(string $phone): string
{
    $phone = trim($phone);

    if ($phone === '') {
        return '';
    }

    $tel = preg_replace('/\s+/', '', $phone);

    return is_string($tel) ? $tel : $phone;
}
