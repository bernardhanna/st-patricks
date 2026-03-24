<?php
// Get ACF fields
$section_id = 'stories-' . uniqid();
$posts_per_slide = get_sub_field('posts_per_slide') ?: 4;
$max_posts = get_sub_field('max_posts') ?: 20;
$show_date = get_sub_field('show_date') !== false;
$show_excerpt = get_sub_field('show_excerpt') ?: false;

// desktop-only look (existing)
$card_background_color = get_sub_field('card_background_color');
if (empty($card_background_color) || strtolower((string) $card_background_color) === '#fafaf9') {
  $card_background_color = 'var(--StPatricks_Aux_DarkBG2, #FBF8F3)';
}
$divider_color = get_sub_field('divider_color') ?: '#c7d2fe';
$text_color = get_sub_field('text_color') ?: '#0f2419';
$date_color = get_sub_field('date_color') ?: '#0f2419';

// Padding classes
$padding_classes = [];
if (have_rows('padding_settings')) {
  while (have_rows('padding_settings')) {
    the_row();
    $screen_size = get_sub_field('screen_size');
    $padding_top = get_sub_field('padding_top');
    $padding_bottom = get_sub_field('padding_bottom');
    if ($screen_size !== '' && $padding_top !== '' && $padding_top !== null) {
      $padding_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
    }
    if ($screen_size !== '' && $padding_bottom !== '' && $padding_bottom !== null) {
      $padding_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
    }
  }
}

// Query posts
$posts_query = new WP_Query([
  'post_type' => 'post',
  'posts_per_page' => $max_posts,
  'post_status' => 'publish',
  'orderby' => 'date',
  'order' => 'DESC'
]);
$posts = $posts_query->posts;

$total_slides = $posts_per_slide ? ceil(count($posts) / $posts_per_slide) : 0;
?>
<section
  id="<?php echo esc_attr($section_id); ?>"
  data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
  class="relative flex overflow-hidden <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
  aria-label="Latest Stories"
