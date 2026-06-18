<?php

require_once dirname(__DIR__, 2) . '/inc/search-results-functions.php';
require_once dirname(__DIR__, 2) . '/inc/useful-links-functions.php';

beforeEach(function () {
    __wp_stub('home_url', fn ($path = '/') => 'http://localhost:10034' . $path);
    __wp_stub('get_template_part', function ($slug, $name = null, $args = []) {
        $templates = [];

        if ($name !== null) {
            $templates[] = "{$slug}-{$name}.php";
        }

        $templates[] = "{$slug}.php";
        $theme_root = dirname(__DIR__, 2);

        foreach ($templates as $template) {
            $path = $theme_root . '/' . $template;

            if (! is_readable($path)) {
                continue;
            }

            include $path;

            return;
        }
    });
});

function matrix_render_search_results_template_for_test($search_results)
{
    $args = ['search_results' => $search_results];

    ob_start();
    require dirname(__DIR__, 2) . '/template-parts/search/results.php';

    return (string) ob_get_clean();
}

test('search queries slugify into clean path urls', function () {
    expect(function_exists('matrix_search_query_to_slug'))->toBeTrue();

    expect(matrix_search_query_to_slug('how to make a referral'))->toBe('how-to-make-a-referral')
        ->and(matrix_search_slug_to_query('how-to-make-a-referral'))->toBe('how to make a referral');
});

test('search results resolve state from pretty search paths', function () {
    $state = matrix_resolve_search_results_state([
        'matrix_search' => 'how-to-make-a-referral',
        'search_type' => 'page',
        'search_sort' => 'date',
        'paged' => '2',
    ]);

    expect($state)->toMatchArray([
        'query' => 'how to make a referral',
        'type' => 'page',
        'sort' => 'date',
        'paged' => 2,
    ]);
});

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
        'http://localhost:10034/search/',
        [
            'query' => 'referral',
            'type' => 'page',
            'sort' => 'date',
            'paged' => 1,
        ],
        4
    );

    expect($url)->toContain('/search/referral/page/4/')
        ->and($url)->toContain('search_type=page')
        ->and($url)->toContain('search_sort=date')
        ->and($url)->not->toContain('s=referral');
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

test('search results cards use grid below desktop and list layout at lg', function () {
    expect(matrix_get_search_results_cards_layout_class_names())->toContain('mob:grid-cols-2')
        ->and(matrix_get_search_results_cards_layout_class_names())->toContain('lg:flex lg:flex-col')
        ->and(matrix_get_search_results_cards_layout_class_names())->not->toContain('lg:grid-cols-3')
        ->and(matrix_get_search_results_card_class_names())->toContain('lg:flex-row')
        ->and(matrix_get_search_results_card_image_class_names())->toContain('lg:w-[280px]');
});

test('search results type badges use distinct background colors', function () {
    expect(matrix_get_search_results_type_badge_colors('post'))->toBe([
        'background' => '#FADBD8',
        'text' => '#08284B',
    ])->and(matrix_get_search_results_type_badge_colors('page'))->toBe([
        'background' => '#C6ECF4',
        'text' => '#08284B',
    ])->and(matrix_get_search_results_type_badge_colors('webinars'))->toBe([
        'background' => '#C3DBAE',
        'text' => '#08284B',
    ]);
});

test('search results pagination urls canonicalize invalid type and sort values', function () {
    $url = matrix_build_search_results_page_url(
        'http://localhost:10034/search/',
        [
            'query' => 'referral',
            'type' => 'Unknown Type',
            'sort' => 'popular',
            'paged' => 1,
        ],
        2
    );

    expect($url)->toContain('/search/referral/page/2/')
        ->and($url)->not->toContain('search_type=')
        ->and($url)->not->toContain('search_sort=');
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
        'base_url' => 'http://localhost:10034/search/',
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

test('search results extract hero image from flexible content rows', function () {
    $rows = [
        [
            'acf_fc_layout' => 'hero_with_breadcrumbs',
            'hero_image' => 0,
        ],
        [
            'acf_fc_layout' => 'wysiwyg',
            'text_content' => '<p>Body copy</p>',
        ],
    ];

    expect(matrix_extract_search_results_hero_image_from_rows($rows))->toBe(0);

    $rows[0]['hero_image'] = 42;

    expect(matrix_extract_search_results_hero_image_from_rows($rows))->toBe(42)
        ->and(matrix_normalize_search_results_attachment_id(['ID' => 99]))->toBe(99)
        ->and(matrix_normalize_search_results_attachment_id(['id' => 17]))->toBe(17);
});

test('search results extract first text from flexible content rows', function () {
    $rows = [
        [
            'acf_fc_layout' => 'hero_with_breadcrumbs',
            'content' => '<p>Referral pathways for healthcare professionals.</p>',
        ],
        [
            'acf_fc_layout' => 'wysiwyg',
            'text_content' => '<p>Later block copy.</p>',
        ],
    ];

    expect(matrix_extract_search_results_text_from_row($rows[0]))->toBe('Referral pathways for healthcare professionals.')
        ->and(matrix_trim_search_results_excerpt(str_repeat('word ', 30)))->toEndWith('...');
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
