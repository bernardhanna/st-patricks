<?php
$search_results = is_array($args['search_results'] ?? null) ? $args['search_results'] : [];
$state = is_array($search_results['state'] ?? null) ? $search_results['state'] : [];
$items = is_array($search_results['items'] ?? null) ? $search_results['items'] : [];
$heading = is_array($search_results['heading'] ?? null) ? $search_results['heading'] : [
    'prefix' => 'Search Results',
    'query' => '',
];
$base_url = (string) ($search_results['base_url'] ?? home_url('/'));
$type_options = is_array($search_results['type_options'] ?? null) ? $search_results['type_options'] : [];
$sort_options = is_array($search_results['sort_options'] ?? null) ? $search_results['sort_options'] : [];
$pagination = is_array($search_results['pagination'] ?? null) ? $search_results['pagination'] : [];
$has_results = ! empty($search_results['has_results']) || $items !== [];
$search_input_id = 'search-results-query-' . (function_exists('wp_rand') ? wp_rand(1000, 999999) : mt_rand(1000, 999999));
$current_page = max(1, (int) ($pagination['current'] ?? 1));
$total_pages = max(1, (int) ($pagination['total'] ?? 1));
?>

<main class="w-full">
    <section class="w-full bg-[#F1F8F9]">
        <div class="mx-auto w-full max-w-[1280px]">
            <?php
            get_template_part('template-parts/partials/hero-breadcrumbs-nav', null, [
                'items' => [
                    [
                        'title' => 'Home',
                        'url' => home_url('/'),
                        'target' => '',
                    ],
                    [
                        'title' => 'Search',
                        'url' => $base_url,
                        'target' => '',
                    ],
                ],
                'current_label' => 'Results',
                'background_color' => '#F1F8F9',
            ]);
            ?>
        </div>
    </section>

    <section class="overflow-x-clip bg-white">
        <div class="mx-auto flex w-full min-w-0 max-w-[1018px] flex-col gap-8 px-5 py-12 xl:px-0 xl:py-[100px]">
            <form
                action="<?php echo esc_url($base_url); ?>"
                method="get"
                class="flex w-full min-w-0 flex-col gap-8"
                data-matrix-search-results-form
            >
                <?php
                get_template_part('template-parts/partials/search-results-intro', null, [
                    'search_base_url' => $base_url,
                    'search_query' => (string) ($state['query'] ?? ''),
                    'heading' => $heading,
                    'search_input_id' => $search_input_id,
                    'render_form_tag' => false,
                ]);
                ?>
                <input type="hidden" name="paged" value="1" data-matrix-search-results-paged />

                <?php if ($has_results) { ?>
                    <div class="flex w-full min-w-0 flex-col gap-4 lg:flex-row lg:items-center lg:justify-between lg:gap-8">
                        <div class="flex w-full min-w-0 flex-col gap-2 lg:flex-row lg:items-center lg:gap-4">
                            <label for="search-results-sort" class="shrink-0 font-primary text-[16px] font-medium leading-[28px] text-[#08284B]">
                                Sort by:
                            </label>
                            <select
                                id="search-results-sort"
                                name="search_sort"
                                class="min-h-[40px] w-full min-w-0 max-w-full rounded-[6px] border border-[#E2E8F0] bg-white px-3 py-2 font-primary text-[16px] leading-[24px] text-[#08284B] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#024B79] lg:w-[213px] lg:max-w-none"
                                data-matrix-search-results-filter
                            >
                                <?php foreach ($sort_options as $option) { ?>
                                    <option value="<?php echo esc_attr((string) ($option['value'] ?? '')); ?>" <?php selected((string) ($state['sort'] ?? 'relevance'), (string) ($option['value'] ?? '')); ?>>
                                        <?php echo esc_html((string) ($option['label'] ?? '')); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="flex w-full min-w-0 flex-col gap-2 lg:flex-row lg:items-center lg:gap-4 lg:justify-end">
                            <label for="search-results-filter" class="shrink-0 font-primary text-[16px] font-medium leading-[28px] text-[#08284B]">
                                Filter by:
                            </label>
                            <select
                                id="search-results-filter"
                                name="search_type"
                                class="min-h-[40px] w-full min-w-0 max-w-full rounded-[6px] border border-[#E2E8F0] bg-white px-3 py-2 font-primary text-[16px] leading-[24px] text-[#08284B] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#024B79] lg:w-[213px] lg:max-w-none"
                                data-matrix-search-results-filter
                            >
                                <?php foreach ($type_options as $option) { ?>
                                    <option value="<?php echo esc_attr((string) ($option['value'] ?? '')); ?>" <?php selected((string) ($state['type'] ?? 'all'), (string) ($option['value'] ?? '')); ?>>
                                        <?php echo esc_html((string) ($option['label'] ?? '')); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                <?php } ?>
            </form>

            <?php if ($has_results) { ?>
                <div class="<?php echo esc_attr(matrix_get_search_results_cards_layout_class_names()); ?>">
                    <?php foreach ($items as $item) { ?>
                        <?php
                        $item_title = trim((string) ($item['title'] ?? ''));
                        $item_url = (string) ($item['url'] ?? '');
                        $item_image = (int) ($item['image'] ?? 0);
                        $item_image_alt = trim((string) ($item['image_alt'] ?? ''));
                        $item_type_key = trim((string) ($item['type_key'] ?? ''));
                        $item_type_label = trim((string) ($item['type_label'] ?? ''));
                        $item_type_badge_colors = matrix_get_search_results_type_badge_colors($item_type_key);
                        $item_date_label = trim((string) ($item['date_label'] ?? ''));
                        $item_excerpt = trim((string) ($item['excerpt'] ?? ''));

                        if ($item_title === '' || $item_url === '') {
                            continue;
                        }
                        ?>
                        <article class="<?php echo esc_attr(matrix_get_search_results_card_class_names()); ?>">
                            <?php if ($item_image > 0) { ?>
                                <a
                                    href="<?php echo esc_url($item_url); ?>"
                                    class="<?php echo esc_attr(matrix_get_search_results_card_image_class_names()); ?>"
                                >
                                    <?php
                                    echo wp_get_attachment_image($item_image, 'medium_large', false, [
                                        'class' => 'h-full w-full object-cover',
                                        'alt' => $item_image_alt,
                                    ]);
                                    ?>
                                </a>
                            <?php } ?>

                            <div class="flex flex-col flex-1 gap-4 min-w-0">
                                <?php if ($item_type_label !== '') { ?>
                                    <span
                                        class="inline-flex w-fit rounded-full px-4 py-1 text-[14px] font-medium leading-[24px]"
                                        style="background-color: <?php echo esc_attr($item_type_badge_colors['background']); ?>; color: <?php echo esc_attr($item_type_badge_colors['text']); ?>;"
                                    >
                                        <?php echo esc_html($item_type_label); ?>
                                    </span>
                                <?php } ?>

                                <h2 class="font-primary text-[20px] font-semibold leading-[24px] tracking-[-0.12px] text-[#1E244B]">
                                    <a
                                        href="<?php echo esc_url($item_url); ?>"
                                        class="focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
                                    >
                                        <?php echo esc_html($item_title); ?>
                                    </a>
                                </h2>

                                <?php if ($item_date_label !== '') { ?>
                                    <p class="text-[15px] font-semibold leading-[16px] tracking-[-0.09px] text-[#1E244B]">
                                        Date: <?php echo esc_html($item_date_label); ?>
                                    </p>
                                <?php } ?>

                                <?php if ($item_excerpt !== '') { ?>
                                    <p class="text-[14px] leading-[24px] text-[#1E244B]">
                                        <?php echo esc_html($item_excerpt); ?>
                                    </p>
                                <?php } ?>
                            </div>
                        </article>
                    <?php } ?>
                </div>

                <?php
                get_template_part('template-parts/partials/archive-pagination', null, [
                    'archive_pagination' => [
                        'current_page' => $current_page,
                        'total_pages' => $total_pages,
                        'aria_label' => 'Search results pagination',
                        'variant' => 'pill',
                        'build_page_url' => static function (int $page) use ($base_url, $state): string {
                            return matrix_build_search_results_page_url($base_url, $state, $page);
                        },
                    ],
                ]);
                ?>
            <?php } else { ?>
                <div class="flex flex-col gap-6">
                    <a
                        href="<?php echo esc_url(home_url('/')); ?>"
                        class="btn inline-flex w-fit items-center justify-center rounded-[6px] bg-[#024B79] px-4 py-2 text-[14px] font-medium leading-[24px] text-white"
                    >
                        Go back to Home page
                    </a>
                </div>
            <?php } ?>
        </div>
    </section>

    <?php
    if (! $has_results) {
        $useful_links = is_array($search_results['useful_links'] ?? null) ? $search_results['useful_links'] : null;

        if (is_array($useful_links)) {
            echo matrix_render_useful_links_section($useful_links); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
    }
    ?>
    <script>
    (function () {
        var form = document.querySelector('[data-matrix-search-results-form]');

        if (!form) {
            return;
        }

        form.querySelectorAll('[data-matrix-search-results-filter]').forEach(function (control) {
            control.addEventListener('change', function () {
                var paged = form.querySelector('[data-matrix-search-results-paged]');

                if (paged) {
                    paged.value = '1';
                }

                HTMLFormElement.prototype.submit.call(form);
            });
        });
    })();
    </script>
</main>
