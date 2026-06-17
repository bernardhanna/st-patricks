<?php

$post_id = (int) ($args['post_id'] ?? (function_exists('get_the_ID') ? get_the_ID() : 0));
$image_class = trim((string) ($args['image_class'] ?? 'h-auto max-h-[387px] w-full object-cover'));
$placeholder_class = trim((string) ($args['placeholder_class'] ?? 'flex h-[240px] w-full items-center justify-center bg-[#E7EEF0] px-6 text-center font-primary text-[14px] font-medium leading-[20px] text-[#08284B] lg:h-[387px]'));
$wrapper_class = trim((string) ($args['wrapper_class'] ?? 'mb-8 overflow-hidden rounded-[6px]'));

if ($post_id < 1) {
    return;
}

$title = matrix_get_event_style_featured_image_placeholder_label($post_id);
$thumbnail_id = (int) get_post_thumbnail_id($post_id);
$force_webinar_placeholder = function_exists('matrix_should_force_webinar_single_placeholder')
    && matrix_should_force_webinar_single_placeholder($post_id);
?>

<div class="<?php echo esc_attr($wrapper_class); ?>">
    <?php if ($force_webinar_placeholder) { ?>
        <?php
        get_template_part('template-parts/single/partials/webinar-single-placeholder', null, [
            'context' => 'featured',
            'title' => $title,
        ]);
        ?>
    <?php } elseif ($thumbnail_id > 0) { ?>
        <?php
        echo wp_get_attachment_image($thumbnail_id, 'large', false, [
            'class' => $image_class,
            'alt' => $title,
        ]);
        ?>
    <?php } else { ?>
        <div class="<?php echo esc_attr($placeholder_class); ?>" role="img" aria-label="<?php echo esc_attr($title); ?>">
            <?php echo esc_html($title); ?>
        </div>
    <?php } ?>
</div>
