<?php

$section_id = 'testimonials-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());
$heading_tag = (string) get_sub_field('heading_tag');
$heading_text = (string) get_sub_field('heading_text');
$layout_style = (string) get_sub_field('layout_style');
$source_mode = (string) get_sub_field('source_mode');
$manual_items_raw = get_sub_field('manual_items');
$selected_testimonials = get_sub_field('selected_testimonials');
$footer_action_mode = (string) get_sub_field('footer_action_mode');
$load_more_button_text = (string) get_sub_field('load_more_button_text');
$footer_button_link = get_sub_field('footer_button_link');
$background_image = get_sub_field('background_image');
$background_color = (string) get_sub_field('background_color');
$heading_color = (string) get_sub_field('heading_color');
$accent_color = (string) get_sub_field('accent_color');
$quote_color = (string) get_sub_field('quote_color');
$author_color = (string) get_sub_field('author_color');
$card_lavender_color = (string) get_sub_field('card_lavender_color');
$card_mauve_color = (string) get_sub_field('card_mauve_color');
$card_inner_color = (string) get_sub_field('card_inner_color');
$button_border_color = (string) get_sub_field('button_border_color');
$button_text_color = (string) get_sub_field('button_text_color');

if ($heading_text === '') {
    $heading_text = 'Testimonials';
}

if ($layout_style === '') {
    $layout_style = 'grid_standard';
}

if ($background_color === '') {
    $background_color = '#F6EDE0';
}

if ($heading_color === '') {
    $heading_color = '#1E244B';
}

if ($accent_color === '') {
    $accent_color = '#6FC9C0';
}

if ($quote_color === '') {
    $quote_color = '#08284B';
}

if ($author_color === '') {
    $author_color = '#08284B';
}

if ($card_lavender_color === '') {
    $card_lavender_color = '#B4A8CE';
}

if ($card_mauve_color === '') {
    $card_mauve_color = '#E4B8D6';
}

if ($card_inner_color === '') {
    $card_inner_color = '#FFFFFF';
}

if ($button_border_color === '') {
    $button_border_color = '#024B79';
}

if ($button_text_color === '') {
    $button_text_color = '#08284B';
}

if ($load_more_button_text === '') {
    $load_more_button_text = 'Load more testimonials';
}

$allowed_tags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p'];
if (!in_array($heading_tag, $allowed_tags, true)) {
    $heading_tag = 'h2';
}

$padding_classes = ['pt-5', 'pb-5'];
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

$manual_items = [];
if (is_array($manual_items_raw)) {
    foreach ($manual_items_raw as $manual_item) {
        $manual_items[] = [
            'quote' => is_string($manual_item['quote'] ?? '') ? $manual_item['quote'] : '',
            'author_name' => (string) ($manual_item['author_name'] ?? ''),
            'author_title' => (string) ($manual_item['author_title'] ?? ''),
            'card_tone' => (string) ($manual_item['card_tone'] ?? ''),
        ];
    }
}

$all_testimonials = [];
if ($source_mode === 'all') {
    $all_testimonials = get_posts([
        'post_type' => 'testimonials',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
    ]);
}

$testimonial_items = matrix_resolve_testimonial_items(
    $source_mode,
    $manual_items,
    is_array($selected_testimonials) ? $selected_testimonials : [],
    $all_testimonials
);

$testimonial_count = count($testimonial_items);
$editorial_rows = $layout_style === 'editorial_featured'
    ? matrix_group_editorial_featured_testimonials($testimonial_items)
    : [];
$should_enable_load_more = $footer_action_mode === 'load_more' && $testimonial_count > 4;
$visible_initial_count = ($footer_action_mode === 'none') ? $testimonial_count : min(4, $testimonial_count);
$button_class = 'testimonials-footer-button-' . $section_id;
$load_more_class = 'testimonials-load-more-button-' . $section_id;
$background_image_alt = $background_image ? (string) get_post_meta($background_image, '_wp_attachment_image_alt', true) : '';

$tone_backgrounds = [
    'lavender' => $card_lavender_color,
    'mauve' => $card_mauve_color,
];
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
    class="flex overflow-hidden relative"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
