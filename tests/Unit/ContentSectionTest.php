<?php

require_once dirname(__DIR__, 2) . '/inc/link-functions.php';
require_once dirname(__DIR__, 2) . '/inc/faq-functions.php';
require_once dirname(__DIR__, 2) . '/inc/content-section-functions.php';

beforeEach(function () {
    __wp_stub('home_url', fn ($path = '') => 'https://example.com' . $path);
});

test('content layout style resolves image positions and falls back to reverse layout', function () {
    expect(matrix_resolve_content_layout_style('image_left'))->toBe('image_left')
        ->and(matrix_resolve_content_layout_style('image_right'))->toBe('image_right')
        ->and(matrix_resolve_content_layout_style('', true))->toBe('image_right')
        ->and(matrix_resolve_content_layout_style('', false))->toBe('image_left');
});

test('content image height mode resolves match text and fixed minimum options', function () {
    expect(matrix_resolve_content_image_height_mode('match_text'))->toBe('match_text')
        ->and(matrix_resolve_content_image_height_mode('fixed_min'))->toBe('fixed_min')
        ->and(matrix_resolve_content_image_height_mode(''))->toBe('match_text');

    expect(matrix_get_content_grid_class_names('match_text'))->toContain('lg:items-stretch')
        ->and(matrix_get_content_grid_class_names('fixed_min'))->not->toContain('lg:items-stretch');

    expect(matrix_get_content_image_class_names('fixed_min'))->toContain('lg:min-h-[19.5rem]')
        ->and(matrix_get_content_image_class_names('match_text'))->toContain('lg:h-full')
        ->and(matrix_get_content_image_class_names('match_text'))->not->toContain('lg:min-h-[19.5rem]');
});

test('content column layout resolves one and two column grid classes', function () {
    expect(matrix_resolve_content_column_layout('two_column'))->toBe('two_column')
        ->and(matrix_resolve_content_column_layout('one_column'))->toBe('one_column')
        ->and(matrix_resolve_content_column_layout(''))->toBe('two_column');

    expect(matrix_get_content_grid_class_names('match_text', 'one_column'))
        ->toBe('grid grid-cols-1 gap-10 items-start w-full')
        ->and(matrix_get_content_grid_class_names('match_text', 'one_column'))->not->toContain('lg:grid-cols-2');

    expect(matrix_get_content_grid_class_names('match_text', 'two_column'))
        ->toContain('lg:grid-cols-2')
        ->and(matrix_get_content_grid_class_names('match_text', 'two_column'))->toContain('lg:items-stretch');

    expect(matrix_get_content_content_column_class_names('image_left', 'one_column'))->toBe('order-1')
        ->and(matrix_get_content_image_column_class_names('image_left', 'one_column'))->toBe('')
        ->and(matrix_get_content_image_class_names('match_text', 'one_column'))->not->toContain('lg:h-full');
});

test('content accent position resolves above and below heading', function () {
    expect(matrix_resolve_content_accent_position('below_heading'))->toBe('below_heading')
        ->and(matrix_resolve_content_accent_position('above_heading'))->toBe('above_heading')
        ->and(matrix_resolve_content_accent_position(''))->toBe('below_heading');
});

test('content text width resolves constrained and full max width classes', function () {
    expect(matrix_resolve_content_text_width_mode('constrained'))->toBe('constrained')
        ->and(matrix_resolve_content_text_width_mode('full'))->toBe('full')
        ->and(matrix_resolve_content_text_width_mode(''))->toBe('constrained')
        ->and(matrix_get_content_text_max_width_class_names('constrained'))->toBe('max-w-[720px]')
        ->and(matrix_get_content_text_max_width_class_names('full'))->toBe('max-w-full');
});

test('editor body content helpers expose scoped rich text classes', function () {
    expect(matrix_get_editor_body_content_class_names())->toBe('editor-body-content blog-single-content wp_editor entry-content')
        ->and(matrix_get_editor_body_content_wrapper_class_names())->toContain('max-w-[1018px]')
        ->and(matrix_get_editor_body_content_wrapper_class_names())->toContain('lg:py-[100px]');
});

test('content wrapper padding resolves default and top-only desktop spacing', function () {
    expect(matrix_get_content_wrapper_class_names('default'))->toContain('lg:py-[100px]')
        ->and(matrix_get_content_wrapper_class_names('default'))->not->toContain('lg:pb-0')
        ->and(matrix_get_content_wrapper_class_names('no_bottom'))->toContain('lg:pt-[100px]')
        ->and(matrix_get_content_wrapper_class_names('no_bottom'))->toContain('lg:pb-0')
        ->and(matrix_get_content_wrapper_class_names('no_bottom'))->not->toContain('lg:py-[100px]');
});

