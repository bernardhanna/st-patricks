<?php

/**
 * Normalize a testimonial item into the shared display shape.
 *
 * @param array $item
 * @param string $fallback_tone
 * @return array<string, string>
 */
function matrix_normalize_testimonial_item($item, $fallback_tone = 'lavender')
{
    $item = is_array($item) ? $item : [];

    return [
        'quote' => trim((string) ($item['quote'] ?? '')),
        'author_name' => trim((string) ($item['author_name'] ?? '')),
        'author_title' => trim((string) ($item['author_title'] ?? '')),
        'card_tone' => trim((string) ($item['card_tone'] ?? $fallback_tone)),
    ];
}

/**
 * Convert a testimonial post-like object or array into the shared display shape.
 *
 * @param mixed $post
 * @param string $fallback_tone
 * @return array<string, string>
 */
function matrix_map_testimonial_post_to_item($post, $fallback_tone = 'lavender')
{
    if (is_object($post)) {
        $post = [
            'post_title' => $post->post_title ?? '',
            'post_content' => $post->post_content ?? '',
            'post_excerpt' => $post->post_excerpt ?? '',
        ];
    }

    $post = is_array($post) ? $post : [];

    return matrix_normalize_testimonial_item([
        'quote' => $post['post_content'] ?? '',
        'author_name' => $post['post_title'] ?? '',
        'author_title' => $post['post_excerpt'] ?? '',
        'card_tone' => $fallback_tone,
    ], $fallback_tone);
}

/**
 * Resolve testimonials from one selected source mode.
 *
 * @param string $source_mode
 * @param array $manual_items
 * @param array $selected_posts
 * @param array $all_posts
 * @return array<int, array<string, string>>
 */
function matrix_resolve_testimonial_items($source_mode, $manual_items = [], $selected_posts = [], $all_posts = [])
{
    $tone_cycle = ['lavender', 'mauve'];
    $resolved_items = [];

    if ($source_mode === 'selected') {
        $source_items = is_array($selected_posts) ? $selected_posts : [];

        foreach (array_values($source_items) as $index => $post_item) {
            $resolved_items[] = matrix_map_testimonial_post_to_item($post_item, $tone_cycle[$index % 2]);
        }

        return $resolved_items;
    }

    if ($source_mode === 'all') {
        $source_items = is_array($all_posts) ? $all_posts : [];

        foreach (array_values($source_items) as $index => $post_item) {
            $resolved_items[] = matrix_map_testimonial_post_to_item($post_item, $tone_cycle[$index % 2]);
        }

        return $resolved_items;
    }

    $source_items = is_array($manual_items) ? $manual_items : [];

    foreach (array_values($source_items) as $index => $item) {
        $resolved_items[] = matrix_normalize_testimonial_item($item, $tone_cycle[$index % 2]);
    }

    return $resolved_items;
}

/**
 * Group testimonial items into editorial rows of two standard cards plus one featured card.
 *
 * @param array $items
 * @return array<int, array{standard_items: array<int, array{index: int, item: array<string, string>}>, featured_item: ?array{index: int, item: array<string, string>}}>
 */
function matrix_group_editorial_featured_testimonials($items)
{
    $items = is_array($items) ? array_values($items) : [];
    $rows = [];

    foreach (array_chunk($items, 3, true) as $chunk_index => $chunk_items) {
        $offset = $chunk_index * 3;
        $standard_items = [];

        foreach (array_slice($chunk_items, 0, 2) as $item_index => $item) {
            $standard_items[] = [
                'index' => $offset + $item_index,
                'item' => $item,
            ];
        }

        $featured_item = null;
        if (isset($chunk_items[2])) {
            $featured_item = [
                'index' => $offset + 2,
                'item' => $chunk_items[2],
            ];
        }

        $rows[] = [
            'standard_items' => $standard_items,
            'featured_item' => $featured_item,
        ];
    }

    return $rows;
}
