<?php

$section_id = 'multidisciplinary-team-grid-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());
$heading = trim((string) get_sub_field('heading'));
$heading_tag = (string) get_sub_field('heading_tag');
$intro = get_sub_field('intro');
$background_color = (string) get_sub_field('background_color');
$cards = matrix_normalize_multidisciplinary_team_cards(get_sub_field('cards'));

if ($heading === '') {
    $heading = 'Our multidisciplinary team';
}

if ($background_color === '') {
    $background_color = '#FFFFFF';
}

$allowed_tags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p'];
if (! in_array($heading_tag, $allowed_tags, true)) {
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

if ($cards === []) {
    return;
}

$tone_backgrounds = [
    'teal' => '#D9F0F4',
    'green' => '#E2EBCF',
    'yellow' => '#F6E9CF',
    'lavender' => '#E5E1F3',
    'pink' => '#F3DDE8',
    'coral' => '#F7DDD2',
];
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
    class="relative flex overflow-hidden"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
>
    <div class="<?php echo esc_attr(implode(' ', array_unique(array_merge(['mx-auto', 'flex', 'w-full', 'max-w-[1018px]', 'flex-col', 'max-xl:px-5'], $padding_classes)))); ?>">
        <<?php echo esc_attr($heading_tag); ?>
            class="font-primary text-[24px] font-semibold leading-[28px] tracking-[-0.18px] text-[#08284B] lg:text-[30px] lg:leading-[36px] lg:tracking-[-0.225px]"
        >
            <?php echo esc_html($heading); ?>
        </<?php echo esc_attr($heading_tag); ?>>

        <div class="mt-4 h-[4px] w-10 bg-[#6FC9C0]"></div>

        <?php if (is_string($intro) && trim(strip_tags($intro)) !== '') { ?>
            <div class="wp_editor mt-6 max-w-[760px] [&_p:last-child]:mb-0 [&_p]:font-primary [&_p]:text-[16px] [&_p]:leading-[28px] [&_p]:text-[#08284B]">
                <?php echo wp_kses_post($intro); ?>
            </div>
        <?php } ?>

        <div class="mt-10 grid grid-cols-1 gap-4 lg:mt-12 lg:grid-cols-2">
            <?php foreach ($cards as $card) { ?>
                <?php
                $card_background = $tone_backgrounds[$card['card_tone']] ?? $tone_backgrounds['teal'];
                $card_classes = 'group flex min-h-[240px] flex-col justify-between border border-transparent bg-[var(--card-bg)] p-6 shadow-[0px_1px_1px_rgba(0,0,0,0.05)] lg:p-8';
                $linked_card_classes = $card_classes . ' transition-colors duration-200 hover:border-[#E2E8F0] hover:bg-white focus-visible:border-[#E2E8F0] focus-visible:bg-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]';
                ?>

                <?php if ($card['is_linked']) { ?>
                    <a
                        href="<?php echo esc_url($card['link']['url']); ?>"
                        target="<?php echo esc_attr($card['link']['target']); ?>"
                        class="<?php echo esc_attr($linked_card_classes); ?>"
                        style="--card-bg: <?php echo esc_attr($card_background); ?>;"
                        <?php if ($card['link']['target'] === '_blank') { ?>
                            rel="noopener noreferrer"
                        <?php } ?>
                    >
                        <div>
                            <h3 class="font-primary text-[24px] font-semibold leading-[32px] tracking-[-0.18px] text-[#08284B]">
                                <?php echo esc_html($card['title']); ?>
                            </h3>

                            <?php if (trim(strip_tags($card['description'])) !== '') { ?>
                                <div class="wp_editor mt-4 [&_p:last-child]:mb-0 [&_p]:font-primary [&_p]:text-[16px] [&_p]:leading-[28px] [&_p]:text-[#08284B]">
                                    <?php echo wp_kses_post($card['description']); ?>
                                </div>
                            <?php } ?>
                        </div>

                        <span class="mt-8 flex justify-end text-[#024B79]" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M8 6L16 12L8 18" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </a>
                <?php } else { ?>
                    <article
                        class="<?php echo esc_attr($card_classes); ?>"
                        style="--card-bg: <?php echo esc_attr($card_background); ?>;"
                    >
                        <div>
                            <h3 class="font-primary text-[24px] font-semibold leading-[32px] tracking-[-0.18px] text-[#08284B]">
                                <?php echo esc_html($card['title']); ?>
                            </h3>

                            <?php if (trim(strip_tags($card['description'])) !== '') { ?>
                                <div class="wp_editor mt-4 [&_p:last-child]:mb-0 [&_p]:font-primary [&_p]:text-[16px] [&_p]:leading-[28px] [&_p]:text-[#08284B]">
                                    <?php echo wp_kses_post($card['description']); ?>
                                </div>
                            <?php } ?>
                        </div>
                    </article>
                <?php } ?>
            <?php } ?>
        </div>
    </div>
</section>
