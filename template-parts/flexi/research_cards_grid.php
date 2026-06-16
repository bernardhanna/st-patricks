<?php

$defaults = matrix_get_research_cards_grid_defaults();

$section_id = 'research-cards-grid-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());
$heading = trim((string) get_sub_field('heading')) ?: $defaults['heading'];
$heading_tag = (string) get_sub_field('heading_tag');
$intro = get_sub_field('intro');
$cards_source = (string) get_sub_field('cards_source');
$posts_per_page = (int) get_sub_field('posts_per_page');

if ($posts_per_page < 1) {
    $posts_per_page = 4;
}

$cards = matrix_resolve_research_cards_grid_cards([
    'cards_source' => $cards_source !== '' ? $cards_source : 'manual',
    'manual_cards' => get_sub_field('cards'),
    'selected_projects' => get_sub_field('selected_research_projects'),
    'selected_categories' => get_sub_field('selected_research_categories'),
    'posts_per_page' => $posts_per_page,
]);
$footer_button_link = matrix_normalize_research_cards_grid_link(get_sub_field('footer_button_link'));
$background_color = (string) get_sub_field('background_color') ?: $defaults['background_color'];
$heading_color = (string) get_sub_field('heading_color') ?: $defaults['heading_color'];
$intro_color = (string) get_sub_field('intro_color') ?: $defaults['intro_color'];
$card_title_color = (string) get_sub_field('card_title_color') ?: $defaults['card_title_color'];
$card_body_color = (string) get_sub_field('card_body_color') ?: $defaults['card_body_color'];
$button_border_color = (string) get_sub_field('button_border_color') ?: $defaults['button_border_color'];
$button_text_color = (string) get_sub_field('button_text_color') ?: $defaults['button_text_color'];

