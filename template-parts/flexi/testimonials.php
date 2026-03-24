<?php
/**
 * Testimonials (Flexi Block)
 * - Uses get_sub_field()
 * - Tailwind only; no Vue attrs
 * - Random section id
 * - Padding controls via padding_settings
 */

$section_id = 'testimonials-' . ( function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid() );

// Content
$left_logo    = get_sub_field('left_logo');
$heading_tag  = get_sub_field('heading_tag') ?: 'h2';
$heading_text = (string) (get_sub_field('heading_text') ?: '');
$description  = (string) (get_sub_field('description') ?: '');
$items        = get_sub_field('items') ?: [];

// Design
$bg_color     = get_sub_field('background_color') ?: '#ffffff';
$heading_col  = get_sub_field('heading_color')    ?: '#0B0B08';
$desc_col     = get_sub_field('desc_color')       ?: '#5F604B';
$quote_col    = get_sub_field('quote_color')      ?: '#4A4B37';
$accent_col   = get_sub_field('accent_color')     ?: '#7ED0E0';

$grad_from    = get_sub_field('gradient_from')    ?: '#7ED0E0';
$grad_to      = get_sub_field('gradient_to')      ?: '#3CA7B6';

$card_radius  = get_sub_field('card_radius') ?: 'rounded-none';
$card_shadow  = (bool) get_sub_field('card_shadow');
$logo_opacity = get_sub_field('logo_opacity');
$logo_opacity = ($logo_opacity === '' || $logo_opacity === null) ? 0.2 : (float) $logo_opacity;

$stack_bg     = (bool) get_sub_field('show_stack_backgrounds');

// Layout: paddings
$padding_classes = [];
if (have_rows('padding_settings')) {
  while (have_rows('padding_settings')) {
    the_row();
    $screen = get_sub_field('screen_size');
    $pt     = get_sub_field('padding_top');
    $pb     = get_sub_field('padding_bottom');

    if ($screen !== '' && $pt !== '' && $pt !== null) {
      $padding_classes[] = "{$screen}:pt-[{$pt}rem]";
    }
    if ($screen !== '' && $pb !== '' && $pb !== null) {
      $padding_classes[] = "{$screen}:pb-[{$pb}rem]";
    }
  }
}

// sanitize heading tag
$allowed_tags = ['h1','h2','h3','h4','h5','h6','span','p'];
if (!in_array($heading_tag, $allowed_tags, true)) {
  $heading_tag = 'h2';
}

// helpers to pull media attributes
$get_img = function($img, $fallback_alt = 'Image') {
  if (!is_array($img)) return [ '', $fallback_alt, $fallback_alt ];
  $url = $img['url']   ?? '';
  $alt = $img['alt']   ?? '';
  $ttl = $img['title'] ?? '';
  if (!$alt) $alt = $fallback_alt;
  if (!$ttl) $ttl = $alt;
  return [ $url, $alt, $ttl ];
};

// classes
$card_base = ['bg-white','overflow-hidden',$card_radius];
if ($card_shadow) $card_base[] = 'shadow-[0_1px_2px_-1px_rgba(0,0,0,0.10),0_1px_3px_0_rgba(0,0,0,0.10)]';

