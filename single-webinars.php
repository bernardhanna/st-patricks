<?php

get_header();
?>
<main class="w-full overflow-hidden bg-white">
    <?php while (have_posts()) { ?>
        <?php the_post(); ?>

        <?php get_template_part('template-parts/single/webinar-hero'); ?>
        <?php get_template_part('template-parts/single/event-body'); ?>
        <?php load_flexible_content_templates(); ?>
    <?php } ?>
</main>
<?php
get_footer();
