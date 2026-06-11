<?php

if (trim(get_the_content()) === '') {
    return;
}
?>
<article
    class="<?php echo esc_attr(matrix_get_editor_body_content_class_names()); ?> relative"
    id="post-<?php the_ID(); ?>"
    <?php post_class(); ?>
>
    <?php the_content(); ?>
</article>
