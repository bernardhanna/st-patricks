<?php

/**
 * Seed Research Projects CPT, taxonomies, sample posts, and archive pages.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-research-projects.php
 */

if (! function_exists('matrix_seed_ensure_term')) {
    function matrix_seed_ensure_term(string $taxonomy, string $name, string $slug = ''): int
    {
        $slug = $slug !== '' ? $slug : sanitize_title($name);
        $existing = get_term_by('slug', $slug, $taxonomy);

        if ($existing instanceof WP_Term) {
            return (int) $existing->term_id;
        }

        $result = wp_insert_term($name, $taxonomy, ['slug' => $slug]);

        if (is_wp_error($result)) {
            return 0;
        }

        return (int) ($result['term_id'] ?? 0);
    }
}

if (! function_exists('matrix_seed_ensure_page')) {
    function matrix_seed_ensure_page(string $path, string $title, array $flexi_rows = []): int
    {
        $page = get_page_by_path($path);

        if ($page instanceof WP_Post) {
            $post_id = (int) $page->ID;
        } else {
            $post_id = (int) wp_insert_post([
                'post_type' => 'page',
                'post_status' => 'publish',
                'post_title' => $title,
                'post_name' => basename($path),
                'post_parent' => 0,
            ]);

            if ($post_id < 1) {
                return 0;
            }

            if (str_contains($path, '/')) {
                $parent_path = dirname($path);
                $parent_page = get_page_by_path($parent_path);
                if ($parent_page instanceof WP_Post) {
                    wp_update_post([
                        'ID' => $post_id,
                        'post_parent' => (int) $parent_page->ID,
                    ]);
                }
            }
        }

        if ($flexi_rows !== [] && function_exists('update_field')) {
            update_field('hero_content_blocks', [], $post_id);
            update_field('flexible_content_blocks', $flexi_rows, $post_id);
        }

        return $post_id;
    }
}

$researchers = [
    'Prof Declan McLoughlin',
    'Dr Paddy Power',
    'Psychology Department',
    'Dr Noel Kennedy',
    'Pharmacy Department Research Group',
    'GPs Research Group',
    'Prof Paul Fearon',
    'Dr Conor Farren',
    'Ciara Ní Dhubhlaing',
    'Sherrie Buckley',
];

$current_term_id = matrix_seed_ensure_term('research_project_category', 'Current', 'current');
$past_term_id = matrix_seed_ensure_term('research_project_category', 'Past', 'past');

$researcher_term_ids = [];
foreach ($researchers as $researcher_name) {
    $term_id = matrix_seed_ensure_term('researcher', $researcher_name);
    if ($term_id > 0) {
        $researcher_term_ids[] = $term_id;
    }
}

$sample_projects = [
    [
        'title' => 'Duis aute irure dolor in lorem ipsum',
        'excerpt' => 'Lorem ipsum dolor sit amet, cons ectetur adipiscing elit.',
        'category' => 'current',
        'researcher_index' => 6,
    ],
    [
        'title' => 'Dolor in reprehendert',
        'excerpt' => 'Lorem ipsum dolor sit amet, cons ectetur adipiscing elit.',
        'category' => 'current',
        'researcher_index' => 7,
    ],
    [
        'title' => 'Understanding adolescent mental health pathways',
        'excerpt' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt.',
        'category' => 'current',
        'researcher_index' => 0,
    ],
    [
        'title' => 'Pharmacy-led intervention outcomes',
        'excerpt' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt.',
        'category' => 'past',
        'researcher_index' => 4,
    ],
    [
        'title' => 'GP collaborative care review',
        'excerpt' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt.',
        'category' => 'past',
        'researcher_index' => 5,
    ],
    [
        'title' => 'Psychology department longitudinal study',
        'excerpt' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt.',
        'category' => 'past',
        'researcher_index' => 2,
    ],
];

$created_posts = 0;

foreach ($sample_projects as $project) {
    $existing = get_page_by_title($project['title'], OBJECT, 'research_projects');
    if ($existing instanceof WP_Post) {
        continue;
    }

    $post_id = (int) wp_insert_post([
        'post_type' => 'research_projects',
        'post_status' => 'publish',
        'post_title' => $project['title'],
        'post_excerpt' => $project['excerpt'],
        'post_content' => '<p>' . esc_html($project['excerpt']) . ' Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>',
    ]);

    if ($post_id < 1) {
        continue;
    }

    $category_term_id = $project['category'] === 'past' ? $past_term_id : $current_term_id;
    if ($category_term_id > 0) {
        wp_set_object_terms($post_id, [$category_term_id], 'research_project_category', false);
    }

    $researcher_index = (int) ($project['researcher_index'] ?? 0);
    if (isset($researcher_term_ids[$researcher_index])) {
        wp_set_object_terms($post_id, [$researcher_term_ids[$researcher_index]], 'researcher', false);
    }

    $created_posts++;
}

$section_padding = [
    [
        'screen_size' => 'mob',
        'padding_top' => '3',
        'padding_bottom' => '3',
    ],
    [
        'screen_size' => 'lg',
        'padding_top' => '6.25',
        'padding_bottom' => '6.25',
    ],
];

$current_archive_page_id = matrix_seed_ensure_page('current-research-projects', 'Current Research Projects', [
    [
        'acf_fc_layout' => 'research_project_archive',
        'heading_tag' => 'h1',
        'heading' => 'Current Research Projects',
        'default_category' => $current_term_id,
        'lock_category' => 1,
        'posts_per_page' => 12,
        'padding_settings' => $section_padding,
    ],
]);

$past_archive_page_id = matrix_seed_ensure_page('past-research-projects', 'Past Research Projects', [
    [
        'acf_fc_layout' => 'research_project_archive',
        'heading_tag' => 'h1',
        'heading' => 'Past Research Projects',
        'default_category' => $past_term_id,
        'lock_category' => 1,
        'posts_per_page' => 12,
        'padding_settings' => $section_padding,
    ],
]);

flush_rewrite_rules(false);

if (class_exists('WP_CLI')) {
    WP_CLI::success(sprintf(
        'Seeded research projects: %d categories, %d researchers, %d new posts, archive pages %d and %d.',
        2,
        count($researcher_term_ids),
        $created_posts,
        $current_archive_page_id,
        $past_archive_page_id
    ));
}
