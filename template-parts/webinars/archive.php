<?php

$defaults = matrix_get_webinars_archive_defaults();
$webinars_archive = is_array($args['webinars_archive'] ?? null) ? $args['webinars_archive'] : [];

if ($webinars_archive === []) {
    return;
}

$normalize_text = static function ($value, $fallback = '') {
    $value = trim((string) $value);

    return $value !== '' ? $value : $fallback;
};

$default_filter_label = (string) ($defaults['filter_label'] ?? 'Filter by:');
$default_search_placeholder = (string) ($defaults['search_placeholder'] ?? 'Search webinars and events');
$default_search_button_label = (string) ($defaults['search_button_label'] ?? 'Search');
$default_empty_state_message = (string) ($defaults['empty_state_message'] ?? 'No webinars matched your filters.');
$filter_label = $normalize_text($webinars_archive['filter_label'] ?? '', $default_filter_label);
$search_placeholder = $normalize_text($webinars_archive['search_placeholder'] ?? '', $default_search_placeholder);
$search_button_label = $normalize_text($webinars_archive['search_button_label'] ?? '', $default_search_button_label);
$empty_state_message = $normalize_text($webinars_archive['empty_state_message'] ?? '', $default_empty_state_message);
$base_url = (string) ($webinars_archive['base_url'] ?? home_url('/'));
$state = is_array($webinars_archive['state'] ?? null) ? $webinars_archive['state'] : [];
$chips = is_array($webinars_archive['chips'] ?? null) ? $webinars_archive['chips'] : [];
$pagination = is_array($webinars_archive['pagination'] ?? null) ? $webinars_archive['pagination'] : [];
$query = $webinars_archive['query'] ?? null;
$section_id = $normalize_text($webinars_archive['section_id'] ?? '');
$data_block = $normalize_text($webinars_archive['data_block'] ?? '');
$section_classes = $normalize_text($webinars_archive['section_classes'] ?? '', 'w-full');
$section_style = $normalize_text($webinars_archive['section_style'] ?? '');
$wrapper_classes = $normalize_text($webinars_archive['wrapper_classes'] ?? '', 'mx-auto flex w-full max-w-[1018px] flex-col px-5 py-12 xl:px-0 xl:py-[100px]');

$state = array_merge([
    'type' => 'all',
    'search' => '',
    'paged' => 1,
], $state);

$current_page = max(1, (int) ($pagination['current'] ?? $state['paged']));
$total_pages = max(1, (int) ($pagination['total'] ?? (($query instanceof WP_Query) ? $query->max_num_pages : 1)));
$search_input_id = 'webinars-archive-search-' . (function_exists('wp_rand') ? wp_rand(1000, 999999) : mt_rand(1000, 999999));
$has_posts = $query instanceof WP_Query && $query->have_posts();
$grid_classes = matrix_get_webinars_archive_card_grid_class_names();
$search_row_classes = matrix_get_webinars_archive_search_row_class_names();
$search_button_classes = matrix_get_webinars_archive_search_button_class_names();
$chip_scroll_classes = matrix_get_blog_filter_archive_horizontal_scroll_class_names();
$chip_group_classes = matrix_get_blog_filter_archive_horizontal_scroll_inner_class_names();
$chip_button_classes = matrix_get_blog_filter_archive_chip_button_class_names();

$format_date_label = static function ($raw_date) {
    $raw_date = trim((string) $raw_date);

    if ($raw_date === '') {
        return '';
    }

    if (preg_match('/^\d{8}$/', $raw_date) === 1) {
        $date = DateTime::createFromFormat('Ymd', $raw_date);

        if ($date instanceof DateTime) {
            return $date->format('d/m/y');
        }
    }

    $timestamp = strtotime($raw_date);

    if ($timestamp !== false) {
        return date_i18n('d/m/y', $timestamp);
    }

    return '';
};