test('flexi section wrapper keeps standard max width and padding', function () {
    expect(matrix_get_flexi_section_wrapper_class_names())->toContain('max-w-[1018px]')
        ->and(matrix_get_flexi_section_wrapper_class_names())->toContain('py-12')
        ->and(matrix_get_flexi_section_wrapper_class_names(['lg:grid']))->toContain('lg:grid');
});

test('shared section vertical padding resolves default standard and bottom only modes', function () {
    expect(matrix_resolve_section_vertical_padding('bottom_only'))->toBe('bottom_only')
        ->and(matrix_resolve_section_vertical_padding('standard'))->toBe('standard')
        ->and(matrix_resolve_section_vertical_padding('unknown'))->toBe('default');

    expect(matrix_get_section_vertical_padding_classes('default', 'lg:py-[100px]'))->toBe('py-12 lg:py-[100px]')
        ->and(matrix_get_section_vertical_padding_classes('standard', 'lg:py-[100px]'))->toBe('py-12')
        ->and(matrix_get_section_vertical_padding_classes('bottom_only', 'lg:py-24'))->toBe('pt-0 pb-12 lg:pt-0 lg:pb-24');
});

test('content background style supports preset and custom values', function () {
    expect(matrix_get_content_background_style('white'))->toBe('background-color: #FFFFFF;')
        ->and(matrix_get_content_background_style('cream'))->toBe('background-color: #FBF8F3;')
        ->and(matrix_get_content_background_style('light_blue'))->toBe('background-color: #C6ECF4;')
        ->and(matrix_get_content_background_style('navy'))->toBe('background-color: #024B79;')
        ->and(matrix_get_content_background_style('color', '#E9E2F7', ''))->toContain('#E9E2F7')
        ->and(matrix_get_content_background_style('gradient', '', 'linear-gradient(135deg, #fff 0%, #000 100%)'))
        ->toContain('linear-gradient(135deg, #fff 0%, #000 100%)')
        ->and(matrix_get_content_background_style('image', '#FBF8F3', ''))->toContain('#FBF8F3');
});

test('content background image overlay resolves opacity and rgba output', function () {
    expect(matrix_resolve_content_background_image_overlay_opacity('25'))->toBe(25)
        ->and(matrix_resolve_content_background_image_overlay_opacity(''))->toBe(50)
        ->and(matrix_get_content_background_image_overlay_style('', 50))->toBe('')
        ->and(matrix_get_content_background_image_overlay_style('#024B79', 0))->toBe('')
        ->and(matrix_get_content_background_image_overlay_style('#024B79', 50))
        ->toBe('background-color: rgba(2, 75, 121, 0.50);');
});

test('content color scheme resolves inverse for navy backgrounds', function () {
    expect(matrix_resolve_content_color_scheme('default', 'navy'))->toBe('inverse')
        ->and(matrix_resolve_content_color_scheme('inverse', 'white'))->toBe('inverse')
        ->and(matrix_resolve_content_color_scheme('default', 'white'))->toBe('default');

    $inverse = matrix_get_content_theme_classes('inverse');

    expect($inverse['heading'])->toBe('text-white')
        ->and($inverse['rich_text'])->toContain('text-white');
});

test('content pdf helpers expose icon and document link classes', function () {
    expect(matrix_get_content_pdf_icon_svg())->toContain('<svg')
        ->and(matrix_get_content_pdf_icon_svg())->toContain('aria-hidden="true"')
        ->and(matrix_get_content_document_link_class_names())->toContain('btn');
});

test('content rich text wrapper includes paragraph spacing classes', function () {
    expect(matrix_get_content_rich_text_wrapper_class_names('medium', 'max-w-[720px]'))
        ->toContain('[&_p]:mb-4')
        ->and(matrix_get_content_rich_text_wrapper_class_names('medium', 'max-w-[720px]'))->toContain('[&_p:last-child]:mb-0')
        ->and(matrix_get_content_rich_text_wrapper_class_names('bold', ''))->toContain('font-bold');
});

test('content button helpers normalize links and class names', function () {
    expect(matrix_normalize_content_link([
        'title' => ' Support Us ',
        'url' => 'https://example.com/support/',
    ]))->toMatchArray([
        'title' => 'Support Us',
        'url' => 'https://example.com/support/',
        'target' => '_self',
    ])
        ->and(matrix_get_content_button_class_names('outline'))->toContain('bg-transparent')
        ->and(matrix_get_content_button_class_names('outline'))->toContain('text-[#024B79]')
        ->and(matrix_get_content_button_class_names('filled'))->toContain('bg-[#024B79]')
        ->and(matrix_get_content_button_class_names('filled'))->toContain('text-white')
        ->and(matrix_get_content_button_class_names('filled', 'inverse'))->toContain('text-white')
        ->and(matrix_get_content_button_class_names('filled', 'inverse'))->toContain('border-white');
});
