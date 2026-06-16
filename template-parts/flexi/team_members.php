<?php

$section_id = 'team-members-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());
$heading = trim((string) get_sub_field('heading'));
$heading_tag = (string) get_sub_field('heading_tag');
$intro = get_sub_field('intro');
$layout_style = (string) get_sub_field('layout_style');
$source_mode = (string) get_sub_field('source_mode');
$selected_team_members = get_sub_field('selected_team_members');
$selected_team_categories = get_sub_field('selected_team_categories');
$posts_per_page = (int) get_sub_field('posts_per_page');
$section_background = (string) get_sub_field('section_background');
$card_background_color = (string) get_sub_field('card_background_color');
$spokespeople_card_background_color = (string) get_sub_field('spokespeople_card_background_color');

if ($heading === '') {
    $heading = 'Our Senior Management Team';
}

if (! in_array($heading_tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p'], true)) {
    $heading_tag = 'h2';
}

if ($layout_style !== 'spokespeople_grid') {
    $layout_style = 'standard_grid';
}

if (! in_array($source_mode, ['all', 'selected', 'category'], true)) {
    $source_mode = 'selected';
}

if ($posts_per_page < 1) {
    $posts_per_page = 9;
}

if ($card_background_color === '') {
    $card_background_color = '#FFFFFF';
}

if ($spokespeople_card_background_color === '') {
    $spokespeople_card_background_color = '#FBFAF7';
}



$team_posts = [];

if ($source_mode === 'selected' && is_array($selected_team_members)) {
    $team_posts = array_slice($selected_team_members, 0, $posts_per_page);
} else {
    $query_args = [
        'post_type' => 'team_members',
        'post_status' => 'publish',
        'posts_per_page' => $posts_per_page,
        'orderby' => [
            'menu_order' => 'ASC',
            'title' => 'ASC',
            'date' => 'DESC',
        ],
    ];

    $selected_category_ids = array_values(array_filter(array_map('intval', is_array($selected_team_categories) ? $selected_team_categories : [])));

    if ($source_mode === 'category' && $selected_category_ids !== []) {
        $query_args['tax_query'] = [
            [
                'taxonomy' => 'team_member_category',
                'field' => 'term_id',
                'terms' => $selected_category_ids,
            ],
        ];
    }

    $team_posts = get_posts($query_args);
}

$team_items = [];

foreach ((array) $team_posts as $team_post) {
    $post_id = $team_post instanceof WP_Post ? (int) $team_post->ID : (int) ($team_post['ID'] ?? 0);

    if ($post_id < 1) {
        continue;
    }

    $image_id = (int) get_post_thumbnail_id($post_id);
    $image_url = $image_id > 0 ? wp_get_attachment_image_url($image_id, 'medium_large') : '';
    $image_alt = $image_id > 0 ? (string) get_post_meta($image_id, '_wp_attachment_image_alt', true) : '';

    $item = matrix_normalize_team_member_item([
        'post_title' => get_the_title($post_id),
        'job_title' => (string) get_field('job_title', $post_id),
        'profile_teaser' => (string) get_field('profile_teaser', $post_id),
        'permalink' => get_permalink($post_id),
        'image' => $image_url !== '' ? [
            'ID' => $image_id,
            'url' => $image_url,
            'alt' => $image_alt !== '' ? $image_alt : get_the_title($post_id),
        ] : null,
    ], $layout_style);

    if (is_array($item)) {
        $team_items[] = $item;
    }
}

if ($team_items === []) {
    return;
}

$section_background_style = matrix_get_team_member_section_background_style($section_background, '#FBFAF7');
$card_surface_color = $layout_style === 'spokespeople_grid' ? $spokespeople_card_background_color : $card_background_color;
$grid_classes = $layout_style === 'spokespeople_grid'
    ? 'grid grid-cols-2 gap-4 mob:gap-5 lg:grid-cols-3 lg:gap-8'
    : 'grid grid-cols-2 gap-4 mob:gap-5 lg:grid-cols-3 lg:gap-8';
$card_content_classes = 'flex flex-1 p-3 mob:p-4 lg:p-6';

if ($layout_style === 'spokespeople_grid') {
    $card_content_classes .= ' flex-col';
} else {
    $card_content_classes .= ' items-center justify-between gap-3 max-sm:items-start max-sm:flex-col';
}
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
    class="flex overflow-hidden relative"
    style="<?php echo esc_attr($section_background_style); ?>"
>
    <div class="<?php echo esc_attr(matrix_get_flexi_section_wrapper_class_names()); ?>">
        <<?php echo esc_attr($heading_tag); ?>
            class="font-primary text-[24px] font-semibold leading-[28px] tracking-[-0.18px] text-[#1E244B] lg:text-[30px] lg:leading-[36px] lg:tracking-[-0.225px]"
        >
            <?php echo esc_html($heading); ?>
        </<?php echo esc_attr($heading_tag); ?>>

        <div class="mt-6 h-[4px] w-10 bg-[#6FC9C0]"></div>

        <?php if (is_string($intro) && trim(strip_tags($intro)) !== '') { ?>
            <div class="wp_editor mt-6 max-w-[1018px] [&_p:last-child]:mb-0 [&_p]:font-primary [&_p]:text-[16px] [&_p]:font-medium [&_p]:leading-[28px] [&_p]:text-[#08284B]">
                <?php echo matrix_kses_rich_text($intro); ?>
            </div>
        <?php } ?>

        <div class="mt-8 <?php echo esc_attr($grid_classes); ?> lg:mt-12">
            <?php foreach ($team_items as $team_item) { ?>
                <?php
                $is_linked = $team_item['permalink'] !== '';
                $card_tag = $is_linked ? 'a' : 'article';
                $card_classes = $layout_style === 'spokespeople_grid'
                    ? 'group flex h-full flex-col overflow-hidden rounded-[8px] border border-transparent bg-[var(--team-card-bg)] shadow-[0px_1px_1px_rgba(0,0,0,0.05)] transition-colors duration-200'
                    : 'group flex h-full flex-col overflow-hidden rounded-[8px] border border-transparent bg-[var(--team-card-bg)] shadow-[0px_1px_1px_rgba(0,0,0,0.05)] transition-colors duration-200';

                if ($is_linked) {
                    $card_classes .= ' hover:border-[#D9E4EE] focus-visible:border-[#D9E4EE] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]';
                }
                ?>

                <<?php echo esc_attr($card_tag); ?>
                    class="<?php echo esc_attr($card_classes); ?>"
                    style="--team-card-bg: <?php echo esc_attr($card_surface_color); ?>;"
                    <?php if ($is_linked) { ?>
                        href="<?php echo esc_url($team_item['permalink']); ?>"
                    <?php } ?>
                >
                    <?php if (is_array($team_item['image']) && ! empty($team_item['image']['url'])) { ?>
                        <div class="h-[92px] w-full overflow-hidden xs:h-[110px] mob:h-[134px] lg:h-[240px]">
                            <img
                                src="<?php echo esc_url($team_item['image']['url']); ?>"
                                alt="<?php echo esc_attr($team_item['image']['alt'] ?? $team_item['name']); ?>"
                                class="object-cover w-full h-full"
                            />
                        </div>
                    <?php } ?>

                    <div class="<?php echo esc_attr($card_content_classes); ?>">
                        <div class="min-w-0">
                            <h3 class="font-primary text-[16px] font-semibold leading-[20px] tracking-[-0.1px] text-[#1E244B] mob:text-[20px] mob:leading-[24px] mob:tracking-[-0.12px]">
                                <?php echo esc_html($team_item['name']); ?>
                            </h3>

                            <?php if ($team_item['job_title'] !== '') { ?>
                                <p class="mt-1 font-primary text-[14px] font-semibold leading-[16px] tracking-[-0.09px] text-[#1E244B]">
                                    <?php echo esc_html($team_item['job_title']); ?>
                                </p>
                            <?php } ?>

                            <?php if ($layout_style === 'spokespeople_grid' && trim(strip_tags($team_item['profile_teaser'])) !== '') { ?>
                                <div class="wp_editor mt-3 [&_p:last-child]:mb-0 [&_p]:font-primary [&_p]:text-[14px] [&_p]:leading-[24px] [&_p]:text-[#1E244B]">
                                    <?php echo matrix_kses_rich_text($team_item['profile_teaser']); ?>
                                </div>
                            <?php } ?>
                        </div>

                        <?php if ($team_item['show_arrow']) { ?>
                            <span class="ml-auto flex shrink-0 items-center justify-center text-[#08284B]" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="18" viewBox="0 0 10 18" fill="none">
                                <path d="M0.999999 1L9 9L1 17" stroke="#001F33" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                        <?php } ?>
                    </div>
                </<?php echo esc_attr($card_tag); ?>>
            <?php } ?>
        </div>
    </div>
</section>
