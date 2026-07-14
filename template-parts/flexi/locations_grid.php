<?php

$section_id = 'locations-grid-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());
$heading = trim((string) get_sub_field('heading'));
$heading_tag = (string) get_sub_field('heading_tag');
$source_mode = (string) get_sub_field('source_mode');
$cards = matrix_resolve_locations_grid_cards(
    $source_mode,
    get_sub_field('cards'),
    get_sub_field('selected_locations')
);
$footer_button_link = matrix_normalize_locations_grid_link(get_sub_field('footer_button_link'));

if ($heading === '') {
    $heading = 'Our locations';
}

if (! in_array($heading_tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p'], true)) {
    $heading_tag = 'h2';
}

if ($cards === []) {
    return;
}

$wrapper_classes = ['flex', 'flex-col', 'items-center', 'w-full', 'mx-auto', 'pt-5', 'pb-5', 'max-xl:px-5', 'max-w-[1018px]'];

?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
    class="flex overflow-hidden relative bg-white"
>
    <div class="py-12 lg:py-[100px] <?php echo esc_attr(implode(' ', array_unique($wrapper_classes))); ?>">
        <div class="w-full">
            <<?php echo esc_attr($heading_tag); ?>
                class="font-primary text-[24px] font-semibold leading-[28px] tracking-[-0.18px] text-[#1E244B] lg:text-[30px] lg:leading-[36px] lg:tracking-[-0.225px]"
            >
                <?php echo esc_html($heading); ?>
            </<?php echo esc_attr($heading_tag); ?>>

            <div class="mt-6 h-[4px] w-10 bg-[#6FC9C0]"></div>

            <div class="grid grid-cols-1 gap-4 mt-8 sm:grid-cols-2 lg:mt-12 lg:grid-cols-3 lg:gap-8">
                <?php foreach ($cards as $card) { ?>
                    <?php
                    $card_tag = $card['is_linked'] ? 'a' : 'article';
                    $card_target = (string) ($card['link']['target'] ?? '_self');
                    $card_classes = 'group flex h-full flex-col overflow-hidden rounded-[8px] bg-[#FBFAF7] shadow-[0px_1px_1px_rgba(0,0,0,0.05)]';

                    if ($card['is_linked']) {
                        $card_classes .= ' focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]';
                    }

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
                        <?php if ($card['is_linked']) { ?>
                            href="<?php echo esc_url($card['link']['url']); ?>"
                            target="<?php echo esc_attr($card_target); ?>"
                            <?php if ($card_target === '_blank') { ?>
                                rel="noopener noreferrer"
                            <?php } ?>
                        <?php } ?>
                    >
                        <?php if ($image_id > 0 || $image_url !== '') { ?>
                            <div class="h-[273px] w-full overflow-hidden bg-[#F8F6F3]">
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

                        <div class="bg-[#FBFAF7] p-6">
                            <h3 class="font-primary text-[20px] font-semibold leading-[24px] tracking-[-0.12px] text-[#1E244B]">
                                <?php echo esc_html($card['title']); ?>
                            </h3>
                        </div>
                    </<?php echo esc_attr($card_tag); ?>>
                <?php } ?>
            </div>

            <?php if (is_array($footer_button_link)) { ?>
                <?php $button_target = (string) ($footer_button_link['target'] ?? '_self'); ?>
                <div class="mt-[34px] flex w-full justify-start">
                    <a
                        href="<?php echo esc_url($footer_button_link['url']); ?>"
                        target="<?php echo esc_attr($button_target); ?>"
                        class="btn inline-flex h-[36px] w-fit items-center justify-center whitespace-nowrap rounded-[6px] border border-[#024B79] px-3 text-[14px] font-medium leading-[24px] text-[#08284B] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
                        <?php if ($button_target === '_blank') { ?>
                            rel="noopener noreferrer"
                        <?php } ?>
                    >
                        <?php echo esc_html($footer_button_link['title']); ?>
                    </a>
                </div>
            <?php } ?>
        </div>
    </div>
</section>
