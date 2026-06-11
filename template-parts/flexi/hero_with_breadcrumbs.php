<?php

$section_id = 'hero-with-breadcrumbs-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());
$layout_style = matrix_resolve_hero_with_breadcrumbs_layout_style(get_sub_field('layout_style'));
$heading = (string) get_sub_field('heading');
$heading_tag = (string) get_sub_field('heading_tag');
$content = get_sub_field('content');
$primary_button = function_exists('matrix_normalize_content_link')
    ? matrix_normalize_content_link(get_sub_field('primary_button'))
    : null;
$hero_image = get_sub_field('hero_image');
$show_breadcrumbs = (bool) get_sub_field('show_breadcrumbs');
$breadcrumb_source = (string) get_sub_field('breadcrumb_source');
$manual_breadcrumb_rows = get_sub_field('manual_breadcrumbs');
$current_crumb_label = (string) get_sub_field('current_crumb_label');
$background_color = (string) get_sub_field('background_color');
$breadcrumb_background_color = (string) get_sub_field('breadcrumb_background_color');
$heading_color = (string) get_sub_field('heading_color');
$text_color = (string) get_sub_field('text_color');
$accent_color = (string) get_sub_field('accent_color');
$aside_heading = trim((string) get_sub_field('aside_heading'));

if ($heading === '') {
    if ($layout_style === 'title_accent') {
        $heading = 'Press Releases';
    } elseif ($layout_style === 'register_intro') {
        $heading = 'Register for Your Portal | Online Form';
    } else {
        $heading = 'About Us landing page title';
    }
}

if ($background_color === '') {
    if ($layout_style === 'title_accent') {
        $background_color = '#FBF8F3';
    } elseif ($layout_style === 'register_intro') {
        $background_color = '#FFFFFF';
    } else {
        $background_color = '#C6ECF4';
    }
}

if ($aside_heading === '' && $layout_style === 'register_intro') {
    $aside_heading = 'Already registered?';
}

if ($breadcrumb_background_color === '') {
    $breadcrumb_background_color = '#F1F8F9';
}

if ($heading_color === '') {
    $heading_color = $layout_style === 'title_accent' ? '#1E244B' : '#08284B';
}

if ($text_color === '') {
    $text_color = '#08284B';
}

if ($accent_color === '') {
    $accent_color = '#6FC9C0';
}

$allowed_tags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p'];
if (!in_array($heading_tag, $allowed_tags, true)) {
    $heading_tag = 'h1';
}

$padding_classes = [];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size = get_sub_field('screen_size');
        $padding_top = get_sub_field('padding_top');
        $padding_bottom = get_sub_field('padding_bottom');

        if ($screen_size !== '' && $padding_top !== '' && $padding_top !== null) {
            $padding_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
        }

        if ($screen_size !== '' && $padding_bottom !== '' && $padding_bottom !== null) {
            $padding_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
        }
    }
}

$manual_breadcrumb_items = [];
if (is_array($manual_breadcrumb_rows)) {
    foreach ($manual_breadcrumb_rows as $breadcrumb_row) {
        $breadcrumb_link = isset($breadcrumb_row['breadcrumb_link']) && is_array($breadcrumb_row['breadcrumb_link']) ? $breadcrumb_row['breadcrumb_link'] : [];

        if (!empty($breadcrumb_link['url']) && !empty($breadcrumb_link['title'])) {
            $manual_breadcrumb_items[] = [
                'title' => (string) $breadcrumb_link['title'],
                'url' => (string) $breadcrumb_link['url'],
                'target' => (string) ($breadcrumb_link['target'] ?? ''),
            ];
        }
    }
}

$auto_breadcrumb_data = matrix_get_auto_hero_breadcrumb_data(get_the_ID());
$breadcrumb_data = matrix_resolve_hero_breadcrumbs(
    $breadcrumb_source,
    $manual_breadcrumb_items,
    $current_crumb_label,
    $auto_breadcrumb_data
);

$breadcrumb_items = is_array($breadcrumb_data['items']) ? $breadcrumb_data['items'] : [];
$breadcrumb_current_label = (string) ($breadcrumb_data['current_label'] ?? '');
$hero_heading_id = $section_id . '-heading';

$hero_image_alt = '';
$hero_image_title = '';
if ($hero_image) {
    $hero_image_alt = (string) get_post_meta($hero_image, '_wp_attachment_image_alt', true);
    $hero_image_title = (string) get_the_title($hero_image);
}

if ($hero_image_alt === '') {
    $hero_image_alt = $hero_image_title !== '' ? $hero_image_title : $heading;
}

$gradient_vars = matrix_get_hero_with_breadcrumbs_gradient_vars($background_color);
$gradient_solid = $gradient_vars['gradient_solid'];
$gradient_soft = $gradient_vars['gradient_soft'];
$gradient_clear = $gradient_vars['gradient_clear'];
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
    class="flex overflow-hidden relative flex-col"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
    aria-labelledby="<?php echo esc_attr($hero_heading_id); ?>"
