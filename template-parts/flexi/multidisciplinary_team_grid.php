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



if ($cards === []) {
    return;
}

$tone_backgrounds = matrix_get_multidisciplinary_team_grid_tone_backgrounds();
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
    class="flex overflow-hidden relative"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
>
    <div class="<?php echo esc_attr(matrix_get_flexi_section_wrapper_class_names()); ?>">
        <<?php echo esc_attr($heading_tag); ?>
            class="font-primary text-[24px] font-semibold leading-[28px] tracking-[-0.18px] text-[#08284B] lg:text-[30px] lg:leading-[36px] lg:tracking-[-0.225px]"
        >
            <?php echo esc_html($heading); ?>
        </<?php echo esc_attr($heading_tag); ?>>

        <div class="mt-6 h-[4px] w-10 bg-[#6FC9C0]"></div>

        <?php if (is_string($intro) && trim(strip_tags($intro)) !== '') { ?>
            <div class="wp_editor mt-6  [&_p:last-child]:mb-0 [&_p]:font-primary [&_p]:text-[16px] [&_p]:leading-[28px] [&_p]:text-[#08284B]">
                <?php echo matrix_kses_rich_text($intro); ?>
            </div>
        <?php } ?>

        <div class="grid grid-cols-1 gap-4 mt-10 lg:mt-12 lg:grid-cols-2">
            <?php foreach ($cards as $card) { ?>
                <?php
                $card_background = $tone_backgrounds[$card['card_tone']] ?? $tone_backgrounds['teal'];
                $card_classes = 'group flex min-h-[240px] flex-col justify-between border border-transparent bg-[var(--card-bg)] p-4 shadow-[0px_1px_1px_rgba(0,0,0,0.05)] lg:p-8';
                $linked_card_classes = $card_classes . ' transition-colors duration-200 hover:border-[#E2E8F0] hover:bg-white focus-visible:border-[#E2E8F0] focus-visible:bg-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]';
                ?>

                <?php if ($card['is_linked']) { ?>
                    <a
                        href="<?php echo esc_url($card['link']['url']); ?>"
                        target="<?php echo esc_attr($card['link']['target']); ?>"
                        class="rounded-lg <?php echo esc_attr($linked_card_classes); ?>"
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
                                    <?php echo matrix_kses_rich_text($card['description']); ?>
                                </div>
                            <?php } ?>
                        </div>

                        <span class="mt-8 flex justify-end text-[#024B79]" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="18" viewBox="0 0 10 18" fill="none">
                            <path d="M0.999999 1L9 9L1 17" stroke="#08284B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
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
                                    <?php echo matrix_kses_rich_text($card['description']); ?>
                                </div>
                            <?php } ?>
                        </div>
                    </article>
                <?php } ?>
            <?php } ?>
        </div>
    </div>
</section>
