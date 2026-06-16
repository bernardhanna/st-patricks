<?php

$section_id = 'video-showcase-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());
$heading = trim((string) get_sub_field('heading'));
$heading_tag = (string) get_sub_field('heading_tag');
$intro = get_sub_field('intro');
$layout_style = matrix_resolve_video_showcase_layout_style(get_sub_field('layout_style'));
$video_surface_size = matrix_resolve_video_showcase_surface_size(get_sub_field('video_surface_size'));
$section_background = (string) get_sub_field('section_background');

$show_heading = $heading !== '';

if (! in_array($heading_tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p'], true)) {
    $heading_tag = 'h2';
}

$slide_rows = [];
if (have_rows('slides')) {
    while (have_rows('slides')) {
        the_row();
        $slide_rows[] = [
            'poster_image' => get_sub_field('poster_image'),
            'video_source_type' => get_sub_field('video_source_type'),
            'video_embed_url' => get_sub_field('video_embed_url'),
            'local_video_file' => get_sub_field('local_video_file'),
            'caption' => get_sub_field('caption'),
            'cta_link' => get_sub_field('cta_link'),
        ];
    }
}

$slides = matrix_normalize_video_showcase_slides($slide_rows);

if ($slides === []) {
    return;
}



$slide_count = count($slides);
$show_slider_controls = $layout_style !== 'feature_single' && $slide_count > 1;
$initial_slide = $slides[0];
$surface_width_class = matrix_get_video_showcase_surface_width_class($layout_style, $video_surface_size);
$surface_height_class = matrix_get_video_showcase_surface_height_class($layout_style, $video_surface_size);
$caption_width_class = matrix_get_video_showcase_caption_width_class($layout_style, $video_surface_size);
$section_background_style = matrix_get_video_showcase_section_background_style(
    $section_background,
    'linear-gradient(135deg, #F6EDE0 0%, #F5F0E0 48%, #F4F5DE 100%)'
);
$show_intro = is_string($intro) && trim(strip_tags($intro)) !== '';
$show_header = $show_heading || $show_intro;
$heading_wrap_width_class = matrix_get_video_showcase_heading_wrap_width_class($layout_style, $video_surface_size);
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
    class="flex overflow-hidden relative"
    style="<?php echo esc_attr($section_background_style); ?>"
>
    <div class="<?php echo esc_attr(matrix_get_flexi_section_wrapper_class_names()); ?>">
        <?php if ($show_header) { ?>
            <div class="<?php echo esc_attr($heading_wrap_width_class); ?>">
                <?php if ($show_heading) { ?>
                    <<?php echo esc_attr($heading_tag); ?>
                        class="font-primary text-[24px] font-semibold leading-[28px] tracking-[-0.18px] text-[#1E244B] lg:text-[30px] lg:leading-[36px] lg:tracking-[-0.225px]"
                    >
                        <?php echo esc_html($heading); ?>
                    </<?php echo esc_attr($heading_tag); ?>>

                    <div class="mt-6 h-[4px] w-10 bg-[#6FC9C0]"></div>
                <?php } ?>

                <?php if ($show_intro) { ?>
                    <div class="wp_editor <?php echo $show_heading ? 'mt-6' : ''; ?> [&_p:last-child]:mb-0 [&_p]:font-primary [&_p]:text-[16px] [&_p]:font-medium [&_p]:leading-[28px] [&_p]:text-[#08284B]">
                        <?php echo matrix_kses_rich_text($intro); ?>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>

        <div class="flex flex-col items-center <?php echo $show_header ? 'mt-8 lg:mt-12' : ''; ?>" data-video-showcase-root>
            <div class="relative w-full <?php echo esc_attr($surface_width_class); ?> <?php echo esc_attr($surface_height_class); ?> overflow-hidden rounded-[8px] bg-[#D9D9D9] shadow-[0px_1px_1px_rgba(0,0,0,0.05)]">
                <a
                    href="#"
                    class="hidden absolute inset-0 z-20"
                    aria-label="Open video"
                    data-active-link
                ></a>

                <img
                    src="<?php echo esc_url($initial_slide['poster_image']['url']); ?>"
                    alt="<?php echo esc_attr($initial_slide['poster_image']['alt']); ?>"
                    class="object-cover absolute inset-0 w-full h-full"
                    data-active-image
                />

                <video
                    class="hidden object-cover absolute inset-0 z-10 w-full h-full"
                    playsinline
                    controls
                    preload="metadata"
                    poster="<?php echo esc_url($initial_slide['poster_image']['url']); ?>"
                    data-active-video
                ></video>

                <iframe
                    class="hidden absolute inset-0 z-10 w-full h-full"
                    title="Featured video"
                    allow="autoplay; fullscreen; picture-in-picture"
                    allowfullscreen
                    data-active-iframe
                ></iframe>

                <button
                    type="button"
                    class="flex absolute inset-0 z-30 justify-center items-center bg-transparent"
                    aria-label="Play video"
                    data-play-video
                >
                    <span class="flex h-16 w-16 items-center justify-center rounded-full border border-[#7ED0E0] bg-white/95 transition-colors hover:bg-[#F2F5F7]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="20" viewBox="0 0 18 20" fill="none" aria-hidden="true">
                            <path d="M3 4.07021C3 2.30927 4.9483 1.24593 6.42909 2.19777L15.6648 8.12756C17.0269 9.0023 17.0269 10.9977 15.6648 11.8724L6.42909 17.8022C4.9483 18.7541 3 17.6907 3 15.9298V4.07021Z" fill="#001F33"/>
                        </svg>
                    </span>
                </button>
            </div>

            <div class="mt-5 w-full <?php echo esc_attr($caption_width_class); ?>">
                <div
                    class="<?php echo esc_attr(trim('wp_editor [&_p:last-child]:mb-0 [&_p]:font-primary [&_p]:text-[#08284B] ' . ($layout_style === 'compact_slider' ? '[&_p]:text-[16px] [&_p]:leading-[26px]' : '[&_p]:text-[18px] [&_p]:leading-[28px]'))); ?>"
                    data-active-caption
                >
                    <?php echo matrix_kses_rich_text($initial_slide['caption']); ?>
                </div>

                <div class="mt-5 <?php echo $initial_slide['cta_link'] ? '' : 'hidden'; ?>" data-active-cta-wrap>
                    <a
                        href="<?php echo esc_url($initial_slide['cta_link']['url'] ?? '#'); ?>"
                        target="<?php echo esc_attr($initial_slide['cta_link']['target'] ?? '_self'); ?>"
                        class="inline-flex min-h-[52px] items-center justify-center border border-[#024B79] px-6 py-4 text-[16px] font-semibold leading-none text-[#024B79] transition-colors hover:bg-[#024B79] hover:text-white focus:outline focus:outline-2 focus:outline-offset-2 focus:outline-[#024B79]"
                        data-active-cta
                    >
                        <?php echo esc_html($initial_slide['cta_link']['title'] ?? 'Learn more'); ?>
                    </a>
                </div>
            </div>

            <?php if ($show_slider_controls) { ?>
                <div class="flex gap-6 items-center mt-8">
                    <button
                        type="button"
                        class="group flex h-8 w-8 items-center justify-center rounded-full border border-[#7ED0E0] bg-white transition-colors hover:bg-[#001F33] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#7ED0E0]"
                        aria-label="Previous video"
                        data-prev
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none" aria-hidden="true">
                            <path d="M6.08301 0.750081L0.749674 6.08342L6.08301 11.4167" class="stroke-[#020617] transition-colors group-hover:stroke-white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <div class="flex gap-4 items-center" data-dots>
                        <?php for ($i = 0; $i < $slide_count; $i++) { ?>
                            <button
                                type="button"
                                class="w-3 h-3 rounded-full transition-colors duration-200"
                                aria-label="<?php echo esc_attr('Go to video ' . ($i + 1)); ?>"
                                data-dot="<?php echo esc_attr($i); ?>"
                            ></button>
                        <?php } ?>
                    </div>

                    <button
                        type="button"
                        class="group flex h-8 w-8 items-center justify-center rounded-full border border-[#7ED0E0] bg-white transition-colors hover:bg-[#001F33] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#7ED0E0]"
                        aria-label="Next video"
                        data-next
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none" aria-hidden="true">
                            <path d="M0.916992 0.750081L6.25033 6.08342L0.916992 11.4167" class="stroke-[#020617] transition-colors group-hover:stroke-white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            <?php } ?>
        </div>
    </div>
</section>

<script>
(function () {
  var root = document.getElementById('<?php echo esc_js($section_id); ?>');
  if (!root) return;

  var slides = <?php echo wp_json_encode($slides); ?>;
  if (!slides.length) return;

  var activeImage = root.querySelector('[data-active-image]');
  var activeVideo = root.querySelector('[data-active-video]');
  var activeIframe = root.querySelector('[data-active-iframe]');
  var activeLink = root.querySelector('[data-active-link]');
  var playButton = root.querySelector('[data-play-video]');
  var caption = root.querySelector('[data-active-caption]');
  var ctaWrap = root.querySelector('[data-active-cta-wrap]');
  var ctaLink = root.querySelector('[data-active-cta]');
  var prevButton = root.querySelector('[data-prev]');
  var nextButton = root.querySelector('[data-next]');
  var dots = root.querySelectorAll('[data-dot]');

  var index = 0;
  var sliderEnabled = <?php echo $show_slider_controls ? 'true' : 'false'; ?>;

  function setDotStyles() {
    if (!dots.length) return;

    for (var i = 0; i < dots.length; i++) {
      dots[i].style.backgroundColor = i === index ? '#0F172A' : '#7ED0E0';
    }
  }

  function resetMedia() {
    if (activeVideo) {
      activeVideo.pause();
      activeVideo.removeAttribute('src');
      activeVideo.load();
      activeVideo.classList.add('hidden');
    }

    if (activeIframe) {
      activeIframe.setAttribute('src', '');
      activeIframe.classList.add('hidden');
    }

    if (activeImage) {
      activeImage.classList.remove('hidden');
    }
  }

  function updateSurface() {
    var slide = slides[index];
    if (!slide) return;

    resetMedia();

    activeImage.src = slide.poster_image.url || '';
    activeImage.alt = slide.poster_image.alt || 'Video poster image';
    activeVideo.poster = slide.poster_image.url || '';

    var isInlineVideo = slide.video_type === 'local' || slide.video_type === 'youtube' || slide.video_type === 'vimeo';

    if (caption) {
      caption.innerHTML = slide.caption || '';
    }

    if (ctaWrap && ctaLink) {
      if (slide.cta_link && slide.cta_link.url) {
        ctaWrap.classList.remove('hidden');
        ctaLink.href = slide.cta_link.url;
        ctaLink.target = slide.cta_link.target || '_self';
        ctaLink.textContent = slide.cta_link.title || 'Learn more';
      } else {
        ctaWrap.classList.add('hidden');
      }
    }

    if (activeLink) {
      if (!isInlineVideo && slide.video_url) {
        activeLink.href = slide.video_url;
        activeLink.target = '_self';
        activeLink.classList.remove('hidden');
      } else {
        activeLink.classList.add('hidden');
      }
    }

    if (playButton) {
      playButton.style.display = isInlineVideo ? 'flex' : 'none';
    }

    if (activeVideo && slide.video_type === 'local' && slide.video_url) {
      activeVideo.src = slide.video_url;
      activeVideo.load();
    }

    setDotStyles();
  }

  function goTo(nextIndex) {
    index = (nextIndex + slides.length) % slides.length;
    updateSurface();
  }

  if (sliderEnabled && prevButton) {
    prevButton.addEventListener('click', function () {
      goTo(index - 1);
    });
  }

  if (sliderEnabled && nextButton) {
    nextButton.addEventListener('click', function () {
      goTo(index + 1);
    });
  }

  if (sliderEnabled && dots.length) {
    for (var i = 0; i < dots.length; i++) {
      (function (dotIndex) {
        dots[dotIndex].addEventListener('click', function () {
          goTo(dotIndex);
        });
      })(i);
    }
  }

  if (playButton) {
    playButton.addEventListener('click', function (event) {
      event.preventDefault();

      var slide = slides[index];
      if (!slide) return;

      if (slide.video_type === 'local' && activeVideo && activeVideo.getAttribute('src')) {
        playButton.style.display = 'none';
        activeImage.classList.add('hidden');
        activeVideo.classList.remove('hidden');
        activeVideo.play();
        return;
      }

      if ((slide.video_type === 'youtube' || slide.video_type === 'vimeo') && activeIframe && slide.video_embed_url) {
        playButton.style.display = 'none';
        activeImage.classList.add('hidden');
        var joiner = slide.video_embed_url.indexOf('?') === -1 ? '?' : '&';
        activeIframe.setAttribute('src', slide.video_embed_url + joiner + 'autoplay=1');
        activeIframe.classList.remove('hidden');
      }
    });
  }

  updateSurface();
})();
</script>
