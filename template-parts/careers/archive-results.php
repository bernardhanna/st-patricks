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

$view_detail_label = $normalize_text($careers_archive['view_detail_label'] ?? '', (string) ($defaults['view_detail_label'] ?? 'View detail'));
$empty_state_message = $normalize_text($careers_archive['empty_state_message'] ?? '', (string) ($defaults['empty_state_message'] ?? 'No vacancies matched your filters.'));
$base_url = (string) ($careers_archive['base_url'] ?? home_url('/'));
$state = is_array($careers_archive['state'] ?? null) ? $careers_archive['state'] : [];
$pagination = is_array($careers_archive['pagination'] ?? null) ? $careers_archive['pagination'] : [];
$query = $careers_archive['query'] ?? null;
$view_detail_button_classes = matrix_get_careers_archive_view_detail_button_class_names();
$table_header_style = matrix_get_careers_archive_table_header_style();
$mobile_table_header_style = matrix_get_careers_archive_mobile_table_header_style();

$state = array_merge([
    'department' => 'all',
    'location' => 'all',
    'search' => '',
    'paged' => 1,
], $state);

$current_page = max(1, (int) ($pagination['current'] ?? $state['paged']));
$total_pages = max(1, (int) ($pagination['total'] ?? (($query instanceof WP_Query) ? $query->max_num_pages : 1)));
$has_posts = $query instanceof WP_Query && $query->have_posts();
$career_rows = [];

if ($has_posts) {
    while ($query->have_posts()) {
        $query->the_post();
        $career_rows[] = matrix_map_career_post_row(get_the_ID());
    }

    wp_reset_postdata();
}
?>

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

<?php
get_template_part('template-parts/partials/archive-pagination', null, [
    'archive_pagination' => [
        'current_page' => $current_page,
        'total_pages' => $total_pages,
        'aria_label' => 'Careers archive pagination',
        'variant' => 'pill',
        'build_page_url' => static function (int $page) use ($base_url, $state): string {
            return matrix_build_careers_archive_page_url($base_url, $state, $page);
        },
        'link_attributes_callback' => static function (int $page): array {
            return [
                'data-care-page' => (string) $page,
            ];
        },
    ],
]);
?>
