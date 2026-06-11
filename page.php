<?php
get_header();
$enable_breadcrumbs = get_field('enable_breadcrumbs', 'option'); // Returns true/false
?>
<main class="overflow-hidden w-full site-main">
    <?php load_hero_templates(); ?>


    <?php
    $enable_breadcrumbs = get_field('enable_breadcrumbs', 'option');

    if ($enable_breadcrumbs !== false) :
        get_template_part('template-parts/header/breadcrumbs');
    endif;
    ?>

    <?php
    if (have_posts()) :
        while (have_posts()) : the_post();
            if (trim(get_the_content()) != '') : ?>
                <section class="bg-white">
                    <div class="<?php echo esc_attr(matrix_get_editor_body_content_wrapper_class_names()); ?>">
                        <?php
                        get_template_part('template-parts/content/content', 'page');
                        ?>
                    </div>
                </section>
    <?php endif;
        endwhile;
    else :
        echo '<p>No content found</p>';
    endif;
    ?>

    <?php load_flexible_content_templates(); ?>
</main>

<?php
get_footer();
?>