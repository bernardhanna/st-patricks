<?php

/**
 * Support Us snag: tighter block spacing + direct SUAS/SUAN/FCS page links.
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/fix-support-us-snag-2026-10-23.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

$permalink = static function (string $path): string {
    $page = get_page_by_path($path);

    return $page instanceof WP_Post ? (string) get_permalink($page) : home_url('/' . trim($path, '/') . '/');
};

$support_id = (int) (get_page_by_path('about-us/support-us')?->ID ?? 0);
if ($support_id <= 0) {
    WP_CLI::error('Support us page not found.');
}

$group_urls = [
    'suas' => $permalink('service-users-and-visitors/service-user-participation/service-user-and-supporters-council'),
    'suan' => $permalink('service-users-and-visitors/service-user-participation/service-user-advisory-network'),
    'fcs' => $permalink('service-users-and-visitors/service-user-participation/family-carers-and-supporters-advisory-network'),
];

$rows = get_field('flexible_content_blocks', $support_id);
if (! is_array($rows)) {
    WP_CLI::error('No flexi blocks on Support us.');
}

$engagement_grid_index = null;
$ways_grid_index = null;

foreach ($rows as $index => $row) {
    if (($row['acf_fc_layout'] ?? '') === 'content') {
        $content_heading = trim((string) ($row['heading'] ?? ''));
        if ($content_heading === 'Our fundraising principles and standards' && ($row['vertical_padding'] ?? '') === 'default') {
            $rows[$index]['vertical_padding'] = 'no_bottom';
        }
    }

    if (($row['acf_fc_layout'] ?? '') !== 'about_links_grid') {
        continue;
    }

    $heading = strtolower(trim((string) ($row['heading_text'] ?? '')));

    if ($heading === 'ways to get involved') {
        $ways_grid_index = $index;
        $rows[$index]['vertical_padding'] = 'no_bottom';
        continue;
    }

    if (! str_contains($heading, 'engagement')) {
        continue;
    }

    $engagement_grid_index = $index;
    $rows[$index]['vertical_padding'] = 'no_top';

    $links = $row['links'] ?? [];
    if (! is_array($links)) {
        continue;
    }

    foreach ($links as $link_index => $link) {
        $title = strtolower(trim((string) ($link['title'] ?? '')));
        $destination = $permalink('service-users-and-visitors/service-user-participation');
        foreach ($group_urls as $needle => $url) {
            if (str_contains($title, $needle)) {
                $destination = $url;
                break;
            }
        }

        $link_field = is_array($link['link'] ?? null) ? $link['link'] : [];
        $rows[$index]['links'][$link_index]['link'] = [
            'title' => (string) ($link_field['title'] ?? $link['title'] ?? ''),
            'url' => $destination,
            'target' => (string) ($link_field['target'] ?? ''),
        ];
    }
}

if ($engagement_grid_index === null) {
    WP_CLI::warning('Engagement groups grid not found.');
}

update_field('flexible_content_blocks', $rows, $support_id);

WP_CLI::success(sprintf(
    'Support us #%d updated (ways grid=%s, engagement grid=%s).',
    $support_id,
    $ways_grid_index === null ? 'missing' : (string) $ways_grid_index,
    $engagement_grid_index === null ? 'missing' : (string) $engagement_grid_index
));
