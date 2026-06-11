<?php

get_header();

$term = get_queried_object();
$default_category = $term instanceof WP_Term ? $term->slug : 'all';

$research_project_archive = matrix_prepare_research_project_archive([
    'section_id' => 'research-project-category-archive',
    'data_block' => 'research-project-category-archive',
    'request_state' => $_GET,
    'base_url' => $term instanceof WP_Term ? get_term_link($term) : matrix_resolve_research_project_archive_base_url(),
    'heading' => $term instanceof WP_Term ? $term->name . ' Research Projects' : 'Research Projects',
    'default_category' => $default_category,
    'lock_category' => true,
]);
?>
<main class="mt-[7rem] w-full">
    <?php
    get_template_part('template-parts/research-projects/filter_archive', null, [
        'research_project_archive' => $research_project_archive,
    ]);
    ?>
</main>
<?php get_footer(); ?>
