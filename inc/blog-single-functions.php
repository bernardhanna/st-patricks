<?php

function matrix_get_blog_single_defaults()
{
    return [
        'back_label' => 'Back to blog',
        'date_label' => 'Date posted:',
        'published_by_label' => 'Published by',
        'author_fallback' => 'St Patrick Hospital Team',
        'share_label' => 'Share on:',
        'related_heading' => 'Related Links',
        'previous_label' => 'Previous article',
        'next_label' => 'Next article',
        'previous_label_event' => 'Previous',
        'next_label_event' => 'Next',
        'event_external_button_label' => 'Link to an Eventbrite',
        'related_count' => 3,
        'events_category_slug' => 'events',
    ];
}

function matrix_is_event_post($post_id = null)
{
    $post_id = (int) ($post_id ?: (function_exists('get_the_ID') ? get_the_ID() : 0));

    if ($post_id < 1 || ! function_exists('has_category')) {
        return false;
    }

    $slug = matrix_get_blog_single_defaults()['events_category_slug'] ?? 'events';

    return has_category($slug, $post_id);
}

function matrix_get_event_post_fields($post_id = null)
{
    $post_id = (int) ($post_id ?: (function_exists('get_the_ID') ? get_the_ID() : 0));
    $defaults = matrix_get_blog_single_defaults();

    if ($post_id < 1 || ! function_exists('get_field')) {
        return [
            'external_url' => '',
            'external_button_label' => (string) ($defaults['event_external_button_label'] ?? 'Link to an Eventbrite'),
            'cta_summary' => '',
            'link_external_from_archive' => false,
        ];
    }

    $external_url = trim((string) get_field('event_external_url', $post_id));
    $button_label = trim((string) get_field('event_external_button_label', $post_id));
    $cta_summary = get_field('event_cta_summary', $post_id);

    return [
        'external_url' => $external_url,
        'external_button_label' => $button_label !== '' ? $button_label : (string) ($defaults['event_external_button_label'] ?? 'Link to an Eventbrite'),
        'cta_summary' => is_string($cta_summary) ? $cta_summary : '',
        'link_external_from_archive' => (bool) get_field('event_link_external_from_archive', $post_id),
    ];
}

function matrix_should_link_event_thumbnail_externally($post_id = null)
{
    if (! matrix_is_event_post($post_id)) {
        return false;
    }

    $fields = matrix_get_event_post_fields($post_id);

    return $fields['link_external_from_archive'] && $fields['external_url'] !== '';
}

function matrix_get_blog_post_card_url($post_id = null, $context = 'default')
{
    $post_id = (int) ($post_id ?: (function_exists('get_the_ID') ? get_the_ID() : 0));

    if ($post_id < 1) {
        return '';
    }

    if ($context === 'thumbnail' && matrix_should_link_event_thumbnail_externally($post_id)) {
        return matrix_get_event_post_fields($post_id)['external_url'];
    }

    return function_exists('get_permalink') ? (string) get_permalink($post_id) : '';
}

function matrix_get_blog_post_link_target($post_id = null, $context = 'default')
{
    $url = matrix_get_blog_post_card_url($post_id, $context);
    $is_external = ($context === 'thumbnail' && matrix_should_link_event_thumbnail_externally($post_id) && $url !== '')
        || matrix_is_external_url($url);
    $target = matrix_normalize_link_target($url);

    return [
        'url' => $url,
        'target' => $target,
        'rel' => matrix_external_link_rel($target),
        'is_external' => $is_external,
    ];
}

function matrix_get_blog_index_url()
{
    $posts_page_id = (int) get_option('page_for_posts');

    if ($posts_page_id > 0) {
        $url = get_permalink($posts_page_id);
        if (is_string($url) && $url !== '') {
            return $url;
        }
    }

    $resources_page = get_page_by_path('resources');
    if ($resources_page instanceof WP_Post) {
        return get_permalink($resources_page);
    }

    return home_url('/resources/');
}

function matrix_format_blog_post_date($post_id = null)
{
    $post_id = $post_id ?: get_the_ID();

    return get_the_date('d/m/y', $post_id);
}

function matrix_get_blog_post_intro($post_id = null)
{
    $post_id = $post_id ?: get_the_ID();
    $excerpt = trim((string) get_the_excerpt($post_id));

    if ($excerpt !== '') {
        return $excerpt;
    }

    $content = (string) get_post_field('post_content', $post_id);

    return wp_trim_words(wp_strip_all_tags($content), 40, '...');
}

