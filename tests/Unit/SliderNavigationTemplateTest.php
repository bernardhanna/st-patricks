<?php

function matrix_get_template_segment(string $template_path, string $start_marker, string $end_marker): string
{
    $template = file_get_contents($template_path);
    $start = strpos($template, $start_marker);
    $end = strpos($template, $end_marker, $start);

    expect($start)->not->toBeFalse()
        ->and($end)->not->toBeFalse();

    return substr($template, $start, $end - $start);
}

test('hero slider mobile navigation spans full width and keeps white arrow buttons', function () {
    $segment = matrix_get_template_segment(
        dirname(__DIR__, 2) . '/template-parts/hero/hero_slider.php',
        '<!-- MOBILE-ONLY CONTROLS',
        '</div>
                    <?php } ?>'
    );

    expect($segment)->toContain('w-full')
        ->and($segment)->toContain('justify-between')
        ->and($segment)->toContain('bg-white')
        ->and($segment)->not->toContain('hover:bg-[#001F33]')
        ->and($segment)->not->toContain('active:bg-black')
        ->and($segment)->not->toContain('focus:bg-[#001F33]')
        ->and($segment)->not->toContain('group-hover:stroke-white')
        ->and($segment)->not->toContain('group-active:stroke-white')
        ->and($segment)->not->toContain('group-focus:stroke-white');
});

test('story slider mobile navigation spans full width and preserves desktop hover states', function () {
    $segment = matrix_get_template_segment(
        dirname(__DIR__, 2) . '/template-parts/flexi/story_slider.php',
        '<!-- Navigation -->',
        '</div>
          <?php endif; ?>'
    );

    expect($segment)->toContain('w-full')
        ->and($segment)->toContain('justify-between')
        ->and($segment)->toContain('lg:w-auto')
        ->and($segment)->toContain('lg:justify-center')
        ->and($segment)->not->toContain(' hover:bg-[#001F33]')
        ->and($segment)->not->toContain(' active:bg-black')
        ->and($segment)->not->toContain(' focus:bg-[#001F33]')
        ->and($segment)->not->toContain(' group-hover:stroke-white')
        ->and($segment)->not->toContain(' group-active:stroke-white')
        ->and($segment)->not->toContain(' group-focus:stroke-white')
        ->and($segment)->toContain('lg:hover:bg-[#001F33]')
        ->and($segment)->toContain('lg:active:bg-black')
        ->and($segment)->toContain('lg:focus:bg-[#001F33]')
        ->and($segment)->toContain('lg:group-hover:stroke-white')
        ->and($segment)->toContain('lg:group-active:stroke-white')
        ->and($segment)->toContain('lg:group-focus:stroke-white');
});
