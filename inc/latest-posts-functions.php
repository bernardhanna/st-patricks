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
