<?php

require_once dirname(__DIR__, 2) . '/inc/key-contact-info-functions.php';

test('matrix_normalize_key_contact_info_columns maps accordion content', function () {
    $normalized = matrix_normalize_key_contact_info_columns([
        [
            'items' => [
                [
                    'title' => 'General Enquires',
                    'starts_open' => 1,
                    'bullet_items' => [
                        ['label' => 'Inpatient care'],
                    ],
                    'phone' => '01 012 123 123',
                    'email' => 'hello@StPatrick.ie',
                ],
            ],
        ],
    ]);

    expect($normalized['columns'])->toHaveCount(1)
        ->and($normalized['initial_open_index'])->toBe(0)
        ->and($normalized['columns'][0]['items'][0]['title'])->toBe('General Enquires')
        ->and($normalized['columns'][0]['items'][0]['bullet_items'])->toBe(['Inpatient care'])
        ->and($normalized['columns'][0]['items'][0]['phone'])->toBe('01 012 123 123')
        ->and($normalized['columns'][0]['items'][0]['email'])->toBe('hello@StPatrick.ie')
        ->and($normalized['columns'][0]['items'][0]['flat_index'])->toBe(0);
});

test('matrix_normalize_key_contact_info_columns adds placeholders for empty panel content', function () {
    $normalized = matrix_normalize_key_contact_info_columns([
        [
            'items' => [
                [
                    'title' => 'Clinical Governance Office (Complaints and feedback)',
                    'starts_open' => 0,
                    'bullet_items' => [],
                    'phone' => '',
                    'email' => '',
                ],
            ],
        ],
    ]);

    expect($normalized['columns'][0]['items'][0]['bullet_items'])->toHaveCount(3)
        ->and($normalized['columns'][0]['items'][0]['phone'])->toBe('01 012 123 123')
        ->and($normalized['columns'][0]['items'][0]['email'])->toBe('hello@StPatrick.ie');
});

test('matrix_normalize_key_contact_info_columns keeps all panels closed by default', function () {
    $normalized = matrix_normalize_key_contact_info_columns([
        [
            'items' => [
                [
                    'title' => 'General Enquires',
                    'starts_open' => 0,
                    'bullet_items' => [
                        ['label' => 'Inpatient care'],
                    ],
                    'phone' => '01 012 123 123',
                    'email' => 'hello@StPatrick.ie',
                ],
                [
                    'title' => 'Pharmacy',
                    'starts_open' => 0,
                    'bullet_items' => [],
                    'phone' => '',
                    'email' => '',
                ],
            ],
        ],
    ]);

    expect($normalized['initial_open_index'])->toBe(-1);
});

test('matrix_normalize_key_contact_info_columns opens only explicitly marked items', function () {
    $normalized = matrix_normalize_key_contact_info_columns([
        [
            'items' => [
                [
                    'title' => 'General Enquires',
                    'starts_open' => 0,
                    'bullet_items' => [['label' => 'Inpatient care']],
                    'phone' => '',
                    'email' => '',
                ],
                [
                    'title' => 'Pharmacy',
                    'starts_open' => 1,
                    'bullet_items' => [['label' => 'Prescriptions']],
                    'phone' => '',
                    'email' => '',
                ],
            ],
        ],
    ]);

    expect($normalized['initial_open_index'])->toBe(1);
});

test('matrix_normalize_key_contact_info_columns assigns flat indexes across columns', function () {
    $normalized = matrix_normalize_key_contact_info_columns([
        ['items' => [['title' => 'Column one', 'phone' => '01 111 1111']]],
        ['items' => [['title' => 'Column two', 'phone' => '01 222 2222']]],
    ]);

    expect($normalized['columns'][0]['items'][0]['flat_index'])->toBe(0)
        ->and($normalized['columns'][1]['items'][0]['flat_index'])->toBe(1);
});

test('key contact info layout helpers expose figma spacing and typography classes', function () {
    expect(matrix_get_key_contact_info_wrapper_class_names())->toContain('max-w-[1018px]')
        ->and(matrix_get_key_contact_info_wrapper_class_names())->toContain('lg:pt-16')
        ->and(matrix_get_key_contact_info_grid_class_names())->toContain('lg:grid-cols-3')
        ->and(matrix_get_key_contact_info_grid_class_names())->toContain('lg:gap-x-8')
        ->and(matrix_get_key_contact_info_title_class_names())->toContain('lg:text-[18px]')
        ->and(matrix_get_key_contact_info_item_class_names())->toContain('border-white');
});
