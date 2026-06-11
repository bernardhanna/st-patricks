<?php
$gradient_solid = (string) ($args['gradient_solid'] ?? '');
$gradient_soft = (string) ($args['gradient_soft'] ?? '');
$gradient_clear = (string) ($args['gradient_clear'] ?? '');
$background_color = (string) ($args['background_color'] ?? '');
?>
<div
    class="absolute inset-0 pointer-events-none lg:hidden"
    style="background: linear-gradient(to bottom, <?php echo esc_attr($gradient_clear); ?> 0%, <?php echo esc_attr($gradient_soft); ?> 55%, <?php echo esc_attr($gradient_solid); ?> 100%);"
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
