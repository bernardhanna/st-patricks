<?php
/**
 * Flexi Block: About Us
 * - Tailwind-only, no Vue
 * - Uses get_sub_field()
 * - Alpine.js counters (Alpine already on page)
 */

$section_id = 'about-' . ( function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid() );

// Content
$faded_logo    = get_sub_field('faded_logo');
$heading_tag   = get_sub_field('heading_tag') ?: 'h2';
$heading_text  = (string) (get_sub_field('heading_text') ?: 'About us');
$description   = (string) (get_sub_field('description') ?: '');

$image_left    = get_sub_field('image_left') ?: [];
$image_right   = get_sub_field('image_right') ?: [];
$key_points    = get_sub_field('key_points') ?: [];

$primary_cta   = get_sub_field('primary_cta');
$secondary_cta = get_sub_field('secondary_cta');

// Design
$bg_color      = get_sub_field('bg_color') ?: '#ffffff';
$heading_color = get_sub_field('heading_color') ?: '#0B0B08';
$desc_color    = get_sub_field('desc_color') ?: '#4A4B37';
$divider_color = get_sub_field('divider_color') ?: '#5F604B';
$value_color   = get_sub_field('value_color') ?: '#5F604B';
$title_color   = get_sub_field('title_color') ?: '#0B0B08';
$text_color    = get_sub_field('text_color') ?: '#4A4B37';
$btn_preset    = get_sub_field('buttons_style') ?: 'solid-dark';

// Layout padding classes
$padding_classes = [];
if (have_rows('padding_settings')) {
  while (have_rows('padding_settings')) {
    the_row();
    $screen = get_sub_field('screen_size');
    $pt     = get_sub_field('padding_top');
    $pb     = get_sub_field('padding_bottom');
    if ($screen && $pt !== '' && $pt !== null) $padding_classes[] = "{$screen}:pt-[{$pt}rem]";
    if ($screen && $pb !== '' && $pb !== null) $padding_classes[] = "{$screen}:pb-[{$pb}rem]";
  }
}

// helpers
$img_parts = function($img, $alt_fallback='') {
  if (!is_array($img)) return ['', $alt_fallback, $alt_fallback];
  $url = $img['url']   ?? '';
  $alt = $img['alt']   ?? ($img['title'] ?? $alt_fallback);
  $ttl = $img['title'] ?? $alt;
  return [$url, $alt ?: $alt_fallback, $ttl ?: $alt_fallback];
};

$btn_classes = [
  'solid-dark' => [
    'primary'   => 'flex items-center justify-center gap-2 bg-secondary text-white px-6 py-3 rounded text-sm font-medium hover:bg-opacity-90 transition-colors',
    'secondary' => 'bg-primary text-secondary border border-primary px-6 py-3 rounded text-sm font-medium hover:bg-secondary hover:text-white transition-colors',
  ],
  'solid-primary' => [
    'primary'   => 'flex items-center justify-center gap-2 bg-primary text-dark-bg px-6 py-3 rounded text-sm font-medium hover:bg-opacity-90 transition-colors',
    'secondary' => 'bg-transparent text-primary border border-primary px-6 py-3 rounded text-sm font-medium hover:bg-primary hover:text-white transition-colors',
  ],
];

$primary_cls   = $btn_classes[$btn_preset]['primary'];
$secondary_cls = $btn_classes[$btn_preset]['secondary'];

// media values
[$fl_url, $fl_alt, $fl_ttl] = $img_parts($faded_logo, 'Logo');
[$l_img_url, $l_img_alt]    = $img_parts($image_left['image'] ?? null, 'Image');
[$l_logo_url]               = $img_parts($image_left['overlay_logo'] ?? null, 'Logo');
$rotate_deg                 = isset($image_left['rotate_deg']) ? (int)$image_left['rotate_deg'] : -6;