>
        <?php if ($show_breadcrumbs && (!empty($breadcrumb_items) || $breadcrumb_current_label !== '')) { ?>
            <?php
            get_template_part('template-parts/partials/hero-breadcrumbs-nav', null, [
                'items' => $breadcrumb_items,
                'current_label' => $breadcrumb_current_label,
                'background_color' => $breadcrumb_background_color,
            ]);
            ?>
        <?php } ?>
    <div class="flex flex-col items-center w-full mx-auto max-w-[1280px]  <?php echo esc_attr(implode(' ', $padding_classes)); ?>">

        <?php if ($layout_style === 'register_intro') { ?>
            <div class="w-full max-w-[1018px] px-5 max-xl:mx-auto lg:px-0">
                <div class="flex flex-col gap-10 lg:flex-row lg:items-start lg:justify-between lg:gap-16">
                    <div class="flex max-w-[636px] flex-col gap-8">
                        <<?php echo esc_attr($heading_tag); ?>
                            id="<?php echo esc_attr($hero_heading_id); ?>"
                            class="font-primary text-[36px] font-bold leading-[40px] tracking-[-0.432px] text-[#08284B] lg:text-[48px] lg:leading-[48px] lg:tracking-[-0.576px]"
                            style="color: <?php echo esc_attr($heading_color); ?>;"
                        >
                            <?php echo esc_html($heading); ?>
                        </<?php echo esc_attr($heading_tag); ?>>

                        <?php if (! empty($content)) { ?>
                            <div
                                class="font-primary text-[16px] font-medium leading-[28px] wp_editor [&_p:last-child]:mb-0"
                                style="color: <?php echo esc_attr($text_color); ?>;"
                            >
                                <?php echo wp_kses_post($content); ?>
                            </div>
                        <?php } ?>
                    </div>

                    <?php if ($aside_heading !== '' || $primary_button) { ?>
                        <aside class="flex flex-col gap-3 items-start w-full lg:w-auto lg:items-end">
                            <?php if ($aside_heading !== '') { ?>
                                <p class="font-primary text-[16px] font-bold leading-[28px] text-[#08284B]">
                                    <?php echo esc_html($aside_heading); ?>
                                </p>
                            <?php } ?>

                            <?php if ($primary_button) { ?>
                                <?php
                                $button_target = $primary_button['target'] !== '' ? $primary_button['target'] : '_self';
                                $opens_external = $button_target === '_blank';
                                ?>
                                <a
                                    href="<?php echo esc_url($primary_button['url']); ?>"
                                    target="<?php echo esc_attr($button_target); ?>"
                                    class="btn inline-flex h-10 w-full items-center justify-center gap-2 rounded-[6px] bg-[#024B79] px-3 text-[14px] font-medium leading-6 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79] lg:w-auto"
                                    <?php if ($opens_external) { ?>
                                        rel="noopener noreferrer"
                                    <?php } ?>
                                >
                                    <?php if ($opens_external && function_exists('matrix_get_hero_external_link_icon_svg')) { ?>
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
                        class="max-w-[1018px] font-primary text-[24px] not-italic font-semibold leading-[28px] tracking-[-0.18px] lg:text-[30px] lg:leading-[36px] lg:tracking-[-0.225px]"
                        style="color: <?php echo esc_attr($heading_color); ?>;"
                    >
                        <?php echo esc_html($heading); ?>
                    </<?php echo esc_attr($heading_tag); ?>>

                    <div class="h-[4px] w-10" style="background-color: <?php echo esc_attr($accent_color); ?>;" aria-hidden="true"></div>

                    <?php if (!empty($content)) { ?>
                        <div
                            class="max-w-[1018px] font-primary text-[16px] not-italic font-medium leading-[28px] text-[#08284B] wp_editor [&_p:last-child]:mb-0"
                            style="color: <?php echo esc_attr($text_color); ?>;"
                        >
                            <?php echo wp_kses_post($content); ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        <?php } else { ?>
            <div class="<?php echo esc_attr(matrix_get_hero_with_breadcrumbs_image_split_grid_class_names()); ?>">
                <div class="<?php echo esc_attr(matrix_get_hero_with_breadcrumbs_image_split_image_column_class_names()); ?>" style="border-color: <?php echo esc_attr($background_color); ?>;">
                    <?php
                    if ($hero_image) {
                        echo wp_get_attachment_image($hero_image, 'full', false, [
                            'alt' => esc_attr($hero_image_alt),
                            'title' => esc_attr($hero_image_title),
                            'class' => 'absolute inset-0 h-full w-full object-cover',
                            'loading' => 'eager',
                        ]);
                    }
                    ?>
                    <?php
                    get_template_part('template-parts/partials/hero-image-split-image-gradients', null, [
                        'gradient_solid' => $gradient_solid,
                        'gradient_soft' => $gradient_soft,
                        'gradient_clear' => $gradient_clear,
                        'background_color' => $background_color,
                    ]);
                    ?>
                </div>

                <div class="<?php echo esc_attr(matrix_get_hero_with_breadcrumbs_image_split_column_class_names()); ?>">
                    <div class="<?php echo esc_attr(matrix_get_hero_with_breadcrumbs_image_split_text_group_class_names()); ?>">
                        <<?php echo esc_attr($heading_tag); ?>
                            id="<?php echo esc_attr($hero_heading_id); ?>"
                            class="<?php echo esc_attr(matrix_get_hero_with_breadcrumbs_image_split_heading_class_names()); ?>"
                            style="color: <?php echo esc_attr($heading_color); ?>;"
                        >
                            <?php echo esc_html($heading); ?>
                        </<?php echo esc_attr($heading_tag); ?>>

                        <?php if (!empty($content)) { ?>
                            <div
                                class="<?php echo esc_attr(matrix_get_hero_with_breadcrumbs_image_split_content_class_names()); ?>"
                                style="color: <?php echo esc_attr($text_color); ?>;"
                            >
                                <?php echo wp_kses_post($content); ?>
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
</section>
