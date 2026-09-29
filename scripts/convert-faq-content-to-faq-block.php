<?php

/**
 * Convert FAQ-style content / accordion blocks into the faqs flexi block
 * (with faqs CPT posts), site-wide where Q&A pairs can be parsed.
 *
 * Usage:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/convert-faq-content-to-faq-block.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

$home = untrailingslashit(home_url('/'));

if (! function_exists('matrix_seed_su_faq_ensure_term')) {
    function matrix_seed_su_faq_ensure_term(string $slug, string $name, int $parent_id = 0): int
    {
        $existing = get_term_by('slug', $slug, 'faq_category');
        if ($existing instanceof WP_Term) {
            if ($parent_id > 0 && (int) $existing->parent !== $parent_id) {
                wp_update_term((int) $existing->term_id, 'faq_category', ['parent' => $parent_id]);
            }

            return (int) $existing->term_id;
        }
        $args = ['slug' => $slug];
        if ($parent_id > 0) {
            $args['parent'] = $parent_id;
        }
        $created = wp_insert_term($name, 'faq_category', $args);

        return is_wp_error($created) ? 0 : (int) ($created['term_id'] ?? 0);
    }
}

if (! function_exists('matrix_seed_su_faq_ensure_post')) {
    function matrix_seed_su_faq_ensure_post(string $title, string $content, string $seed_key, array $term_ids, int $menu_order = 0): int
    {
        $existing = get_posts([
            'post_type' => 'faqs',
            'post_status' => 'any',
            'posts_per_page' => 1,
            'meta_query' => [[
                'key' => '_matrix_seed_key',
                'value' => $seed_key,
            ]],
        ]);
        if ($existing !== []) {
            $faq_id = (int) $existing[0]->ID;
            wp_update_post([
                'ID' => $faq_id,
                'post_title' => $title,
                'post_content' => $content,
                'post_status' => 'publish',
                'menu_order' => $menu_order,
            ]);
        } else {
            $faq_id = (int) wp_insert_post([
                'post_type' => 'faqs',
                'post_status' => 'publish',
                'post_title' => $title,
                'post_content' => $content,
                'menu_order' => $menu_order,
            ]);
            if ($faq_id < 1) {
                return 0;
            }
            update_post_meta($faq_id, '_matrix_seed_key', $seed_key);
        }
        if ($term_ids !== []) {
            wp_set_object_terms($faq_id, array_map('intval', $term_ids), 'faq_category', false);
        }

        return $faq_id;
    }
}

$faq_row = static function (string $heading, array $faq_ids): array {
    return [
        'acf_fc_layout' => 'faqs',
        'show_heading' => 1,
        'layout_style' => 'default',
        'heading' => $heading !== '' ? $heading : 'Frequently Asked Questions (FAQs)',
        'heading_tag' => 'h2',
        'source_mode' => 'selected',
        'selected_faqs' => array_values(array_filter(array_map('intval', $faq_ids))),
        'section_background' => '#FBFAF7',
        'heading_color' => '#1E244B',
        'underline_color' => '#6FC9C0',
        'item_background' => '#FFFFFF',
        'open_item_background' => 'linear-gradient(-42.77deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
        'question_color' => '#1E244B',
        'answer_color' => '#08284B',
    ];
};

/**
 * @return list<array{0:string,1:string}>
 */
$parse_qa_from_html = static function (string $html): array {
    $pairs = [];
    if (preg_match_all('#<h3[^>]*>(.*?)</h3>(.*?)(?=<h3\b|$)#is', $html, $m, PREG_SET_ORDER)) {
        foreach ($m as $match) {
            $q = trim(wp_strip_all_tags($match[1]));
            $a = trim($match[2]);
            if ($q !== '' && $a !== '') {
                $pairs[] = [$q, $a];
            }
        }
        if ($pairs !== []) {
            return $pairs;
        }
    }
    if (preg_match_all('#<p>\s*<strong>(.*?)</strong>\s*</p>\s*(.*?)(?=<p>\s*<strong>|$)#is', $html, $m, PREG_SET_ORDER)) {
        foreach ($m as $match) {
            $q = trim(wp_strip_all_tags($match[1]));
            $a = trim($match[2]);
            if ($q !== '' && $a !== '' && (str_contains($q, '?') || strlen($q) < 120)) {
                $pairs[] = [$q, $a];
            }
        }
    }

    return $pairs;
};

/**
 * @return list<array{0:string,1:string}>
 */
