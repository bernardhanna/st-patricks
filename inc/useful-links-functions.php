<?php

function matrix_normalize_useful_link_compare_url(string $url): string
{
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
    if ($host === 'localhost' || $host === '127.0.0.1' || str_contains($host, 'matrix-test.com')) {
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
}

function matrix_useful_link_is_current_page(string $link_url, string $current_url = '', int $current_id = 0): bool
{
    $link_url = trim($link_url);
    if ($link_url === '') {
        return false;
    }

    if ($current_url === '' && function_exists('is_singular') && is_singular()) {
        $current_url = (string) get_permalink();
        $current_id = (int) get_queried_object_id();
    }

    $current_url = trim($current_url);
    if ($current_url === '') {
        return false;
    }

    if (untrailingslashit($link_url) === untrailingslashit($current_url)) {
        return true;
    }

    if (matrix_normalize_useful_link_compare_url($link_url) === matrix_normalize_useful_link_compare_url($current_url)) {
        return true;
    }

    if ($current_id > 0 && function_exists('url_to_postid')) {
        $linked_id = (int) url_to_postid($link_url);
        if ($linked_id > 0 && $linked_id === $current_id) {
            return true;
        }
    }

    return false;
}

function matrix_normalize_useful_links($rows, $exclude_url = null, $exclude_post_id = 0)
{
    $items = [];

    if ($exclude_url === null && function_exists('is_singular') && is_singular()) {
        $exclude_url = (string) get_permalink();
        $exclude_post_id = (int) get_queried_object_id();
    }

    $exclude_url = is_string($exclude_url) ? $exclude_url : '';
    $exclude_post_id = (int) $exclude_post_id;

    foreach ((array) $rows as $row) {
        if (! is_array($row)) {
            continue;
        }

        $link = $row['link'] ?? null;

        if (! is_array($link) || empty($link['url'])) {
            continue;
        }

        $title = trim((string) ($link['title'] ?? ''));

        if ($title === '') {
            continue;
        }

        $url = (string) $link['url'];

        if ($exclude_url !== '' && matrix_useful_link_is_current_page($url, $exclude_url, $exclude_post_id)) {
            continue;
        }

        $items[] = [
            'url' => $url,
            'title' => $title,
            'target' => matrix_normalize_link_target($url, (string) ($link['target'] ?? '')),
        ];
    }

    return $items;
}

function matrix_get_search_results_useful_links_defaults()
{
    $home = function_exists('home_url') ? home_url('/') : '/';

    $link = static function (string $title, string $path) use ($home): array {
        return [
            'link' => [
                'title' => $title,
                'url' => $home . ltrim($path, '/'),
                'target' => '',
            ],
        ];
    };

    return [
        'section_id' => 'search-results-useful-links',
        'data_block' => 'search-results-useful-links',
        'heading' => 'Useful links',
        'heading_tag' => 'h2',
        'background_color' => '#E9E2F7',
        'heading_color' => '#1E244B',
        'link_color' => '#1E244B',
        'variant' => 'search',
        'links' => [
            $link('Day Programmes', 'what-we-offer/day-programmes/'),
            $link('Inpatient Care', 'inpatient-care/'),
            $link('Outpatient Care - Dean Clinics', 'what-we-offer/outpatient-care-dean-clinics/'),
            $link('About Your Portal', 'about-your-portal/'),
            [
                'link' => [
                    'title' => 'Make a Payment',
                    'url' => 'https://buy.stripe.com/aFa4gy8Yide50e9erjbwk00',
                    'target' => '_blank',
                ],
            ],
            $link('Directions and Parking', 'directions-and-parking/'),
            $link('Clinician Insights', 'healthcare-professionals/clinician-insights/'),
            $link('Sitemap (All website links)', 'sitemap/'),
            $link('Media Queries', 'about-us/media-queries/'),
        ],
    ];
}

function matrix_prepare_useful_links_section(array $config = [])
{
    $defaults = matrix_get_search_results_useful_links_defaults();
    $section = array_merge($defaults, $config);

    if (matrix_normalize_useful_links($section['links'] ?? []) === []) {
        return null;
    }

    return $section;
}

function matrix_render_useful_links_section(array $section)
{
    if ($section === []) {
        return '';
    }

    $template = function_exists('locate_template')
        ? locate_template('template-parts/useful-links/section.php')
        : '';

    if ($template === '' || ! is_readable($template)) {
        $template = dirname(__DIR__) . '/template-parts/useful-links/section.php';
    }

    if (! is_readable($template)) {
        return '';
    }

    ob_start();
    $args = ['useful_links' => $section];
    include $template;

    return (string) ob_get_clean();
}
