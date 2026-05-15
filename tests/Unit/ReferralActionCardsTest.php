<?php

require_once dirname(__DIR__, 2) . '/inc/referral-action-cards-functions.php';

test('referral action card normalization applies defaults and trims text', function () {
    expect(function_exists('matrix_normalize_referral_action_card'))->toBeTrue();

    $card = matrix_normalize_referral_action_card([
        'title' => '  Make a Referral via Healthlink  ',
        'description' => '  The fastest route for referrals.  ',
        'button' => [
            'title' => 'Go to Healthlink',
            'url' => 'https://example.com/healthlink',
            'target' => '_blank',
        ],
        'action_icon' => 'external',
        'background_color' => '#CEF2EE',
    ], [
        'background_color' => '#FFFFFF',
        'action_icon' => 'external',
    ]);

    expect($card)->toMatchArray([
        'title' => 'Make a Referral via Healthlink',
        'description' => 'The fastest route for referrals.',
        'action_icon' => 'external',
        'background_color' => '#CEF2EE',
    ]);
});

test('referral action card falls back to a safe icon and default color', function () {
    expect(function_exists('matrix_normalize_referral_action_card'))->toBeTrue();

    $card = matrix_normalize_referral_action_card([
        'title' => 'Download form',
        'action_icon' => 'weird-value',
        'background_color' => '',
    ], [
        'background_color' => '#E4F4D6',
        'action_icon' => 'download',
    ]);

    expect($card['action_icon'])->toBe('download')
        ->and($card['background_color'])->toBe('#E4F4D6');
});

test('referral action card helper can detect whether a button is renderable', function () {
    expect(function_exists('matrix_referral_action_card_has_button'))->toBeTrue();

    expect(matrix_referral_action_card_has_button([
        'button' => [
            'title' => 'Download Adult Referral Form',
            'url' => 'https://example.com/form.pdf',
        ],
    ]))->toBeTrue()
        ->and(matrix_referral_action_card_has_button([
            'button' => [
                'title' => 'Broken',
                'url' => '',
            ],
        ]))->toBeFalse();
});

test('referral action card icon helper returns inline svg for supported icon types', function () {
    expect(function_exists('matrix_get_referral_action_card_icon_svg'))->toBeTrue();

    $external = matrix_get_referral_action_card_icon_svg('external');
    $download = matrix_get_referral_action_card_icon_svg('download');

    expect($external)->toContain('<svg')
        ->and($external)->toContain('aria-hidden="true"')
        ->and($download)->toContain('<svg')
        ->and($download)->toContain('aria-hidden="true"');
});