?>
<section id="<?php echo esc_attr($section_id); ?>"
         class="flex overflow-hidden relative"
         style="background-color: <?php echo esc_attr($bg_color); ?>;">
  <div class="flex flex-col items-center w-full mx-auto max-w-container pt-5 pb-5 max-lg:px-5 <?php echo esc_attr(implode(' ', $padding_classes)); ?>">

    <div class="flex flex-col gap-8 w-full lg:flex-row lg:justify-between lg:items-start lg:gap-16">

      <!-- Left Content -->
      <div class="flex flex-col items-start gap-8 lg:w-[342px] flex-shrink-0">
        <div class="flex relative flex-col gap-8 justify-center items-start">
          <?php if ($left_logo):
            [ $logo_url, $logo_alt, $logo_ttl ] = $get_img($left_logo, 'Logo');
          ?>
            <?php if (!empty($logo_url)): ?>
              <img src="<?php echo esc_url($logo_url); ?>"
                   alt="<?php echo esc_attr($logo_alt); ?>"
                   title="<?php echo esc_attr($logo_ttl); ?>"
                   class="w-20 h-[78px] -ml-7 -mt-4"
                   style="opacity: <?php echo esc_attr($logo_opacity); ?>;">
            <?php endif; ?>
          <?php endif; ?>

          <?php if ($heading_text !== ''): ?>
            <<?php echo tag_escape($heading_tag); ?>
              class="text-[30px] font-semibold leading-[36px] tracking-tight"
              style="color: <?php echo esc_attr($heading_col); ?>;">
              <?php echo nl2br(esc_html($heading_text)); ?>
            </<?php echo tag_escape($heading_tag); ?>>
          <?php endif; ?>

          <div class="w-10 h-px" style="background-color: <?php echo esc_attr($accent_col); ?>;"></div>
        </div>

        <?php if (!empty($description)): ?>
          <div class="wp_editor text-base font-medium leading-7 max-w-full lg:max-w-[342px]"
               style="color: <?php echo esc_attr($desc_col); ?>;">
            <?php echo wp_kses_post($description); ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Right Content -->
      <div class="flex relative flex-col flex-1 gap-8 items-center">

        <div class="relative w-full max-w-[600px]">
          <?php
          // render first testimonial prominently
          $first = $items[0] ?? null;
          if ($first):
            [ $purl, $palt, $pttl ] = $get_img($first['photo'] ?? null, $first['author_name'] ?? 'Person');
            $quote  = (string) ($first['quote'] ?? '');
            $author = (string) ($first['author_name'] ?? '');
            $title  = (string) ($first['author_title'] ?? '');
          ?>
          <div class="relative z-10">
            <div class="flex flex-col sm:flex-row items-start <?php echo esc_attr(implode(' ', $card_base)); ?>">
              <div class="flex relative flex-col gap-8 items-center p-4 w-full sm:flex-row lg:p-16">
                <!-- gradient overlay -->
                <div class="absolute inset-0 pointer-events-none"
                     style="background: linear-gradient(90deg, <?php echo esc_attr($grad_from); ?> 0%, <?php echo esc_attr($grad_to); ?> 100%); opacity: .30;"></div>

                <!-- person image -->
                <?php if (!empty($purl)): ?>
                  <div class="relative z-10 flex-shrink-0">
                    <img src="<?php echo esc_url($purl); ?>"
                         alt="<?php echo esc_attr($palt); ?>"
                         title="<?php echo esc_attr($pttl); ?>"
                         class="w-[179px] h-[194px] object-cover rounded-[4px]">
                  </div>
                <?php endif; ?>

                <!-- quote card -->
                <div class="relative z-10 flex-1 p-4 bg-white rounded-[4px]">
                  <!-- quote text -->
                  <?php if (!empty($quote)): ?>
                    <div class="wp_editor text-base italic leading-6 min-h-[70px]"
                         style="color: <?php echo esc_attr($quote_col); ?>;">
                      <?php echo wp_kses_post($quote); ?>
                    </div>
                  <?php endif; ?>

                  <!-- divider -->
                  <div class="my-4 w-10 h-px" style="background-color: <?php echo esc_attr($accent_col); ?>;"></div>

                  <!-- author -->
                  <div class="flex flex-col items-start">
                    <?php if (!empty($author)): ?>
                      <div class="text-sm font-semibold leading-5" style="color: <?php echo esc_attr($heading_col); ?>;">
                        <?php echo esc_html($author); ?>
                      </div>
                    <?php endif; ?>
                    <?php if (!empty($title)): ?>
                      <div class="text-xs leading-4" style="color: <?php echo esc_attr($heading_col); ?>;">
                        <?php echo esc_html($title); ?>
                      </div>
                    <?php endif; ?>
                  </div>

                  <!-- quote decorator square -->
                  <div class="absolute right-4 top-4 w-6 h-6 rotate-45 rounded-[6px]"
                       style="background-color: #ffffff;"></div>
                </div>
              </div>
            </div>
          </div>

          <?php if ($stack_bg): ?>
            <!-- stacked gradient cards -->
            <div class="absolute top-8 left-10 right-10 bottom-8 rounded-[6px] z-0"
                 style="background: linear-gradient(90deg, <?php echo esc_attr($grad_from); ?> 0%, <?php echo esc_attr($grad_to); ?> 100%); opacity: .50;"></div>
            <div class="absolute top-16 left-20 right-20 bottom-16 rounded-[6px] z-0"
                 style="background: linear-gradient(90deg, <?php echo esc_attr($grad_from); ?> 0%, <?php echo esc_attr($grad_to); ?> 100%); opacity: .30;"></div>
          <?php endif; ?>

          <?php endif; ?>
        </div>

        <!-- decorative quote icon + simple nav/dots (static) -->
        <div class="relative w-full max-w-[600px]">
          <div class="absolute -top-4 right-8 z-30">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
              <path d="M3 21C6 21 10 20 10 13V5.00003C10 3.75003 9.244 2.98303 8 3.00003H4C2.75 3.00003 2 3.75003 2 4.97203V11C2 12.25 2.75 13 4 13C5 13 5 13 5 14V15C5 16 4 17 3 17C2 17 2 17.008 2 18.031V20C2 21 2 21 3 21Z"
                    stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
              <path d="M15 21C18 21 22 20 22 13V5.00003C22 3.75003 21.243 2.98303 20 3.00003H16C14.75 3.00003 14 3.75003 14 4.97203V11C14 12.25 14.75 13 16 13H16.75C16.75 15.25 17 17 14 17V20C14 21 14 21 15 21Z"
                    stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
          </div>

          <div class="flex flex-row gap-4 justify-center items-center pt-10">
            <!-- arrows (visual only) -->
            <div class="flex flex-row gap-4 justify-center items-center">
              <button class="w-8 h-8 -rotate-90 p-0 flex justify-center items-center rounded-[6px] border bg-white hover:bg-gray-50"
                      style="border-color: <?php echo esc_attr($grad_from); ?>;">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                  <path d="M8 12.6666L8 3.33325" stroke="#020617" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                  <path d="M12.6666 7.99992L7.99992 3.33325L3.33325 7.99992" stroke="#020617" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
              </button>

              <!-- dots reflect count -->
              <?php $count = max(1, count($items)); ?>
              <div class="flex flex-row gap-2 items-center">
                <?php for ($i=0; $i<$count; $i++): ?>
                  <?php
                    $active = ($i === 0);
                    $dot_color = $active ? $grad_to : $grad_from;
                  ?>
                  <div class="w-[10px] h-[10px] rounded-[6px]" style="background-color: <?php echo esc_attr($dot_color); ?>;"></div>
                <?php endfor; ?>
              </div>

              <button class="w-8 h-8 -rotate-90 p-0 flex justify-center items-center rounded-[6px] border bg-white hover:bg-gray-50"
                      style="border-color: <?php echo esc_attr($grad_from); ?>;">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                  <path d="M8 3.33325L8 12.6666" stroke="#020617" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                  <path d="M3.33325 8L7.99992 12.6667L12.6666 8" stroke="#020617" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>

  </div>
</section>
