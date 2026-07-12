<?php

function matrix_resolve_content_layout_style($layout_style, $reverse_layout = false)
{
    $layout_style = is_string($layout_style) ? trim($layout_style) : '';

    if ($layout_style === 'image_right') {
        return 'image_right';
    }

    if ($layout_style === 'image_left') {
        return 'image_left';
    }

    return $reverse_layout ? 'image_right' : 'image_left';
}

function matrix_resolve_content_image_height_mode($value)
{
    $value = is_string($value) ? trim($value) : '';

    if ($value === 'fixed_min') {
        return 'fixed_min';
    }

    return 'match_text';
}

function matrix_resolve_content_column_layout($value)
{
    $value = is_string($value) ? trim($value) : '';

    if ($value === 'one_column') {
        return 'one_column';
    }

    return 'two_column';
}

function matrix_get_content_grid_class_names($image_height_mode, $column_layout = 'two_column')
{
    if (matrix_resolve_content_column_layout($column_layout) === 'one_column') {
        return 'grid grid-cols-1 gap-10 items-start w-full';
    }

    $classes = 'grid grid-cols-1 gap-10 items-start w-full lg:grid-cols-2 lg:gap-8';

    if (matrix_resolve_content_image_height_mode($image_height_mode) === 'match_text') {
        return $classes . ' lg:items-stretch';
    }

    return $classes;
}

function matrix_get_content_content_column_class_names($layout_style, $column_layout = 'two_column')
{
    if (matrix_resolve_content_column_layout($column_layout) === 'one_column') {
        return 'order-1';
    }

    return $layout_style === 'image_right' ? 'lg:order-1 order-1' : 'lg:order-2 order-1';
}

function matrix_get_content_image_column_class_names($layout_style, $column_layout = 'two_column')
{
    if (matrix_resolve_content_column_layout($column_layout) === 'one_column') {
        return '';
    }

    return $layout_style === 'image_right' ? 'lg:order-2' : 'lg:order-1';
}

function matrix_get_content_image_wrapper_class_names($image_column_class, $image_height_mode, $column_layout = 'two_column')
{
    $classes = array_filter([
        $image_column_class !== '' ? $image_column_class : null,
        'order-2',
        'flex',
        'justify-center',
        'lg:justify-start',
    ]);

    if (
        matrix_resolve_content_column_layout($column_layout) === 'two_column'
        && matrix_resolve_content_image_height_mode($image_height_mode) === 'match_text'
    ) {
        $classes[] = 'lg:h-full';
    }

    return implode(' ', $classes);
}

function matrix_get_content_image_figure_class_names($image_height_mode, $column_layout = 'two_column')
{
    $classes = ['w-full'];

    if (matrix_resolve_content_column_layout($column_layout) === 'two_column') {
        $classes[] = 'lg:max-w-[502px]';
    }

    if (
        matrix_resolve_content_column_layout($column_layout) === 'two_column'
        && matrix_resolve_content_image_height_mode($image_height_mode) === 'match_text'
    ) {
        $classes[] = 'lg:h-full';
    }

    return implode(' ', $classes);
}

function matrix_get_content_image_class_names($image_height_mode, $column_layout = 'two_column')
{
    $classes = 'h-[212px] w-full rounded-[8px] object-cover';

    if (matrix_resolve_content_column_layout($column_layout) === 'one_column') {
        return $classes;
    }

    $classes .= ' lg:h-auto';

    if (matrix_resolve_content_image_height_mode($image_height_mode) === 'fixed_min') {
        return $classes . ' lg:min-h-[19.5rem]';
    }

    return $classes . ' lg:h-full lg:min-h-0';
}

function matrix_resolve_content_accent_position($value)
{
    $value = is_string($value) ? trim($value) : '';

    if ($value === 'above_heading') {
        return 'above_heading';
    }

    return 'below_heading';
}

function matrix_resolve_content_text_width_mode($value)
{
    $value = is_string($value) ? trim($value) : '';

    if ($value === 'full') {
        return 'full';
    }

    return 'constrained';
}

function matrix_get_content_text_max_width_class_names($text_width_mode)
{
    return matrix_resolve_content_text_width_mode($text_width_mode) === 'full'
        ? 'max-w-full'
        : 'max-w-[720px]';
}

function matrix_resolve_content_button_variant($value, $default = 'filled')
{
    $value = is_string($value) ? trim($value) : '';

    if ($value === 'outline') {
        return 'outline';
    }

    return $default === 'outline' ? 'outline' : 'filled';
}