[$r_img_url, $r_img_alt]    = $img_parts($image_right['image'] ?? null, 'Image');
[$r_logo_url]               = $img_parts($image_right['overlay_logo'] ?? null, 'Logo');

// sanitize heading tag
$allowed_tags = ['h1','h2','h3','h4','h5','h6','span','p'];
if (!in_array($heading_tag, $allowed_tags, true)) $heading_tag = 'h2';

?>

<section id="<?php echo esc_attr($section_id); ?>"
         data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
         class="relative overflow-hidden <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
         style="background: var(--StPatricks_Aux_DarkBG4, linear-gradient(278deg, #FAFBF6 3.24%, #F1F8F9 90.88%));">

  <div class="px-6 py-24 mx-auto max-w-container">
    <div class="mx-auto max-w-7xl">

      <div class="flex flex-col gap-8 justify-between items-start xl:flex-row xl:items-center md:gap-12 lg:gap-16">
        <!-- Left Content -->
        <div class="flex flex-col flex-shrink-0 gap-6 items-start w-full md:gap-8 xl:w-auto">
          <div class="flex relative flex-col gap-8 justify-center items-start md:gap-12">
            <?php if ($fl_url): ?>
              <img src="<?php echo esc_url($fl_url); ?>"
                   alt="<?php echo esc_attr($fl_alt); ?>"
                   title="<?php echo esc_attr($fl_ttl); ?>"
                   class="absolute -top-3 -left-4 w-16 h-16 sm:-left-7 sm:-top-4 sm:w-20 sm:h-19">
            <?php endif; ?>

            <div class="relative z-10">
              <<?php echo tag_escape($heading_tag); ?>
                class="mb-3 text-2xl font-semibold leading-8 font-le-monde sm:text-3xl sm:leading-9 tracking-tight-md"
                style="color: <?php echo esc_attr($heading_color); ?>;">
                <?php echo esc_html($heading_text); ?>
              </<?php echo tag_escape($heading_tag); ?>>

              <div class="w-10 h-px" style="background-color: <?php echo esc_attr($divider_color); ?>;"></div>
            </div>
          </div>

          <?php if ($description): ?>
            <div class="w-full max-w-md text-sm font-medium leading-6 wp_editor sm:text-base sm:leading-7"
                 style="color: <?php echo esc_attr($desc_color); ?>;">
              <?php echo wp_kses_post($description); ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- Right Images -->
        <div class="flex relative flex-col gap-4 justify-center items-center w-full md:flex-row sm:gap-6 lg:gap-8 xl:flex-1">

          <!-- Rotated -->
          <div class="relative">
            <div class="w-64 h-40 sm:w-80 sm:h-48 lg:w-[436px] lg:h-[260px] bg-white rounded shadow-sm p-1 z-50 relative"
                 style="transform: rotate(<?php echo esc_attr($rotate_deg); ?>deg);">
              <div class="relative w-full h-full bg-center bg-cover rounded"
                   style="background-image:url('<?php echo esc_url($l_img_url); ?>');"
                   aria-label="<?php echo esc_attr($l_img_alt); ?>">
                <?php if ($l_logo_url): ?>
                  <img src="<?php echo esc_url($l_logo_url); ?>"
                       alt=""
                       class="absolute bottom-0 right-0 w-32 h-32 sm:w-48 sm:h-40 lg:w-[268px] lg:h-[260px] object-contain"
                       aria-hidden="true">
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Straight -->
          <div class="absolute w-64 h-40 sm:w-80 sm:h-48 lg:w-[436px] lg:h-[260px] bg-white rounded shadow-sm p-1">
            <div class="relative w-full h-full bg-center bg-cover rounded"
                 style="background-image:url('<?php echo esc_url($r_img_url); ?>');"
                 aria-label="<?php echo esc_attr($r_img_alt); ?>">
              <?php if ($r_logo_url): ?>
                <img src="<?php echo esc_url($r_logo_url); ?>"
                     alt=""
                     class="absolute bottom-0 right-0 w-32 h-32 sm:w-48 sm:h-40 lg:w-[268px] lg:h-[260px] object-contain"
                     aria-hidden="true">
              <?php endif; ?>
            </div>
          </div>

        </div>
      </div>

      <!-- Key Points (Counters) -->
      <?php if (!empty($key_points)): ?>
        <div class="flex flex-col gap-8 justify-center items-center mt-12 mb-8 lg:flex-row lg:gap-16 lg:mt-16">

          <?php foreach ($key_points as $i => $kp):
            $val      = isset($kp['value']) ? (float)$kp['value'] : 0;
            $suffix   = (string) ($kp['suffix'] ?? '');
            $title    = (string) ($kp['title'] ?? '');
            $text     = (string) ($kp['text'] ?? '');
            [$wm_url] = $img_parts($kp['watermark'] ?? null, '');
          ?>
            <div class="flex flex-col gap-3 items-center text-center" x-data="{
                  shown: false,
                  val: 0,
                  target: <?php echo json_encode($val); ?>,
                  dur: 1000,
                  start() {
                    if (this.shown) return;
                    this.shown = true;
                    const start = performance.now();
                    const step = (t) => {
                      const p = Math.min(1, (t - start) / this.dur);
                      this.val = Math.round(this.target * p);
                      if (p < 1) requestAnimationFrame(step); else this.val = this.target;
                    };
                    requestAnimationFrame(step);
                  }
                }" x-intersect.once="start()">

              <div class="flex relative justify-center items-center w-16 h-16">
                <?php if ($wm_url): ?>
                  <img src="<?php echo esc_url($wm_url); ?>" alt="" aria-hidden="true"
                       class="absolute -bottom-4 -left-4 w-24 h-24 sm:w-28 sm:h-28 lg:w-30 lg:h-29 sm:-left-6 lg:-left-7 sm:-bottom-6 lg:-bottom-7">
                <?php endif; ?>
                <span class="relative z-10 text-2xl font-semibold leading-8 font-le-monde sm:text-3xl sm:leading-9 tracking-tight-md"
                      :text-content="val + <?php echo json_encode($suffix); ?>"
                      style="color: <?php echo esc_attr($value_color); ?>;">
                  <?php echo esc_html((string)$val . $suffix); ?>
                </span>
              </div>

              <?php if ($title): ?>
                <h3 class="text-lg font-semibold leading-6 font-le-monde sm:text-xl sm:leading-7 tracking-tight-sm"
                    style="color: <?php echo esc_attr($title_color); ?>;">
                  <?php echo esc_html($title); ?>
                </h3>
              <?php endif; ?>

              <?php if ($text): ?>
                <p class="w-full max-w-xs sm:max-w-sm lg:w-[228px] text-sm font-medium leading-5 text-center px-4 lg:px-0"
                   style="color: <?php echo esc_attr($text_color); ?>;">
                  <?php echo esc_html($text); ?>
                </p>
              <?php endif; ?>
            </div>

            <!-- dividers between items -->
            <?php if ($i < count($key_points)-1): ?>
              <div class="hidden w-px h-10 lg:block" style="background-color: <?php echo esc_attr($divider_color); ?>;"></div>
              <div class="block w-full h-px lg:hidden" style="background-color: <?php echo esc_attr($divider_color); ?>; max-width: 20rem;"></div>
            <?php endif; ?>

          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Buttons -->
      <div class="flex flex-col gap-4 justify-center items-center mt-8 sm:flex-row">
        <?php if (!empty($primary_cta['url'])): ?>
          <a href="<?php echo esc_url($primary_cta['url']); ?>"
             target="<?php echo esc_attr($primary_cta['target'] ?: '_self'); ?>"
             class="<?php echo esc_attr($primary_cls); ?>">
            <!-- icon (optional): -->
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="19" viewBox="0 0 22 19" fill="none">
            <path d="M18.414 11.4139C20 9.8279 21 8.4999 21 6.4999C21 5.3871 20.6624 4.30048 20.0319 3.38356C19.4013 2.46664 18.5075 1.76256 17.4684 1.3643C16.4293 0.96604 15.2938 0.892344 14.212 1.15294C13.1301 1.41354 12.1528 1.99618 11.409 2.8239M18.414 11.4139C18.2168 11.6112 17.9826 11.7676 17.7249 11.8744C17.4672 11.9812 17.191 12.0361 16.912 12.0361C16.633 12.0361 16.3568 11.9812 16.0991 11.8744C15.8414 11.7676 15.6072 11.6112 15.41 11.4139C15.6235 11.607 15.7956 11.8415 15.9157 12.1032C16.0358 12.3648 16.1015 12.6482 16.1087 12.936C16.116 13.2238 16.0646 13.5101 15.9578 13.7774C15.851 14.0448 15.6909 14.2876 15.4873 14.4912C15.2837 14.6948 15.0409 14.8549 14.7735 14.9617C14.5062 15.0685 14.2199 15.1199 13.9321 15.1126C13.6443 15.1054 13.3609 15.0397 13.0993 14.9196C12.8376 14.7995 12.6031 14.6274 12.41 14.4139C12.6074 14.6105 12.7641 14.8441 12.8712 15.1014C12.9782 15.3586 13.0335 15.6344 13.0339 15.9131C13.0342 16.1917 12.9797 16.4677 12.8733 16.7252C12.767 16.9827 12.6109 17.2167 12.414 17.4139C12.224 17.604 11.9976 17.7538 11.7484 17.8544C11.4991 17.9551 11.2322 18.0044 10.9635 17.9996C10.6947 17.9947 10.4297 17.9358 10.1843 17.8262C9.93886 17.7166 9.71804 17.5587 9.535 17.3619L4 11.9999C2.5 10.4999 1 8.7999 1 6.4999C1.00022 5.38719 1.33794 4.30071 1.96856 3.38395C2.59917 2.46718 3.49303 1.76325 4.53208 1.36512C5.57112 0.966986 6.7065 0.893373 7.78826 1.154C8.87002 1.41463 9.84728 1.99724 10.591 2.8249C10.7022 2.92823 10.8484 2.98559 11.0002 2.9854C11.152 2.98522 11.2981 2.92751 11.409 2.8239M18.414 11.4139C18.7889 11.0388 18.9996 10.5302 18.9996 9.9999C18.9996 9.46957 18.7889 8.96096 18.414 8.5859L16.533 6.7039C16.3092 6.48 16.0435 6.30239 15.751 6.18121C15.4585 6.06003 15.1451 5.99766 14.8285 5.99766C14.5119 5.99766 14.1985 6.06003 13.906 6.18121C13.6135 6.30239 13.3478 6.48 13.124 6.7039L11.414 8.4139C11.0389 8.78884 10.5303 8.99947 10 8.99947C9.46967 8.99947 8.96106 8.78884 8.586 8.4139C8.21106 8.03884 8.00043 7.53023 8.00043 6.9999C8.00043 6.46957 8.21106 5.96096 8.586 5.5859L11.409 2.8239" stroke="white" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span><?php echo esc_html($primary_cta['title'] ?: 'Learn more'); ?></span>
          </a>
        <?php endif; ?>

        <?php if (!empty($secondary_cta['url'])): ?>
          <a href="<?php echo esc_url($secondary_cta['url']); ?>"
             target="<?php echo esc_attr($secondary_cta['target'] ?: '_self'); ?>"
             class="<?php echo esc_attr($secondary_cls); ?>">
            <?php echo esc_html($secondary_cta['title'] ?: 'Find out more'); ?>
          </a>
        <?php endif; ?>
      </div>

    </div>
  </div>
</section>
