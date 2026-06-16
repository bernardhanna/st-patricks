<?php

require_once dirname(__DIR__, 2) . '/inc/hero-functions.php';

if (! function_exists('home_url')) {
    function home_url($path = '')
    {
        return 'https://example.com' . $path;
    }
}

if (! function_exists('esc_html')) {
    function esc_html($text)
    {
        return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
    }
}

test('manual breadcrumb mode returns manual items and current label', function () {
    expect(function_exists('matrix_resolve_hero_breadcrumbs'))->toBeTrue();

    $manual_items = [
        [
            'title' => 'Home',
            'url' => 'https://example.com/',
            'target' => '',
        ],
        [
            'title' => 'About Us',
            'url' => 'https://example.com/about-us/',
            'target' => '',
        ],
    ];

    $result = matrix_resolve_hero_breadcrumbs(
        'manual',
        $manual_items,
        'Healthcare professionals',
        [
            'items' => [
                ['title' => 'Ignored', 'url' => 'https://example.com/ignored/', 'target' => ''],
            ],
            'current_label' => 'Ignored current',
        ]
    );

    expect($result['items'])->toBe($manual_items)
        ->and($result['current_label'])->toBe('Healthcare professionals');
});

test('hero layout style defaults to image_split', function () {
    expect(matrix_resolve_hero_with_breadcrumbs_layout_style(''))->toBe('image_split')
        ->and(matrix_resolve_hero_with_breadcrumbs_layout_style('image_split'))->toBe('image_split')
        ->and(matrix_resolve_hero_with_breadcrumbs_layout_style('unknown'))->toBe('image_split');
});

test('hero layout style resolves title_accent', function () {
    expect(matrix_resolve_hero_with_breadcrumbs_layout_style('title_accent'))->toBe('title_accent');
});

test('hero layout style resolves register_intro', function () {
    expect(matrix_resolve_hero_with_breadcrumbs_layout_style('register_intro'))->toBe('register_intro');
});

test('hero external link icon helper returns accessible svg markup', function () {
    expect(function_exists('matrix_get_hero_external_link_icon_svg'))->toBeTrue()
        ->and(matrix_get_hero_external_link_icon_svg())->toContain('<svg')
        ->and(matrix_get_hero_external_link_icon_svg())->toContain('aria-hidden="true"');
});

test('hero image split layout helpers add spacing before embedded and primary buttons', function () {
    expect(matrix_get_hero_with_breadcrumbs_image_split_column_class_names())->toContain('lg:gap-6')
        ->and(matrix_get_hero_with_breadcrumbs_image_split_content_class_names())->toContain('[&_p:has(.btn)]:mt-5')
        ->and(matrix_get_hero_with_breadcrumbs_image_split_content_class_names())->toContain('lg:[&_p:has(.btn)]:mt-6')
        ->and(matrix_get_hero_with_breadcrumbs_primary_button_class_names())->toContain('bg-[#024B79]')
        ->and(matrix_get_hero_with_breadcrumbs_primary_button_class_names())->toContain('text-white')
        ->and(matrix_get_hero_with_breadcrumbs_image_split_grid_class_names())->toContain('lg:grid-cols-[minmax(0,1fr)_581px]')
        ->and(matrix_get_hero_with_breadcrumbs_image_split_image_column_class_names())->toContain('order-1')
        ->and(matrix_get_hero_with_breadcrumbs_image_split_heading_class_names())->toContain('text-[28px]');
});

test('hero with breadcrumbs text max width resolves wide and default classes', function () {
    expect(matrix_get_hero_with_breadcrumbs_text_max_width_class('wide'))->toBe('max-w-[50rem]')
        ->and(matrix_get_hero_with_breadcrumbs_text_max_width_class('default'))->toBe('max-w-[599px]')
        ->and(matrix_get_hero_with_breadcrumbs_image_split_heading_class_names('wide'))->toContain('max-w-[50rem]')
        ->and(matrix_get_hero_with_breadcrumbs_image_split_content_class_names('wide'))->toContain('max-w-[50rem]')
        ->and(matrix_get_hero_with_breadcrumbs_image_split_content_class_names('wide'))->not->toContain('max-w-[1160px]');
});

