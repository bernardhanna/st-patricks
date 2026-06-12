<?php

get_header();

if (get_post_type() === 'post') {
    ?>
    <main class="w-full overflow-hidden bg-white">
        <?php while (have_posts()) { ?>
            <?php the_post(); ?>

            <?php get_template_part('template-parts/single/hero'); ?>

            <?php if (matrix_is_event_post()) { ?>
                <?php get_template_part('template-parts/single/event-body'); ?>
            <?php } else { ?>
            <section class="bg-white">
                <div class="mx-auto flex w-full max-w-[1018px] flex-col gap-12 px-5 py-12 lg:flex-row lg:items-start lg:gap-[60px] lg:px-0 lg:py-[100px]">
                    <div class="min-w-0 flex-1">
                        <?php if (has_post_thumbnail()) { ?>
                            <div class="mb-8 overflow-hidden rounded-[6px]">
                                <?php
                                the_post_thumbnail('large', [
                                    'class' => 'h-auto max-h-[387px] w-full object-cover',
                                ]);
                                ?>
                            </div>
                        <?php } ?>

                        <?php if (trim(get_the_content()) !== '') { ?>
                            <div class="relative min-w-0 w-full wp_editor">
                                <article class="<?php echo esc_attr(matrix_get_editor_body_content_class_names()); ?>" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                                    <?php the_content(); ?>
                                </article>
                            </div>
                        <?php } ?>

                        <?php get_template_part('template-parts/single/author-share'); ?>
                    </div>

                    <?php get_template_part('template-parts/single/related-posts'); ?>
                </div>
            </section>
            <?php } ?>

            <?php load_flexible_content_templates(); ?>
        <?php } ?>
    </main>
    <?php
    get_footer();
    return;
}

?>
<main class="overflow-hidden w-full min-h-screen site-main">
    <?php get_template_part('template-parts/single/hero'); ?>

    <?php
    if (function_exists('load_hero_templates')) {
        load_hero_templates();
    }
    ?>

    <?php
    $enable_breadcrumbs = get_field('enable_breadcrumbs', 'option');

    if ($enable_breadcrumbs !== false) {
        get_template_part('template-parts/header/breadcrumbs');
    }
    ?>

    <?php
    if (have_posts()) {
        while (have_posts()) {
            the_post();
            if (trim(get_the_content()) != '') { ?>
                <section class="bg-white">
                    <div class="<?php echo esc_attr(matrix_get_editor_body_content_wrapper_class_names()); ?>">
                        <?php get_template_part('template-parts/content/content', 'page'); ?>
                    </div>
                </section>
            <?php }
        }
    } else {
        echo '<p>No content found</p>';
    }
    ?>

    <?php load_flexible_content_templates(); ?>
</main>

<?php
get_footer();
