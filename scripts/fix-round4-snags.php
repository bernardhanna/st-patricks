<?php

/**
 * Apply round 4 snags, stories hero, and current-vacancies CTA anchors.
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/fix-round4-snags.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

$permalink = static function (string $path): string {
    $page = get_page_by_path($path);
    if ($page instanceof WP_Post) {
        return (string) get_permalink($page);
    }

    return home_url('/' . trim($path, '/') . '/');
};
$save = static function (int $post_id, array $rows, string $label): void {
    update_field('flexible_content_blocks', $rows, $post_id);
    WP_CLI::success($label . ' #' . $post_id);
};
$hero_image_id = static function (array $hero): int {
    $image = $hero['hero_image'] ?? '';
    if (is_array($image)) {
        return (int) ($image['ID'] ?? $image['id'] ?? 0);
    }

    return (int) $image;
};
$apply_hero_image = static function (array &$hero, int $image_id, string $alt = '') use ($hero_image_id): void {
    if ($image_id <= 0 || get_post_type($image_id) !== 'attachment') {
        return;
    }

    $hero['hero_image'] = $image_id;
    $hero['layout_style'] = 'image_split';

    $existing_alt = trim((string) get_post_meta($image_id, '_wp_attachment_image_alt', true));
    if ($existing_alt === '' && $alt !== '') {
        update_post_meta($image_id, '_wp_attachment_image_alt', $alt);
    }
};

$ensure_hero = static function (string $path, int $image_id, string $label, string $alt = '') use ($apply_hero_image, $save): void {
    $page = get_page_by_path($path);
    if (! $page instanceof WP_Post) {
        WP_CLI::warning('Missing page ' . $path);

        return;
    }

    $rows = get_field('flexible_content_blocks', $page->ID);
    if (! is_array($rows) || ! isset($rows[0]) || ($rows[0]['acf_fc_layout'] ?? '') !== 'hero_with_breadcrumbs') {
        WP_CLI::warning('No hero on ' . $path);

        return;
    }

    $apply_hero_image($rows[0], $image_id, $alt);
    $save((int) $page->ID, $rows, $label);
};

$vacancies_path = '/about-us/careers/#current-vacancies';
$is_careers_landing_url = static function (string $url): bool {
    $parts = wp_parse_url($url);
    $path = untrailingslashit((string) ($parts['path'] ?? ''));
    $fragment = trim((string) ($parts['fragment'] ?? ''));

    if ($fragment !== '' && $fragment !== 'current-vacancies') {
        return false;
    }

    return (bool) preg_match('#/(?:about-us/)?careers$#', $path);
};
$rewrite_vacancy_link = static function (array $link) use ($vacancies_path, $is_careers_landing_url): array {
    $title = strtolower(trim((string) ($link['title'] ?? '')));
    $url = trim((string) ($link['url'] ?? ''));
    $is_vacancy_title = str_contains($title, 'vacanc');
    $is_here_to_careers = $title === 'here' && $is_careers_landing_url($url);

    if ($is_vacancy_title || $is_here_to_careers) {
        $link['url'] = $vacancies_path;
    }

    return $link;
};
$rewrite_vacancy_html = static function (string $html) use ($vacancies_path): string {
    if ($html === '' || (! str_contains(strtolower($html), 'vacanc') && ! str_contains(strtolower($html), 'careers'))) {
        return $html;
    }

    return (string) preg_replace_callback(
        '#<a([^>]+)href=(["\'])([^"\']+)\2([^>]*)>(.*?)</a>#is',
        static function (array $match) use ($vacancies_path): string {
            $url = html_entity_decode((string) $match[3]);
            $label = strtolower(trim(wp_strip_all_tags((string) $match[5])));
            $path = untrailingslashit((string) (wp_parse_url($url, PHP_URL_PATH) ?? ''));
            $is_careers = (bool) preg_match('#/(?:about-us/)?careers$#', $path);
            $is_vacancy_copy = str_contains($label, 'vacanc') || $label === 'here';

            if ($is_careers && $is_vacancy_copy) {
                return '<a' . $match[1] . 'href=' . $match[2] . $vacancies_path . $match[2] . $match[4] . '>' . $match[5] . '</a>';
            }

            return $match[0];
        },
        $html
    ) ?? $html;
};

$walk_rows = static function (array $rows) use (&$walk_rows, $rewrite_vacancy_link, $rewrite_vacancy_html): array {
    foreach ($rows as $index => $row) {
        if (! is_array($row)) {
            continue;
        }

        foreach (['primary_button', 'secondary_button', 'button', 'button_link'] as $key) {
            if (isset($row[$key]) && is_array($row[$key])) {
                $row[$key] = $rewrite_vacancy_link($row[$key]);
            }
        }

        foreach (['content', 'intro_text'] as $html_key) {
            if (isset($row[$html_key]) && is_string($row[$html_key])) {
                $row[$html_key] = $rewrite_vacancy_html($row[$html_key]);
            }
        }

        if (isset($row['link']) && is_array($row['link'])) {
            $row['link'] = $rewrite_vacancy_link($row['link']);
        }

        foreach (['links', 'cards', 'items', 'content_rows', 'link_cards'] as $nested_key) {
            if (isset($row[$nested_key]) && is_array($row[$nested_key])) {
                $row[$nested_key] = $walk_rows($row[$nested_key]);
            }
        }

        $rows[$index] = $row;
    }

    return $rows;
};

// --- Heroes ---
$ensure_hero(
    'service-users-and-visitors/frequently-asked-questions-faqs',
    743,
    'Service Users FAQs hero',
    'St Patrick’s Mental Health Services buildings, clinic, and online support.'
);
$ensure_hero(
    'service-users-and-visitors/stories-and-support',
    567,
    'Stories and Support hero',
    'People sharing stories and support at St Patrick’s Mental Health Services.'
);
$ensure_hero(
    'service-users-and-visitors/service-user-participation',
    4092,
    'Service user participation hero',
    'Service users taking part in engagement and participation at St Patrick’s Mental Health Services.'
);
$ensure_hero(
    'what-we-offer/outpatient-care-dean-clinics',
    3131,
    'Dean Clinics hero',
    'St Patrick’s Dean Clinic.'
);
$ensure_hero(
    'what-we-offer/st-patricks-at-home',
    3692,
    'St Patrick’s at Home hero',
    'St Patrick’s at Home homecare service.'
);

$suan_page = get_page_by_path('service-users-and-visitors/service-user-participation/service-user-advisory-network');
if ($suan_page instanceof WP_Post) {
    $suan = get_field('flexible_content_blocks', $suan_page->ID);
    if (is_array($suan)) {
        $hero = $suan[0] ?? [];
        if (($hero['acf_fc_layout'] ?? '') === 'hero_with_breadcrumbs') {
            $existing = $hero_image_id($hero);
            if ($existing <= 0) {
                $apply_hero_image($hero, 3934, 'Service User Advisory Network members.');
            } else {
                $hero['layout_style'] = 'image_split';
            }
            $suan[0] = $hero;
        }

        $what_html = '';
        $how_html = '';
        $suas_button = null;
        $new_rows = [];
        foreach ($suan as $row) {
            $layout = $row['acf_fc_layout'] ?? '';
            $heading = trim((string) ($row['heading'] ?? ''));
            if ($layout === 'content' && $heading === 'What is SUAN?') {
                $what_html = (string) ($row['content'] ?? '');
                continue;
            }
            if ($layout === 'content' && $heading === 'How do I join SUAN?') {
                $how_html = (string) ($row['content'] ?? '');
                $suas_button = is_array($row['primary_button'] ?? null) ? $row['primary_button'] : null;
                continue;
            }
            if ($layout === 'content_accordion') {
                $titles = array_map(
                    static fn ($item): string => is_array($item) ? trim((string) ($item['title'] ?? '')) : '',
                    is_array($row['items'] ?? null) ? $row['items'] : []
                );
                if (in_array('What is SUAN?', $titles, true) && in_array('How do I join SUAN?', $titles, true)) {
                    continue;
                }
            }
            $new_rows[] = $row;
        }

        if ($what_html !== '' || $how_html !== '') {
            if (is_array($suas_button) && trim((string) ($suas_button['url'] ?? '')) !== '' && trim((string) ($suas_button['title'] ?? '')) !== '') {
                $how_html .= '<p><a class="' . esc_attr(matrix_get_content_button_class_names('filled')) . '" href="' . esc_url((string) $suas_button['url']) . '">' . esc_html((string) $suas_button['title']) . '</a></p>';
            }
            $accordion = matrix_orlaith_accordion_row([
                'What is SUAN?' => $what_html,
                'How do I join SUAN?' => $how_html,
            ]);
            $accordion['vertical_padding'] = 'compact';
            array_splice($new_rows, 1, 0, [$accordion]);
        }

        $save((int) $suan_page->ID, $new_rows, 'SUAN accordions');
    }
}

// --- Vacancies CTAs to #current-vacancies ---
$query = new WP_Query([
    'post_type' => ['page', 'post'],
    'post_status' => ['publish', 'draft'],
    'posts_per_page' => -1,
    'fields' => 'ids',
]);
foreach ($query->posts as $post_id) {
    $post_id = (int) $post_id;
    $rows = get_field('flexible_content_blocks', $post_id);
    if (! is_array($rows)) {
        continue;
    }
    $updated = $walk_rows($rows);
    if ($updated !== $rows) {
        $save($post_id, $updated, 'Vacancies anchors ' . get_the_title($post_id));
    }
}

WP_CLI::success('Round 4 snag updates complete.');
