<?php
// Safe section id
$section_id = 'what-we-offer-' . ( function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid() );

// Get fields
$heading        = get_sub_field('heading');
$heading_tag    = get_sub_field('heading_tag') ?: 'h2';
$heading_link   = get_sub_field('heading_link');
$show_heading_icon = get_sub_field('show_heading_icon');
$services       = get_sub_field('services');
$main_image     = get_sub_field('main_image');

// Main image alt from media
$main_image_alt = '';
if ($main_image) {
    $main_image_alt = get_post_meta($main_image, '_wp_attachment_image_alt', true);
    if ($main_image_alt === '' || $main_image_alt === null) {
        $main_image_alt = 'Healthcare services illustration';
    }
}

// Background gradient (new)
$background_gradient = get_sub_field('background_gradient');
if ($background_gradient === '' || $background_gradient === null) {
    $background_gradient = 'var(--StPatricks_Aux_DarkBG4, linear-gradient(278deg, #F6EDE0 3.24%, #F4F5DE 90.88%))';
}

// Padding settings
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
if (!in_array($heading_tag, $allowed_tags, true)) {
    $heading_tag = 'h2';
}

$service_hover_icon_url = home_url('/wp-content/uploads/2026/03/left.svg');
?>

<section id="<?php echo esc_attr($section_id); ?>"
         data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
         class="flex overflow-hidden relative"
         style="background: <?php echo esc_attr($background_gradient); ?>;">
    <div class="flex flex-col items-center w-full mx-auto max-w-container py-12 lg:py-24 max-lg:px-5 max-sm:px-6 <?php echo esc_attr(implode(' ', $padding_classes)); ?>">

        <!-- Header Section -->
        <?php if (!empty($heading)) : ?>
        <div class="flex flex-col justify-center w-full max-w-container_md">
            <?php if ($heading_link && is_array($heading_link) && !empty($heading_link['url']) && !empty($heading_link['title'])) : ?>
                <a
                    href="<?php echo esc_url($heading_link['url']); ?>"
                    target="<?php echo esc_attr($heading_link['target'] ?? '_self'); ?>"
                    class="inline-flex gap-2 items-center text-3xl font-semibold tracking-tight leading-tight transition-colors duration-200 text-indigo-950 hover:text-primary hover:underline btn max-sm:text-2xl w-fit"
                    aria-label="<?php echo esc_attr($heading_link['title']); ?>"
                >
                    <<?php echo esc_attr($heading_tag); ?> class="text-inherit font-inherit leading-inherit tracking-inherit">
                        <?php echo esc_html($heading); ?>
                    </<?php echo esc_attr($heading_tag); ?>>

                    <?php if ($show_heading_icon) : ?>
                        <svg
                            width="24" height="24" viewBox="0 0 24 24"
                            fill="none" xmlns="http://www.w3.org/2000/svg"
                            class="w-6 h-6 transition-colors duration-200"
                            aria-hidden="true"
                        >
                            <path
                                d="M9 18L15 12L9 6"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    <?php endif; ?>
                </a>
            <?php else : ?>
                <<?php echo esc_attr($heading_tag); ?> class="text-3xl font-semibold tracking-tight leading-tight text-indigo-950 max-sm:text-2xl">
                    <?php echo esc_html($heading); ?>
                </<?php echo esc_attr($heading_tag); ?>>
            <?php endif; ?>

            <div class="mt-4 w-10  bg-[#F86] h-[4px]"
                 role="presentation"
                 aria-hidden="true"></div>
            </div>
        <?php endif; ?>

        <!-- Main Content Grid -->
        <div class="flex flex-wrap justify-between items-start mt-16 w-full  max-w-[1018px] max-md:mt-10">

          <!-- Services Container -->
          <?php if (!empty($services) && is_array($services)) : ?>
              <div class="grid flex-1 grid-cols-1 gap-x-10 gap-y-10 shrink basis-0 min-w-60 max-md:max-w-full md:grid-cols-2 tab:grid-cols-1">
                  <?php foreach ($services as $service) :
                      $service_icon        = $service['service_icon'] ?? null;
                      $service_title       = $service['service_title'] ?? '';
                      $service_description = $service['service_description'] ?? '';
                      $service_link        = $service['service_link'] ?? null;
                      $show_service_icon   = !empty($service['show_service_icon']);
                      $service_icon_url    = $service_icon ? wp_get_attachment_image_url($service_icon, 'full') : '';
                      $has_service_link    = $service_link && is_array($service_link) && !empty($service_link['url']);

                      $service_icon_alt = '';
                      if ($service_icon) {
                          $service_icon_alt = get_post_meta($service_icon, '_wp_attachment_image_alt', true);
                          if ($service_icon_alt === '' || $service_icon_alt === null) {
                              $service_icon_alt = $service_title ? $service_title . ' icon' : 'Service icon';
                          }
                      }
                  ?>
                  <article class="w-full">
                      <?php if ($has_service_link) : ?>
                          <a
                              href="<?php echo esc_url($service_link['url']); ?>"
                              target="<?php echo esc_attr($service_link['target'] ?? '_self'); ?>"
                              class="group flex overflow-hidden gap-6 items-start min-h-[140px] w-full rounded transition-colors duration-200"
                              aria-label="<?php echo esc_attr($service_title ?: ($service_link['title'] ?? 'View service')); ?>"
                          >
                      <?php else : ?>
                          <div class="group flex overflow-hidden gap-6 items-start min-h-[140px] w-full rounded">
                      <?php endif; ?>

                          <?php if ($service_icon_url) : ?>
                              <div class="relative shrink-0">
                                  <img
                                      src="<?php echo esc_url($service_icon_url); ?>"
                                      alt="<?php echo esc_attr($service_icon_alt); ?>"
                                      class="object-contain transition-opacity duration-200 group-hover:opacity-0"
                                      decoding="async"
                                      loading="lazy"
                                  />
                                  <img
                                      src="<?php echo esc_url($service_hover_icon_url); ?>"
                                      alt="<?php echo esc_attr($service_icon_alt); ?>"
                                      class="object-contain absolute inset-0 opacity-0 transition-opacity duration-200 group-hover:opacity-100"
                                      decoding="async"
                                      loading="lazy"
                                  />
                              </div>
                          <?php endif; ?>

                          <div class="flex flex-col w-full min-w-0">
                              <?php if (!empty($service_title)) : ?>
                                  <div class="flex gap-2 items-center self-start text-2xl font-semibold tracking-normal leading-none min-h-[33px] text-indigo-950 max-sm:text-xl">
                                      <h3 class="self-stretch my-auto text-indigo-950 transition-colors duration-200 group-hover:text-[#024B79]">
                                          <?php echo esc_html($service_title); ?>
                                      </h3>

                                      <?php if ($show_service_icon) : ?>
                                          <svg
                                              width="24" height="24" viewBox="0 0 24 24"
                                              fill="none" xmlns="http://www.w3.org/2000/svg"
                                              class="w-6 h-6 transition-colors duration-200 text-[#001F33] group-hover:text-[#024B79]"
                                              aria-hidden="true"
                                          >
                                              <path
                                                  d="M8 4L16 12L8 20"
                                                  stroke="currentColor"
                                                  stroke-width="2"
                                                  stroke-linecap="round"
                                                  stroke-linejoin="round"
                                              />
                                          </svg>
                                      <?php endif; ?>
                                  </div>
                              <?php endif; ?>

                              <?php if (!empty($service_description)) : ?>
                                  <div class="mt-4 text-base font-medium leading-7 text-teal-950 wp_editor">
                                      <?php echo wp_kses_post($service_description); ?>
                                  </div>
                              <?php endif; ?>
                          </div>

                      <?php if ($has_service_link) : ?>
                          </a>
                      <?php else : ?>
                          </div>
                      <?php endif; ?>
                  </article>
                  <?php endforeach; ?>
              </div>
          <?php endif; ?>

            <!-- Main Image -->
            <?php if ($main_image) : ?>
            <aside class="flex-1 shrink w-full basis-0 min-h-[656px] min-w-60 tab:flex max-md:max-w-full max-tab:hidden">
                <?php echo wp_get_attachment_image($main_image, 'full', false, [
                    'alt'   => esc_attr($main_image_alt),
                    'class' => 'object-contain flex-1 shrink w-full min-h-[656px] min-w-60 max-md:flex max-md:max-w-full',
                ]); ?>
            </aside>
            <?php endif; ?>
        </div>
    </div>
</section>