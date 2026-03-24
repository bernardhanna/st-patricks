<?php
// File: inc/enqueue-scripts.php

function matrix_starter_enqueue_scripts()
{
  $theme_version = get_option('theme_css_version', '1.0');

  // Ensure jQuery is present early
  wp_enqueue_script('jquery');

  // Dev/prod assets
  $is_dev = defined('WP_ENV') && WP_ENV === 'development';
  $base   = get_template_directory_uri();

  $app_js  = $is_dev ? '/wp-content/themes/matrix-starter/dist/app.js'  : $base . '/dist/app.js';
  $app_css = $is_dev ? '/wp-content/themes/matrix-starter/dist/app.css' : $base . '/dist/app.css';

  // Main bundle (footer), depends on jQuery in case you use it inside
  wp_enqueue_script('matrix-starter', $app_js, ['jquery'], '1.0.0', true);
  wp_enqueue_style('matrix-starter', $app_css, [], $theme_version);

  // --- ✅ Alpine.js (CDN) + Intersect plugin ---
  wp_enqueue_script(
    'alpine-intersect',
    'https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js',
    [],
    null,
    true
  );
  wp_enqueue_script(
    'alpine',
    'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js',
    ['alpine-intersect'],
    null,
    true
  );
  // Register the plugin with Alpine on init
  wp_add_inline_script('alpine', "document.addEventListener('alpine:init',()=>{ if (window.Alpine && window.AlpineIntersect) Alpine.plugin(window.AlpineIntersect); });");

  // Optional: prevent flash of cloaked content
  wp_add_inline_style('matrix-starter', '[x-cloak]{display:none !important;}');

  // Theme forms (footer)
  wp_enqueue_script(
    'theme-forms',
    $base . '/inc/forms/js/forms.js',
    ['jquery'],
    filemtime(get_template_directory() . '/inc/forms/js/forms.js'),
    true
  );

  if ( get_field('enable_recaptcha', 'option') ) {
    $key = get_field('recaptcha_site_key', 'option');
    wp_enqueue_script( 'recaptcha', "https://www.google.com/recaptcha/api.js?render={$key}", [], null, true );
    wp_add_inline_script( 'theme-forms', "window.themeFormsRecaptchaV3 = '{$key}';", 'before' );
  }

  // Fonts
  wp_enqueue_style(
    'ubuntu',
    'https://fonts.googleapis.com/css2?family=League+Spartan:wght@100..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap',
    [],
    null
  );
  wp_enqueue_style(
    'great-fonts',
    'https://fonts.googleapis.com/css2?family=Great+Vibes:wght@400&display=swap',
    [],
    null
  );

  // ---- Register optional third-parties once (CSS/JS) ----
  // We keep everything registered; only enqueue when enabled.
  wp_register_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', [], null);
  wp_register_style('hamburgers-css', 'https://cdnjs.cloudflare.com/ajax/libs/hamburgers/1.2.1/hamburgers.min.css', [], '1.2.1');
  wp_register_script('flowbite', 'https://unpkg.com/flowbite@1.6.5/dist/flowbite.min.js', ['alpine'], '1.6.5', true);
  wp_register_style('slick-css', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css', [], '1.8.1');
  wp_register_script('slick-js', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', ['jquery'], '1.8.1', true);
  wp_register_script('headroom', 'https://cdnjs.cloudflare.com/ajax/libs/headroom/0.12.0/headroom.min.js', [], '0.12.0', true);

  // 🌍 Leaflet (OpenStreetMap) — the two "new scripts" (CSS+JS) registered here:
  wp_register_style('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', [], '1.9.4');
  wp_register_script('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', [], '1.9.4', true);

  // ---- Conditionally enqueue based on Theme Options ----
  $enabled_scripts = get_field('enabled_scripts', 'option');
  if (is_array($enabled_scripts)) {
    if (in_array('font_awesome', $enabled_scripts, true)) {
      wp_enqueue_style('font-awesome');
    }
    if (in_array('hamburger_css', $enabled_scripts, true)) {
      wp_enqueue_style('hamburgers-css');
    }
    if (in_array('flowbite', $enabled_scripts, true)) {
      // If your Flowbite usage is Alpine-free you can remove 'alpine' dep; leaving it is safe.
      wp_enqueue_script('flowbite');
    }
    if (in_array('slick', $enabled_scripts, true)) {
      wp_enqueue_style('slick-css');
      wp_enqueue_script('slick-js');
    }
    if (in_array('headroom', $enabled_scripts, true)) {
      wp_enqueue_script('headroom');
      wp_add_inline_script('headroom', "
        document.addEventListener('DOMContentLoaded', function() {
          var header = document.querySelector('#site-nav');
          if (!header || typeof Headroom === 'undefined') return;
          header.classList.add('fixed','top-0','left-0','w-full','z-50','transition-transform','duration-300','ease-in-out','translate-y-0');
          var headroom = new Headroom(header, { tolerance: 5, offset: 100 });
          headroom.onPin   = function(){ header.classList.remove('-translate-y-full'); header.classList.add('translate-y-0'); };
          headroom.onUnpin = function(){ header.classList.remove('translate-y-0'); header.classList.add('-translate-y-full'); };
          headroom.init();
        });
      ");
    }
    // ✅ NEW: Leaflet can be force-enabled globally from options
    if (in_array('leaflet', $enabled_scripts, true)) {
      wp_enqueue_style('leaflet');
      wp_enqueue_script('leaflet');
    }
  }

  // Woo fragments
  if (class_exists('WooCommerce')) {
    wp_enqueue_script('wc-cart-fragments');
  }

  // Defer only non-critical scripts. Never defer jQuery or Alpine.
  add_filter('script_loader_tag', function ($tag, $handle) {
    $no_defer = [
      'jquery','jquery-core','jquery-migrate',
      'matrix-starter','theme-forms',
      'wc-cart-fragments','woocommerce',
      'recaptcha',
      'alpine-intersect','alpine', // ✅ keep Alpine immediate
    ];
    if (in_array($handle, $no_defer, true)) return $tag;
    if (strpos($tag, ' src=') !== false) {
      return str_replace(' src', ' defer src', $tag);
    }
    return $tag;
  }, 10, 2);
}
add_action('wp_enqueue_scripts', 'matrix_starter_enqueue_scripts', 20);

// Keep jQuery in header group so it prints early if needed
add_action('wp_enqueue_scripts', function () {
  if (!is_admin()) {
    wp_scripts()->add_data('jquery', 'group', 0);
    wp_scripts()->add_data('jquery-core', 'group', 0);
    wp_scripts()->add_data('jquery-migrate', 'group', 0);
  }
}, 5);
