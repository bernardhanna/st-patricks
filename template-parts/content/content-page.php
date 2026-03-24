<?php
$content = get_the_content();
$extra_class = !empty(trim($content)) ? ' py-12' : '';
?>
<article class="relative wp_editor" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="entry-content<?php echo esc_attr($extra_class); ?>">
        <?php the_content(); ?>
    </div>
</article>
