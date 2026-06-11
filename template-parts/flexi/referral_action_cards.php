<?php

$section_id = 'referral-action-cards-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());

$cards = [
    matrix_normalize_referral_action_card([
        'title' => get_sub_field('left_title'),
        'description' => get_sub_field('left_description'),
        'button' => get_sub_field('left_button'),
        'action_icon' => get_sub_field('left_action_icon'),
        'background_color' => get_sub_field('left_background_color'),
    ], [
        'background_color' => '#CEF2EE',
        'action_icon' => 'external',
    ]),
    matrix_normalize_referral_action_card([
        'title' => get_sub_field('right_title'),
        'description' => get_sub_field('right_description'),
        'button' => get_sub_field('right_button'),
        'action_icon' => get_sub_field('right_action_icon'),
        'background_color' => get_sub_field('right_background_color'),
    ], [
        'background_color' => '#E4F4D6',
        'action_icon' => 'download',
    ]),
];

$has_visible_content = false;
foreach ($cards as $card) {
    if ($card['title'] !== '' || trim(strip_tags($card['description'])) !== '' || matrix_referral_action_card_has_button($card)) {
        $has_visible_content = true;
        break;
    }
}

if (! $has_visible_content) {
    return;
}

$wrapper_classes = ['mx-auto', 'grid', 'w-full', 'max-w-[1018px]', 'grid-cols-1', 'gap-4', 'pt-5', 'pb-5', 'max-xl:px-5', 'lg:grid-cols-2'];
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
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
    class="flex overflow-hidden relative bg-white"
>
    <div class="py-12 lg:py-[100px] <?php echo esc_attr(implode(' ', $wrapper_classes)); ?>">
        <?php foreach ($cards as $card) { ?>
            <?php
            $has_button = matrix_referral_action_card_has_button($card);
            $button_target = (string) ($card['button']['target'] ?? '_self');
            ?>

            <article
                class="flex h-full min-h-[240px] flex-col rounded-[8px] px-6 py-6 shadow-sm"
                style="background-color: <?php echo esc_attr($card['background_color']); ?>;"
            >
                <?php if ($card['title'] !== '') { ?>
                    <h3 class="font-primary text-[24px] font-semibold leading-[32px] tracking-[-0.144px] text-[#1E244B]">
                        <?php echo esc_html($card['title']); ?>
                    </h3>
                <?php } ?>

                <?php if (trim(strip_tags($card['description'])) !== '') { ?>
                    <div class="wp_editor mt-4 [&_p:last-child]:mb-0 [&_p]:font-primary [&_p]:text-[16px] [&_p]:font-medium [&_p]:leading-[28px] [&_p]:text-[#1E244B]">
                        <?php echo wp_kses_post($card['description']); ?>
                    </div>
                <?php } ?>

                <?php if ($has_button) { ?>
                    <div class="pt-6 mt-auto">
                        <a
                            href="<?php echo esc_url($card['button']['url']); ?>"
                            target="<?php echo esc_attr($button_target); ?>"
                            class="btn inline-flex h-[40px] w-fit items-center justify-center gap-2 rounded-[6px] bg-[#024B79] px-3 text-[14px] font-medium leading-[24px] text-white transition-colors duration-200 hover:bg-[#08284B] focus:outline focus:outline-2 focus:outline-offset-2 focus:outline-[#024B79]"
                            <?php if ($button_target === '_blank') { ?>
                                rel="noopener noreferrer"
                            <?php } ?>
                        >
                            <?php echo matrix_get_referral_action_card_icon_svg($card['action_icon']); ?>
                            <span><?php echo esc_html($card['button']['title']); ?></span>
                        </a>
                    </div>
                <?php } ?>
            </article>
        <?php } ?>
    </div>
</section>
