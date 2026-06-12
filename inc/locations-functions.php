<?php

function matrix_get_location_card_image(int $post_id): ?array
{
    if ($post_id <= 0) {
        return null;
    }

    $card_image = get_field('card_image', $post_id);

    if (is_array($card_image) && ! empty($card_image['url'])) {
        return [
            'ID' => (int) ($card_image['ID'] ?? 0),
            'url' => trim((string) ($card_image['url'] ?? '')),
            'alt' => trim((string) ($card_image['alt'] ?? '')),
            'title' => trim((string) ($card_image['title'] ?? '')),
        ];
    }

    $thumbnail_id = (int) get_post_thumbnail_id($post_id);

    if ($thumbnail_id <= 0) {
        return null;
    }

    $alt = trim((string) get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true));

    if ($alt === '') {
        $alt = get_the_title($post_id);
    }

    return [
        'ID' => $thumbnail_id,
        'url' => (string) wp_get_attachment_url($thumbnail_id),
        'alt' => $alt,
        'title' => get_the_title($post_id),
    ];
}

function matrix_get_location_listing_summary(int $post_id): string
{
    if ($post_id <= 0) {
        return '';
    }

    $summary = trim((string) get_field('listing_summary', $post_id));

    if ($summary !== '') {
        return $summary;
    }

    $excerpt = trim((string) get_the_excerpt($post_id));

    return $excerpt;
}

/**
 * @param mixed $posts
 * @return array<int, array<string, mixed>>
 */
function matrix_locations_grid_cards_from_posts($posts): array
{
    if (! is_array($posts)) {
        return [];
    }

    $rows = [];

    foreach ($posts as $post) {
        $post_id = $post instanceof WP_Post ? (int) $post->ID : (int) $post;

        if ($post_id <= 0 || get_post_type($post_id) !== 'locations') {
            continue;
        }

        $title = trim(get_the_title($post_id));

        if ($title === '') {
            continue;
        }

        $summary = matrix_get_location_listing_summary($post_id);
        $link_title = $summary !== '' ? wp_strip_all_tags($summary) : sprintf('Find out more about %s', $title);

        $rows[] = [
            'title' => $title,
            'image' => matrix_get_location_card_image($post_id),
            'link' => [
                'title' => $link_title,
                'url' => get_permalink($post_id),
                'target' => '_self',
            ],
        ];
    }

    return matrix_normalize_locations_grid_cards($rows);
}

function matrix_resolve_locations_grid_cards(string $source_mode, $manual_cards, $selected_locations): array
{
    if ($source_mode === 'locations') {
        return matrix_locations_grid_cards_from_posts($selected_locations);
    }

    return matrix_normalize_locations_grid_cards($manual_cards);
}
