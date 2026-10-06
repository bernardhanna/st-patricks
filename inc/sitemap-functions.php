<?php

/**
 * Build a nested sitemap node from a WP_Post page object.
 *
 * @param WP_Post|object $page
 * @param array<int, array<int, WP_Post|object>> $pages_by_parent
 * @return array<string, mixed>
 */
function matrix_build_sitemap_page_node($page, array $pages_by_parent)
{
    $children = [];

    foreach ($pages_by_parent[$page->ID] ?? [] as $child) {
        $children[] = matrix_build_sitemap_page_node($child, $pages_by_parent);
    }

    return [
        'id' => (int) $page->ID,
        'title' => (string) $page->post_title,
        'url' => (string) get_permalink($page),
        'children' => $children,
    ];
}

/**
 * Curated top-level page paths for the HTML sitemap (site IA, not every root page).
 *
 * @return string[]
 */
function matrix_get_sitemap_hub_page_paths()
{
    /**
     * Filter curated hub page paths shown as sitemap sections.
     *
     * @param string[] $paths
     */
    return apply_filters('matrix_sitemap_hub_page_paths', [
        'about-us',
        'what-we-offer',
        'inpatient-care',
        'programmes-therapies',
        'healthcare-professionals',
        'service-users-and-visitors',
        'news-and-events',
        'contact-us',
        'your-portal',
    ]);
}

/**
 * Additional page paths merged under a hub section (siblings not in the page tree).
 *
 * @return array<string, string[]>
 */
function matrix_get_sitemap_hub_extra_children()
{
    /**
     * Filter extra child page paths keyed by hub slug.
     *
     * @param array<string, string[]> $extra_children
     */
    return apply_filters('matrix_sitemap_hub_extra_children', [
        'inpatient-care' => [
            'service-users-and-visitors/your-stay-in-hospital-as-an-adult',
            'healthcare-professionals/refer-an-adult-for-inpatient-care',
        ],
        'your-portal' => [
            'your-portal/about-your-portal',
            'your-portal/register-for-your-portal',
            'service-users-and-visitors/service-user-it-support',
        ],
        'contact-us' => [
            'service-users-and-visitors/directions-and-parking',
        ],
        'service-users-and-visitors' => [
            'service-users-and-visitors/directions-and-parking',
            'your-portal/about-your-portal',
            'service-users-and-visitors/service-user-it-support',
            'getting-help/concerned-about-yourself-or-someone-you-know',
        ],
    ]);
}

/**
 * Specific CPT posts listed under a hub (in addition to full CPT archives).
 *
 * @return array<string, array<int, array{post_type: string, name: string}>>
 */
function matrix_get_sitemap_hub_extra_cpt_posts()
{
    /**
     * @param array<string, array<int, array{post_type: string, name: string}>> $extra_posts
     */
    return apply_filters('matrix_sitemap_hub_extra_cpt_posts', [
        'inpatient-care' => [
            ['post_type' => 'locations', 'name' => 'st-patricks-university-hospital'],
            ['post_type' => 'locations', 'name' => 'st-patricks-hospital-lucan'],
            ['post_type' => 'locations', 'name' => 'willow-grove-adolescent-unit'],
        ],
    ]);
}

/**
 * CPT archives whose published posts are listed under a hub section.
 *
 * @return array<string, string>
 */
function matrix_get_sitemap_hub_cpt_archives()
{
    /**
     * @param array<string, string> $archives Hub slug => post type name.
     */
    return apply_filters('matrix_sitemap_hub_cpt_archives', [
        'programmes-therapies' => 'programmes_therapies',
    ]);
}

/**
 * Taxonomy term links shown under a hub (e.g. news categories on the posts page).
 *
 * @return array<string, array{taxonomy: string, query_var: string, all_label?: string}>
 */
function matrix_get_sitemap_hub_taxonomy_children()
{
    /**
     * @param array<string, array{taxonomy: string, query_var: string, all_label?: string}> $sections
     */
    return apply_filters('matrix_sitemap_hub_taxonomy_children', [
        'news-and-events' => [
            'taxonomy' => 'category',
            'query_var' => 'blog_category',
            'all_label' => 'All',
        ],
    ]);
}