$parse_qa_from_accordion = static function (array $items): array {
    $pairs = [];
    foreach ($items as $item) {
        if (! is_array($item)) {
            continue;
        }
        $q = trim((string) ($item['title'] ?? ''));
        $parts = [];
        foreach ((array) ($item['content_rows'] ?? []) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $html = trim((string) ($row['content'] ?? ''));
            if ($html !== '') {
                $parts[] = $html;
            }
        }
        $a = trim(implode("\n", $parts));
        if ($q !== '' && $a !== '') {
            $pairs[] = [$q, $a];
        }
    }

    return $pairs;
};

$replace_live_links = static function (string $html) use ($home): string {
    $map = [
        'https://www.stpatricks.ie/getting-help/carers-supporters' => $home . '/service-users-and-visitors/carers-and-supporters/',
        'https://www.stpatricks.ie/care-treatment/your-portal/service-user-it-support' => $home . '/service-user-it-support/',
        'https://www.stpatricks.ie/care-treatment/your-portal' => $home . '/about-your-portal/',
        'https://www.stpatricks.ie/care-treatment/medication' => $home . '/service-users-and-visitors/medication/',
        'https://www.stpatricks.ie/about-us' => $home . '/about-us/',
        'https://www.stpatricks.ie/privacy-notice' => $home . '/data-protection-policy/',
        'https://www.stpatricks.ie/get-involved/service-user-participation/service-user-and-supporters-council-suas' => $home . '/get-involved/service-user-participation/service-user-and-supporters-council-suas/',
    ];
    foreach ($map as $from => $to) {
        $html = str_replace([$from, rtrim($from, '/')], rtrim($to, '/'), $html);
    }

    return $html;
};

$is_faq_heading = static function (string $heading): bool {
    return (bool) preg_match('/faq|frequently asked/i', $heading);
};

$seed_key_base = static function (int $post_id, string $heading): string {
    return 'faq-block-' . $post_id . '-' . sanitize_title(substr($heading, 0, 60));
};

$query = new WP_Query([
    'post_type' => 'any',
    'post_status' => ['publish', 'draft'],
    'posts_per_page' => -1,
    'orderby' => 'ID',
    'order' => 'ASC',
]);

$converted_pages = 0;
$converted_blocks = 0;
$created_faqs = 0;

foreach ($query->posts as $post) {
    $rows = get_field('flexible_content_blocks', $post->ID);
    if (! is_array($rows) || $rows === []) {
        continue;
    }

    $new = [];
    $changed = false;
    $page_term = matrix_seed_su_faq_ensure_term(
        'page-' . $post->post_name,
        get_the_title($post) !== '' ? get_the_title($post) : ('Page ' . $post->ID)
    );

    foreach ($rows as $row) {
        if (! is_array($row)) {
            $new[] = $row;
            continue;
        }

        $layout = (string) ($row['acf_fc_layout'] ?? '');
        $heading = trim(wp_strip_all_tags((string) ($row['heading'] ?? '')));

        $pairs = [];
        if ($layout === 'content' && $is_faq_heading($heading)) {
            $pairs = $parse_qa_from_html((string) ($row['content'] ?? ''));
        } elseif ($layout === 'content_accordion' && $is_faq_heading($heading)) {
            $pairs = $parse_qa_from_accordion((array) ($row['items'] ?? []));
        }

        if (count($pairs) < 2) {
            $new[] = $row;
            continue;
        }

        $faq_ids = [];
        $base = $seed_key_base((int) $post->ID, $heading);
        foreach ($pairs as $i => [$q, $a]) {
            $a = $replace_live_links($a);
            $faq_id = matrix_seed_su_faq_ensure_post(
                $q,
                $a,
                $base . '-' . ($i + 1),
                $page_term > 0 ? [$page_term] : [],
                $i + 1
            );
            if ($faq_id > 0) {
                $faq_ids[] = $faq_id;
                $created_faqs++;
            }
        }

        if ($faq_ids === []) {
            $new[] = $row;
            continue;
        }

        $new[] = $faq_row($heading, $faq_ids);
        $changed = true;
        $converted_blocks++;
    }

    if (! $changed) {
        continue;
    }

    update_field('flexible_content_blocks', $new, $post->ID);
    $converted_pages++;
    WP_CLI::log(sprintf(
        '#%d %s → FAQ block(s) converted (%s)',
        $post->ID,
        get_the_title($post),
        get_permalink($post)
    ));
}

WP_CLI::success(sprintf(
    'Converted %d block(s) across %d page(s); upserted %d FAQ posts.',
    $converted_blocks,
    $converted_pages,
    $created_faqs
));
