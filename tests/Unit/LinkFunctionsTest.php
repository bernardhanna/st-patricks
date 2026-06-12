<?php

if (! function_exists('home_url')) {
    function home_url(string $path = ''): string
    {
        return 'https://www.stpatricks.ie' . $path;
    }
}

require_once dirname(__DIR__, 2) . '/inc/link-functions.php';

test('matrix_is_external_url detects off-site http links', function () {
    expect(matrix_is_external_url('https://www.walkinmyshoes.ie/'))->toBeTrue()
        ->and(matrix_is_external_url('https://soundcloud.com/example'))->toBeTrue()
        ->and(matrix_is_external_url('/about-us/'))->toBeFalse()
        ->and(matrix_is_external_url('mailto:hello@example.com'))->toBeFalse();
});

test('matrix_normalize_link_target opens external urls in a new tab', function () {
    expect(matrix_normalize_link_target('https://example.com/page'))->toBe('_blank')
        ->and(matrix_normalize_link_target('/about-us/'))->toBe('_self')
        ->and(matrix_normalize_link_target('https://example.com/page', '_self'))->toBe('_blank');
});

test('matrix_process_external_links_in_html adds target blank and rel', function () {
    $html = '<p>Read <a href="https://example.com/report.pdf">the report</a>.</p>';
    $processed = matrix_process_external_links_in_html($html);

    expect($processed)->toContain('target="_blank"')
        ->and($processed)->toContain('rel="noopener noreferrer"');
});

test('matrix_normalize_acf_link forces external acf links to open in a new tab', function () {
    $link = matrix_normalize_acf_link([
        'title' => 'Walk in My Shoes',
        'url' => 'https://www.walkinmyshoes.ie/',
        'target' => '_self',
    ]);

    expect($link)->toMatchArray([
        'title' => 'Walk in My Shoes',
        'url' => 'https://www.walkinmyshoes.ie/',
        'target' => '_blank',
        'rel' => 'noopener noreferrer',
    ]);
});
