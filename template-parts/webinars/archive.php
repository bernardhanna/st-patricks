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
    <div class="<?php echo esc_attr($wrapper_classes); ?>">
        <div
            x-data="{
                type: '<?php echo esc_js((string) $state['type']); ?>',
                submitType(slug) {
                    this.type = slug;
                    this.$refs.typeInput.value = slug;
                    this.$refs.pageInput.value = 1;
                    this.$refs.form.submit();
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
                    <div class="flex w-full max-w-[384px] flex-col gap-3 sm:flex-row">
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
                            class="btn inline-flex h-[40px] items-center justify-center gap-2 rounded-[6px] bg-[#08284B] px-4 text-[14px] font-medium leading-[24px] text-white sm:w-auto"
                        >
                            <?php echo esc_html($search_button_label); ?>
                        </button>
                    </div>

                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-end lg:gap-8">
                        <p class="font-primary text-[16px] font-medium leading-[28px] text-[#08284B]">
                            <?php echo esc_html($filter_label); ?>
                        </p>

                        <?php if ($chips !== []) { ?>
                            <div class="flex flex-wrap gap-3" role="group" aria-label="<?php echo esc_attr($filter_label); ?>">
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
                                        class="btn inline-flex min-h-[36px] items-center justify-center rounded-full border px-6 text-[14px] font-medium leading-[24px] transition-colors"
                                        :aria-pressed="type === '<?php echo esc_js($chip_slug); ?>' ? 'true' : 'false'"
                                        :style="type === '<?php echo esc_js($chip_slug); ?>' ? 'border-color: #80CCD9; background-color: #80CCD9; color: #08284B;' : 'border-color: #08284B; background-color: #FFFFFF; color: #08284B;'"
                                        @click="submitType('<?php echo esc_js($chip_slug); ?>')"
                                    >
                                        <?php echo esc_html($chip_label); ?>
                                    </button>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </form>

            <?php if ($has_posts) { ?>
                <div class="mt-8 grid grid-cols-1 gap-4 lg:mt-10 lg:grid-cols-2">
                    <?php while ($query->have_posts()) { ?>
                        <?php
                        $query->the_post();
                        $post_id = get_the_ID();
                        $title = get_the_title($post_id);
                        $permalink = get_permalink($post_id);
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
                        <article
                            class="flex h-full flex-col gap-4 rounded-[8px] p-6 shadow-sm"
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
                                <a
                                    href="<?php echo esc_url($permalink); ?>"
                                    class="focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
                                >
                                    <?php echo esc_html($title); ?>
                                    <span aria-hidden="true"> &rarr;</span>
                                </a>
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
                        </article>
                    <?php } ?>
                    <?php wp_reset_postdata(); ?>
                </div>
            <?php } else { ?>
                <p class="mt-8 text-[14px] leading-[24px] text-[#1E244B] lg:mt-10">
                    <?php echo esc_html($empty_state_message); ?>
                </p>
            <?php } ?>

            <?php if ($total_pages > 1) { ?>
                <nav class="mt-10 flex flex-wrap items-center justify-center gap-6" aria-label="Webinars archive pagination">
                    <?php if ($current_page > 1) { ?>
                        <a
                            href="<?php echo esc_url(matrix_build_webinars_archive_page_url($base_url, $state, $current_page - 1)); ?>"
                            class="btn inline-flex h-8 w-8 items-center justify-center rounded-full border border-[#C6ECF4] bg-white text-[#08284B]"
                            aria-label="Go to previous page"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                                <path d="M8.75 3.5L5.25 7L8.75 10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    <?php } ?>

                    <div class="flex flex-wrap items-center justify-center gap-2">
                        <?php for ($page = 1; $page <= $total_pages; $page++) { ?>
                            <?php if ($page === $current_page) { ?>
                                <span
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-[#024B79] text-[15px] font-semibold leading-[16px] tracking-[-0.09px] text-white"
                                    aria-current="page"
                                >
                                    <?php echo esc_html((string) $page); ?>
                                </span>
                            <?php } else { ?>
                                <a
                                    href="<?php echo esc_url(matrix_build_webinars_archive_page_url($base_url, $state, $page)); ?>"
                                    class="btn inline-flex h-8 w-8 items-center justify-center rounded-full border border-[#C6ECF4] bg-white text-[15px] font-semibold leading-[16px] tracking-[-0.09px] text-[#08284B]"
                                    aria-label="Go to page <?php echo esc_attr((string) $page); ?>"
                                >
                                    <?php echo esc_html((string) $page); ?>
                                </a>
                            <?php } ?>
                        <?php } ?>
                    </div>

                    <?php if ($current_page < $total_pages) { ?>
                        <a
                            href="<?php echo esc_url(matrix_build_webinars_archive_page_url($base_url, $state, $current_page + 1)); ?>"
                            class="btn inline-flex h-8 w-8 items-center justify-center rounded-full border border-[#C6ECF4] bg-white text-[#08284B]"
                            aria-label="Go to next page"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                                <path d="M5.25 3.5L8.75 7L5.25 10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    <?php } ?>
                </nav>
            <?php } ?>
        </div>
    </div>
</section>
