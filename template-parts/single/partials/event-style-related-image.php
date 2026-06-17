<?php

$card = is_array($args['card'] ?? null) ? $args['card'] : [];
$image_class = trim((string) ($args['image_class'] ?? 'h-[186px] w-full object-cover'));
$placeholder_class = trim((string) ($args['placeholder_class'] ?? 'flex h-[186px] w-full items-center justify-center rounded-[4px] bg-[#E7EEF0] px-4 text-center font-primary text-[14px] font-medium leading-[20px] text-[#08284B]'));

if ($card === []) {
    return;
}

$title = (string) ($card['title'] ?? '');
$href = (string) ($card['thumbnail_href'] ?? $card['permalink'] ?? '#');
$target = (string) ($card['thumbnail_target'] ?? '_self');
$rel = (string) ($card['thumbnail_rel'] ?? '');
$image_id = (int) ($card['image_id'] ?? 0);
$image_alt = (string) ($card['image_alt'] ?? $title);
$use_webinar_placeholder = ! empty($card['use_webinar_placeholder']);
?>

<a
    href="<?php echo esc_url($href); ?>"
    <?php if ($target === '_blank') { ?>
        target="_blank"
        rel="<?php echo esc_attr($rel !== '' ? $rel : 'noopener noreferrer'); ?>"
    <?php } ?>
    class="block overflow-hidden rounded-[4px] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
>
    <?php if ($use_webinar_placeholder) { ?>
        <?php
        get_template_part('template-parts/single/partials/webinar-single-placeholder', null, [
            'context' => 'related',
            'title' => $title,
        ]);
        ?>
    <?php } elseif ($image_id > 0) { ?>
        <?php
        echo wp_get_attachment_image($image_id, 'medium_large', false, [
            'class' => $image_class,
            'alt' => $image_alt !== '' ? $image_alt : $title,
        ]);
        ?>
    <?php } else { ?>
        <div class="<?php echo esc_attr($placeholder_class); ?>" role="img" aria-label="<?php echo esc_attr($title); ?>">
            <?php echo esc_html($title); ?>
        </div>
    <?php } ?>
</a>
