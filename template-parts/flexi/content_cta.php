<?php

$section_id = 'content-cta-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());
$heading = trim((string) get_sub_field('heading'));
$heading_tag = (string) get_sub_field('heading_tag');
$body = get_sub_field('body');
$button_link = matrix_normalize_content_cta_link(get_sub_field('button_link'));
$layout_style = matrix_resolve_content_cta_layout_style(get_sub_field('layout_style'));
$background_type = (string) get_sub_field('background_type');
$background_color = (string) get_sub_field('background_color');
$background_gradient = (string) get_sub_field('background_gradient');
$background_image = get_sub_field('background_image');
$background_tint_color = (string) get_sub_field('background_tint_color');
$background_image_opacity = get_sub_field('background_image_opacity');
$background_image_overlay_color = (string) get_sub_field('background_image_overlay_color');
$background_image_overlay_opacity = get_sub_field('background_image_overlay_opacity');
$color_scheme = matrix_resolve_content_cta_color_scheme(get_sub_field('color_scheme'));
$theme_classes = matrix_get_content_cta_theme_classes($color_scheme);
$uses_image_background = matrix_content_cta_uses_image_background($layout_style, $background_image);

if ($uses_image_background) {
    $background_style = matrix_get_content_cta_tint_background_style($background_tint_color);
    $background_image_opacity_style = matrix_get_content_cta_background_image_opacity_style($background_image_opacity);
    $background_image_overlay_style = matrix_get_content_cta_background_image_overlay_style(
        $background_image_overlay_color,
        $background_image_overlay_opacity
    );
    $background_image_alt = (string) get_post_meta($background_image, '_wp_attachment_image_alt', true);
} else {
    $background_style = matrix_get_content_cta_background_style($background_type, $background_color, $background_gradient);
    $background_image_opacity_style = '';
    $background_image_overlay_style = '';
    $background_image_alt = '';
}

if ($heading === '') {
    $heading = 'Are you a healthcare professional?';
}

if (! in_array($heading_tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p'], true)) {
    $heading_tag = 'h2';
}

if ($heading === '' && (! is_string($body) || trim(strip_tags($body)) === '') && ! is_array($button_link)) {
    return;
}

$heading_id = $section_id . '-heading';
$wrapper_classes = matrix_get_content_cta_wrapper_class_names($layout_style);
$is_inverse = $color_scheme === 'inverse';
$body_classes = $is_inverse
    ? '[&_p]:text-white/90 [&_a]:text-white [&_a]:underline hover:[&_a]:no-underline'
    : '[&_p]:text-[#08284B] [&_a]:text-[#024B79] [&_a]:underline hover:[&_a]:no-underline';
$heading_classes = matrix_get_content_cta_heading_class_names($layout_style, $theme_classes['heading']);
$rich_text_classes = matrix_get_content_cta_body_class_names($layout_style, $body_classes);
$content_row_classes = matrix_get_content_cta_content_row_class_names($layout_style);
$button_classes = matrix_get_content_cta_button_class_names($layout_style, $color_scheme);
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
    data-content-cta-layout="<?php echo esc_attr($layout_style); ?>"
    data-content-cta-scheme="<?php echo esc_attr($color_scheme); ?>"
    class="relative flex overflow-hidden"
    style="<?php echo esc_attr($background_style); ?>"
    <?php if ($heading !== '') { ?>
        aria-labelledby="<?php echo esc_attr($heading_id); ?>"
    <?php } ?>
>
    <?php if ($uses_image_background) { ?>
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <?php
            echo wp_get_attachment_image($background_image, 'full', false, [
                'alt' => esc_attr($background_image_alt),
                'class' => 'h-full w-full object-cover',
                'loading' => 'lazy',
                'style' => $background_image_opacity_style,
            ]);
            ?>
            <?php if ($background_image_overlay_style !== '') { ?>
                <div class="absolute inset-0" style="<?php echo esc_attr($background_image_overlay_style); ?>"></div>
            <?php } ?>
        </div>
    <?php } ?>

    <div class="<?php echo esc_attr($wrapper_classes); ?>">
        <header class="flex w-full flex-col gap-8">
            <<?php echo esc_attr($heading_tag); ?>
                id="<?php echo esc_attr($heading_id); ?>"
                class="<?php echo esc_attr($heading_classes); ?>"
            >
                <?php echo esc_html($heading); ?>
            </<?php echo esc_attr($heading_tag); ?>>

            <div class="<?php echo esc_attr($theme_classes['accent']); ?> h-[4px] w-10" aria-hidden="true"></div>
        </header>

        <div class="<?php echo esc_attr($content_row_classes); ?>">
            <?php if (is_string($body) && trim(strip_tags($body)) !== '') { ?>
                <div class="<?php echo esc_attr($rich_text_classes); ?>">
                    <?php echo matrix_kses_rich_text($body); ?>
                </div>
            <?php } ?>

            <?php if (is_array($button_link)) { ?>
                <?php $button_target = (string) ($button_link['target'] ?? '_self'); ?>
                <div class="flex shrink-0<?php echo $layout_style === 'default' ? ' lg:justify-end' : ''; ?>">
                    <a
                        href="<?php echo esc_url($button_link['url']); ?>"
                        target="<?php echo esc_attr($button_target); ?>"
                        class="<?php echo esc_attr($button_classes); ?>"
                        <?php if ($button_target === '_blank') { ?>
                            rel="noopener noreferrer"
                        <?php } ?>
                    >
                        <?php echo esc_html($button_link['title']); ?>
                    </a>
                </div>
            <?php } ?>
        </div>
    </div>
</section>
