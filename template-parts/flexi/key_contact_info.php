<?php

$section_id = 'key-contact-info-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());
$columns = matrix_normalize_key_contact_info_columns(get_sub_field('columns'));
$section_background = (string) get_sub_field('section_background');
$closed_panel_background = (string) get_sub_field('closed_panel_background');
$open_panel_background = (string) get_sub_field('open_panel_background');

if ($columns === []) {
    return;
}

if ($section_background === '') {
    $section_background = '#FFFFFF';
}

if ($closed_panel_background === '') {
    $closed_panel_background = '#FBFAF7';
}

if ($open_panel_background === '') {
    $open_panel_background = 'linear-gradient(-79.46deg, #F8F6F3 3.24%, #F5F6ED 90.88%)';
}

$section_background_style = matrix_get_key_contact_info_background_style($section_background, '#FFFFFF');
$closed_panel_background_style = matrix_get_key_contact_info_background_style($closed_panel_background, '#FBFAF7');
$open_panel_background_style = matrix_get_key_contact_info_background_style($open_panel_background, 'linear-gradient(-79.46deg, #F8F6F3 3.24%, #F5F6ED 90.88%)');

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
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
    class="relative flex overflow-hidden"
    style="<?php echo esc_attr($section_background_style); ?>"
>
    <div class="<?php echo esc_attr(implode(' ', array_unique(array_merge(['mx-auto', 'flex', 'w-full', 'max-w-[1018px]', 'flex-col', 'gap-8', 'max-xl:px-5', 'lg:grid', 'lg:grid-cols-3', 'lg:items-start'], $padding_classes)))); ?>">
        <?php foreach ($columns as $column_index => $column) { ?>
            <div
                x-data="{ activeIndex: <?php echo esc_attr((string) $column['initial_open_index']); ?>, toggleItem(index) { this.activeIndex = this.activeIndex === index ? -1 : index; } }"
                class="flex w-full flex-col gap-3"
            >
                <?php foreach ($column['items'] as $item_index => $item) { ?>
                    <?php
                    $button_id = $section_id . '-col-' . $column_index . '-button-' . $item_index;
                    $panel_id = $section_id . '-col-' . $column_index . '-panel-' . $item_index;
                    $has_panel_content = $item['bullet_items'] !== [] || $item['phone'] !== '' || $item['email'] !== '';
                    ?>
                    <div
                        class="overflow-hidden rounded-[4px]"
                        :style="activeIndex === <?php echo esc_attr((string) $item_index); ?> ? '<?php echo esc_js($open_panel_background_style); ?>' : '<?php echo esc_js($closed_panel_background_style); ?>'"
                    >
                        <button
                            type="button"
                            id="<?php echo esc_attr($button_id); ?>"
                            class="btn flex min-h-[58px] w-full items-center justify-between gap-4 px-6 py-4 text-left focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#024B79]"
                            :aria-expanded="activeIndex === <?php echo esc_attr((string) $item_index); ?> ? 'true' : 'false'"
                            <?php if ($has_panel_content) { ?>
                                aria-controls="<?php echo esc_attr($panel_id); ?>"
                            <?php } ?>
                            @click="toggleItem(<?php echo esc_attr((string) $item_index); ?>)"
                        >
                            <span class="font-primary text-[16px] font-semibold leading-[28px] text-[#08284B] lg:text-[18px]">
                                <?php echo esc_html($item['title']); ?>
                            </span>

                            <?php if ($has_panel_content) { ?>
                                <span
                                    class="shrink-0 text-[#08284B] transition-transform duration-200"
                                    :class="activeIndex === <?php echo esc_attr((string) $item_index); ?> ? 'rotate-180' : ''"
                                    aria-hidden="true"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                            <?php } ?>
                        </button>

                        <?php if ($has_panel_content) { ?>
                            <div
                                id="<?php echo esc_attr($panel_id); ?>"
                                x-show="activeIndex === <?php echo esc_attr((string) $item_index); ?>"
                                x-cloak
                                aria-labelledby="<?php echo esc_attr($button_id); ?>"
                                class="px-6 pb-4"
                            >
                                <div class="flex flex-col gap-2 pb-3">
                                    <?php foreach ($item['bullet_items'] as $bullet_label) { ?>
                                        <div class="flex items-center gap-3">
                                            <span class="flex size-6 shrink-0 items-center justify-center font-primary text-[16px] font-medium leading-[28px] text-[#08284B]" aria-hidden="true">-</span>
                                            <span class="font-primary text-[16px] font-medium leading-[28px] text-[#08284B]">
                                                <?php echo esc_html($bullet_label); ?>
                                            </span>
                                        </div>
                                    <?php } ?>

                                    <?php if ($item['phone'] !== '') { ?>
                                        <div class="flex items-center gap-3">
                                            <span class="flex size-6 shrink-0 items-center justify-center text-[#08284B]" aria-hidden="true">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                    <path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24c1.12.37 2.33.57 3.58.57a1 1 0 011 1V20a1 1 0 01-1 1C10.85 21 3 13.15 3 3a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.46.57 3.58a1 1 0 01-.24 1.01l-2.2 2.2z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </span>
                                            <a
                                                href="<?php echo esc_url('tel:' . preg_replace('/\s+/', '', $item['phone'])); ?>"
                                                class="font-primary text-[16px] font-medium leading-[28px] text-[#08284B] transition-colors hover:text-[#024B79] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
                                            >
                                                <?php echo esc_html($item['phone']); ?>
                                            </a>
                                        </div>
                                    <?php } ?>

                                    <?php if ($item['email'] !== '') { ?>
                                        <div class="flex items-center gap-3">
                                            <span class="flex size-6 shrink-0 items-center justify-center text-[#08284B]" aria-hidden="true">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                    <path d="M4 6h16v12H4V6z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                                                    <path d="M4 7l8 6 8-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </span>
                                            <a
                                                href="<?php echo esc_url('mailto:' . sanitize_email($item['email'])); ?>"
                                                class="font-primary text-[16px] font-medium leading-[28px] text-[#08284B] transition-colors hover:text-[#024B79] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
                                            >
                                                <?php echo esc_html($item['email']); ?>
                                            </a>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</section>
