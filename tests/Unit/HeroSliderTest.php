<?php

test('mobile hero slider controls use full width nav and white arrows', function () {
    $template = file_get_contents(dirname(__DIR__, 2) . '/template-parts/hero/hero_slider.php');
    $app_css = file_get_contents(dirname(__DIR__, 2) . '/dist/app.css');

    preg_match('/<!-- MOBILE-ONLY CONTROLS:.*?<div class="([^"]+)">/s', $template, $controls_matches);
    preg_match('/<!-- Dots \(mobile\) -->\s*<div class="([^"]+)">/s', $template, $dot_matches);
    preg_match_all('/<!-- Mobile (?:Prev|Next) -->\s*<button\s+class="([^"]+)"/s', $template, $button_matches);
    preg_match_all('/<!-- Mobile (?:Prev|Next) -->.*?<path[^>]+class="([^"]+)"/s', $template, $arrow_matches);

    expect($controls_matches[1] ?? '')->toContain('w-full')
        ->and($controls_matches[1] ?? '')->toContain('justify-between')
        ->and($dot_matches[1] ?? '')->toContain('flex-1')
        ->and($dot_matches[1] ?? '')->toContain('justify-center');

    expect($button_matches[1] ?? [])->toHaveCount(2)
        ->each->toContain('bg-[#001F33]');

    expect($arrow_matches[1] ?? [])->toHaveCount(2)
        ->each->toContain('stroke-white');

    expect($app_css)->toContain('background-color: rgb(0 31 51 / var(--tw-bg-opacity, 1));')
        ->and($app_css)->toContain('stroke: #fff;');
});