>
  <div class="flex flex-col items-center pt-5 pb-24 mx-auto w-full max-w-container max-lg:px-5 max-md:pt-16 max-md:pb-16 max-sm:pt-10 max-sm:pb-10">

    <!-- MOBILE/TABLET (< lg) — horizontal scroll with its own arrows & dots -->
    <div class="w-full lg:hidden">
      <div class="relative">
        <style>
          #<?php echo esc_attr($section_id); ?>-track {
            -ms-overflow-style: none;  /* IE and old Edge */
            scrollbar-width: none;     /* Firefox */
          }
          #<?php echo esc_attr($section_id); ?>-track::-webkit-scrollbar {
            display: none;             /* Chrome, Safari, Opera */
          }
        </style>
        <div
          id="<?php echo esc_attr($section_id); ?>-track"
          class="flex overflow-x-auto gap-3 pb-4 mb-4 scroll-smooth snap-x snap-mandatory"
          role="region"
          aria-label="Stories (scroll horizontally)"
        >
          <?php if (!empty($posts)) : foreach ($posts as $p) :
            setup_postdata($p);
            $post_date  = get_the_date('j M Y', $p->ID);
            $post_title = get_the_title($p->ID);
            $post_excerpt = get_the_excerpt($p->ID);
            $mobile_excerpt = !empty($post_excerpt)
              ? wp_trim_words($post_excerpt, 20)
              : wp_trim_words(wp_strip_all_tags(get_post_field('post_content', $p->ID)), 20);
            $post_url   = get_permalink($p->ID);
            $img_url    = get_the_post_thumbnail_url($p->ID, 'medium_large') ?: '';
          ?>
            <article class="flex-shrink-0 w-[237px] rounded-card shadow-sm overflow-hidden snap-start" style="background: var(--StPatricks_Aux_DarkBG2, #FBF8F3);">
              <a href="<?php echo esc_url($post_url); ?>" class="block" aria-label="<?php echo esc_attr(sprintf('Read more: %s', $post_title)); ?>">
                <?php if ($img_url): ?>
                  <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($post_title); ?>" class="w-full h-[260px] object-cover" />
                <?php endif; ?>
                <div class="p-4">
                  <?php if ($show_date): ?>
                    <time datetime="<?php echo esc_attr(get_the_date('c', $p->ID)); ?>" class="block mb-1 text-sm font-medium text-secondary-coral">
                      <?php echo esc_html($post_date); ?>
                    </time>
                  <?php endif; ?>
                  <h3 class="font-semibold text-h4 font-heading text-secondary-coral">
                    <?php echo esc_html($post_title); ?>
                  </h3>
                  <?php if ($show_excerpt && !empty($mobile_excerpt)): ?>
                    <p class="mt-2 text-sm leading-6 text-teal-950">
                      <?php echo esc_html($mobile_excerpt); ?>
                    </p>
                  <?php endif; ?>
                </div>
              </a>
            </article>
          <?php endforeach; endif; ?>
        </div>

        <!-- Mobile controls -->
        <nav class="flex gap-6 justify-center items-center mb-4" aria-label="Stories navigation (mobile)">
          <button
            type="button"
            id="<?php echo esc_attr($section_id); ?>-m-prev"
            class="group flex justify-center items-center w-8 h-8 bg-white rounded-full border border-[#7ED0E0] transition-colors hover:border-[#7ED0E0] hover:bg-[#001F33] active:border-[#7ED0E0] active:bg-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#7ED0E0] focus:border-[#7ED0E0] focus:bg-[#001F33] disabled:opacity-50 disabled:cursor-not-allowed"
            aria-label="Previous story"
            title="Previous story"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none" aria-hidden="true">
              <path d="M6.08301 0.750081L0.749674 6.08342L6.08301 11.4167" class="stroke-[#020617] transition-colors group-hover:stroke-white group-active:stroke-white group-focus:stroke-white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>

          <div id="<?php echo esc_attr($section_id); ?>-m-dots" class="flex gap-3 items-center"></div>

          <button
            type="button"
            id="<?php echo esc_attr($section_id); ?>-m-next"
            class="group flex justify-center items-center w-8 h-8 bg-white rounded-full border border-[#7ED0E0] transition-colors hover:border-[#7ED0E0] hover:bg-[#001F33] active:border-[#7ED0E0] active:bg-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#7ED0E0] focus:border-[#7ED0E0] focus:bg-[#001F33] disabled:opacity-50 disabled:cursor-not-allowed"
            aria-label="Next story"
            title="Next story"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none" aria-hidden="true">
              <path d="M0.916992 0.750081L6.25033 6.08342L0.916992 11.4167" class="stroke-[#020617] transition-colors group-hover:stroke-white group-active:stroke-white group-focus:stroke-white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
        </nav>
      </div>
    </div>

    <!-- DESKTOP (≥ lg) — original slider with arrows & dots -->
    <div class="hidden mx-auto w-full lg:block max-w-container_md">
      <div
        id="<?php echo esc_attr($section_id); ?>-desktop"
        class="w-full stories-slider-desktop"
        role="region"
        aria-label="Stories carousel"
        aria-live="polite"
      >
        <?php
        $slide_index = 0;
        for ($i = 0; $i < count($posts); $i += $posts_per_slide):
          $slide_posts = array_slice($posts, $i, $posts_per_slide);
          $slide_index++;
        ?>
          <div class="slide" role="group" aria-label="Slide <?php echo esc_attr($slide_index); ?> of <?php echo esc_attr($total_slides); ?>" style="<?php echo $slide_index === 1 ? '' : 'display:none'; ?>">
            <div class="flex gap-3 justify-between items-stretch w-full max-w-full">
              <?php foreach ($slide_posts as $card_index => $sp):
                setup_postdata($sp);
                $post_date  = get_the_date('j M Y', $sp->ID);
                $post_title = get_the_title($sp->ID);
                $post_excerpt = get_the_excerpt($sp->ID);
                $post_url   = get_permalink($sp->ID);
                $post_thumb = get_the_post_thumbnail_url($sp->ID, 'medium_large') ?: '';
                $position_in_pattern = ($card_index % 4) + 1;
                $show_thumbnail_layout = in_array($position_in_pattern, [1, 3], true);
                $excerpt_text = !empty($post_excerpt)
                  ? wp_trim_words($post_excerpt, 20)
                  : wp_trim_words(wp_strip_all_tags(get_post_field('post_content', $sp->ID)), 20);
              ?>
                <article class="flex flex-col flex-[1_0_0] max-w-[280px] h-full min-h-[290px]">
                  <a
                    href="<?php echo esc_url($post_url); ?>"
                    class="block w-full h-full rounded-lg shadow-sm group focus:outline-none focus:ring-2 focus:ring-offset-2 lg:min-h-[290px]"
                    aria-label="<?php echo esc_attr(sprintf('Read more: %s', $post_title)); ?>"
                    title="<?php echo esc_attr($post_title); ?>"
                    style="background-color: <?php echo esc_attr($card_background_color); ?>;"
                  >
                    <div class="box-border flex flex-col gap-1.5 items-start p-4 w-full">
                      <header class="flex flex-col gap-3 justify-center items-start w-full">
                        <?php if ($show_thumbnail_layout && !empty($post_thumb)): ?>
                          <img
                            src="<?php echo esc_url($post_thumb); ?>"
                            alt="<?php echo esc_attr($post_title); ?>"
                            class="w-full h-[142px] object-cover rounded"
                            loading="lazy"
                          />
                        <?php endif; ?>

                        <?php if ($show_date): ?>
                          <time
                            datetime="<?php echo esc_attr(get_the_date('c', $sp->ID)); ?>"
                            class="w-full text-sm font-medium leading-6"
                            style="color: <?php echo esc_attr($date_color); ?>;"
                          ><?php echo esc_html($post_date); ?></time>
                        <?php endif; ?>

                        <div class="w-10 h-1" style="background-color: <?php echo esc_attr($divider_color); ?>;" aria-hidden="true"></div>

                        <h3 class="w-full text-xl font-semibold tracking-normal leading-7 group-hover:underline">
                          <span style="color: <?php echo esc_attr($text_color); ?>;"><?php echo esc_html($post_title); ?></span>
                        </h3>

                        <?php if (!$show_thumbnail_layout && !empty($excerpt_text)): ?>
                          <p class="mt-2 w-full text-sm leading-6" style="color: <?php echo esc_attr($text_color); ?>;">
                            <?php echo esc_html($excerpt_text); ?>
                          </p>
                        <?php endif; ?>
                      </header>
                    </div>
                  </a>
                </article>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endfor; ?>
      </div>

      <?php if ($total_slides > 1): ?>
        <nav class="flex gap-6 justify-center items-center pt-12 w-ful" aria-label="Stories navigation (desktop)">
          <button
            type="button"
            id="<?php echo esc_attr($section_id); ?>-d-prev"
            class="group flex justify-center items-center w-8 h-8 bg-white rounded-full border border-[#7ED0E0] transition-colors hover:border-[#7ED0E0] hover:bg-[#001F33] active:border-[#7ED0E0] active:bg-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#7ED0E0] focus:border-[#7ED0E0] focus:bg-[#001F33] disabled:opacity-50 disabled:cursor-not-allowed"
            aria-label="Previous stories"
            title="Previous stories"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none" aria-hidden="true">
              <path d="M6.08301 0.750081L0.749674 6.08342L6.08301 11.4167" class="stroke-[#020617] transition-colors group-hover:stroke-white group-active:stroke-white group-focus:stroke-white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>

          <div id="<?php echo esc_attr($section_id); ?>-d-dots" class="flex gap-4 items-center" role="tablist" aria-label="Story slides">
            <?php for ($dot = 0; $dot < $total_slides; $dot++): ?>
              <button
                type="button"
                class="w-3 h-3 rounded-full transition-colors duration-200 cursor-pointer"
                data-slide="<?php echo esc_attr($dot); ?>"
                role="tab"
                aria-label="Go to slide <?php echo esc_attr($dot + 1); ?>"
                aria-selected="<?php echo $dot === 0 ? 'true' : 'false'; ?>"
                tabindex="<?php echo $dot === 0 ? '0' : '-1'; ?>"
                style="<?php echo $dot === 0 ? 'background-color:#0f172a' : 'background-color:#7ED0E0'; ?>"
              ></button>
            <?php endfor; ?>
          </div>

          <button
            type="button"
            id="<?php echo esc_attr($section_id); ?>-d-next"
            class="group flex justify-center items-center w-8 h-8 bg-white rounded-full border border-[#7ED0E0] transition-colors hover:border-[#7ED0E0] hover:bg-[#001F33] active:border-[#7ED0E0] active:bg-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#7ED0E0] focus:border-[#7ED0E0] focus:bg-[#001F33] disabled:opacity-50 disabled:cursor-not-allowed"
            aria-label="Next stories"
            title="Next stories"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none" aria-hidden="true">
              <path d="M0.916992 0.750081L6.25033 6.08342L0.916992 11.4167" class="stroke-[#020617] transition-colors group-hover:stroke-white group-active:stroke-white group-focus:stroke-white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
        </nav>
      <?php endif; ?>
    </div>

  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var root = document.getElementById('<?php echo esc_js($section_id); ?>');
  if (!root) return;

  /* -------------------------
   * Desktop slider (>= lg)
   * ------------------------- */
  (function(){
    var wrap = document.getElementById('<?php echo esc_js($section_id); ?>-desktop');
    if (!wrap) return;

    var slides = Array.prototype.slice.call(wrap.children);
    if (!slides.length) return;

    var prevBtn = document.getElementById('<?php echo esc_js($section_id); ?>-d-prev');
    var nextBtn = document.getElementById('<?php echo esc_js($section_id); ?>-d-next');
    var dotsWrap = document.getElementById('<?php echo esc_js($section_id); ?>-d-dots');
    var dots = dotsWrap ? Array.prototype.slice.call(dotsWrap.children) : [];

    var current = 0;

    function show(i){
      slides.forEach(function(slide, idx){
        var on = idx === i;
        slide.style.display = on ? '' : 'none';
        slide.setAttribute('aria-hidden', on ? 'false' : 'true');
      });
      current = i;
      updateUI();
    }

    function updateUI(){
      if (prevBtn) {
        var dis = current === 0;
        prevBtn.disabled = dis;
        prevBtn.setAttribute('aria-disabled', dis ? 'true' : 'false');
      }
      if (nextBtn) {
        var disN = current === slides.length - 1;
        nextBtn.disabled = disN;
        nextBtn.setAttribute('aria-disabled', disN ? 'true' : 'false');
      }
      if (dots.length){
        dots.forEach(function(dot, idx){
          var active = idx === current;
          dot.setAttribute('aria-selected', active ? 'true' : 'false');
          dot.setAttribute('tabindex', active ? '0' : '-1');
          dot.style.backgroundColor = active ? '#0f172a' : '#7ED0E0';
        });
      }
    }

    if (prevBtn) prevBtn.addEventListener('click', function(){ if (current > 0) show(current - 1); });
    if (nextBtn) nextBtn.addEventListener('click', function(){ if (current < slides.length - 1) show(current + 1); });
    if (dots.length){
      dots.forEach(function(dot, idx){
        dot.addEventListener('click', function(){ show(idx); });
      });
    }

    show(0);
  })();

  /* -------------------------
   * Mobile scroller (< lg): 1-card per step with arrows & dots
   * ------------------------- */
  (function(){
    var track = document.getElementById('<?php echo esc_js($section_id); ?>-track');
    if (!track) return;

    var cards = Array.prototype.slice.call(track.children);
    if (!cards.length) return;

    var prevBtn = document.getElementById('<?php echo esc_js($section_id); ?>-m-prev');
    var nextBtn = document.getElementById('<?php echo esc_js($section_id); ?>-m-next');
    var dotsWrap = document.getElementById('<?php echo esc_js($section_id); ?>-m-dots');

    // Build dots
    if (dotsWrap){
      dotsWrap.innerHTML = '';
      cards.forEach(function(_, idx){
        var b = document.createElement('button');
        b.type = 'button';
        b.className = 'w-3 h-3 rounded-full transition-colors duration-200';
        b.setAttribute('aria-label', 'Go to card ' + (idx + 1));
        b.dataset.index = idx;
        dotsWrap.appendChild(b);
      });
    }
    var dots = dotsWrap ? Array.prototype.slice.call(dotsWrap.children) : [];

    // Helpers to compute widths & gaps
    function getStepSize(){
      var card = cards[0];
      if (!card) return 0;
      var cs = window.getComputedStyle(track);
      var gap = parseFloat(cs.columnGap || cs.gap || 0) || 0;
      return Math.round(card.getBoundingClientRect().width + gap);
    }

    var currentIndex = 0;
    function clamp(i){ return Math.max(0, Math.min(i, cards.length - 1)); }

    function scrollToIndex(i, behavior){
      i = clamp(i);
      var step = getStepSize();
      track.scrollTo({ left: step * i, behavior: behavior || 'smooth' });
      currentIndex = i;
      updateUI();
    }

    function indexFromScroll(){
      var step = getStepSize();
      if (!step) return 0;
      return clamp(Math.round(track.scrollLeft / step));
    }

    function updateUI(){
      // Keep mobile indicator count compact: never show more than 4 dots.
      if (dots.length){
        var maxVisibleDots = 4;
        if (dots.length <= maxVisibleDots) {
          dots.forEach(function(dot){
            dot.style.display = '';
          });
        } else {
          var start = Math.max(0, Math.min(currentIndex - 1, dots.length - maxVisibleDots));
          var end = start + maxVisibleDots - 1;
          dots.forEach(function(dot, idx){
            dot.style.display = (idx >= start && idx <= end) ? '' : 'none';
          });
        }
      }

      if (prevBtn) {
        var dis = currentIndex === 0;
        prevBtn.disabled = dis;
        prevBtn.setAttribute('aria-disabled', dis ? 'true' : 'false');
      }
      if (nextBtn) {
        var disN = currentIndex === cards.length - 1;
        nextBtn.disabled = disN;
        nextBtn.setAttribute('aria-disabled', disN ? 'true' : 'false');
      }
      if (dots.length){
        dots.forEach(function(dot, idx){
          var active = idx === currentIndex;
          dot.style.backgroundColor = active ? '#0f172a' : '#7ED0E0';
          dot.setAttribute('aria-selected', active ? 'true' : 'false');
          dot.setAttribute('tabindex', active ? '0' : '-1');
        });
      }
    }

    // Events
    if (prevBtn) prevBtn.addEventListener('click', function(){ scrollToIndex(currentIndex - 1); });
    if (nextBtn) nextBtn.addEventListener('click', function(){ scrollToIndex(currentIndex + 1); });
    if (dots.length){
      dots.forEach(function(dot){
        dot.addEventListener('click', function(){
          var i = parseInt(dot.dataset.index || '0', 10) || 0;
          scrollToIndex(i);
        });
      });
    }

    // Sync dots with manual swipe
    var raf = null;
    track.addEventListener('scroll', function(){
      if (raf) cancelAnimationFrame(raf);
      raf = requestAnimationFrame(function(){
        var idx = indexFromScroll();
        if (idx !== currentIndex){
          currentIndex = idx;
          updateUI();
        }
      });
    });

    // Init
    updateUI();
  })();
});
</script>

<?php wp_reset_postdata(); ?>
