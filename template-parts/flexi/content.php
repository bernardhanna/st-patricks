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
$background_type = (string) get_sub_field('background_type');
$background_color = (string) get_sub_field('background_color');
$background_gradient = (string) get_sub_field('background_gradient');
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

if ($heading === '') {
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
    $image_alt = $heading;
}

$heading_id = $section_id . '-heading';
$background_style = matrix_get_content_background_style($background_type, $background_color, $background_gradient);
$image_column_class = $layout_style === 'image_right' ? 'lg:order-2' : 'lg:order-1';
$content_column_class = $layout_style === 'image_right' ? 'lg:order-1' : 'lg:order-2';

$wrapper_classes = ['mx-auto', 'flex', 'w-full', 'max-w-[1018px]', 'flex-col', 'pt-5', 'pb-5', 'max-xl:px-5', 'lg:py-[100px]'];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size = get_sub_field('screen_size');
        $padding_top = get_sub_field('padding_top');
        $padding_bottom = get_sub_field('padding_bottom');

        if ($screen_size !== '' && $padding_top !== '' && $padding_top !== null) {
            $wrapper_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
        }

        if ($screen_size !== '' && $padding_bottom !== '' && $padding_bottom !== null) {
            $wrapper_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
        }
    }
}

$accent_markup = '<div class="h-[4px] w-10 bg-[#6FC9C0]" aria-hidden="true"></div>';
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
    class="relative flex overflow-hidden"
    style="<?php echo esc_attr($background_style); ?>"
    aria-labelledby="<?php echo esc_attr($heading_id); ?>"
>
    <div class="<?php echo esc_attr(implode(' ', array_unique($wrapper_classes))); ?>">
        <div class="grid w-full grid-cols-1 items-center gap-10 lg:grid-cols-2 lg:gap-16">
            <?php if ($image) { ?>
                <div class="<?php echo esc_attr($image_column_class); ?> flex justify-center lg:justify-start">
                    <figure class="w-full max-w-[502px]">
                        <?php
                        echo wp_get_attachment_image($image, 'full', false, [
                            'alt' => esc_attr($image_alt),
                            'class' => 'h-auto w-full max-h-[346px] rounded-[8px] object-cover',
                            'loading' => 'lazy',
                        ]);
                        ?>
                    </figure>
                </div>
            <?php } ?>

            <article class="<?php echo esc_attr($content_column_class); ?> flex w-full flex-col gap-8">
                <header class="flex w-full flex-col gap-8">
                    <?php if ($accent_position === 'above_heading') { ?>
                        <?php echo $accent_markup; ?>
                    <?php } ?>

                    <<?php echo esc_attr($heading_tag); ?>
                        id="<?php echo esc_attr($heading_id); ?>"
                        class="font-primary text-[24px] font-semibold leading-[28px] tracking-[-0.18px] text-[#1E244B] lg:text-[30px] lg:leading-[36px] lg:tracking-[-0.225px]"
                    >
                        <?php echo esc_html($heading); ?>
                    </<?php echo esc_attr($heading_tag); ?>>

                    <?php if ($accent_position === 'below_heading') { ?>
                        <?php echo $accent_markup; ?>
                    <?php } ?>
                </header>

                <?php if (matrix_content_has_visible_rich_text($intro_text)) { ?>
                    <div class="wp_editor max-w-[720px] font-primary text-[16px] font-semibold leading-[28px] text-[#08284B] lg:text-[18px] [&_p:last-child]:mb-0">
                        <?php echo wp_kses_post($intro_text); ?>
                    </div>
                <?php } ?>

                <?php if (matrix_content_has_visible_rich_text($content)) { ?>
                    <div class="wp_editor max-w-[720px] font-primary text-[16px] font-normal leading-[28px] text-[#08284B] lg:text-[18px] [&_p:last-child]:mb-0">
                        <?php echo wp_kses_post($content); ?>
                    </div>
                <?php } ?>

                <?php if ($document_link) { ?>
                    <?php
                    $document_target = $document_link['target'] !== '' ? $document_link['target'] : '_blank';
                    ?>
                    <a
                        href="<?php echo esc_url($document_link['url']); ?>"
                        target="<?php echo esc_attr($document_target); ?>"
                        class="<?php echo esc_attr(matrix_get_content_document_link_class_names()); ?>"
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
                    <div class="flex flex-wrap gap-2.5">
                        <?php if ($primary_button) { ?>
                            <a
                                href="<?php echo esc_url($primary_button['url']); ?>"
                                target="<?php echo esc_attr($primary_button['target'] !== '' ? $primary_button['target'] : '_self'); ?>"
                                class="<?php echo esc_attr(matrix_get_content_button_class_names($primary_button_variant)); ?>"
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
                                class="<?php echo esc_attr(matrix_get_content_button_class_names($secondary_button_variant)); ?>"
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
        </div>
    </div>
</section>
