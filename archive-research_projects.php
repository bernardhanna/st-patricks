<?php

get_header();

$archive_url = get_post_type_archive_link('research_projects');
$category_context = matrix_get_research_project_archive_category_context();
$archive_args = [
    'prepare_args' => [
        'section_id' => 'research-projects-archive-page',
        'data_block' => 'research-projects-archive-page',
        'request_state' => $_GET,
        'base_url' => $archive_url,
    ],
];

if (is_array($category_context)) {
    $archive_args['prepare_args']['default_category'] = $category_context['slug'];
    $archive_args['prepare_args']['lock_category'] = true;
    $archive_args['hero_overrides'] = [
        'hero_heading_text' => $category_context['hero_heading_text'],
    ];
    $archive_args['breadcrumb_items'] = [
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
    ];
    $archive_args['current_breadcrumb_label'] = $category_context['name'];
}
?>
<main class="mt-[0rem] w-full">
    <?php
    get_template_part('template-parts/research-projects/archive', null, $archive_args);
    ?>
</main>
<?php get_footer(); ?>
