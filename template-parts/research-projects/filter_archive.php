<?php

$defaults = matrix_get_research_project_archive_defaults();
$research_project_archive = is_array($args['research_project_archive'] ?? null) ? $args['research_project_archive'] : [];

if ($research_project_archive === []) {
    return;
}

$default_heading = (string) ($defaults['heading'] ?? 'Research Projects');
$default_filter_label = (string) ($defaults['filter_label'] ?? 'Filter by:');
$default_researcher_filter_label = (string) ($defaults['researcher_filter_label'] ?? 'Filter by profile');
$default_search_placeholder = (string) ($defaults['search_placeholder'] ?? 'Search research projects');
$default_search_button_label = (string) ($defaults['search_button_label'] ?? 'Search');
$default_empty_state_message = (string) ($defaults['empty_state_message'] ?? 'No research projects matched your filters.');
$default_posts_per_page = (int) ($defaults['posts_per_page'] ?? 12);
$heading_tag = (string) ($research_project_archive['heading_tag'] ?? 'h2');
$heading = trim((string) ($research_project_archive['heading'] ?? ''));
$filter_label = trim((string) ($research_project_archive['filter_label'] ?? $default_filter_label));
$researcher_filter_label = trim((string) ($research_project_archive['researcher_filter_label'] ?? $default_researcher_filter_label));
$search_placeholder = trim((string) ($research_project_archive['search_placeholder'] ?? $default_search_placeholder));
$search_button_label = trim((string) ($research_project_archive['search_button_label'] ?? $default_search_button_label));
$empty_state_message = trim((string) ($research_project_archive['empty_state_message'] ?? $default_empty_state_message));
$base_url = (string) ($research_project_archive['base_url'] ?? home_url('/'));
$state = is_array($research_project_archive['state'] ?? null) ? $research_project_archive['state'] : [];
$chips = is_array($research_project_archive['chips'] ?? null) ? $research_project_archive['chips'] : [];
$researcher_options = is_array($research_project_archive['researcher_options'] ?? null) ? $research_project_archive['researcher_options'] : [];
$lock_category = (bool) ($research_project_archive['lock_category'] ?? false);
$pagination = is_array($research_project_archive['pagination'] ?? null) ? $research_project_archive['pagination'] : [];
$colors = is_array($research_project_archive['colors'] ?? null) ? $research_project_archive['colors'] : [];
$section_id = trim((string) ($research_project_archive['section_id'] ?? ''));
$data_block = trim((string) ($research_project_archive['data_block'] ?? ''));
$section_classes = trim((string) ($research_project_archive['section_classes'] ?? 'w-full'));
$section_style = trim((string) ($research_project_archive['section_style'] ?? ''));
$wrapper_classes = trim((string) ($research_project_archive['wrapper_classes'] ?? ''));
$query = $research_project_archive['query'] ?? null;

