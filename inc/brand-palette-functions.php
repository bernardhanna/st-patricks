<?php

/**
 * Brand palette lavender (What We Offer / testimonials accent).
 */
function matrix_get_brand_lavender_color(): string
{
    return '#B4A8CE';
}

/**
 * Legacy soft-lavender section fills that should use brand lavender on Willow Grove pages.
 *
 * @return list<string> Uppercase hex without #.
 */
function matrix_get_legacy_soft_lavender_hex_codes(): array
{
    return ['E9E2F7', 'E8E2F7', 'E5E1F3'];
}

/**
 * Whether the current (or given) page is part of Willow Grove adolescent branding.
 */
function matrix_is_willow_grove_branded_page(?int $post_id = null): bool
{
    if ($post_id === null) {
        if (! function_exists('is_singular') || ! is_singular()) {
            return false;
        }

        $post_id = (int) get_queried_object_id();
    }

    if ($post_id <= 0) {
        return false;
    }

    $post = get_post($post_id);

    if (! is_object($post) || ! isset($post->post_name)) {
        return false;
    }

    $branded_slugs = [
        'your-care-with-willow-grove',
        'willow-grove-adolescent-unit',
        'inpatient-adolescent-unit',
        'refer-an-adolescent',
        'adolescent-mental-health-services',
    ];

    if (in_array($post->post_name, $branded_slugs, true)) {
        return true;
    }

    if (str_contains($post->post_name, 'willow-grove')) {
        return true;
    }

    if ($post->post_type === 'locations' && str_contains($post->post_name, 'willow-grove')) {
        return true;
    }

    $ancestor_id = (int) $post->post_parent;

    while ($ancestor_id > 0) {
        $ancestor = get_post($ancestor_id);

        if (! is_object($ancestor) || ! isset($ancestor->post_name)) {
            break;
        }

        if ($ancestor->post_name === 'your-care-with-willow-grove') {
            return true;
        }

        $ancestor_id = (int) $ancestor->post_parent;
    }

    return false;
}

/**
 * On Willow Grove pages, map legacy soft lavenders to the brand palette lavender.
 */
function matrix_resolve_willow_grove_lavender_color(string $color, ?int $post_id = null): string
{
    if (! matrix_is_willow_grove_branded_page($post_id)) {
        return $color;
    }

    $normalized = strtoupper(ltrim(trim($color), '#'));

    if ($normalized === '' || ! in_array($normalized, matrix_get_legacy_soft_lavender_hex_codes(), true)) {
        return $color;
    }

    return matrix_get_brand_lavender_color();
}
