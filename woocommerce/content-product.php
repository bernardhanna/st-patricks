<?php
/**
 * Template: Single product in loops (card)
 * Location: your-theme/woocommerce/content-product.php
 */

defined('ABSPATH') || exit;

global $product;
if (empty($product) || !$product->is_visible()) {
  return;
}

$product_id  = $product->get_id();
$permalink   = get_permalink($product_id);
$title       = get_the_title($product_id);
$short_desc  = apply_filters('woocommerce_short_description', $product->get_short_description());
$image_html  = woocommerce_get_product_thumbnail('woocommerce_thumbnail', [
  'class' => 'object-contain w-full h-full',
], 0);

/** Optional voltage badge (attribute pa_voltage or meta "voltage") */
$voltage = '';
// Try product attribute first
$attr_voltage = $product->get_attribute('pa_voltage'); // e.g. '230 VOLT'
if (!empty($attr_voltage)) {
  $voltage = wp_strip_all_tags($attr_voltage);
} else {
  // fallback custom field
  $meta_voltage = get_post_meta($product_id, 'voltage', true);
  if (!empty($meta_voltage)) {
    $voltage = wp_strip_all_tags($meta_voltage);
  }
}

/** Price with “(inc. VAT)” note */
$price_html = $product->get_price_html();
$inc_vat_note = ' ' . esc_html__('(inc. VAT)', 'your-textdomain');

/** Accessible label for ATC */
$aria_label = sprintf(esc_attr__('Add %s to cart', 'your-textdomain'), $title);

/** Capture Woo add-to-cart and replace inner with our SVG (keep markup type intact) */
ob_start();
woocommerce_template_loop_add_to_cart([
  'class' => 'x-add-to-cart inline-flex items-center justify-center w-11 h-11',
  'aria-label' => $aria_label,
]);
$add_to_cart_html = ob_get_clean();

/**
 * Replace inner HTML (between > and < of the first tag) with our SVG + sr-only text.
 * This keeps attributes/URL/nonce intact regardless of product type (link, button, form).
 */
$svg = '
  <span class="sr-only">' . esc_html($aria_label) . '</span>
  <svg width="44" height="44" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" class="pointer-events-none">
    <circle opacity="0.1" cx="22" cy="22" r="22" fill="#ED1C24"></circle>
    <path d="M14.153 14.0001L16.01 25.146C16.0439 25.3795 16.1598 25.5932 16.3369 25.749C16.5238 25.9148 16.7662 26.0043 17.0159 26H27.9998C28.2098 26 28.4144 25.934 28.5848 25.8112C28.7551 25.6885 28.8824 25.5152 28.9488 25.316L31.9488 16.3161C31.9988 16.1658 32.0125 16.0057 31.9885 15.8491C31.9646 15.6924 31.9038 15.5437 31.8111 15.4152C31.7184 15.2867 31.5965 15.182 31.4555 15.1099C31.3144 15.0377 31.1582 15.0001 30.9998 15.0001H16.3469L15.99 12.8501C15.9533 12.6045 15.8259 12.3815 15.633 12.2252C15.4501 12.0757 15.2201 11.996 14.984 12.0002H13C12.7348 12.0002 12.4804 12.1055 12.2929 12.293C12.1054 12.4806 12 12.7349 12 13.0001C12 13.2654 12.1054 13.5197 12.2929 13.7072C12.4804 13.8948 12.7348 14.0001 13 14.0001H14.153ZM17.8469 24L16.6799 17.0001H29.6128L27.2788 24H17.8469ZM19.9999 29.9999C19.9999 30.5304 19.7892 31.0391 19.4141 31.4141C19.0391 31.7892 18.5304 31.9999 17.9999 31.9999C17.4695 31.9999 16.9608 31.7892 16.5857 31.4141C16.2107 31.0391 16 30.5304 16 29.9999C16 29.4695 16.2107 28.9608 16.5857 28.5857C16.9608 28.2107 17.4695 28 17.9999 28C18.5304 28 19.0391 28.2107 19.4141 28.5857C19.7892 28.9608 19.9999 29.4695 19.9999 29.9999ZM28.9998 29.9999C28.9998 30.5304 28.7891 31.0391 28.414 31.4141C28.0389 31.7892 27.5302 31.9999 26.9998 31.9999C26.4694 31.9999 25.9607 31.7892 25.5856 31.4141C25.2106 31.0391 24.9998 30.5304 24.9998 29.9999C24.9998 29.4695 25.2106 28.9608 25.5856 28.5857C25.9607 28.2107 26.4694 28 26.9998 28C27.5302 28 28.0389 28.2107 28.414 28.5857C28.7891 28.9608 28.9998 29.4695 28.9998 29.9999Z" fill="#ED1C24"></path>
  </svg>';
