<?php

/**
 * Page Section Taxonomy for Admin Filtering
 *
 * Adds a hidden "Section" taxonomy to pages for easier admin filtering.
 * Pages are auto-assigned to sections based on their parent hierarchy.
 */

if (! function_exists('matrix_register_page_section_taxonomy')) {
    /**
     * Register the page_section taxonomy for pages.
     */
    function matrix_register_page_section_taxonomy(): void
    {
        $labels = [
            'name' => 'Sections',
            'singular_name' => 'Section',
            'search_items' => 'Search Sections',
            'all_items' => 'All Sections',
            'parent_item' => 'Parent Section',
            'parent_item_colon' => 'Parent Section:',
            'edit_item' => 'Edit Section',
            'update_item' => 'Update Section',
            'add_new_item' => 'Add New Section',
            'new_item_name' => 'New Section Name',
            'menu_name' => 'Sections',
        ];

        $args = [
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'show_in_nav_menus' => false,
            'show_in_rest' => true,
            'show_tagcloud' => false,
            'show_in_quick_edit' => true,
            'show_admin_column' => true,
            'hierarchical' => true,
            'rewrite' => false,
            // Keep query_var available so admin list filters can use ?page_section=slug.
            'query_var' => 'page_section',
        ];

        register_taxonomy('page_section', 'page', $args);
    }
}

if (! function_exists('matrix_ensure_page_section_terms')) {
    /**
     * Ensure the default section terms exist.
     */
    function matrix_ensure_page_section_terms(): void
    {
        $sections = [
            'about-us' => 'About Us',
            'what-we-offer' => 'What We Offer',
            'healthcare-professionals' => 'Healthcare Professionals',
            'service-users-and-visitors' => 'Service Users and Visitors',
            'your-portal' => 'Your Portal',
            'contact-us' => 'Contact Us',
            'news-and-events' => 'News & Events',
        ];

        foreach ($sections as $slug => $name) {
            if (! term_exists($slug, 'page_section')) {
                wp_insert_term($name, 'page_section', ['slug' => $slug]);
            }
        }
    }
}

if (! function_exists('matrix_get_page_section_root_slugs')) {
    /**
     * Get the list of root page slugs that define sections.
     *
     * @return array<string, int> Slug => Page ID mapping
     */
    function matrix_get_page_section_root_slugs(): array
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        $slugs = [
            'about-us',
            'what-we-offer',
            'healthcare-professionals',
            'service-users-and-visitors',
            'your-portal',
            'contact-us',
            'news-and-events',
        ];

        $cache = [];

        foreach ($slugs as $slug) {
            $page = get_page_by_path($slug);

            if ($page instanceof WP_Post) {
                $cache[$slug] = (int) $page->ID;
            }
        }

        return $cache;
    }
}

if (! function_exists('matrix_get_page_section_slug')) {
    /**
     * Determine which section a page belongs to based on its hierarchy.
     *
     * @param int $post_id The page ID
     * @return string|null The section slug or null if not in a section
     */
    function matrix_get_page_section_slug(int $post_id): ?string
    {
        $post = get_post($post_id);

        if (! $post instanceof WP_Post || $post->post_type !== 'page') {
            return null;
        }

        $root_slugs = matrix_get_page_section_root_slugs();

        // Check if this page is itself a root section page
        if (isset($root_slugs[$post->post_name])) {
            return $post->post_name;
        }

        // Traverse up the ancestor chain to find the section root
        $ancestors = get_post_ancestors($post_id);

        if (empty($ancestors)) {
            return null;
        }

        // The last ancestor is the top-level parent
        $root_id = end($ancestors);
        $root_post = get_post($root_id);

        if (! $root_post instanceof WP_Post) {
            return null;
        }

        $root_slug = $root_post->post_name;

        return isset($root_slugs[$root_slug]) ? $root_slug : null;
    }
}

if (! function_exists('matrix_auto_assign_page_section')) {
    /**
     * Auto-assign page to section taxonomy when saved.
     *
     * @param int     $post_id Post ID
     * @param WP_Post $post    Post object
     * @param bool    $update  Whether this is an update
     */
    function matrix_auto_assign_page_section(int $post_id, WP_Post $post, bool $update): void
    {
        // Skip autosaves and revisions
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (wp_is_post_revision($post_id)) {
            return;
        }

        if ($post->post_type !== 'page') {
            return;
        }

        $section_slug = matrix_get_page_section_slug($post_id);

        if ($section_slug === null) {
            // Remove from all sections if not in a section hierarchy
            wp_set_object_terms($post_id, [], 'page_section');
            return;
        }

        // Assign to the appropriate section
        wp_set_object_terms($post_id, $section_slug, 'page_section');
    }
}

