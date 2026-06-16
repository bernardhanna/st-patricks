<?php
$gradient_solid = (string) ($args['gradient_solid'] ?? '');
$gradient_soft = (string) ($args['gradient_soft'] ?? '');
$gradient_clear = (string) ($args['gradient_clear'] ?? '');
$background_color = (string) ($args['background_color'] ?? '');
$layout = (string) ($args['layout'] ?? 'split');
?>
<?php if ($layout === 'stacked') { ?>
    <div
        class="absolute inset-0 pointer-events-none"
        style="background: <?php echo esc_attr(matrix_build_hero_image_split_stacked_image_gradient($gradient_clear, $gradient_soft, $gradient_solid)); ?>;"
        aria-hidden="true"
    ></div>
<?php } else { ?>
    <div
        class="absolute inset-0 pointer-events-none lg:hidden"
        style="background: <?php echo esc_attr(matrix_build_hero_image_split_mobile_bottom_fade_gradient($gradient_clear, $gradient_soft, $gradient_solid)); ?>;"
        aria-hidden="true"
    ></div>
    <div
        class="absolute inset-0 pointer-events-none max-lg:hidden"
        style="background: linear-gradient(90deg, <?php echo esc_attr($gradient_solid); ?> 0%, <?php echo esc_attr($gradient_soft); ?> 14.69%, <?php echo esc_attr($gradient_clear); ?> 45.97%);"
        aria-hidden="true"
    ></div>
    <div
        class="hidden absolute inset-y-0 right-0 w-1/3 pointer-events-none xl:block"
        style="background: linear-gradient(to right, transparent, <?php echo esc_attr($background_color); ?>);"
        aria-hidden="true"
    ></div>
<?php } ?>
