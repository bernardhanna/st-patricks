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
$layout_style   = matrix_resolve_what_we_offer_layout_style(get_sub_field('layout_style'));
$intro_text     = trim((string) get_sub_field('intro_text'));
$vertical_padding = matrix_resolve_section_vertical_padding(get_sub_field('vertical_padding'));
$section_padding_classes = matrix_get_what_we_offer_section_padding_classes($vertical_padding);

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
    $background_gradient = 'var(--StPatricks_Aux_DarkBG4, linear-gradient(278deg, #F8F6F3 3.24%, #F5F6ED 90.88%))';
}

// Sanitize heading tag
$allowed_tags = ['h1','h2','h3','h4','h5','h6','span','p'];
if (!in_array($heading_tag, $allowed_tags, true)) {
    $heading_tag = 'h2';
}

$service_rows = [];

if (!empty($services) && is_array($services)) {
    foreach ($services as $index => $service) {
        $service_title       = (string) ($service['service_title'] ?? '');
        $service_description = (string) ($service['service_description'] ?? '');
        $service_link        = $service['service_link'] ?? null;
        $show_service_icon   = !empty($service['show_service_icon']);
        $has_service_link    = $service_link && is_array($service_link) && !empty($service_link['url']);

        $service_rows[] = [
            'index' => $index,
            'source' => $service,
            'title' => $service_title,
            'description' => $service_description,
            'link' => $service_link,
            'has_link' => $has_service_link,
            'show_icon' => $show_service_icon,
            'accent_color' => matrix_get_what_we_offer_accent_color($service, $index),
            'icon_background' => matrix_get_what_we_offer_intro_two_column_icon_background(
                matrix_get_what_we_offer_accent_color($service, $index)
            ),
        ];
    }
}
?>

<section id="<?php echo esc_attr($section_id); ?>"
         data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
         class="flex overflow-hidden relative"
         style="background: <?php echo esc_attr($background_gradient); ?>;">
    <div class="flex flex-col items-center w-full mx-auto max-w-container <?php echo esc_attr($section_padding_classes); ?> max-lg:px-5 max-sm:px-6">

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
                <<?php echo esc_attr($heading_tag); ?> class="text-3xl font-semibold tracking-tight leading-tight text-[#1E244B] max-sm:text-2xl">
                    <?php echo esc_html($heading); ?>
                </<?php echo esc_attr($heading_tag); ?>>
            <?php endif; ?>

            <div class="mt-4 w-10  bg-[#6FC9C0] h-[4px]"
                 role="presentation"
                 aria-hidden="true"></div>
            </div>
        <?php endif; ?>

        <?php if ($layout_style === 'image_feature') : ?>
            <!-- Main Content Grid -->
            <div class="flex flex-wrap justify-between items-start gap-10 mt-16 w-full  max-w-[1018px] max-md:mt-10">

              <!-- Services Container -->
              <?php if ($service_rows !== []) : ?>
                  <div class="grid flex-1 grid-cols-1 gap-x-10 gap-y-10 shrink basis-0 min-w-60 max-md:max-w-full md:grid-cols-2 tab:grid-cols-1">
                      <?php foreach ($service_rows as $service) : ?>
                      <article class="w-full">
                          <?php if ($service['has_link']) : ?>
                              <a
                                  href="<?php echo esc_url($service['link']['url']); ?>"
                                  target="<?php echo esc_attr($service['link']['target'] ?? '_self'); ?>"
                                  class="group flex overflow-hidden gap-6 items-start min-h-[140px] w-full rounded transition-colors duration-200"
                                  aria-label="<?php echo esc_attr($service['title'] ?: ($service['link']['title'] ?? 'View service')); ?>"
                              >
                          <?php else : ?>
                              <div class="group flex overflow-hidden gap-6 items-start min-h-[140px] w-full rounded">
                          <?php endif; ?>

                              <?php matrix_render_what_we_offer_service_rail($service); ?>

                              <div class="flex flex-col w-full min-w-0">
                                  <?php if ($service['title'] !== '') : ?>
                                      <div class="flex gap-2 items-center self-start text-2xl font-semibold tracking-normal leading-none min-h-[33px] text-indigo-950 max-sm:text-xl">
                                          <h3 class="self-stretch my-auto text-[#1E244B] transition-colors duration-200 group-hover:text-[#024B79]">
                                              <?php echo esc_html($service['title']); ?>
                                          </h3>

                                          <?php if ($service['show_icon']) : ?>
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

                                  <?php if ($service['description'] !== '') : ?>
                                      <div class="mt-4 text-base font-medium leading-7 text-[#08284B] wp_editor">
                                          <?php echo matrix_kses_rich_text($service['description']); ?>
                                      </div>
                                  <?php endif; ?>
                              </div>

                          <?php if ($service['has_link']) : ?>
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
        <?php endif; ?>

        <?php if ($layout_style === 'intro_two_column') : ?>
            <div class="mt-16 w-full max-w-[1018px] max-md:mt-10">
                <?php if ($intro_text !== '') : ?>
                    <div class="max-w-[606px] text-base font-medium leading-7 text-[#08284B] wp_editor">
                        <?php echo wpautop(esc_html($intro_text)); ?>
                    </div>
                <?php endif; ?>

                <?php if ($service_rows !== []) : ?>
                    <div class="mt-8 grid grid-cols-1 gap-x-10 gap-y-8 lg:grid-cols-2">
                        <?php foreach ($service_rows as $service) : ?>
                            <article class="w-full">
                                <?php if ($service['has_link']) : ?>
                                    <a
                                        href="<?php echo esc_url($service['link']['url']); ?>"
                                        target="<?php echo esc_attr($service['link']['target'] ?? '_self'); ?>"
                                        class="group flex min-h-[140px] gap-6 rounded transition-colors duration-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
                                        aria-label="<?php echo esc_attr($service['title'] ?: ($service['link']['title'] ?? 'View service')); ?>"
                                        <?php if (($service['link']['target'] ?? '_self') === '_blank') { ?>
                                            rel="noopener noreferrer"
                                        <?php } ?>
                                    >
                                <?php else : ?>
                                    <div class="group flex min-h-[140px] gap-6">
                                <?php endif; ?>

                                    <?php matrix_render_what_we_offer_service_rail($service); ?>

                                    <div class="flex min-w-0 flex-1 flex-col justify-start">
                                        <?php if ($service['title'] !== '') : ?>
                                            <div class="flex gap-2 items-center self-start text-2xl font-semibold leading-tight text-[#1E244B] max-sm:text-xl">
                                                <h3 class="text-inherit transition-colors duration-200 group-hover:text-[#024B79]">
                                                    <?php echo esc_html($service['title']); ?>
                                                </h3>

                                                <?php if ($service['show_icon']) : ?>
                                                    <svg
                                                        width="24" height="24" viewBox="0 0 24 24"
                                                        fill="none" xmlns="http://www.w3.org/2000/svg"
                                                        class="h-6 w-6 shrink-0 text-[#001F33] transition-colors duration-200 group-hover:text-[#024B79]"
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

                                        <?php if ($service['description'] !== '') : ?>
                                            <div class="mt-4 text-base font-medium leading-7 text-[#08284B] wp_editor">
                                                <?php echo matrix_kses_rich_text($service['description']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                <?php if ($service['has_link']) : ?>
                                    </a>
                                <?php else : ?>
                                    </div>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
