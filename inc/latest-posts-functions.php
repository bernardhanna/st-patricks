<?php

function matrix_build_latest_posts_query_args($selected_categories = [], $posts_per_page = 6)
{
    $resolved_posts_per_page = (int) $posts_per_page;

    if ($resolved_posts_per_page < 1) {
        $resolved_posts_per_page = 6;
    }

    $args = [
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => $resolved_posts_per_page,
        'orderby' => 'date',
        'order' => 'DESC',
    ];

    $category_ids = array_values(array_filter(array_map('intval', (array) $selected_categories)));

    if ($category_ids !== []) {
        $args['category__in'] = $category_ids;
    }

    return $args;
}

function matrix_get_latest_posts_header_button_class_names()
{
    return 'btn inline-flex h-[36px] w-fit shrink-0 items-center justify-center whitespace-nowrap rounded-[6px] border border-[#024B79] bg-transparent px-3 text-[14px] font-medium leading-[24px] text-[#08284B] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]';
}

function matrix_normalize_latest_posts_card($post_id)
{
    $post_id = (int) $post_id;

    if ($post_id < 1) {
        return null;
    }

    $title = get_the_title($post_id);
    $permalink = get_permalink($post_id);
    $thumbnail_id = (int) get_post_thumbnail_id($post_id);
    $thumbnail_url = $thumbnail_id > 0 ? (string) wp_get_attachment_image_url($thumbnail_id, 'medium_large') : '';
    $thumbnail_alt = $thumbnail_id > 0 ? trim((string) get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true)) : '';

    if ($thumbnail_alt === '') {
        $thumbnail_alt = $title;
    }

    if (! is_string($permalink) || $permalink === '') {
        return null;
    }

    return [
        'post_id' => $post_id,
        'title' => $title,
        'permalink' => $permalink,
        'thumbnail_url' => $thumbnail_url,
        'thumbnail_alt' => $thumbnail_alt,
    ];
}
