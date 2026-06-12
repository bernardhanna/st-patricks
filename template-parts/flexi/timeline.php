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

$wrapper_classes = ['flex', 'flex-col', 'items-center', 'w-full', 'mx-auto', 'pt-5', 'pb-5', 'max-xl:px-5', 'max-w-[1018px]'];
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

$render_timeline_card = static function (array $item) use ($card_background_color, $allowed_item_tags) {
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
                        class="h-full w-full object-cover"
                    />
                    <?php
                }
                ?>
            </div>
        <?php } ?>

        <?php if ($item['display_date'] !== '') { ?>
            <time
                datetime="<?php echo esc_attr($item['event_date']); ?>"
                class="font-primary text-[12px] font-semibold uppercase leading-[18px] tracking-[0.08em] text-[#08284B] md:hidden"
            >
                <?php echo esc_html($item['display_date']); ?>
            </time>
        <?php } ?>

        <<?php echo esc_attr($item_heading_tag); ?> class="font-primary text-[24px] font-semibold leading-[32px] tracking-[-0.144px] text-[#1E244B]">
            <?php echo esc_html($item['item_heading']); ?>
        </<?php echo esc_attr($item_heading_tag); ?>>

        <?php if (trim(strip_tags($item['item_text'])) !== '') { ?>
            <div class="wp_editor [&_p:last-child]:mb-0 [&_p]:font-primary [&_p]:text-[16px] [&_p]:font-medium [&_p]:leading-[28px] [&_p]:text-[#08284B]">
                <?php echo matrix_kses_rich_text($item['item_text']); ?>
            </div>
        <?php } ?>

        <?php if ($item['has_cta'] && is_array($item['cta_link'])) { ?>
            <?php $cta_target = (string) ($item['cta_link']['target'] ?? '_self'); ?>
            <div class="pt-2">
                <a
                    href="<?php echo esc_url($item['cta_link']['url']); ?>"
                    target="<?php echo esc_attr($cta_target); ?>"
                    class="btn inline-flex h-[36px] w-fit items-center justify-center whitespace-nowrap rounded-[6px] border border-[#024B79] px-3 text-[14px] font-medium leading-[24px] text-[#08284B] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
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
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
    class="relative flex overflow-hidden bg-white"
    aria-labelledby="<?php echo esc_attr($heading_id); ?>"
>
    <div class="<?php echo esc_attr(implode(' ', array_unique($wrapper_classes))); ?>">
        <header class="flex w-full max-w-[690px] flex-col items-center text-center mx-auto">
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

        <ol class="mt-12 flex w-full flex-col lg:mt-16">
            <?php foreach ($items as $index => $item) { ?>
                <?php
                $is_last = $index === count($items) - 1;
                $side = $item['side'];
                $card_column_class = $side === 'right' ? 'md:order-3' : 'md:order-1';
                $date_column_class = $side === 'right' ? 'md:order-1 md:justify-start' : 'md:order-3 md:justify-end';
                ?>

                <li class="w-full <?php echo esc_attr($is_last ? '' : 'pb-8 lg:pb-16'); ?>">
                    <div class="grid w-full grid-cols-1 gap-6 md:grid-cols-[minmax(0,1fr)_100px_minmax(0,1fr)] md:items-stretch md:gap-8">
                        <div class="w-full <?php echo esc_attr($card_column_class); ?>">
                            <?php $render_timeline_card($item); ?>
                        </div>

                        <div class="relative hidden w-full md:order-2 md:flex md:flex-col md:items-center">
                            <span
                                class="relative z-[2] inline-block h-5 w-5 rounded-full border-[3px] border-white shadow-[0px_1px_1px_rgba(0,0,0,0.05)]"
                                style="background-color: <?php echo esc_attr($timeline_accent_color); ?>;"
                                aria-hidden="true"
                            ></span>

                            <?php if (! $is_last) { ?>
                                <span
                                    class="absolute left-1/2 top-5 h-[calc(100%+2rem)] w-px -translate-x-1/2 lg:h-[calc(100%+4rem)]"
                                    style="background-color: <?php echo esc_attr($timeline_accent_color); ?>;"
                                    aria-hidden="true"
                                ></span>
                            <?php } ?>
                        </div>

                        <div class="hidden w-full md:flex md:items-start <?php echo esc_attr($date_column_class); ?>">
                            <?php if ($item['display_date'] !== '') { ?>
                                <time
                                    datetime="<?php echo esc_attr($item['event_date']); ?>"
                                    class="font-primary text-[40px] font-semibold leading-[48px] tracking-[-0.48px] text-[#08284B] lg:text-[48px] lg:leading-[56px] lg:tracking-[-0.576px]"
                                >
                                    <?php echo esc_html($item['display_date']); ?>
                                </time>
                            <?php } ?>
                        </div>
                    </div>
                </li>
            <?php } ?>
        </ol>

        <?php if (is_array($footer_button_link)) { ?>
            <?php $footer_target = (string) ($footer_button_link['target'] ?? '_self'); ?>
            <div class="mt-12 flex w-full justify-center lg:mt-16">
                <a
                    href="<?php echo esc_url($footer_button_link['url']); ?>"
                    target="<?php echo esc_attr($footer_target); ?>"
                    class="btn inline-flex h-[36px] w-fit items-center justify-center whitespace-nowrap rounded-[6px] bg-[#024B79] px-3 text-[14px] font-medium leading-[24px] text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
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
