<?php

get_header();

$term = get_queried_object();
$default_category = $term instanceof WP_Term ? $term->slug : 'all';
$term_label = $term instanceof WP_Term ? $term->name : 'Research Projects';
$archive_url = get_post_type_archive_link('research_projects');
?>
<main class="mt-[0rem] w-full">
    <?php
    get_template_part('template-parts/research-projects/archive', null, [
        'prepare_args' => [
            'section_id' => 'research-project-category-archive',
            'data_block' => 'research-project-category-archive',
            'request_state' => $_GET,
            'base_url' => $term instanceof WP_Term ? get_term_link($term) : matrix_resolve_research_project_archive_base_url(),
            'default_category' => $default_category,
            'lock_category' => true,
        ],
        'hero_overrides' => [
            'hero_heading_text' => $term_label . ' Research Projects',
        ],
        'breadcrumb_items' => [
            [
                'title' => 'Home',
                'url' => home_url('/'),
                'target' => '',
            ],
            [
                'title' => 'Research Projects',
                'url' => is_string($archive_url) ? $archive_url : matrix_resolve_research_project_archive_base_url(),
                'target' => '',
            ],
        ],
        'current_breadcrumb_label' => $term_label,
    ]);
    ?>
</main>
<?php get_footer(); ?>