$add_to_cart_html = preg_replace(
  '#(<(a|button)[^>]*>)(.*?)(</\2>)#si',
  '$1' . $svg . '$4',
  $add_to_cart_html,
  1
);
?>
<article <?php wc_product_class('relative bg-white rounded border-2 border-[#D6DFE4] border-solid', $product); ?> role="article" aria-labelledby="product-'<?php echo esc_attr($product_id); ?>'-title">
  <div class="flex flex-col h-full">
    <!-- Top: Image box (rounded, bordered) with optional voltage badge -->
    <header class="relative overflow-hidden bg-white  h-[200px] w-full max-md:h-[180px]">
      <?php if (!empty($voltage)) : ?>
        <div
          class="flex absolute top-2.5 left-2.5 justify-center items-center w-10 h-10 text-[10px] font-bold leading-3 text-center bg-blue-500 rounded-full text-white z-[2]"
          role="img"
          aria-label="<?php echo esc_attr($voltage); ?> electrical specification"
        >
          <?php echo nl2br(esc_html($voltage)); ?>
        </div>
      <?php endif; ?>

      <a href="<?php echo esc_url($permalink); ?>" class="flex absolute inset-0 justify-center items-center p-3 border-b-2 border-[#D6DFE4] border-solid" aria-label="<?php echo esc_attr($title); ?>">
        <?php
        if ($image_html) {
          // Wrap your image in a fixed box to mimic exact placement
          echo '<div class="relative  h-[188px] w-full  max-md:h-[168px]">'. $image_html .'</div>';
        } else {
          echo sprintf(
            '<img src="%s" alt="%s" class="object-contain w-[188px] h-[188px] max-md:w-[168px] max-md:h-[168px]" />',
            esc_url(wc_placeholder_img_src('woocommerce_thumbnail')),
            esc_attr($title)
          );
        }
        ?>
      </a>
    </header>

    <!-- Middle: Title + (optional) short description -->
    <section class="p-5 mt-4 text-sm leading-6 text-gray-600">
      <h4 id="product-<?php echo esc_attr($product_id); ?>-title" class="mb-2 text-base font-bold leading-5 text-zinc-900">
        <a href="<?php echo esc_url($permalink); ?>" class="hover:underline"><?php echo esc_html($title); ?></a>
      </h4>

      <?php if (!empty($short_desc)) : ?>
        <div class="text-sm leading-6 text-gray-600 line-clamp-3">
          <?php echo wp_kses_post($short_desc); ?>
        </div>
      <?php endif; ?>
    </section>

    <!-- Bottom: Price + Add to cart icon button -->
    <footer class="flex justify-between items-center px-5 pb-2">
      <div class="text-base font-bold leading-5 text-zinc-900">
        <?php echo wp_kses_post($price_html); ?>
        <span class="text-sm font-normal text-gray-600"><?php echo $inc_vat_note; ?></span>
      </div>

      <!-- Icon-only add-to-cart (Woo-logic preserved) -->
      <div class="transition-opacity duration-200 cart-button w-fit hover:opacity-80">
        <?php
          // Add ring/focus styles to the interactive element (button/a)
          // Inject extra classes safely
          $add_to_cart_html = preg_replace(
            '#<(a|button)([^>]*)class="([^"]*)"#i',
            '<$1$2class="$3 btn focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary rounded-full"',
            $add_to_cart_html,
            1
          );
          echo $add_to_cart_html;
        ?>
      </div>
    </footer>
  </div>
</article>
