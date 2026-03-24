<?php
/**
 * Cart Page (Flex/Grid & Plus/Minus version)
 *
 * Overrides:
 * wp-content/plugins/woocommerce/templates/cart/cart.php
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.1.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' );
?>

<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
  <?php do_action( 'woocommerce_before_cart_table' ); ?>

  <div class="flex flex-col space-y-4 woocommerce-cart-grid">

    <!-- Header Row -->

    <?php do_action( 'woocommerce_before_cart_contents' ); ?>

    <?php foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
      $_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
      $product_id   = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
      $product_name = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );

      if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 || ! apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
        continue;
      }

      $permalink = apply_filters( 'woocommerce_cart_item_permalink',
        $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '',
        $cart_item,
        $cart_item_key
      );
    ?>
      <!-- Item Row -->
      <div class="flex items-center p-4 bg-white rounded-none shadow-sm cart-row cart-item">
        <!-- Remove -->
        <div class="w-1/12 text-center">
          <?php
            echo apply_filters( 'woocommerce_cart_item_remove_link',
              sprintf(
                '<a href="%s" class="inline-block w-8 h-8 text-sm font-bold leading-8 text-center text-white bg-primary rounded-full hover:text-white hover:bg-primary" aria-label="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
                esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                esc_attr( sprintf( __( 'Remove %s from cart', 'woocommerce' ), wp_strip_all_tags( $product_name ) ) ),
                esc_attr( $product_id ),
                esc_attr( $_product->get_sku() )
              ),
              $cart_item_key
            );
          ?>
        </div>

        <!-- Thumbnail -->
        <div class="flex justify-center w-2/12">
          <?php
            $thumb = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
            echo $permalink
              ? sprintf( '<a href="%s">%s</a>', esc_url( $permalink ), $thumb )
              : $thumb;
          ?>
        </div>

        <!-- Name & Meta -->
        <div class="w-3/12 text-center">
          <?php
            if ( $permalink ) {
              printf( '<a href="%s" class="font-medium hover:underline">%s</a>', esc_url( $permalink ), esc_html( $product_name ) );
            } else {
              echo '<span class="font-medium">' . esc_html( $product_name ) . '</span>';
            }
            echo wc_get_formatted_cart_item_data( $cart_item );
            if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
              echo '<p class="text-sm text-yellow-600">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>';
            }
          ?>
        </div>

        <!-- Price -->
        <div class="w-1/12 text-center">
          <?php
            echo apply_filters( 'woocommerce_cart_item_price',
              WC()->cart->get_product_price( $_product ),
              $cart_item,
              $cart_item_key
            );
          ?>
        </div>

        <!-- Quantity (plus/minus) -->
        <div class="w-2/12 text-center">
          <div class="inline-flex overflow-hidden items-center rounded-none border border-gray-300">
            <button type="button" class="px-3 py-1 qty-decrease" aria-label="<?php esc_attr_e( 'Decrease quantity', 'woocommerce' ); ?>">
              &minus;
            </button>
            <?php
              $input = woocommerce_quantity_input( [
                'input_name'   => "cart[{$cart_item_key}][qty]",
                'input_value'  => $cart_item['quantity'],
                'max_value'    => $_product->get_max_purchase_quantity(),
                'min_value'    => 0,
                'product_name' => $product_name,
                'classes'      => [ 'qty', 'qty-input', 'w-12', 'text-center', 'outline-none', 'border-t', 'border-b', 'border-gray-300' ],
              ], $_product, false );
              echo apply_filters( 'woocommerce_cart_item_quantity', $input, $cart_item_key, $cart_item );
            ?>
            <button type="button" class="px-3 py-1 qty-increase" aria-label="<?php esc_attr_e( 'Increase quantity', 'woocommerce' ); ?>">
              &plus;
            </button>
          </div>
        </div>

        <!-- Subtotal -->
        <div class="w-1/12 text-center">
          <?php
            echo apply_filters( 'woocommerce_cart_item_subtotal',
              WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ),
              $cart_item,
              $cart_item_key
            );
          ?>
        </div>
      </div>
    <?php endforeach; ?>

    <?php do_action( 'woocommerce_cart_contents' ); ?>

    <!-- Actions Row -->
    <div class="flex flex-col justify-between items-center space-y-4 cart-row cart-actions md:flex-row md:space-y-0">
      <?php if ( wc_coupons_enabled() ) : ?>
        <div class="flex items-center space-x-2 coupon">
          <label for="coupon_code" class="sr-only"><?php esc_html_e( 'Coupon:', 'woocommerce' ); ?></label>
          <input
            type="text"
            name="coupon_code"
            id="coupon_code"
            class="px-3 py-2 rounded-none border border-gray-300 input-text"
            placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>"
          />
          <button
            type="submit"
            name="apply_coupon"
            value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"
            class="block px-6 py-3 text-center text-white rounded-none border-2 border-slate-900 bg-slate-900"
          >
            <?php esc_html_e( 'Apply coupon', 'woocommerce' ); ?>
          </button>
          <?php do_action( 'woocommerce_cart_coupon' ); ?>
        </div>
      <?php endif; ?>

      <div class="flex space-x-4">
        <button
          type="submit"
          name="update_cart"
          value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>"
          class="block px-6 py-3 text-center text-white rounded-none border-2 border-slate-900 bg-slate-900"
        >
          <?php esc_html_e( 'Update cart', 'woocommerce' ); ?>
        </button>
        <?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
        <?php do_action( 'woocommerce_cart_actions' ); ?>
      </div>
    </div>

  <script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.querySelector('.woocommerce-cart-form');

  form.querySelectorAll('.qty-decrease, .qty-increase').forEach(button => {
    button.addEventListener('click', function () {
      const wrapper = this.closest('.inline-flex');
      if (!wrapper) return;

      const input = wrapper.querySelector('input.qty');
      if (!input) return;

      const min = parseInt(input.getAttribute('min')) || 0;
      const maxAttr = input.getAttribute('max');
      const max = maxAttr ? parseInt(maxAttr) : Infinity;
      let currentVal = parseInt(input.value) || 0;

      if (this.classList.contains('qty-decrease') && currentVal > min) {
        input.value = currentVal - 1;
      }

      if (this.classList.contains('qty-increase') && currentVal < max) {
        input.value = currentVal + 1;
      }

      // 🔁 Force WooCommerce to recognize the change
      input.dispatchEvent(new Event('change', { bubbles: true }));

      // 🔁 Small delay before auto-submit
      setTimeout(() => {
        form.querySelector('[name="update_cart"]').click();
      }, 100);
    });
  });
});
  </script>

  </div><!-- /.woocommerce-cart-grid -->

  <?php do_action( 'woocommerce_after_cart_table' ); ?>
</form>

<?php do_action( 'woocommerce_before_cart_collaterals' ); ?>

<div class="mt-8 cart-collaterals">
  <?php do_action( 'woocommerce_cart_collaterals' ); ?>
</div>

<?php do_action( 'woocommerce_after_cart' ); ?>
