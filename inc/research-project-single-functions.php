<?php

function matrix_get_research_project_single_defaults()
{
    return [
        'back_label' => 'Back to research projects',
        'date_label' => 'Date posted:',
        'published_by_label' => 'Published by',
        'author_fallback' => 'St Patrick Hospital Team',
        'share_label' => 'Share on:',
        'related_heading' => 'Related Links',
        'previous_label' => 'Previous project',
        'next_label' => 'Next project',
        'related_count' => 3,
    ];
}

function matrix_get_research_project_archive_url()
{
    $archive_url = function_exists('get_post_type_archive_link') ? get_post_type_archive_link('research_projects') : '';
    if (is_string($archive_url) && $archive_url !== '') {
        return $archive_url;
    }

    $current_page = get_page_by_path('current-research-projects');
    if ($current_page instanceof WP_Post) {
        return get_permalink($current_page);
    }

    return home_url('/research-projects/');
}

function matrix_format_research_project_date($post_id = null)
{
    $post_id = $post_id ?: get_the_ID();

    return get_the_date('d/m/y', $post_id);
}

function matrix_get_research_project_intro($post_id = null)
{
    $post_id = $post_id ?: get_the_ID();
    $excerpt = trim((string) get_the_excerpt($post_id));

    if ($excerpt !== '') {
        return $excerpt;
    }

    $content = (string) get_post_field('post_content', $post_id);

    return wp_trim_words(wp_strip_all_tags($content), 40, '...');
}

function matrix_get_research_project_author_name($post_id = null)
{
    $post_id = $post_id ?: get_the_ID();
    $defaults = matrix_get_research_project_single_defaults();
    $custom_author = function_exists('get_field') ? trim((string) get_field('post_author_name', $post_id)) : '';

    if ($custom_author !== '') {
        return $custom_author;
    }

    $researcher_name = matrix_get_research_project_primary_researcher_name($post_id);
    if ($researcher_name !== '') {
        return $researcher_name;
    }

    return (string) ($defaults['author_fallback'] ?? 'St Patrick Hospital Team');
}

function matrix_get_research_project_share_links($post_id = null)
{
    $post_id = (int) ($post_id ?: (function_exists('get_the_ID') ? get_the_ID() : 0));

    if ($post_id < 1) {
        return [];
    }

    $permalink = function_exists('get_permalink') ? (string) get_permalink($post_id) : '';
    $post_title = function_exists('get_the_title') ? (string) get_the_title($post_id) : '';
    $url = rawurlencode($permalink);
    $title = rawurlencode($post_title);

    return [
        [
            'id' => 'facebook',
            'label' => 'Share on Facebook',
            'url' => 'https://www.facebook.com/sharer/sharer.php?u=' . $url,
        ],
        [
            'id' => 'twitter',
            'label' => 'Share on X',
            'url' => 'https://twitter.com/intent/tweet?url=' . $url . '&text=' . $title,
        ],
        [
            'id' => 'whatsapp',
            'label' => 'Share on WhatsApp',
            'url' => 'https://wa.me/?text=' . rawurlencode($post_title . ' ' . $permalink),
        ],
        [
            'id' => 'email',
            'label' => 'Share by email',
            'url' => 'mailto:?subject=' . $title . '&body=' . $url,
        ],
        [
            'id' => 'copy',
            'label' => 'Copy link',
            'url' => $permalink,
            'is_copy' => true,
        ],
    ];
}

function matrix_get_research_project_related_posts($post_id = null, $count = 3)
{
    $post_id = (int) ($post_id ?: get_the_ID());
    $count = max(1, (int) $count);
    $terms = wp_get_post_terms($post_id, 'research_project_category', ['fields' => 'ids']);

    $args = [
        'post_type' => 'research_projects',
        'post_status' => 'publish',
        'posts_per_page' => $count,
        'post__not_in' => [$post_id],
        'orderby' => 'date',
        'order' => 'DESC',
    ];

    if (! empty($terms) && ! is_wp_error($terms)) {
        $args['tax_query'] = [[
            'taxonomy' => 'research_project_category',
            'field' => 'term_id',
            'terms' => $terms,
        ]];
    }

    $query = new WP_Query($args);

    if (! $query->have_posts()) {
        $query = new WP_Query([
            'post_type' => 'research_projects',
            'post_status' => 'publish',
            'posts_per_page' => $count,
            'post__not_in' => [$post_id],
            'orderby' => 'date',
            'order' => 'DESC',
        ]);
    }

    return $query;
}

function matrix_get_research_project_adjacent_post_link($direction = 'next', $post_id = null)
{
    $post_id = (int) ($post_id ?: get_the_ID());
    $post = get_post($post_id);

    if (! $post instanceof WP_Post) {
        return null;
    }

    $adjacent = $direction === 'previous'
        ? get_previous_post(true, '', 'research_project_category')
        : get_next_post(true, '', 'research_project_category');

    if (! $adjacent instanceof WP_Post) {
        return null;
    }

    return [
        'id' => (int) $adjacent->ID,
        'title' => get_the_title($adjacent),
        'permalink' => get_permalink($adjacent),
    ];
}

function matrix_map_research_project_related_post_card($post_id)
{
    $post_id = (int) $post_id;
    $thumbnail_id = get_post_thumbnail_id($post_id);

    return [
        'id' => $post_id,
        'title' => get_the_title($post_id),
        'permalink' => get_permalink($post_id),
        'thumbnail_href' => get_permalink($post_id),
        'thumbnail_target' => '_self',
        'thumbnail_rel' => '',
        'date_label' => 'Date: ' . matrix_format_research_project_date($post_id),
        'category_name' => matrix_get_research_project_primary_category_name($post_id),
        'image_id' => (int) $thumbnail_id,
        'image_url' => $thumbnail_id ? (string) wp_get_attachment_image_url($thumbnail_id, 'medium_large') : '',
        'image_alt' => $thumbnail_id ? trim((string) get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true)) : '',
    ];
}
