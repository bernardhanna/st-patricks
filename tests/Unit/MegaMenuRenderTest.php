<?php

require_once dirname(__DIR__, 2) . '/inc/mega-menu-render.php';

test('mega menu heading underline uses olive 1px bar', function () {
    ob_start();
    matrix_render_nav_mega_menu_heading_underline();
    $html = ob_get_clean();

    expect($html)->toContain('h-px')
        ->and($html)->toContain('w-10')
        ->and($html)->toContain('bg-[#5F604B]');
});

test('mega menu cta uses figma teal with midnight text', function () {
    $classes = matrix_get_nav_mega_menu_cta_class_names();

    expect($classes)->toContain('bg-[#7ED0E0]')
        ->and($classes)->toContain('text-[#1E244B]');
});

test('mega menu link classes include figma underline offset', function () {
    expect(matrix_get_nav_mega_menu_link_class_names(true))->toContain('underline-offset-[7px]')
        ->and(matrix_get_nav_mega_menu_link_class_names(false))->toContain('hover:underline-offset-[7px]');
});
