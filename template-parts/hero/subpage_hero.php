<?php
// Get ACF fields
$background_image = get_sub_field('background_image');
$logo_id         = get_sub_field('logo'); // NEW: optional logo
$heading_tag     = get_sub_field('heading_tag') ?: 'h1';
$heading_text    = get_sub_field('heading_text');
$heading_color   = get_sub_field('heading_color');
$content_text    = get_sub_field('content_text');
$text_color      = get_sub_field('text_color');

// Padding settings
$padding_classes = ['pt-5', 'pb-5'];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size    = get_sub_field('screen_size');
        $padding_top    = get_sub_field('padding_top');
        $padding_bottom = get_sub_field('padding_bottom');
        $padding_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
        $padding_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
    }
}

// Generate unique section ID
$section_id = 'subpage-hero-' . uniqid();

// Build logo HTML (optional)
$logo_html = '';
if ($logo_id) {
    $logo_alt = get_post_meta($logo_id, '_wp_attachment_image_alt', true) ?: 'Logo';
    $logo_html = wp_get_attachment_image($logo_id, 'medium', false, [
        'alt'   => esc_attr($logo_alt),
        'class' => 'object-contain h-112 md:h-24 w-auto',
        'loading' => 'lazy',
    ]);
}
?>

<section id="<?php echo esc_attr($section_id); ?>" class="relative flex overflow-hidden max-lg:mt-[5rem] mt-[8rem]">
  <div class="flex flex-col items-center w-full mx-auto max-w-container max-lg:px-5 <?php echo esc_attr(implode(' ', $padding_classes)); ?>">

    <?php if (!empty($background_image['url'])): ?>
      <img
        src="<?php echo esc_url($background_image['url']); ?>"
        alt="<?php echo esc_attr($background_image['alt'] ?: ''); ?>"
        class="object-cover absolute inset-0 size-full"
        aria-hidden="true"
      />
    <?php endif; ?>

    <div class="relative flex flex-col justify-center items-start w-full min-h-[520px] max-w-[1139px] max-md:max-w-full px-0 lg:px-5  xxl:px-0">
      <div class="relative flex flex-col justify-center px-14 py-8 mb-0 max-w-full bg-white mix-blend-normal opacity-90 backdrop-blur-[104px] rounded-tl-[20px] rounded-tr-[4px] rounded-br-[20px] rounded-bl-[4px] w-fit lg:w-[770px] max-md:px-5 max-md:mb-2.5">
        <div class="max-md:max-w-full">

          <?php if ($logo_html): ?>
            <div class="mb-4">
              <?php echo $logo_html; ?>
            </div>
          <?php endif; ?>

          <?php if ($heading_text): ?>
            <<?php echo esc_attr($heading_tag); ?>
              class="text-4xl font-medium text-zinc-900 max-md:max-w-full"
              style="color: <?php echo esc_attr($heading_color); ?>;"
            >
              <?php echo esc_html($heading_text); ?>
            </<?php echo esc_attr($heading_tag); ?>>
          <?php endif; ?>

          <?php if ($content_text): ?>
            <div
              class="mt-2.5 text-2xl leading-8 text-neutral-700 max-md:max-w-full wp_editor"
              style="color: <?php echo esc_attr($text_color); ?>;"
            >
              <?php echo wp_kses_post($content_text); ?>
            </div>
          <?php endif; ?>

        </div>
      </div>
    </div>

  </div>
</section>
