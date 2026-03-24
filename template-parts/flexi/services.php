<?php
/**
 * Services (Flexi Block)
 * - Uses get_sub_field()
 * - Random section ID
 * - Required wrappers + padding_settings
 * - Tailwind only (no min-w-[240px] or aspect-* classes)
 */

$section_id = 'services-' . ( function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid() );

// Content
$heading_tag  = get_sub_field('heading_tag') ?: 'h2';
$heading_text = (string) (get_sub_field('heading_text') ?: '');
$cards        = get_sub_field('cards') ?: [];
$cta          = get_sub_field('view_more_button');

// Design
$bg_color     = get_sub_field('background_color') ?: '#ffffff';
$heading_col  = get_sub_field('heading_color')    ?: '#2C2C21';
$title_col    = get_sub_field('title_color')      ?: '#4A4B37';
$wm_opacity   = get_sub_field('watermark_opacity');
$wm_opacity   = ($wm_opacity === '' || $wm_opacity === null) ? 0.12 : (float) $wm_opacity;

$card_radius  = get_sub_field('card_radius') ?: 'rounded-none';
$card_shadow  = (bool) get_sub_field('card_shadow');

// Layout
$md_cols      = get_sub_field('md_columns') ?: '2';
$lg_cols      = get_sub_field('lg_columns') ?: '3';
$xl_cols      = get_sub_field('xl_columns') ?: '3';

// Build grid classes (ensure these are safelisted in Tailwind if needed)
$grid_classes = [
  'grid', 'grid-cols-1',
  "md:grid-cols-{$md_cols}",
  "lg:grid-cols-{$lg_cols}",
  "xl:grid-cols-{$xl_cols}",
  'gap-4', 'md:gap-6', 'lg:gap-8',
];

// Padding classes
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

// helper: link array normalizer (expects ACF link array)
$resolve_link = function($link) {
  if (!is_array($link) || empty($link['url'])) return null;
  return [
    'url'    => esc_url($link['url']),
    'title'  => esc_html($link['title'] ?: $link['url']),
    'target' => esc_attr($link['target'] ?: '_self'),
  ];
};

?>
<section id="<?php echo esc_attr($section_id); ?>"
         class="flex overflow-hidden relative"
         style="background-color: <?php echo esc_attr($bg_color); ?>;">
  <div class="flex flex-col items-center w-full mx-auto max-w-container py-32 max-lg:px-5 <?php echo esc_attr(implode(' ', $padding_classes)); ?>">

    <div class="w-full">
      <?php if ($heading_text !== ''): ?>
        <<?php echo tag_escape($heading_tag); ?>
          class="mb-8 text-lg md:text-xl font-medium tracking-[-0.1px] text-center"
          style="font-family:'EB Garamond', Georgia, 'Times New Roman', serif; color: <?php echo esc_attr($heading_col); ?>;">
          <?php echo esc_html($heading_text); ?>
        </<?php echo tag_escape($heading_tag); ?>>
      <?php endif; ?>

      <div class="<?php echo esc_attr(implode(' ', $grid_classes)); ?>">
        <?php foreach ($cards as $card):
          $link     = $resolve_link($card['link'] ?? null);
          $title    = trim((string) ($card['title'] ?? ''));
          $image    = $card['image'] ?? null;
          $wm       = $card['watermark'] ?? null;

          $img_url  = $image['url']  ?? '';
          $img_alt  = ($image['alt'] ?? '') ?: ($title ?: 'Service image');
          $img_ttl  = ($image['title'] ?? '') ?: $img_alt;

          $wm_url   = $wm['url'] ?? '';
          $wm_alt   = ($wm['alt'] ?? '') ?: 'Watermark';
          $wm_ttl   = ($wm['title'] ?? '') ?: $wm_alt;

          $card_classes = [
            'relative','overflow-hidden','border','border-neutral-200','bg-white',
            $card_radius,
          ];
          if ($card_shadow) $card_classes[] = 'shadow-sm';
        ?>
          <article class="<?php echo esc_attr(implode(' ', $card_classes)); ?>">
            <?php if ($link): ?>
              <a href="<?php echo $link['url']; ?>"
                 target="<?php echo $link['target']; ?>"
                 class="absolute inset-0 z-10"
                 aria-label="<?php echo esc_attr($title ?: $link['title']); ?>"></a>
            <?php endif; ?>

            <figure class="relative w-full h-48 sm:h-56 md:h-60 lg:h-64">
              <?php if (!empty($img_url)): ?>
                <img src="<?php echo esc_url($img_url); ?>"
                     alt="<?php echo esc_attr($img_alt); ?>"
                     title="<?php echo esc_attr($img_ttl); ?>"
                     class="object-cover absolute inset-0 w-full h-full" />
              <?php endif; ?>

              <?php if (!empty($wm_url)): ?>
                <img src="<?php echo esc_url($wm_url); ?>"
                     alt="<?php echo esc_attr($wm_alt); ?>"
                     title="<?php echo esc_attr($wm_ttl); ?>"
                     class="absolute right-2 bottom-2 w-24 h-auto pointer-events-none"
                     style="opacity: <?php echo esc_attr($wm_opacity); ?>;" />
              <?php endif; ?>
            </figure>

            <header class="flex justify-between items-center px-4 py-3">
              <h3 class="text-base font-semibold leading-snug md:text-lg"
                  style="color: <?php echo esc_attr($title_col); ?>;">
                <?php echo esc_html($title); ?>
              </h3>
              <!-- arrow -->
              <svg class="flex-shrink-0 w-6 h-6" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M15 7L20 12L15 17" stroke="<?php echo esc_attr($title_col); ?>" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"></path>
                <path d="M4 6V8C4 9.061 4.421 10.078 5.172 10.828C5.922 11.579 6.939 12 8 12H20" stroke="<?php echo esc_attr($title_col); ?>" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"></path>
              </svg>
            </header>
          </article>
        <?php endforeach; ?>
      </div>

      <?php if ($cta = $resolve_link($cta)): ?>
        <div class="flex justify-center mt-6">
          <a href="<?php echo $cta['url']; ?>"
             target="<?php echo $cta['target']; ?>"
             class="inline-flex gap-2.5 justify-center items-center px-3 h-9 font-medium rounded transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 bg-primary text-secondary hover:bg-opacity-80">
            <?php echo $cta['title']; ?>
          </a>
        </div>
      <?php endif; ?>
    </div>

  </div>
</section>
