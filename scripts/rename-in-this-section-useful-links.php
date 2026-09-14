<?php

/**
 * Rename "In this section" → "Useful Links", move to bottom (before newsletter),
 * and remove any useful-link that points at the current page.
 *
 * Usage:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/rename-in-this-section-useful-links.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

$target_heading = 'Useful Links';

$normalize_compare_url = static function (string $url): string {
    $url = trim(html_entity_decode($url));
    if ($url === '') {
        return '';
    }

    $parts = wp_parse_url($url);
    if (! is_array($parts)) {
        return untrailingslashit(strtolower($url));
    }

    $host = strtolower((string) ($parts['host'] ?? ''));
    $host = preg_replace('#^www\.#', '', $host) ?: $host;
    // Treat local/staging hosts as path-only matches when comparing self-links.
    if ($host === 'localhost' || str_contains($host, 'matrix-test.com') || $host === '127.0.0.1') {
        $host = '';
    }

    $path = (string) ($parts['path'] ?? '/');
    if ($path === '') {
        $path = '/';
    }
    $path = untrailingslashit(strtolower($path));
    if ($path === '') {
        $path = '/';
    }

    return $host . $path;
};

$urls_are_same_page = static function (string $link_url, string $current_url, int $current_id) use ($normalize_compare_url): bool {
    $link_url = trim($link_url);
    $current_url = trim($current_url);
    if ($link_url === '' || $current_url === '') {
        return false;
    }

    if (untrailingslashit($link_url) === untrailingslashit($current_url)) {
        return true;
    }

    if ($normalize_compare_url($link_url) === $normalize_compare_url($current_url)) {
        return true;
    }

    if ($current_id > 0) {
        $linked_id = url_to_postid($link_url);
        if ($linked_id > 0 && $linked_id === $current_id) {
            return true;
        }
    }

    return false;
};

$strip_self_links = static function (array $row, string $current_url, int $current_id) use ($urls_are_same_page): array {
    if (($row['acf_fc_layout'] ?? '') !== 'useful_links' || empty($row['links']) || ! is_array($row['links'])) {
        return $row;
    }

    $kept = [];
    foreach ($row['links'] as $item) {
        if (! is_array($item)) {
            continue;
        }
        $url = (string) ($item['link']['url'] ?? '');
        if ($url !== '' && $urls_are_same_page($url, $current_url, $current_id)) {
            continue;
        }
        $kept[] = $item;
    }
    $row['links'] = $kept;

    return $row;
};

$is_in_this_section = static function (array $row): bool {
    if (($row['acf_fc_layout'] ?? '') !== 'useful_links') {
        return false;
    }
    $heading = trim(wp_strip_all_tags((string) ($row['heading'] ?? '')));

    return $heading !== '' && stripos($heading, 'In this section') !== false;
};

$is_newsletter_like = static function (array $row): bool {
    $layout = (string) ($row['acf_fc_layout'] ?? '');
    if ($layout === 'contact_form' && ($row['form_style'] ?? '') === 'mailing_list') {
        return true;
    }
    if (stripos($layout, 'newsletter') !== false) {
        return true;
    }
    $heading = trim(wp_strip_all_tags((string) ($row['heading'] ?? ($row['heading_text'] ?? ''))));
    if ($heading !== '' && (stripos($heading, 'newsletter') !== false || stripos($heading, 'mailing list') !== false)) {
        return in_array($layout, ['contact_form', 'content_cta', 'content'], true);
    }

    return false;
};

/**
 * @param list<array<string,mixed>> $rows
 * @param list<array<string,mixed>> $useful_rows
 * @return list<array<string,mixed>>
 */
$place_before_newsletter = static function (array $rows, array $useful_rows) use ($is_newsletter_like): array {
    if ($useful_rows === []) {
        return $rows;
    }

    $insert_at = count($rows);
    for ($i = count($rows) - 1; $i >= 0; $i--) {
        if ($is_newsletter_like($rows[$i])) {
            $insert_at = $i;
            continue;
        }
        break;
    }

    return array_merge(
        array_slice($rows, 0, $insert_at),
        $useful_rows,
        array_slice($rows, $insert_at)
    );
};

$query = new WP_Query([
    'post_type' => 'any',
    'post_status' => ['publish', 'draft', 'pending', 'private', 'future'],
    'posts_per_page' => -1,
    'orderby' => 'ID',
    'order' => 'ASC',
    'fields' => 'ids',
]);

$updated = 0;
$renamed_moved = 0;
$self_links_removed = 0;

foreach ($query->posts as $post_id) {
    $post_id = (int) $post_id;
    $rows = get_field('flexible_content_blocks', $post_id);
    if (! is_array($rows) || $rows === []) {
        continue;
    }

    $current_url = (string) get_permalink($post_id);
    $useful_rows = [];
    $remaining = [];
    $changed = false;
    $removed_here = 0;

    foreach ($rows as $row) {
        if (! is_array($row)) {
            $remaining[] = $row;
            continue;
        }

        $before_count = is_array($row['links'] ?? null) ? count($row['links']) : 0;
        $row = $strip_self_links($row, $current_url, $post_id);
        $after_count = is_array($row['links'] ?? null) ? count($row['links']) : 0;
        if ($after_count < $before_count) {
            $removed_here += ($before_count - $after_count);
            $changed = true;
        }

        if ($is_in_this_section($row)) {
            $row['heading'] = $target_heading;
            $useful_rows[] = $row;
            $changed = true;
            continue;
        }

        // Also move existing "Useful Links" / "Useful links" blocks to bottom.
        $heading = trim(wp_strip_all_tags((string) ($row['heading'] ?? '')));
        if (($row['acf_fc_layout'] ?? '') === 'useful_links' && strcasecmp($heading, 'Useful Links') === 0) {
            $row['heading'] = $target_heading;
            $useful_rows[] = $row;
            $changed = true;
            continue;
        }
        if (($row['acf_fc_layout'] ?? '') === 'useful_links' && strcasecmp($heading, 'Useful links') === 0) {
            $row['heading'] = $target_heading;
            $useful_rows[] = $row;
            $changed = true;
            continue;
        }

        $remaining[] = $row;
    }

    if (! $changed && $useful_rows === []) {
        continue;
    }

    if ($useful_rows !== []) {
        $new_rows = $place_before_newsletter($remaining, $useful_rows);
        $renamed_moved++;
    } else {
        $new_rows = $remaining;
    }

    update_field('flexible_content_blocks', $new_rows, $post_id);
    $updated++;
    $self_links_removed += $removed_here;

    WP_CLI::log(sprintf(
        '#%d %s — useful blocks:%d self-links removed:%d',
        $post_id,
        get_the_title($post_id),
        count($useful_rows),
        $removed_here
    ));
}

WP_CLI::success(sprintf(
    'Updated %d posts (renamed/moved Useful Links on %d; removed %d self-links).',
    $updated,
    $renamed_moved,
    $self_links_removed
));