function matrix_normalize_content_link($link)
{
    if (! is_array($link)) {
        return null;
    }

    $title = trim((string) ($link['title'] ?? ''));
    $url = trim((string) ($link['url'] ?? ''));

    if ($title === '' || $url === '') {
        return null;
    }

    return [
        'title' => $title,
        'url' => $url,
        'target' => matrix_normalize_link_target($url, (string) ($link['target'] ?? '')),
    ];
}

function matrix_content_has_visible_rich_text($value)
{
    return is_string($value) && trim(strip_tags($value)) !== '';
}

function matrix_resolve_content_color_scheme($scheme = '', $background_type = '')
{
    if (trim((string) $scheme) === 'inverse') {
        return 'inverse';
    }

    if (trim((string) $background_type) === 'navy') {
        return 'inverse';
    }

    return 'default';
}

/**
 * @return array{heading: string, rich_text: string, document_link: string}
 */
function matrix_get_content_theme_classes(string $color_scheme = 'default'): array
{
    if (matrix_resolve_content_color_scheme($color_scheme) === 'inverse') {
        return [
            'heading' => 'text-white',
            'rich_text' => 'text-white [&_a]:text-white [&_a]:underline hover:[&_a]:no-underline',
            'document_link' => 'text-white hover:text-white/90 focus-visible:text-white/90',
        ];
    }

    return [
        'heading' => 'text-[#1E244B]',
        'rich_text' => 'text-[#08284B] [&_a]:text-[#024B79] [&_a]:underline hover:[&_a]:no-underline',
        'document_link' => 'text-[#1E244B] transition-colors duration-200 hover:text-[#024B79] focus-visible:text-[#024B79]',
    ];
}

function matrix_get_content_rich_text_wrapper_class_names($weight = 'medium', $text_max_width_classes = '', $color_scheme = 'default')
{
    $theme_classes = matrix_get_content_theme_classes($color_scheme);

    $classes = array_filter([
        'wp_editor',
        is_string($text_max_width_classes) && $text_max_width_classes !== '' ? $text_max_width_classes : null,
        'font-primary',
        'text-[16px]',
        $weight === 'bold' ? 'font-bold' : 'font-medium',
        'leading-[28px]',
        $theme_classes['rich_text'],
        '[&_p]:mb-4',
        '[&_p:last-child]:mb-0',
        '[&_ul]:mb-4',
        '[&_ul]:list-disc',
        '[&_ul]:pl-6',
        '[&_ol]:mb-4',
        '[&_ol]:list-decimal',
        '[&_ol]:pl-6',
        '[&_li]:mb-2',
        '[&_a]:font-medium',
    ]);

    return implode(' ', $classes);
}

function matrix_wysiwyg_should_use_policy_section_layout($html): bool
{
    $html = is_string($html) ? $html : '';

    return preg_match('/<h2\b/i', $html) === 1
        && preg_match('/<p\b[^>]*class=["\'][^"\']*\bintro\b/i', $html) === 1;
}

function matrix_get_dom_node_html(DOMDocument $document, DOMNode $node): string
{
    $html = $document->saveHTML($node);

    return is_string($html) ? $html : '';
}

function matrix_get_dom_node_inner_html(DOMDocument $document, DOMNode $node): string
{
    $html = '';

    foreach ($node->childNodes as $child_node) {
        $html .= matrix_get_dom_node_html($document, $child_node);
    }

    return $html;
}

function matrix_policy_wysiwyg_node_has_visible_content(DOMNode $node): bool
{
    return trim($node->textContent) !== '';
}

function matrix_get_policy_wysiwyg_fragment_html(DOMDocument $document, DOMNode $node): string
{
    if ($node instanceof DOMElement && strtolower($node->tagName) === 'div') {
        $class_name = ' ' . $node->getAttribute('class') . ' ';

        if (strpos($class_name, ' section-head ') !== false) {
            return matrix_get_dom_node_inner_html($document, $node);
        }
    }

    return matrix_get_dom_node_html($document, $node);
}

/**
 * Split migrated policy WYSIWYG markup into content-style sections.
 *
 * @return array<int, array{heading: string, content: string}>
 */
