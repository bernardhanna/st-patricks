<?php

$section_id = 'content-section-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());
$heading = trim((string) get_sub_field('heading'));
$heading_tag = (string) get_sub_field('heading_tag');
$intro_text = get_sub_field('intro_text');
$content = get_sub_field('content');
$image = get_sub_field('image');
$layout_style = matrix_resolve_content_layout_style(
    get_sub_field('layout_style'),
    (bool) get_sub_field('reverse_layout')
);
$accent_position = matrix_resolve_content_accent_position(get_sub_field('accent_position'));
$column_layout = matrix_resolve_content_column_layout(get_sub_field('column_layout'));
$image_height_mode = matrix_resolve_content_image_height_mode(get_sub_field('image_height_mode'));
$text_width_mode = matrix_resolve_content_text_width_mode(get_sub_field('text_width'));
$text_max_width_classes = matrix_get_content_text_max_width_class_names($text_width_mode);
$background_type = (string) get_sub_field('background_type');
$background_color = (string) get_sub_field('background_color');
$background_gradient = (string) get_sub_field('background_gradient');
$color_scheme = matrix_resolve_content_color_scheme(
    get_sub_field('color_scheme'),
    $background_type
);
$theme_classes = matrix_get_content_theme_classes($color_scheme);
$primary_button = matrix_normalize_content_link(get_sub_field('primary_button'));
$document_link = matrix_normalize_content_link(get_sub_field('document_link'));
$secondary_button = matrix_normalize_content_link(get_sub_field('secondary_button'));
$primary_button_variant = matrix_resolve_content_button_variant(
    get_sub_field('primary_button_variant'),
    'filled'
);
$secondary_button_variant = matrix_resolve_content_button_variant(
    get_sub_field('secondary_button_variant'),
    'outline'
);

$show_heading = $heading !== '';

$has_intro = matrix_content_has_visible_rich_text($intro_text);
$has_content = matrix_content_has_visible_rich_text($content);
$has_image = (bool) $image;
$has_actions = (bool) ($primary_button || $document_link || $secondary_button);

if (! $show_heading && ! $has_intro && ! $has_content && ! $has_image && ! $has_actions) {
    return;
}

$allowed_tags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p'];
if (! in_array($heading_tag, $allowed_tags, true)) {
    $heading_tag = 'h2';
}

$image_alt = '';
if ($image) {
    $image_alt = (string) get_post_meta($image, '_wp_attachment_image_alt', true);
    if ($image_alt === '') {
        $image_alt = (string) get_the_title($image);
    }
}

if ($image_alt === '') {
    $image_alt = $heading !== '' ? $heading : 'Section image';
}

$heading_id = $section_id . '-heading';
$background_style = matrix_get_content_background_style($background_type, $background_color, $background_gradient);
$image_column_class = matrix_get_content_image_column_class_names($layout_style, $column_layout);
$content_column_class = matrix_get_content_content_column_class_names($layout_style, $column_layout);

$wrapper_classes = matrix_get_content_wrapper_class_names(get_sub_field('vertical_padding'));


$accent_markup = '<div class="h-[4px] w-10 bg-[#6FC9C0]" aria-hidden="true"></div>';
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
    data-content-scheme="<?php echo esc_attr($color_scheme); ?>"
    class="flex overflow-hidden relative"
    style="<?php echo esc_attr($background_style); ?>"
    <?php if ($show_heading) { ?>
        aria-labelledby="<?php echo esc_attr($heading_id); ?>"
    <?php } ?>