if (! function_exists('matrix_page_section_admin_filter')) {
    /**
     * Add section dropdown filter to Pages admin list.
     *
     * @param string $post_type The current post type
     */
    function matrix_page_section_admin_filter(string $post_type): void
    {
        if ($post_type !== 'page') {
            return;
        }

        $taxonomy = 'page_section';
        $selected = isset($_GET[$taxonomy]) ? sanitize_text_field(wp_unslash((string) $_GET[$taxonomy])) : '';

        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC',
        ]);

        if (is_wp_error($terms) || empty($terms)) {
            return;
        }

        echo '<label for="' . esc_attr($taxonomy) . '-filter" class="screen-reader-text">' . esc_html__('Filter by section', 'matrix-starter') . '</label>';
        echo '<select name="' . esc_attr($taxonomy) . '" id="' . esc_attr($taxonomy) . '-filter">';
        echo '<option value="">' . esc_html__('All Sections', 'matrix-starter') . '</option>';

        foreach ($terms as $term) {
            if (! $term instanceof WP_Term) {
                continue;
            }

            $count = $term->count;
            printf(
                '<option value="%s" %s>%s (%d)</option>',
                esc_attr($term->slug),
                selected($selected, $term->slug, false),
                esc_html($term->name),
                (int) $count
            );
        }

        echo '</select>';
    }
}

if (! function_exists('matrix_page_section_filter_admin_query')) {
    /**
     * Apply the section dropdown selection to the Pages admin list query.
     *
     * Taxonomy registration alone is not enough when publicly_queryable is false.
     *
     * @param WP_Query $query Main query
     */
    function matrix_page_section_filter_admin_query(WP_Query $query): void
    {
        if (! is_admin() || ! $query->is_main_query()) {
            return;
        }

        global $pagenow;

        if ($pagenow !== 'edit.php') {
            return;
        }

        $post_type = $query->get('post_type');

        if ($post_type !== 'page' && ! (is_array($post_type) && in_array('page', $post_type, true))) {
            // edit.php for pages may leave post_type empty in some contexts.
            $request_type = isset($_GET['post_type']) ? sanitize_key(wp_unslash((string) $_GET['post_type'])) : 'post';

            if ($request_type !== 'page') {
                return;
            }
        }

        $section = isset($_GET['page_section']) ? sanitize_text_field(wp_unslash((string) $_GET['page_section'])) : '';

        if ($section === '') {
            return;
        }

        $tax_query = $query->get('tax_query');

        if (! is_array($tax_query)) {
            $tax_query = [];
        }

        $tax_query[] = [
            'taxonomy' => 'page_section',
            'field' => 'slug',
            'terms' => $section,
        ];

        $query->set('tax_query', $tax_query);
    }
}

if (! function_exists('matrix_page_section_admin_column_content')) {
    /**
     * Display section in the admin column.
     *
     * @param string $column_name Column name
     * @param int    $post_id     Post ID
     */
    function matrix_page_section_admin_column_content(string $column_name, int $post_id): void
    {
        if ($column_name !== 'taxonomy-page_section') {
            return;
        }

        $terms = get_the_terms($post_id, 'page_section');

        if (is_wp_error($terms) || empty($terms)) {
            echo '<span aria-hidden="true">—</span>';
            return;
        }

        $term_names = array_map(function ($term) {
            return esc_html($term->name);
        }, $terms);

        echo implode(', ', $term_names);
    }
}

// Register hooks
if (function_exists('add_action')) {
    // Register taxonomy on init
    add_action('init', 'matrix_register_page_section_taxonomy', 5);

    // Ensure default terms exist after taxonomy is registered
    add_action('init', 'matrix_ensure_page_section_terms', 10);

    // Auto-assign section when page is saved
    add_action('save_post_page', 'matrix_auto_assign_page_section', 20, 3);

    // Add admin filter dropdown and apply it to the list query.
    add_action('restrict_manage_posts', 'matrix_page_section_admin_filter');
    add_action('pre_get_posts', 'matrix_page_section_filter_admin_query');

    // Custom column content (taxonomy column is auto-added, but we customize display)
    add_action('manage_pages_custom_column', 'matrix_page_section_admin_column_content', 10, 2);
}
