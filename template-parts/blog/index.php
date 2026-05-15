<?php
$settings = get_field('blog_settings', 'option') ?: [];

$hero_tag = (string) ($settings['hero_heading_tag'] ?? 'h1');
$hero_text = (string) ($settings['hero_heading_text'] ?? "What's new at Tyrecare");
$sub_text = (string) ($settings['hero_subheading_text'] ?? 'Latest and greatest.');
$hero_bg = ! empty($settings['hero_background_image']) && is_array($settings['hero_background_image'])
    ? $settings['hero_background_image']
    : null;
$hero_image_id = (int) ($hero_bg['ID'] ?? $hero_bg['id'] ?? 0);
$section_background_color = '#C6ECF4';
$breadcrumb_background_color = '#F1F8F9';
$heading_color = '#08284B';
$text_color = '#08284B';
$defaults = matrix_get_blog_filter_archive_defaults();
$filter_title = (string) ($settings['filter_section_title'] ?? ($defaults['filter_label'] ?? 'Filter by:'));
$request_state = $_GET;

if (! in_array($hero_tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p'], true)) {
    $hero_tag = 'h1';
}

if (empty($request_state['blog_page'])) {
    $request_state['blog_page'] = max(1, (int) get_query_var('paged'));
}

if (empty($request_state['blog_category']) && is_category()) {
    $queried_term = get_queried_object();

    if ($queried_term instanceof WP_Term && $queried_term->taxonomy === 'category') {
        $request_state['blog_category'] = $queried_term->slug;
    }
}

if (! array_key_exists('blog_search', $request_state) && is_search()) {
    $request_state['blog_search'] = get_search_query(false);
}

$terms = get_terms([
    'taxonomy' => 'category',
    'hide_empty' => true,
]);

if (is_wp_error($terms) || ! is_array($terms)) {
    $terms = [];
}

$chips = [
    [
        'slug' => 'all',
        'label' => 'All',
    ],
];
$slug_to_id_map = [];

foreach ($terms as $term) {
    if (! $term instanceof WP_Term) {
        continue;
    }

    $chips[] = [
        'slug' => $term->slug,
        'label' => $term->name,
        'term_id' => (int) $term->term_id,
    ];
    $slug_to_id_map[$term->slug] = (int) $term->term_id;
}

$state = matrix_resolve_blog_filter_archive_state(
    $request_state,
    array_keys($slug_to_id_map),
    (int) ($defaults['posts_per_page'] ?? 12)
);
$query = new WP_Query(matrix_build_blog_filter_archive_query_args($state, $slug_to_id_map));
$posts_page_id = (int) get_option('page_for_posts');
$base_url = $posts_page_id > 0 ? get_permalink($posts_page_id) : '';

if (! is_string($base_url) || $base_url === '') {
    $resources_page = get_page_by_path('resources');
    $base_url = $resources_page instanceof WP_Post ? get_permalink($resources_page) : home_url('/resources/');
}

$posts_page_title = $posts_page_id > 0 ? get_the_title($posts_page_id) : '';
if (! is_string($posts_page_title) || $posts_page_title === '') {
    $posts_page_title = 'Resources';
}

$breadcrumb_items = [
    [
        'title' => 'Home',
        'url' => home_url('/'),
        'target' => '',
    ],
];
$current_breadcrumb_label = $posts_page_title;

if (is_category() || is_search()) {
    $breadcrumb_items[] = [
        'title' => $posts_page_title,
        'url' => $base_url,
        'target' => '',
    ];
}

if (is_category()) {
    $current_breadcrumb_label = single_cat_title('', false);
} elseif (is_search()) {
    $search_term = trim((string) get_search_query(false));
    $current_breadcrumb_label = $search_term !== '' ? sprintf('Search: %s', $search_term) : 'Search Results';
}

$breadcrumb_data = function_exists('matrix_resolve_hero_breadcrumbs')
    ? matrix_resolve_hero_breadcrumbs('manual', $breadcrumb_items, $current_breadcrumb_label, [])
    : [
        'items' => $breadcrumb_items,
        'current_label' => $current_breadcrumb_label,
    ];
$breadcrumb_items = is_array($breadcrumb_data['items'] ?? null) ? $breadcrumb_data['items'] : [];
$current_breadcrumb_label = (string) ($breadcrumb_data['current_label'] ?? '');

$hero_image_alt = '';
$hero_image_title = '';

if ($hero_image_id > 0) {
    $hero_image_alt = (string) get_post_meta($hero_image_id, '_wp_attachment_image_alt', true);
    $hero_image_title = (string) get_the_title($hero_image_id);
} elseif ($hero_bg) {
    $hero_image_alt = (string) ($hero_bg['alt'] ?? '');
    $hero_image_title = (string) ($hero_bg['title'] ?? '');
}

if ($hero_image_alt === '') {
    $hero_image_alt = $hero_image_title !== '' ? $hero_image_title : $hero_text;
}

$gradient_soft = 'rgba(198, 236, 244, 0.9)';
$gradient_clear = 'rgba(198, 236, 244, 0)';

if (preg_match('/^#([A-Fa-f0-9]{6})$/', $section_background_color, $matches)) {
    $hex = $matches[1];
    $red = hexdec(substr($hex, 0, 2));
    $green = hexdec(substr($hex, 2, 2));
    $blue = hexdec(substr($hex, 4, 2));
    $gradient_soft = "rgba({$red}, {$green}, {$blue}, 0.9)";
    $gradient_clear = "rgba({$red}, {$green}, {$blue}, 0)";
}

$blog_filter_archive = [
    'heading_tag' => 'h2',
    'heading' => "What's new at St Patrick's",
    'filter_label' => $filter_title,
    'base_url' => $base_url,
    'state' => $state,
    'chips' => $chips,
    'query' => $query,
    'pagination' => [
        'current' => max(1, (int) $query->get('paged')),
        'total' => max(1, (int) $query->max_num_pages),
    ],
];
?>
<div class="mt-[7rem] w-full">
    <section
        class="flex overflow-hidden relative"
        style="background-color: <?php echo esc_attr($section_background_color); ?>;"
    >
        <div class="flex flex-col items-center w-full mx-auto max-w-[1280px] max-xl:px-5 pt-5 pb-5">
            <?php if (! empty($breadcrumb_items) || $current_breadcrumb_label !== '') { ?>
                <nav
                    class="w-full px-5 py-3 lg:px-[70px]"
                    style="background-color: <?php echo esc_attr($breadcrumb_background_color); ?>;"
                    aria-label="Breadcrumb"
                >
                    <ol class="flex flex-wrap gap-3 items-center" role="list">
                        <?php foreach ($breadcrumb_items as $breadcrumb_item) { ?>
                            <li class="flex gap-3 items-center">
                                <a
                                    href="<?php echo esc_url($breadcrumb_item['url']); ?>"
                                    target="<?php echo esc_attr(($breadcrumb_item['target'] ?? '') !== '' ? $breadcrumb_item['target'] : '_self'); ?>"
                                    class="inline-flex w-fit whitespace-nowrap font-primary text-[14px] not-italic font-semibold leading-[20px] text-[#08284B] transition-colors duration-200 hover:text-[#024B79] focus-visible:text-[#024B79]"
                                    aria-label="<?php echo esc_attr($breadcrumb_item['title']); ?>"
                                >
                                    <?php echo esc_html($breadcrumb_item['title']); ?>
                                </a>
                                <svg class="shrink-0" width="10" height="12" viewBox="0 0 10 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M4 1L8 6L4 11" stroke="#08284B" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </li>
                        <?php } ?>

                        <?php if ($current_breadcrumb_label !== '') { ?>
                            <li class="font-primary text-[14px] not-italic font-normal leading-[20px] text-[#08284B]" aria-current="page">
                                <?php echo esc_html($current_breadcrumb_label); ?>
                            </li>
                        <?php } ?>
                    </ol>
                </nav>
            <?php } ?>

            <div class="grid w-full items-center overflow-hidden lg:min-h-[320px] lg:grid-cols-[minmax(0,1fr)_581px]">
                <div class="w-full px-5 py-10 lg:pl-[52px] lg:pr-8">
                    <div class="grid gap-[17px]">
                        <<?php echo esc_attr($hero_tag); ?>
                            class="max-w-[599px] font-primary text-[36px] not-italic font-bold leading-[40px] tracking-[-0.432px] text-[#08284B] lg:text-[48px] lg:leading-[48px] lg:tracking-[-0.576px]"
                            style="color: <?php echo esc_attr($heading_color); ?>;"
                        >
                            <?php echo esc_html($hero_text); ?>
                        </<?php echo esc_attr($hero_tag); ?>>

                        <?php if ($sub_text !== '') { ?>
                            <p
                                class="max-w-[599px] font-primary text-[18px] not-italic font-normal leading-[28px] text-[#08284B]"
                                style="color: <?php echo esc_attr($text_color); ?>;"
                            >
                                <?php echo esc_html($sub_text); ?>
                            </p>
                        <?php } ?>
                    </div>
                </div>

                <div class="relative h-[240px] w-full overflow-hidden lg:h-[320px] lg:border-l-2" style="border-color: <?php echo esc_attr($section_background_color); ?>;">
                    <?php if ($hero_image_id > 0) { ?>
                        <?php
                        echo wp_get_attachment_image($hero_image_id, 'full', false, [
                            'alt' => esc_attr($hero_image_alt),
                            'title' => esc_attr($hero_image_title),
                            'class' => 'absolute inset-0 h-full w-full object-cover',
                            'loading' => 'eager',
                        ]);
                        ?>
                    <?php } ?>

                    <div
                        class="absolute inset-0 pointer-events-none"
                        style="background: linear-gradient(90deg, <?php echo esc_attr($section_background_color); ?> 0%, <?php echo esc_attr($gradient_soft); ?> 14.69%, <?php echo esc_attr($gradient_clear); ?> 45.97%);"
                        aria-hidden="true"
                    ></div>
                </div>
            </div>
        </div>
    </section>

    <?php
    get_template_part('template-parts/blog/filter_archive', null, [
        'blog_filter_archive' => $blog_filter_archive,
    ]);
    ?>
</div>
