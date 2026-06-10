<?php

$defaults = matrix_get_careers_archive_defaults();
$careers_archive = is_array($args['careers_archive'] ?? null) ? $args['careers_archive'] : [];

if ($careers_archive === []) {
    return;
}

$normalize_text = static function ($value, $fallback = '') {
    $value = trim((string) $value);

    return $value !== '' ? $value : $fallback;
};

$heading = $normalize_text($careers_archive['heading'] ?? '', (string) ($defaults['heading'] ?? 'Current Vacancies'));
$heading_tag = (string) ($careers_archive['heading_tag'] ?? 'h2');
$filter_label = $normalize_text($careers_archive['filter_label'] ?? '', (string) ($defaults['filter_label'] ?? 'Filter by:'));
$department_placeholder = $normalize_text($careers_archive['department_placeholder'] ?? '', (string) ($defaults['department_placeholder'] ?? 'Department'));
$location_placeholder = $normalize_text($careers_archive['location_placeholder'] ?? '', (string) ($defaults['location_placeholder'] ?? 'Location'));
$apply_filters_label = $normalize_text($careers_archive['apply_filters_label'] ?? '', (string) ($defaults['apply_filters_label'] ?? 'Apply filters'));
$view_detail_label = $normalize_text($careers_archive['view_detail_label'] ?? '', (string) ($defaults['view_detail_label'] ?? 'View detail'));
$empty_state_message = $normalize_text($careers_archive['empty_state_message'] ?? '', (string) ($defaults['empty_state_message'] ?? 'No vacancies matched your filters.'));
$base_url = (string) ($careers_archive['base_url'] ?? home_url('/'));
$state = is_array($careers_archive['state'] ?? null) ? $careers_archive['state'] : [];
$department_options = is_array($careers_archive['department_options'] ?? null) ? $careers_archive['department_options'] : [];
$location_options = is_array($careers_archive['location_options'] ?? null) ? $careers_archive['location_options'] : [];
$pagination = is_array($careers_archive['pagination'] ?? null) ? $careers_archive['pagination'] : [];
$query = $careers_archive['query'] ?? null;
$section_id = $normalize_text($careers_archive['section_id'] ?? '');
$data_block = $normalize_text($careers_archive['data_block'] ?? '');
$section_classes = $normalize_text($careers_archive['section_classes'] ?? '', 'w-full');
$section_style = $normalize_text($careers_archive['section_style'] ?? '');
$wrapper_classes = $normalize_text($careers_archive['wrapper_classes'] ?? '', matrix_get_careers_archive_default_wrapper_classes());
$filter_select_classes = matrix_get_careers_archive_filter_select_class_names();
$apply_filters_button_classes = matrix_get_careers_archive_apply_filters_button_class_names();
$view_detail_button_classes = matrix_get_careers_archive_view_detail_button_class_names();
$select_chevron_style = "background-image: url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 16 16' fill='none'%3E%3Cpath d='M4 6L8 10L12 6' stroke='%2308284B' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E\");";