/**
 * Standalone utility pages shown as their own sitemap section (no children).
 *
 * @return string[]
 */
function matrix_get_sitemap_utility_page_paths()
{
    /**
     * Filter utility page paths appended after hub sections.
     *
     * @param string[] $paths
     */
    return apply_filters('matrix_sitemap_utility_page_paths', [
        'cookie-privacy-policy',
        'data-protection-policy',
        'accessibility',
    ]);
}

/**
 * Page IDs excluded from the sitemap tree by default.
 *
 * @return int[]
 */
function matrix_get_sitemap_excluded_page_ids()
{
    $exclude = [];

    $sitemap_page = get_page_by_path('sitemap');

    if ($sitemap_page instanceof WP_Post) {
        $exclude[] = (int) $sitemap_page->ID;
    }

    $flexi_page = get_page_by_path('flexi');

    if ($flexi_page instanceof WP_Post) {
        $exclude[] = (int) $flexi_page->ID;
    }

    /**
     * Filter default excluded page IDs from the HTML sitemap.
     *
     * @param int[] $exclude
     */
    return array_values(array_unique(array_filter(array_map('intval', apply_filters('matrix_sitemap_excluded_page_ids', $exclude)))));
}

/**
 * Request path for a post, used to skip destinations that already 301 away.
 *
 * @param WP_Post|object $post
 */
function matrix_get_sitemap_post_request_path($post)
{
    if (! is_object($post)) {
        return '';
    }

    $post_type = (string) ($post->post_type ?? 'page');

    if ($post_type === 'page' && function_exists('get_page_uri')) {
        return trim((string) get_page_uri($post), '/');
    }

    if (! function_exists('get_permalink')) {
        return '';
    }

    return trim((string) parse_url((string) get_permalink($post), PHP_URL_PATH), '/');
}

/**
 * Whether a local path is a theme-level redirect source.
 *
 * @param string $path
 */
function matrix_sitemap_path_is_redirected($path)
{
    $path = trim((string) $path, '/');

    if ($path === '' || ! function_exists('matrix_get_theme_path_redirect_map')) {
        return false;
    }

    $map = matrix_get_theme_path_redirect_map();

    return is_array($map) && isset($map[$path]);
}

/**
 * Whether a post should appear in the HTML sitemap.
 *
 * @param WP_Post|object|null $post
 * @param array<int, true>    $exclude_lookup
 */
function matrix_sitemap_post_is_listable($post, array $exclude_lookup = [])
{
    if (! is_object($post) || ! isset($post->ID)) {
        return false;
    }

    $status = (string) ($post->post_status ?? '');

    if ($status !== '' && $status !== 'publish') {
        return false;
    }

    if (isset($exclude_lookup[(int) $post->ID])) {
        return false;
    }

    return ! matrix_sitemap_path_is_redirected(matrix_get_sitemap_post_request_path($post));
}

/**
 * Build the hierarchical page tree from a list of page objects.
 *
 * @param array<int, WP_Post|object> $pages
 * @return array<int, array<string, mixed>>
 */
function matrix_build_sitemap_page_tree_from_pages(array $pages)
{
    $pages_by_parent = [];

    foreach ($pages as $page) {
        if (! is_object($page)) {
            continue;
        }

        $parent_id = (int) ($page->post_parent ?? 0);
        $pages_by_parent[$parent_id][] = $page;
    }

    $tree = [];

    foreach ($pages_by_parent[0] ?? [] as $top_level_page) {
        $tree[] = matrix_build_sitemap_page_node($top_level_page, $pages_by_parent);
    }

    return $tree;
}

/**
 * @param array<int, true> $exclude_lookup
 * @return array<string, mixed>|null
 */