test('hero image split wide text max width uses single column stacked layout', function () {
    expect(matrix_get_hero_with_breadcrumbs_image_split_grid_class_names('wide'))->toBe('mx-auto flex w-full max-w-[1160px] flex-col py-16 max-xl:px-0')
        ->and(matrix_get_hero_with_breadcrumbs_image_split_grid_class_names('wide'))->not->toContain('lg:grid')
        ->and(matrix_get_hero_with_breadcrumbs_image_split_image_column_class_names('wide'))->toContain('order-2')
        ->and(matrix_get_hero_with_breadcrumbs_image_split_image_column_class_names('wide'))->not->toContain('lg:border-l-2')
        ->and(matrix_get_hero_with_breadcrumbs_image_split_column_class_names('wide'))->toContain('order-1')
        ->and(matrix_get_hero_with_breadcrumbs_image_split_column_class_names('wide'))->not->toContain('max-w-')
        ->and(matrix_get_hero_with_breadcrumbs_image_split_column_class_names('wide'))->not->toContain('lg:pl-[52px]')
        ->and(matrix_get_hero_with_breadcrumbs_image_split_gradient_layout('wide'))->toBe('stacked')
        ->and(matrix_get_hero_with_breadcrumbs_image_split_gradient_layout('default'))->toBe('split');
});

test('utility page hero config uses wide stacked image split hero', function () {
    $config = matrix_get_utility_page_hero_config('Accessibility', 'Intro copy.');

    expect($config['layout_style'])->toBe('image_split')
        ->and($config['text_max_width'])->toBe('wide')
        ->and($config['background_color'])->toBe('#C6ECF4')
        ->and($config['content'])->toBe('<p>Intro copy.</p>');

    $view = matrix_prepare_hero_with_breadcrumbs_view_model($config);

    expect($view['layout_style'])->toBe('image_split')
        ->and($view['text_max_width'])->toBe('wide')
        ->and($view['breadcrumb_current_label'])->toBe('Accessibility')
        ->and(matrix_get_hero_with_breadcrumbs_image_split_grid_class_names($view['text_max_width']))->toContain('max-w-[1160px]');
});

test('hero image split gradient vars derive rgba stops from hex background', function () {
    $gradient_vars = matrix_get_hero_with_breadcrumbs_gradient_vars('#C6ECF4');

    expect($gradient_vars['gradient_solid'])->toBe('#C6ECF4')
        ->and($gradient_vars['gradient_soft'])->toBe('rgba(198, 236, 244, 0.9)')
        ->and($gradient_vars['gradient_clear'])->toBe('rgba(198, 236, 244, 0)');
});

test('hero mobile bottom fade gradient matches figma 2780:6668 stops', function () {
    $gradient = matrix_build_hero_image_split_mobile_bottom_fade_gradient(
        'rgba(198, 236, 244, 0)',
        'rgba(198, 236, 244, 0.9)',
        '#C6ECF4'
    );

    expect($gradient)->toContain('rgba(198, 236, 244, 0) 0%')
        ->and($gradient)->toContain('rgba(198, 236, 244, 0) 62%')
        ->and($gradient)->toContain('rgba(198, 236, 244, 0.9) 80%')
        ->and($gradient)->toContain('#C6ECF4 100%');
});

test('hero stacked image gradient keeps fade concentrated at the top edge', function () {
    $gradient = matrix_build_hero_image_split_stacked_image_gradient(
        'rgba(198, 236, 244, 0)',
        'rgba(198, 236, 244, 0.9)',
        '#C6ECF4'
    );

    expect($gradient)->toContain('#C6ECF4 0%')
        ->and($gradient)->toContain('#C6ECF4 18%')
        ->and($gradient)->toContain('rgba(198, 236, 244, 0) 52%');
});

test('auto breadcrumb mode falls back to auto breadcrumb data', function () {
    expect(function_exists('matrix_resolve_hero_breadcrumbs'))->toBeTrue();

    $auto_items = [
        [
            'title' => 'Home',
            'url' => 'https://example.com/',
            'target' => '',
        ],
        [
            'title' => 'Flexi',
            'url' => 'https://example.com/flexi/',
            'target' => '',
        ],
    ];

    $result = matrix_resolve_hero_breadcrumbs(
        'auto',
        [],
        '',
        [
            'items' => $auto_items,
            'current_label' => 'Hero demo',
        ]
    );

    expect($result['items'])->toBe($auto_items)
        ->and($result['current_label'])->toBe('Hero demo');
});
