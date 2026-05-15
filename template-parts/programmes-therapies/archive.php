<?php

$archive = is_array($args['programmes_therapies_archive'] ?? null) ? $args['programmes_therapies_archive'] : [];

if ($archive === []) {
    return;
}

$heading = (string) ($archive['heading'] ?? 'Select a programme or therapy');
$heading_tag = (string) ($archive['heading_tag'] ?? 'h2');
$empty_state_message = (string) ($archive['empty_state_message'] ?? 'No programmes or therapies matched your filters.');
$base_url = (string) ($archive['base_url'] ?? home_url('/'));
$state = is_array($archive['state'] ?? null) ? $archive['state'] : [];
$filters = is_array($archive['filters'] ?? null) ? $archive['filters'] : [];
$pagination = is_array($archive['pagination'] ?? null) ? $archive['pagination'] : [];
$query = $archive['query'] ?? null;
$section_id = trim((string) ($archive['section_id'] ?? ''));
$data_block = trim((string) ($archive['data_block'] ?? ''));
$section_classes = trim((string) ($archive['section_classes'] ?? 'w-full'));
$wrapper_classes = trim((string) ($archive['wrapper_classes'] ?? ''));

if (! in_array($heading_tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p'], true)) {
    $heading_tag = 'h2';
}

$state = array_merge([
    'type' => 'all',
    'care' => 'all',
    'delivery' => 'all',
    'paged' => 1,
], $state);

$type_options = is_array($filters['type_options'] ?? null) ? $filters['type_options'] : [];
$care_groups = is_array($filters['care_groups'] ?? null) ? $filters['care_groups'] : [];
$current_page = max(1, (int) ($pagination['current'] ?? $state['paged']));
$total_pages = max(1, (int) ($pagination['total'] ?? (($query instanceof WP_Query) ? $query->max_num_pages : 1)));
$has_posts = $query instanceof WP_Query && $query->have_posts();
$form_id = 'programmes-therapies-archive-' . (function_exists('wp_rand') ? wp_rand(1000, 999999) : mt_rand(1000, 999999));
?>

<section
    <?php if ($section_id !== '') { ?>
        id="<?php echo esc_attr($section_id); ?>"
    <?php } ?>
    <?php if ($data_block !== '') { ?>
        data-matrix-block="<?php echo esc_attr($data_block); ?>"
    <?php } ?>
    class="<?php echo esc_attr($section_classes); ?>"
>
    <div class="<?php echo esc_attr($wrapper_classes); ?>">
        <header class="flex flex-col gap-8">
            <<?php echo esc_attr($heading_tag); ?> class="font-primary text-[24px] font-semibold leading-[28px] tracking-[-0.18px] text-[#1E244B] lg:text-[30px] lg:leading-[36px] lg:tracking-[-0.225px]">
                <?php echo esc_html($heading); ?>
            </<?php echo esc_attr($heading_tag); ?>>
            <div class="h-[4px] w-10 bg-[#6FC9C0]" aria-hidden="true"></div>
        </header>

        <div
            x-data="{
                type: '<?php echo esc_js((string) $state['type']); ?>',
                care: '<?php echo esc_js((string) $state['care']); ?>',
                delivery: '<?php echo esc_js((string) $state['delivery']); ?>',
                submitFilters() {
                    this.$refs.typeInput.value = this.type;
                    this.$refs.careInput.value = this.care;
                    this.$refs.deliveryInput.value = this.delivery;
                    this.$refs.pageInput.value = 1;
                    this.$refs.form.submit();
                },
                selectType(slug) {
                    this.type = slug;
                    this.care = 'all';
                    this.delivery = 'all';
                    this.submitFilters();
                },
                selectCareDelivery(careSlug, deliverySlug) {
                    this.care = careSlug;
                    this.delivery = deliverySlug;
                    this.submitFilters();
                },
                isTypeSelected(slug) {
                    return this.type === slug;
                },
                isCareDeliverySelected(careSlug, deliverySlug) {
                    return this.care === careSlug && this.delivery === deliverySlug;
                }
            }"
            class="mt-10 flex w-full flex-col gap-10 lg:mt-[40px] lg:flex-row lg:items-start lg:gap-[60px]"
        >
            <aside class="w-full shrink-0 lg:w-[367px]" aria-label="Filter programmes and therapies">
                <form
                    x-ref="form"
                    id="<?php echo esc_attr($form_id); ?>"
                    method="get"
                    action="<?php echo esc_url($base_url); ?>"
                    class="flex flex-col gap-3 rounded-[8px] bg-[#F1F8F9] p-6"
                >
                    <input type="hidden" name="pt_type" x-ref="typeInput" :value="type" />
                    <input type="hidden" name="pt_care" x-ref="careInput" :value="care" />
                    <input type="hidden" name="pt_delivery" x-ref="deliveryInput" :value="delivery" />
                    <input type="hidden" name="pt_page" x-ref="pageInput" value="<?php echo esc_attr((string) $current_page); ?>" />

                    <?php foreach ($type_options as $type_option) { ?>
                        <?php
                        $type_slug = matrix_programmes_therapies_archive_sanitize_slug((string) ($type_option['slug'] ?? ''));
                        $type_label = trim((string) ($type_option['label'] ?? ''));

                        if ($type_slug === '' || $type_label === '') {
                            continue;
                        }
                        ?>
                        <div class="rounded-[4px] bg-white py-3">
                            <button
                                type="button"
                                class="btn flex w-full items-center gap-3 pl-3 text-left focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
                                @click="selectType('<?php echo esc_js($type_slug); ?>')"
                                :aria-pressed="isTypeSelected('<?php echo esc_js($type_slug); ?>') ? 'true' : 'false'"
                            >
                                <span
                                    class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-[4px] border border-[#024B79]"
                                    :class="isTypeSelected('<?php echo esc_js($type_slug); ?>') ? 'bg-[#024B79] text-white' : 'bg-white text-transparent'"
                                    aria-hidden="true"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M3.5 8.25L6.5 11.25L12.5 4.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <span class="font-primary text-[20px] font-semibold leading-[24px] tracking-[-0.12px] text-[#1E244B]">
                                    <?php echo esc_html($type_label); ?>
                                </span>
                            </button>
                        </div>
                    <?php } ?>

                    <?php foreach ($care_groups as $care_group) { ?>
                        <?php
                        $care_slug = matrix_programmes_therapies_archive_sanitize_slug((string) ($care_group['slug'] ?? ''));
                        $care_label = trim((string) ($care_group['label'] ?? ''));
                        $delivery_options = is_array($care_group['delivery_options'] ?? null) ? $care_group['delivery_options'] : [];

                        if ($care_slug === '' || $care_label === '') {
                            continue;
                        }
                        ?>
                        <div class="rounded-[4px] bg-white p-3">
                            <div class="flex flex-col gap-3">
                                <p class="font-primary text-[20px] font-semibold leading-[24px] tracking-[-0.12px] text-[#1E244B]">
                                    <?php echo esc_html($care_label); ?>
                                </p>
                                <div class="h-px w-full bg-[#C6ECF4]" aria-hidden="true"></div>
                            </div>

                            <div class="mt-1 flex flex-col">
                                <?php foreach ($delivery_options as $delivery_option) { ?>
                                    <?php
                                    $delivery_slug = matrix_programmes_therapies_archive_sanitize_slug((string) ($delivery_option['slug'] ?? ''));
                                    $delivery_label = trim((string) ($delivery_option['label'] ?? ''));

                                    if ($delivery_slug === '' || $delivery_label === '') {
                                        continue;
                                    }
                                    ?>
                                    <button
                                        type="button"
                                        class="btn flex w-full items-center gap-3 px-4 py-[10px] text-left focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
                                        @click="selectCareDelivery('<?php echo esc_js($care_slug); ?>', '<?php echo esc_js($delivery_slug); ?>')"
                                        :aria-pressed="isCareDeliverySelected('<?php echo esc_js($care_slug); ?>', '<?php echo esc_js($delivery_slug); ?>') ? 'true' : 'false'"
                                    >
                                        <span
                                            class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-[4px] border border-[#024B79]"
                                            :class="isCareDeliverySelected('<?php echo esc_js($care_slug); ?>', '<?php echo esc_js($delivery_slug); ?>') ? 'bg-[#024B79]' : 'bg-white'"
                                            aria-hidden="true"
                                        ></span>
                                        <span class="font-primary text-[18px] font-normal leading-[20px] text-[#08284B]">
                                            <?php echo esc_html($delivery_label); ?>
                                        </span>
                                    </button>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </form>
            </aside>

            <div class="min-w-0 flex-1">
                <?php if ($has_posts) { ?>
                    <div class="flex flex-col gap-8">
                        <?php while ($query->have_posts()) { ?>
                            <?php
                            $query->the_post();
                            $card = matrix_map_programmes_therapies_post_card(get_the_ID());
                            ?>
                            <article class="rounded-[8px] bg-[#FBFAF7] p-6 shadow-[0px_1px_1px_rgba(0,0,0,0.05)]">
                                <?php if ($card['tags'] !== []) { ?>
                                    <ul class="mb-4 flex flex-wrap gap-4" role="list">
                                        <?php foreach ($card['tags'] as $tag) { ?>
                                            <li>
                                                <span class="inline-flex h-[30px] items-center justify-center rounded-full bg-[#FADBD8] px-4 font-primary text-[14px] font-medium leading-[24px] text-[#08284B]">
                                                    <?php echo esc_html((string) ($tag['label'] ?? '')); ?>
                                                </span>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                <?php } ?>

                                <h3 class="font-primary text-[20px] font-semibold leading-[24px] tracking-[-0.12px] text-[#1E244B]">
                                    <a
                                        href="<?php echo esc_url($card['permalink']); ?>"
                                        class="btn inline-flex items-center gap-2 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
                                    >
                                        <span><?php echo esc_html($card['title']); ?></span>
                                        <span aria-hidden="true">&rarr;</span>
                                    </a>
                                </h3>

                                <?php if ($card['summary'] !== '') { ?>
                                    <p class="mt-4 font-primary text-[14px] font-normal leading-[24px] text-[#1E244B]">
                                        <?php echo esc_html($card['summary']); ?>
                                    </p>
                                <?php } ?>
                            </article>
                        <?php } ?>
                        <?php wp_reset_postdata(); ?>
                    </div>
                <?php } else { ?>
                    <p class="font-primary text-[16px] leading-[28px] text-[#1E244B]">
                        <?php echo esc_html($empty_state_message); ?>
                    </p>
                <?php } ?>

                <?php if ($total_pages > 1) { ?>
                    <nav class="mt-10 flex flex-wrap items-center justify-center gap-2" aria-label="Programmes and therapies pagination">
                        <?php if ($current_page > 1) { ?>
                            <a
                                href="<?php echo esc_url(matrix_build_programmes_therapies_archive_page_url($base_url, $state, $current_page - 1)); ?>"
                                class="btn inline-flex h-8 w-8 items-center justify-center rounded-full border border-[#C6ECF4] bg-white text-[#08284B] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
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
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-[#08284B] font-primary text-[14px] font-semibold text-white"
                                    aria-current="page"
                                >
                                    <?php echo esc_html((string) $page); ?>
                                </span>
                            <?php } else { ?>
                                <a
                                    href="<?php echo esc_url(matrix_build_programmes_therapies_archive_page_url($base_url, $state, $page)); ?>"
                                    class="btn inline-flex h-8 w-8 items-center justify-center rounded-full border border-[#C6ECF4] bg-white font-primary text-[14px] font-semibold text-[#08284B] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
                                    aria-label="Go to page <?php echo esc_attr((string) $page); ?>"
                                >
                                    <?php echo esc_html((string) $page); ?>
                                </a>
                            <?php } ?>
                        <?php } ?>

                        <?php if ($current_page < $total_pages) { ?>
                            <a
                                href="<?php echo esc_url(matrix_build_programmes_therapies_archive_page_url($base_url, $state, $current_page + 1)); ?>"
                                class="btn inline-flex h-8 w-8 items-center justify-center rounded-full border border-[#C6ECF4] bg-white text-[#08284B] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
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
