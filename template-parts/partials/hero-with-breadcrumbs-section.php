<?php

$hero = is_array($args['hero'] ?? null) ? $args['hero'] : [];

if ($hero === []) {
    return;
}

extract($hero, EXTR_SKIP);
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    <?php if (($data_matrix_block ?? '') !== '') { ?>
        data-matrix-block="<?php echo esc_attr($data_matrix_block); ?>"
    <?php } ?>
    class="flex overflow-hidden relative flex-col <?php echo empty($has_body_content) ? 'hero-with-breadcrumbs--breadcrumbs-only' : ''; ?>"
    <?php if (! empty($has_body_content)) { ?>
        style="background-color: <?php echo esc_attr($background_color); ?>;"
        aria-labelledby="<?php echo esc_attr($hero_heading_id); ?>"
    <?php } else { ?>
        aria-label="<?php echo esc_attr__('Breadcrumb', 'matrix-starter'); ?>"
    <?php } ?>
>
    <?php if ($show_breadcrumbs && (! empty($breadcrumb_items) || ($breadcrumb_current_label ?? '') !== '')) { ?>
        <?php
        get_template_part('template-parts/partials/hero-breadcrumbs-nav', null, [
            'items' => $breadcrumb_items,
            'current_label' => $breadcrumb_current_label,
            'background_color' => $breadcrumb_background_color,
        ]);
        ?>
    <?php } ?>
    <?php if (! empty($has_body_content)) { ?>
    <div class="<?php echo esc_attr(matrix_get_hero_with_breadcrumbs_container_class_names()); ?>">

        <?php if ($layout_style === 'register_intro') { ?>
            <div class="<?php echo esc_attr(matrix_get_hero_with_breadcrumbs_register_intro_wrapper_class_names()); ?>">
                <div class="<?php echo esc_attr(matrix_get_hero_with_breadcrumbs_register_intro_layout_class_names()); ?>">
                    <div class="<?php echo esc_attr(matrix_get_hero_with_breadcrumbs_register_intro_text_column_class_names()); ?>">
                        <<?php echo esc_attr($heading_tag); ?>
                            id="<?php echo esc_attr($hero_heading_id); ?>"
                            class="<?php echo esc_attr(matrix_get_hero_with_breadcrumbs_register_intro_heading_class_names()); ?>"
                            style="color: <?php echo esc_attr($heading_color); ?>;"
                        >
                            <?php echo esc_html($heading); ?>
                        </<?php echo esc_attr($heading_tag); ?>>

                        <?php if (! empty($content)) { ?>
                            <div
                                class="<?php echo esc_attr(matrix_get_hero_with_breadcrumbs_register_intro_content_class_names()); ?>"
                                style="color: <?php echo esc_attr($text_color); ?>;"
                            >
                                <?php echo matrix_kses_rich_text($content); ?>
                            </div>
                        <?php } ?>
                    </div>

                    <?php if ($aside_heading !== '' || $primary_button) { ?>
                        <aside class="<?php echo esc_attr(matrix_get_hero_with_breadcrumbs_register_intro_aside_class_names()); ?>">
                            <?php if ($aside_heading !== '') { ?>
                                <p class="<?php echo esc_attr(matrix_get_hero_with_breadcrumbs_register_intro_aside_heading_class_names()); ?>">
                                    <?php echo esc_html($aside_heading); ?>
                                </p>
                            <?php } ?>

                            <?php if ($primary_button) { ?>
                                <?php
                                $button_target = $primary_button['target'] !== '' ? $primary_button['target'] : '_self';
                                $opens_external = $button_target === '_blank';
                                $show_button_icon = ! empty($show_primary_button_icon);
                                ?>
                                <a
                                    href="<?php echo esc_url($primary_button['url']); ?>"
                                    target="<?php echo esc_attr($button_target); ?>"
                                    class="<?php echo esc_attr(matrix_get_hero_with_breadcrumbs_register_intro_button_class_names()); ?>"
                                    <?php if ($opens_external) { ?>
                                        rel="noopener noreferrer"
                                    <?php } ?>
                                >
                                    <?php if ($show_button_icon && function_exists('matrix_get_hero_external_link_icon_svg')) { ?>
                                        <?php echo matrix_get_hero_external_link_icon_svg(); ?>
                                    <?php } ?>
                                    <span><?php echo esc_html($primary_button['title']); ?></span>
                                </a>
                            <?php } ?>
                        </aside>
                    <?php } ?>
                </div>
            </div>
        <?php } elseif ($layout_style === 'title_accent') { ?>
            <div class="w-full px-5 lg:px-[70px]">
                <div class="flex flex-col gap-8 max-w-[1018px]">
                    <<?php echo esc_attr($heading_tag); ?>
                        id="<?php echo esc_attr($hero_heading_id); ?>"
                        class="<?php echo esc_attr($heading_max_width_class); ?> font-primary text-[24px] not-italic font-semibold leading-[28px] tracking-[-0.18px] lg:text-[30px] lg:leading-[36px] lg:tracking-[-0.225px]"
                        style="color: <?php echo esc_attr($heading_color); ?>;"
                    >
                        <?php echo esc_html($heading); ?>
                    </<?php echo esc_attr($heading_tag); ?>>

                    <div class="h-[4px] w-10" style="background-color: <?php echo esc_attr($accent_color); ?>;" aria-hidden="true"></div>

                    <?php if (! empty($content)) { ?>
                        <div
                            class="<?php echo esc_attr($text_max_width_class); ?> font-primary text-[16px] not-italic font-medium leading-[28px] text-[#08284B] wp_editor [&_p:last-child]:mb-0"
                            style="color: <?php echo esc_attr($text_color); ?>;"
                        >
                            <?php echo matrix_kses_rich_text($content); ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        <?php } else { ?>
            <div class="<?php echo esc_attr(matrix_get_hero_with_breadcrumbs_image_split_grid_class_names($text_max_width)); ?>">
                <?php if ($hero_image) { ?>
                <div class="<?php echo esc_attr(matrix_get_hero_with_breadcrumbs_image_split_image_column_class_names($text_max_width)); ?>" style="border-color: <?php echo esc_attr($background_color); ?>;">
                    <?php
                    echo wp_get_attachment_image($hero_image, 'full', false, [
                        'alt' => esc_attr($hero_image_alt),
                        'title' => esc_attr($hero_image_title),
                        'class' => 'absolute inset-0 h-full w-full object-cover',
                        'loading' => 'eager',
                    ]);
                    ?>
                    <?php
                    get_template_part('template-parts/partials/hero-image-split-image-gradients', null, [
                        'gradient_solid' => $gradient_solid,
                        'gradient_soft' => $gradient_soft,
                        'gradient_clear' => $gradient_clear,
                        'background_color' => $background_color,
                        'layout' => matrix_get_hero_with_breadcrumbs_image_split_gradient_layout($text_max_width),
                    ]);
                    ?>
                </div>
                <?php } elseif ($text_max_width !== 'wide') { ?>
                <div class="<?php echo esc_attr(matrix_get_hero_with_breadcrumbs_image_split_image_column_class_names($text_max_width)); ?>" style="border-color: <?php echo esc_attr($background_color); ?>;">
                    <?php
                    get_template_part('template-parts/partials/hero-image-split-image-gradients', null, [
                        'gradient_solid' => $gradient_solid,
                        'gradient_soft' => $gradient_soft,
                        'gradient_clear' => $gradient_clear,
                        'background_color' => $background_color,
                        'layout' => matrix_get_hero_with_breadcrumbs_image_split_gradient_layout($text_max_width),
                    ]);
                    ?>
                </div>
                <?php } ?>

                <div class="<?php echo esc_attr(matrix_get_hero_with_breadcrumbs_image_split_column_class_names($text_max_width)); ?>">
                    <div class="<?php echo esc_attr(matrix_get_hero_with_breadcrumbs_image_split_text_group_class_names()); ?>">
                        <<?php echo esc_attr($heading_tag); ?>
                            id="<?php echo esc_attr($hero_heading_id); ?>"
                            class="<?php echo esc_attr(matrix_get_hero_with_breadcrumbs_image_split_heading_class_names($text_max_width, $heading_max_width)); ?>"
                            style="color: <?php echo esc_attr($heading_color); ?>;"
                        >
                            <?php echo esc_html($heading); ?>
                        </<?php echo esc_attr($heading_tag); ?>>

                        <?php if (! empty($content)) { ?>
                            <div
                                class="<?php echo esc_attr(matrix_get_hero_with_breadcrumbs_image_split_content_class_names($text_max_width)); ?>"
                                style="color: <?php echo esc_attr($text_color); ?>;"
                            >
                                <?php echo matrix_kses_rich_text($content); ?>
                            </div>
                        <?php } ?>
                    </div>

                    <?php if ($primary_button) { ?>
                        <a
                            href="<?php echo esc_url($primary_button['url']); ?>"
                            target="<?php echo esc_attr($primary_button['target'] !== '' ? $primary_button['target'] : '_self'); ?>"
                            class="<?php echo esc_attr(matrix_get_hero_with_breadcrumbs_primary_button_class_names()); ?>"
                            <?php if ($primary_button['target'] === '_blank') { ?>
                                rel="noopener noreferrer"
                            <?php } ?>
                        >
                            <?php echo esc_html($primary_button['title']); ?>
                        </a>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
    </div>
    <?php } ?>
</section>
