<?php

$section_id = 'faqs-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());
$heading = trim((string) get_sub_field('heading'));
$heading_tag = (string) get_sub_field('heading_tag');
$show_heading_value = get_sub_field('show_heading');
$show_heading = $show_heading_value === null || $show_heading_value === '' ? true : (bool) $show_heading_value;
$layout_style = function_exists('matrix_resolve_faq_layout_style')
    ? matrix_resolve_faq_layout_style(get_sub_field('layout_style'))
    : 'default';
$source_mode = (string) get_sub_field('source_mode');
$selected_faqs = get_sub_field('selected_faqs');
$selected_faq_categories = get_sub_field('selected_faq_categories');
$empty_state_message = trim((string) get_sub_field('empty_state_message'));
$section_background = (string) get_sub_field('section_background');
$heading_color = (string) get_sub_field('heading_color');
$underline_color = (string) get_sub_field('underline_color');
$item_background = (string) get_sub_field('item_background');
$open_item_background = (string) get_sub_field('open_item_background');
$question_color = (string) get_sub_field('question_color');
$answer_color = (string) get_sub_field('answer_color');

if ($heading === '') {
    $heading = 'FAQs';
}

if (! in_array($heading_tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p'], true)) {
    $heading_tag = 'h2';
}

if (! in_array($source_mode, ['all', 'selected', 'category'], true)) {
    $source_mode = 'all';
}

if ($empty_state_message === '') {
    $empty_state_message = 'No FAQs are available right now.';
}

if ($heading_color === '') {
    $heading_color = '#1E244B';
}

if ($underline_color === '') {
    $underline_color = '#6FC9C0';
}

if ($question_color === '') {
    $question_color = '#1E244B';
}

if ($answer_color === '') {
    $answer_color = '#08284B';
}

$wrapper_classes = $layout_style === 'page'
    ? ['flex', 'w-full', 'max-w-[1018px]', 'flex-col', 'mx-auto', 'px-5', 'py-12', 'xl:px-0', 'xl:py-[100px]']
    : ['flex', 'w-full', 'max-w-[1018px]', 'flex-col', 'items-center', 'mx-auto', 'pt-5', 'pb-5', 'max-xl:px-5'];

$padding_classes = [];
if ($layout_style !== 'page' && have_rows('padding_settings')) {
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

$selected_category_ids = array_values(array_filter(array_map('intval', is_array($selected_faq_categories) ? $selected_faq_categories : [])));
$category_posts = [];
$all_posts = [];

if ($source_mode === 'category' && $selected_category_ids !== []) {
    $category_posts = get_posts([
        'post_type' => 'faqs',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => [
            'menu_order' => 'ASC',
            'title' => 'ASC',
            'date' => 'DESC',
        ],
        'tax_query' => [
            [
                'taxonomy' => 'faq_category',
                'field' => 'term_id',
                'terms' => $selected_category_ids,
            ],
        ],
    ]);
} elseif ($source_mode === 'all') {
    $all_posts = get_posts([
        'post_type' => 'faqs',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => [
            'menu_order' => 'ASC',
            'title' => 'ASC',
            'date' => 'DESC',
        ],
    ]);
}

$faq_items = matrix_resolve_faq_items(
    $source_mode,
    is_array($selected_faqs) ? $selected_faqs : [],
    $category_posts,
    $all_posts
);

$initial_open_index = -1;
foreach ($faq_items as $index => $faq_item) {
    if (! empty($faq_item['starts_open'])) {
        $initial_open_index = $index;
        break;
    }
}

if ($initial_open_index < 0 && $faq_items !== []) {
    $initial_open_index = 0;
}

$section_background_style = matrix_get_faq_background_style($section_background, '#FBFAF7');
$item_background_style = matrix_get_faq_background_style($item_background, '#FFFFFF');
$open_item_background_style = matrix_get_faq_background_style($open_item_background, 'linear-gradient(135deg, #F8F6F3 0%, #F5F6ED 100%)');
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
    class="relative flex overflow-hidden"
    style="<?php echo esc_attr($section_background_style); ?>"
>
    <div
        x-data="{ activeIndex: <?php echo esc_attr((string) $initial_open_index); ?>, toggleItem(index) { this.activeIndex = this.activeIndex === index ? -1 : index; } }"
        class="<?php echo esc_attr(implode(' ', array_unique(array_merge($wrapper_classes, $padding_classes)))); ?>"
    >
        <div class="w-full">
            <?php if ($show_heading) { ?>
            <<?php echo esc_attr($heading_tag); ?>
                class="max-w-[312px] font-primary text-[24px] font-semibold leading-[28px] tracking-[-0.18px] lg:text-[30px] lg:leading-[36px] lg:tracking-[-0.225px]"
                style="color: <?php echo esc_attr($heading_color); ?>;"
            >
                <?php echo esc_html($heading); ?>
            </<?php echo esc_attr($heading_tag); ?>>

            <div class="mt-4 h-[4px] w-10" style="background-color: <?php echo esc_attr($underline_color); ?>;" aria-hidden="true"></div>
            <?php } ?>

            <?php if ($faq_items === []) { ?>
                <p class="<?php echo esc_attr($show_heading ? 'mt-8' : ''); ?> max-w-[1018px] font-primary text-[16px] font-medium leading-[28px] text-[#08284B]">
                    <?php echo esc_html($empty_state_message); ?>
                </p>
            <?php } else { ?>
                <div class="<?php echo esc_attr($show_heading ? 'mt-8 lg:mt-16' : ''); ?> flex w-full flex-col gap-3">
                    <?php foreach ($faq_items as $index => $faq_item) { ?>
                        <?php
                        $button_id = $section_id . '-button-' . $index;
                        $panel_id = $section_id . '-panel-' . $index;
                        ?>
                        <div
                            class="overflow-hidden rounded-[4px] border border-white"
                            :style="activeIndex === <?php echo esc_attr((string) $index); ?> ? '<?php echo esc_js($open_item_background_style); ?>' : '<?php echo esc_js($item_background_style); ?>'"
                        >
                            <button
                                type="button"
                                id="<?php echo esc_attr($button_id); ?>"
                                class="flex min-h-[58px] w-full items-center justify-between gap-4 px-6 py-4 text-left focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#024B79]"
                                :aria-expanded="activeIndex === <?php echo esc_attr((string) $index); ?> ? 'true' : 'false'"
                                aria-controls="<?php echo esc_attr($panel_id); ?>"
                                @click="toggleItem(<?php echo esc_attr((string) $index); ?>)"
                            >
                                <span
                                    class="font-primary text-[16px] font-semibold leading-[24px] tracking-[-0.1px] lg:text-[18px] lg:leading-[28px]"
                                    :class="activeIndex === <?php echo esc_attr((string) $index); ?> ? 'text-[#08284B]' : ''"
                                    style="color: <?php echo esc_attr($question_color); ?>;"
                                >
                                    <?php echo esc_html($faq_item['question']); ?>
                                </span>

                                <span
                                    class="shrink-0 text-[#1E244B] transition-transform duration-200"
                                    :class="activeIndex === <?php echo esc_attr((string) $index); ?> ? 'rotate-180' : ''"
                                    aria-hidden="true"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                            </button>

                            <div
                                id="<?php echo esc_attr($panel_id); ?>"
                                x-show="activeIndex === <?php echo esc_attr((string) $index); ?>"
                                x-cloak
                                aria-labelledby="<?php echo esc_attr($button_id); ?>"
                                class="px-6 pb-4"
                            >
                                <div
                                    class="wp_editor border-t border-[rgba(30,36,75,0.12)] pt-4 text-[15px] font-medium leading-[28px] [&_a]:underline [&_p:last-child]:mb-0 [&_strong]:font-semibold lg:text-[16px]"
                                    style="color: <?php echo esc_attr($answer_color); ?>;"
                                >
                                    <?php echo wp_kses_post($faq_item['answer']); ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>
</section>
