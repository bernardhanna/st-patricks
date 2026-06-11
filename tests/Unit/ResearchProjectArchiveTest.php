<?php

require_once dirname(__DIR__, 2) . '/inc/research-project-archive-functions.php';

test('research project archive resolves request state against allowed filters', function () {
    $state = matrix_resolve_research_project_archive_state(
        [
            'research_category' => 'current',
            'research_researcher' => 'dr-conor-farren',
            'research_search' => '  mental health  ',
            'research_page' => '2',
        ],
        ['current', 'past'],
        ['dr-conor-farren', 'prof-paul-fearon'],
        12
    );

    expect($state['category'])->toBe('current')
        ->and($state['researcher'])->toBe('dr-conor-farren')
        ->and($state['search'])->toBe('mental health')
        ->and($state['paged'])->toBe(2)
        ->and($state['posts_per_page'])->toBe(12);
});

test('research project archive falls back when filters are not allowed', function () {
    $state = matrix_resolve_research_project_archive_state(
        [
            'research_category' => 'unknown',
            'research_researcher' => 'unknown',
            'research_page' => '0',
        ],
        ['current', 'past'],
        ['dr-conor-farren'],
        12,
        'current'
    );

    expect($state['category'])->toBe('current')
        ->and($state['researcher'])->toBe('all')
        ->and($state['paged'])->toBe(1);
});

test('research project archive builds query args with taxonomy filters', function () {
    $args = matrix_build_research_project_archive_query_args([
        'category' => 'current',
        'researcher' => 'dr-conor-farren',
        'search' => 'anxiety',
        'paged' => 2,
        'posts_per_page' => 12,
    ], [
        'current' => 17,
        'past' => 18,
    ], [
        'dr-conor-farren' => 42,
    ]);

    expect($args['post_type'])->toBe('research_projects')
        ->and($args['posts_per_page'])->toBe(12)
        ->and($args['paged'])->toBe(2)
        ->and($args['s'])->toBe('anxiety')
        ->and($args['tax_query'])->toHaveCount(3)
        ->and($args['tax_query']['relation'])->toBe('AND');
});

test('research project archive preserves filters in pagination urls', function () {
    $url = matrix_build_research_project_archive_page_url(
        'http://localhost:10034/current-research-projects/',
        [
            'category' => 'current',
            'researcher' => 'dr-conor-farren',
            'search' => 'service user',
            'paged' => 1,
        ],
        4
    );

    expect($url)->toContain('research_category=current')
        ->and($url)->toContain('research_researcher=dr-conor-farren')
        ->and($url)->toContain('research_search=service+user')
        ->and($url)->toContain('research_page=4')
        ->and($url)->not->toContain('research_page=1')
        ->and($url)->not->toContain('research_researcher=all');
});

test('research project main archive uses path-based category urls without default params', function () {
    $url = matrix_build_research_project_archive_page_url(
        'http://localhost:10034/research-projects/',
        [
            'category' => 'past',
            'researcher' => 'all',
            'search' => '',
            'paged' => 1,
        ],
        1
    );

    expect($url)->toBe('http://localhost:10034/research-projects/past/')
        ->and(matrix_build_research_project_archive_filter_params([
            'category' => 'all',
            'researcher' => 'all',
            'search' => '',
            'paged' => 1,
        ]))->toBe([]);
});
