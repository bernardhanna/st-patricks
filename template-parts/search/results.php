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
$heading_prefix = trim((string) ($heading['prefix'] ?? 'Search Results'));
$heading_query = trim((string) ($heading['query'] ?? ''));
$search_input_id = 'search-results-query-' . (function_exists('wp_rand') ? wp_rand(1000, 999999) : mt_rand(1000, 999999));
$current_page = max(1, (int) ($pagination['current'] ?? 1));
$total_pages = max(1, (int) ($pagination['total'] ?? 1));
?>

<main class="w-full">
    <section class="w-full bg-[#C6ECF4]">
        <div class="mx-auto w-full max-w-[1280px]">
            <nav class="w-full bg-[#F1F8F9] px-5 py-3 lg:px-[70px]" aria-label="Breadcrumb">
                <ol class="flex flex-wrap items-center gap-3">
                    <li class="flex items-center gap-3">
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="font-primary text-[14px] font-semibold leading-[20px] text-[#08284B]">
                            Home
                        </a>
                        <span aria-hidden="true" class="text-[#08284B]">/</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="font-primary text-[14px] font-semibold leading-[20px] text-[#08284B]">Search</span>
                        <span aria-hidden="true" class="text-[#08284B]">/</span>
                    </li>
                    <li class="font-primary text-[14px] font-normal leading-[20px] text-[#08284B]" aria-current="page">
                        Results
                    </li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="bg-white">
        <div class="mx-auto flex w-full max-w-[1018px] flex-col gap-8 px-5 py-12 xl:px-0 xl:py-[100px]">
            <form action="<?php echo esc_url($base_url); ?>" method="get" class="flex flex-col gap-8">
                <div class="flex w-full max-w-[384px] flex-col gap-3 sm:flex-row">
                    <label for="<?php echo esc_attr($search_input_id); ?>" class="sr-only">Search site content</label>
                    <input
                        id="<?php echo esc_attr($search_input_id); ?>"
                        type="search"
                        name="s"
                        value="<?php echo esc_attr((string) ($state['query'] ?? '')); ?>"
                        placeholder="Search site content"
                        class="min-h-[40px] flex-1 rounded-[6px] border border-[#E2E8F0] px-3 py-2 font-primary text-[16px] leading-[24px] text-[#08284B] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#024B79]"
                    />
                    <input type="hidden" name="paged" value="1" />
                    <button
                        type="submit"
                        class="btn inline-flex h-[40px] items-center justify-center rounded-[6px] bg-[#08284B] px-4 text-[14px] font-medium leading-[24px] text-white"
                    >
                        Search
                    </button>
                </div>

                <h1 class="font-primary text-[36px] font-bold leading-[40px] tracking-[-0.432px] text-[#08284B] lg:text-[48px] lg:leading-[48px] lg:tracking-[-0.576px]">
                    <?php echo esc_html($heading_prefix); ?>
                    <?php if ($heading_query !== '') { ?>
                        <span class="font-normal italic"><?php echo esc_html(" '" . $heading_query . "'"); ?></span>
                    <?php } ?>
                </h1>

                <?php if ($has_results) { ?>
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-4">
                            <label for="search-results-sort" class="font-primary text-[16px] font-medium leading-[28px] text-[#08284B]">
                                Sort by:
                            </label>
                            <select
                                id="search-results-sort"
                                name="search_sort"
                                class="min-h-[40px] min-w-[213px] rounded-[6px] border border-[#E2E8F0] bg-white px-3 py-2 font-primary text-[16px] leading-[24px] text-[#08284B] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#024B79]"
                            >
                                <?php foreach ($sort_options as $option) { ?>
                                    <option value="<?php echo esc_attr((string) ($option['value'] ?? '')); ?>" <?php selected((string) ($state['sort'] ?? 'relevance'), (string) ($option['value'] ?? '')); ?>>
                                        <?php echo esc_html((string) ($option['label'] ?? '')); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-4">
                            <label for="search-results-filter" class="font-primary text-[16px] font-medium leading-[28px] text-[#08284B]">
                                Filter by:
                            </label>
                            <select
                                id="search-results-filter"
                                name="search_type"
                                class="min-h-[40px] min-w-[213px] rounded-[6px] border border-[#E2E8F0] bg-white px-3 py-2 font-primary text-[16px] leading-[24px] text-[#08284B] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#024B79]"
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
                <div class="flex flex-col gap-4">
                    <?php foreach ($items as $item) { ?>
                        <?php
                        $item_title = trim((string) ($item['title'] ?? ''));
                        $item_url = (string) ($item['url'] ?? '');
                        $item_image = (int) ($item['image'] ?? 0);
                        $item_image_alt = trim((string) ($item['image_alt'] ?? ''));
                        $item_type_label = trim((string) ($item['type_label'] ?? ''));
                        $item_date_label = trim((string) ($item['date_label'] ?? ''));
                        $item_excerpt = trim((string) ($item['excerpt'] ?? ''));

                        if ($item_title === '' || $item_url === '') {
                            continue;
                        }
                        ?>
                        <article class="flex flex-col gap-6 rounded-[8px] bg-[#FBFAF7] p-6 shadow-sm lg:flex-row lg:items-start">
                            <?php if ($item_image > 0) { ?>
                                <a
                                    href="<?php echo esc_url($item_url); ?>"
                                    class="block h-[186px] w-full overflow-hidden rounded-[6px] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79] lg:w-[280px] lg:flex-shrink-0"
                                >
                                    <?php
                                    echo wp_get_attachment_image($item_image, 'medium_large', false, [
                                        'class' => 'h-full w-full object-cover',
                                        'alt' => $item_image_alt,
                                    ]);
                                    ?>
                                </a>
                            <?php } ?>

                            <div class="flex min-w-0 flex-1 flex-col gap-4">
                                <?php if ($item_type_label !== '') { ?>
                                    <span class="inline-flex w-fit rounded-full bg-[#FADBD8] px-4 py-1 text-[14px] font-medium leading-[24px] text-[#08284B]">
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

                <?php if ($total_pages > 1) { ?>
                    <nav class="flex flex-wrap items-center justify-center gap-2" aria-label="Search results pagination">
                        <?php for ($page = 1; $page <= $total_pages; $page++) { ?>
                            <?php
                            $is_current_page = $page === $current_page;
                            $page_link_classes = 'flex h-8 w-8 items-center justify-center rounded-full border text-[14px] leading-[20px]';
                            $page_link_classes .= $is_current_page
                                ? ' border-[#024B79] bg-[#024B79] text-white'
                                : ' border-[#C6ECF4] text-[#08284B]';
                            ?>
                            <a
                                href="<?php echo esc_url(matrix_build_search_results_page_url($base_url, $state, $page)); ?>"
                                class="<?php echo esc_attr($page_link_classes); ?>"
                                <?php if ($is_current_page) { ?>aria-current="page"<?php } ?>
                            >
                                <?php echo esc_html((string) $page); ?>
                            </a>
                        <?php } ?>
                    </nav>
                <?php } ?>
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
</main>
