<?php

require_once dirname(__DIR__, 2) . '/inc/careers-archive-functions.php';

test('careers archive resolves request state against allowed filters', function () {
    expect(function_exists('matrix_resolve_careers_archive_state'))->toBeTrue();

    $state = matrix_resolve_careers_archive_state(
        [
            'career_department' => 'nursing',
            'career_location' => 'dublin',
            'career_search' => '  receptionist  ',
            'career_page' => '2',
        ],
        ['nursing', 'administration'],
        ['dublin', 'lucan'],
        10
    );

    expect($state['department'])->toBe('nursing')
        ->and($state['location'])->toBe('dublin')
        ->and($state['search'])->toBe('receptionist')
        ->and($state['paged'])->toBe(2)
        ->and($state['posts_per_page'])->toBe(10);
});

test('careers archive falls back to all when filters are not allowed', function () {
    $state = matrix_resolve_careers_archive_state(
        [
            'career_department' => 'other',
            'career_location' => 'other',
            'career_search' => '',
            'career_page' => '0',
        ],
        ['nursing'],
        ['dublin'],
        10
    );

    expect($state['department'])->toBe('all')
        ->and($state['location'])->toBe('all')
        ->and($state['search'])->toBe('')
        ->and($state['paged'])->toBe(1);
});

test('careers archive builds query args for department location search and paging', function () {
    expect(function_exists('matrix_build_careers_archive_query_args'))->toBeTrue();

    $args = matrix_build_careers_archive_query_args([
        'department' => 'nursing',
        'location' => 'dublin',
        'search' => 'admin support',
        'paged' => 3,
        'posts_per_page' => 10,
    ], [
        'nursing' => 11,
    ], [
        'dublin' => 21,
    ]);

    expect($args['post_type'])->toBe('careers')
        ->and($args['post_status'])->toBe('publish')
        ->and($args['posts_per_page'])->toBe(10)
        ->and($args['paged'])->toBe(3)
        ->and($args['s'])->toBe('admin support')
        ->and($args['tax_query']['relation'])->toBe('AND')
        ->and($args['tax_query'][0]['taxonomy'])->toBe('career_department')
        ->and($args['tax_query'][0]['terms'])->toBe([11])
        ->and($args['tax_query'][1]['taxonomy'])->toBe('career_location')
        ->and($args['tax_query'][1]['terms'])->toBe([21]);
});

test('careers archive preserves filters in pagination urls', function () {
    expect(function_exists('matrix_build_careers_archive_page_url'))->toBeTrue();

    $url = matrix_build_careers_archive_page_url(
        'http://localhost:10034/careers/',
        [
            'department' => 'nursing',
            'location' => 'dublin',
            'search' => 'receptionist',
            'paged' => 1,
        ],
        4
    );

    expect($url)->toContain('career_department=nursing')
        ->and($url)->toContain('career_location=dublin')
        ->and($url)->toContain('career_search=receptionist')
        ->and($url)->toContain('career_page=4');
});

test('careers archive maps post rows with area and location labels', function () {
    expect(function_exists('matrix_map_career_post_row'))->toBeTrue();
});

test('careers archive layout helpers match figma vacancies styling', function () {
    expect(matrix_get_careers_archive_table_header_style())->toContain('linear-gradient')
        ->and(matrix_get_careers_archive_mobile_table_header_style())->toContain('-13.24deg')
        ->and(matrix_get_careers_archive_filter_select_class_names())->toContain('font-normal')
        ->and(matrix_get_careers_archive_apply_filters_button_class_names())->toContain('bg-[#08284B]')
        ->and(matrix_get_careers_archive_apply_filters_button_class_names())->toContain('w-fit')
        ->and(matrix_get_careers_archive_view_detail_button_class_names())->toContain('bg-[#024B79]')
        ->and(matrix_get_careers_archive_view_detail_button_class_names())->toContain('whitespace-nowrap');
});

test('careers archive render helper returns empty string for invalid archive', function () {
    expect(function_exists('matrix_render_careers_archive_results_html'))->toBeTrue()
        ->and(matrix_render_careers_archive_results_html([]))->toBe('')
        ->and(matrix_render_careers_archive_results_html('invalid'))->toBe('');
});
