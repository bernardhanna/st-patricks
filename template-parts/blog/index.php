<?php
$settings = get_field('blog_settings', 'option') ?: [];

$hero_tag = (string) ($settings['hero_heading_tag'] ?? 'h1');
$hero_text = (string) ($settings['hero_heading_text'] ?? 'News and Events');
$sub_text = (string) ($settings['hero_subheading_text'] ?? 'Get mental health news and events, articles, videos and podcasts from St Patrick\'s Mental Health Services.');
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

$gradient_vars = matrix_get_hero_with_breadcrumbs_gradient_vars($section_background_color);
$gradient_solid = $gradient_vars['gradient_solid'];
$gradient_soft = $gradient_vars['gradient_soft'];
$gradient_clear = $gradient_vars['gradient_clear'];

$blog_filter_archive = [
    'show_heading' => false,
    'filter_label' => $filter_title,
    'wrapper_classes' => 'flex w-full max-w-[1018px] flex-col items-center mx-auto py-12 lg:py-[100px] max-xl:px-5',
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
<div class="mt-[0rem] w-full">
    <section
        class="relative flex flex-col overflow-hidden"
        style="background-color: <?php echo esc_attr($section_background_color); ?>;"
    >
        <?php if (! empty($breadcrumb_items) || $current_breadcrumb_label !== '') { ?>
            <?php
            get_template_part('template-parts/partials/hero-breadcrumbs-nav', null, [
                'items' => $breadcrumb_items,
                'current_label' => $current_breadcrumb_label,
                'background_color' => $breadcrumb_background_color,
            ]);
            ?>
        <?php } ?>

        <div class="mx-auto flex w-full max-w-[1280px] flex-col items-center">
            <div class="<?php echo esc_attr(matrix_get_hero_with_breadcrumbs_image_split_grid_class_names()); ?>">
                <div class="<?php echo esc_attr(matrix_get_hero_with_breadcrumbs_image_split_image_column_class_names()); ?>" style="border-color: <?php echo esc_attr($section_background_color); ?>;">
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

                    <?php
                    get_template_part('template-parts/partials/hero-image-split-image-gradients', null, [
                        'gradient_solid' => $gradient_solid,
                        'gradient_soft' => $gradient_soft,
                        'gradient_clear' => $gradient_clear,
                        'background_color' => $section_background_color,
                    ]);
                    ?>
                </div>

                <div class="<?php echo esc_attr(matrix_get_hero_with_breadcrumbs_image_split_column_class_names()); ?>">
                    <div class="<?php echo esc_attr(matrix_get_hero_with_breadcrumbs_image_split_text_group_class_names()); ?>">
                        <<?php echo esc_attr($hero_tag); ?>
                            class="<?php echo esc_attr(matrix_get_hero_with_breadcrumbs_image_split_heading_class_names()); ?>"
                            style="color: <?php echo esc_attr($heading_color); ?>;"
                        >
                            <?php echo esc_html($hero_text); ?>
                        </<?php echo esc_attr($hero_tag); ?>>

                        <?php if ($sub_text !== '') { ?>
                            <p
                                class="<?php echo esc_attr(matrix_get_hero_with_breadcrumbs_image_split_content_class_names()); ?>"
                                style="color: <?php echo esc_attr($text_color); ?>;"
                            >
                                <?php echo esc_html($sub_text); ?>
                            </p>
                        <?php } ?>
                    </div>
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
