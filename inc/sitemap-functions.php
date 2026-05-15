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
 * Build the hierarchical page tree for the HTML sitemap.
 *
 * @param int[] $exclude_ids
 * @return array<int, array<string, mixed>>
 */
function matrix_build_sitemap_page_tree(array $exclude_ids = [])
{
    if ($exclude_ids === []) {
        $exclude_ids = matrix_get_sitemap_excluded_page_ids();
    }

    $pages = get_pages([
        'post_status' => 'publish',
        'sort_column' => 'menu_order,post_title',
        'sort_order' => 'ASC',
        'exclude' => implode(',', $exclude_ids),
    ]);

    return matrix_build_sitemap_page_tree_from_pages(is_array($pages) ? $pages : []);
}

/**
 * Build flat link rows for a post type archive section.
 *
 * @param string $post_type
 * @param int    $limit
 * @return array<int, array<string, string>>
 */
function matrix_build_sitemap_post_type_links($post_type, $limit = 100)
{
    $posts = get_posts([
        'post_type' => $post_type,
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'orderby' => 'title',
        'order' => 'ASC',
        'no_found_rows' => true,
    ]);

    $links = [];

    foreach ($posts as $post) {
        if (! $post instanceof WP_Post) {
            continue;
        }

        $links[] = [
            'title' => (string) get_the_title($post),
            'url' => (string) get_permalink($post),
        ];
    }

    return $links;
}

/**
 * Build supplemental archive sections (posts and public CPTs).
 *
 * @return array<int, array<string, mixed>>
 */
function matrix_build_sitemap_archive_sections()
{
    $sections = [];
    $definitions = [
        'post' => [
            'title' => 'News and events',
            'path' => 'news-and-events/',
        ],
        'webinars' => [
            'title' => 'Webinars',
            'path' => 'webinars/',
        ],
        'careers' => [
            'title' => 'Careers',
            'path' => 'about-us/careers/',
        ],
        'faqs' => [
            'title' => 'FAQs',
            'path' => '',
        ],
        'team_members' => [
            'title' => 'Team members',
            'path' => '',
        ],
    ];

    foreach ($definitions as $post_type => $definition) {
        $post_type_object = get_post_type_object($post_type);

        if (! $post_type_object instanceof WP_Post_Type || ! $post_type_object->public) {
            continue;
        }

        $archive_url = '';

        if ($post_type === 'post') {
            $posts_page_id = (int) get_option('page_for_posts');

            if ($posts_page_id > 0) {
                $archive_url = (string) get_permalink($posts_page_id);
            }
        } elseif ($definition['path'] !== '') {
            $archive_url = home_url('/' . ltrim($definition['path'], '/'));
        } elseif ($post_type_object->has_archive) {
            $archive_url = (string) get_post_type_archive_link($post_type);
        }

        $items = matrix_build_sitemap_post_type_links($post_type);

        if ($archive_url === '' && $items === []) {
            continue;
        }

        $sections[] = [
            'title' => (string) $definition['title'],
            'url' => $archive_url,
            'items' => $items,
        ];
    }

    /**
     * Filter supplemental archive sections shown below the page tree.
     *
     * @param array<int, array<string, mixed>> $sections
     */
    return apply_filters('matrix_sitemap_archive_sections', $sections);
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
        $intro = 'Browse all published pages, news, webinars, careers, FAQs, and team profiles across the St Patrick\'s Mental Health Services website.';
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

    $list_classes = $depth === 0
        ? 'mt-4 flex flex-col gap-2'
        : 'mt-2 flex flex-col gap-2 border-l border-[rgba(8,40,75,0.15)] pl-4';

    $link_classes = $depth === 0
        ? 'font-primary text-[16px] font-semibold leading-[28px] text-[#08284B] transition-colors hover:text-[#024B79] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#024B79]'
        : 'font-primary text-[16px] font-medium leading-[28px] text-[#08284B] transition-colors hover:text-[#024B79] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#024B79]';

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
