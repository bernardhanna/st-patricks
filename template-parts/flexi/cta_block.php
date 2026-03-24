<?php
// ACF
$title         = get_sub_field('title') ?: 'Child Safeguarding';
$description   = get_sub_field('description') ?: '';
$button        = get_sub_field('button') ?: [];
$show_icon     = (bool) get_sub_field('show_icon');

$polaroid      = get_sub_field('polaroid_image');
$faded_logo    = get_sub_field('faded_logo');
$wm_logo       = get_sub_field('watermark_logo');

$divider_color = get_sub_field('divider_color') ?: '#D1D5DB';
$min_full      = (bool) get_sub_field('min_full_screen');
$extra_classes = trim((string) get_sub_field('section_classes'));

$padding_classes = [];
if (have_rows('padding_settings')) {
  while (have_rows('padding_settings')) {
    the_row();
    $screen = (string) get_sub_field('screen_size');
    $pt     = get_sub_field('padding_top');
    $pb     = get_sub_field('padding_bottom');
    if ($pt !== '' && $pt !== null) $padding_classes[] = ($screen ? "{$screen}:" : '') . "pt-[{$pt}rem]";
    if ($pb !== '' && $pb !== null) $padding_classes[] = ($screen ? "{$screen}:" : '') . "pb-[{$pb}rem]";
  }
}

$btn_url    = $button['url'] ?? '#';
$btn_title  = $button['title'] ?? 'Learn more';
$btn_target = $button['target'] ?? '_self';

// Helpers
$img_url = $polaroid['url'] ?? '';
$img_alt = $polaroid['alt'] ?? ($polaroid['title'] ?? 'CTA image');

$fade_url = $faded_logo['url'] ?? '';
$fade_alt = $faded_logo['alt'] ?? '';

$wm_url = $wm_logo['url'] ?? '';
$wm_alt = $wm_logo['alt'] ?? '';

$section_min = $min_full ? 'min-h-screen' : 'min-h-[14rem]';
?>

<section class="relative overflow-hidden <?php echo esc_attr($section_min); ?> <?php echo esc_attr($extra_classes); ?>">
  <div class=" flex items-center max-w-container mx-auto py-16  <?php echo esc_attr(implode(' ', $padding_classes)); ?>">

    <!-- Polaroid -->
    <div class="flex-shrink-0">
      <div class="p-2 w-48 h-48 bg-white rounded shadow-sm transition-transform duration-300 -rotate-6 sm:w-56 sm:h-56 md:w-64 md:h-64 lg:w-72 lg:h-72 hover:-rotate-3" style="box-shadow:0 1px 2px 0 rgba(0,0,0,0.05);">
        <div class="relative flex h-full w-full items-center justify-center overflow-hidden rounded <?php echo $img_url ? '' : 'bg-gray-100'; ?>"
             <?php if ($img_url): ?> style="background:url('<?php echo esc_url($img_url); ?>') center/cover no-repeat;"<?php endif; ?>>
          <?php if ($wm_url): ?>
            <img src="<?php echo esc_url($wm_url); ?>" alt="<?php echo esc_attr($wm_alt); ?>"
                 class="absolute right-0 bottom-0 w-32 h-32 opacity-30 translate-x-16 translate-y-16 sm:h-36 sm:w-36"
                 aria-hidden="true">
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Text -->
    <div class="flex flex-col flex-1 gap-3 justify-center pr-0 pl-6 min-h-56 sm:pr-2 md:pr-6 lg:pr-12">
      <div class="flex relative flex-col gap-6 sm:gap-8 md:gap-12">
        <?php if ($fade_url): ?>
          <img src="<?php echo esc_url($fade_url); ?>" alt="<?php echo esc_attr($fade_alt); ?>"
               class="absolute -top-4 -left-6 w-16 h-16 opacity-20 sm:-left-8 sm:h-20 sm:w-20">
        <?php endif; ?>

        <h2 class="text-2xl font-medium tracking-tight leading-tight font-le-monde text-st-patricks-text-dark sm:text-3xl lg:text-4xl">
          <?php echo esc_html($title); ?>
        </h2>

        <div class="w-10 h-px" style="background: <?php echo esc_attr($divider_color); ?>;"></div>
      </div>

      <?php if ($description): ?>
        <p class="max-w-lg text-base leading-relaxed font-inter text-st-patricks-text-medium">
          <?php echo esc_html($description); ?>
        </p>
      <?php endif; ?>
    </div>
  <div class="flex-shrink-0">
    <a href="<?php echo esc_url($btn_url); ?>" target="<?php echo esc_attr($btn_target); ?>"
       class="inline-flex gap-2 items-center px-4 py-2 text-sm font-medium leading-6 text-white rounded transition-all duration-200 bg-secondary font-inter hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-secondary"
       style="border-radius:calc(6px - 2px);">
      <?php if ($show_icon): ?>
        <svg class="w-6 h-6" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
          <path d="M9.928 16.094c.5.3 1.2.5 2 .5s1.5-.2 2-.5" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M14.928 12.094h.01" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M19.308 6.907c.701 1.019 1.184 2.172 1.42 3.386.338.164.623.42.822.738.2.318.305.686.305 1.062 0 .376-.105.744-.305 1.062-.2.318-.485.574-.822.738-.432 2.013-1.541 3.818-3.142 5.112-1.601 1.295-3.598 2.001-5.657 2.001-2.059 0-4.056-.706-5.657-2.001-1.602-1.294-2.711-3.099-3.143-5.112-.338-.164-.623-.42-.822-.738-.2-.318-.305-.686-.305-1.062 0-.376.105-.744.305-1.062.2-.318.485-.574.822-.738.414-2.029 1.516-3.854 3.119-5.165C7.85 3.816 9.857 3.098 11.928 3.094c2 0 3.5 1.1 3.5 2.5 0 1.4-.9 2.5-2 2.5-.8 0-1.5-.4-1.5-1" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M8.928 12.094h.01" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      <?php endif; ?>
      <span><?php echo esc_html($btn_title); ?></span>
    </a>
  </div>
  </div>

  
</section>
