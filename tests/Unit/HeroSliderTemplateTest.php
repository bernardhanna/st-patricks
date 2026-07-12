<?php

test('hero slider mobile navigation spans full width and matches desktop dot color', function () {
    $template = file_get_contents(dirname(__DIR__, 2) . '/template-parts/hero/hero_slider.php');
    preg_match('/<!-- Dots \(mobile\) -->(.*?)<!-- Mobile Next -->/s', $template, $mobile_dots);

    expect($template)->toContain('class="flex w-full justify-between items-center mb-4 lg:hidden"')
        ->and($mobile_dots[1] ?? '')->toContain('$dot_color = $is_active ? \'#80CCD9\' : \'#7ED0E0\';')
        ->and($template)->toContain('d.style.backgroundColor = \'#80CCD9\';');
});
