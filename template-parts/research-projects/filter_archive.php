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
$show_heading = array_key_exists('show_heading', $research_project_archive)
    ? (bool) $research_project_archive['show_heading']
    : true;

if (! in_array($heading_tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p'], true)) {
    $heading_tag = 'h2';
}

if ($heading === '' && $show_heading) {
    $heading = $default_heading;
}

if ($wrapper_classes === '') {
    $wrapper_classes = 'flex w-full max-w-[1018px] flex-col items-center mx-auto py-12 lg:py-[100px] max-xl:px-5';
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
$controls_classes = matrix_get_filter_archive_controls_class_names('start');
$chip_group_classes = 'flex flex-wrap gap-3';
$grid_classes = matrix_get_filter_archive_card_grid_class_names();
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
                <div class="flex flex-col gap-6 lg:max-w-[640px]">
                    <?php if ($show_category_chips) { ?>
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:gap-6">
                            <p
                                class="font-primary text-[16px] font-medium leading-[28px]"
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
                                        class="<?php echo esc_attr(matrix_get_research_project_archive_chip_button_class_names()); ?>"
                                        :aria-pressed="category === '<?php echo esc_js($chip_slug); ?>' ? 'true' : 'false'"
                                        :style="category === '<?php echo esc_js($chip_slug); ?>' ? 'border-color: <?php echo esc_js($colors['active_chip_background']); ?>; background-color: <?php echo esc_js($colors['active_chip_background']); ?>; color: <?php echo esc_js($colors['active_chip_text']); ?>;' : 'border-color: <?php echo esc_js($colors['chip_border']); ?>; background-color: <?php echo esc_js($colors['inactive_chip_background']); ?>; color: <?php echo esc_js($colors['chip_text']); ?>;'"
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
                    class="flex w-full max-w-[384px] items-center gap-2"
                    @submit.prevent="redirectWithFilters(1)"
                >
                    <div class="min-w-0 flex-1">
                        <label for="<?php echo esc_attr($search_input_id); ?>" class="sr-only">
                            <?php echo esc_html($search_placeholder); ?>
                        </label>
                        <input
                            x-ref="searchInput"
                            id="<?php echo esc_attr($search_input_id); ?>"
                            type="search"
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
                        $permalink = get_permalink($post_id);
                        $thumbnail_id = get_post_thumbnail_id($post_id);
                        $primary_category_name = matrix_get_research_project_primary_category_name($post_id);
                        $primary_category_slug = '';
                        $project_categories = get_the_terms($post_id, 'research_project_category');
                        if (is_array($project_categories)) {
                            foreach ($project_categories as $project_category) {
                                if ($project_category instanceof WP_Term) {
                                    $primary_category_slug = $project_category->slug;
                                    break;
                                }
                            }
                        }
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
                                    <?php
                                    $category_badge_colors = matrix_get_research_project_category_badge_colors(
                                        $primary_category_name,
                                        $primary_category_slug
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
                                        href="<?php echo esc_url($permalink); ?>"
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
                        return matrix_build_research_project_archive_page_url($base_url, $state, $page);
                    },
                ],
            ]);
            ?>
        </div>
    </div>
</section>
