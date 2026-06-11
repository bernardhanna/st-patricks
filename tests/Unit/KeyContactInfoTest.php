<?php

require_once dirname(__DIR__, 2) . '/inc/key-contact-info-functions.php';

test('matrix_normalize_key_contact_info_columns maps accordion content', function () {
    $columns = matrix_normalize_key_contact_info_columns([
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

    expect($columns)->toHaveCount(1)
        ->and($columns[0]['initial_open_index'])->toBe(0)
        ->and($columns[0]['items'][0]['title'])->toBe('General Enquires')
        ->and($columns[0]['items'][0]['bullet_items'])->toBe(['Inpatient care'])
        ->and($columns[0]['items'][0]['phone'])->toBe('01 012 123 123')
        ->and($columns[0]['items'][0]['email'])->toBe('hello@StPatrick.ie');
});

test('matrix_normalize_key_contact_info_columns adds placeholders for empty panel content', function () {
    $columns = matrix_normalize_key_contact_info_columns([
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

    expect($columns[0]['items'][0]['bullet_items'])->toHaveCount(3)
        ->and($columns[0]['items'][0]['phone'])->toBe('01 012 123 123')
        ->and($columns[0]['items'][0]['email'])->toBe('hello@StPatrick.ie');
});

test('matrix_normalize_key_contact_info_columns keeps all panels closed by default', function () {
    $columns = matrix_normalize_key_contact_info_columns([
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

    expect($columns[0]['initial_open_index'])->toBe(-1);
});

test('matrix_normalize_key_contact_info_columns opens only explicitly marked items', function () {
    $columns = matrix_normalize_key_contact_info_columns([
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

    expect($columns[0]['initial_open_index'])->toBe(1);
});
