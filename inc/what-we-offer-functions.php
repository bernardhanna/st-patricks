<?php

function matrix_resolve_what_we_offer_layout_style($value)
{
    $value = is_string($value) ? trim($value) : '';

    if ($value === 'intro_two_column') {
        return 'intro_two_column';
    }

    return 'image_feature';
}

function matrix_get_what_we_offer_accent_color($service_row, $index = 0)
{
    $accent_color = is_array($service_row) ? trim((string) ($service_row['accent_color'] ?? '')) : '';

    if ($accent_color !== '') {
        return $accent_color;
    }

    $palette = [
        '#6FC9C0',
        '#C3DBAE',
        '#B4A8CE',
        '#E4B8D6',
    ];

    return $palette[max(0, (int) $index) % count($palette)];
}

/**
 * Pastel icon-rail backgrounds for intro_two_column (Figma node 966:5970).
 */
function matrix_get_what_we_offer_intro_two_column_icon_background(string $accent_color): string
{
    $normalized_accent = strtoupper(ltrim(trim($accent_color), '#'));

    $tints = [
        '6FC9C0' => '#CEF2EE',
        'C3DBAE' => '#E4F4D6',
        'B4A8CE' => '#E9E2F7',
        'E4B8D6' => '#F9E5F2',
    ];

    if (isset($tints[$normalized_accent])) {
        return $tints[$normalized_accent];
    }

    return $accent_color !== '' ? $accent_color : '#CEF2EE';
}

function matrix_get_what_we_offer_intro_two_column_icon_urls($base_url = '')
{
    $base_url = rtrim((string) $base_url, '/');

    return [
        'default' => $base_url . '/wp-content/uploads/2025/11/left.svg',
        'hover' => $base_url . '/wp-content/uploads/2026/03/left.svg',
    ];
}

function matrix_get_what_we_offer_intro_two_column_icon_svg($state = 'default', $accent_color = '#6FC9C0')
{
    $theme_dir = function_exists('get_template_directory')
        ? get_template_directory()
        : dirname(__DIR__);
    $svg_path = $theme_dir . '/assets/svg/st-patricks-logo-symbol.svg';

    if (! is_readable($svg_path)) {
        return '';
    }

    $svg = file_get_contents($svg_path);

    if ($svg === false || $svg === '') {
        return '';
    }

    if ($state === 'hover') {
        return str_replace('opacity="0.25"', 'opacity="1"', $svg);
    }

    $accent_color = trim((string) $accent_color);
    if ($accent_color === '') {
        $accent_color = '#6FC9C0';
    }

    $safe_accent = function_exists('esc_attr')
        ? esc_attr($accent_color)
        : htmlspecialchars($accent_color, ENT_QUOTES, 'UTF-8');

    $svg = str_replace('fill="white"', 'fill="' . $safe_accent . '"', $svg);

    return str_replace('opacity="0.25"', 'opacity="1"', $svg);
}

/**
 * Render the shared CSS/SVG service icon rail (Figma node 966:5970).
 *
 * @param array{accent_color?: string, icon_background?: string} $service
 */
function matrix_render_what_we_offer_service_rail(array $service): void
{
    $accent_color = (string) ($service['accent_color'] ?? '#6FC9C0');
    $icon_background = (string) ($service['icon_background'] ?? matrix_get_what_we_offer_intro_two_column_icon_background($accent_color));
    $safe_background = function_exists('esc_attr')
        ? esc_attr($icon_background)
        : htmlspecialchars($icon_background, ENT_QUOTES, 'UTF-8');
    ?>
    <div
        class="relative h-[140px] w-[40px] shrink-0 overflow-hidden rounded-[4px]"
        aria-hidden="true"
    >
        <div
            class="relative h-[140px] w-full rounded-[4px]"
            style="background-color: <?php echo $safe_background; ?>;"
        >
            <div class="pointer-events-none absolute left-1/2 top-[10px] z-[1] h-8 w-8 -translate-x-1/2 transition-opacity duration-300 group-hover:opacity-0">
                <?php echo matrix_get_what_we_offer_intro_two_column_icon_svg('default', $accent_color); ?>
            </div>
        </div>
        <div class="absolute left-0 top-full h-[140px] w-[40px] rounded-[4px] bg-[#08284B] transition-transform duration-300 group-hover:-translate-y-full">
            <div class="pointer-events-none absolute left-1/2 top-[10px] z-[1] h-8 w-8 -translate-x-1/2 opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                <?php echo matrix_get_what_we_offer_intro_two_column_icon_svg('hover', $accent_color); ?>
            </div>
        </div>
    </div>
    <?php
}
