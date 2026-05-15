<?php

require_once dirname(__DIR__, 2) . '/inc/programmes-therapies-archive-functions.php';

test('programmes therapies archive resolves request state against allowed taxonomies', function () {
    $state = matrix_resolve_programmes_therapies_archive_state(
        [
            'pt_type' => 'therapies',
            'pt_care' => 'inpatient-programme',
            'pt_delivery' => 'hybrid',
            'pt_page' => '2',
        ],
        ['programmes', 'therapies'],
        ['inpatient-programme', 'day-patient-programme'],
        ['hybrid', 'online', 'in-person'],
        10
    );

    expect($state['type'])->toBe('therapies')
        ->and($state['care'])->toBe('inpatient-programme')
        ->and($state['delivery'])->toBe('hybrid')
        ->and($state['paged'])->toBe(2)
        ->and($state['posts_per_page'])->toBe(10);
});

test('programmes therapies archive falls back when filters are invalid', function () {
    $state = matrix_resolve_programmes_therapies_archive_state(
        [
            'pt_type' => 'invalid',
            'pt_care' => 'invalid',
            'pt_delivery' => 'invalid',
            'pt_page' => '0',
        ],
        ['programmes', 'therapies'],
        ['inpatient-programme'],
        ['hybrid'],
        10
    );

    expect($state['type'])->toBe('all')
        ->and($state['care'])->toBe('all')
        ->and($state['delivery'])->toBe('all')
        ->and($state['paged'])->toBe(1);
});

test('programmes therapies archive builds query args for taxonomy filters', function () {
    $args = matrix_build_programmes_therapies_archive_query_args(
        [
            'type' => 'programmes',
            'care' => 'day-patient-programme',
            'delivery' => 'online',
            'paged' => 1,
            'posts_per_page' => 10,
        ],
        ['programmes' => 11, 'therapies' => 12],
        ['day-patient-programme' => 21],
        ['online' => 31]
    );

    expect($args['post_type'])->toBe('programmes_therapies')
        ->and($args['posts_per_page'])->toBe(10)
        ->and($args['tax_query']['relation'])->toBe('AND')
        ->and($args['tax_query'])->toHaveKey(0)
        ->and($args['tax_query'])->toHaveKey(1)
        ->and($args['tax_query'])->toHaveKey(2);
});

test('programmes therapies archive preserves filters in pagination urls', function () {
    $url = matrix_build_programmes_therapies_archive_page_url(
        'http://localhost:10034/flexi/',
        [
            'type' => 'therapies',
            'care' => 'homecare-programme',
            'delivery' => 'in-person',
            'paged' => 1,
        ],
        3
    );

    expect($url)->toContain('pt_type=therapies')
        ->and($url)->toContain('pt_care=homecare-programme')
        ->and($url)->toContain('pt_delivery=in-person')
        ->and($url)->toContain('pt_page=3');
});

test('programmes therapies archive exposes filter group defaults', function () {
    $filters = matrix_build_programmes_therapies_archive_filter_groups([], matrix_get_programmes_therapies_archive_defaults());

    expect($filters['type_options'])->toHaveCount(3)
        ->and($filters['type_options'][0]['slug'])->toBe('all')
        ->and($filters['care_groups'])->toBe([]);
});
