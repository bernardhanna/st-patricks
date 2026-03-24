<?php
/**
 * Hero  (Flexi Block)
 * - Uses get_sub_field()
 * - Random section id
 * - Outer: <section class="flex overflow-hidden relative">
 * - Inner: container div with padding_settings classes
 * - Tailwind-only (no min-w-[240px], no aspect-* classes)
 */

$section_id      = 'hero-' . ( function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid() );

// Content
$heading_tag     = get_sub_field('heading_tag') ?: 'h1';
$heading_text    = get_sub_field('heading_text') ?: '';
$description     = get_sub_field('description'); // WYSIWYG
$primary_btn     = get_sub_field('primary_button');
$secondary_btn   = get_sub_field('secondary_button');
$hero_image      = get_sub_field('hero_image');

// Design
$bg_color        = get_sub_field('background_color') ?: '#C8E3F7';
$heading_color   = get_sub_field('heading_color') ?: '#2C2C21';
$text_color      = get_sub_field('text_color') ?: '#2C2C21';
$decor_images    = get_sub_field('decor_images');
$show_pager      = (bool) get_sub_field('show_pager');
$pager_count     = (int) (get_sub_field('pager_count') ?: 5);
$pager_active    = (int) (get_sub_field('pager_active_index') ?: 1);

// Layout padding
$padding_classes = [];
if (have_rows('padding_settings')) {
  while (have_rows('padding_settings')) {
    the_row();
    $screen_size    = get_sub_field('screen_size');
    $padding_top    = get_sub_field('padding_top');
    $padding_bottom = get_sub_field('padding_bottom');

    if ($screen_size !== '' && $padding_top !== '' && $padding_top !== null) {
      $padding_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
    }
    if ($screen_size !== '' && $padding_bottom !== '' && $padding_bottom !== null) {
      $padding_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
    }
  }
}

// Sanitize heading tag
$allowed_tags = ['h1','h2','h3','h4','h5','h6','span','p'];
if (! in_array($heading_tag, $allowed_tags, true)) {
  $heading_tag = 'h1';
}

// Helpers for link arrays
$resolve_link = function($link) {
  if (!is_array($link) || empty($link['url'])) {
    return null;
  }
  return [
    'url'    => esc_url($link['url']),
    'title'  => esc_html($link['title'] ?: $link['url']),
    'target' => esc_attr($link['target'] ?: '_self'),
  ];
};
$primary  = $resolve_link($primary_btn);
$secondary= $resolve_link($secondary_btn);

// Hero image attributes
$hero_img_url   = $hero_image['url']   ?? '';
$hero_img_alt   = isset($hero_image['alt']) && $hero_image['alt'] !== '' ? $hero_image['alt'] : 'Hero image';
$hero_img_title = isset($hero_image['title']) && $hero_image['title'] !== '' ? $hero_image['title'] : $hero_img_alt;
?>