function matrix_get_blog_post_author_name($post_id = null)
{
    $post_id = $post_id ?: get_the_ID();
    $defaults = matrix_get_blog_single_defaults();
    $custom_author = function_exists('get_field') ? trim((string) get_field('post_author_name', $post_id)) : '';

    if ($custom_author !== '') {
        return $custom_author;
    }

    $author_id = (int) get_post_field('post_author', $post_id);
    if ($author_id > 0) {
        $display_name = trim((string) get_the_author_meta('display_name', $author_id));
        if ($display_name !== '') {
            return $display_name;
        }
    }

    return (string) ($defaults['author_fallback'] ?? 'St Patrick Hospital Team');
}

function matrix_get_blog_post_share_links($post_id = null)
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

function matrix_get_blog_related_posts($post_id = null, $count = 3)
{
    $post_id = (int) ($post_id ?: get_the_ID());
    $count = max(1, (int) $count);
    $categories = wp_get_post_terms($post_id, 'category', ['fields' => 'ids']);

    $args = [
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => $count,
        'post__not_in' => [$post_id],
        'orderby' => 'date',
        'order' => 'DESC',
    ];

    if (! empty($categories) && ! is_wp_error($categories)) {
        $args['category__in'] = $categories;
    }

    $query = new WP_Query($args);

    if (! $query->have_posts()) {
        $query = new WP_Query([
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $count,
            'post__not_in' => [$post_id],
            'orderby' => 'date',
            'order' => 'DESC',
        ]);
    }

    return $query;
}

function matrix_map_blog_related_post_card($post_id)
{
    $post_id = (int) $post_id;
    $categories = get_the_category($post_id);
    $primary_category = ($categories && $categories[0] instanceof WP_Term) ? $categories[0] : null;
    $thumbnail_id = get_post_thumbnail_id($post_id);

    $thumbnail_link = matrix_get_blog_post_link_target($post_id, 'thumbnail');

    return [
        'id' => $post_id,
        'title' => get_the_title($post_id),
        'permalink' => matrix_get_blog_post_card_url($post_id),
        'thumbnail_href' => $thumbnail_link['url'],
        'thumbnail_target' => $thumbnail_link['target'],
        'thumbnail_rel' => $thumbnail_link['rel'],
        'date_label' => 'Date: ' . matrix_format_blog_post_date($post_id),
        'category_name' => $primary_category ? $primary_category->name : '',
        'image_id' => (int) $thumbnail_id,
        'image_url' => $thumbnail_id ? (string) wp_get_attachment_image_url($thumbnail_id, 'medium_large') : '',
        'image_alt' => $thumbnail_id ? trim((string) get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true)) : '',
    ];
}

function matrix_migrate_image_basename(string $url): string
{
    if ($url === '') {
        return '';
    }

    $path = parse_url($url, PHP_URL_PATH);

    return is_string($path) && $path !== ''
        ? strtolower(basename($path))
        : '';
}

function matrix_migrate_img_matches_attachment(DOMElement $img, int $attachment_id, string $attachment_url): bool
{
    if ($attachment_id < 1 || $attachment_url === '') {
        return false;
    }

    $class = $img->getAttribute('class');

    if ($class !== '' && preg_match('/\bwp-image-' . $attachment_id . '\b/', $class)) {
        return true;
    }

    $src = $img->getAttribute('src');

    if ($src === '') {
        return false;
    }

    $thumb_base = matrix_migrate_image_basename($attachment_url);
    $src_base = matrix_migrate_image_basename($src);

    return $thumb_base !== '' && $thumb_base === $src_base;
}

/**
 * Remove a leading hero image from migrated body HTML when it matches the post thumbnail.
 */
