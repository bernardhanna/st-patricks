<?php

require_once dirname(__DIR__, 2) . '/inc/inpatient-bed-vacancies-functions.php';

test('inpatient bed vacancy item normalization applies defaults and trims text', function () {
    $item = matrix_normalize_inpatient_bed_vacancy_item([
        'bed_count' => '2',
        'location_title' => '  Adolescent Inpatient Bed Vacancies  ',
        'location_subtitle' => '  Willow Grove  ',
        'disclaimer' => '  Additional context text.  ',
        'status_background_color' => '#C3DBAE',
    ]);

    expect($item)->toMatchArray([
        'bed_count' => 2,
        'location_title' => 'Adolescent Inpatient Bed Vacancies',
        'location_subtitle' => 'Willow Grove',
        'disclaimer' => 'Additional context text.',
        'status_background_color' => '#C3DBAE',
    ]);
});

test('inpatient bed vacancy rows skip empty entries', function () {
    $items = matrix_normalize_inpatient_bed_vacancy_items([
        [
            'location_title' => 'Adult Inpatient Bed Vacancies',
            'location_subtitle' => 'St Patrick\'s',
            'bed_count' => 1,
        ],
        [
            'location_title' => '',
            'disclaimer' => '',
        ],
    ]);

    expect($items)->toHaveCount(1)
        ->and($items[0]['location_title'])->toBe('Adult Inpatient Bed Vacancies');
});

test('inpatient bed vacancy location label formats subtitle in brackets', function () {
    expect(matrix_format_inpatient_bed_vacancy_location_label([
        'location_title' => 'Adolescent Inpatient Bed Vacancies',
        'location_subtitle' => 'Willow Grove',
    ]))->toBe('Adolescent Inpatient Bed Vacancies (Willow Grove)')
        ->and(matrix_format_inpatient_bed_vacancy_location_label([
            'location_title' => 'Adult Inpatient Bed Vacancies',
            'location_subtitle' => '',
        ]))->toBe('Adult Inpatient Bed Vacancies');
});

test('inpatient bed vacancies defaults include expected design tokens', function () {
    expect(matrix_get_inpatient_bed_vacancies_defaults())->toMatchArray([
        'heading' => 'Current Inpatient Bed Vacancies',
        'section_background_color' => '#FBF8F3',
        'status_background_color' => '#C3DBAE',
        'underline_color' => '#6FC9C0',
    ]);
});
