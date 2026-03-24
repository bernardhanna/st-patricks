<?php
/**
 * inc/woocommerce.php
 * - Admin notice when Woo toggle is ON but WooCommerce is missing
 * - Gates all Woo customizations behind the theme option + plugin active
 * - Loads single-product builder hooks
 * - Disables default Woo styles (optional blocks dequeue)
 * - Declares theme support for Woo gallery features
 * - Applies option-driven removals for shop/single/cart/checkout
 * - Injects page hero for Shop/Product/Cart (respects per-post `hide_hero` toggle)
 * - Safe script handling (no admin defer; Woo scripts left alone)
 */

// 1) Admin notice: toggle ON but Woo not active
add_action('admin_notices', function () {
    if (
        function_exists('get_field') &&
        get_field('enable_woocommerce', 'option') &&
        ! class_exists('WooCommerce') &&
        current_user_can('install_plugins')
    ) {
        $install_url = esc_url(wp_nonce_url(
            self_admin_url('update.php?action=install-plugin&plugin=woocommerce'),
            'install-plugin_woocommerce'
        ));
        echo '<div class="notice notice-warning is-dismissible"><p>';
        printf(
            esc_html__('WooCommerce support is enabled in Theme Options, but WooCommerce isn’t active. %s.', 'matrix-starter'),
            '<a href="' . $install_url . '">' . esc_html__('Install & Activate WooCommerce', 'matrix-starter') . '</a>'
        );
        echo '</p></div>';
    }
});

// 2) Bail early if disabled or Woo missing
if (! function_exists('get_field') || ! get_field('enable_woocommerce', 'option') || ! class_exists('WooCommerce')) {
    return;
}

// 2.5) Load builder hooks (removals + render). This file just wires actions.
$hooks = get_theme_file_path('/inc/woocommerce-single-builder.php');
if (file_exists($hooks)) {
    require_once $hooks;
} else {
    error_log('Missing builder hooks file: ' . $hooks);
}

// 3) Disable Woo styles (optional)
add_filter('woocommerce_enqueue_styles', '__return_empty_array');
add_action('wp_enqueue_scripts', function () {
    // Remove Woo Blocks CSS if you’re not using them
    wp_dequeue_style('wc-blocks-style');
    wp_dequeue_style('wc-blocks-vendors-style');
}, 100);

// 4) Theme support
add_action('after_setup_theme', function () {
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
});

// 5) Option-driven removals via template_redirect
add_action('template_redirect', function () {
    if (! function_exists('get_field') || ! class_exists('WooCommerce')) {
        return;
    }

    // Shop
    if (is_shop()) {
        if (get_field('hide_shop_breadcrumbs', 'option')) {
            remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
        }
        if (get_field('hide_result_count', 'option')) {
            remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
        }
        if (get_field('hide_catalog_ordering', 'option')) {
            remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
        }
        if (get_field('hide_shop_sidebar', 'option')) {
            remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
        }
    }

    // Single Product
    if (is_product()) {
        if (get_field('hide_product_sidebar', 'option')) {
            remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
        }
        if (get_field('disable_reviews', 'option')) {
            add_filter('comments_open', '__return_false', 20, 2);
            remove_action('woocommerce_after_single_product_summary', 'comments_template', 50);
        }
        if (get_field('hide_product_breadcrumbs', 'option')) {
            remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
        }
        if (get_field('hide_related_products', 'option')) {
            remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
        }
        if (get_field('hide_upsells', 'option')) {
            remove_action('woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15);
        }
    }

    // Cart
    if (is_cart()) {
        if (get_field('hide_cart_cross_sells', 'option')) {
            remove_action('woocommerce_cart_collaterals', 'woocommerce_cross_sell_display');
        }
    }

    // Checkout
    if (is_checkout()) {
        if (get_field('hide_checkout_coupon_form', 'option')) {
            remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);
        }
        if (get_field('hide_checkout_login_form', 'option')) {
            remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10);
        }
    }
});

