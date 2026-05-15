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

$query_args = matrix_build_latest_posts_query_args($selected_categories, 6);
$latest_posts_query = new WP_Query($query_args);
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
    class="relative flex overflow-hidden"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
>
    <div class="<?php echo esc_attr(implode(' ', array_unique(array_merge(['mx-auto', 'flex', 'w-full', 'max-w-[1018px]', 'flex-col', 'max-xl:px-5'], $padding_classes)))); ?>">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-[680px]">
                <<?php echo esc_attr($heading_tag); ?>
                    class="font-primary text-[24px] font-semibold leading-[28px] tracking-[-0.18px] lg:text-[30px] lg:leading-[36px] lg:tracking-[-0.225px]"
                    style="color: <?php echo esc_attr($heading_color); ?>;"
                >
                    <?php echo esc_html($heading); ?>
                </<?php echo esc_attr($heading_tag); ?>>

                <div class="mt-4 h-[4px] w-10 bg-[#6FC9C0]"></div>
            </div>

            <?php if (is_array($header_button_link) && ! empty($header_button_link['url'])) { ?>
                <a
                    href="<?php echo esc_url($header_button_link['url']); ?>"
                    target="<?php echo esc_attr($header_button_link['target'] ?: '_self'); ?>"
                    class="inline-flex min-h-[52px] items-center justify-center border border-[#024B79] px-6 py-4 text-[16px] font-semibold leading-none text-[#024B79] transition-colors hover:bg-[#024B79] hover:text-white focus:outline focus:outline-2 focus:outline-offset-2 focus:outline-[#024B79]"
                >
                    <?php echo esc_html($header_button_link['title'] ?: 'View all posts'); ?>
                </a>
            <?php } ?>
        </div>

        <?php if ($latest_posts_query->have_posts()) { ?>
            <div class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-2 lg:mt-12 lg:grid-cols-3 lg:gap-8">
                <?php while ($latest_posts_query->have_posts()) { ?>
                    <?php
                    $latest_posts_query->the_post();
                    $post_id = get_the_ID();
                    $title = get_the_title($post_id);
                    $permalink = get_permalink($post_id);
                    $thumbnail_id = get_post_thumbnail_id($post_id);
                    $thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'large') : '';
                    $thumbnail_alt = $thumbnail_id ? (string) get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true) : '';
                    ?>
                    <article class="flex h-full flex-col overflow-hidden rounded-[8px] bg-white shadow-[0px_1px_1px_rgba(0,0,0,0.05)]">
                        <?php if ($thumbnail_url !== '') { ?>
                            <a href="<?php echo esc_url($permalink); ?>" class="block overflow-hidden">
                                <img
                                    src="<?php echo esc_url($thumbnail_url); ?>"
                                    alt="<?php echo esc_attr($thumbnail_alt !== '' ? $thumbnail_alt : $title); ?>"
                                    class="h-[240px] w-full object-cover"
                                />
                            </a>
                        <?php } ?>

                        <div class="flex flex-1 flex-col p-5 lg:p-6">
                            <h3 class="font-primary text-[24px] font-semibold leading-[30px] tracking-[-0.18px]">
                                <a
                                    href="<?php echo esc_url($permalink); ?>"
                                    class="transition-colors hover:text-[#024B79] focus:outline focus:outline-2 focus:outline-offset-2 focus:outline-[#024B79]"
                                    style="color: <?php echo esc_attr($card_title_color); ?>;"
                                >
                                    <?php echo esc_html($title); ?>
                                </a>
                            </h3>
                        </div>
                    </article>
                <?php } ?>
            </div>
            <?php wp_reset_postdata(); ?>
        <?php } else { ?>
            <p class="mt-8 font-primary text-[16px] leading-[28px] text-[#08284B] lg:mt-12">
                <?php echo esc_html($empty_state_message); ?>
            </p>
        <?php } ?>
    </div>
</section>
