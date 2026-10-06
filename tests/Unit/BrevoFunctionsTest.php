<?php

require_once dirname(__DIR__, 2) . '/inc/forms/brevo-functions.php';

test('brevo list payloads parse id and name pairs', function () {
    $lists = matrix_parse_brevo_lists_payload([
        'lists' => [
            ['id' => 12, 'name' => 'SUAN'],
            ['id' => '18', 'name' => 'FCS'],
            ['id' => 0, 'name' => 'Ignored'],
        ],
        'count' => 2,
    ]);

    expect($lists)->toBe([
        12 => 'SUAN',
        18 => 'FCS',
    ]);
});

test('brevo list ids parse from strings and arrays', function () {
    expect(matrix_parse_brevo_list_ids('12, 18; 0'))->toBe([12, 18])
        ->and(matrix_parse_brevo_list_ids(['12', 18, 18]))->toBe([12, 18]);
});

test('brevo list signatures reject unsigned or swapped ids', function () {
    $signature = matrix_brevo_list_signature(12);

    expect(matrix_brevo_list_signature_is_valid(12, $signature))->toBeTrue()
        ->and(matrix_brevo_list_signature_is_valid(18, $signature))->toBeFalse()
        ->and(matrix_brevo_list_signature_is_valid(12, 'tampered'))->toBeFalse()
        ->and(matrix_get_signed_brevo_list_id_from_request([
            '_cfg_brevo_list_id' => '12',
            '_cfg_brevo_sig' => $signature,
        ]))->toBe(12)
        ->and(matrix_get_signed_brevo_list_id_from_request([
            '_cfg_brevo_list_id' => '18',
            '_cfg_brevo_sig' => $signature,
        ]))->toBe(0);
});

test('subscribe list ids prefer a signed newsletter list over allowed defaults', function () {
    $signature = matrix_brevo_list_signature(42);

    __wp_stub('get_field', fn ($field, $post_id = false) => $field === 'brevo_list_ids' ? '12,18' : null);

    expect(matrix_resolve_subscribe_brevo_list_ids([
        'list_ids' => '18',
        '_cfg_brevo_list_id' => '42',
        '_cfg_brevo_sig' => $signature,
    ]))->toBe([42])
        ->and(matrix_resolve_subscribe_brevo_list_ids([
            'list_ids' => '99',
        ]))->toBe([12, 18])
        ->and(matrix_resolve_subscribe_brevo_list_ids([
            'list_ids' => '18',
        ]))->toBe([18]);
});

test('theme form brevo consent requires a mailing list or newsletter tick', function () {
    expect(matrix_theme_form_has_brevo_consent([
        'email' => 'a@example.com',
    ]))->toBeFalse()
        ->and(matrix_theme_form_has_brevo_consent([
            'consent_mailing_list' => 'yes',
        ]))->toBeTrue()
        ->and(matrix_theme_form_has_brevo_consent([
            'consent_newsletter' => 'on',
        ]))->toBeTrue();
});
