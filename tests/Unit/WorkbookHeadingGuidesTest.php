<?php

require_once dirname(__DIR__, 2) . '/inc/programmes-therapies-single-functions.php';

test('workbook heading guides become real heading tags', function () {
    $html = '<p>Intro copy.</p>'
        . '<p><strong>H2</strong> <strong>Overview of the programme</strong></p>'
        . '<p>Body copy.</p>'
        . '<p><strong>H3 Bipolar Programme Workshop</strong></p>'
        . '<p>Workshop details.</p>'
        . '<p><strong>H2 Referrals</strong></p>';

    $converted = matrix_convert_workbook_heading_guides($html);

    expect($converted)->toContain('<h2>Overview of the programme</h2>')
        ->and($converted)->toContain('<h3>Bipolar Programme Workshop</h3>')
        ->and($converted)->toContain('<h2>Referrals</h2>')
        ->and($converted)->not->toContain('<strong>H2')
        ->and($converted)->not->toContain('<strong>H3')
        ->and($converted)->toContain('<p>Intro copy.</p>')
        ->and($converted)->toContain('<p>Body copy.</p>');
});

test('workbook heading converter leaves normal paragraphs alone', function () {
    $html = '<p>The H2 receptor is unrelated.</p><p><strong>Important note</strong></p>';

    expect(matrix_convert_workbook_heading_guides($html))->toBe($html);
});
