<?php

$title = (string) ($args['title'] ?? '');
$show_portal_cta = (bool) ($args['show_portal_cta'] ?? false);
$portal_cta = $args['portal_cta'] ?? null;
?>

<div class="relative z-[1] flex w-[232px] shrink-0 flex-col gap-12">
    <div>
        <h2 class="font-primary text-[30px] font-semibold leading-9 tracking-[-0.225px] text-[#1E244B]">
            <?php echo esc_html($title); ?>
        </h2>
        <?php matrix_render_nav_mega_menu_heading_underline(); ?>
    </div>

    <?php if ($show_portal_cta && is_array($portal_cta) && ! empty($portal_cta['url']) && ! empty($portal_cta['title'])) : ?>
        <a
            href="<?php echo esc_url($portal_cta['url']); ?>"
            target="<?php echo esc_attr($portal_cta['target'] ?? '_self'); ?>"
            class="<?php echo esc_attr(matrix_get_nav_mega_menu_cta_class_names()); ?>"
        >
            <?php echo esc_html($portal_cta['title']); ?>
        </a>
    <?php endif; ?>
</div>
