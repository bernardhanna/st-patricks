<?php

$context = trim((string) ($args['context'] ?? 'featured'));
$title = trim((string) ($args['title'] ?? ''));
$placeholder_id = matrix_get_webinar_single_placeholder_image_id();
$wrapper_class = matrix_get_webinar_single_placeholder_class_names($context);
$logo_class = matrix_get_webinar_single_placeholder_logo_class_names($context);
$placeholder_url = matrix_get_webinar_single_placeholder_image_url();
$is_vector_logo = $placeholder_id < 1 && str_ends_with(strtolower($placeholder_url), '.svg');
?>

<div
    class="<?php echo esc_attr($wrapper_class); ?>"
    <?php if ($title !== '') { ?>
        role="img"
        aria-label="<?php echo esc_attr($title); ?>"
    <?php } else { ?>
        aria-hidden="true"
    <?php } ?>
>
    <?php if ($placeholder_id > 0) { ?>
        <?php
        echo wp_get_attachment_image($placeholder_id, $context === 'related' ? 'medium_large' : 'large', false, [
            'class' => $context === 'related' ? 'h-[186px] w-full object-cover' : 'h-auto max-h-[387px] w-full object-cover',
            'alt' => $title !== '' ? $title : '',
        ]);
        ?>
    <?php } elseif ($is_vector_logo) { ?>
        <img
            src="<?php echo esc_url($placeholder_url); ?>"
            alt=""
            class="<?php echo esc_attr($logo_class); ?>"
            aria-hidden="true"
        />
    <?php } elseif ($title !== '') { ?>
        <p class="text-center font-primary text-[14px] font-medium leading-[20px] text-[#08284B]">
            <?php echo esc_html($title); ?>
        </p>
    <?php } ?>
</div>
