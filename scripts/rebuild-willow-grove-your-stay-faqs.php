<?php

/**
 * Convert Willow Grove adolescent Your Stay Q&As into a faqs flexi block,
 * and set Anxiety Information Booklet overview to contained image beside text.
 */

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

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
        'heading' => $heading,
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

$page_id = 3939;
$rows = get_field('flexible_content_blocks', $page_id) ?: [];
$parent = matrix_seed_su_faq_ensure_term('willow-grove-your-stay', 'Willow Grove Your Stay');
$term_ids = array_values(array_filter([$parent]));

$new_rows = [];
$converted = 0;

foreach ($rows as $row) {
    if (($row['acf_fc_layout'] ?? '') !== 'content') {
        $new_rows[] = $row;
        continue;
    }

    $heading = trim((string) ($row['heading'] ?? ''));
    $html = (string) ($row['content'] ?? '');

    if ($heading !== 'Your stay in Willow Grove' || ! preg_match('#<h3\b#i', $html)) {
        $new_rows[] = $row;
        continue;
    }

    $pairs = [];
    if (preg_match_all('#<h3[^>]*>(.*?)</h3>(.*?)(?=<h3\b|$)#is', $html, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $q = trim(wp_strip_all_tags($match[1]));
            $a = trim($match[2]);
            if ($q !== '' && $a !== '') {
                $pairs[] = [$q, $a];
            }
        }
    }

    if ($pairs === []) {
        $new_rows[] = $row;
        continue;
    }

    $intro = trim((string) preg_replace('#<h3\b.*#is', '', $html));
    $row['content'] = $intro !== '' ? $intro : '<p>You can find out more below about your stay in Willow Grove.</p>';
    $row['column_layout'] = 'one_column';
    $row['text_width'] = 'full';
    $row['image'] = false;
    $row['vertical_padding'] = 'no_bottom';
    $new_rows[] = $row;

    $faq_ids = [];
    foreach ($pairs as $index => [$question, $answer]) {
        $seed_key = 'willow-grove-your-stay-' . sanitize_title($question);
        $faq_id = matrix_seed_su_faq_ensure_post($question, $answer, $seed_key, $term_ids, $index + 1);
        if ($faq_id > 0) {
            $faq_ids[] = $faq_id;
        }
    }

    $new_rows[] = $faq_row('Common questions about your stay', $faq_ids);
    $converted = count($faq_ids);
}

update_field('flexible_content_blocks', $new_rows, $page_id);
WP_CLI::success("Page {$page_id}: converted {$converted} FAQs into faqs block.");

$anxiety_id = 1417;
$anxiety_rows = get_field('flexible_content_blocks', $anxiety_id) ?: [];
foreach ($anxiety_rows as &$anxiety_row) {
    if (($anxiety_row['acf_fc_layout'] ?? '') !== 'content') {
        continue;
    }
    if (trim((string) ($anxiety_row['heading'] ?? '')) !== 'Overview') {
        continue;
    }
    $anxiety_row['column_layout'] = 'two_column';
    $anxiety_row['layout_style'] = 'image_right';
    $anxiety_row['image_height_mode'] = 'contain';
    $anxiety_row['text_width'] = 'full';
}
unset($anxiety_row);
update_field('flexible_content_blocks', $anxiety_rows, $anxiety_id);
WP_CLI::success("Page {$anxiety_id}: Overview set to contain image beside text (text left).");
