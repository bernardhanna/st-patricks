<?php

$section_id = 'content-cta-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());
$heading = trim((string) get_sub_field('heading'));
$heading_tag = (string) get_sub_field('heading_tag');
$body = get_sub_field('body');
$button_link = matrix_normalize_content_cta_link(get_sub_field('button_link'));
$background_type = (string) get_sub_field('background_type');
$background_color = (string) get_sub_field('background_color');
$background_gradient = (string) get_sub_field('background_gradient');
$color_scheme = matrix_resolve_content_cta_color_scheme(get_sub_field('color_scheme'));
$theme_classes = matrix_get_content_cta_theme_classes($color_scheme);
$background_style = matrix_get_content_cta_background_style($background_type, $background_color, $background_gradient);

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
$wrapper_classes = 'mx-auto flex w-full max-w-[1018px] flex-col gap-8 py-12 max-xl:px-5 lg:gap-8 lg:py-[100px]';
$is_inverse = $color_scheme === 'inverse';
$body_classes = $is_inverse
    ? '[&_p]:text-white/90 [&_a]:text-white [&_a]:underline hover:[&_a]:no-underline'
    : '[&_p]:text-[#08284B] [&_a]:text-[#024B79] [&_a]:underline hover:[&_a]:no-underline';
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
    data-content-cta-scheme="<?php echo esc_attr($color_scheme); ?>"
    class="relative flex overflow-hidden"
    style="<?php echo esc_attr($background_style); ?>"
    <?php if ($heading !== '') { ?>
        aria-labelledby="<?php echo esc_attr($heading_id); ?>"
    <?php } ?>
>
    <div class="<?php echo esc_attr($wrapper_classes); ?>">
        <header class="flex w-full flex-col gap-8">
            <<?php echo esc_attr($heading_tag); ?>
                id="<?php echo esc_attr($heading_id); ?>"
                class="font-primary text-[24px] font-semibold leading-[28px] tracking-[-0.18px] lg:text-[30px] lg:leading-[36px] lg:tracking-[-0.225px] <?php echo esc_attr($theme_classes['heading']); ?>"
            >
                <?php echo esc_html($heading); ?>
            </<?php echo esc_attr($heading_tag); ?>>

            <div class="<?php echo esc_attr($theme_classes['accent']); ?> h-[4px] w-10" aria-hidden="true"></div>
        </header>

        <div class="flex w-full flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
            <?php if (is_string($body) && trim(strip_tags($body)) !== '') { ?>
                <div class="wp_editor max-w-[720px] [&_p:last-child]:mb-0 [&_p]:font-primary [&_p]:text-[18px] [&_p]:font-normal [&_p]:leading-[28px] <?php echo $body_classes; ?>">
                    <?php echo matrix_kses_rich_text($body); ?>
                </div>
            <?php } ?>

            <?php if (is_array($button_link)) { ?>
                <?php $button_target = (string) ($button_link['target'] ?? '_self'); ?>
                <div class="flex shrink-0 lg:justify-end">
                    <a
                        href="<?php echo esc_url($button_link['url']); ?>"
                        target="<?php echo esc_attr($button_target); ?>"
                        class="btn inline-flex h-[36px] w-fit items-center justify-center whitespace-nowrap rounded-[6px] px-3 text-[14px] font-medium leading-[24px] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 <?php echo esc_attr($theme_classes['button']); ?>"
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
