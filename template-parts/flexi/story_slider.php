<?php
/**
 * Frontend Output: Story Slider (no React)
 * - ACF Flexi block using get_sub_field()
 * - Random section id
 * - Padding repeater → arbitrary Tailwind classes
 * - 3-card carousel UI: center large, side previews (desktop), dots + arrows
 * - Entire center card is clickable when has_video + video_link provided (ACF Link array)
 */

$section_id = 'story-slider-' . wp_generate_uuid4();

// Content
$show_heading  = get_sub_field('show_heading');
$heading_tag   = get_sub_field('heading_tag') ?: 'h2';
$heading_text  = get_sub_field('heading_text') ?: 'Voices of hope and healing';
$intro_text    = get_sub_field('intro_text');

// Slides
$slides = [];
if (have_rows('slides')) {
  while (have_rows('slides')) { the_row();
    $img_id    = get_sub_field('image');
    $has_video = (bool) get_sub_field('has_video');
    $video_lnk = get_sub_field('video_link'); // ACF link array or null

    $img_url   = $img_id ? wp_get_attachment_image_url($img_id, 'large') : '';
    $img_alt   = $img_id ? get_post_meta($img_id, '_wp_attachment_image_alt', true) : '';
    $img_title = $img_id ? get_the_title($img_id) : '';

    $slides[] = [
      'img'  => [
        'url'   => $img_url ?: '',
        'alt'   => $img_alt ?: 'Story image',
        'title' => $img_title ?: 'Image',
      ],
      'has_video' => $has_video,
      'video'     => $video_lnk, // ['url','title','target']
    ];
  }
}

// Design
$bg_from           = get_sub_field('bg_from') ?: '#F6EDE0';
$bg_via            = get_sub_field('bg_via')  ?: '#F5F0E0';
$bg_to             = get_sub_field('bg_to')   ?: '#F4F5DE';
$bg_image_id       = get_sub_field('bg_image');
$bg_image_url      = $bg_image_id ? wp_get_attachment_image_url($bg_image_id, 'full') : '';
$overlay_opacity   = get_sub_field('overlay_opacity') ?: '0.1';
$accent_bar_color  = get_sub_field('accent_bar_color') ?: '#F97316';
$quote_stroke      = get_sub_field('quote_stroke_color') ?: '#ffffff';
$nav_border_color  = get_sub_field('nav_border_color') ?: '#93C5FD';
$dot_active        = get_sub_field('dot_active_color') ?: '#0A2540';
$dot_inactive      = get_sub_field('dot_inactive_color') ?: '#3B82F6';
$card_radius       = get_sub_field('card_radius') ?: 'rounded-md';

// Layout: padding classes
$padding_classes = [];
if (have_rows('padding_settings')) {
  while (have_rows('padding_settings')) {
    the_row();
    $screen_size    = get_sub_field('screen_size');
    $padding_top    = get_sub_field('padding_top');
    $padding_bottom = get_sub_field('padding_bottom');
    if ($screen_size !== '' && $padding_top !== '' && $padding_bottom !== '') {
      $padding_classes[] = esc_attr("{$screen_size}:pt-[{$padding_top}rem]");
      $padding_classes[] = esc_attr("{$screen_size}:pb-[{$padding_bottom}rem]");
    }
  }
}
$padding_str = implode(' ', $padding_classes);

?>
<section id="<?php echo esc_attr($section_id); ?>" class="flex overflow-hidden relative" aria-label="Story slider"  style="background-image: linear-gradient(135deg, <?php echo esc_attr($bg_from); ?>, <?php echo esc_attr($bg_via); ?>, <?php echo esc_attr($bg_to); ?>);">
  <div class="flex flex-col items-center w-full mx-auto max-w-container pt-5 pb-5 max-lg:px-5 <?php echo esc_attr($padding_str); ?>">

    <div class="flex relative justify-center items-center px-6 py-16 w-full md:py-20 lg:py-24 md:px-12 lg:px-28 xl:px-32"
