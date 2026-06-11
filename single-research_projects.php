<?php

get_header();
?>
<main class="w-full overflow-hidden bg-white">
    <?php while (have_posts()) { ?>
        <?php the_post(); ?>

        <?php get_template_part('template-parts/single/research-project-hero'); ?>

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
                        <article class="blog-single-content wp_editor entry-content" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                            <?php the_content(); ?>
                        </article>
                    <?php } ?>

                    <?php get_template_part('template-parts/single/research-project-author-share'); ?>
                </div>

                <?php get_template_part('template-parts/single/research-project-related-posts'); ?>
            </div>
        </section>

        <?php load_flexible_content_templates(); ?>
    <?php } ?>
</main>
<?php
get_footer();
