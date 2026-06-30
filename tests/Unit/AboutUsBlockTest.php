<?php

test('about us heading underline uses the standard title spacing', function () {
    $template = file_get_contents(dirname(__DIR__, 2) . '/template-parts/flexi/about_us.php');

    expect($template)->toContain('mt-6 h-[4px] w-10 bg-[#6FC9C0]')
        ->and($template)->not->toContain('mt-4 w-10  bg-[#6FC9C0] h-[4px]');
});