// 7) AJAX cart count fragment
add_filter('woocommerce_add_to_cart_fragments', function ($fragments) {
    $count = (function_exists('WC') && WC()->cart) ? (int) WC()->cart->get_cart_contents_count() : 0;

    ob_start(); ?>
    <span
      class="woocommerce-cart-count <?php echo $count > 0 ? 'inline-flex justify-center items-center py-1 font-bold leading-none text-white rounded-full lg:px-2 lg:ml-2 bg-primary' : 'hidden'; ?>"
      aria-live="polite"
      aria-atomic="true"
    >
      <?php echo $count > 0 ? esc_html($count) : ''; ?>
    </span>
    <?php

    $fragments['.woocommerce-cart-count'] = ob_get_clean();
    return $fragments;
});
// 8) WooCommerce cart fragments (front-end only)
add_action('wp_enqueue_scripts', function () {
    if (! class_exists('WooCommerce')) {
        return;
    }
    wp_enqueue_script('wc-cart-fragments');
}, 20);

// 9) Safe script deferring (never touch admin; keep Woo scripts as-is)
add_filter('script_loader_tag', function ($tag, $handle) {
    if (is_admin()) {
        return $tag;
    }
    if (in_array($handle, ['wc-cart-fragments', 'woocommerce'], true)) {
        return $tag;
    }
    return str_replace(' src', ' defer src', $tag);
}, 10, 2);

// 10) Add placeholders to checkout fields
add_filter('woocommerce_checkout_fields', function ($fields) {
    foreach ($fields as $section => &$section_fields) {
        foreach ($section_fields as $key => &$field) {
            if (! isset($field['placeholder']) && isset($field['label'])) {
                $field['placeholder'] = $field['label'];
            }
        }
    }
    return $fields;
});

// Shop hero
add_action('woocommerce_before_main_content', 'matrix_add_shop_hero_section', 5);
function matrix_add_shop_hero_section()
{
    if (!is_shop()) return;

    // Global option: Hide shop hero
    if (function_exists('get_field') && get_field('hide_shop_hero', 'option')) return;

    $shop_id = wc_get_page_id('shop');
    if ($shop_id && get_post_status($shop_id)) {
        // Per-page toggle
        if (function_exists('get_field') && get_field('hide_hero', $shop_id)) return;

        global $post;
        $original_post = $post;
        $post = get_post($shop_id);
        setup_postdata($post);

        get_template_part('template-parts/page/hero');

        wp_reset_postdata();
        $post = $original_post;
    }
}

// Single product hero
add_action('woocommerce_before_single_product', 'matrix_add_product_hero_section', 5);
function matrix_add_product_hero_section()
{
    if (!is_product()) return;

    // Global option: Hide product hero
    if (function_exists('get_field') && get_field('hide_product_hero', 'option')) return;

    $product_id = get_the_ID();
    // Per-product toggle
    if (function_exists('get_field') && get_field('hide_hero', $product_id)) return;

    setup_postdata(get_post($product_id));
    get_template_part('template-parts/page/hero');
    wp_reset_postdata();
}

// Sidebar for filters in Shop
add_action('widgets_init', function() {
  register_sidebar([
    'name'          => __('Shop Filters', 'matrix-starter'),
    'id'            => 'shop-filters',
    'description'   => __('Widgets in this area will show in the shop filters sidebar.', 'matrix-starter'),
    'before_widget' => '<div id="%1$s" class="widget %2$s">',
    'after_widget'  => '</div>',
    'before_title'  => '<h3 class="mb-2 text-base font-bold leading-4 text-zinc-900">',
    'after_title'   => '</h3>',
  ]);
});

// (Optional) Products per page (matches your "15" UI)
add_filter('loop_shop_per_page', function($cols) {
  return 15;
}, 20);

// (Optional) Remove default wrappers if needed (we used our own)
remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

// Cart hero
add_action('woocommerce_before_main_content', 'matrix_add_cart_hero_section', 5);
function matrix_add_cart_hero_section()
{
    if (!is_cart()) return;

    // Global option: Hide cart hero
    if (function_exists('get_field') && get_field('hide_cart_hero', 'option')) return;

    $cart_id = wc_get_page_id('cart');
    if ($cart_id && get_post_status($cart_id)) {
        // Per-page toggle
        if (function_exists('get_field') && get_field('hide_hero', $cart_id)) return;

        global $post;
        $original_post = $post;
        $post = get_post($cart_id);
        setup_postdata($post);

        get_template_part('template-parts/page/hero');

        wp_reset_postdata();
        $post = $original_post;
    }
}

// Force 3 columns on shop/archive pages
add_filter('loop_shop_columns', function ($cols) {
  return 3;
}, 999);