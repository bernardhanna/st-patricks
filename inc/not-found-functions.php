<?php

/**
 * Normalize helpful links stored on the 404 options screen.
 *
 * @param mixed $rows
 * @return array<int, array<string, mixed>>
 */
function matrix_normalize_not_found_acf_links($rows)
{
    $items = [];

    foreach ((array) $rows as $row) {
        if (! is_array($row)) {
            continue;
        }

        $link = $row['link_data'] ?? null;

        if (! is_array($link) || empty($link['url'])) {
            continue;
        }

        $title = trim((string) ($link['title'] ?? ''));

        if ($title === '') {
            continue;
        }

        $url = (string) $link['url'];

        $items[] = [
            'link' => [
                'title' => $title,
                'url' => $url,
                'target' => matrix_normalize_link_target($url, (string) ($link['target'] ?? '')),
            ],
        ];
    }

    return $items;
}

/**
 * Build the default 404 heading in the same format as empty search results.
 *
 * @param string $custom_title
 * @return array{prefix: string, query: string, plain: string}
 */
function matrix_get_not_found_heading_data($custom_title = '')
{
    $custom_title = trim($custom_title);
    $legacy_default = 'Sorry, We Can\u{2019}t Find That Page.';

    if ($custom_title !== '' && $custom_title !== $legacy_default) {
        return [
            'prefix' => $custom_title,
            'query' => '',
            'plain' => $custom_title,
        ];
    }

    return [
        'prefix' => "We couldn\u{2019}t find a match for",
        'query' => 'this page',
        'plain' => '',
    ];
}

/**
 * Prepare useful links for the 404 page.
 *
 * @param array<int, array<string, mixed>> $acf_links
 * @return array<string, mixed>|null
 */
function matrix_prepare_not_found_useful_links(array $acf_links = [])
{
    $defaults = matrix_get_search_results_useful_links_defaults();
    $links = $acf_links !== [] ? $acf_links : ($defaults['links'] ?? []);

    return matrix_prepare_useful_links_section([
        'section_id' => 'not-found-useful-links',
        'data_block' => 'not-found-useful-links',
        'heading' => $defaults['heading'] ?? 'Useful links',
        'heading_tag' => $defaults['heading_tag'] ?? 'h2',
        'background_color' => $defaults['background_color'] ?? '#E9E2F7',
        'heading_color' => $defaults['heading_color'] ?? '#1E244B',
        'link_color' => $defaults['link_color'] ?? '#1E244B',
        'variant' => 'search',
        'links' => $links,
    ]);
}

/**
 * Prepare view-model data for the 404 template.
 *
 * @return array<string, mixed>
 */
function matrix_prepare_not_found_page()
{
    $settings = function_exists('get_field') ? (array) (get_field('not_found_settings', 'option') ?: []) : [];
    $custom_title = trim((string) ($settings['hero_title'] ?? ''));
    $acf_links = matrix_normalize_not_found_acf_links($settings['links'] ?? []);

    return [
        'heading' => matrix_get_not_found_heading_data($custom_title),
        'search_base_url' => matrix_get_search_results_base_url(),
        'search_query' => '',
        'home_url' => function_exists('home_url') ? home_url('/') : '/',
        'useful_links' => matrix_prepare_not_found_useful_links($acf_links),
    ];
}
