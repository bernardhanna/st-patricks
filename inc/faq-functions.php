<?php

function matrix_map_faq_post_to_item($post, $starts_open = false)
{
    if (is_object($post)) {
        $post = [
            'post_title' => $post->post_title ?? '',
            'post_content' => $post->post_content ?? '',
        ];
    }

    $post = is_array($post) ? $post : [];

    $question = trim((string) ($post['post_title'] ?? ''));
    $answer = (string) ($post['post_content'] ?? '');

    if ($question === '' || trim(strip_tags($answer)) === '') {
        return null;
    }

    return [
        'question' => $question,
        'answer' => $answer,
        'starts_open' => (bool) $starts_open,
    ];
}

function matrix_resolve_faq_items($source_mode, $selected_posts = [], $category_posts = [], $all_posts = [])
{
    if ($source_mode === 'selected') {
        $source_items = is_array($selected_posts) ? $selected_posts : [];
    } elseif ($source_mode === 'category') {
        $source_items = is_array($category_posts) ? $category_posts : [];
    } else {
        $source_items = is_array($all_posts) ? $all_posts : [];
    }

    $resolved_items = [];

    foreach (array_values($source_items) as $index => $post) {
        $item = matrix_map_faq_post_to_item($post, $index === 0);

        if (is_array($item)) {
            if ($resolved_items !== []) {
                $item['starts_open'] = false;
            }

            $resolved_items[] = $item;
        }
    }

    if ($resolved_items !== []) {
        $resolved_items[0]['starts_open'] = true;
    }

    return $resolved_items;
}

function matrix_resolve_faq_layout_style($value)
{
    $value = is_string($value) ? trim($value) : '';

    if ($value === 'page') {
        return 'page';
    }

    return 'default';
}

function matrix_get_faq_background_style($background_value, $fallback = '#FFFFFF')
{
    $resolved_value = trim((string) $background_value);

    if ($resolved_value === '') {
        $resolved_value = trim((string) $fallback);
    }

    if ($resolved_value === '') {
        return '';
    }

    if (stripos($resolved_value, 'gradient(') !== false) {
        return "background: {$resolved_value};";
    }

    return "background-color: {$resolved_value};";
}