if (! in_array($heading_tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p'], true)) {
    $heading_tag = 'h2';
}

$state = array_merge([
    'department' => 'all',
    'location' => 'all',
    'search' => '',
    'paged' => 1,
], $state);

$current_page = max(1, (int) ($pagination['current'] ?? $state['paged']));
$total_pages = max(1, (int) ($pagination['total'] ?? (($query instanceof WP_Query) ? $query->max_num_pages : 1)));
$department_select_id = 'careers-archive-department-' . (function_exists('wp_rand') ? wp_rand(1000, 999999) : mt_rand(1000, 999999));
$location_select_id = 'careers-archive-location-' . (function_exists('wp_rand') ? wp_rand(1000, 999999) : mt_rand(1000, 999999));
$has_posts = $query instanceof WP_Query && $query->have_posts();
$heading_id = $section_id !== '' ? $section_id . '-heading' : 'careers-archive-heading';
$table_header_style = matrix_get_careers_archive_table_header_style();
$mobile_table_header_style = matrix_get_careers_archive_mobile_table_header_style();
$career_rows = [];

if ($has_posts) {
    while ($query->have_posts()) {
        $query->the_post();
        $career_rows[] = matrix_map_career_post_row(get_the_ID());
    }

    wp_reset_postdata();
}
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
    aria-labelledby="<?php echo esc_attr($heading_id); ?>"
>
    <div class="<?php echo esc_attr($wrapper_classes); ?>">
        <div class="flex w-full flex-col gap-8 lg:gap-16">
            <header class="flex w-full flex-col gap-8">
                <div class="flex w-full flex-col gap-8">
                    <<?php echo esc_attr($heading_tag); ?>
                        id="<?php echo esc_attr($heading_id); ?>"
                        class="font-primary text-[24px] font-semibold leading-[28px] tracking-[-0.18px] text-[#1E244B] lg:text-[30px] lg:leading-[36px] lg:tracking-[-0.225px]"
                    >
                        <?php echo esc_html($heading); ?>
                    </<?php echo esc_attr($heading_tag); ?>>

                    <div class="h-[4px] w-10 bg-[#6FC9C0]" aria-hidden="true"></div>
                </div>

                <form
                    method="get"
                    action="<?php echo esc_url($base_url); ?>"
                    class="flex w-full flex-col gap-4 lg:flex-row lg:items-center lg:gap-8"
                >
                    <input type="hidden" name="career_page" value="1" />

                    <?php if (trim((string) $state['search']) !== '') { ?>
                        <input type="hidden" name="career_search" value="<?php echo esc_attr((string) $state['search']); ?>" />
                    <?php } ?>

                    <p class="shrink-0 font-primary text-[16px] font-medium leading-[28px] text-[#08284B]">
                        <?php echo esc_html($filter_label); ?>
                    </p>

                    <div class="flex w-full flex-col gap-3 lg:flex-row lg:items-center lg:gap-2">
                        <label for="<?php echo esc_attr($department_select_id); ?>" class="sr-only">
                            <?php echo esc_html($department_placeholder); ?>
                        </label>
                        <select
                            id="<?php echo esc_attr($department_select_id); ?>"
                            name="career_department"
                            class="<?php echo esc_attr($filter_select_classes); ?> lg:flex-1"
                            style="<?php echo esc_attr($select_chevron_style); ?>"
                        >
                            <option value="all"><?php echo esc_html($department_placeholder); ?></option>
                            <?php foreach ($department_options as $option) { ?>
                                <?php
                                $slug = matrix_careers_archive_sanitize_slug((string) ($option['slug'] ?? ''));
                                $label = trim((string) ($option['label'] ?? ''));
                                if ($slug === '' || $label === '') {
                                    continue;
                                }
                                ?>
                                <option value="<?php echo esc_attr($slug); ?>" <?php selected((string) $state['department'], $slug); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php } ?>
                        </select>

                        <label for="<?php echo esc_attr($location_select_id); ?>" class="sr-only">
                            <?php echo esc_html($location_placeholder); ?>
                        </label>
                        <select
                            id="<?php echo esc_attr($location_select_id); ?>"
                            name="career_location"
                            class="<?php echo esc_attr($filter_select_classes); ?> lg:flex-1"
                            style="<?php echo esc_attr($select_chevron_style); ?>"
                        >
                            <option value="all"><?php echo esc_html($location_placeholder); ?></option>
                            <?php foreach ($location_options as $option) { ?>
                                <?php
                                $slug = matrix_careers_archive_sanitize_slug((string) ($option['slug'] ?? ''));
                                $label = trim((string) ($option['label'] ?? ''));
                                if ($slug === '' || $label === '') {
                                    continue;
                                }
                                ?>
                                <option value="<?php echo esc_attr($slug); ?>" <?php selected((string) $state['location'], $slug); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php } ?>
                        </select>

                        <button
                            type="submit"
                            class="<?php echo esc_attr($apply_filters_button_classes); ?>"
                        >
                            <?php echo esc_html($apply_filters_label); ?>
                        </button>
                    </div>
                </form>
            </header>

            <div class="flex w-full flex-col gap-[34px]">
                <?php if ($career_rows !== []) { ?>
                    <div class="w-full bg-white lg:hidden">
                        <div
                            class="h-3 w-full rounded-t-[4px]"
                            style="<?php echo esc_attr($mobile_table_header_style); ?>"
                            aria-hidden="true"
                        ></div>

                        <?php foreach ($career_rows as $row) { ?>
                            <article class="flex w-full flex-col gap-1 border border-t-0 border-[#E2E8F0] p-4">
                                <p class="flex flex-wrap items-center gap-1 font-primary text-[16px] text-[#08284B]">
                                    <span class="font-bold leading-[28px]">Position:</span>
                                    <span class="font-normal leading-[24px]"><?php echo esc_html($row['position']); ?></span>
                                </p>
                                <p class="flex flex-wrap items-center gap-1 font-primary text-[16px] text-[#08284B]">
                                    <span class="font-bold leading-[28px]">Area:</span>
                                    <span class="font-normal leading-[24px]"><?php echo esc_html($row['area']); ?></span>
                                </p>
                                <p class="flex flex-wrap items-center gap-1 font-primary text-[16px] text-[#08284B]">
                                    <span class="font-bold leading-[28px]">Location:</span>
                                    <span class="font-normal leading-[24px]"><?php echo esc_html($row['location']); ?></span>
                                </p>

                                <?php if ($row['permalink'] !== '') { ?>
                                    <div class="w-[160px] max-w-full pt-0">
                                        <a
                                            href="<?php echo esc_url($row['permalink']); ?>"
                                            class="<?php echo esc_attr($view_detail_button_classes); ?>"
                                        >
                                            <?php echo esc_html($view_detail_label); ?>
                                        </a>
                                    </div>
                                <?php } ?>
                            </article>
                        <?php } ?>
                    </div>

                    <div class="hidden w-full overflow-x-auto bg-white lg:block">
                        <table class="w-full min-w-[720px] table-fixed border-collapse">
                            <thead>
                                <tr class="rounded-t-[4px]" style="<?php echo esc_attr($table_header_style); ?>">
                                    <th scope="col" class="rounded-tl-[4px] px-8 py-3 text-left font-primary text-[16px] font-bold leading-[28px] text-[#08284B]">
                                        Position
                                    </th>
                                    <th scope="col" class="w-[180px] px-5 py-3 text-left font-primary text-[16px] font-bold leading-[28px] text-[#08284B]">
                                        Area
                                    </th>
                                    <th scope="col" class="w-[180px] px-5 py-3 text-left font-primary text-[16px] font-bold leading-[28px] text-[#08284B]">
                                        Location
                                    </th>
                                    <th scope="col" class="w-[160px] min-w-[160px] rounded-tr-[4px] py-3 pl-0 pr-8">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($career_rows as $row) { ?>
                                    <tr class="border border-t-0 border-[#E2E8F0]">
                                        <td class="px-8 py-4 pr-5 font-primary text-[16px] font-normal leading-[24px] text-[#08284B]">
                                            <?php echo esc_html($row['position']); ?>
                                        </td>
                                        <td class="w-[180px] px-5 py-4 font-primary text-[16px] font-normal leading-[24px] text-[#08284B]">
                                            <?php echo esc_html($row['area']); ?>
                                        </td>
                                        <td class="w-[180px] px-5 py-4 font-primary text-[16px] font-normal leading-[24px] text-[#08284B]">
                                            <?php echo esc_html($row['location']); ?>
                                        </td>
                                        <td class="w-[160px] min-w-[160px] py-4 pl-0 pr-8">
                                            <?php if ($row['permalink'] !== '') { ?>
                                                <a
                                                    href="<?php echo esc_url($row['permalink']); ?>"
                                                    class="<?php echo esc_attr($view_detail_button_classes); ?>"
                                                >
                                                    <?php echo esc_html($view_detail_label); ?>
                                                </a>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <p class="font-primary text-[16px] font-medium leading-[28px] text-[#08284B]">
                        <?php echo esc_html($empty_state_message); ?>
                    </p>
                <?php } ?>

                <?php if ($total_pages > 1) { ?>
                    <nav class="flex flex-wrap items-center justify-center gap-6" aria-label="Careers archive pagination">
                        <?php if ($current_page > 1) { ?>
                            <a
                                href="<?php echo esc_url(matrix_build_careers_archive_page_url($base_url, $state, $current_page - 1)); ?>"
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
                                        href="<?php echo esc_url(matrix_build_careers_archive_page_url($base_url, $state, $page)); ?>"
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
                                href="<?php echo esc_url(matrix_build_careers_archive_page_url($base_url, $state, $current_page + 1)); ?>"
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
    </div>
</section>
