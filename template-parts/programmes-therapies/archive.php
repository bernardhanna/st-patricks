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
$form_id = 'programmes-therapies-archive-' . (function_exists('wp_rand') ? wp_rand(1000, 999999) : mt_rand(1000, 999999));
$ajax_url = function_exists('admin_url') ? admin_url('admin-ajax.php') : '';
$posts_per_page = max(1, (int) ($state['posts_per_page'] ?? 10));
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
    <div class="py-12 lg:py-[100px] <?php echo esc_attr($wrapper_classes); ?>">
        <header class="flex flex-col gap-8 self-start">
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
                page: <?php echo (int) $current_page; ?>,
                loading: false,
                error: '',
                baseUrl: '<?php echo esc_js($base_url); ?>',
                ajaxUrl: '<?php echo esc_js($ajax_url); ?>',
                postsPerPage: <?php echo (int) $posts_per_page; ?>,
                emptyStateMessage: '<?php echo esc_js($empty_state_message); ?>',
                syncHiddenInputs() {
                    this.$refs.typeInput.value = this.type;
                    this.$refs.careInput.value = this.care;
                    this.$refs.deliveryInput.value = this.delivery;
                    this.$refs.pageInput.value = this.page;
                },
                buildFilterUrl(page = this.page) {
                    const url = new URL(this.baseUrl, window.location.origin);

                    url.searchParams.delete('pt_type');
                    url.searchParams.delete('pt_care');
                    url.searchParams.delete('pt_delivery');
                    url.searchParams.delete('pt_page');

                    if (this.type !== 'all') {
                        url.searchParams.set('pt_type', this.type);
                    }

                    if (this.care !== 'all') {
                        url.searchParams.set('pt_care', this.care);
                    }

                    if (this.delivery !== 'all') {
                        url.searchParams.set('pt_delivery', this.delivery);
                    }

                    if (page > 1) {
                        url.searchParams.set('pt_page', String(page));
                    }

                    const hash = window.location.hash || '';
                    return url.toString() + hash;
                },
                updateBrowserUrl(page = this.page, pushHistory = true) {
                    const nextUrl = this.buildFilterUrl(page);

                    if (pushHistory) {
                        history.pushState({ programmesTherapiesArchive: true }, '', nextUrl);
                    }
                },
                readStateFromUrl() {
                    const params = new URLSearchParams(window.location.search);
                    this.type = params.get('pt_type') || 'all';
                    this.care = params.get('pt_care') || 'all';
                    this.delivery = params.get('pt_delivery') || 'all';
                    this.page = Math.max(1, parseInt(params.get('pt_page') || '1', 10) || 1);
                    this.syncHiddenInputs();
                },
                async applyFilters(page = 1, pushHistory = true) {
                    this.page = page;
                    this.syncHiddenInputs();
                    this.loading = true;
                    this.error = '';

                    const params = new URLSearchParams({
                        action: 'matrix_programmes_therapies_archive',
                        pt_type: this.type,
                        pt_care: this.care,
                        pt_delivery: this.delivery,
                        pt_page: String(page),
                        posts_per_page: String(this.postsPerPage),
                        base_url: this.baseUrl,
                        empty_state_message: this.emptyStateMessage,
                    });

                    try {
                        const response = await fetch(`${this.ajaxUrl}?${params.toString()}`, {
                            headers: { Accept: 'application/json' },
                        });

                        if (!response.ok) {
                            throw new Error('Filter request failed');
                        }

                        const data = await response.json();

                        if (!data.success) {
                            throw new Error('Filter request failed');
                        }

                        this.$refs.resultsPanel.innerHTML = data.data?.html || '';
                        this.updateBrowserUrl(page, pushHistory);
                    } catch (err) {
                        this.error = 'Could not update results. Please try again.';
                    } finally {
                        this.loading = false;
                    }
                },
                selectType(slug) {
                    this.type = slug;
                    this.care = 'all';
                    this.delivery = 'all';
                    this.applyFilters(1);
                },
                selectCareDelivery(careSlug, deliverySlug) {
                    this.care = careSlug;
                    this.delivery = deliverySlug;
                    this.applyFilters(1);
                },
                isTypeSelected(slug) {
                    return this.type === slug;
                },
                isCareDeliverySelected(careSlug, deliverySlug) {
                    return this.care === careSlug && this.delivery === deliverySlug;
                },
                handleResultsClick(event) {
                    const link = event.target.closest('[data-pt-page]');

                    if (!link) {
                        return;
                    }

                    event.preventDefault();
                    const nextPage = parseInt(link.getAttribute('data-pt-page') || '1', 10) || 1;
                    this.applyFilters(nextPage);
                },
                init() {
                    this.$refs.resultsPanel.addEventListener('click', (event) => this.handleResultsClick(event));

                    window.addEventListener('popstate', () => {
                        this.readStateFromUrl();
                        this.applyFilters(this.page, false);
                    });
                }
            }"
            x-init="init()"
            class="mt-10 flex w-full flex-col gap-10 lg:mt-[40px] lg:flex-row lg:items-start lg:gap-[60px]"
        >
            <aside class="w-full shrink-0 lg:w-[367px]" aria-label="Filter programmes and therapies">
                <form
                    x-ref="form"
                    id="<?php echo esc_attr($form_id); ?>"
                    method="get"
                    action="<?php echo esc_url($base_url); ?>"
                    class="flex flex-col gap-3 rounded-[8px] bg-[#F1F8F9] p-6"
                    @submit.prevent="applyFilters(1)"
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
                                class="flex w-full items-center gap-3 pl-3 text-left rounded-[4px] transition-colors duration-200 hover:bg-[#F1F8F9] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
                                :class="isTypeSelected('<?php echo esc_js($type_slug); ?>') ? 'bg-[#F1F8F9]' : ''"
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

                            <div class="flex flex-col mt-1">
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
                                        class="flex w-full items-center gap-3 rounded-[4px] px-4 py-[10px] text-left transition-colors duration-200 hover:bg-[#F1F8F9] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
                                        :class="isCareDeliverySelected('<?php echo esc_js($care_slug); ?>', '<?php echo esc_js($delivery_slug); ?>') ? 'bg-[#F1F8F9]' : ''"
                                        @click="selectCareDelivery('<?php echo esc_js($care_slug); ?>', '<?php echo esc_js($delivery_slug); ?>')"
                                        :aria-pressed="isCareDeliverySelected('<?php echo esc_js($care_slug); ?>', '<?php echo esc_js($delivery_slug); ?>') ? 'true' : 'false'"
                                    >
                                        <span
                                            class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-[4px] border border-[#024B79]"
                                            :class="isCareDeliverySelected('<?php echo esc_js($care_slug); ?>', '<?php echo esc_js($delivery_slug); ?>') ? 'bg-[#024B79] text-white' : 'bg-white text-transparent'"
                                            aria-hidden="true"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <path d="M3.5 8.25L6.5 11.25L12.5 4.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                        <span
                                            class="font-primary text-[18px] leading-[20px] text-[#08284B]"
                                            :class="isCareDeliverySelected('<?php echo esc_js($care_slug); ?>', '<?php echo esc_js($delivery_slug); ?>') ? 'font-semibold' : 'font-normal'"
                                        >
                                            <?php echo esc_html($delivery_label); ?>
                                        </span>
                                    </button>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                </form>
            </aside>

            <div class="flex-1 min-w-0">
                <p
                    x-show="error"
                    x-cloak
                    class="mb-4 font-primary text-[14px] leading-[24px] text-red-600"
                    x-text="error"
                    role="alert"
                ></p>

                <div
                    x-ref="resultsPanel"
                    :aria-busy="loading ? 'true' : 'false'"
                >
                    <?php get_template_part('template-parts/programmes-therapies/archive-results', null, [
                        'programmes_therapies_archive' => $archive,
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</section>
