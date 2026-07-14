<?php

$section_id = 'timeline-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());
$heading = trim((string) get_sub_field('heading'));
$heading_tag = (string) get_sub_field('heading_tag');
$intro = get_sub_field('intro');
$items = matrix_normalize_timeline_items(get_sub_field('timeline_items'));
$footer_button_link = matrix_normalize_timeline_link(get_sub_field('footer_button_link'));
$card_background_color = (string) get_sub_field('card_background_color');
$timeline_accent_color = (string) get_sub_field('timeline_accent_color');

if ($heading === '') {
    $heading = 'Our History, timeline title - medium length';
}

if (! in_array($heading_tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p'], true)) {
    $heading_tag = 'h2';
}

if ($card_background_color === '') {
    $card_background_color = '#E4F4D6';
}

if ($timeline_accent_color === '') {
    $timeline_accent_color = '#6FC9C0';
}

if ($items === []) {
    return;
}

$heading_id = $section_id . '-heading';
$allowed_item_tags = ['h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p'];

$wrapper_classes = ['flex', 'flex-col', 'items-center', 'w-full', 'mx-auto', 'py-12', 'lg:py-[100px]', 'max-xl:px-5', 'max-w-[63.625rem]'];

$render_timeline_card = static function (array $item, bool $include_mobile_date = false) use ($card_background_color, $allowed_item_tags) {
    $item_heading_tag = $item['item_heading_tag'];

    if (! in_array($item_heading_tag, $allowed_item_tags, true)) {
        $item_heading_tag = 'h3';
    }

    $image = is_array($item['image']) ? $item['image'] : null;
    $image_id = is_array($image) ? (int) ($image['ID'] ?? 0) : 0;
    $image_url = is_array($image) ? trim((string) ($image['url'] ?? '')) : '';
    $image_alt = is_array($image) ? trim((string) ($image['alt'] ?? '')) : '';

    if ($image_id > 0 && $image_alt === '') {
        $image_alt = trim((string) get_post_meta($image_id, '_wp_attachment_image_alt', true));
    }

    if ($image_alt === '') {
        $image_alt = $item['item_heading'];
    }
    ?>
    <article
        class="flex h-full flex-col gap-[10px] rounded-[8px] p-6 shadow-[0px_1px_1px_rgba(0,0,0,0.05)] lg:p-8"
        style="background-color: <?php echo esc_attr($card_background_color); ?>;"
    >
        <?php if ($include_mobile_date && $item['display_date'] !== '') { ?>
            <time
                datetime="<?php echo esc_attr($item['event_date']); ?>"
                class="font-primary text-[3rem] font-semibold leading-[3.5rem] tracking-[-0.036rem] text-[#08284B] lg:hidden"
            >
                <?php echo esc_html($item['display_date']); ?>
            </time>
        <?php } ?>

        <?php if ($image_id > 0 || $image_url !== '') { ?>
            <div class="h-[161px] w-full overflow-hidden rounded-[6px] bg-[#F8F6F3]">
                <?php
                if ($image_id > 0) {
                    echo wp_get_attachment_image($image_id, 'medium_large', false, [
                        'class' => 'h-full w-full object-cover',
                        'alt' => $image_alt,
                    ]);
                } else {
                    ?>
                    <img
                        src="<?php echo esc_url($image_url); ?>"
                        alt="<?php echo esc_attr($image_alt); ?>"
                        class="object-cover w-full h-full"
                    />
                    <?php
                }
                ?>
            </div>
        <?php } ?>

        <<?php echo esc_attr($item_heading_tag); ?> class="font-primary text-[1.5rem] font-semibold leading-[1.75rem] tracking-[-0.009rem] text-[#1E244B] lg:text-[24px] lg:leading-[32px] lg:tracking-[-0.144px]">
            <?php echo esc_html($item['item_heading']); ?>
        </<?php echo esc_attr($item_heading_tag); ?>>

        <?php if (trim(strip_tags($item['item_text'])) !== '') { ?>
            <div class="wp_editor [&_p:last-child]:mb-0 [&_p]:font-primary [&_p]:text-[1rem] [&_p]:font-medium [&_p]:leading-[1.75rem] [&_p]:text-[#08284B]">
                <?php echo matrix_kses_rich_text($item['item_text']); ?>
            </div>
        <?php } ?>

        <?php if ($item['has_cta'] && is_array($item['cta_link'])) { ?>
            <?php $cta_target = (string) ($item['cta_link']['target'] ?? '_self'); ?>
            <div class="pt-2">
                <a
                    href="<?php echo esc_url($item['cta_link']['url']); ?>"
                    target="<?php echo esc_attr($cta_target); ?>"
                    class="inline-flex h-[36px] w-fit items-center justify-center whitespace-nowrap rounded-[6px] border border-[#024B79] px-3 text-[14px] font-medium leading-[24px] text-[#08284B] transition-colors duration-200 hover:bg-[#024B79] hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
                    <?php if ($cta_target === '_blank') { ?>
                        rel="noopener noreferrer"
                    <?php } ?>
                >
                    <?php echo esc_html($item['cta_link']['title']); ?>
                </a>
            </div>
        <?php } ?>
    </article>
    <?php
};

$render_timeline_date = static function (array $item, string $classes = '') {
    if ($item['display_date'] === '') {
        return;
    }
    ?>
    <time
        datetime="<?php echo esc_attr($item['event_date']); ?>"
        class="font-primary text-[3rem] font-semibold leading-[3.5rem] tracking-[-0.036rem] text-[#08284B] <?php echo esc_attr($classes); ?>"
    >
        <?php echo esc_html($item['display_date']); ?>
    </time>
    <?php
};
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
    class="flex overflow-hidden relative bg-white"
    aria-labelledby="<?php echo esc_attr($heading_id); ?>"
>
    <div class="<?php echo esc_attr(implode(' ', array_unique($wrapper_classes))); ?>">
        <header class="mx-auto flex w-full max-w-[690px] flex-col items-center text-center">
            <<?php echo esc_attr($heading_tag); ?>
                id="<?php echo esc_attr($heading_id); ?>"
                class="font-primary text-[24px] font-semibold leading-[28px] tracking-[-0.18px] text-[#1E244B] lg:text-[30px] lg:leading-[36px] lg:tracking-[-0.225px]"
            >
                <?php echo esc_html($heading); ?>
            </<?php echo esc_attr($heading_tag); ?>>

            <div class="mt-6 h-[4px] w-10 bg-[#6FC9C0]"></div>

            <?php if (is_string($intro) && trim(strip_tags($intro)) !== '') { ?>
                <div class="wp_editor mt-8 [&_p:last-child]:mb-0 [&_p]:font-primary [&_p]:text-[16px] [&_p]:font-medium [&_p]:leading-[28px] [&_p]:text-[#08284B]">
                    <?php echo matrix_kses_rich_text($intro); ?>
                </div>
            <?php } ?>
        </header>

        <ol class="flex flex-col mt-12 w-full lg:mt-16">
            <?php foreach ($items as $index => $item) { ?>
                <?php
                $is_first = $index === 0;
                $is_last = $index === count($items) - 1;
                $side = $item['side'];
                $has_footer_cta = is_array($footer_button_link);
                $desktop_date_classes = $side === 'right'
                    ? 'lg:justify-end lg:text-right'
                    : 'lg:justify-start lg:text-left';
                ?>

                <li class="w-full <?php echo esc_attr($is_last && $has_footer_cta ? 'lg:pb-16' : ($is_last ? '' : 'lg:pb-16')); ?>">
                    <?php /* Mobile / tablet: left spine + card with date inside (Figma 3279:15332) */ ?>
                    <div class="grid w-full grid-cols-[31px_minmax(0,1fr)] lg:hidden">
                        <div class="flex relative flex-col items-center self-stretch">
                            <span
                                class="absolute top-0 left-1/2 h-[50px] w-[2px] -translate-x-1/2"
                                style="background-color: <?php echo esc_attr($timeline_accent_color); ?>;"
                                aria-hidden="true"
                            ></span>

                            <span
                                class="relative z-[2] mt-[50px] inline-block h-5 w-5 shrink-0 rounded-full border-[3px] border-white shadow-[0px_1px_1px_rgba(0,0,0,0.05)]"
                                style="background-color: <?php echo esc_attr($timeline_accent_color); ?>;"
                                aria-hidden="true"
                            ></span>

                            <?php if (! $is_last) { ?>
                                <span
                                    class="absolute top-[4.375rem] bottom-0 left-1/2 w-[2px] -translate-x-1/2"
                                    style="background-color: <?php echo esc_attr($timeline_accent_color); ?>;"
                                    aria-hidden="true"
                                ></span>
                            <?php } elseif ($has_footer_cta) { ?>
                                <span
                                    class="absolute top-[4.375rem] -bottom-16 left-1/2 w-[2px] -translate-x-1/2"
                                    style="background-color: <?php echo esc_attr($timeline_accent_color); ?>;"
                                    aria-hidden="true"
                                ></span>
                            <?php } ?>
                        </div>

                        <div class="pt-8 pl-8 min-w-0">
                            <?php $render_timeline_card($item, true); ?>
                        </div>
                    </div>

                    <?php /* Desktop: alternating card/date around centre spine (Figma 3279:19849) */ ?>
                    <div class="hidden w-full lg:grid lg:grid-cols-[minmax(0,1fr)_100px_minmax(0,1fr)] lg:items-start">
                        <?php if ($side === 'left') { ?>
                            <div class="w-full lg:col-start-1">
                                <?php $render_timeline_card($item); ?>
                            </div>
                        <?php } else { ?>
                            <div class="flex w-full lg:col-start-1 lg:items-start <?php echo esc_attr($desktop_date_classes); ?>">
                                <?php $render_timeline_date($item); ?>
                            </div>
                        <?php } ?>

                        <div class="flex relative flex-col items-center self-stretch lg:col-start-2">
                            <?php if ($is_first) { ?>
                                <span
                                    class="absolute top-0 left-1/2 h-5 w-[2px] -translate-x-1/2"
                                    style="background-color: <?php echo esc_attr($timeline_accent_color); ?>;"
                                    aria-hidden="true"
                                ></span>
                            <?php } ?>

                            <span
                                class="relative z-[2] inline-block h-5 w-5 shrink-0 rounded-full border-[3px] border-white shadow-[0px_1px_1px_rgba(0,0,0,0.05)] <?php echo esc_attr($is_first ? 'mt-5' : ''); ?>"
                                style="background-color: <?php echo esc_attr($timeline_accent_color); ?>;"
                                aria-hidden="true"
                            ></span>

                            <?php if (! $is_last) { ?>
                                <span
                                    class="absolute left-1/2 w-[2px] -translate-x-1/2 <?php echo esc_attr($is_first ? 'top-10' : 'top-5'); ?> h-[calc(100%+4rem)]"
                                    style="background-color: <?php echo esc_attr($timeline_accent_color); ?>;"
                                    aria-hidden="true"
                                ></span>
                            <?php } elseif ($has_footer_cta) { ?>
                                <span
                                    class="absolute -bottom-16 left-1/2 w-[2px] -translate-x-1/2 <?php echo esc_attr($is_first ? 'top-10' : 'top-5'); ?>"
                                    style="background-color: <?php echo esc_attr($timeline_accent_color); ?>;"
                                    aria-hidden="true"
                                ></span>
                            <?php } ?>
                        </div>

                        <?php if ($side === 'left') { ?>
                            <div class="flex w-full lg:col-start-3 lg:items-start <?php echo esc_attr($desktop_date_classes); ?>">
                                <?php $render_timeline_date($item); ?>
                            </div>
                        <?php } else { ?>
                            <div class="w-full lg:col-start-3">
                                <?php $render_timeline_card($item); ?>
                            </div>
                        <?php } ?>
                    </div>
                </li>
            <?php } ?>
        </ol>

        <?php if (is_array($footer_button_link)) { ?>
            <?php $footer_target = (string) ($footer_button_link['target'] ?? '_self'); ?>
            <div class="flex justify-center mt-12 w-full">
                <a
                    href="<?php echo esc_url($footer_button_link['url']); ?>"
                    target="<?php echo esc_attr($footer_target); ?>"
                    class="btn inline-flex h-[36px] w-fit items-center justify-center whitespace-nowrap rounded-[6px] bg-[#024B79] px-3 text-[14px] font-medium leading-[24px] text-white transition-colors duration-200 hover:bg-[#C3DBAE] hover:text-[#1E244B] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
                    <?php if ($footer_target === '_blank') { ?>
                        rel="noopener noreferrer"
                    <?php } ?>
                >
                    <?php echo esc_html($footer_button_link['title']); ?>
                </a>
            </div>
        <?php } ?>
    </div>
</section>