function matrix_build_sitemap_child_node_from_page_path($path, array $exclude_lookup)
{
    $page = get_page_by_path($path);

    if (! $page instanceof WP_Post || ! matrix_sitemap_post_is_listable($page, $exclude_lookup)) {
        return null;
    }

    return [
        'id' => (int) $page->ID,
        'title' => (string) $page->post_title,
        'url' => (string) get_permalink($page),
        'children' => [],
    ];
}

/**
 * @param array<int, true> $exclude_lookup
 * @return array<string, mixed>|null
 */
function matrix_build_sitemap_child_node_from_cpt_post(array $source, array $exclude_lookup)
{
    $post_type = sanitize_key((string) ($source['post_type'] ?? ''));
    $name = sanitize_title((string) ($source['name'] ?? ''));

    if ($post_type === '' || $name === '') {
        return null;
    }

    $posts = get_posts([
        'post_type' => $post_type,
        'name' => $name,
        'post_status' => 'publish',
        'posts_per_page' => 1,
    ]);

    if ($posts === [] || ! $posts[0] instanceof WP_Post) {
        return null;
    }

    $post = $posts[0];

    if (! matrix_sitemap_post_is_listable($post, $exclude_lookup)) {
        return null;
    }

    return [
        'id' => (int) $post->ID,
        'title' => (string) $post->post_title,
        'url' => (string) get_permalink($post),
        'children' => [],
    ];
}

/**
 * @param array<int, true> $exclude_lookup
 * @return array<int, array<string, mixed>>
 */
function matrix_build_sitemap_cpt_archive_children($post_type, array $exclude_lookup)
{
    $post_type = sanitize_key((string) $post_type);

    if ($post_type === '') {
        return [];
    }

    $posts = get_posts([
        'post_type' => $post_type,
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ]);

    $children = [];

    foreach (is_array($posts) ? $posts : [] as $post) {
        if (! $post instanceof WP_Post || ! matrix_sitemap_post_is_listable($post, $exclude_lookup)) {
            continue;
        }

        $children[] = [
            'id' => (int) $post->ID,
            'title' => (string) $post->post_title,
            'url' => (string) get_permalink($post),
            'children' => [],
        ];
    }

    return $children;
}

/**
 * @return array<int, array<string, mixed>>
 */
