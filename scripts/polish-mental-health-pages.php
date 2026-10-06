<?php

/**
 * Minor MH polish: cream stacking, Useful links labels/URLs, hero image alts.
 *
 * Usage: wp eval-file wp-content/themes/matrix-starter/scripts/polish-mental-health-pages.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

$white = '#FFFFFF';

/**
 * @return array<string, string> label => absolute URL (or path fallback)
 */
function matrix_polish_mh_useful_links_for(string $current_slug): array
{
    $all = [
        'addiction-dual-diagnosis' => 'Addiction & dual diagnosis',
        'anxiety' => 'Anxiety',
        'bipolar-disorder' => 'Bipolar disorder',
        'depression' => 'Depression',
        'eating-disorders' => 'Eating disorders',
        'personality-disorders' => 'Personality disorders',
        'schizophrenia-psychosis' => 'Psychosis',
        'schizophrenia' => 'Schizophrenia',
        'young-adults' => 'Young adults',
        'older-adults' => 'Older adults',
    ];

    $links = [];
    foreach ($all as $slug => $label) {
        if ($slug === $current_slug) {
            continue;
        }

        $posts = get_posts([
            'name' => $slug,
            'post_type' => 'mental_health',
            'post_status' => 'publish',
            'posts_per_page' => 1,
        ]);

        if ($posts !== []) {
            $links[$label] = (string) get_permalink($posts[0]);
            continue;
        }

        // Fallback path still resolves via CPT-aware matrix_orlaith_permalink.
        $links[$label] = 'mental-health/' . $slug;
    }

    return $links;
}

/**
 * Break consecutive cream section backgrounds (leave intentional white/cream patterns alone).
 *
 * @param array<int, array<string, mixed>> $rows
 * @return array<int, array<string, mixed>>
 */
function matrix_polish_mh_break_cream_stacks(array $rows, string $white): array
{
    $prev_was_cream = false;

    foreach ($rows as $index => $row) {
        if (! is_array($row)) {
            continue;
        }

        $layout = (string) ($row['acf_fc_layout'] ?? '');
        if ($layout === 'hero_with_breadcrumbs' || $layout === 'useful_links') {
            $prev_was_cream = false;
            continue;
        }

        $bg_key = null;
        if (array_key_exists('section_background', $row)) {
            $bg_key = 'section_background';
        } elseif (array_key_exists('background_color', $row)) {
            $bg_key = 'background_color';
        } elseif (array_key_exists('background_colour', $row)) {
            $bg_key = 'background_colour';
        }

        if ($bg_key === null) {
            $prev_was_cream = false;
            continue;
        }

        $current = strtoupper((string) $row[$bg_key]);
        $is_cream = str_contains($current, 'FBFAF7') || str_contains($current, 'FBF8F3');

        if ($is_cream && $prev_was_cream) {
            $rows[$index][$bg_key] = $white;
            $prev_was_cream = false;
            continue;
        }

        $prev_was_cream = $is_cream;
    }

    return $rows;
}

$hero_alts = [
    764 => 'Person standing outdoors, representing living with psychosis',
    766 => 'Illustration representing bipolar disorder',
    761 => 'Person representing eating disorders support and recovery',
    762 => 'Person representing addiction and dual diagnosis support',
    758 => 'Person representing depression and recovery',
    2204 => 'Illustration explaining personality disorders',
    1060 => 'Illustration representing schizophrenia',
    763 => 'Young adults together, representing young adult mental health',
    760 => 'Older adult, representing later-life mental health',
    2222 => 'Illustration of fight, flight and future-focused anxiety',
    818 => 'Illustration about stress, anxiety and recovery',
];

foreach ($hero_alts as $attachment_id => $alt) {
    $existing = trim((string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true));
    if ($existing !== '') {
        WP_CLI::log("Skip alt {$attachment_id} (already set)");
        continue;
    }
    update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt);
    WP_CLI::success("Set alt on attachment {$attachment_id}");
}

$slugs = [
    'schizophrenia-psychosis',
    'bipolar-disorder',
    'eating-disorders',
    'addiction-dual-diagnosis',
    'depression',
    'personality-disorders',
    'anxiety',
    'schizophrenia',
    'young-adults',
    'older-adults',
];

foreach ($slugs as $slug) {
    $posts = get_posts([
        'post_type' => 'mental_health',
        'name' => $slug,
        'posts_per_page' => 1,
        'post_status' => 'any',
    ]);

    if ($posts === []) {
        WP_CLI::warning("Missing mental_health/{$slug}");
        continue;
    }

    $post_id = (int) $posts[0]->ID;
    $rows = get_field('flexible_content_blocks', $post_id);
    if (! is_array($rows) || $rows === []) {
        WP_CLI::warning("No flexi on {$slug}");
        continue;
    }

    $rows = matrix_polish_mh_break_cream_stacks($rows, $white);

    foreach ($rows as $index => $row) {
        if (($row['acf_fc_layout'] ?? '') !== 'useful_links') {
            continue;
        }

        $links = matrix_polish_mh_useful_links_for($slug);
        $rows[$index] = matrix_orlaith_useful_links_row($links, 'Useful links');
        $rows[$index]['background_color'] = '#F1F8F9';
    }

    // Ensure section headings stay h2 under the hero h1 (correct outline).
    foreach ($rows as $index => $row) {
        $layout = (string) ($row['acf_fc_layout'] ?? '');
        if ($layout === 'hero_with_breadcrumbs') {
            $rows[$index]['heading_tag'] = 'h1';
            continue;
        }
        if (isset($row['heading_tag']) && $row['heading_tag'] !== '') {
            $rows[$index]['heading_tag'] = 'h2';
        }
    }

    // Nested accordion WYSIWYG: if panel content jumps h2→h4 with no h3, use h3.
    foreach ($rows as $index => $row) {
        if (($row['acf_fc_layout'] ?? '') !== 'content_accordion') {
            continue;
        }
        foreach (($row['items'] ?? []) as $item_index => $item) {
            foreach (($item['content_rows'] ?? []) as $row_index => $content_row) {
                $content = $content_row['content'] ?? '';
                if (! is_string($content) || ! preg_match('/<h4\b/i', $content) || preg_match('/<h3\b/i', $content)) {
                    continue;
                }
                $rows[$index]['items'][$item_index]['content_rows'][$row_index]['content'] = preg_replace(
                    ['/<h4\b/i', '/<\/h4>/i'],
                    ['<h3', '</h3>'],
                    $content
                );
            }
        }
    }

    delete_field('flexible_content_blocks', $post_id);
    update_field('flexible_content_blocks', $rows, $post_id);
    WP_CLI::success("Polished {$slug} ({$post_id})");
}

WP_CLI::log('Done.');