function matrix_remove_leading_duplicate_featured_image_from_content(string $content, int $attachment_id, string $attachment_url): string
{
    if ($content === '' || $attachment_id < 1 || $attachment_url === '') {
        return $content;
    }

    if (! function_exists('matrix_migrate_parse_html_document')) {
        $migrate_file = function_exists('get_template_directory')
            ? get_template_directory() . '/inc/migrate-functions.php'
            : dirname(__FILE__) . '/migrate-functions.php';
        require_once $migrate_file;
    }

    $dom = matrix_migrate_parse_html_document($content);

    if (! $dom instanceof DOMDocument) {
        return $content;
    }

    $body = $dom->getElementsByTagName('body')->item(0);

    if (! $body instanceof DOMElement) {
        return $content;
    }

    $node_to_remove = null;

    foreach ($body->childNodes as $child) {
        if ($child instanceof DOMText && trim($child->textContent) === '') {
            continue;
        }

        if (! $child instanceof DOMElement) {
            break;
        }

        if ($child->tagName === 'figure') {
            $img = $child->getElementsByTagName('img')->item(0);

            if ($img instanceof DOMElement && matrix_migrate_img_matches_attachment($img, $attachment_id, $attachment_url)) {
                $node_to_remove = $child;
            }

            break;
        }

        if ($child->tagName === 'img') {
            if (matrix_migrate_img_matches_attachment($child, $attachment_id, $attachment_url)) {
                $node_to_remove = $child;
            }

            break;
        }

        break;
    }

    if (! $node_to_remove instanceof DOMNode || ! $node_to_remove->parentNode instanceof DOMNode) {
        return $content;
    }

    $node_to_remove->parentNode->removeChild($node_to_remove);

    return trim(matrix_migrate_dom_inner_html($body));
}

function matrix_remove_leading_duplicate_featured_image(string $content, int $post_id): string
{
    if ($content === '' || $post_id < 1 || ! function_exists('has_post_thumbnail') || ! has_post_thumbnail($post_id)) {
        return $content;
    }

    $attachment_id = (int) get_post_thumbnail_id($post_id);
    $attachment_url = (string) wp_get_attachment_url($attachment_id);

    return matrix_remove_leading_duplicate_featured_image_from_content($content, $attachment_id, $attachment_url);
}

/**
 * Strip Umbraco migration artefacts from imported post HTML.
 */
function matrix_format_migrated_post_content(string $content, int $post_id = 0): string
{
    if ($content === '') {
        return '';
    }

    if (! function_exists('matrix_migrate_parse_html_document')) {
        $migrate_file = function_exists('get_template_directory')
            ? get_template_directory() . '/inc/migrate-functions.php'
            : dirname(__FILE__) . '/migrate-functions.php';
        require_once $migrate_file;
    }

    $dom = matrix_migrate_parse_html_document($content);

    if (! $dom instanceof DOMDocument) {
        return $content;
    }

    $xpath = new DOMXPath($dom);

    foreach ($xpath->query('//h3[contains(concat(" ", normalize-space(@class), " "), " hide-for-main ")]') as $node) {
        if ($node->parentNode instanceof DOMNode) {
            $node->parentNode->removeChild($node);
        }
    }

    foreach ($xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " section-head ")]') as $wrapper) {
        if (! $wrapper->parentNode instanceof DOMNode) {
            continue;
        }

        $parent = $wrapper->parentNode;

        while ($wrapper->firstChild) {
            $parent->insertBefore($wrapper->firstChild, $wrapper);
        }

        $parent->removeChild($wrapper);
    }

    $body = $dom->getElementsByTagName('body')->item(0);

    if (! $body instanceof DOMNode) {
        return $content;
    }

    $html = trim(matrix_migrate_dom_inner_html($body));

    if ($post_id > 0) {
        $html = matrix_remove_leading_duplicate_featured_image($html, $post_id);
    }

    return matrix_process_external_links_in_html($html);
}

function matrix_filter_blog_single_content(string $content): string
{
    if ($content === '' || ! is_singular('post')) {
        return $content;
    }

    $post_id = get_the_ID();

    if ($post_id < 1 || get_post_meta($post_id, '_matrix_migrate_old_path', true) === '') {
        return $content;
    }

    return matrix_format_migrated_post_content($content, $post_id);
}

if (function_exists('add_filter')) {
    add_filter('the_content', 'matrix_filter_blog_single_content', 20);
}

function matrix_get_blog_adjacent_post_link($direction = 'next', $post_id = null)
{
    $post_id = (int) ($post_id ?: get_the_ID());
    $post = get_post($post_id);

    if (! $post instanceof WP_Post) {
        return null;
    }

    $adjacent = $direction === 'previous'
        ? get_previous_post(false, '', 'category')
        : get_next_post(false, '', 'category');

    if (! $adjacent instanceof WP_Post) {
        return null;
    }

    return [
        'id' => (int) $adjacent->ID,
        'title' => get_the_title($adjacent),
        'permalink' => get_permalink($adjacent),
    ];
}
