<?php

function matrix_normalize_useful_links($rows)
{
    $items = [];

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
