<?php

require_once dirname(__DIR__, 2) . '/inc/team-grid-functions.php';

test('multidisciplinary team cards are normalized for template rendering', function () {
    expect(function_exists('matrix_normalize_multidisciplinary_team_cards'))->toBeTrue();

    $cards = [
        [
            'title' => '  Psychology  ',
            'description' => '<p>Evidence-based psychological support.</p>',
            'link' => [
                'url' => '/service/psychology',
                'title' => 'Read more',
                'target' => '',
            ],
            'card_tone' => '',
        ],
        [
            'title' => 'Nursing',
            'description' => '<p>Specialist inpatient and outpatient nursing care.</p>',
            'link' => [],
            'card_tone' => 'coral',
        ],
        [
            'title' => '',
            'description' => '',
            'link' => [],
            'card_tone' => '',
        ],
    ];

    $normalized = matrix_normalize_multidisciplinary_team_cards($cards);

    expect($normalized)->toHaveCount(2)
        ->and($normalized[0]['title'])->toBe('Psychology')
        ->and($normalized[0]['description'])->toBe('<p>Evidence-based psychological support.</p>')
        ->and($normalized[0]['card_tone'])->toBe('teal')
        ->and($normalized[0]['is_linked'])->toBeTrue()
        ->and($normalized[0]['link'])->toBe([
            'url' => '/service/psychology',
            'title' => 'Read more',
            'target' => '_self',
        ])
        ->and($normalized[1]['card_tone'])->toBe('coral')
        ->and($normalized[1]['is_linked'])->toBeFalse()
        ->and($normalized[1]['link'])->toBeNull();
});
