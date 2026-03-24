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

// Mobile footer logo (fallback to desktop footer logo)
$mobile_footer_logo = get_field('mobile_footer_logo', 'option');
if (!$mobile_footer_logo) {
    $mobile_footer_logo = $footer_logo;
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

// Mobile accordion chevron icon (down)
$chev_svg = '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="6" viewBox="0 0 10 6" fill="none" aria-hidden="true"><path d="M1 1L5 5L9 1" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';

// Social icons (mobile)
$mob_icons = [
    'twitter'  => '<svg width="32" height="32" viewBox="0 0 32 32" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M20.1344 9.5H22.3406L17.5219 15.0062L23.1906 22.5H18.7531L15.275 17.9563L11.3 22.5H9.09063L14.2438 16.6094L8.80938 9.5H13.3594L16.5 13.6531L20.1344 9.5ZM19.3594 21.1812H20.5813L12.6938 10.75H11.3813L19.3594 21.1812Z" fill="#024B79"/></svg>',
    'facebook' => '<svg width="32" height="32" viewBox="0 0 32 32" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M19.723 16.9995L20.1675 14.104H17.389V12.225C17.389 11.433 17.777 10.6605 19.0215 10.6605H20.2845V8.1955C20.2845 8.1955 19.1385 8 18.0425 8C15.7545 8 14.259 9.387 14.259 11.8975V14.1045H11.7155V17H14.259V24H17.389V17L19.723 16.9995Z" fill="#024B79"/></svg>',
    'tiktok'   => '<svg width="32" height="32" viewBox="0 0 32 32" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M16.3513 8.01399C17.2238 8.00049 18.0913 8.00849 18.9578 8.00049C19.0103 9.02099 19.3773 10.0605 20.1243 10.782C20.8698 11.5215 21.9243 11.86 22.9503 11.9745V14.659C21.9888 14.6275 21.0228 14.4275 20.1503 14.0135C19.7703 13.8415 19.4163 13.62 19.0698 13.3935C19.0653 15.3415 19.0778 17.287 19.0573 19.227C19.0053 20.159 18.6978 21.0865 18.1558 21.8545C17.2838 23.133 15.7703 23.9665 14.2158 23.9925C13.2623 24.047 12.3098 23.787 11.4973 23.308C10.1508 22.514 9.20327 21.0605 9.06527 19.5005C9.04769 19.1701 9.04502 18.8391 9.05727 18.5085C9.17727 17.24 9.80477 16.0265 10.7788 15.201C11.8828 14.2395 13.4293 13.7815 14.8773 14.0525C14.8908 15.04 14.8513 16.0265 14.8513 17.014C14.1898 16.8 13.4168 16.86 12.8388 17.2615C12.4159 17.5401 12.0964 17.95 11.9293 18.428C11.7913 18.766 11.8308 19.1415 11.8388 19.5005C11.9973 20.5945 13.0493 21.514 14.1723 21.4145C14.9168 21.4065 15.6303 20.9745 16.0183 20.342C16.1438 20.1205 16.2843 19.894 16.2918 19.6335C16.3573 18.441 16.3313 17.2535 16.3393 16.061C16.3448 13.3735 16.3313 10.6935 16.3518 8.01449Z" fill="#024B79"/></svg>',
    'instagram'=> '<svg width="32" height="32" viewBox="0 0 32 32" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><path d="M16 8.00098C13.8255 8.00098 13.5545 8.01148 12.7035 8.04748C11.849 8.08948 11.271 8.22198 10.761 8.41998C10.2273 8.62153 9.74387 8.93702 9.34451 9.34448C8.93642 9.74332 8.62084 10.2269 8.42001 10.761C8.22201 11.271 8.08951 11.849 8.04751 12.7035C8.00901 13.555 8.00101 13.8255 8.00101 16C8.00101 18.1745 8.01151 18.4455 8.04751 19.2965C8.08951 20.1485 8.22201 20.729 8.42001 21.239C8.62156 21.7727 8.93705 22.2561 9.34451 22.6555C9.74335 23.0636 10.2269 23.3791 10.761 23.58C11.271 23.7755 11.8515 23.9105 12.7035 23.9525C13.555 23.991 13.8255 23.999 16 23.999C18.1745 23.999 18.4455 23.9885 19.2965 23.9525C20.1485 23.9105 20.729 23.775 21.239 23.58C21.7728 23.3784 22.2561 23.0629 22.6555 22.6555C23.0649 22.2577 23.3807 21.7738 23.58 21.239C23.7755 20.729 23.9105 20.1485 23.9525 19.2965C23.991 18.445 23.999 18.1745 23.999 16C23.999 13.8255 23.9885 13.5545 23.9525 12.7035C23.9105 11.8515 23.775 11.268 23.58 10.761C23.3785 10.2272 23.063 9.74384 22.6555 9.34448C22.2577 8.93511 21.7739 8.61933 21.239 8.41998C20.729 8.22198 20.1485 8.08948 19.2965 8.04748C18.445 8.00898 18.1745 8.00098 16 8.00098ZM16 9.44098C18.1355 9.44098 18.3905 9.45148 19.2345 9.48748C20.013 9.52398 20.437 9.65398 20.7185 9.76398C21.0664 9.8926 21.3814 10.097 21.6405 10.3625C21.906 10.62 22.1098 10.9344 22.2365 11.282C22.3465 11.5635 22.4765 11.9875 22.513 12.766C22.549 13.61 22.5595 13.8655 22.5595 16.0005C22.5595 18.1355 22.549 18.391 22.51 19.235C22.468 20.0135 22.338 20.4375 22.2285 20.719C22.077 21.0945 21.908 21.357 21.629 21.641C21.3691 21.9049 21.0543 22.1083 20.707 22.237C20.429 22.347 19.999 22.477 19.2175 22.5135C18.369 22.5495 18.119 22.56 15.978 22.56C13.837 22.56 13.5875 22.5495 12.738 22.5105C11.9595 22.4685 11.53 22.3385 11.2485 22.229C10.8685 22.0775 10.608 21.9085 10.329 21.6295C10.0475 21.348 9.86851 21.08 9.73051 20.7075C9.61851 20.4295 9.49051 19.9995 9.44901 19.218C9.42051 18.3795 9.40701 18.1195 9.40701 15.9885C9.40701 13.8585 9.42051 13.598 9.44901 12.749C9.49051 11.9675 9.61851 11.5385 9.73051 11.2595C9.86851 10.879 10.048 10.619 10.329 10.3375C10.6075 10.059 10.8685 9.87898 11.2485 9.73798C11.53 9.62848 11.949 9.49848 12.7305 9.45948C13.579 9.42898 13.829 9.41798 15.967 9.41798L16 9.44098Z" fill="#024B79"/></svg>',
];

// ====== MOBILE FOOTER (lg and below) ======
?>
<footer class="px-4 lg:hidden bg-[#F1F8F9] pt-section" role="contentinfo" aria-label="Site footer (mobile)">
  <div class="pt-12 mx-auto max-w-7xl">
    <?php if (!empty($mobile_footer_logo['url'])): ?>
      <img src="<?php echo esc_url($mobile_footer_logo['url']); ?>"
           alt="<?php echo esc_attr($mobile_footer_logo['alt'] ?? get_bloginfo('name')); ?>"
           title="<?php echo esc_attr($mobile_footer_logo['title'] ?? get_bloginfo('name')); ?>"
           class="w-[200px] h-[52px] mb-8" />
    <?php endif; ?>

    <?php
    // ✅ Fixed: use a closure (anonymous function) so we can use ($chev_svg)
    $mobile_footer_accordion = function($id_base, $heading, $links) use ($chev_svg) {
        $btn_id   = $id_base . '-btn';
        $panel_id = $id_base . '-panel'; ?>
        <div class="py-4 mb-4 border-b border-[#C6ECF4]">
          <button type="button"
                  id="<?php echo esc_attr($btn_id); ?>"
                  class="flex justify-between items-center w-full"
                  aria-expanded="false"
                  aria-controls="<?php echo esc_attr($panel_id); ?>">
            <h3 class="font-primary text-[18px] not-italic font-semibold leading-[22.75px] tracking-[-0.09px] text-[#1E244B]"><?php echo esc_html($heading); ?></h3>
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
      <h3 class="mb-2 font-primary text-[18px] not-italic font-semibold leading-[22.75px] tracking-[-0.09px] text-[#1E244B]"><?php echo esc_html($contact_heading); ?></h3>
      <p class="mb-6 text-sm font-medium text-dark-bg"><?php echo esc_html($locations_heading); ?></p>

      <div class="flex flex-col gap-3 mb-3">
        <?php if (!empty($contact_phone_link['url'])): ?>
        <div class="flex gap-3 items-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M21.9999 16.9201V19.9201C22.0011 20.1986 21.944 20.4743 21.8324 20.7294C21.7209 20.9846 21.5572 21.2137 21.352 21.402C21.1468 21.5902 20.9045 21.7336 20.6407 21.8228C20.3769 21.912 20.0973 21.9452 19.8199 21.9201C16.7428 21.5857 13.7869 20.5342 11.1899 18.8501C8.77376 17.3148 6.72527 15.2663 5.18993 12.8501C3.49991 10.2413 2.44818 7.27109 2.11993 4.1801C2.09494 3.90356 2.12781 3.62486 2.21643 3.36172C2.30506 3.09859 2.4475 2.85679 2.6347 2.65172C2.82189 2.44665 3.04974 2.28281 3.30372 2.17062C3.55771 2.05843 3.83227 2.00036 4.10993 2.0001H7.10993C7.59524 1.99532 8.06572 2.16718 8.43369 2.48363C8.80166 2.80008 9.04201 3.23954 9.10993 3.7201C9.23656 4.68016 9.47138 5.62282 9.80993 6.5301C9.94448 6.88802 9.9736 7.27701 9.89384 7.65098C9.81408 8.02494 9.6288 8.36821 9.35993 8.6401L8.08993 9.9101C9.51349 12.4136 11.5864 14.4865 14.0899 15.9101L15.3599 14.6401C15.6318 14.3712 15.9751 14.1859 16.3491 14.1062C16.723 14.0264 17.112 14.0556 17.4699 14.1901C18.3772 14.5286 19.3199 14.7635 20.2799 14.8901C20.7657 14.9586 21.2093 15.2033 21.5265 15.5776C21.8436 15.9519 22.0121 16.4297 21.9999 16.9201Z" stroke="#024B79" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M14.0498 2C16.0881 2.21477 17.992 3.1188 19.4467 4.56258C20.9014 6.00636 21.8197 7.90341 22.0498 9.94" stroke="#024B79" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M14.0498 6C15.0333 6.19394 15.9358 6.67903 16.6402 7.39231C17.3446 8.10559 17.8183 9.01413 17.9998 10" stroke="#024B79" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <a href="<?php echo esc_url($contact_phone_link['url']); ?>" target="<?php echo esc_attr($contact_phone_link['target'] ?: '_self'); ?>" class="text-sm text-dark-bg hover:underline">
            <?php echo esc_html($contact_phone_link['title'] ?: $contact_phone_link['url']); ?>
          </a>
        </div>
        <?php endif; ?>

        <?php if (!empty($contact_email_link['url'])): ?>
        <div class="flex gap-3 items-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M20 4H4C2.89543 4 2 4.89543 2 6V18C2 19.1046 2.89543 20 4 20H20C21.1046 20 22 19.1046 22 18V6C22 4.89543 21.1046 4 20 4Z" stroke="#024B79" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M22 7L13.03 12.7C12.7213 12.8934 12.3643 12.996 12 12.996C11.6357 12.996 11.2787 12.8934 10.97 12.7L2 7" stroke="#024B79" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <a href="<?php echo esc_url($contact_email_link['url']); ?>" target="<?php echo esc_attr($contact_email_link['target'] ?: '_self'); ?>" class="text-sm text-dark-bg hover:underline">
            <?php echo esc_html($contact_email_link['title'] ?: $contact_email_link['url']); ?>
          </a>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="mb-8 lg:mb-0">
      <h3 class="mb-4 font-primary text-[18px] not-italic font-semibold leading-[22.75px] tracking-[-0.09px] text-[#1E244B]"><?php echo esc_html($social_heading); ?></h3>
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
                        <h2 id="footer-about-heading" class="font-primary text-[20px] not-italic font-semibold leading-[28px] tracking-[-0.1px] text-[#1E244B]">
                            <?php echo esc_html($about_heading); ?>
                        </h2>

                        <?php if (!empty($about_links)) : ?>
                            <nav class="flex flex-col gap-2.5 justify-center items-start mt-5" aria-label="About us links">
                                <?php foreach ($about_links as $row) :
                                    $link = matrix_resolve_link($row);
                                    if (empty($link['url'])) continue; ?>
                                    <a href="<?php echo esc_url($link['url']); ?>"
                                       target="<?php echo esc_attr($link['target'] ?: '_self'); ?>"
                                       class="inline-flex justify-start items-center gap-2.5 whitespace-nowrap w-fit font-primary text-[14px] not-italic font-normal leading-[24px] text-[#1E244B] transition-colors duration-200 hover:text-[#024B79] hover:underline hover:underline-offset-2 focus-visible:text-[#024B79] focus-visible:underline focus-visible:underline-offset-2">
                                        <span class="self-stretch my-auto text-current">
                                            <?php echo esc_html($link['title'] ?: $link['url']); ?>
                                        </span>
                                    </a>
                                <?php endforeach; ?>
                            </nav>
                        <?php endif; ?>
                    </section>

                    <!-- Quick Links -->
                    <section class="text-indigo-950" aria-labelledby="footer-quick-links-heading">
                        <h2 id="footer-quick-links-heading" class="font-primary text-[20px] not-italic font-semibold leading-[28px] tracking-[-0.1px] text-[#1E244B]">
                            <?php echo esc_html($quick_heading); ?>
                        </h2>

                        <?php if (!empty($quick_links)) : ?>
                            <nav class="flex flex-col gap-2.5 justify-center items-start mt-5" aria-label="Quick links">
                                <?php foreach ($quick_links as $row) :
                                    $link = matrix_resolve_link($row);
                                    if (empty($link['url'])) continue; ?>
                                    <a href="<?php echo esc_url($link['url']); ?>"
                                       target="<?php echo esc_attr($link['target'] ?: '_self'); ?>"
                                       class="inline-flex justify-start items-center gap-2.5 w-fit font-primary text-[14px] not-italic font-normal leading-[24px] text-[#1E244B] transition-colors duration-200 hover:text-[#024B79] hover:underline hover:underline-offset-2 focus-visible:text-[#024B79] focus-visible:underline focus-visible:underline-offset-2">
                                        <span class="self-stretch my-auto text-current">
                                            <?php echo esc_html($link['title'] ?: $link['url']); ?>
                                        </span>
                                    </a>
                                <?php endforeach; ?>
                            </nav>
                        <?php endif; ?>
                    </section>

                    <!-- Media -->
                    <section class="text-indigo-950" aria-labelledby="footer-media-heading">
                        <h2 id="footer-media-heading" class="font-primary text-[20px] not-italic font-semibold leading-[28px] tracking-[-0.1px] text-[#1E244B]">
                            <?php echo esc_html($media_heading); ?>
                        </h2>

                        <?php if (!empty($media_links)) : ?>
                            <nav class="flex flex-col gap-2.5 justify-center items-start mt-5" aria-label="Media links">
                                <?php foreach ($media_links as $row) :
                                    $link = matrix_resolve_link($row);
                                    if (empty($link['url'])) continue; ?>
                                    <a href="<?php echo esc_url($link['url']); ?>"
                                       target="<?php echo esc_attr($link['target'] ?: '_self'); ?>"
                                       class="inline-flex justify-start items-center gap-2.5 whitespace-nowrap w-fit font-primary text-[14px] not-italic font-normal leading-[24px] text-[#1E244B] transition-colors duration-200 hover:text-[#024B79] hover:underline hover:underline-offset-2 focus-visible:text-[#024B79] focus-visible:underline focus-visible:underline-offset-2">
                                        <span class="self-stretch my-auto text-current">
                                            <?php echo esc_html($link['title'] ?: $link['url']); ?>
                                        </span>
                                    </a>
                                <?php endforeach; ?>
                            </nav>
                        <?php endif; ?>
                    </section>

                    <!-- Careers -->
                    <section class="text-indigo-950" aria-labelledby="footer-careers-heading">
                        <h2 id="footer-careers-heading" class="font-primary text-[20px] not-italic font-semibold leading-[28px] tracking-[-0.1px] text-[#1E244B]">
                            <?php echo esc_html($careers_heading); ?>
                        </h2>

                        <?php if (!empty($careers_links)) : ?>
                            <nav class="flex flex-col gap-2.5 items-start mt-4" aria-label="Career links">
                                <?php foreach ($careers_links as $row) :
                                    $link = matrix_resolve_link($row);
                                    if (empty($link['url'])) continue; ?>
                                    <a href="<?php echo esc_url($link['url']); ?>"
                                       target="<?php echo esc_attr($link['target'] ?: '_self'); ?>"
                                       class="inline-flex justify-start items-center gap-2.5 whitespace-nowrap w-fit font-primary text-[14px] not-italic font-normal leading-[24px] text-[#1E244B] transition-colors duration-200 hover:text-[#024B79] hover:underline hover:underline-offset-2 focus-visible:text-[#024B79] focus-visible:underline focus-visible:underline-offset-2">
                                        <span class="self-stretch my-auto text-current">
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
                            <h2 id="footer-contact-heading" class="font-primary text-[20px] not-italic font-semibold leading-[28px] tracking-[-0.1px] text-[#1E244B]">
                                <?php echo esc_html($contact_heading); ?>
                            </h2>

                            <address class="flex flex-col self-start mt-6 text-sm not-italic leading-6">
                                <h3 class="font-primary text-[14px] not-italic font-medium leading-[24px] text-[#1E244B]">
                                    <?php echo esc_html($locations_heading); ?>
                                </h3>

                                <?php if (!empty($contact_phone_link['url'])) : ?>
                                    <div class="flex gap-3 items-center self-start mt-4">
                                        <div class="flex self-stretch my-auto w-6 h-6 shrink-0" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M22.0004 16.9201V19.9201C22.0016 20.1986 21.9445 20.4743 21.8329 20.7294C21.7214 20.9846 21.5577 21.2137 21.3525 21.402C21.1473 21.5902 20.905 21.7336 20.6412 21.8228C20.3773 21.912 20.0978 21.9452 19.8204 21.9201C16.7433 21.5857 13.7874 20.5342 11.1904 18.8501C8.77425 17.3148 6.72576 15.2663 5.19042 12.8501C3.5004 10.2413 2.44866 7.27109 2.12042 4.1801C2.09543 3.90356 2.1283 3.62486 2.21692 3.36172C2.30555 3.09859 2.44799 2.85679 2.63519 2.65172C2.82238 2.44665 3.05023 2.28281 3.30421 2.17062C3.5582 2.05843 3.83276 2.00036 4.11042 2.0001H7.11042C7.59573 1.99532 8.06621 2.16718 8.43418 2.48363C8.80215 2.80008 9.0425 3.23954 9.11042 3.7201C9.23704 4.68016 9.47187 5.62282 9.81042 6.5301C9.94497 6.88802 9.97408 7.27701 9.89433 7.65098C9.81457 8.02494 9.62928 8.36821 9.36042 8.6401L8.09042 9.9101C9.51398 12.4136 11.5869 14.4865 14.0904 15.9101L15.3604 14.6401C15.6323 14.3712 15.9756 14.1859 16.3495 14.1062C16.7235 14.0264 17.1125 14.0556 17.4704 14.1901C18.3777 14.5286 19.3204 14.7635 20.2804 14.8901C20.7662 14.9586 21.2098 15.2033 21.527 15.5776C21.8441 15.9519 22.0126 16.4297 22.0004 16.9201Z" stroke="#024B79" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M14.0508 2C16.089 2.21477 17.993 3.1188 19.4476 4.56258C20.9023 6.00636 21.8207 7.90341 22.0508 9.94" stroke="#024B79" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M14.0508 6C15.0343 6.19394 15.9368 6.67903 16.6412 7.39231C17.3455 8.10559 17.8192 9.01413 18.0008 10" stroke="#024B79" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                        </div>
                                        <a href="<?php echo esc_url($contact_phone_link['url']); ?>"
                                           target="<?php echo esc_attr($contact_phone_link['target'] ?: '_self'); ?>"
                                           class="inline-flex justify-start items-center gap-2.5 self-stretch my-auto w-fit font-primary text-[14px] not-italic font-normal leading-[24px] text-[#1E244B] transition-colors duration-200 hover:text-[#024B79] hover:underline hover:underline-offset-2 focus-visible:text-[#024B79] focus-visible:underline focus-visible:underline-offset-2">
                                            <span class="self-stretch my-auto text-current">
                                                <?php echo esc_html($contact_phone_link['title'] ?: $contact_phone_link['url']); ?>
                                            </span>
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($contact_email_link['url'])) : ?>
                                    <div class="flex gap-3 items-center mt-4 whitespace-nowrap">
                                        <div class="flex self-stretch my-auto w-6 h-6 shrink-0" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M20 4H4C2.89543 4 2 4.89543 2 6V18C2 19.1046 2.89543 20 4 20H20C21.1046 20 22 19.1046 22 18V6C22 4.89543 21.1046 4 20 4Z" stroke="#024B79" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M22 7L13.03 12.7C12.7213 12.8934 12.3643 12.996 12 12.996C11.6357 12.996 11.2787 12.8934 10.97 12.7L2 7" stroke="#024B79" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        </div>
                                        <a href="<?php echo esc_url($contact_email_link['url']); ?>"
                                           target="<?php echo esc_attr($contact_email_link['target'] ?: '_self'); ?>"
                                           class="inline-flex justify-start items-center gap-2.5 self-stretch my-auto w-fit font-primary text-[14px] not-italic font-normal leading-[24px] text-[#1E244B] transition-colors duration-200 hover:text-[#024B79] hover:underline hover:underline-offset-2 focus-visible:text-[#024B79] focus-visible:underline focus-visible:underline-offset-2">
                                            <span class="self-stretch my-auto text-current">
                                                <?php echo esc_html($contact_email_link['title'] ?: $contact_email_link['url']); ?>
                                            </span>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </address>
                        </div>

                        <!-- Social Media -->
                        <section class="mt-7 w-full max-w-[183px]" aria-labelledby="footer-social-heading">
                            <h2 id="footer-social-heading" class="font-primary text-[20px] not-italic font-semibold leading-[28px] tracking-[-0.1px] text-[#1E244B]">
                                <?php echo esc_html($social_heading); ?>
                            </h2>

                            <?php if (!empty($social_links)) : ?>
                                <nav class="flex gap-3 items-start mt-4" aria-label="Social media links">
                                    <?php
                                    $icons = [
                                        'twitter'  => '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="13" viewBox="0 0 15 13" fill="none">
                                        <path d="M11.325 0H13.5312L8.7125 5.50625L14.3813 13H9.94375L6.46562 8.45625L2.49062 13H0.28125L5.43437 7.10938L0 0H4.55L7.69062 4.15312L11.325 0ZM10.55 11.6812H11.7719L3.88437 1.25H2.57187L10.55 11.6812Z" fill="#024B79"/>
                                        </svg>',
                                        'facebook' => '<svg xmlns="http://www.w3.org/2000/svg" width="9" height="16" viewBox="0 0 9 16" fill="none">
                                        <path d="M8.0075 8.9995L8.452 6.104H5.6735V4.225C5.6735 3.433 6.0615 2.6605 7.306 2.6605H8.569V0.1955C8.569 0.1955 7.423 0 6.327 0C4.039 0 2.5435 1.387 2.5435 3.8975V6.1045H0V9H2.5435V16H5.6735V9L8.0075 8.9995Z" fill="#024B79"/>
                                        </svg>',
                                        'tiktok'   => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="16" viewBox="0 0 14 16" fill="none">
                                            <path d="M7.30155 0.0135C8.17405 0 9.04155 0.008 9.90805 0C9.96055 1.0205 10.3275 2.06 11.0745 2.7815C11.82 3.521 12.8745 3.8595 13.9005 3.974V6.6585C12.939 6.627 11.973 6.427 11.1005 6.013C10.7205 5.841 10.3665 5.6195 10.02 5.393C10.0155 7.341 10.028 9.2865 10.0075 11.2265C9.95555 12.1585 9.64805 13.086 9.10605 13.854C8.23405 15.1325 6.72055 15.966 5.16605 15.992C4.21255 16.0465 3.26005 15.7865 2.44755 15.3075C1.10105 14.5135 0.153546 13.06 0.0155463 11.5C-0.00203715 11.1696 -0.00470655 10.8386 0.00754628 10.508C0.127546 9.2395 0.755046 8.026 1.72905 7.2005C2.83305 6.239 4.37955 5.781 5.82755 6.052C5.84105 7.0395 5.80155 8.026 5.80155 9.0135C5.14005 8.7995 4.36705 8.8595 3.78905 9.261C3.36622 9.53964 3.04666 9.94949 2.87955 10.4275C2.74155 10.7655 2.78105 11.141 2.78905 11.5C2.94755 12.594 3.99955 13.5135 5.12255 13.414C5.86705 13.406 6.58055 12.974 6.96855 12.3415C7.09405 12.12 7.23455 11.8935 7.24205 11.633C7.30755 10.4405 7.28155 9.253 7.28955 8.0605C7.29505 5.373 7.28155 2.693 7.30205 0.014L7.30155 0.0135Z" fill="#024B79"/>
                                            </svg>',
                                        'instagram'=> '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                            <path d="M7.999 0C5.8245 0 5.5535 0.0105 4.7025 0.0465C3.848 0.0885 3.27 0.221 2.76 0.419C2.22625 0.620553 1.74286 0.936045 1.3435 1.3435C0.935418 1.74235 0.619829 2.22589 0.419 2.76C0.221 3.27 0.0885 3.848 0.0465 4.7025C0.008 5.554 0 5.8245 0 7.999C0 10.1735 0.0105 10.4445 0.0465 11.2955C0.0885 12.1475 0.221 12.728 0.419 13.238C0.620553 13.7717 0.936045 14.2551 1.3435 14.6545C1.74235 15.0626 2.22589 15.3782 2.76 15.579C3.27 15.7745 3.8505 15.9095 4.7025 15.9515C5.554 15.99 5.8245 15.998 7.999 15.998C10.1735 15.998 10.4445 15.9875 11.2955 15.9515C12.1475 15.9095 12.728 15.774 13.238 15.579C13.7717 15.3774 14.2551 15.062 14.6545 14.6545C15.0639 14.2567 15.3796 13.7729 15.579 13.238C15.7745 12.728 15.9095 12.1475 15.9515 11.2955C15.99 10.444 15.998 10.1735 15.998 7.999C15.998 5.8245 15.9875 5.5535 15.9515 4.7025C15.9095 3.8505 15.774 3.267 15.579 2.76C15.3774 2.22625 15.062 1.74286 14.6545 1.3435C14.2567 0.934138 13.7729 0.618351 13.238 0.419C12.728 0.221 12.1475 0.0885 11.2955 0.0465C10.444 0.008 10.1735 0 7.999 0ZM7.999 1.44C10.1345 1.44 10.3895 1.4505 11.2335 1.4865C12.012 1.523 12.436 1.653 12.7175 1.763C13.0654 1.89163 13.3804 2.09607 13.6395 2.3615C13.905 2.61907 14.1088 2.93343 14.2355 3.281C14.3455 3.5625 14.4755 3.9865 14.512 4.765C14.548 5.609 14.5585 5.8645 14.5585 7.9995C14.5585 10.1345 14.548 10.39 14.509 11.234C14.467 12.0125 14.337 12.4365 14.2275 12.718C14.076 13.0935 13.907 13.356 13.628 13.64C13.3681 13.9039 13.0533 14.1074 12.706 14.236C12.428 14.346 11.998 14.476 11.2165 14.5125C10.368 14.5485 10.118 14.559 7.977 14.559C5.836 14.559 5.5865 14.5485 4.737 14.5095C3.9585 14.4675 3.529 14.3375 3.2475 14.228C2.8675 14.0765 2.607 13.9075 2.328 13.6285C2.0465 13.347 1.8675 13.079 1.7295 12.7065C1.6175 12.4285 1.4895 11.9985 1.448 11.217C1.4195 10.3785 1.406 10.1185 1.406 7.9875C1.406 5.8575 1.4195 5.597 1.448 4.748C1.4895 3.9665 1.6175 3.5375 1.7295 3.2585C1.8675 2.878 2.047 2.618 2.328 2.3365C2.6065 2.058 2.8675 1.878 3.2475 1.737C3.529 1.6275 3.948 1.4975 4.7295 1.4585C5.578 1.428 5.828 1.417 7.966 1.417L7.999 1.44ZM7.999 3.8935C7.45967 3.8933 6.92559 3.99939 6.42728 4.20569C5.92897 4.41199 5.47619 4.71446 5.09483 5.09583C4.71346 5.47719 4.41099 5.92997 4.20469 6.42828C3.99839 6.92659 3.8923 7.46067 3.8925 8C3.8923 8.53933 3.99839 9.07341 4.20469 9.57172C4.41099 10.07 4.71346 10.5228 5.09483 10.9042C5.47619 11.2855 5.92897 11.588 6.42728 11.7943C6.92559 12.0006 7.45967 12.1067 7.999 12.1065C8.53833 12.1067 9.07241 12.0006 9.57072 11.7943C10.069 11.588 10.5218 11.2855 10.9032 10.9042C11.2845 10.5228 11.587 10.07 11.7933 9.57172C11.9996 9.07341 12.1057 8.53933 12.1055 8C12.1057 7.46067 11.9996 6.92659 11.7933 6.42828C11.587 5.92997 11.2845 5.47719 10.9032 5.09583C10.5218 4.71446 10.069 4.41199 9.57072 4.20569C9.07241 3.99939 8.53833 3.8933 7.999 3.8935ZM7.999 10.6665C6.525 10.6665 5.3325 9.474 5.3325 8C5.3325 6.526 6.525 5.3335 7.999 5.3335C9.473 5.3335 10.6655 6.526 10.6655 8C10.6655 9.474 9.473 10.6665 7.999 10.6665ZM13.231 3.7295C13.2307 3.98433 13.1293 4.22864 12.949 4.40874C12.7687 4.58884 12.5243 4.69 12.2695 4.69C12.1435 4.69 12.0187 4.66518 11.9023 4.61696C11.7859 4.56874 11.6801 4.49807 11.591 4.40897C11.5019 4.31987 11.4313 4.2141 11.383 4.09768C11.3348 3.98127 11.31 3.8565 11.31 3.7305C11.31 3.6045 11.3348 3.47973 11.383 3.36332C11.4313 3.2469 11.5019 3.14113 11.591 3.05203C11.6801 2.96293 11.7859 2.89226 11.9023 2.84404C12.0187 2.79582 12.1435 2.771 12.2695 2.771C12.798 2.771 13.231 3.201 13.231 3.7295Z" fill="#024B79"/>
                                            </svg>',
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
                                       class="inline-flex justify-start items-center gap-2.5 self-stretch my-auto whitespace-nowrap w-fit font-primary text-[14px] not-italic font-normal leading-[24px] text-[#1E244B] transition-colors duration-200 hover:text-[#024B79] hover:underline hover:underline-offset-2 focus-visible:text-[#024B79] focus-visible:underline focus-visible:underline-offset-2">
                                        <span class="self-stretch my-auto text-current">
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
