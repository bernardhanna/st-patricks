<?php if ( class_exists('WooCommerce') ) : ?>
  <?php
  // Fallback values for native Woo cart (if shortcode not available)
  $cart_url = wc_get_cart_url();
  $count    = ( function_exists('WC') && WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0;

  // Helper: does the XootiX side cart shortcode exist?
  $has_sidecart = function_exists('shortcode_exists') && shortcode_exists('xoo_wsc_cart');
  ?>
  <div class="flex relative items-center woocommerce-cart-wrapper group" aria-live="polite" aria-atomic="true">
    <?php if ( $has_sidecart ) : ?>

      <?php
      // ✅ Preferred: Use Side Cart WooCommerce (XootiX) icon
      // This outputs the plugin’s toggle + live count and handles opening the side cart.
      echo do_shortcode('[xoo_wsc_cart]');
      ?>

    <?php else : ?>

      <!-- ❌ Side cart not available – fallback to native Woo link -->
      <a
        href="<?php echo esc_url($cart_url); ?>"
        class="flex relative justify-center items-center text-sm font-semibold rounded border border-none transition xl:px-4 xl:py-2 text-primary xl:border-primary xl:hover:bg-primary hover:text-white hover:border-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
        aria-label="View your shopping cart"
      >
        <svg class="xl:mr-2 h-[26px] w-[53px] stroke-black md:stroke-white" fill="none" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 7M7 13l-2 6h14M10 21h.01M18 21h.01" />
        </svg>

        <span class="sr-only">Cart</span>

        <span
          class="woocommerce-cart-count <?php echo $count > 0 ? 'inline-flex justify-center items-center py-1 font-bold leading-none text-white rounded-full lg:px-2 lg:ml-2 bg-secondary' : 'hidden'; ?>"
        >
          <?php echo $count > 0 ? esc_html($count) : ''; ?>
        </span>
      </a>

    <?php endif; ?>
  </div>
<?php endif; ?>
