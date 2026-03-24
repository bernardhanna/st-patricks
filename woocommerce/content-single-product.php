<?php
defined('ABSPATH') || exit;

global $product;

/**
 * Keep Woo’s standard wrappers so plugins remain compatible,
 * but we won’t output the default inner hooks we removed above.
 */

do_action('woocommerce_before_single_product');

if (post_password_required()) {
  echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
  return;
}
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class('', $product); ?>>

  <?php
  // Our custom builder section (full layout incl. images + summary)
  if (function_exists('mytheme_render_single_product_builder')) {
    mytheme_render_single_product_builder();
  } else {
    // Fallback to default Woo if builder not loaded
    do_action('woocommerce_before_single_product_summary');
    ?>
    <div class="summary entry-summary">
      <?php do_action('woocommerce_single_product_summary'); ?>
    </div>
    <?php
    do_action('woocommerce_after_single_product_summary');
  }
  ?>

</div>

<?php do_action('woocommerce_after_single_product'); ?>