function matrix_prepare_policy_wysiwyg_sections($html): array
{
    $html = is_string($html) ? trim($html) : '';

    if ($html === '' || ! class_exists('DOMDocument')) {
        return [];
    }

    $previous_errors = libxml_use_internal_errors(true);
    $document = new DOMDocument('1.0', 'UTF-8');
    $loaded = $document->loadHTML(
        '<?xml encoding="UTF-8"><div id="matrix-policy-wysiwyg-root">' . $html . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();
    libxml_use_internal_errors($previous_errors);

    if (! $loaded) {
        return [];
    }

    $root = $document->getElementById('matrix-policy-wysiwyg-root');

    if (! $root instanceof DOMElement) {
        return [];
    }

    $sections = [];
    $current_section = null;
    $intro_html = '';

    foreach ($root->childNodes as $node) {
        if ($node instanceof DOMElement && strtolower($node->tagName) === 'h2') {
            if (is_array($current_section) && trim(strip_tags($current_section['content'])) !== '') {
                $sections[] = $current_section;
            }

            $current_section = [
                'heading' => trim($node->textContent),
                'content' => $intro_html,
            ];
            $intro_html = '';
            continue;
        }

        if (! matrix_policy_wysiwyg_node_has_visible_content($node)) {
            continue;
        }

        $fragment_html = matrix_get_policy_wysiwyg_fragment_html($document, $node);

        if ($current_section === null) {
            $intro_html .= $fragment_html;
            continue;
        }

        $current_section['content'] .= $fragment_html;
    }

    if (is_array($current_section) && trim(strip_tags($current_section['content'])) !== '') {
        $sections[] = $current_section;
    }

    return array_values(array_filter($sections, static function ($section) {
        return is_array($section)
            && trim((string) ($section['heading'] ?? '')) !== ''
            && trim(strip_tags((string) ($section['content'] ?? ''))) !== '';
    }));
}

function matrix_get_content_background_style($background_type, $background_color = '', $background_gradient = '')
{
    $background_type = is_string($background_type) ? trim($background_type) : 'gradient';

    if ($background_type === 'image') {
        $color = trim((string) $background_color);

        if ($color === '') {
            $color = '#FFFFFF';
        }

        return function_exists('matrix_get_faq_background_style')
            ? matrix_get_faq_background_style($color)
            : 'background-color: ' . $color . ';';
    }

    if ($background_type === 'white') {
        return 'background-color: #FFFFFF;';
    }

    if ($background_type === 'cream') {
        return 'background-color: #FBF8F3;';
    }

    if ($background_type === 'light_blue') {
        return 'background-color: #C6ECF4;';
    }

    if ($background_type === 'navy') {
        return 'background-color: #024B79;';
    }

    if ($background_type === 'gradient') {
        $gradient = trim((string) $background_gradient);

        if ($gradient === '') {
            $gradient = 'linear-gradient(278deg, #F8F6F3 3.24%, #F5F6ED 90.88%)';
        }

        return function_exists('matrix_get_faq_background_style')
            ? matrix_get_faq_background_style($gradient)
            : 'background: ' . $gradient . ';';
    }

    $color = trim((string) $background_color);

    if ($color === '') {
        $color = '#FFFFFF';
    }

    return function_exists('matrix_get_faq_background_style')
        ? matrix_get_faq_background_style($color)
        : 'background-color: ' . $color . ';';
}

function matrix_resolve_content_background_image_overlay_opacity($value)
{
    $value = trim((string) $value);

    if (in_array($value, ['0', '25', '50', '75'], true)) {
        return (int) $value;
    }

    return 50;
}

function matrix_get_content_background_image_overlay_style(string $overlay_color, $overlay_opacity = 50): string
{
    $overlay_color = trim($overlay_color);
    $overlay_opacity = matrix_resolve_content_background_image_overlay_opacity($overlay_opacity);

    if ($overlay_color === '' || $overlay_opacity <= 0) {
        return '';
    }

    if (preg_match('/^#([A-Fa-f0-9]{6})$/', $overlay_color, $matches)) {
        $hex = $matches[1];
        $red = hexdec(substr($hex, 0, 2));
        $green = hexdec(substr($hex, 2, 2));
        $blue = hexdec(substr($hex, 4, 2));
        $alpha = number_format($overlay_opacity / 100, 2, '.', '');

        return 'background-color: rgba(' . $red . ', ' . $green . ', ' . $blue . ', ' . $alpha . ');';
    }

    return function_exists('matrix_get_faq_background_style')
        ? matrix_get_faq_background_style($overlay_color)
        : 'background-color: ' . $overlay_color . ';';
}

function matrix_get_content_button_class_names($variant = 'filled', $color_scheme = 'default')
{
    $is_inverse = matrix_resolve_content_color_scheme($color_scheme) === 'inverse';

    if ($is_inverse) {
        if ($variant === 'outline') {
            return 'btn inline-flex h-[36px] w-fit items-center justify-center whitespace-nowrap rounded-[6px] border border-white/80 bg-transparent px-3 text-[14px] font-medium leading-[24px] text-white hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white';
        }

        return 'btn inline-flex h-[36px] w-fit items-center justify-center whitespace-nowrap rounded-[6px] border border-white bg-transparent px-3 text-[14px] font-medium leading-[24px] text-white hover:bg-white/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white';
    }

    if ($variant === 'outline') {
        return 'btn inline-flex h-[36px] w-fit items-center justify-center whitespace-nowrap rounded-[6px] border border-[#024B79] bg-transparent px-3 text-[14px] font-medium leading-[24px] text-[#024B79] hover:bg-[#024B79]/5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]';
    }

    return 'btn inline-flex h-[36px] w-fit items-center justify-center whitespace-nowrap rounded-[6px] border border-[#024B79] bg-[#024B79] px-3 text-[14px] font-medium leading-[24px] text-white hover:bg-[#013a5f] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]';
}

function matrix_get_content_document_link_class_names($color_scheme = 'default')
{
    $theme_classes = matrix_get_content_theme_classes($color_scheme);

    return 'btn inline-flex w-fit items-center gap-2 font-primary text-[16px] font-semibold leading-[24px] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79] ' . $theme_classes['document_link'];
}

function matrix_get_content_pdf_icon_svg()
{
    return '<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M11.5 1.5H4.5C3.67 1.5 3 2.17 3 3V17C3 17.83 3.67 18.5 4.5 18.5H15.5C16.33 18.5 17 17.83 17 17V6.5L11.5 1.5Z" fill="#E53935"/><path d="M11 2V7H16" fill="#FFCDD2"/><path d="M6.5 11H13.5M6.5 14H11" stroke="white" stroke-width="1.25" stroke-linecap="round"/></svg>';
}

/**
 * Class names for full-width WordPress editor body content (pages, singles).
 * Scoped separately from flexi WYSIWYG snippets that only use `wp_editor`.
 */
function matrix_get_editor_body_content_class_names()
{
    return 'editor-body-content blog-single-content wp_editor entry-content';
}

/**
 * Wrapper layout for editor body content sections.
 */
function matrix_get_editor_body_content_wrapper_class_names()
{
    return 'mx-auto w-full max-w-[1018px] px-5 py-12 lg:px-0 lg:py-[100px]';
}

/**
 * Standard inner wrapper for flexi blocks after custom padding_settings removal.
 *
 * @param array<int, string> $extra
 */
function matrix_get_flexi_section_wrapper_class_names(array $extra = []): string
{
    return implode(' ', array_unique(array_merge([
        'mx-auto',
        'flex',
        'w-full',
        'max-w-[1018px]',
        'flex-col',
        'max-xl:px-5',
        'py-12',
        'lg:py-[100px]',
    ], $extra)));
}

function matrix_resolve_section_vertical_padding($value = '')
{
    $value = trim((string) $value);

    if (in_array($value, ['standard', 'bottom_only'], true)) {
        return $value;
    }

    return 'default';
}

function matrix_get_section_vertical_padding_classes(
    string $vertical_padding = 'default',
    string $desktop_padding = 'lg:py-[100px]',
    string $desktop_bottom_padding = ''
): string {
    $vertical_padding = matrix_resolve_section_vertical_padding($vertical_padding);
    $desktop_bottom_padding = $desktop_bottom_padding !== ''
        ? $desktop_bottom_padding
        : (string) preg_replace('/\bpy-/', 'pb-', $desktop_padding);

    if ($vertical_padding === 'standard') {
        return 'py-12';
    }

    if ($vertical_padding === 'bottom_only') {
        return "pt-0 pb-12 lg:pt-0 {$desktop_bottom_padding}";
    }

    return "py-12 {$desktop_padding}";
}

function matrix_resolve_content_vertical_padding($value = '')
{
    return trim((string) $value) === 'no_bottom' ? 'no_bottom' : 'default';
}

function matrix_get_content_wrapper_class_names($vertical_padding = 'default')
{
    $vertical_padding = matrix_resolve_content_vertical_padding($vertical_padding);

    $classes = [
        'mx-auto',
        'flex',
        'w-full',
        'max-w-[1018px]',
        'flex-col',
        'px-4',
        'lg:px-0',
        'py-12',
    ];

    if ($vertical_padding === 'no_bottom') {
        $classes[] = 'lg:pt-[100px]';
        $classes[] = 'lg:pb-0';
    } else {
        $classes[] = 'lg:py-[100px]';
    }

    return implode(' ', $classes);
}
