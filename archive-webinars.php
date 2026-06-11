<?php

get_header();

$webinars_archive = matrix_prepare_webinars_archive([
    'section_id' => 'webinars-archive-page',
    'data_block' => 'webinars-archive-page',
    'request_state' => $_GET,
    'base_url' => get_post_type_archive_link('webinars'),
]);
?>
<main class="mt-[0rem] w-full">
    <?php
    get_template_part('template-parts/webinars/archive', null, [
        'webinars_archive' => $webinars_archive,
    ]);
    ?>
</main>
<?php get_footer(); ?>
