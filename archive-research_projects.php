<?php

get_header();
?>
<main class="mt-[0rem] w-full">
    <?php
    get_template_part('template-parts/research-projects/archive', null, [
        'prepare_args' => [
            'section_id' => 'research-projects-archive-page',
            'data_block' => 'research-projects-archive-page',
            'request_state' => $_GET,
            'base_url' => get_post_type_archive_link('research_projects'),
        ],
    ]);
    ?>
</main>
<?php get_footer(); ?>
