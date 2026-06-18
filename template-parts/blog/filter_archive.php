<?php

$defaults = matrix_get_blog_filter_archive_defaults();
$blog_filter_archive = is_array($args['blog_filter_archive'] ?? null) ? $args['blog_filter_archive'] : [];
if ($blog_filter_archive === []) {
    return;
}

$default_heading = (string) ($defaults['heading'] ?? 'News and events');
$default_filter_label = (string) ($defaults['filter_label'] ?? 'Filter by:');
$default_search_placeholder = (string) ($defaults['search_placeholder'] ?? 'Search news and events');
$default_search_button_label = (string) ($defaults['search_button_label'] ?? 'Search');
$default_empty_state_message = (string) ($defaults['empty_state_message'] ?? 'No posts matched your filters.');
$default_posts_per_page = (int) ($defaults['posts_per_page'] ?? 12);
$heading_tag = (string) ($blog_filter_archive['heading_tag'] ?? 'h2');
$heading = trim((string) ($blog_filter_archive['heading'] ?? ''));
$filter_label = trim((string) ($blog_filter_archive['filter_label'] ?? $default_filter_label));
$search_placeholder = trim((string) ($blog_filter_archive['search_placeholder'] ?? $default_search_placeholder));
$search_button_label = trim((string) ($blog_filter_archive['search_button_label'] ?? $default_search_button_label));
$empty_state_message = trim((string) ($blog_filter_archive['empty_state_message'] ?? $default_empty_state_message));
$base_url = (string) ($blog_filter_archive['base_url'] ?? home_url('/'));
$state = is_array($blog_filter_archive['state'] ?? null) ? $blog_filter_archive['state'] : [];
$chips = is_array($blog_filter_archive['chips'] ?? null) ? $blog_filter_archive['chips'] : [];
$pagination = is_array($blog_filter_archive['pagination'] ?? null) ? $blog_filter_archive['pagination'] : [];
$colors = is_array($blog_filter_archive['colors'] ?? null) ? $blog_filter_archive['colors'] : [];
$section_id = trim((string) ($blog_filter_archive['section_id'] ?? ''));
$data_block = trim((string) ($blog_filter_archive['data_block'] ?? ''));
$section_classes = trim((string) ($blog_filter_archive['section_classes'] ?? 'w-full'));
$section_style = trim((string) ($blog_filter_archive['section_style'] ?? ''));
$wrapper_classes = trim((string) ($blog_filter_archive['wrapper_classes'] ?? ''));
$query = $blog_filter_archive['query'] ?? null;

