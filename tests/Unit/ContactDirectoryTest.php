<?php

require_once dirname(__DIR__, 2) . '/inc/contact-directory-functions.php';

test('contact directory normalizes manual items with opening hours', function () {
    $normalized = matrix_normalize_contact_directory_columns([
        [
            'items' => [
                [
                    'item_source' => 'manual',
                    'title' => 'General Enquiries',
                    'starts_open' => 1,
                    'bullet_items' => [
                        ['label' => 'Inpatient care'],
                    ],
                    'phone' => '01 249 3200',
                    'email' => 'hello@stpatricks.ie',
                    'opening_hours' => [
                        ['day_label' => 'Mon - Fri', 'hours' => '09:00 - 20:00'],
                    ],
                ],
            ],
        ],
    ]);

    expect($normalized['columns'])->toHaveCount(1)
        ->and($normalized['initial_open_index'])->toBe(0)
        ->and($normalized['columns'][0]['items'][0]['opening_hours'])->toHaveCount(1)
        ->and($normalized['columns'][0]['items'][0]['bullet_items'])->toBe(['Inpatient care']);
});

test('contact directory merges location fields when item source is location', function () {
    __wp_stub('get_post_type', 'locations');
    __wp_stub('get_the_title', 'Adolescent Dean Clinic');
    __wp_stub('get_field', fn ($field, $post_id = false) => match ($field) {
        'phone' => '01 249 3590',
        'email' => '',
        'opening_hours' => [
            ['day_label' => 'Mon - Fri', 'hours' => '09:00 - 17:00'],
        ],
        default => null,
    });
    __wp_stub('get_permalink', 'https://example.com/locations/adolescent-dean-clinic/');

    $normalized = matrix_normalize_contact_directory_columns([
        [
            'items' => [
                [
                    'item_source' => 'location',
                    'location' => 2136,
                    'title' => 'Adolescent Dean Clinic',
                    'starts_open' => 0,
                    'bullet_items' => [],
                    'phone' => '',
                    'email' => '',
                    'opening_hours' => [],
                ],
            ],
        ],
    ]);

    expect($normalized['columns'][0]['items'][0]['phone'])->toBe('01 249 3590')
        ->and($normalized['columns'][0]['items'][0]['location_url'])->toContain('adolescent-dean-clinic')
        ->and($normalized['columns'][0]['items'][0]['opening_hours'])->toHaveCount(1);
});

test('contact directory reports panel content when opening hours exist', function () {
    expect(matrix_contact_directory_item_has_panel_content([
        'opening_hours' => [
            ['day_label' => 'Mon - Fri', 'hours' => '09:00 - 17:00'],
        ],
    ]))->toBeTrue();
});

test('contact directory layout helpers match figma three column grid', function () {
    expect(matrix_get_contact_directory_wrapper_class_names())->toContain('max-w-[1018px]')
        ->and(matrix_get_contact_directory_grid_class_names())->toContain('lg:grid-cols-3')
        ->and(matrix_get_contact_directory_grid_class_names())->not->toContain('lg:grid-cols-2')
        ->and(matrix_contact_directory_has_visible_intro('<p>Contact copy</p>'))->toBeTrue()
        ->and(matrix_contact_directory_has_visible_intro(''))->toBeFalse();
});
