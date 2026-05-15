<?php

require_once dirname(__DIR__, 2) . '/inc/search-results-functions.php';
require_once dirname(__DIR__, 2) . '/inc/useful-links-functions.php';

if (! function_exists('home_url')) {
    function home_url($path = '/')
    {
        return 'http://localhost:10034' . $path;
    }
}

if (! function_exists('esc_url')) {
    function esc_url($value)
    {
        return (string) $value;
    }
}

if (! function_exists('esc_attr')) {
    function esc_attr($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (! function_exists('esc_html')) {
    function esc_html($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

function matrix_render_search_results_template_for_test($search_results)
{
    $args = ['search_results' => $search_results];

    ob_start();
    require dirname(__DIR__, 2) . '/template-parts/search/results.php';

    return (string) ob_get_clean();
}

test('search results resolve state from native and custom query vars', function () {
    expect(function_exists('matrix_resolve_search_results_state'))->toBeTrue();

    $state = matrix_resolve_search_results_state([
        's' => '  referral  ',
        'search_type' => 'webinars',
        'search_sort' => 'date',
        'paged' => '3',
    ]);

    expect($state)->toMatchArray([
        'query' => 'referral',
        'type' => 'webinars',
        'sort' => 'date',
        'paged' => 3,
    ]);
});

test('search results fall back to all and relevance for invalid values', function () {
    $state = matrix_resolve_search_results_state([
        's' => 'help',
        'search_type' => 'unknown',
        'search_sort' => 'popular',
        'paged' => '0',
    ]);

    expect($state)->toMatchArray([
        'query' => 'help',
        'type' => 'all',
        'sort' => 'relevance',
        'paged' => 1,
    ]);
});

test('search results build query args for a webinars date sort', function () {
    expect(function_exists('matrix_build_search_results_query_args'))->toBeTrue();

    $args = matrix_build_search_results_query_args([
        'query' => 'mental health',
        'type' => 'webinars',
        'sort' => 'date',
        'paged' => 2,
    ]);

    expect($args['s'])->toBe('mental health')
        ->and($args['post_type'])->toBe(['webinars'])
        ->and($args['paged'])->toBe(2)
        ->and($args['orderby'])->toBe('date')
        ->and($args['order'])->toBe('DESC');
});

test('search results build a guaranteed empty query for blank searches', function () {
    $args = matrix_build_search_results_query_args([
        'query' => '   ',
        'type' => 'all',
        'sort' => 'relevance',
        'paged' => 1,
    ]);

    expect($args['post_type'])->toBe(['post', 'page', 'webinars'])
        ->and($args['post_status'])->toBe('publish')
        ->and($args['paged'])->toBe(1)
        ->and($args['post__in'])->toBe([0])
        ->and(array_key_exists('s', $args))->toBeFalse();
});

test('search results pagination urls preserve search state', function () {
    expect(function_exists('matrix_build_search_results_page_url'))->toBeTrue();

    $url = matrix_build_search_results_page_url(
        'http://localhost:10034/?s=referral',
        [
            'query' => 'referral',
            'type' => 'page',
            'sort' => 'date',
            'paged' => 1,
        ],
        4
    );

    expect($url)->toContain('s=referral')
        ->and($url)->toContain('search_type=page')
        ->and($url)->toContain('search_sort=date')
        ->and($url)->toContain('paged=4');
});

test('search results expose heading copy for results and empty states', function () {
    expect(function_exists('matrix_get_search_results_heading_data'))->toBeTrue();

    $results_heading = matrix_get_search_results_heading_data('how to make a referral', true);
    $empty_heading = matrix_get_search_results_heading_data('prescriptions and medication', false);

    expect($results_heading)->toMatchArray([
        'prefix' => 'Search result for',
        'query' => 'how to make a referral',
    ]);

    expect($empty_heading)->toMatchArray([
        'prefix' => "We couldn\u{2019}t find a match for",
        'query' => 'prescriptions and medication',
    ]);
});

test('search results heading falls back to a neutral label for empty queries', function () {
    $results_heading = matrix_get_search_results_heading_data('   ', true);
    $empty_heading = matrix_get_search_results_heading_data('', false);

    expect($results_heading)->toMatchArray([
        'prefix' => 'Search Results',
        'query' => '',
    ]);

    expect($empty_heading)->toMatchArray([
        'prefix' => 'Search Results',
        'query' => '',
    ]);
});

test('search results type labels map to display labels', function () {
    expect(function_exists('matrix_get_search_results_type_label'))->toBeTrue();

    expect(matrix_get_search_results_type_label('post'))->toBe('Blog')
        ->and(matrix_get_search_results_type_label('page'))->toBe('Page')
        ->and(matrix_get_search_results_type_label('webinars'))->toBe('Webinar');
});

test('search results pagination urls canonicalize invalid type and sort values', function () {
    $url = matrix_build_search_results_page_url(
        'http://localhost:10034/?s=referral',
        [
            'query' => 'referral',
            'type' => 'Unknown Type',
            'sort' => 'popular',
            'paged' => 1,
        ],
        2
    );

    expect($url)->toContain('s=referral')
        ->and($url)->toContain('search_type=all')
        ->and($url)->toContain('search_sort=relevance')
        ->and($url)->toContain('paged=2');
});

test('search results no-results form does not preserve hidden filter state', function () {
    $output = matrix_render_search_results_template_for_test([
        'state' => [
            'query' => 'zzzz-no-match-zzzz',
            'type' => 'webinars',
            'sort' => 'date',
            'paged' => 2,
        ],
        'items' => [],
        'has_results' => false,
        'heading' => [
            'prefix' => "We couldn\u{2019}t find a match for",
            'query' => 'zzzz-no-match-zzzz',
        ],
        'base_url' => 'http://localhost:10034/',
        'useful_links' => matrix_prepare_useful_links_section(),
    ]);

    expect($output)->toContain('name="s"')
        ->and($output)->toContain('name="paged" value="1"')
        ->and($output)->not->toContain('name="search_sort"')
        ->and($output)->not->toContain('name="search_type"')
        ->and($output)->toContain('Go back to Home page')
        ->and($output)->toContain('Useful links')
        ->and($output)->toContain('Day Programmes')
        ->and($output)->toContain('data-matrix-block="search-results-useful-links"');
});

test('search results useful links defaults include nine suggested destinations', function () {
    expect(function_exists('matrix_get_search_results_useful_links_defaults'))->toBeTrue();

    $defaults = matrix_get_search_results_useful_links_defaults();
    $links = matrix_normalize_useful_links($defaults['links'] ?? []);

    expect($links)->toHaveCount(9)
        ->and($links[0]['title'])->toBe('Day Programmes')
        ->and($links[8]['title'])->toBe('Media Queries');

    $section = matrix_prepare_useful_links_section();
    $html = matrix_render_useful_links_section($section);

    expect($html)->toContain('Useful links')
        ->and($html)->toContain('Day Programmes');
});
