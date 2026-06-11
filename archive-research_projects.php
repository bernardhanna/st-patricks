<?php

get_header();

$research_project_archive = matrix_prepare_research_project_archive([
    'section_id' => 'research-projects-archive-page',
    'data_block' => 'research-projects-archive-page',
    'request_state' => $_GET,
    'base_url' => get_post_type_archive_link('research_projects'),
    'heading' => 'Research Projects',
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
