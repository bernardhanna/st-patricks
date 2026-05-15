<?php

get_header();

$programmes_therapies_archive = matrix_prepare_programmes_therapies_archive([
    'section_id' => 'programmes-therapies-archive-page',
    'data_block' => 'programmes-therapies-archive-page',
    'request_state' => $_GET,
    'base_url' => get_post_type_archive_link('programmes_therapies'),
]);
?>
<main class="mt-[7rem] w-full">
    <?php
    get_template_part('template-parts/programmes-therapies/archive', null, [
        'programmes_therapies_archive' => $programmes_therapies_archive,
    ]);
    ?>
</main>
<?php get_footer(); ?>