>
    <div
        x-data="{ visibleCount: <?php echo esc_attr((string) $visible_initial_count); ?>, totalCount: <?php echo esc_attr((string) $testimonial_count); ?> }"
        class="flex flex-col items-center w-full mx-auto max-w-[1018px] py-12 lg:py-[100px] max-xl:px-5 <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
    >
        <?php if ($background_image) { ?>
            <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
                <?php
                echo wp_get_attachment_image($background_image, 'full', false, [
                    'alt' => esc_attr($background_image_alt),
                    'class' => 'h-full w-full object-cover opacity-50',
                    'loading' => 'lazy',
                ]);
                ?>
            </div>
        <?php } ?>

        <div class="grid relative gap-16 w-full">
            <div class="grid w-full max-w-[312px]">
                <<?php echo esc_attr($heading_tag); ?>
                    class="font-primary text-[30px] not-italic font-semibold leading-[36px] tracking-[-0.225px]"
                    style="color: <?php echo esc_attr($heading_color); ?>;"
                >
                    <?php echo esc_html($heading_text); ?>
                </<?php echo esc_attr($heading_tag); ?>>

                <div class="mt-7 w-10 h-[4px]" style="background-color: <?php echo esc_attr($accent_color); ?>;" aria-hidden="true"></div>
            </div>

            <?php if (!empty($testimonial_items)) { ?>
                <?php if ($layout_style === 'editorial_featured') { ?>
                    <div class="grid gap-8 w-full">
                        <?php foreach ($editorial_rows as $row) { ?>
                            <?php if (!empty($row['standard_items'])) {
                                $first_standard_index = (int) $row['standard_items'][0]['index'];
                                ?>
                                <div
                                    x-show="<?php echo esc_attr((string) $first_standard_index); ?> < visibleCount"
                                    class="grid gap-8 md:grid-cols-2"
                                >
                                    <?php foreach ($row['standard_items'] as $grouped_item) {
                                        $index = (int) $grouped_item['index'];
                                        $item = $grouped_item['item'];
                                        $card_tone = $item['card_tone'] !== '' ? $item['card_tone'] : (($index % 2 === 0) ? 'lavender' : 'mauve');
                                        $card_background_color = $tone_backgrounds[$card_tone] ?? $card_lavender_color;
                                        $quote = $item['quote'];
                                        $author_name = $item['author_name'];
                                        $author_title = $item['author_title'];
                                        ?>
                                        <article
                                            x-show="<?php echo esc_attr((string) $index); ?> < visibleCount"
                                            x-cloak
                                            class="relative flex min-h-[210px] items-stretch rounded-lg px-4 pb-4 pt-16"
                                            style="background-color: <?php echo esc_attr($card_background_color); ?>;"
                                        >
                                            <div class="absolute right-5 top-[22px]" aria-hidden="true">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M3 21C6 21 10 20 10 13V5.00003C10 3.75003 9.244 2.98303 8 3.00003H4C2.75 3.00003 2 3.75003 2 4.97203V11C2 12.25 2.75 13 4 13C5 13 5 13 5 14V15C5 16 4 17 3 17C2 17 2 17.008 2 18.031V20C2 21 2 21 3 21Z" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                                    <path d="M15 21C18 21 22 20 22 13V5.00003C22 3.75003 21.243 2.98303 20 3.00003H16C14.75 3.00003 14 3.75003 14 4.97203V11C14 12.25 14.75 13 16 13H16.75C16.75 15.25 17 17 14 17V20C14 21 14 21 15 21Z" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                                </svg>
                                            </div>

                                            <div class="flex flex-1 flex-col justify-between gap-[10px] rounded-[6px] px-4 py-2" style="background-color: <?php echo esc_attr($card_inner_color); ?>;">
                                                <div class="wp_editor font-primary text-[16px] italic leading-[24px]" style="color: <?php echo esc_attr($quote_color); ?>;">
                                                    <?php echo wp_kses_post($quote); ?>
                                                </div>

                                                <div class="font-primary text-[#08284B]">
                                                    <?php if ($author_name !== '') { ?>
                                                        <div class="text-[12px] not-italic font-semibold leading-[16px]" style="color: <?php echo esc_attr($author_color); ?>;">
                                                            <?php echo esc_html($author_name); ?>
                                                            <?php if ($author_title !== '') { ?>,<?php } ?>
                                                        </div>
                                                    <?php } ?>
                                                    <?php if ($author_title !== '') { ?>
                                                        <div class="text-[12px] not-italic font-semibold leading-[16px]" style="color: <?php echo esc_attr($author_color); ?>;">
                                                            <?php echo esc_html($author_title); ?>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </article>
                                    <?php } ?>
                                </div>
                            <?php } ?>

                            <?php if (!empty($row['featured_item'])) {
                                $featured_index = (int) $row['featured_item']['index'];
                                $item = $row['featured_item']['item'];
                                $card_tone = $item['card_tone'] !== '' ? $item['card_tone'] : (($featured_index % 2 === 0) ? 'lavender' : 'mauve');
                                $card_background_color = $tone_backgrounds[$card_tone] ?? $card_lavender_color;
                                $quote = $item['quote'];
                                $author_name = $item['author_name'];
                                $author_title = $item['author_title'];
                                ?>
                                <div
                                    x-show="<?php echo esc_attr((string) $featured_index); ?> < visibleCount"
                                    class="flex w-full"
                                >
                                    <article
                                        x-show="<?php echo esc_attr((string) $featured_index); ?> < visibleCount"
                                        x-cloak
                                        class="relative flex min-h-[210px] w-full items-stretch rounded-lg px-4 pb-4 pt-16"
                                        style="background-color: <?php echo esc_attr($card_background_color); ?>;"
                                    >
                                        <div class="absolute right-5 top-[20px]" aria-hidden="true">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M3 21C6 21 10 20 10 13V5.00003C10 3.75003 9.244 2.98303 8 3.00003H4C2.75 3.00003 2 3.75003 2 4.97203V11C2 12.25 2.75 13 4 13C5 13 5 13 5 14V15C5 16 4 17 3 17C2 17 2 17.008 2 18.031V20C2 21 2 21 3 21Z" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                                <path d="M15 21C18 21 22 20 22 13V5.00003C22 3.75003 21.243 2.98303 20 3.00003H16C14.75 3.00003 14 3.75003 14 4.97203V11C14 12.25 14.75 13 16 13H16.75C16.75 15.25 17 17 14 17V20C14 21 14 21 15 21Z" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg>
                                        </div>

                                        <div class="flex flex-1 flex-col justify-between gap-[10px] rounded-[6px] px-4 py-2" style="background-color: <?php echo esc_attr($card_inner_color); ?>;">
                                            <div class="wp_editor font-primary text-[16px] italic leading-[24px]" style="color: <?php echo esc_attr($quote_color); ?>;">
                                                <?php echo wp_kses_post($quote); ?>
                                            </div>

                                            <div class="font-primary text-[#08284B]">
                                                <?php if ($author_name !== '') { ?>
                                                    <div class="text-[12px] not-italic font-semibold leading-[16px]" style="color: <?php echo esc_attr($author_color); ?>;">
                                                        <?php echo esc_html($author_name); ?>
                                                        <?php if ($author_title !== '') { ?>,<?php } ?>
                                                    </div>
                                                <?php } ?>
                                                <?php if ($author_title !== '') { ?>
                                                    <div class="text-[12px] not-italic font-semibold leading-[16px]" style="color: <?php echo esc_attr($author_color); ?>;">
                                                        <?php echo esc_html($author_title); ?>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </article>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <div class="grid gap-8 md:grid-cols-2">
                        <?php foreach ($testimonial_items as $index => $item) {
                            $card_tone = $item['card_tone'] !== '' ? $item['card_tone'] : (($index % 2 === 0) ? 'lavender' : 'mauve');
                            $card_background_color = $tone_backgrounds[$card_tone] ?? $card_lavender_color;
                            $quote = $item['quote'];
                            $author_name = $item['author_name'];
                            $author_title = $item['author_title'];
                            ?>
                            <article
                                x-show="<?php echo esc_attr((string) $index); ?> < visibleCount"
                                x-cloak
                                class="relative flex min-h-[210px] items-stretch rounded-lg px-4 pb-4 pt-16"
                                style="background-color: <?php echo esc_attr($card_background_color); ?>;"
                            >
                                <div class="absolute right-5 top-[22px]" aria-hidden="true">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M3 21C6 21 10 20 10 13V5.00003C10 3.75003 9.244 2.98303 8 3.00003H4C2.75 3.00003 2 3.75003 2 4.97203V11C2 12.25 2.75 13 4 13C5 13 5 13 5 14V15C5 16 4 17 3 17C2 17 2 17.008 2 18.031V20C2 21 2 21 3 21Z" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                        <path d="M15 21C18 21 22 20 22 13V5.00003C22 3.75003 21.243 2.98303 20 3.00003H16C14.75 3.00003 14 3.75003 14 4.97203V11C14 12.25 14.75 13 16 13H16.75C16.75 15.25 17 17 14 17V20C14 21 14 21 15 21Z" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </div>

                                <div class="flex flex-1 flex-col justify-between gap-[10px] rounded-[6px] px-4 py-2" style="background-color: <?php echo esc_attr($card_inner_color); ?>;">
                                    <div class="wp_editor font-primary text-[16px] italic leading-[24px]" style="color: <?php echo esc_attr($quote_color); ?>;">
                                        <?php echo wp_kses_post($quote); ?>
                                    </div>

                                    <div class="font-primary text-[#08284B]">
                                        <?php if ($author_name !== '') { ?>
                                            <div class="text-[12px] not-italic font-semibold leading-[16px]" style="color: <?php echo esc_attr($author_color); ?>;">
                                                <?php echo esc_html($author_name); ?>
                                                <?php if ($author_title !== '') { ?>,<?php } ?>
                                            </div>
                                        <?php } ?>
                                        <?php if ($author_title !== '') { ?>
                                            <div class="text-[12px] not-italic font-semibold leading-[16px]" style="color: <?php echo esc_attr($author_color); ?>;">
                                                <?php echo esc_html($author_title); ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </article>
                        <?php } ?>
                    </div>
                <?php } ?>
            <?php } ?>

            <?php if ($should_enable_load_more || ($footer_action_mode === 'link_button' && is_array($footer_button_link) && !empty($footer_button_link['url']))) { ?>
                <div class="flex w-full">
                    <?php if ($should_enable_load_more) { ?>
                        <button
                            x-show="visibleCount < totalCount"
                            x-on:click="visibleCount = Math.min(visibleCount + 4, totalCount)"
                            class="btn <?php echo esc_attr($load_more_class); ?> w-fit whitespace-nowrap inline-flex items-center justify-center rounded-md border px-4 py-2 text-[14px] not-italic font-medium leading-[24px] transition-colors duration-200"
                            style="border-color: <?php echo esc_attr($button_border_color); ?>; color: <?php echo esc_attr($button_text_color); ?>;"
                            type="button"
                        >
                            <?php echo esc_html($load_more_button_text); ?>
                        </button>
                        <style>
                            .<?php echo esc_attr($load_more_class); ?>:hover,
                            .<?php echo esc_attr($load_more_class); ?>:focus-visible {
                                background-color: <?php echo esc_attr($button_border_color); ?>;
                                color: #ffffff !important;
                            }
                        </style>
                    <?php } else {
                        $button_title = (string) ($footer_button_link['title'] ?? 'Learn more');
                        $button_target = (string) ($footer_button_link['target'] ?? '_self');
                        ?>
                        <a
                            href="<?php echo esc_url($footer_button_link['url']); ?>"
                            target="<?php echo esc_attr($button_target); ?>"
                            aria-label="<?php echo esc_attr($button_title); ?>"
                            class="btn <?php echo esc_attr($button_class); ?> inline-flex w-fit whitespace-nowrap items-center justify-center rounded-md border px-4 py-2 text-[14px] not-italic font-medium leading-[24px] transition-colors duration-200"
                            style="border-color: <?php echo esc_attr($button_border_color); ?>; color: <?php echo esc_attr($button_text_color); ?>;"
                        >
                            <?php echo esc_html($button_title); ?>
                        </a>
                        <style>
                            .<?php echo esc_attr($button_class); ?>:hover,
                            .<?php echo esc_attr($button_class); ?>:focus-visible {
                                background-color: <?php echo esc_attr($button_border_color); ?>;
                                color: #ffffff !important;
                            }
                        </style>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>
</section>