>
    <div class="<?php echo esc_attr($wrapper_classes); ?>">
        <div class="<?php echo esc_attr(matrix_get_content_grid_class_names($image_height_mode, $column_layout)); ?>">
            <article class="<?php echo esc_attr($content_column_class); ?> order-1 flex w-full flex-col gap-8">
                <?php if ($show_heading) { ?>
                    <header class="flex flex-col gap-8 w-full">
                        <?php if ($accent_position === 'above_heading') { ?>
                            <?php echo $accent_markup; ?>
                        <?php } ?>

                        <<?php echo esc_attr($heading_tag); ?>
                            id="<?php echo esc_attr($heading_id); ?>"
                            class="font-primary text-[24px] font-semibold leading-[28px] tracking-[-0.18px] lg:text-[30px] lg:leading-[36px] lg:tracking-[-0.225px] <?php echo esc_attr($theme_classes['heading']); ?>"
                        >
                            <?php echo esc_html($heading); ?>
                        </<?php echo esc_attr($heading_tag); ?>>

                        <?php if ($accent_position === 'below_heading') { ?>
                            <?php echo $accent_markup; ?>
                        <?php } ?>
                    </header>
                <?php } ?>

                <?php if ($has_intro) { ?>
                    <div class="<?php echo esc_attr(matrix_get_content_rich_text_wrapper_class_names('bold', $text_max_width_classes, $color_scheme)); ?>">
                        <?php echo matrix_kses_rich_text($intro_text); ?>
                    </div>
                <?php } ?>

                <?php if ($has_content) { ?>
                    <div class="<?php echo esc_attr(matrix_get_content_rich_text_wrapper_class_names('medium', $text_max_width_classes, $color_scheme)); ?>">
                        <?php echo matrix_kses_rich_text($content); ?>
                    </div>
                <?php } ?>

                <?php if ($document_link) { ?>
                    <?php
                    $document_target = $document_link['target'] !== '' ? $document_link['target'] : '_blank';
                    ?>
                    <a
                        href="<?php echo esc_url($document_link['url']); ?>"
                        target="<?php echo esc_attr($document_target); ?>"
                        class="<?php echo esc_attr(matrix_get_content_document_link_class_names($color_scheme)); ?>"
                        <?php if ($document_target === '_blank') { ?>
                            rel="noopener noreferrer"
                        <?php } ?>
                    >
                        <?php echo matrix_get_content_pdf_icon_svg(); ?>
                        <span><?php echo esc_html($document_link['title']); ?></span>
                        <span aria-hidden="true">→</span>
                    </a>
                <?php } ?>

                <?php if ($primary_button || $secondary_button) { ?>
                    <div class="flex flex-wrap gap-4 lg:gap-2.5">
                        <?php if ($primary_button) { ?>
                            <a
                                href="<?php echo esc_url($primary_button['url']); ?>"
                                target="<?php echo esc_attr($primary_button['target'] !== '' ? $primary_button['target'] : '_self'); ?>"
                                class="<?php echo esc_attr(matrix_get_content_button_class_names($primary_button_variant, $color_scheme)); ?>"
                                <?php if ($primary_button['target'] === '_blank') { ?>
                                    rel="noopener noreferrer"
                                <?php } ?>
                            >
                                <?php echo esc_html($primary_button['title']); ?>
                            </a>
                        <?php } ?>

                        <?php if ($secondary_button) { ?>
                            <a
                                href="<?php echo esc_url($secondary_button['url']); ?>"
                                target="<?php echo esc_attr($secondary_button['target'] !== '' ? $secondary_button['target'] : '_self'); ?>"
                                class="<?php echo esc_attr(matrix_get_content_button_class_names($secondary_button_variant, $color_scheme)); ?>"
                                <?php if ($secondary_button['target'] === '_blank') { ?>
                                    rel="noopener noreferrer"
                                <?php } ?>
                            >
                                <?php echo esc_html($secondary_button['title']); ?>
                            </a>
                        <?php } ?>
                    </div>
                <?php } ?>
            </article>

            <?php if ($image) { ?>
                <div class="<?php echo esc_attr(matrix_get_content_image_wrapper_class_names($image_column_class, $image_height_mode, $column_layout)); ?>">
                    <figure class="<?php echo esc_attr(matrix_get_content_image_figure_class_names($image_height_mode, $column_layout)); ?>">
                        <?php
                        echo wp_get_attachment_image($image, 'full', false, [
                            'alt' => esc_attr($image_alt),
                            'class' => matrix_get_content_image_class_names($image_height_mode, $column_layout),
                            'loading' => 'lazy',
                        ]);
                        ?>
                    </figure>
                </div>
            <?php } ?>
        </div>
    </div>
</section>
