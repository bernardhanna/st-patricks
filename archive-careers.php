<?php

get_header();

$careers_archive = matrix_prepare_careers_archive([
    'section_id' => 'careers-archive-page',
    'data_block' => 'careers-archive-page',
    'request_state' => $_GET,
    'base_url' => get_post_type_archive_link('careers'),
]);
?>
<main class="mt-[0rem] w-full">
    <?php
    get_template_part('template-parts/careers/archive', null, [
        'careers_archive' => $careers_archive,
    ]);
    ?>
</main>
<?php get_footer(); ?>
