<?php

function matrix_get_programmes_therapies_single_defaults()
{
    return [
        'back_label' => 'Back to programmes',
    ];
}

function matrix_get_programmes_therapies_archive_url()
{
    $day_programmes = get_page_by_path('what-we-offer/day-programmes');

    if ($day_programmes instanceof WP_Post) {
        $url = get_permalink($day_programmes);

        if (is_string($url) && $url !== '') {
            return $url . '#select-programme-or-therapy';
        }
    }

    $index_page = get_page_by_path('programmes-therapies');

    if ($index_page instanceof WP_Post) {
        $url = get_permalink($index_page);

        if (is_string($url) && $url !== '') {
            return $url . '#select-programme-or-therapy';
        }
    }

    return home_url('/what-we-offer/day-programmes/#select-programme-or-therapy');
}

function matrix_get_programmes_therapies_intro($post_id = null)
{
    $post_id = (int) ($post_id ?: get_the_ID());

    if ($post_id < 1) {
        return '';
    }

    return matrix_get_programmes_therapies_post_summary($post_id);
}
