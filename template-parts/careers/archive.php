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
$section_id = $normalize_text($careers_archive['section_id'] ?? '');
$data_block = $normalize_text($careers_archive['data_block'] ?? '');
$section_classes = $normalize_text($careers_archive['section_classes'] ?? '', 'w-full');
$section_style = $normalize_text($careers_archive['section_style'] ?? '');
$wrapper_classes = $normalize_text($careers_archive['wrapper_classes'] ?? '', matrix_get_careers_archive_default_wrapper_classes());
$filter_select_classes = matrix_get_careers_archive_filter_select_class_names();
$apply_filters_button_classes = matrix_get_careers_archive_apply_filters_button_class_names();
$select_chevron_style = "background-image: url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 16 16' fill='none'%3E%3Cpath d='M4 6L8 10L12 6' stroke='%2308284B' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E\");";
$allowed_department_ids = is_array($careers_archive['allowed_department_ids'] ?? null) ? $careers_archive['allowed_department_ids'] : [];
$allowed_location_ids = is_array($careers_archive['allowed_location_ids'] ?? null) ? $careers_archive['allowed_location_ids'] : [];

if (! in_array($heading_tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p'], true)) {
    $heading_tag = 'h2';
}

$state = array_merge([
    'department' => 'all',
    'location' => 'all',
    'search' => '',
    'paged' => 1,
    'posts_per_page' => (int) ($defaults['posts_per_page'] ?? 10),
], $state);

$current_page = max(1, (int) ($pagination['current'] ?? $state['paged']));
$department_select_id = 'careers-archive-department-' . (function_exists('wp_rand') ? wp_rand(1000, 999999) : mt_rand(1000, 999999));
$location_select_id = 'careers-archive-location-' . (function_exists('wp_rand') ? wp_rand(1000, 999999) : mt_rand(1000, 999999));
$form_id = 'careers-archive-form-' . (function_exists('wp_rand') ? wp_rand(1000, 999999) : mt_rand(1000, 999999));
$heading_id = $section_id !== '' ? $section_id . '-heading' : 'careers-archive-heading';
$ajax_url = function_exists('admin_url') ? admin_url('admin-ajax.php') : '';
$posts_per_page = max(1, (int) ($state['posts_per_page'] ?? ($defaults['posts_per_page'] ?? 10)));
$allowed_departments_param = implode(',', array_map('intval', $allowed_department_ids));
$allowed_locations_param = implode(',', array_map('intval', $allowed_location_ids));
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
        <div
            x-data="{
                department: '<?php echo esc_js((string) $state['department']); ?>',
                location: '<?php echo esc_js((string) $state['location']); ?>',
                search: '<?php echo esc_js((string) $state['search']); ?>',
                page: <?php echo (int) $current_page; ?>,
                loading: false,
                error: '',
                baseUrl: '<?php echo esc_js($base_url); ?>',
                ajaxUrl: '<?php echo esc_js($ajax_url); ?>',
                postsPerPage: <?php echo (int) $posts_per_page; ?>,
                allowedDepartments: '<?php echo esc_js($allowed_departments_param); ?>',
                allowedLocations: '<?php echo esc_js($allowed_locations_param); ?>',
                viewDetailLabel: '<?php echo esc_js($view_detail_label); ?>',
                emptyStateMessage: '<?php echo esc_js($empty_state_message); ?>',
                syncHiddenInputs() {
                    this.$refs.pageInput.value = this.page;
                    this.$refs.searchInput.value = this.search;
                },
                buildFilterUrl(page = this.page) {
                    const url = new URL(this.baseUrl, window.location.origin);

                    url.searchParams.delete('career_department');
                    url.searchParams.delete('career_location');
                    url.searchParams.delete('career_search');
                    url.searchParams.delete('career_page');

                    if (this.department !== 'all') {
                        url.searchParams.set('career_department', this.department);
                    }

                    if (this.location !== 'all') {
                        url.searchParams.set('career_location', this.location);
                    }

                    if (this.search.trim() !== '') {
                        url.searchParams.set('career_search', this.search.trim());
                    }

                    if (page > 1) {
                        url.searchParams.set('career_page', String(page));
                    }

                    const hash = window.location.hash || '';
                    return url.toString() + hash;
                },
                updateBrowserUrl(page = this.page, pushHistory = true) {
                    const nextUrl = this.buildFilterUrl(page);

                    if (pushHistory) {
                        history.pushState({ careersArchive: true }, '', nextUrl);
                    }
                },
                readStateFromUrl() {
                    const params = new URLSearchParams(window.location.search);
                    this.department = params.get('career_department') || 'all';
                    this.location = params.get('career_location') || 'all';
                    this.search = params.get('career_search') || '';
                    this.page = Math.max(1, parseInt(params.get('career_page') || '1', 10) || 1);
                    this.syncHiddenInputs();
                },
                async applyFilters(page = 1, pushHistory = true) {
                    this.page = page;
                    this.syncHiddenInputs();
                    this.loading = true;
                    this.error = '';

                    const params = new URLSearchParams({
                        action: 'matrix_careers_archive',
                        career_department: this.department,
                        career_location: this.location,
                        career_search: this.search.trim(),
                        career_page: String(page),
                        posts_per_page: String(this.postsPerPage),
                        base_url: this.baseUrl,
                        empty_state_message: this.emptyStateMessage,
                        view_detail_label: this.viewDetailLabel,
                        allowed_departments: this.allowedDepartments,
                        allowed_locations: this.allowedLocations,
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
                handleResultsClick(event) {
                    const link = event.target.closest('[data-care-page]');

                    if (!link) {
                        return;
                    }

                    event.preventDefault();
                    const nextPage = parseInt(link.getAttribute('data-care-page') || '1', 10) || 1;
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
            class="flex w-full flex-col gap-8 lg:gap-16"
        >
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
                    x-ref="form"
                    id="<?php echo esc_attr($form_id); ?>"
                    method="get"
                    action="<?php echo esc_url($base_url); ?>"
                    class="flex w-full flex-col gap-4 lg:flex-row lg:items-center lg:gap-8"
                    @submit.prevent="applyFilters(1)"
                >
                    <input type="hidden" name="career_page" x-ref="pageInput" value="<?php echo esc_attr((string) $current_page); ?>" />
                    <input type="hidden" name="career_search" x-ref="searchInput" :value="search" />

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
                            x-model="department"
                            class="<?php echo esc_attr($filter_select_classes); ?> lg:flex-1"
                            style="<?php echo esc_attr($select_chevron_style); ?>"
                            @change="applyFilters(1)"
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
                                <option value="<?php echo esc_attr($slug); ?>">
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
                            x-model="location"
                            class="<?php echo esc_attr($filter_select_classes); ?> lg:flex-1"
                            style="<?php echo esc_attr($select_chevron_style); ?>"
                            @change="applyFilters(1)"
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
                                <option value="<?php echo esc_attr($slug); ?>">
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php } ?>
                        </select>

                        <button
                            type="submit"
                            class="<?php echo esc_attr($apply_filters_button_classes); ?>"
                            :disabled="loading"
                            :aria-busy="loading ? 'true' : 'false'"
                        >
                            <?php echo esc_html($apply_filters_label); ?>
                        </button>
                    </div>
                </form>
            </header>

            <div class="flex w-full flex-col gap-[34px]">
                <p
                    x-show="error"
                    x-cloak
                    class="font-primary text-[14px] leading-[24px] text-red-600"
                    x-text="error"
                    role="alert"
                ></p>

                <div
                    x-ref="resultsPanel"
                    :aria-busy="loading ? 'true' : 'false'"
                >
                    <?php get_template_part('template-parts/careers/archive-results', null, [
                        'careers_archive' => array_merge($careers_archive, [
                            'query' => $careers_archive['query'] ?? null,
                        ]),
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</section>