>

      <?php if (!empty($bg_image_url)): ?>
        <div class="absolute inset-0 pointer-events-none" style="background-image: url('<?php echo esc_url($bg_image_url); ?>'); background-size: cover; background-position: center; opacity: <?php echo esc_attr($overlay_opacity); ?>;"></div>
      <?php endif; ?>

      <div class="flex relative flex-col gap-12 justify-between items-center w-full max-w-7xl lg:flex-row lg:gap-16">

        <!-- Left content -->
        <div class="flex flex-col gap-8 items-start w-full lg:w-80 xl:w-96 lg:flex-shrink-0">
          <div class="flex flex-col gap-8 w-full">
            <?php if ($show_heading): ?>
              <<?php echo tag_escape($heading_tag); ?> class="text-3xl md:text-4xl lg:text-[30px] font-semibold leading-tight tracking-[-0.225px]">
                <?php echo esc_html($heading_text); ?>
              </<?php echo tag_escape($heading_tag); ?>>
              <div class="w-10 h-1 rounded-sm" style="background-color: <?php echo esc_attr($accent_bar_color); ?>;"></div>
            <?php endif; ?>

            <?php if (!empty($intro_text)): ?>
              <div class="text-base font-medium leading-7 wp_editor">
                <?php echo wp_kses_post($intro_text); ?>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Slider (no external lib) -->
        <div class="flex flex-col gap-8 items-center w-full lg:flex-1" data-slider-root>
          <div class="flex relative justify-center items-center w-full">
            <div class="relative w-full max-w-[700px] h-[380px] md:h-[420px] lg:h-[460px] flex items-center justify-center" data-slider-stage>
              <?php
              $count = count($slides);
              $active = 0;
              $prev   = $count ? ($active - 1 + $count) % $count : 0;
              $next   = $count ? ($active + 1) % $count : 0;

              function story_slider_quote_svg($stroke) {
                $s = esc_attr($stroke);
                return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" class="w-6 h-6" xmlns="http://www.w3.org/2000/svg">
                  <path d="M3 21C6 21 10 20 10 13V5.00003C10 3.75003 9.244 2.98303 8 3.00003H4C2.75 3.00003 2 3.75003 2 4.97203V11C2 12.25 2.75 13 4 13C5 13 5 13 5 14V15C5 16 4 17 3 17C2 17 2 17.008 2 18.031V20C2 21 2 21 3 21Z" stroke="'.$s.'" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M15 21C18 21 22 20 22 13V5.00003C22 3.75003 21.243 2.98303 20 3.00003H16C14.75 3.00003 14 3.75003 14 4.97203V11C14 12.25 14.75 13 16 13H16.75C16.75 15.25 17 17 14 17V20C14 21 14 21 15 21Z" stroke="'.$s.'" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>';
              }

              if ($count):
              ?>
                <!-- Prev card (desktop only) -->
                <div class="hidden absolute left-0 top-1/2 -translate-x-4 -translate-y-1/2 lg:block" data-prev-card>
                  <div class="w-[260px] xl:w-[320px] h-[360px] xl:h-[400px] <?php echo esc_attr($card_radius); ?> bg-[#FBE8D8] opacity-50 shadow-sm overflow-hidden relative">
                    <img src="<?php echo esc_url($slides[$prev]['img']['url']); ?>" alt="<?php echo esc_attr($slides[$prev]['img']['alt']); ?>" title="<?php echo esc_attr($slides[$prev]['img']['title']); ?>" class="object-cover absolute inset-0 w-full h-full" loading="lazy" decoding="async" />
                    <div class="absolute right-3 bottom-3">
                      <?php echo story_slider_quote_svg($quote_stroke); ?>
                    </div>
                  </div>
                </div>

                <!-- Active card -->
                <div class="relative z-10" data-active-card>
                  <div class="w-full max-w-[340px] sm:max-w-[400px] md:max-w-[500px] lg:max-w-[540px] xl:w-[640px] h-[380px] md:h-[420px] lg:h-[460px] <?php echo esc_attr($card_radius); ?> bg-[#FADCC6] shadow-md overflow-hidden relative">
                    <?php
                      $active_slide = $slides[$active];
                      $has_video = !empty($active_slide['has_video']);
                      $v = $active_slide['video'];
                      $a_href  = $has_video && !empty($v['url']) ? esc_url($v['url']) : '';
                      $a_target= $has_video && !empty($v['target']) ? esc_attr($v['target']) : '_self';
                      $a_title = $has_video && !empty($v['title'])  ? esc_attr($v['title'])  : 'Play video';
                    ?>
                    <?php if ($a_href): ?>
                      <a href="<?php echo $a_href; ?>" target="<?php echo $a_target; ?>" class="absolute inset-0 z-10" aria-label="<?php echo $a_title; ?>"></a>
                    <?php endif; ?>
                    <img src="<?php echo esc_url($active_slide['img']['url']); ?>" alt="<?php echo esc_attr($active_slide['img']['alt']); ?>" title="<?php echo esc_attr($active_slide['img']['title']); ?>" class="object-cover absolute inset-0 w-full h-full" loading="eager" />
                    <?php if ($has_video): ?>
                      <button type="button" class="flex absolute inset-0 justify-center items-center pointer-events-none" aria-hidden="true" tabindex="-1">
                        <div class="w-14 h-14 <?php echo esc_attr($card_radius); ?> border" style="border-color: <?php echo esc_attr($nav_border_color); ?>; background-color: #ffffff;">
                          <div class="flex justify-center items-center w-full h-full">
                            <svg width="16" height="18" viewBox="0 0 16 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <path d="M2 3.66333C2 2.081 3.75049 1.12532 5.08152 1.98098L13.383 7.31765C14.6076 8.10492 14.6076 9.89509 13.383 10.6824L5.08152 16.019C3.75049 16.8747 2 15.919 2 14.3367V3.66333Z" fill="#001F33"/>
                            </svg>
                          </div>
                        </div>
                      </button>
                    <?php endif; ?>
                    <div class="absolute right-6 bottom-6">
                      <?php echo story_slider_quote_svg($quote_stroke); ?>
                    </div>
                  </div>
                </div>

                <!-- Next card (desktop only) -->
                <div class="hidden absolute right-0 top-1/2 translate-x-4 -translate-y-1/2 lg:block" data-next-card>
                  <div class="w-[260px] xl:w-[320px] h-[360px] xl:h-[400px] <?php echo esc_attr($card_radius); ?> bg-[#FBE8D8] opacity-50 shadow-sm overflow-hidden relative">
                    <img src="<?php echo esc_url($slides[$next]['img']['url']); ?>" alt="<?php echo esc_attr($slides[$next]['img']['alt']); ?>" title="<?php echo esc_attr($slides[$next]['img']['title']); ?>" class="object-cover absolute inset-0 w-full h-full" loading="lazy" decoding="async" />
                    <div class="absolute right-3 bottom-3">
                      <?php echo story_slider_quote_svg($quote_stroke); ?>
                    </div>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Navigation -->
          <?php if ($count > 1): ?>
          <div class="flex gap-6 items-center" data-controls>
            <button type="button" class="w-8 h-8 <?php echo esc_attr($card_radius); ?> border bg-white flex items-center justify-center hover:bg-gray-50 transition-colors" style="border-color: <?php echo esc_attr($nav_border_color); ?>;" aria-label="Previous story" data-prev>
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="M10.6666 2.66683L5.33329 8.00016L10.6666 13.3335" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>

            <div class="flex gap-4 items-center" data-dots>
              <?php for ($i=0; $i<$count; $i++): ?>
                <button type="button" class="w-2.5 h-2.5 rounded-full transition-all" aria-label="Go to story <?php echo esc_attr($i+1); ?>" data-dot="<?php echo esc_attr($i); ?>"></button>
              <?php endfor; ?>
            </div>

            <button type="button" class="w-8 h-8 <?php echo esc_attr($card_radius); ?> border bg-white flex items-center justify-center hover:bg-gray-50 transition-colors" style="border-color: <?php echo esc_attr($nav_border_color); ?>;" aria-label="Next story" data-next>
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="M5.33337 2.66683L10.6667 8.00016L5.33338 13.3335" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
(function(){
  var root = document.getElementById('<?php echo esc_js($section_id); ?>');
  if (!root) return;

  var prevCard = root.querySelector('[data-prev-card] img');
  var nextCard = root.querySelector('[data-next-card] img');
  var activeCardImg = root.querySelector('[data-active-card] img');
  var activeLink = root.querySelector('[data-active-card] a');
  var playBtn = root.querySelector('[data-active-card] button');
  var dotsWrap = root.querySelector('[data-dots]');
  var dots = dotsWrap ? dotsWrap.querySelectorAll('[data-dot]') : [];
  var prevBtn = root.querySelector('[data-prev]');
  var nextBtn = root.querySelector('[data-next]');

  var slides = <?php echo wp_json_encode($slides); ?>;
  var count = slides.length;
  if (!count) return;

  var idx = 0;

  function setDotStyles() {
    if (!dots.length) return;
    for (var i=0;i<dots.length;i++) {
      var active = (i === idx);
      dots[i].style.backgroundColor = active ? '<?php echo esc_js($dot_active); ?>' : '<?php echo esc_js($dot_inactive); ?>';
      dots[i].style.transform = active ? 'scale(1.25)' : 'scale(1)';
    }
  }

  function updateCards() {
    if (!slides.length) return;
    var prev = (idx - 1 + count) % count;
    var next = (idx + 1) % count;

    if (prevCard) {
      prevCard.src = slides[prev].img.url || '';
      prevCard.alt = slides[prev].img.alt || 'Story image';
      prevCard.title = slides[prev].img.title || 'Image';
    }
    if (nextCard) {
      nextCard.src = slides[next].img.url || '';
      nextCard.alt = slides[next].img.alt || 'Story image';
      nextCard.title = slides[next].img.title || 'Image';
    }
    if (activeCardImg) {
      activeCardImg.src = slides[idx].img.url || '';
      activeCardImg.alt = slides[idx].img.alt || 'Story image';
      activeCardImg.title = slides[idx].img.title || 'Image';
    }

    if (activeLink) {
      var hasLink = slides[idx].has_video && slides[idx].video && slides[idx].video.url;
      if (hasLink) {
        activeLink.href = slides[idx].video.url;
        activeLink.target = slides[idx].video.target || '_self';
        activeLink.setAttribute('aria-label', slides[idx].video.title || 'Play video');
        activeLink.style.display = 'block';
      } else {
        activeLink.style.display = 'none';
      }
    }
    if (playBtn) {
      var showPlay = slides[idx].has_video && slides[idx].video && slides[idx].video.url;
      playBtn.style.display = showPlay ? 'flex' : 'none';
    }

    setDotStyles();
  }

  function goTo(i) {
    idx = (i + count) % count;
    updateCards();
  }

  if (prevBtn) prevBtn.addEventListener('click', function(){ goTo(idx - 1); });
  if (nextBtn) nextBtn.addEventListener('click', function(){ goTo(idx + 1); });
  if (dots.length) {
    for (var d=0; d<dots.length; d++) {
      (function(n){
        dots[n].addEventListener('click', function(){ goTo(n); });
      })(d);
    }
  }

  updateCards();
})();
</script>