if (! in_array($heading_tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p'], true)) {
    $heading_tag = 'h2';
}

if ($heading === '') {
    $heading = $default_heading;
}

if ($wrapper_classes === '') {
    $wrapper_classes = 'flex w-full max-w-[1018px] flex-col items-center mx-auto pt-5 pb-5 max-xl:px-5';
}

$state = array_merge([
    'category' => 'all',
    'researcher' => 'all',
    'search' => '',
    'paged' => 1,
    'posts_per_page' => $default_posts_per_page,
], $state);

$colors = array_merge([
    'background' => '#FFFFFF',
    'filter_label' => '#08284B',
    'chip_text' => '#08284B',
    'chip_border' => '#08284B',
    'active_chip_background' => '#80CCD9',
    'active_chip_text' => '#08284B',
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
$controls_classes = 'flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between';
$chip_group_classes = 'flex flex-wrap gap-3';
$grid_classes = 'mt-8 grid grid-cols-1 gap-6 md:grid-cols-2 xl:mt-10 xl:grid-cols-3 xl:gap-8';
$pagination_classes = 'mt-10 flex flex-wrap items-center justify-center gap-2';
$search_input_id = 'research-project-archive-search-' . wp_rand(1000, 999999);
$researcher_select_id = 'research-project-archive-researcher-' . wp_rand(1000, 999999);
$has_posts = $query instanceof WP_Query && $query->have_posts();
$show_category_chips = ! $lock_category && count($chips) > 1;
$archive_root_url = untrailingslashit(matrix_resolve_research_project_archive_base_url());
$uses_path_category_urls = matrix_is_research_project_main_archive_url($base_url);
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
                researcher: '<?php echo esc_js((string) $state['researcher']); ?>',
                formAction: '<?php echo esc_js(untrailingslashit($base_url)); ?>',
                archiveRoot: '<?php echo esc_js($archive_root_url); ?>',
                usePathCategory: <?php echo $uses_path_category_urls ? 'true' : 'false'; ?>,
                buildUrl(page = 1) {
                    const params = new URLSearchParams();
                    const search = (this.$refs.searchInput?.value || '').trim();

                    if (this.researcher !== 'all') {
                        params.set('research_researcher', this.researcher);
                    }

                    if (search !== '') {
                        params.set('research_search', search);
                    }

                    if (this.usePathCategory) {
                        let url = this.archiveRoot;

                        if (this.category !== 'all') {
                            url += '/' + this.category;
                        }

                        url = url.replace(/\/$/, '') + '/';

                        if (page > 1) {
                            url += 'page/' + page + '/';
                        }

                        const query = params.toString();

                        return query !== '' ? `${url}?${query}` : url;
                    }

                    if (this.category !== 'all') {
                        params.set('research_category', this.category);
                    }

                    if (page > 1) {
                        params.set('research_page', String(page));
                    }

                    const query = params.toString();

                    return query !== '' ? `${this.formAction}/?${query}` : `${this.formAction}/`;
                },
                redirectWithFilters(page = 1) {
                    window.location.href = this.buildUrl(page);
                },
                submitCategory(slug) {
                    this.category = slug;
                    this.redirectWithFilters(1);
                },
                submitResearcher(slug) {
                    this.researcher = slug;
                    this.redirectWithFilters(1);
                }
            }"
            class="w-full"
        >
            <<?php echo esc_attr($heading_tag); ?>
                class="font-primary text-[24px] font-semibold leading-[28px] tracking-[-0.18px] lg:text-[30px] lg:leading-[36px] lg:tracking-[-0.225px]"
                style="color: <?php echo esc_attr($colors['card_title']); ?>;"
            >
                <?php echo esc_html($heading); ?>
            </<?php echo esc_attr($heading_tag); ?>>

            <div class="mt-6 h-[4px] w-10 bg-[#6FC9C0]" aria-hidden="true"></div>

            <div class="mt-8 <?php echo esc_attr($controls_classes); ?>">
                <div class="flex flex-col gap-6 lg:max-w-[640px]">
                    <?php if ($show_category_chips) { ?>
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:gap-6">
                            <p
                                class="font-primary text-[16px] font-semibold leading-[24px]"
                                style="color: <?php echo esc_attr($colors['filter_label']); ?>;"
                            >
                                <?php echo esc_html($filter_label); ?>
                            </p>

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
                                        class="btn inline-flex w-fit whitespace-nowrap items-center justify-center rounded-full border px-5 py-3 text-[14px] font-semibold leading-none transition-colors"
                                        :aria-pressed="category === '<?php echo esc_js($chip_slug); ?>' ? 'true' : 'false'"
                                        :style="category === '<?php echo esc_js($chip_slug); ?>' ? 'border-color: <?php echo esc_js($colors['active_chip_background']); ?>; background-color: <?php echo esc_js($colors['active_chip_background']); ?>; color: <?php echo esc_js($colors['active_chip_text']); ?>;' : 'border-color: <?php echo esc_js($colors['chip_border']); ?>; background-color: transparent; color: <?php echo esc_js($colors['chip_text']); ?>;'"
                                        @click="submitCategory('<?php echo esc_js($chip_slug); ?>')"
                                    >
                                        <?php echo esc_html($chip_label); ?>
                                    </button>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>

                    <?php if (count($researcher_options) > 1) { ?>
                        <div class="flex flex-col gap-2">
                            <label
                                for="<?php echo esc_attr($researcher_select_id); ?>"
                                class="font-primary text-[14px] font-medium leading-[20px]"
                                style="color: <?php echo esc_attr($colors['filter_label']); ?>;"
                            >
                                <?php echo esc_html($researcher_filter_label); ?>
                            </label>
                            <select
                                id="<?php echo esc_attr($researcher_select_id); ?>"
                                class="w-full max-w-[420px] rounded-[6px] border px-4 py-3 font-primary text-[16px] leading-[24px] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#024B79]"
                                style="color: <?php echo esc_attr($colors['search_input_text']); ?>; border-color: <?php echo esc_attr($colors['search_input_border']); ?>;"
                                @change="submitResearcher($event.target.value)"
                            >
                                <?php foreach ($researcher_options as $option) { ?>
                                    <?php
                                    $option_slug = sanitize_title((string) ($option['slug'] ?? ''));
                                    $option_label = trim((string) ($option['label'] ?? ''));

                                    if ($option_slug === '' || $option_label === '') {
                                        continue;
                                    }
                                    ?>
                                    <option
                                        value="<?php echo esc_attr($option_slug); ?>"
                                        <?php selected((string) $state['researcher'], $option_slug); ?>
                                    >
                                        <?php echo esc_html($option_label); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    <?php } ?>
                </div>

                <form
                    x-ref="form"
                    method="get"
                    action="<?php echo esc_url($base_url); ?>"
                    class="flex w-full max-w-[420px] flex-col gap-3 sm:flex-row"
                    @submit.prevent="redirectWithFilters(1)"
                >
                    <div class="flex-1">
                        <label for="<?php echo esc_attr($search_input_id); ?>" class="sr-only">
                            <?php echo esc_html($search_placeholder); ?>
                        </label>
                        <input
                            x-ref="searchInput"
                            id="<?php echo esc_attr($search_input_id); ?>"
                            type="search"
                            value="<?php echo esc_attr((string) $state['search']); ?>"
                            placeholder="<?php echo esc_attr($search_placeholder); ?>"
                            class="w-full rounded-[6px] border px-4 py-4 font-primary text-[16px] leading-[24px] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#024B79]"
                            style="color: <?php echo esc_attr($colors['search_input_text']); ?>; border-color: <?php echo esc_attr($colors['search_input_border']); ?>;"
                        />
                    </div>

                    <button
                        type="submit"
                        class="btn inline-flex min-h-[56px] w-full items-center justify-center rounded-[6px] px-6 py-4 text-[16px] font-semibold leading-none sm:w-auto"
                        style="background-color: <?php echo esc_attr($colors['search_button_background']); ?>; color: <?php echo esc_attr($colors['search_button_text']); ?>;"
                    >
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
                        $permalink = get_permalink($post_id);
                        $thumbnail_id = get_post_thumbnail_id($post_id);
                        $primary_category_name = matrix_get_research_project_primary_category_name($post_id);
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
                                href="<?php echo esc_url($permalink); ?>"
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
                                    <div class="mb-4">
                                        <span
                                            class="inline-flex w-fit rounded-full border px-3 py-1 font-primary text-[12px] font-semibold uppercase tracking-[0.06em]"
                                            style="border-color: <?php echo esc_attr($colors['chip_border']); ?>; color: <?php echo esc_attr($colors['card_meta']); ?>;"
                                        >
                                            <?php echo esc_html($primary_category_name); ?>
                                        </span>
                                    </div>
                                <?php } ?>

                                <h3 class="font-primary text-[24px] font-semibold leading-[30px] tracking-[-0.18px]">
                                    <a
                                        href="<?php echo esc_url($permalink); ?>"
                                        class="focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
                                        style="color: <?php echo esc_attr($colors['card_title']); ?>;"
                                    >
                                        <?php echo esc_html($title); ?>
                                    </a>
                                </h3>

                                <p
                                    class="mt-3 font-primary text-[14px] font-medium leading-[20px]"
                                    style="color: <?php echo esc_attr($colors['card_meta']); ?>;"
                                >
                                    <time datetime="<?php echo esc_attr(get_the_date('c', $post_id)); ?>">
                                        <?php echo esc_html(get_the_date('j F Y', $post_id)); ?>
                                    </time>
                                </p>

                                <?php if ($excerpt !== '') { ?>
                                    <p
                                        class="mt-4 font-primary text-[16px] leading-[28px]"
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

            <?php if ($total_pages > 1) { ?>
                <nav class="<?php echo esc_attr($pagination_classes); ?>" aria-label="Archive pagination">
                    <?php if ($current_page > 1) { ?>
                        <a
                            href="<?php echo esc_url(matrix_build_research_project_archive_page_url($base_url, $state, $current_page - 1)); ?>"
                            class="inline-flex justify-center items-center w-11 h-11 rounded-full border btn"
                            style="border-color: <?php echo esc_attr($colors['chip_border']); ?>; color: <?php echo esc_attr($colors['chip_text']); ?>;"
                            aria-label="Go to previous page"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                                <path d="M8.75 3.5L5.25 7L8.75 10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    <?php } ?>

                    <?php for ($page = 1; $page <= $total_pages; $page++) { ?>
                        <?php if ($page === $current_page) { ?>
                            <span
                                class="inline-flex h-11 w-11 items-center justify-center rounded-full border font-primary text-[14px] font-semibold"
                                style="border-color: <?php echo esc_attr($colors['active_chip_background']); ?>; background-color: <?php echo esc_attr($colors['active_chip_background']); ?>; color: <?php echo esc_attr($colors['active_chip_text']); ?>;"
                                aria-current="page"
                            >
                                <?php echo esc_html((string) $page); ?>
                            </span>
                        <?php } else { ?>
                            <a
                                href="<?php echo esc_url(matrix_build_research_project_archive_page_url($base_url, $state, $page)); ?>"
                                class="btn inline-flex h-11 w-11 items-center justify-center rounded-full border font-primary text-[14px] font-semibold"
                                style="border-color: <?php echo esc_attr($colors['chip_border']); ?>; color: <?php echo esc_attr($colors['chip_text']); ?>;"
                                aria-label="Go to page <?php echo esc_attr((string) $page); ?>"
                            >
                                <?php echo esc_html((string) $page); ?>
                            </a>
                        <?php } ?>
                    <?php } ?>

                    <?php if ($current_page < $total_pages) { ?>
                        <a
                            href="<?php echo esc_url(matrix_build_research_project_archive_page_url($base_url, $state, $current_page + 1)); ?>"
                            class="inline-flex justify-center items-center w-11 h-11 rounded-full border btn"
                            style="border-color: <?php echo esc_attr($colors['chip_border']); ?>; color: <?php echo esc_attr($colors['chip_text']); ?>;"
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