function matrix_build_sitemap_taxonomy_children($base_url, array $config)
{
    $taxonomy = sanitize_key((string) ($config['taxonomy'] ?? ''));
    $query_var = sanitize_key((string) ($config['query_var'] ?? ''));
    $all_label = trim((string) ($config['all_label'] ?? 'All'));

    if ($taxonomy === '' || $query_var === '' || $base_url === '') {
        return [];
    }

    $terms = get_terms([
        'taxonomy' => $taxonomy,
        'hide_empty' => true,
    ]);

    if (is_wp_error($terms) || ! is_array($terms)) {
        return [];
    }

    $children = [
        [
            'id' => 0,
            'title' => $all_label,
            'url' => (string) $base_url,
            'children' => [],
        ],
    ];

    foreach ($terms as $term) {
        if (! $term instanceof WP_Term) {
            continue;
        }

        if ((string) $term->slug === 'uncategorized') {
            continue;
        }

        $children[] = [
            'id' => (int) $term->term_id,
            'title' => html_entity_decode((string) $term->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'url' => (string) add_query_arg([$query_var => $term->slug], $base_url),
            'children' => [],
        ];
    }

    return $children;
}

/**
 * @param array<int, array<string, mixed>> $children
 * @param array<string, mixed>|null         $child
 */
function matrix_append_sitemap_child(array &$children, array &$existing_urls, $child)
{
    if (! is_array($child)) {
        return;
    }

    $url = trim((string) ($child['url'] ?? ''));

    if ($url === '' || isset($existing_urls[$url])) {
        return;
    }

    $children[] = $child;
    $existing_urls[$url] = true;
}

/**
 * Build a shallow sitemap node (direct children only).
 *
 * @param WP_Post|object $page
 * @param array<int, true> $exclude_lookup
 * @return array<string, mixed>
 */
function matrix_build_sitemap_shallow_page_node($page, array $exclude_lookup)
{
    $children = [];
    $child_pages = get_pages([
        'parent' => (int) $page->ID,
        'post_status' => 'publish',
        'sort_column' => 'menu_order,post_title',
        'sort_order' => 'ASC',
    ]);

    foreach (is_array($child_pages) ? $child_pages : [] as $child) {
        if (! $child instanceof WP_Post || ! matrix_sitemap_post_is_listable($child, $exclude_lookup)) {
            continue;
        }

        $children[] = [
            'id' => (int) $child->ID,
            'title' => (string) $child->post_title,
            'url' => (string) get_permalink($child),
            'children' => [],
        ];
    }

    $hub_slug = (string) $page->post_name;
    $extra_paths = matrix_get_sitemap_hub_extra_children()[$hub_slug] ?? [];
    $existing_urls = array_flip(array_column($children, 'url'));

    foreach ($extra_paths as $extra_path) {
        matrix_append_sitemap_child(
            $children,
            $existing_urls,
            matrix_build_sitemap_child_node_from_page_path($extra_path, $exclude_lookup)
        );
    }

    foreach (matrix_get_sitemap_hub_extra_cpt_posts()[$hub_slug] ?? [] as $extra_cpt_post) {
        matrix_append_sitemap_child(
            $children,
            $existing_urls,
            matrix_build_sitemap_child_node_from_cpt_post($extra_cpt_post, $exclude_lookup)
        );
    }

    $cpt_archive_post_type = matrix_get_sitemap_hub_cpt_archives()[$hub_slug] ?? '';

    if ($cpt_archive_post_type !== '') {
        foreach (matrix_build_sitemap_cpt_archive_children($cpt_archive_post_type, $exclude_lookup) as $cpt_child) {
            matrix_append_sitemap_child($children, $existing_urls, $cpt_child);
        }
    }

    $taxonomy_config = matrix_get_sitemap_hub_taxonomy_children()[$hub_slug] ?? null;

    if (is_array($taxonomy_config)) {
        foreach (matrix_build_sitemap_taxonomy_children((string) get_permalink($page), $taxonomy_config) as $taxonomy_child) {
            matrix_append_sitemap_child($children, $existing_urls, $taxonomy_child);
        }
    }

    usort($children, static function (array $left, array $right) {
        return strcasecmp((string) ($left['title'] ?? ''), (string) ($right['title'] ?? ''));
    });

    return [
        'id' => (int) $page->ID,
        'title' => (string) $page->post_title,
        'url' => (string) get_permalink($page),
        'children' => $children,
    ];
}

/**
 * Build a sitemap section for a page path, or null when the page is missing.
 *
 * @param string           $path
 * @param array<int, true> $exclude_lookup
 * @param bool             $include_children
 * @return array<string, mixed>|null
 */
function matrix_build_sitemap_section_for_path($path, array $exclude_lookup, $include_children = true)
{
    $page = get_page_by_path($path);

    if (! $page instanceof WP_Post || ! matrix_sitemap_post_is_listable($page, $exclude_lookup)) {
        return null;
    }

    if (! $include_children) {
        return [
            'id' => (int) $page->ID,
            'title' => (string) $page->post_title,
            'url' => (string) get_permalink($page),
            'children' => [],
        ];
    }

    return matrix_build_sitemap_shallow_page_node($page, $exclude_lookup);
}

/**
 * Build the curated page tree for the HTML sitemap.
 *
 * @param int[] $exclude_ids
 * @return array<int, array<string, mixed>>
 */
function matrix_build_sitemap_page_tree(array $exclude_ids = [])
{
    if ($exclude_ids === []) {
        $exclude_ids = matrix_get_sitemap_excluded_page_ids();
    }

    $exclude_lookup = array_fill_keys(array_map('intval', $exclude_ids), true);
    $sections = [];

    foreach (matrix_get_sitemap_hub_page_paths() as $path) {
        $section = matrix_build_sitemap_section_for_path($path, $exclude_lookup);

        if ($section !== null) {
            $sections[] = $section;
        }
    }

    foreach (matrix_get_sitemap_utility_page_paths() as $path) {
        $section = matrix_build_sitemap_section_for_path($path, $exclude_lookup, false);

        if ($section !== null) {
            $sections[] = $section;
        }
    }

    /**
     * Filter curated sitemap sections before rendering.
     *
     * @param array<int, array<string, mixed>> $sections
     * @param int[]                            $exclude_ids
     */
    return apply_filters('matrix_sitemap_page_sections', $sections, $exclude_ids);
}

/**
 * Build supplemental archive sections (landing pages only; no long post lists).
 *
 * @return array<int, array<string, mixed>>
 */
function matrix_build_sitemap_archive_sections()
{
    /**
     * Filter supplemental archive sections shown below the page tree.
     *
     * Return sections with an optional `items` array of `{ title, url }` rows.
     * The default is empty so the sitemap stays a concise overview of main sections.
     *
     * @param array<int, array<string, mixed>> $sections
     */
    return apply_filters('matrix_sitemap_archive_sections', []);
}

/**
 * Prepare view-model data for the HTML sitemap template.
 *
 * @param int $page_id
 * @return array<string, mixed>
 */
function matrix_prepare_sitemap_page($page_id = 0)
{
    $page_id = $page_id > 0 ? $page_id : (int) get_queried_object_id();
    $page_title = $page_id > 0 ? (string) get_the_title($page_id) : 'Sitemap';

    if ($page_title === '') {
        $page_title = 'Sitemap';
    }

    $intro = '';

    if ($page_id > 0) {
        $raw_intro = trim((string) get_post_field('post_excerpt', $page_id));

        if ($raw_intro === '') {
            $raw_content = trim(wp_strip_all_tags((string) get_post_field('post_content', $page_id)));

            if ($raw_content !== '') {
                $raw_intro = wp_trim_words($raw_content, 40, '…');
            }
        }

        $intro = $raw_intro;
    }

    if ($intro === '') {
        $intro = 'A quick overview of the main sections of the St Patrick\'s Mental Health Services website.';
    }

    return [
        'page_id' => $page_id,
        'heading' => $page_title,
        'intro' => $intro,
        'breadcrumbs' => [
            [
                'title' => 'Home',
                'url' => home_url('/'),
                'target' => '',
            ],
        ],
        'current_crumb_label' => $page_title,
        'page_sections' => matrix_build_sitemap_page_tree(),
        'archive_sections' => matrix_build_sitemap_archive_sections(),
    ];
}

/**
 * Render nested sitemap list markup.
 *
 * @param array<int, array<string, mixed>> $items
 * @param int                              $depth
 * @return string
 */
function matrix_render_sitemap_list(array $items, $depth = 0)
{
    $items = array_values(array_filter($items, static function ($item) {
        return is_array($item)
            && trim((string) ($item['title'] ?? '')) !== ''
            && trim((string) ($item['url'] ?? '')) !== '';
    }));

    if ($items === []) {
        return '';
    }

    $depth = max(0, (int) $depth);

    $list_classes = 'mt-4 flex flex-col gap-1.5';
    $link_classes = 'font-primary text-[16px] font-normal leading-[26px] text-[#08284B] underline-offset-2 transition-colors hover:text-[#024B79] hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#024B79]';

    $html = '<ul class="' . esc_attr($list_classes) . '" role="list">';

    foreach ($items as $item) {
        $title = trim((string) ($item['title'] ?? ''));
        $url = trim((string) ($item['url'] ?? ''));
        $children = is_array($item['children'] ?? null) ? $item['children'] : [];

        $html .= '<li>';
        $html .= '<a href="' . esc_url($url) . '" class="' . esc_attr($link_classes) . '">';
        $html .= esc_html($title);
        $html .= '</a>';
        $html .= matrix_render_sitemap_list($children, $depth + 1);
        $html .= '</li>';
    }

    $html .= '</ul>';

    return $html;
}