if (! in_array($heading_tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p'], true)) {
    $heading_tag = 'h2';
}

$show_heading = array_key_exists('show_heading', $blog_filter_archive)
    ? (bool) $blog_filter_archive['show_heading']
    : true;

if ($heading === '' && $show_heading) {
    $heading = $default_heading;
}

if ($filter_label === '') {
    $filter_label = $default_filter_label;
}

if ($search_placeholder === '') {
    $search_placeholder = $default_search_placeholder;
}

if ($search_button_label === '') {
    $search_button_label = $default_search_button_label;
}

if ($empty_state_message === '') {
    $empty_state_message = $default_empty_state_message;
}

if ($wrapper_classes === '') {
    $wrapper_classes = 'flex w-full max-w-[1018px] flex-col items-center mx-auto pt-5 pb-5 max-xl:px-5';
}

$state = array_merge([
    'category' => 'all',
    'search' => '',
    'paged' => 1,
    'posts_per_page' => $default_posts_per_page,
], $state);

$colors = array_merge([
    'background' => '#FFFFFF',
    'filter_label' => '#08284B',
    'chip_text' => '#08284B',
    'chip_border' => '#08284B',
    'inactive_chip_background' => '#FFFFFF',
    'active_chip_background' => '#80CCD9',
    'active_chip_text' => '#08284B',
    'active_pagination_background' => '#024B79',
    'active_pagination_text' => '#FFFFFF',
    'search_input_text' => '#08284B',
    'search_input_border' => '#E2E8F0',
    'search_button_background' => '#08284B',
    'search_button_text' => '#FFFFFF',
    'card_background' => '#F1F8F9',
    'card_title' => '#1E244B',
    'card_meta' => '#1E244B',
    'card_excerpt' => '#1E244B',
], $colors);

$current_page = max(1, (int) ($pagination['current'] ?? $state['paged']));
$total_pages = max(1, (int) ($pagination['total'] ?? (($query instanceof WP_Query) ? $query->max_num_pages : 1)));
$controls_classes = matrix_get_filter_archive_controls_class_names();
$chip_scroll_classes = matrix_get_blog_filter_archive_horizontal_scroll_class_names();
$chip_group_classes = matrix_get_blog_filter_archive_horizontal_scroll_inner_class_names();
$grid_classes = matrix_get_filter_archive_card_grid_class_names();
$search_input_id = 'blog-filter-archive-search-' . wp_rand(1000, 999999);
$has_posts = $query instanceof WP_Query && $query->have_posts();
?>

<section
    <?php if ($section_id !== '') { ?>
        id="<?php echo esc_attr($section_id); ?>"
    <?php } ?>
    <?php if ($data_block !== '') { ?>
        data-matrix-block="<?php echo esc_attr($data_block); ?>"
    <?php } ?>
    class="<?php echo esc_attr($section_classes); ?>"
    <?php if ($section_style !== '') { ?>
        style="<?php echo esc_attr($section_style); ?>"
    <?php } ?>
>
    <div class="<?php echo esc_attr($wrapper_classes); ?>">
        <div
            x-data="{
                category: '<?php echo esc_js((string) $state['category']); ?>',
                pointerDown: false,
                dragging: false,
                dragThreshold: 10,
                startX: 0,
                scrollStart: 0,
                moved: false,
                submitCategory(slug) {
                    this.category = slug;
                    this.$refs.categoryInput.value = slug;
                    this.$refs.pageInput.value = 1;
                    this.$refs.form.submit();
                },
                onChipScrollPointerDown(event) {
                    if (event.pointerType === 'mouse' && event.button !== 0) {
                        return;
                    }

                    this.pointerDown = true;
                    this.dragging = false;
                    this.moved = false;
                    this.startX = event.clientX;
                    this.scrollStart = this.$refs.chipScroll.scrollLeft;
                },
                onChipScrollPointerMove(event) {
                    if (! this.pointerDown) {
                        return;
                    }

                    const distance = event.clientX - this.startX;

                    // Only treat the gesture as a drag once it clears the threshold,
                    // so a normal click/tap with minor pointer jitter still fires.
                    if (! this.dragging && Math.abs(distance) > this.dragThreshold) {
                        this.dragging = true;
                        this.moved = true;
                        this.$refs.chipScroll.setPointerCapture?.(event.pointerId);
                    }

                    if (this.dragging) {
                        this.$refs.chipScroll.scrollLeft = this.scrollStart - distance;
                    }
                },
                onChipScrollPointerUp(event) {
                    if (! this.pointerDown) {
                        return;
                    }

                    this.pointerDown = false;

                    if (this.dragging) {
                        this.dragging = false;
                        this.$refs.chipScroll.releasePointerCapture?.(event.pointerId);
                    }
                },
                onChipClick(event, slug) {
                    if (this.moved) {
                        event.preventDefault();
                        event.stopPropagation();
                        this.moved = false;
                        return;
                    }

                    this.submitCategory(slug);
                }
            }"
            class="w-full"
        >
            <?php if ($show_heading && $heading !== '') { ?>
                <<?php echo esc_attr($heading_tag); ?>
                    class="font-primary text-[24px] font-semibold leading-[28px] tracking-[-0.18px] lg:text-[30px] lg:leading-[36px] lg:tracking-[-0.225px]"
                    style="color: <?php echo esc_attr($colors['card_title']); ?>;"
                >
                    <?php echo esc_html($heading); ?>
                </<?php echo esc_attr($heading_tag); ?>>

                <div class="mt-6 h-[4px] w-10 bg-[#6FC9C0]" aria-hidden="true"></div>
            <?php } ?>

            <div class="<?php echo esc_attr($show_heading && $heading !== '' ? 'mt-8 ' : ''); ?><?php echo esc_attr($controls_classes); ?>">
                <div class="flex min-w-0 flex-col gap-4 lg:flex-1 lg:flex-row lg:items-center lg:gap-6">
                    <p
                        class="shrink-0 font-primary text-[16px] font-medium leading-[28px]"
                        style="color: <?php echo esc_attr($colors['filter_label']); ?>;"
                    >
                        <?php echo esc_html($filter_label); ?>
                    </p>

                    <div
                        x-ref="chipScroll"
                        class="<?php echo esc_attr($chip_scroll_classes); ?>"
                        @pointerdown="onChipScrollPointerDown($event)"
                        @pointermove="onChipScrollPointerMove($event)"
                        @pointerup="onChipScrollPointerUp($event)"
                        @pointercancel="onChipScrollPointerUp($event)"
                        @pointerleave="onChipScrollPointerUp($event)"
                    >
                        <div class="<?php echo esc_attr($chip_group_classes); ?>" role="group" aria-label="<?php echo esc_attr($filter_label); ?>">
                            <?php foreach ($chips as $chip) { ?>
                                <?php
                                $chip_slug = sanitize_title((string) ($chip['slug'] ?? ''));
                                $chip_label = trim((string) ($chip['label'] ?? ''));

                                if ($chip_slug === '' || $chip_label === '') {
                                    continue;
                                }
                                ?>
                                <button
                                    type="button"
                                    class="<?php echo esc_attr(matrix_get_blog_filter_archive_chip_button_class_names()); ?>"
                                    :aria-pressed="category === '<?php echo esc_js($chip_slug); ?>' ? 'true' : 'false'"
                                    :style="category === '<?php echo esc_js($chip_slug); ?>' ? 'border-color: <?php echo esc_js($colors['active_chip_background']); ?>; background-color: <?php echo esc_js($colors['active_chip_background']); ?>; color: <?php echo esc_js($colors['active_chip_text']); ?>;' : 'border-color: <?php echo esc_js($colors['chip_border']); ?>; background-color: <?php echo esc_js($colors['inactive_chip_background']); ?>; color: <?php echo esc_js($colors['chip_text']); ?>;'"
                                    @click="onChipClick($event, '<?php echo esc_js($chip_slug); ?>')"
                                >
                                    <?php echo esc_html($chip_label); ?>
                                </button>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <form
                    x-ref="form"
                    method="get"
                    action="<?php echo esc_url($base_url); ?>"
                    class="flex w-full max-w-[384px] items-center gap-2"
                    @submit="$refs.pageInput.value = 1"
                >
                    <input type="hidden" name="blog_category" x-ref="categoryInput" :value="category" />
                    <input type="hidden" name="blog_page" x-ref="pageInput" value="<?php echo esc_attr((string) $current_page); ?>" />

                    <div class="min-w-0 flex-1">
                        <label for="<?php echo esc_attr($search_input_id); ?>" class="sr-only">
                            <?php echo esc_html($search_placeholder); ?>
                        </label>
                        <input
                            id="<?php echo esc_attr($search_input_id); ?>"
                            type="search"
                            name="blog_search"
                            value="<?php echo esc_attr((string) $state['search']); ?>"
                            placeholder="<?php echo esc_attr($search_placeholder); ?>"
                            class="min-h-[40px] w-full rounded-[6px] border border-[#E2E8F0] px-3 py-2 font-primary text-[16px] leading-[24px] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#024B79]"
                            style="color: <?php echo esc_attr($colors['search_input_text']); ?>; border-color: <?php echo esc_attr($colors['search_input_border']); ?>;"
                        />
                    </div>

                    <button
                        type="submit"
                        class="btn inline-flex h-[36px] shrink-0 items-center justify-center gap-2 rounded-[6px] px-3 text-[14px] font-medium leading-[24px]"
                        style="background-color: <?php echo esc_attr($colors['search_button_background']); ?>; color: <?php echo esc_attr($colors['search_button_text']); ?>;"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true" class="shrink-0">
                            <path d="M6.99935 12.6667C9.94487 12.6667 12.3327 10.2789 12.3327 7.33333C12.3327 4.38781 9.94487 2 6.99935 2C4.05383 2 1.66602 4.38781 1.66602 7.33333C1.66602 10.2789 4.05383 12.6667 6.99935 12.6667Z" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M13.6656 14L10.7656 11.1" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <?php echo esc_html($search_button_label); ?>
                    </button>
                </form>
            </div>

            <?php if ($has_posts) { ?>
                <div class="<?php echo esc_attr($grid_classes); ?>">
                    <?php while ($query->have_posts()) { ?>
                        <?php
                        $query->the_post();
                        $post_id = get_the_ID();
                        $title = get_the_title($post_id);
                        $card_link = matrix_get_blog_post_link_target($post_id, 'archive');
                        $thumbnail_id = get_post_thumbnail_id($post_id);
                        $categories = get_the_category($post_id);
                        $primary_category = ($categories && $categories[0] instanceof WP_Term) ? $categories[0] : null;
                        $primary_category_name = $primary_category ? $primary_category->name : '';
                        $excerpt = trim((string) get_the_excerpt($post_id));

                        if ($excerpt === '') {
                            $excerpt = wp_trim_words(wp_strip_all_tags((string) get_post_field('post_content', $post_id)), 24, '...');
                        }

                        $thumbnail_alt = '';
                        if ($thumbnail_id > 0) {
                            $thumbnail_alt = trim((string) get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true));
                        }

                        if ($thumbnail_alt === '') {
                            $thumbnail_alt = $title;
                        }
                        ?>
                        <article
                            class="flex h-full flex-col overflow-hidden rounded-[8px]"
                            style="background-color: <?php echo esc_attr($colors['card_background']); ?>;"
                        >
                            <a
                                href="<?php echo esc_url($card_link['url']); ?>"
                                <?php if ($card_link['target'] === '_blank') { ?>
                                    target="_blank"
                                    rel="<?php echo esc_attr($card_link['rel']); ?>"
                                <?php } ?>
                                class="block overflow-hidden focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
                            >
                                <?php if ($thumbnail_id > 0) { ?>
                                    <?php
                                    echo wp_get_attachment_image($thumbnail_id, 'large', false, [
                                        'class' => 'h-[240px] w-full object-cover',
                                        'alt' => $thumbnail_alt,
                                    ]);
                                    ?>
                                <?php } else { ?>
                                    <div class="flex h-[240px] w-full items-center justify-center bg-[#E7EEF0] px-6 text-center font-primary text-[14px] font-medium leading-[20px] text-[#08284B]">
                                        <?php echo esc_html($title); ?>
                                    </div>
                                <?php } ?>
                            </a>

                            <div class="flex flex-col flex-1 p-5 lg:p-6">
                                <?php if ($primary_category_name !== '') { ?>
                                    <?php
                                    $category_badge_colors = matrix_get_blog_post_category_badge_colors(
                                        $primary_category_name,
                                        $primary_category ? $primary_category->slug : ''
                                    );
                                    ?>
                                    <div class="mb-4">
                                        <span
                                            class="inline-flex h-[30px] w-fit items-center justify-center rounded-full px-4 font-primary text-[14px] font-medium leading-[24px]"
                                            style="background-color: <?php echo esc_attr($category_badge_colors['background']); ?>; color: <?php echo esc_attr($category_badge_colors['text']); ?>;"
                                        >
                                            <?php echo esc_html($primary_category_name); ?>
                                        </span>
                                    </div>
                                <?php } ?>

                                <h3 class="<?php echo esc_attr(matrix_get_filter_archive_card_title_class_names()); ?>">
                                    <a
                                        href="<?php echo esc_url($card_link['url']); ?>"
                                        <?php if ($card_link['target'] === '_blank') { ?>
                                            target="_blank"
                                            rel="<?php echo esc_attr($card_link['rel']); ?>"
                                        <?php } ?>
                                        class="focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
                                        style="color: <?php echo esc_attr($colors['card_title']); ?>;"
                                    >
                                        <?php echo esc_html($title); ?>
                                    </a>
                                </h3>

                                <p
                                    class="<?php echo esc_attr(matrix_get_filter_archive_card_date_class_names()); ?>"
                                    style="color: <?php echo esc_attr($colors['card_meta']); ?>;"
                                >
                                    <time datetime="<?php echo esc_attr(get_the_date('c', $post_id)); ?>">
                                        <?php echo esc_html(matrix_format_blog_post_date($post_id)); ?>
                                    </time>
                                </p>

                                <?php if ($excerpt !== '') { ?>
                                    <p
                                        class="<?php echo esc_attr(matrix_get_filter_archive_card_excerpt_class_names()); ?>"
                                        style="color: <?php echo esc_attr($colors['card_excerpt']); ?>;"
                                    >
                                        <?php echo esc_html($excerpt); ?>
                                    </p>
                                <?php } ?>
                            </div>
                        </article>
                    <?php } ?>
                    <?php wp_reset_postdata(); ?>
                </div>
            <?php } else { ?>
                <p
                    class="mt-8 font-primary text-[16px] leading-[28px] lg:mt-10"
                    style="color: <?php echo esc_attr($colors['card_excerpt']); ?>;"
                >
                    <?php echo esc_html($empty_state_message); ?>
                </p>
            <?php } ?>

            <?php
            get_template_part('template-parts/partials/archive-pagination', null, [
                'archive_pagination' => [
                    'current_page' => $current_page,
                    'total_pages' => $total_pages,
                    'aria_label' => 'Archive pagination',
                    'colors' => $colors,
                    'build_page_url' => static function (int $page) use ($base_url, $state): string {
                        return matrix_build_blog_filter_archive_page_url($base_url, $state, $page);
                    },
                ],
            ]);
            ?>
        </div>
    </div>
</section>
