<?php

$section_id = 'latest-posts-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());
$heading = trim((string) get_sub_field('heading'));
$heading_tag = (string) get_sub_field('heading_tag');
$selected_categories = get_sub_field('selected_categories');
$header_button_link = get_sub_field('header_button_link');
$empty_state_message = trim((string) get_sub_field('empty_state_message'));
$background_color = (string) get_sub_field('background_color');
$heading_color = (string) get_sub_field('heading_color');
$card_title_color = (string) get_sub_field('card_title_color');

if ($heading === '') {
    $heading = 'Latest News, Events, and Expert advice from SPMHS';
}

if (! in_array($heading_tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p'], true)) {
    $heading_tag = 'h2';
}

if ($background_color === '') {
    $background_color = '#FBFAF7';
}

if ($heading_color === '') {
    $heading_color = '#1E244B';
}

if ($card_title_color === '') {
    $card_title_color = '#1E244B';
}

if ($empty_state_message === '') {
    $empty_state_message = 'No posts are available yet.';
}



$query_args = matrix_build_latest_posts_query_args($selected_categories, 6);
$latest_posts_query = new WP_Query($query_args);
$latest_post_cards = [];

if ($latest_posts_query->have_posts()) {
    while ($latest_posts_query->have_posts()) {
        $latest_posts_query->the_post();
        $card = matrix_normalize_latest_posts_card(get_the_ID());

        if (is_array($card)) {
            $latest_post_cards[] = $card;
        }
    }

    wp_reset_postdata();
}
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
    class="flex overflow-hidden relative"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
>
    <div class="py-12 pb-16 lg:py-[100px] lg:pb-[120px]">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between lg:gap-8">
            <div class="max-w-[680px]">
                <<?php echo esc_attr($heading_tag); ?>
                    class="font-primary text-[24px] font-semibold leading-[28px] tracking-[-0.18px] lg:text-[30px] lg:leading-[36px] lg:tracking-[-0.225px]"
                    style="color: <?php echo esc_attr($heading_color); ?>;"
                >
                    <?php echo esc_html($heading); ?>
                </<?php echo esc_attr($heading_tag); ?>>

                <div class="mt-6 h-[4px] w-10 bg-[#6FC9C0]" aria-hidden="true"></div>
            </div>

            <?php if (is_array($header_button_link) && ! empty($header_button_link['url'])) { ?>
                <a
                    href="<?php echo esc_url($header_button_link['url']); ?>"
                    target="<?php echo esc_attr($header_button_link['target'] ?: '_self'); ?>"
                    class="<?php echo esc_attr(matrix_get_latest_posts_header_button_class_names()); ?>"
                    <?php if (($header_button_link['target'] ?? '') === '_blank') { ?>
                        rel="noopener noreferrer"
                    <?php } ?>
                >
                    <?php echo esc_html($header_button_link['title'] ?: 'View all posts'); ?>
                </a>
            <?php } ?>
        </div>

        <?php if ($latest_post_cards !== []) { ?>
            <div class="grid grid-cols-2 gap-x-8 gap-y-12 mt-12 md:grid-cols-2 lg:mt-16 lg:grid-cols-3 lg:gap-y-16">
                <?php foreach ($latest_post_cards as $card) { ?>
                    <a
                        href="<?php echo esc_url($card['permalink']); ?>"
                        class="group flex h-full flex-col gap-4 rounded-lg  focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
                    >
                        <?php if ($card['thumbnail_url'] !== '') { ?>
                            <div class="h-[129px] w-full overflow-hidden rounded-[4px]">
                                <img
                                    src="<?php echo esc_url($card['thumbnail_url']); ?>"
                                    alt="<?php echo esc_attr($card['thumbnail_alt']); ?>"
                                    class="h-full w-full object-cover transition-transform duration-200 group-hover:scale-[1.02]"
                                />
                            </div>
                        <?php } else { ?>
                            <div class="flex h-[129px] w-full items-center justify-center rounded-[4px] bg-[#E7EEF0] px-4 text-center font-primary text-[14px] font-medium leading-[20px] text-[#08284B]">
                                <?php echo esc_html($card['title']); ?>
                            </div>
                        <?php } ?>

                        <h3
                            class="font-primary text-[18px] font-semibold leading-[28px] tracking-[-0.12px] transition-colors group-hover:text-[#024B79] mob:text-[20px] mob:leading-[32px]"
                            style="color: <?php echo esc_attr($card_title_color); ?>;"
                        >
                            <?php echo esc_html($card['title']); ?>
                            <span class="whitespace-nowrap" aria-hidden="true"> →</span>
                        </h3>
                    </a>
                <?php } ?>
            </div>
        <?php } else { ?>
            <p class="mt-12 font-primary text-[16px] leading-[28px] text-[#08284B] lg:mt-16">
                <?php echo esc_html($empty_state_message); ?>
            </p>
        <?php } ?>
    </div>
</section>
