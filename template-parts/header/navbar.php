<?php
/**
 * Navbar
 * - Removes all Vue attributes
 * - Uses Navi for menus
 * - Keeps Alpine store and template parts
 * - Buttons + search controlled via ACF options
 */

$logo_id  = get_theme_mod('custom_logo');
$logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : '';
$logo_alt = $logo_id ? get_post_meta($logo_id, '_wp_attachment_image_alt', true) : get_bloginfo('name');

$nav_settings      = get_field('navigation_settings_start', 'option') ?: [];
$enable_search     = ! empty($nav_settings['enable_search']);
$help_btn          = $nav_settings['looking_help_button'] ?? null;   // ACF link array
$referral_btn      = $nav_settings['referral_button'] ?? null;       // ACF link array

// Map dropdown images to menu item IDs
$dropdown_image_map = [];
if (! empty($nav_settings['dropdown_images'])) {
  foreach ($nav_settings['dropdown_images'] as $row) {
    $mid = $row['menu_item'] ?? null;
    $img = $row['image']     ?? null;
    if ($mid && ! empty($img['url'])) {
      $dropdown_image_map[(int) $mid] = $img;
    }
  }
}

use Log1x\Navi\Navi;
$primary_navigation = Navi::make()->build('primary');
?>

<!-- Alpine store (once) -->
<script>
document.addEventListener('alpine:init', () => {
  if (!Alpine.store('nav')) Alpine.store('nav', { open: false });
});
</script>

<section
  id="site-nav"
  x-data
  x-init="window.addEventListener('resize', () => { if (window.innerWidth >= 1200) { $store.nav.open = false } })"
  x-effect="$store.nav.open ? document.body.style.overflow='hidden' : document.body.style.overflow=''"
  class="bg-white"
  role="banner"
>
  <?php get_template_part('template-parts/header/topbar'); ?>

  <!-- WHITE BAR -->
  <nav
  class="box-border flex relative justify-between items-center p-6 mx-auto w-full bg-white shadow-x font-primary max-md:p-5 max-sm:p-4 max-w-container"
  role="navigation"
  aria-label="Main navigation"
>
  <!-- Logo -->
  <div class="flex items-center">
    <a href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?> - Go to homepage">
      <?php if ($logo_url) : ?>
        <img
          src="<?php echo esc_url($logo_url); ?>"
          alt="<?php echo esc_attr($logo_alt); ?>"
          class="h-[46px] w-[180px] object-contain max-md:w-40 max-md:h-[41px] max-sm:h-9 max-sm:w-[140px]"
        />
      <?php else : ?>
        <span class="text-xl font-bold text-slate-800"><?php echo esc_html(get_bloginfo('name')); ?></span>
      <?php endif; ?>
    </a>
  </div>

  <!-- Desktop Navigation -->
  <?php if ($primary_navigation->isNotEmpty()) : ?>
    <ul id="primary-menu" class="hidden gap-4 items-center lg:flex" role="menubar">
      <?php foreach ($primary_navigation->toArray() as $index => $item) : ?>
        <li class="relative group <?php echo esc_attr($item->classes); ?> <?php echo $item->active ? 'current-item' : ''; ?>" role="none">
          <div class="flex gap-1 items-center">
            <a
              href="<?php echo esc_url($item->url); ?>"
              class="flex gap-1 items-center text-sm font-semibold leading-5 rounded text-teal-950 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
              role="menuitem"
              aria-haspopup="<?php echo $item->children ? 'true' : 'false'; ?>"
              aria-expanded="false"
            >
              <span><?php echo esc_html($item->label); ?></span>

              <?php if ($item->children) : ?>
                <!-- Chevron icon (style-only change) -->
                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true">
                  <path d="M2 4L6 8L10 4" stroke="black" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              <?php endif; ?>
            </a>
          </div>

          <?php if ($item->children) : ?>
            <?php get_template_part('template-parts/header/navbar/dropdown', null, [
              'item'   => $item,
              'index'  => $index,
              'images' => $dropdown_image_map
            ]); ?>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <!-- Right Side: Search + Buttons + Mobile trigger -->
  <div class="flex gap-4 items-center">
    <!-- Search -->
    <?php if ($enable_search) : ?>
      <button
        type="button"
        class="flex items-center justify-center w-[31px] h-[31px] p-1.5 rounded-[15.5px] hover:bg-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
        aria-label="Open search"
      >
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <path d="M11 19C15.4183 19 19 15.4183 19 11C19 6.58172 15.4183 3 11 3C6.58172 3 3 6.58172 3 11C3 15.4183 6.58172 19 11 19Z" stroke="#001F33" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M20.9999 21L16.6499 16.65" stroke="#001F33" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
    <?php endif; ?>

    <!-- Looking for help -->
    <?php if (!empty($help_btn['url']) && !empty($help_btn['title'])) : ?>
      <a
        href="<?php echo esc_url($help_btn['url']); ?>"
        target="<?php echo esc_attr($help_btn['target'] ?: '_self'); ?>"
        class="hidden gap-2 items-center px-3 h-9 bg-sky-900 rounded transition-colors sm:flex hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
        aria-label="<?php echo esc_attr($help_btn['title']); ?>"
      >
        <span class="text-sm font-medium leading-6 text-white">
          <?php echo esc_html($help_btn['title']); ?>
        </span>
      </a>
    <?php endif; ?>

    <!-- Make a referral -->
    <?php if (!empty($referral_btn['url']) && !empty($referral_btn['title'])) : ?>
      <a
        href="<?php echo esc_url($referral_btn['url']); ?>"
        target="<?php echo esc_attr($referral_btn['target'] ?: '_self'); ?>"
        class="flex items-center px-3 h-9 rounded border border-sky-900 transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
        role="button"
        aria-label="<?php echo esc_attr($referral_btn['title']); ?>"
      >
        <span class="text-sm font-medium leading-6 text-teal-950">
          <?php echo esc_html($referral_btn['title']); ?>
        </span>
      </a>
    <?php endif; ?>

    <!-- Mobile / off-canvas (unchanged placement) -->
    <?php get_template_part('template-parts/header/navbar/mobile'); ?>
  </div>
</nav>
</section>
