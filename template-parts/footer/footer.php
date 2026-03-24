<?php
// Colors / Padding
$background_color = get_field('background_color', 'option') ?: '#ffffff';

$padding_classes = [];
if (have_rows('padding_settings', 'option')) {
    while (have_rows('padding_settings', 'option')) {
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

// Brand strip (optional background logos) — used only on desktop block
$brand_strip_opacity = get_field('brand_strip_opacity', 'option');
$brand_strip_opacity = ($brand_strip_opacity === '' || $brand_strip_opacity === null) ? 0.25 : (float) $brand_strip_opacity;
$brand_strip_logos   = get_field('brand_strip_logos', 'option');

// Footer logo (fallback to custom_logo)
$footer_logo = get_field('footer_logo', 'option');
if (!$footer_logo) {
    $logo_id  = get_theme_mod('custom_logo');
    if ($logo_id) {
        $footer_logo = [
            'url'   => wp_get_attachment_image_url($logo_id, 'full'),
            'alt'   => get_post_meta($logo_id, '_wp_attachment_image_alt', true) ?: get_bloginfo('name'),
            'title' => get_the_title($logo_id) ?: get_bloginfo('name'),
        ];
    }
}

// Columns
$about_heading = get_field('about_heading', 'option') ?: 'About us';
$about_links   = get_field('about_links', 'option');

$quick_heading = get_field('quick_links_heading', 'option') ?: 'Quick Links';
$quick_links   = get_field('quick_links', 'option');

$media_heading = get_field('media_heading', 'option') ?: 'Media';
$media_links   = get_field('media_links', 'option');

$careers_heading = get_field('careers_heading', 'option') ?: 'Careers';
$careers_links   = get_field('careers_links', 'option');

$contact_heading    = get_field('contact_heading', 'option') ?: 'Contact us';
$locations_heading  = get_field('locations_heading', 'option') ?: 'Our Locations';
$contact_phone_link = get_field('contact_phone_link', 'option');
$contact_email_link = get_field('contact_email_link', 'option');

// Social
$social_heading = get_field('social_heading', 'option') ?: 'Social media';
$social_links   = get_field('social_links', 'option');

// Legal / copyright
$copyright_text        = get_field('copyright_text', 'option') ?: ('St Patrick Hospital © ' . date('Y'));
$legal_links           = get_field('legal_links', 'option');
$developer_credit      = get_field('developer_credit', 'option') ?: 'Designed & Developed by';
$developer_credit_link = get_field('developer_credit_link', 'option');

// Helper
if (!function_exists('matrix_resolve_link')) {
    function matrix_resolve_link($raw) {
        $out = ['url' => '', 'title' => '', 'target' => '_self'];
        if (is_array($raw) && isset($raw['link'])) {
            $raw = $raw['link'];
        }
        if (is_array($raw)) {
            $out['url']    = (string) ($raw['url'] ?? '');
            $out['title']  = (string) ($raw['title'] ?? '');
            $out['target'] = (string) ($raw['target'] ?? '_self');
            return $out;
        }
        if (is_string($raw)) {
            $out['url']   = $raw;
            $out['title'] = $raw;
            return $out;
        }
        return $out;
    }
}

// Simple chevron icon (down)
$chev_svg = '<svg width="16" height="16" viewBox="0 0 16 16" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M4 6L8 10L12 6" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';

// Social icons (mobile)
$mob_icons = [
    'twitter'  => '<svg width="32" height="32" viewBox="0 0 32 32" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M20.1344 9.5H22.3406L17.5219 15.0062L23.1906 22.5H18.7531L15.275 17.9563L11.3 22.5H9.09063L14.2438 16.6094L8.80938 9.5H13.3594L16.5 13.6531L20.1344 9.5ZM19.3594 21.1812H20.5813L12.6938 10.75H11.3813L19.3594 21.1812Z" fill="#024B79"/></svg>',
    'facebook' => '<svg width="32" height="32" viewBox="0 0 32 32" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M19.723 16.9995L20.1675 14.104H17.389V12.225C17.389 11.433 17.777 10.6605 19.0215 10.6605H20.2845V8.1955C20.2845 8.1955 19.1385 8 18.0425 8C15.7545 8 14.259 9.387 14.259 11.8975V14.1045H11.7155V17H14.259V24H17.389V17L19.723 16.9995Z" fill="#024B79"/></svg>',
    'tiktok'   => '<svg width="32" height="32" viewBox="0 0 32 32" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M16.3513 8.01399C17.2238 8.00049 18.0913 8.00849 18.9578 8.00049C19.0103 9.02099 19.3773 10.0605 20.1243 10.782C20.8698 11.5215 21.9243 11.86 22.9503 11.9745V14.659C21.9888 14.6275 21.0228 14.4275 20.1503 14.0135C19.7703 13.8415 19.4163 13.62 19.0698 13.3935C19.0653 15.3415 19.0778 17.287 19.0573 19.227C19.0053 20.159 18.6978 21.0865 18.1558 21.8545C17.2838 23.133 15.7703 23.9665 14.2158 23.9925C13.2623 24.047 12.3098 23.787 11.4973 23.308C10.1508 22.514 9.20327 21.0605 9.06527 19.5005C9.04769 19.1701 9.04502 18.8391 9.05727 18.5085C9.17727 17.24 9.80477 16.0265 10.7788 15.201C11.8828 14.2395 13.4293 13.7815 14.8773 14.0525C14.8908 15.04 14.8513 16.0265 14.8513 17.014C14.1898 16.8 13.4168 16.86 12.8388 17.2615C12.4159 17.5401 12.0964 17.95 11.9293 18.428C11.7913 18.766 11.8308 19.1415 11.8388 19.5005C11.9973 20.5945 13.0493 21.514 14.1723 21.4145C14.9168 21.4065 15.6303 20.9745 16.0183 20.342C16.1438 20.1205 16.2843 19.894 16.2918 19.6335C16.3573 18.441 16.3313 17.2535 16.3393 16.061C16.3448 13.3735 16.3313 10.6935 16.3518 8.01449Z" fill="#024B79"/></svg>',
    'instagram'=> '<svg width="32" height="32" viewBox="0 0 32 32" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M16 8.00098C13.8255 8.00098 13.5545 8.01148 12.7035 8.04748C11.849 8.08948 11.271 8.22198 10.761 8.41998C10.2273 8.62153 9.74387 8.93702 9.34451 9.34448C8.93642 9.74332 8.62084 10.2269 8.42001 10.761C8.22201 11.271 8.08951 11.849 8.04751 12.7035C8.00901 13.555 8.00101 13.8255 8.00101 16C8.00101 18.1745 8.01151 18.4455 8.04751 19.2965C8.08951 20.1485 8.22201 20.729 8.42001 21.239C8.62156 21.7727 8.93705 22.2561 9.34451 22.6555C9.74335 23.0636 10.2269 23.3791 10.761 23.58C11.271 23.7755 11.8515 23.9105 12.7035 23.9525C13.555 23.991 13.8255 23.999 16 23.999C18.1745 23.999 18.4455 23.9885 19.2965 23.9525C20.1485 23.9105 20.729 23.775 21.239 23.58C21.7728 23.3784 22.2561 23.0629 22.6555 22.6555C23.0649 22.2577 23.3807 21.7738 23.58 21.239C23.7755 20.729 23.9105 20.1485 23.9525 19.2965C23.991 18.445 23.999 18.1745 23.999 16C23.999 13.8255 23.9885 13.5545 23.9525 12.7035C23.9105 11.8515 23.775 11.268 23.58 10.761C23.3785 10.2272 23.063 9.74384 22.6555 9.34448C22.2577 8.93511 21.7739 8.61933 21.239 8.41998C20.729 8.22198 20.1485 8.08948 19.2965 8.04748C18.445 8.00898 18.1745 8.00098 16 8.00098ZM16 9.44098C18.1355 9.44098 18.3905 9.45148 19.2345 9.48748C20.013 9.52398 20.437 9.65398 20.7185 9.76398C21.0664 9.8926 21.3814 10.097 21.6405 10.3625C21.906 10.62 22.1098 10.9344 22.2365 11.282C22.3465 11.5635 22.4765 11.9875 22.513 12.766C22.549 13.61 22.5595 13.8655 22.5595 16.0005C22.5595 18.1355 22.549 18.391 22.51 19.235C22.468 20.0135 22.338 20.4375 22.2285 20.719C22.077 21.0945 21.908 21.357 21.629 21.641C21.3691 21.9049 21.0543 22.1083 20.707 22.237C20.429 22.347 19.999 22.477 19.2175 22.5135C18.369 22.5495 18.119 22.56 15.978 22.56C13.837 22.56 13.5875 22.5495 12.738 22.5105C11.9595 22.4685 11.53 22.3385 11.2485 22.229C10.8685 22.0775 10.608 21.9085 10.329 21.6295C10.0475 21.348 9.86851 21.08 9.73051 20.7075C9.61851 20.4295 9.49051 19.9995 9.44901 19.218C9.42051 18.3795 9.40701 18.1195 9.40701 15.9885C9.40701 13.8585 9.42051 13.598 9.44901 12.749C9.49051 11.9675 9.61851 11.5385 9.73051 11.2595C9.86851 10.879 10.048 10.619 10.329 10.3375C10.6075 10.059 10.8685 9.87898 11.2485 9.73798C11.53 9.62848 11.949 9.49848 12.7305 9.45948C13.579 9.42898 13.829 9.41798 15.967 9.41798L16 9.44098Z" fill="#024B79"/></svg>',
];

// ====== MOBILE FOOTER (lg and below) ======
?>
<footer class="px-4 lg:hidden bg-light pt-section" role="contentinfo" aria-label="Site footer (mobile)">
  <div class="mx-auto max-w-7xl">
    <?php if (!empty($footer_logo['url'])): ?>
      <img src="<?php echo esc_url($footer_logo['url']); ?>"
           alt="<?php echo esc_attr($footer_logo['alt'] ?? get_bloginfo('name')); ?>"
           title="<?php echo esc_attr($footer_logo['title'] ?? get_bloginfo('name')); ?>"
           class="w-[200px] h-[52px] mb-8" />
    <?php endif; ?>

    <?php
    // ✅ Fixed: use a closure (anonymous function) so we can use ($chev_svg)
    $mobile_footer_accordion = function($id_base, $heading, $links) use ($chev_svg) {
        $btn_id   = $id_base . '-btn';
        $panel_id = $id_base . '-panel'; ?>
        <div class="py-4 mb-4 border-b border-primary-light">
          <button type="button"
                  id="<?php echo esc_attr($btn_id); ?>"
                  class="flex justify-between items-center w-full"
                  aria-expanded="false"
                  aria-controls="<?php echo esc_attr($panel_id); ?>">
            <h3 class="font-semibold text-h4 font-heading text-dark-bg"><?php echo esc_html($heading); ?></h3>
            <span class="transition-transform duration-200" aria-hidden="true"><?php echo $chev_svg; ?></span>
          </button>

          <div id="<?php echo esc_attr($panel_id); ?>" class="hidden mt-3">
            <?php if (!empty($links)): ?>
              <nav class="flex flex-col gap-3" aria-label="<?php echo esc_attr($heading); ?>">
                <?php foreach ($links as $row):
                  $link = matrix_resolve_link($row);
                  if (empty($link['url'])) continue; ?>
                  <a href="<?php echo esc_url($link['url']); ?>"
                     target="<?php echo esc_attr($link['target'] ?: '_self'); ?>"
                     class="text-sm text-dark-bg hover:underline">
                    <?php echo esc_html($link['title'] ?: $link['url']); ?>
                  </a>
                <?php endforeach; ?>
              </nav>
            <?php endif; ?>
          </div>
        </div>
    <?php }; ?>

    <?php $mobile_footer_accordion('mob-about',  $about_heading,   $about_links); ?>
    <?php $mobile_footer_accordion('mob-quick',  $quick_heading,   $quick_links); ?>
    <?php $mobile_footer_accordion('mob-media',  $media_heading,   $media_links); ?>
    <?php $mobile_footer_accordion('mob-career', $careers_heading, $careers_links); ?>

    <div class="mb-8">
      <h3 class="mb-2 font-semibold text-h4 font-heading text-dark-bg"><?php echo esc_html($contact_heading); ?></h3>
      <p class="mb-6 text-sm font-medium text-dark-bg"><?php echo esc_html($locations_heading); ?></p>

      <div class="flex flex-col gap-3 mb-3">
        <?php if (!empty($contact_phone_link['url'])): ?>
        <div class="flex gap-3 items-center">
          <svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M22.0001 16.9201..." stroke="#024B79" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"></path><path d="M14.05 2..." stroke="#024B79" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"></path><path d="M14.05 6..." stroke="#024B79" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"></path></svg>
          <a href="<?php echo esc_url($contact_phone_link['url']); ?>" target="<?php echo esc_attr($contact_phone_link['target'] ?: '_self'); ?>" class="text-sm text-dark-bg hover:underline">
            <?php echo esc_html($contact_phone_link['title'] ?: $contact_phone_link['url']); ?>
          </a>
        </div>
        <?php endif; ?>

        <?php if (!empty($contact_email_link['url'])): ?>
        <div class="flex gap-3 items-center">
          <svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M20 4H4C2.89543..." stroke="#024B79" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"></path><path d="M22 7L13.03..." stroke="#024B79" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"></path></svg>
          <a href="<?php echo esc_url($contact_email_link['url']); ?>" target="<?php echo esc_attr($contact_email_link['target'] ?: '_self'); ?>" class="text-sm text-dark-bg hover:underline">
            <?php echo esc_html($contact_email_link['title'] ?: $contact_email_link['url']); ?>
          </a>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="mb-8 lg:mb-0">
      <h3 class="mb-4 font-semibold text-h4 font-heading text-dark-bg"><?php echo esc_html($social_heading); ?></h3>
      <div class="flex gap-3 items-center">
        <?php if (!empty($social_links)): foreach ($social_links as $s):
          $platform = $s['platform'] ?? '';
          $url      = $s['url'] ?? '';
          $target   = $s['target'] ?? '_blank';
          if (!$url) continue; ?>
          <a href="<?php echo esc_url($url); ?>" target="<?php echo esc_attr($target); ?>" <?php echo $target === '_blank' ? 'rel="noopener noreferrer"' : ''; ?> class="flex justify-center items-center w-8 h-8 rounded-full transition-colors bg-primary-light hover:bg-opacity-80" aria-label="<?php echo esc_attr(ucfirst($platform)); ?>">
            <?php echo $mob_icons[$platform] ?? ''; ?>
          </a>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <div class="pt-6 pb-6 border-t border-primary-light">
      <div class="flex flex-col gap-4 md:flex-row md:justify-between md:items-center">
        <div class="flex flex-col gap-2 text-sm md:flex-row md:gap-6 text-dark-bg">
          <p><?php echo esc_html($copyright_text); ?></p>
          <?php if (!empty($legal_links)): foreach ($legal_links as $row):
            $link = matrix_resolve_link($row);
            if (empty($link['url'])) continue; ?>
            <a href="<?php echo esc_url($link['url']); ?>" target="<?php echo esc_attr($link['target'] ?: '_self'); ?>" class="hover:underline">
              <?php echo esc_html($link['title'] ?: $link['url']); ?>
            </a>
          <?php endforeach; endif; ?>
        </div>
        <div class="flex gap-1 items-center text-sm text-dark-bg">
          <span><?php echo esc_html($developer_credit); ?></span>
          <?php if (!empty($developer_credit_link['url'])): ?>
            <a href="<?php echo esc_url($developer_credit_link['url']); ?>" target="<?php echo esc_attr($developer_credit_link['target'] ?: '_blank'); ?>" rel="noopener noreferrer" class="hover:underline">
              <?php echo esc_html($developer_credit_link['title'] ?: 'Matrix Internet'); ?>
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</footer>

<script>
(function(){
  // Simple accordion for mobile (lg:hidden footer)
  var root = document.currentScript ? document.currentScript.previousElementSibling : null;
  while (root && !(root.matches && root.matches('footer.lg\\:hidden'))) { root = root.previousElementSibling; }
  if (!root) return;

  var buttons = root.querySelectorAll('button[aria-controls]');
  buttons.forEach(function(btn){
    btn.addEventListener('click', function(){
      var expanded = btn.getAttribute('aria-expanded') === 'true';
      var panelId = btn.getAttribute('aria-controls');
      var panel = document.getElementById(panelId);
      btn.setAttribute('aria-expanded', expanded ? 'false' : 'true');
      if (panel) {
        panel.classList.toggle('hidden', expanded);
        var svgWrap = btn.querySelector('span');
        if (svgWrap) svgWrap.style.transform = expanded ? 'rotate(0deg)' : 'rotate(180deg)';
      }
    });
  });
})();
</script>

<?php
// ====== DESKTOP FOOTER (lg and up) — your existing grid version ======
?>
<footer class="hidden w-full bg-white lg:block"
        style="background-color: <?php echo esc_attr($background_color); ?>;"
        role="contentinfo"
        aria-label="Site footer (desktop)">

    <section class="flex overflow-hidden relative">
        <?php if (!empty($brand_strip_logos)) : ?>
            <div class="absolute inset-0 pointer-events-none" style="opacity: <?php echo esc_attr($brand_strip_opacity); ?>;">
                <div class="flex justify-center items-center h-24">
                    <div class="flex gap-2">
                        <?php foreach ($brand_strip_logos as $row) :
                            $img = $row['image'] ?? null;
                            $w   = isset($row['width'])  ? (int) $row['width']  : 112;
                            $h   = isset($row['height']) ? (int) $row['height'] : 96;
                            if (!$img || empty($img['url'])) {
                                continue;
                            }
                            $alt   = $img['alt']   ?? 'Brand logo';
                            $title = $img['title'] ?? $alt;
                            ?>
                            <img src="<?php echo esc_url($img['url']); ?>"
                                 alt="<?php echo esc_attr($alt); ?>"
                                 title="<?php echo esc_attr($title); ?>"
                                 class="flex-shrink-0"
                                 style="width: <?php echo esc_attr($w); ?>px; height: <?php echo esc_attr($h); ?>px;">
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="flex flex-col items-center w-full mx-auto max-w-[1300px] pt-12 pb-5 max-lg:px-5 <?php echo esc_attr(implode(' ', $padding_classes)); ?>">
            <div class="w-full max-xl:px-5">

                <!-- Top navigation area as GRID -->
                <nav class="grid grid-cols-1 gap-10 items-start w-full max-md:max-w-full md:gap-8 lg:gap-10 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6"
                     aria-label="Footer navigation">

                    <!-- Logo -->
                    <div class="flex-shrink-0">
                        <?php if (!empty($footer_logo['url'])) : ?>
                            <img src="<?php echo esc_url($footer_logo['url']); ?>"
                                 alt="<?php echo esc_attr($footer_logo['alt'] ?? get_bloginfo('name')); ?>"
                                 title="<?php echo esc_attr($footer_logo['title'] ?? get_bloginfo('name')); ?>"
                                 class="object-contain w-[140px] aspect-[1.24]">
                        <?php else : ?>
                            <span class="text-xl font-semibold text-indigo-950">
                                <?php echo esc_html(get_bloginfo('name')); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- About us -->
                    <section class="text-indigo-950" aria-labelledby="footer-about-heading">
                        <h2 id="footer-about-heading" class="text-xl font-semibold tracking-normal leading-snug text-indigo-950">
                            <?php echo esc_html($about_heading); ?>
                        </h2>

                        <?php if (!empty($about_links)) : ?>
                            <nav class="flex flex-col justify-center items-start mt-5 text-sm leading-6" aria-label="About us links">
                                <?php foreach ($about_links as $row) :
                                    $link = matrix_resolve_link($row);
                                    if (empty($link['url'])) continue; ?>
                                    <a href="<?php echo esc_url($link['url']); ?>"
                                       target="<?php echo esc_attr($link['target'] ?: '_self'); ?>"
                                       class="flex gap-2.5 justify-center items-center whitespace-nowrap btn w-fit hover:text-hover focus:text-hover">
                                        <span class="self-stretch my-auto text-indigo-950">
                                            <?php echo esc_html($link['title'] ?: $link['url']); ?>
                                        </span>
                                    </a>
                                <?php endforeach; ?>
                            </nav>
                        <?php endif; ?>
                    </section>

                    <!-- Quick Links -->
                    <section class="text-indigo-950" aria-labelledby="footer-quick-links-heading">
                        <h2 id="footer-quick-links-heading" class="text-xl font-semibold tracking-normal leading-snug text-indigo-950">
                            <?php echo esc_html($quick_heading); ?>
                        </h2>

                        <?php if (!empty($quick_links)) : ?>
                            <nav class="flex flex-col justify-center items-start mt-5 text-sm leading-6" aria-label="Quick links">
                                <?php foreach ($quick_links as $row) :
                                    $link = matrix_resolve_link($row);
                                    if (empty($link['url'])) continue; ?>
                                    <a href="<?php echo esc_url($link['url']); ?>"
                                       target="<?php echo esc_attr($link['target'] ?: '_self'); ?>"
                                       class="flex gap-2.5 justify-center items-center btn w-fit hover:text-hover focus:text-hover">
                                        <span class="self-stretch my-auto text-indigo-950">
                                            <?php echo esc_html($link['title'] ?: $link['url']); ?>
                                        </span>
                                    </a>
                                <?php endforeach; ?>
                            </nav>
                        <?php endif; ?>
                    </section>

                    <!-- Media -->
                    <section class="text-indigo-950" aria-labelledby="footer-media-heading">
                        <h2 id="footer-media-heading" class="text-xl font-semibold tracking-normal leading-snug text-indigo-950">
                            <?php echo esc_html($media_heading); ?>
                        </h2>

                        <?php if (!empty($media_links)) : ?>
                            <nav class="flex flex-col justify-center items-start mt-5 text-sm leading-6" aria-label="Media links">
                                <?php foreach ($media_links as $row) :
                                    $link = matrix_resolve_link($row);
                                    if (empty($link['url'])) continue; ?>
                                    <a href="<?php echo esc_url($link['url']); ?>"
                                       target="<?php echo esc_attr($link['target'] ?: '_self'); ?>"
                                       class="flex gap-2.5 justify-center items-center whitespace-nowrap btn w-fit hover:text-hover focus:text-hover">
                                        <span class="self-stretch my-auto text-indigo-950">
                                            <?php echo esc_html($link['title'] ?: $link['url']); ?>
                                        </span>
                                    </a>
                                <?php endforeach; ?>
                            </nav>
                        <?php endif; ?>
                    </section>

                    <!-- Careers -->
                    <section class="text-indigo-950" aria-labelledby="footer-careers-heading">
                        <h2 id="footer-careers-heading" class="text-xl font-semibold tracking-normal leading-snug text-indigo-950">
                            <?php echo esc_html($careers_heading); ?>
                        </h2>

                        <?php if (!empty($careers_links)) : ?>
                            <nav class="flex flex-col items-start mt-4 text-sm leading-6" aria-label="Career links">
                                <?php foreach ($careers_links as $row) :
                                    $link = matrix_resolve_link($row);
                                    if (empty($link['url'])) continue; ?>
                                    <a href="<?php echo esc_url($link['url']); ?>"
                                       target="<?php echo esc_attr($link['target'] ?: '_self'); ?>"
                                       class="flex gap-2.5 justify-center items-center whitespace-nowrap btn w-fit hover:text-hover focus:text-hover">
                                        <span class="self-stretch my-auto text-indigo-950">
                                            <?php echo esc_html($link['title'] ?: $link['url']); ?>
                                        </span>
                                    </a>
                                <?php endforeach; ?>
                            </nav>
                        <?php endif; ?>
                    </section>

                    <!-- Contact + Social -->
                    <section aria-labelledby="footer-contact-heading">
                        <div class="flex flex-col w-full text-indigo-950">
                            <h2 id="footer-contact-heading" class="text-xl font-semibold tracking-normal leading-snug text-indigo-950">
                                <?php echo esc_html($contact_heading); ?>
                            </h2>

                            <address class="flex flex-col self-start mt-6 text-sm not-italic leading-6">
                                <h3 class="font-medium text-indigo-950">
                                    <?php echo esc_html($locations_heading); ?>
                                </h3>

                                <?php if (!empty($contact_phone_link['url'])) : ?>
                                    <div class="flex gap-3 items-center self-start mt-4">
                                        <div class="flex self-stretch my-auto w-6 h-6 shrink-0" aria-hidden="true">
                                            <svg width="24" height="24" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M21.9999 17.1071..." stroke="#001F33" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </div>
                                        <a href="<?php echo esc_url($contact_phone_link['url']); ?>"
                                           target="<?php echo esc_attr($contact_phone_link['target'] ?: '_self'); ?>"
                                           class="flex gap-2.5 justify-center items-center self-stretch my-auto btn w-fit hover:text-hover focus:text-hover">
                                            <span class="self-stretch my-auto text-indigo-950">
                                                <?php echo esc_html($contact_phone_link['title'] ?: $contact_phone_link['url']); ?>
                                            </span>
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($contact_email_link['url'])) : ?>
                                    <div class="flex gap-3 items-center mt-4 whitespace-nowrap">
                                        <div class="flex self-stretch my-auto w-6 h-6 shrink-0" aria-hidden="true">
                                            <svg width="24" height="24" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 4.18701..." stroke="#001F33" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </div>
                                        <a href="<?php echo esc_url($contact_email_link['url']); ?>"
                                           target="<?php echo esc_attr($contact_email_link['target'] ?: '_self'); ?>"
                                           class="flex gap-2.5 justify-center items-center self-stretch my-auto btn w-fit hover:text-hover focus:text-hover">
                                            <span class="self-stretch my-auto text-indigo-950">
                                                <?php echo esc_html($contact_email_link['title'] ?: $contact_email_link['url']); ?>
                                            </span>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </address>
                        </div>

                        <!-- Social Media -->
                        <section class="mt-7 w-full max-w-[183px]" aria-labelledby="footer-social-heading">
                            <h2 id="footer-social-heading" class="text-xl font-semibold tracking-normal leading-snug text-indigo-950">
                                <?php echo esc_html($social_heading); ?>
                            </h2>

                            <?php if (!empty($social_links)) : ?>
                                <nav class="flex gap-3 items-start mt-4" aria-label="Social media links">
                                    <?php
                                    $icons = [
                                        'twitter'  => '<svg class="w-4 h-4 text-sky-900" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M12.0859 2.5H14.1719..." /></svg>',
                                        'facebook' => '<svg class="w-4 h-4 text-sky-900" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M11.7227 9L12.1672..." /></svg>',
                                        'tiktok'   => '<svg class="w-4 h-4 text-sky-900" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M8.35156 0.0195312..." /></svg>',
                                        'instagram'=> '<svg class="w-4 h-4 text-sky-900" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M8 0C5.82812 0..." /></svg>',
                                    ];
                                    foreach ($social_links as $s) :
                                        $platform = $s['platform'] ?? '';
                                        $url      = $s['url'] ?? '';
                                        $target   = $s['target'] ?? '_blank';
                                        if (!$url) continue; ?>
                                        <a href="<?php echo esc_url($url); ?>"
                                           target="<?php echo esc_attr($target); ?>"
                                           <?php echo $target === '_blank' ? 'rel="noopener noreferrer"' : ''; ?>
                                           aria-label="<?php echo esc_attr(ucfirst($platform)); ?>"
                                           class="flex justify-center items-center w-8 h-8 bg-sky-200 rounded-[32px] btn hover:bg-opacity-80 focus:bg-opacity-80">
                                            <?php echo $icons[$platform] ?? ''; ?>
                                        </a>
                                    <?php endforeach; ?>
                                </nav>
                            <?php endif; ?>
                        </section>
                    </section>
                </nav>

                <!-- Bottom legal & copyright -->
                <section class="overflow-hidden mt-16 w-full text-sm text-indigo-950 max-md:mt-10 max-md:max-w-full" aria-label="Legal and copyright information">
                    <div class="flex flex-wrap items-center py-6 w-full border-t border-solid border-t-sky-200 max-md:max-w-full">
                        <nav class="flex flex-wrap flex-1 gap-6 items-center self-stretch my-auto leading-none shrink basis-0 min-w-60 max-md:max-w-full" aria-label="Legal links">
                            <span class="self-stretch my-auto text-indigo-950">
                                <?php echo esc_html($copyright_text); ?>
                            </span>

                            <?php if (!empty($legal_links)) : ?>
                                <?php foreach ($legal_links as $row) :
                                    $link = matrix_resolve_link($row);
                                    if (empty($link['url'])) continue; ?>
                                    <span class="self-stretch my-auto text-sky-200" aria-hidden="true">|</span>
                                    <a href="<?php echo esc_url($link['url']); ?>"
                                       target="<?php echo esc_attr($link['target'] ?: '_self'); ?>"
                                       class="flex gap-2.5 justify-center items-center self-stretch my-auto leading-6 whitespace-nowrap btn w-fit hover:text-hover focus:text-hover">
                                        <span class="self-stretch my-auto text-indigo-950">
                                            <?php echo esc_html($link['title'] ?: $link['url']); ?>
                                        </span>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </nav>

                        <div class="flex gap-1 items-center self-stretch my-auto min-w-60">
                            <span class="self-stretch my-auto leading-none text-indigo-950">
                                <?php echo esc_html($developer_credit); ?>
                            </span>
                            <?php if (!empty($developer_credit_link['url'])) : ?>
                                <a href="<?php echo esc_url($developer_credit_link['url']); ?>"
                                   target="<?php echo esc_attr($developer_credit_link['target'] ?: '_blank'); ?>"
                                   rel="noopener noreferrer"
                                   class="flex gap-2.5 justify-center items-center self-stretch my-auto leading-6 whitespace-nowrap btn w-fit hover:text-hover focus:text-hover">
                                    <span class="self-stretch my-auto text-indigo-950">
                                        <?php echo esc_html($developer_credit_link['title'] ?: 'Matrix Internet'); ?>
                                    </span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </section>
</footer>
