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

/**
 * FAQ blocks often sit directly under an intro content section.
 * Avoid stacking a full 100px top pad on top of that section's bottom pad.
 *
 * @return list<string>
 */
function matrix_get_faq_section_wrapper_classes($layout_style = 'default', $show_heading = true): array
{
    $layout_style = matrix_resolve_faq_layout_style($layout_style);
    $show_heading = (bool) $show_heading;

    if ($layout_style === 'page') {
        $classes = ['flex', 'w-full', 'max-w-[1018px]', 'flex-col', 'mx-auto', 'px-5', 'xl:px-0'];
        $desktop_bottom = 'xl:pb-[100px]';
    } else {
        $classes = ['flex', 'w-full', 'max-w-[1018px]', 'flex-col', 'items-center', 'mx-auto', 'max-xl:px-5'];
        $desktop_bottom = 'lg:pb-[100px]';
    }

    if ($show_heading) {
        // Modest top spacing; previous section already supplies most of the separation.
        $classes[] = 'pt-12';
        $classes[] = 'pb-12';
        $classes[] = $layout_style === 'page' ? 'xl:pt-12' : 'lg:pt-12';
        $classes[] = $desktop_bottom;
    } else {
        // Heading/intro lives in the section above — continue flush into the accordion.
        $classes[] = 'pt-0';
        $classes[] = 'pb-12';
        $classes[] = $layout_style === 'page' ? 'xl:pt-0' : 'lg:pt-0';
        $classes[] = $desktop_bottom;
    }

    return $classes;
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