$format_time_label = static function ($raw_time) {
    $raw_time = trim((string) $raw_time);

    if ($raw_time === '') {
        return '';
    }

    $timestamp = strtotime($raw_time);

    if ($timestamp !== false) {
        return strtolower(date_i18n('ga', $timestamp));
    }

    return '';
};
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
    <div class="py-12 lg:py-[100px] <?php echo esc_attr($wrapper_classes); ?>">
        <div
            x-data="{
                type: '<?php echo esc_js((string) $state['type']); ?>',
                isDragging: false,
                startX: 0,
                scrollStart: 0,
                moved: false,
                submitType(slug) {
                    this.type = slug;
                    this.$refs.typeInput.value = slug;
                    this.$refs.pageInput.value = 1;
                    this.$refs.form.submit();
                },
                onChipScrollPointerDown(event) {
                    if (event.pointerType === 'mouse' && event.button !== 0) {
                        return;
                    }

                    this.isDragging = true;
                    this.moved = false;
                    this.startX = event.clientX;
                    this.scrollStart = this.$refs.chipScroll.scrollLeft;
                    this.$refs.chipScroll.setPointerCapture?.(event.pointerId);
                },
                onChipScrollPointerMove(event) {
                    if (! this.isDragging) {
                        return;
                    }

                    const distance = event.clientX - this.startX;

                    if (Math.abs(distance) > 3) {
                        this.moved = true;
                    }

                    this.$refs.chipScroll.scrollLeft = this.scrollStart - distance;
                },
                onChipScrollPointerUp(event) {
                    if (! this.isDragging) {
                        return;
                    }

                    this.isDragging = false;
                    this.$refs.chipScroll.releasePointerCapture?.(event.pointerId);
                },
                onChipClick(event, slug) {
                    if (this.moved) {
                        event.preventDefault();
                        event.stopPropagation();
                        this.moved = false;
                        return;
                    }

                    this.submitType(slug);
                }
            }"
            class="w-full"
        >
            <form
                x-ref="form"
                method="get"
                action="<?php echo esc_url($base_url); ?>"
                class="flex flex-col gap-6"
                @submit="$refs.pageInput.value = 1"
            >
                <input type="hidden" name="webinar_type" x-ref="typeInput" :value="type" />
                <input type="hidden" name="webinar_page" x-ref="pageInput" value="<?php echo esc_attr((string) $current_page); ?>" />

                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div class="<?php echo esc_attr($search_row_classes); ?>">
                        <div class="flex-1">
                            <label for="<?php echo esc_attr($search_input_id); ?>" class="sr-only">
                                <?php echo esc_html($search_placeholder); ?>
                            </label>
                            <input
                                id="<?php echo esc_attr($search_input_id); ?>"
                                type="search"
                                name="webinar_search"
                                value="<?php echo esc_attr((string) $state['search']); ?>"
                                placeholder="<?php echo esc_attr($search_placeholder); ?>"
                                class="w-full rounded-[6px] border border-[#E2E8F0] px-3 py-2 font-primary text-[16px] leading-[24px] text-[#08284B] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#024B79]"
                            />
                        </div>

                        <button
                            type="submit"
                            class="<?php echo esc_attr($search_button_classes); ?>"
                        >
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true" class="shrink-0">
                        <path d="M6.99935 12.6667C9.94487 12.6667 12.3327 10.2789 12.3327 7.33333C12.3327 4.38781 9.94487 2 6.99935 2C4.05383 2 1.66602 4.38781 1.66602 7.33333C1.66602 10.2789 4.05383 12.6667 6.99935 12.6667Z" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M13.6656 14L10.7656 11.1" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                            <?php echo esc_html($search_button_label); ?>
                        </button>
                    </div>

                    <div class="flex min-w-0 flex-col gap-4 lg:flex-1 lg:flex-row lg:items-center lg:justify-end lg:gap-8">
                        <p class="shrink-0 font-primary text-[16px] font-medium leading-[28px] text-[#08284B]">
                            <?php echo esc_html($filter_label); ?>
                        </p>

                        <?php if ($chips !== []) { ?>
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
                                        $chip_slug = matrix_webinars_archive_sanitize_slug((string) ($chip['slug'] ?? ''));
                                        $chip_label = trim((string) ($chip['label'] ?? ''));

                                        if ($chip_slug === '' || $chip_label === '') {
                                            continue;
                                        }
                                        ?>
                                        <button
                                            type="button"
                                            class="<?php echo esc_attr($chip_button_classes); ?>"
                                            :aria-pressed="type === '<?php echo esc_js($chip_slug); ?>' ? 'true' : 'false'"
                                            :style="type === '<?php echo esc_js($chip_slug); ?>' ? 'border-color: #80CCD9; background-color: #80CCD9; color: #08284B;' : 'border-color: #08284B; background-color: #FFFFFF; color: #08284B;'"
                                            @click="onChipClick($event, '<?php echo esc_js($chip_slug); ?>')"
                                        >
                                            <?php echo esc_html($chip_label); ?>
                                        </button>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </form>

            <?php if ($has_posts) { ?>
                <div class="<?php echo esc_attr($grid_classes); ?>">
                    <?php while ($query->have_posts()) { ?>
                        <?php
                        $query->the_post();
                        $post_id = get_the_ID();
                        $title = get_the_title($post_id);
                        $card_link = matrix_get_blog_post_link_target($post_id, 'archive');
                        $terms = get_the_terms($post_id, 'webinar_type');
                        $display_term = (is_array($terms) && count($terms) === 1 && $terms[0] instanceof WP_Term) ? $terms[0] : null;
                        $type_slug = $display_term instanceof WP_Term ? $display_term->slug : 'all';
                        $theme = matrix_get_webinars_archive_card_theme($type_slug);
                        $summary = function_exists('get_field') ? trim(wp_strip_all_tags((string) get_field('webinar_summary', $post_id))) : '';
                        $date_label = $format_date_label(function_exists('get_field') ? get_field('webinar_date', $post_id) : '');
                        $time_label = $format_time_label(function_exists('get_field') ? get_field('webinar_time', $post_id) : '');

                        if ($summary === '') {
                            $summary = trim((string) get_the_excerpt($post_id));
                        }

                        if ($summary === '') {
                            $summary = wp_trim_words(wp_strip_all_tags((string) get_post_field('post_content', $post_id)), 28, '...');
                        }
                        ?>
                        <article class="h-full">
                            <a
                                href="<?php echo esc_url($card_link['url']); ?>"
                                <?php if ($card_link['target'] === '_blank') { ?>
                                    target="_blank"
                                    rel="<?php echo esc_attr($card_link['rel']); ?>"
                                <?php } ?>
                                class="flex h-full flex-col gap-4 rounded-[8px] p-6 shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
                                style="background-color: <?php echo esc_attr($theme['card_background']); ?>;"
                            >
                                <?php if ($display_term instanceof WP_Term) { ?>
                                    <span
                                        class="inline-flex h-[30px] w-fit items-center justify-center rounded-full px-4 text-[14px] font-medium leading-[24px] text-[#08284B]"
                                        style="background-color: <?php echo esc_attr($theme['badge_background']); ?>;"
                                    >
                                        <?php echo esc_html($display_term->name); ?>
                                    </span>
                                <?php } ?>

                                <h3 class="font-primary text-[20px] font-semibold leading-[24px] tracking-[-0.12px] text-[#1E244B]">
                                    <?php echo esc_html($title); ?>
                                    <span aria-hidden="true"> &rarr;</span>
                                </h3>

                                <?php if ($date_label !== '' || $time_label !== '') { ?>
                                    <div class="grid gap-1 text-[15px] font-semibold leading-[16px] tracking-[-0.09px] text-[#1E244B]">
                                        <?php if ($date_label !== '') { ?>
                                            <p>Date: <?php echo esc_html($date_label); ?></p>
                                        <?php } ?>

                                        <?php if ($time_label !== '') { ?>
                                            <p>Time: <?php echo esc_html($time_label); ?></p>
                                        <?php } ?>
                                    </div>
                                <?php } ?>

                                <?php if ($summary !== '') { ?>
                                    <p class="text-[14px] leading-[24px] text-[#1E244B]">
                                        <?php echo esc_html($summary); ?>
                                    </p>
                                <?php } ?>
                            </a>
                        </article>
                    <?php } ?>
                    <?php wp_reset_postdata(); ?>
                </div>
            <?php } else { ?>
                <p class="mt-8 text-[14px] leading-[24px] text-[#1E244B] lg:mt-10">
                    <?php echo esc_html($empty_state_message); ?>
                </p>
            <?php } ?>

            <?php
            get_template_part('template-parts/partials/archive-pagination', null, [
                'archive_pagination' => [
                    'current_page' => $current_page,
                    'total_pages' => $total_pages,
                    'aria_label' => 'Webinars archive pagination',
                    'variant' => 'pill',
                    'build_page_url' => static function (int $page) use ($base_url, $state): string {
                        return matrix_build_webinars_archive_page_url($base_url, $state, $page);
                    },
                ],
            ]);
            ?>
        </div>
    </div>
</section>
