<?php

test('desktop help cta uses mint secondary background', function () {
    $template = file_get_contents(dirname(__DIR__, 2) . '/template-parts/header/navbar.php');
    $app_css = file_get_contents(dirname(__DIR__, 2) . '/dist/app.css');

    preg_match('/<!-- Looking for help -->.*?class="([^"]+)"/s', $template, $matches);
    preg_match('/\.bg-secondary\s*\{\s*[^}]*background-color:\s*rgb\(195 219 174 \/ var\(--tw-bg-opacity, 1\)\);/s', $app_css, $css_matches);

    expect($matches[1] ?? '')->toContain('bg-secondary')
        ->and($css_matches)->not->toBeEmpty();
});
