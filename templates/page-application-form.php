<?php

/**
 * Template Name: Application Form
 * Template Post Type: page
 */

get_header();
?>
<main id="main-content" class="overflow-hidden w-full site-main">
    <?php load_hero_templates(); ?>

    <?php
    if (have_posts()) :
        while (have_posts()) :
            the_post();
            if (trim((string) get_the_content()) !== '') :
                ?>
                <section class="bg-white">
                    <div class="<?php echo esc_attr(matrix_get_editor_body_content_wrapper_class_names()); ?>">
                        <?php get_template_part('template-parts/content/content', 'page'); ?>
                    </div>
                </section>
                <?php
            endif;
        endwhile;
    endif;
    ?>

    <?php
    // Render flexi rows except useful_links so the form sits above related links.
    if (function_exists('have_rows') && have_rows('flexible_content_blocks')) {
        while (have_rows('flexible_content_blocks')) {
            the_row();
            $layout = (string) get_row_layout();
            if ($layout === 'useful_links') {
                continue;
            }
            get_template_part('template-parts/flexi/' . $layout);
        }
    }

    get_template_part('template-parts/careers/application-form', null, [
        'form' => matrix_prepare_careers_application_form(0, [
            'standalone' => true,
            'heading' => 'Application form',
            'form_name' => 'Careers Application (standalone page)',
        ]),
    ]);

    if (function_exists('have_rows') && have_rows('flexible_content_blocks')) {
        while (have_rows('flexible_content_blocks')) {
            the_row();
            if (get_row_layout() !== 'useful_links') {
                continue;
            }
            get_template_part('template-parts/flexi/useful_links');
        }
    }
    ?>
</main>
<?php
get_footer();