<section id="<?php echo esc_attr($section_id); ?>"
         class="flex overflow-hidden relative"
         style="background-color: <?php echo esc_attr($bg_color); ?>;">
  <!-- Background decorative elements -->
  <?php if (!empty($decor_images) && is_array($decor_images)) : ?>
    <div class="absolute inset-0 pointer-events-none">
      <?php foreach ($decor_images as $di):
        $img = $di['image'] ?? null;
        if (!$img || empty($img['url'])) { continue; }

        $pos_classes = trim((string) ($di['position_classes'] ?? 'absolute'));
        $opacity     = (isset($di['opacity']) && $di['opacity'] !== '') ? (float) $di['opacity'] : 0.2;
        $show_md     = !empty($di['show_md']); // if false, always hidden on md?
        $show_lg     = !empty($di['show_lg']);

        // Visibility classes: default hidden; show per toggles
        // - If show_lg: "hidden lg:block"
        // - Else if show_md: "hidden md:block"
        // - Else: "block"
        $visibility  = $show_lg ? 'hidden lg:block' : ($show_md ? 'hidden md:block' : 'block');

        $alt   = $img['alt']   ?: '';
        $title = $img['title'] ?: $alt;
      ?>
        <img src="<?php echo esc_url($img['url']); ?>"
             alt="<?php echo esc_attr($alt); ?>"
             title="<?php echo esc_attr($title); ?>"
             class="<?php echo esc_attr($pos_classes . ' ' . $visibility); ?>"
             style="opacity: <?php echo esc_attr($opacity); ?>;"
             aria-hidden="true" />
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- Inner container -->
  <div class="flex flex-col items-center w-full mx-auto max-w-container pt-5 pb-5 max-lg:px-5 <?php echo esc_attr(implode(' ', $padding_classes)); ?>">

    <!-- Main Hero Content -->
    <div class="relative z-10 flex flex-col lg:flex-row items-center min-h-[440px] px-4 md:px-8 lg:px-16 py-8 lg:py-12 w-full">

      <!-- Left Content -->
      <div class="flex-1 mb-8 max-w-2xl lg:mb-0 lg:pr-8">
        <div class="space-y-6">
          <?php if (!empty($heading_text)) : ?>
            <<?php echo tag_escape($heading_tag); ?>
              class="text-3xl font-bold leading-tight font-heading md:text-4xl lg:text-5xl"
              style="color: <?php echo esc_attr($heading_color); ?>;">
              <?php echo esc_html($heading_text); ?>
            </<?php echo tag_escape($heading_tag); ?>>
          <?php endif; ?>

          <?php if (!empty($description)) : ?>
            <div class="text-lg font-medium leading-relaxed md:text-xl wp_editor"
                 style="color: <?php echo esc_attr($text_color); ?>;">
              <?php echo wp_kses_post($description); ?>
            </div>
          <?php endif; ?>

          <div class="flex flex-col gap-4 sm:flex-row">
            <?php if ($primary): ?>
              <a href="<?php echo $primary['url']; ?>"
                 target="<?php echo $primary['target']; ?>"
                 class="flex gap-2 justify-center items-center px-6 py-3 text-sm font-medium text-white rounded transition-colors bg-secondary hover:bg-opacity-90"
                 aria-label="<?php echo esc_attr($primary['title']); ?>">
                <!-- phone/assist icon -->
                <svg class="w-6 h-6" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                  <path d="M19.414 14.914C21 13.328 22 12 22 10C22 8.88722 21.6624 7.80061 21.0319 6.88369C20.4013 5.96677 19.5075 5.26268 18.4684 4.86442C17.4293 4.46616 16.2938 4.39247 15.212 4.65307C14.1301 4.91367 13.1528 5.4963 12.409 6.32402M19.414 14.914C19.2168 15.1113 18.9826 15.2678 18.7249 15.3745C18.4672 15.4813 18.191 15.5362 17.912 15.5362C17.633 15.5362 17.3568 15.4813 17.0991 15.3745C16.8414 15.2678 16.6072 15.1113 16.41 14.914C16.6235 15.1071 16.7956 15.3416 16.9157 15.6033C17.0358 15.8649 17.1015 16.1483 17.1087 16.4361C17.116 16.7239 17.0646 17.0102 16.9578 17.2776C16.851 17.5449 16.6909 17.7878 16.4873 17.9913C16.2837 18.1949 16.0409 18.355 15.7735 18.4618C15.5062 18.5686 15.2199 18.62 14.9321 18.6128C14.6443 18.6055 14.3609 18.5399 14.0993 18.4198C13.8376 18.2996 13.6031 18.1276 13.41 17.914C13.6074 18.1107 13.7641 18.3443 13.8712 18.6015C13.9782 18.8588 14.0335 19.1346 14.0339 19.4132C14.0342 19.6918 13.9797 19.9678 13.8733 20.2253C13.767 20.4828 13.6109 20.7169 13.414 20.914C13.224 21.1041 12.9976 21.2539 12.7484 21.3546C12.4991 21.4552 12.2322 21.5046 11.9635 21.4997C11.6947 21.4948 11.4297 21.4359 11.1843 21.3263C10.9389 21.2168 10.718 21.0588 10.535 20.862L5 15.5C3.5 14 2 12.3 2 10C2.00022 8.88731 2.33794 7.80083 2.96856 6.88407C3.59917 5.9673 4.49303 5.26338 5.53208 4.86524C6.57112 4.46711 7.7065 4.3935 8.78826 4.65412C9.87002 4.91475 10.8473 5.49737 11.591 6.32502C11.7022 6.42836 11.8484 6.48571 12.0002 6.48552C12.152 6.48534 12.2981 6.42763 12.409 6.32402M19.414 14.914C19.7889 14.539 19.9996 14.0304 19.9996 13.5C19.9996 12.9697 19.7889 12.4611 19.414 12.086L17.533 10.204C17.3092 9.98012 17.0435 9.80251 16.751 9.68133C16.4585 9.56015 16.1451 9.49778 15.8285 9.49778C15.5119 9.49778 15.1985 9.56015 14.906 9.68133C14.6135 9.80251 14.3478 9.98012 14.124 10.204L12.414 11.914C12.0389 12.289 11.5303 12.4996 11 12.4996C10.4697 12.4996 9.96106 12.289 9.586 11.914C9.21106 11.539 9.00043 11.0304 9.00043 10.5C9.00043 9.96969 9.21106 9.46108 9.586 9.08602L12.409 6.32402"
                        stroke="white" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <span><?php echo $primary['title']; ?></span>
              </a>
            <?php endif; ?>

            <?php if ($secondary): ?>
              <a href="<?php echo $secondary['url']; ?>"
                 target="<?php echo $secondary['target']; ?>"
                 class="px-6 py-3 text-sm font-medium rounded transition-colors bg-primary text-secondary hover:bg-opacity-90"
                 aria-label="<?php echo esc_attr($secondary['title']); ?>">
                <?php echo $secondary['title']; ?>
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Right Content - Hero Image -->
      <div class="flex flex-1 justify-center lg:justify-end">
        <?php if (!empty($hero_img_url)) : ?>
          <img src="<?php echo esc_url($hero_img_url); ?>"
               alt="<?php echo esc_attr($hero_img_alt); ?>"
               title="<?php echo esc_attr($hero_img_title); ?>"
               class="object-contain max-w-full h-auto max-h-80 lg:max-h-96" />
        <?php endif; ?>
      </div>
    </div>

    <!-- Pager (optional, static markup) -->
    <?php if ($show_pager && $pager_count > 0): ?>
      <div class="flex relative z-10 gap-4 justify-center items-center pb-6 w-full">
        <!-- Prev -->
        <button class="flex justify-center items-center w-8 h-8 bg-white rounded border transition-colors border-primary-light hover:bg-gray-50" aria-label="Previous">
          <svg class="w-4 h-4" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M12.6667 8L3.33337 8" stroke="#020617" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
            <path d="M8.00004 3.33335L3.33337 8.00002L8.00004 12.6667" stroke="#020617" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
          </svg>
        </button>

        <!-- Dots -->
        <div class="flex gap-2 items-center">
          <?php for ($i = 1; $i <= $pager_count; $i++): 
            $active = ($i === $pager_active);
            $dot_classes = $active ? 'bg-secondary' : 'bg-primary';
          ?>
            <button class="w-2.5 h-2.5 rounded-full transition-colors <?php echo esc_attr($dot_classes); ?>" aria-label="Go to slide <?php echo esc_attr($i); ?>"></button>
          <?php endfor; ?>
        </div>

        <!-- Next -->
        <button class="flex justify-center items-center w-8 h-8 bg-white rounded border transition-colors border-primary-light hover:bg-gray-50" aria-label="Next">
          <svg class="w-4 h-4" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M3.33325 8L12.6666 8" stroke="#020617" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
            <path d="M8 12.6667L12.6667 8.00002L8 3.33335" stroke="#020617" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
          </svg>
        </button>
      </div>
    <?php endif; ?>

  </div>
</section>
