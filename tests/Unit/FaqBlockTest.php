<?php

require_once dirname(__DIR__, 2) . '/inc/faq-functions.php';

test('selected faq mode maps post-like items into question and answer rows', function () {
    expect(function_exists('matrix_resolve_faq_items'))->toBeTrue();

    $items = matrix_resolve_faq_items(
        'selected',
        [
            [
                'post_title' => 'How do I make a referral?',
                'post_content' => '<p>You can make a referral by contacting our service directly.</p>',
            ],
        ],
        [],
        []
    );

    expect($items)->toHaveCount(1)
        ->and($items[0]['question'])->toBe('How do I make a referral?')
        ->and($items[0]['answer'])->toBe('<p>You can make a referral by contacting our service directly.</p>');
});

test('all faq mode falls back to the first item being open when none are flagged', function () {
    $items = matrix_resolve_faq_items(
        'all',
        [],
        [],
        [
            [
                'post_title' => 'What should I bring?',
                'post_content' => '<p>Please bring your appointment details and any supporting information.</p>',
            ],
            [
                'post_title' => 'Where do I park?',
                'post_content' => '<p>Visitor parking is available on site.</p>',
            ],
        ]
    );

    expect($items)->toHaveCount(2)
        ->and($items[0]['starts_open'])->toBeTrue()
        ->and($items[1]['starts_open'])->toBeFalse();
});

test('faq layout style resolves page and default variants', function () {
    expect(matrix_resolve_faq_layout_style(''))->toBe('default')
        ->and(matrix_resolve_faq_layout_style('page'))->toBe('page')
        ->and(matrix_resolve_faq_layout_style('unknown'))->toBe('default');
});

test('category faq mode skips empty posts and keeps later valid items closed', function () {
    $items = matrix_resolve_faq_items(
        'category',
        [],
        [
            [
                'post_title' => '',
                'post_content' => '',
            ],
            [
                'post_title' => 'Can family attend appointments?',
                'post_content' => '<p>Family attendance depends on the service and appointment type.</p>',
            ],
        ],
        []
    );

    expect($items)->toHaveCount(1)
        ->and($items[0]['question'])->toBe('Can family attend appointments?')
        ->and($items[0]['starts_open'])->toBeTrue();
});
