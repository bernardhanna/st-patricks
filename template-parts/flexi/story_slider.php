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
    $video_source_type = get_sub_field('video_source_type') ?: 'youtube_vimeo';
    $video_embed_url = get_sub_field('video_embed_url');
    $local_video_file = get_sub_field('local_video_file');
    $video_lnk = get_sub_field('video_link'); // ACF link array or null
    $legacy_link_url = (is_array($video_lnk) && !empty($video_lnk['url'])) ? $video_lnk['url'] : '';
    $video_url = '';
    $video_link_array = $video_lnk;

    // Prefer explicit source fields, then fallback to legacy video_link.
    if ($has_video) {
      if ($video_source_type === 'local' && is_array($local_video_file) && !empty($local_video_file['url'])) {
        $video_url = $local_video_file['url'];
        $video_link_array = [
          'url' => $video_url,
          'title' => $local_video_file['title'] ?? 'Play video',
          'target' => '_self',
        ];
      } elseif ($video_source_type === 'youtube_vimeo' && !empty($video_embed_url)) {
        $video_url = $video_embed_url;
        $video_link_array = [
          'url' => $video_url,
          'title' => 'Play video',
          'target' => '_self',
        ];
      } elseif ($video_source_type === 'external_link' && !empty($legacy_link_url)) {
        $video_url = $legacy_link_url;
      } elseif (!empty($legacy_link_url)) {
        // Backwards compatibility for existing rows without explicit source type.
        $video_url = $legacy_link_url;
      }
    }

    $video_type = 'none';
    $video_embed_url = '';

    if ($video_url) {
      if (preg_match('/\.(mp4|webm|ogg)(\?.*)?$/i', $video_url)) {
        $video_type = 'local';
      } elseif (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/shorts\/)([A-Za-z0-9_-]{6,})/i', $video_url, $yt_match)) {
        $video_type = 'youtube';
        $video_embed_url = 'https://www.youtube.com/embed/' . rawurlencode($yt_match[1]) . '?rel=0&modestbranding=1';
      } elseif (preg_match('/vimeo\.com\/(?:video\/)?([0-9]+)/i', $video_url, $vm_match)) {
        $video_type = 'vimeo';
        $video_embed_url = 'https://player.vimeo.com/video/' . rawurlencode($vm_match[1]) . '?title=0&byline=0&portrait=0';
      } else {
        $video_type = 'external';
      }
    }

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
      'video'     => $video_link_array, // ['url','title','target']
      'video_url' => $video_url,
      'video_type' => $video_type,
      'video_embed_url' => $video_embed_url,
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
<section id="<?php echo esc_attr($section_id); ?>" data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>" class="flex overflow-hidden relative" aria-label="Story slider"  style="background-image: linear-gradient(135deg, <?php echo esc_attr($bg_from); ?>, <?php echo esc_attr($bg_via); ?>, <?php echo esc_attr($bg_to); ?>);">
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
              <<?php echo tag_escape($heading_tag); ?> class="text-[24px] font-semibold not-italic leading-[28px] tracking-[-0.18px] lg:text-[30px] lg:leading-[36px] lg:tracking-[-0.225px] lg:max-w-[312px]">
                <?php echo esc_html($heading_text); ?>
              </<?php echo tag_escape($heading_tag); ?>>
              <div class="w-10 h-1 rounded-sm" style="background-color: <?php echo esc_attr($accent_bar_color); ?>;"></div>
            <?php endif; ?>

            <?php if (!empty($intro_text)): ?>
              <div class="text-[16px] font-medium not-italic leading-[28px] wp_editor">
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

              if ($count):
              ?>
                <!-- Prev card (desktop only) -->
                <div class="absolute left-0 top-1/2 -translate-x-10 sm:-translate-x-8 md:-translate-x-6 xl:-translate-x-4 -translate-y-1/2" data-prev-card>
                  <div class="relative w-[130px] sm:w-[150px] md:w-[170px] xl:w-[240px] xxl:w-[320px] h-[190px] sm:h-[210px] md:h-[240px] xl:h-[320px] xxl:h-[400px] opacity-60">
                    <div class="relative z-10 p-2 h-full rounded-[10px] bg-[#F6B27A] shadow-sm">
                      <div class="relative overflow-hidden w-full h-full rounded-[8px] bg-white border-x-[8px] border-b-[8px] border-t-[20px] border-[#F6B27A]">
                        <img src="<?php echo esc_url($slides[$prev]['img']['url']); ?>" alt="<?php echo esc_attr($slides[$prev]['img']['alt']); ?>" title="<?php echo esc_attr($slides[$prev]['img']['title']); ?>" class="object-cover absolute inset-0 w-full h-full" loading="lazy" decoding="async" />
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Active card -->
                <div class="relative z-10" data-active-card>
                  <div class="relative w-[340px] sm:w-[400px] md:w-[500px] lg:w-[480px] xl:w-[560px] xxl:w-[640px] h-[380px] md:h-[420px] lg:h-[460px]">
                    <div class="relative z-10 p-3 w-full h-full rounded-[10px] bg-[#F6B27A] shadow-md">
                      <div class="relative overflow-hidden w-full h-full rounded-[8px] bg-white border-x-[10px] border-b-[10px] border-t-[30px] border-[#F6B27A]">
                    <?php
                      $active_slide = $slides[$active];
                      $has_video = !empty($active_slide['has_video']);
                      $v = $active_slide['video'];
                      $a_href  = $has_video && !empty($v['url']) ? esc_url($v['url']) : '';
                      $a_target= $has_video && !empty($v['target']) ? esc_attr($v['target']) : '_self';
                      $a_title = $has_video && !empty($v['title'])  ? esc_attr($v['title'])  : 'Play video';
                      $video_type = $active_slide['video_type'] ?? 'none';
                      $has_inline_video = in_array($video_type, ['local', 'youtube', 'vimeo'], true);
                    ?>
                    <a href="<?php echo $a_href ?: '#'; ?>" target="<?php echo $a_target; ?>" class="absolute inset-0 z-20 hidden" aria-label="<?php echo $a_title; ?>" data-active-link></a>
                    <img src="<?php echo esc_url($active_slide['img']['url']); ?>" alt="<?php echo esc_attr($active_slide['img']['alt']); ?>" title="<?php echo esc_attr($active_slide['img']['title']); ?>" class="object-cover absolute inset-0 w-full h-full" loading="eager" data-active-image />
                    <video
                      class="hidden object-cover absolute inset-0 z-10 w-full h-full"
                      playsinline
                      controls
                      preload="metadata"
                      data-active-video
                      poster="<?php echo esc_url($active_slide['img']['url']); ?>"
                    ></video>
                    <iframe
                      class="hidden absolute inset-0 z-10 w-full h-full"
                      data-active-iframe
                      title="Story video"
                      allow="autoplay; fullscreen; picture-in-picture"
                      allowfullscreen
                    ></iframe>
                    <?php if ($has_video): ?>
                      <button type="button" class="<?php echo $has_inline_video ? 'group flex' : 'hidden'; ?> absolute inset-0 z-30 justify-center items-center border-0 bg-transparent hover:bg-transparent active:bg-transparent focus:bg-transparent" aria-label="Play video" data-play-video>
                        <div class="w-14 h-14 rounded-full border transition-colors duration-200 group-hover:bg-[#F2F5F7]" style="border-color: <?php echo esc_attr($nav_border_color); ?>; background-color: #ffffff;">
                          <div class="flex justify-center items-center w-full h-full">
                            <svg width="16" height="18" viewBox="0 0 16 18" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                              <path d="M2 3.66333C2 2.081 3.75049 1.12532 5.08152 1.98098L13.383 7.31765C14.6076 8.10492 14.6076 9.89509 13.383 10.6824L5.08152 16.019C3.75049 16.8747 2 15.919 2 14.3367V3.66333Z" fill="#001F33"/>
                            </svg>
                          </div>
                        </div>
                      </button>
                    <?php endif; ?>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Next card (desktop only) -->
                <div class="absolute right-0 top-1/2 translate-x-10 sm:translate-x-8 md:translate-x-6 xl:translate-x-4 -translate-y-1/2" data-next-card>
                  <div class="relative w-[130px] sm:w-[150px] md:w-[170px] xl:w-[240px] xxl:w-[320px] h-[190px] sm:h-[210px] md:h-[240px] xl:h-[320px] xxl:h-[400px] opacity-60">
                    <div class="relative z-10 p-2 h-full rounded-[10px] bg-[#F6B27A] shadow-sm">
                      <div class="relative overflow-hidden w-full h-full rounded-[8px] bg-white border-x-[8px] border-b-[8px] border-t-[20px] border-[#F6B27A]">
                        <img src="<?php echo esc_url($slides[$next]['img']['url']); ?>" alt="<?php echo esc_attr($slides[$next]['img']['alt']); ?>" title="<?php echo esc_attr($slides[$next]['img']['title']); ?>" class="object-cover absolute inset-0 w-full h-full" loading="lazy" decoding="async" />
                      </div>
                    </div>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Navigation -->
          <?php if ($count > 1): ?>
          <div class="flex gap-6 items-center" data-controls>
            <button type="button" class="group w-8 h-8 rounded-full border border-[#7ED0E0] bg-white flex items-center justify-center transition-colors hover:border-[#7ED0E0] hover:bg-[#001F33] active:border-[#7ED0E0] active:bg-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#7ED0E0] focus:border-[#7ED0E0] focus:bg-[#001F33]" aria-label="Previous story" data-prev>
              <svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none" aria-hidden="true">
                <path d="M6.08301 0.750081L0.749674 6.08342L6.08301 11.4167" class="stroke-[#020617] transition-colors group-hover:stroke-white group-active:stroke-white group-focus:stroke-white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>

            <div class="flex gap-4 items-center" data-dots>
              <?php for ($i=0; $i<$count; $i++): ?>
                <button type="button" class="w-3 h-3 rounded-full transition-colors duration-200" aria-label="Go to story <?php echo esc_attr($i+1); ?>" data-dot="<?php echo esc_attr($i); ?>"></button>
              <?php endfor; ?>
            </div>

            <button type="button" class="group w-8 h-8 rounded-full border border-[#7ED0E0] bg-white flex items-center justify-center transition-colors hover:border-[#7ED0E0] hover:bg-[#001F33] active:border-[#7ED0E0] active:bg-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#7ED0E0] focus:border-[#7ED0E0] focus:bg-[#001F33]" aria-label="Next story" data-next>
              <svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none" aria-hidden="true">
                <path d="M0.916992 0.750081L6.25033 6.08342L0.916992 11.4167" class="stroke-[#020617] transition-colors group-hover:stroke-white group-active:stroke-white group-focus:stroke-white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
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
  var activeCardImg = root.querySelector('[data-active-image]');
  var activeCardVideo = root.querySelector('[data-active-video]');
  var activeCardIframe = root.querySelector('[data-active-iframe]');
  var activeLink = root.querySelector('[data-active-link]');
  var playBtn = root.querySelector('[data-play-video]');
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
      dots[i].style.backgroundColor = active ? '#0f172a' : '#7ED0E0';
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

    if (activeCardVideo) {
      activeCardVideo.pause();
      activeCardVideo.removeAttribute('src');
      activeCardVideo.load();
      activeCardVideo.classList.add('hidden');
    }
    if (activeCardIframe) {
      activeCardIframe.setAttribute('src', '');
      activeCardIframe.setAttribute('data-embed-src', '');
      activeCardIframe.classList.add('hidden');
    }
    if (activeCardImg) {
      activeCardImg.classList.remove('hidden');
    }

    var hasVideo = slides[idx].has_video && slides[idx].video && slides[idx].video.url;
    var videoType = hasVideo ? (slides[idx].video_type || 'external') : 'none';
    var hasInlineVideo = hasVideo && (videoType === 'local' || videoType === 'youtube' || videoType === 'vimeo');

    if (activeCardVideo && videoType === 'local' && slides[idx].video_url) {
      activeCardVideo.src = slides[idx].video_url;
      activeCardVideo.poster = slides[idx].img.url || '';
      activeCardVideo.load();
    }
    if (activeCardIframe && (videoType === 'youtube' || videoType === 'vimeo') && slides[idx].video_embed_url) {
      activeCardIframe.setAttribute('data-embed-src', slides[idx].video_embed_url);
    }

    if (activeLink) {
      if (hasVideo && !hasInlineVideo) {
        activeLink.href = slides[idx].video.url;
        activeLink.target = slides[idx].video.target || '_self';
        activeLink.setAttribute('aria-label', slides[idx].video.title || 'Play video');
        activeLink.classList.remove('hidden');
      } else {
        activeLink.classList.add('hidden');
      }
    }

    if (playBtn) {
      playBtn.style.display = hasInlineVideo ? 'flex' : 'none';
    }

    setDotStyles();
  }

  function goTo(i) {
    idx = (i + count) % count;
    updateCards();
  }

  if (prevBtn) prevBtn.addEventListener('click', function(){ goTo(idx - 1); });
  if (nextBtn) nextBtn.addEventListener('click', function(){ goTo(idx + 1); });
  if (playBtn && activeCardImg) {
    playBtn.addEventListener('click', function(e){
      e.preventDefault();
      var slide = slides[idx];
      if (!slide) return;

      if (slide.video_type === 'local' && activeCardVideo && activeCardVideo.getAttribute('src')) {
        playBtn.style.display = 'none';
        activeCardImg.classList.add('hidden');
        activeCardVideo.classList.remove('hidden');
        activeCardVideo.play();
        return;
      }

      if ((slide.video_type === 'youtube' || slide.video_type === 'vimeo') && activeCardIframe && slide.video_embed_url) {
        playBtn.style.display = 'none';
        activeCardImg.classList.add('hidden');
        var joiner = slide.video_embed_url.indexOf('?') === -1 ? '?' : '&';
        activeCardIframe.setAttribute('src', slide.video_embed_url + joiner + 'autoplay=1');
        activeCardIframe.classList.remove('hidden');
      }
    });
  }
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