if (! in_array($heading_tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p'], true)) {
    $heading_tag = 'h2';
}

$card_title_tag = matrix_get_research_cards_grid_card_title_tag($heading_tag);

if ($cards === []) {
    return;
}

$wrapper_classes = ['flex', 'w-full', 'max-w-[1018px]', 'flex-col', 'items-center', 'mx-auto', 'pt-5', 'pb-5', 'max-xl:px-5'];


$grid_classes = 'mt-8 grid grid-cols-1 gap-8 sm:grid-cols-2 xl:mt-12 xl:grid-cols-4';
$card_base_classes = 'group flex h-full flex-col gap-4 text-left';
$linked_card_classes = $card_base_classes . ' focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]';
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
    class="flex overflow-hidden relative"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
>
    <div class="py-12 lg:py-[100px] <?php echo esc_attr(implode(' ', $wrapper_classes)); ?>">
        <div class="w-full">
            <<?php echo esc_attr($heading_tag); ?>
                class="font-primary text-[24px] font-semibold leading-[28px] tracking-[-0.18px] lg:text-[30px] lg:leading-[36px] lg:tracking-[-0.225px]"
                style="color: <?php echo esc_attr($heading_color); ?>;"
            >
                <?php echo esc_html($heading); ?>
            </<?php echo esc_attr($heading_tag); ?>>

            <div class="mt-6 h-[4px] w-10 bg-[#6FC9C0]"></div>

            <?php if (is_string($intro) && trim(strip_tags($intro)) !== '') { ?>
                <div
                    class="wp_editor mt-6 max-w-[1018px] [&_p:last-child]:mb-0 [&_p]:font-primary [&_p]:text-[16px] [&_p]:font-medium [&_p]:leading-[28px]"
                    style="color: <?php echo esc_attr($intro_color); ?>;"
                >
                    <?php echo matrix_kses_rich_text($intro); ?>
                </div>
            <?php } ?>

            <div class="<?php echo esc_attr($grid_classes); ?>">
                <?php foreach ($cards as $card) { ?>
                    <?php
                    $is_linked = is_array($card['link']) && ! empty($card['link']['url']);
                    $card_tag = $is_linked ? 'a' : 'article';
                    $card_classes = $is_linked ? $linked_card_classes : $card_base_classes;
                    $card_target = $is_linked ? (string) ($card['link']['target'] ?? '_self') : '_self';
                    $image = is_array($card['image']) ? $card['image'] : null;
                    $image_id = is_array($image) ? (int) ($image['ID'] ?? 0) : 0;
                    $image_url = is_array($image) ? trim((string) ($image['url'] ?? '')) : '';
                    $image_alt = is_array($image) ? trim((string) ($image['alt'] ?? '')) : '';

                    if ($image_id > 0 && $image_alt === '') {
                        $image_alt = trim((string) get_post_meta($image_id, '_wp_attachment_image_alt', true));
                    }

                    if ($image_alt === '') {
                        $image_alt = $card['title'];
                    }
                    ?>

                    <<?php echo esc_attr($card_tag); ?>
                        class="<?php echo esc_attr($card_classes); ?>"
                        <?php if ($is_linked) { ?>
                            href="<?php echo esc_url($card['link']['url']); ?>"
                            target="<?php echo esc_attr($card_target); ?>"
                            <?php if ($card_target === '_blank') { ?>
                                rel="noopener noreferrer"
                            <?php } ?>
                        <?php } ?>
                    >
                        <?php if ($image_id > 0 || $image_url !== '') { ?>
                            <div class="h-[10.5rem] rounded-[4px] w-full overflow-hidden bg-[#F8F6F3]">
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

                        <div class="flex flex-col flex-1">
                            <div class="flex gap-3 justify-between items-start">
                                <<?php echo esc_attr($card_title_tag); ?>
                                    class="font-primary text-[20px] font-semibold leading-[24px] tracking-[-0.12px]"
                                    style="color: <?php echo esc_attr($card_title_color); ?>;"
                                >
                                    <?php echo esc_html($card['title']); ?>
                                </<?php echo esc_attr($card_title_tag); ?>>

                                <?php if ($is_linked) { ?>
                                    <span class="shrink-0" style="color: <?php echo esc_attr($card_title_color); ?>;" aria-hidden="true">
                                       <span class="ml-1 text-[#1E244B] font-primary text-xl font-semibold leading-8 tracking-[-0.0075rem]" aria-hidden="true">→</span>
                                    </span>
                                <?php } ?>
                            </div>

                            <?php if (trim(strip_tags($card['summary'])) !== '') { ?>
                                <div
                                    class="wp_editor mt-3 [&_p:last-child]:mb-0 [&_p]:font-primary [&_p]:text-[16px] [&_p]:leading-[28px]"
                                    style="color: <?php echo esc_attr($card_body_color); ?>;"
                                >
                                    <?php echo matrix_kses_rich_text($card['summary']); ?>
                                </div>
                            <?php } ?>
                        </div>
                    </<?php echo esc_attr($card_tag); ?>>
                <?php } ?>
            </div>

            <?php if (is_array($footer_button_link) && ! empty($footer_button_link['url'])) { ?>
                <?php
                $button_title = (string) ($footer_button_link['title'] ?? 'Learn more');
                $button_target = (string) ($footer_button_link['target'] ?? '_self');
                ?>
                <div class="flex justify-start mt-10 w-full sm:justify-end">
                    <a
                        href="<?php echo esc_url($footer_button_link['url']); ?>"
                        target="<?php echo esc_attr($button_target); ?>"
                        class="btn inline-flex w-fit whitespace-nowrap items-center justify-center rounded-md border border-[var(--research-cards-grid-button-border)] px-4 py-2 text-[14px] font-medium leading-[24px] text-[var(--research-cards-grid-button-text)] transition-colors duration-200 hover:bg-[var(--research-cards-grid-button-border)] hover:text-white focus-visible:bg-[var(--research-cards-grid-button-border)] focus-visible:text-white"
                        style="--research-cards-grid-button-border: <?php echo esc_attr($button_border_color); ?>; --research-cards-grid-button-text: <?php echo esc_attr($button_text_color); ?>;"
                        <?php if ($button_target === '_blank') { ?>
                            rel="noopener noreferrer"
                        <?php } ?>
                    >
                        <?php echo esc_html($button_title); ?>
                    </a>
                </div>
            <?php } ?>
        </div>
    </div>
</section>
