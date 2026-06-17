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

// Padding classes


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
  class="relative flex overflow-hidden"
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
          class="flex overflow-x-auto gap-3 pb-4 mb-4 scroll-smooth snap-x snap-mandatory cursor-grab touch-pan-x select-none"
          role="region"
          aria-label="Stories (scroll horizontally)"
        >
          <?php if (!empty($posts)) : foreach ($posts as $p) :
            setup_postdata($p);
            $post_date  = matrix_format_blog_post_date($p->ID);
            $post_title = get_the_title($p->ID);
            $post_excerpt = get_the_excerpt($p->ID);
            $mobile_excerpt = !empty($post_excerpt)
              ? wp_trim_words($post_excerpt, 20)
              : wp_trim_words(wp_strip_all_tags(get_post_field('post_content', $p->ID)), 20);
            $post_url   = get_permalink($p->ID);
            $img_url    = get_the_post_thumbnail_url($p->ID, 'medium_large') ?: '';
          ?>
            <article class="flex w-[237px] shrink-0 snap-start flex-col overflow-hidden rounded-lg shadow-sm" style="background: var(--StPatricks_Aux_DarkBG2, #FBF8F3);">
              <a href="<?php echo esc_url($post_url); ?>" class="group flex h-full flex-col" aria-label="<?php echo esc_attr(sprintf('Read more: %s', $post_title)); ?>">
                <?php if ($img_url): ?>
                  <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($post_title); ?>" class="h-[142px] w-full shrink-0 object-cover" />
                <?php endif; ?>
                <div class="flex flex-1 flex-col gap-3 p-4">
                  <?php if ($show_date): ?>
                    <time datetime="<?php echo esc_attr(get_the_date('c', $p->ID)); ?>" class="font-primary text-sm font-medium leading-6 text-[#08284B]">
                      <?php echo esc_html($post_date); ?>
                    </time>
                  <?php endif; ?>
                  <div class="h-1 w-10 shrink-0 bg-[#F9F1D1]" aria-hidden="true"></div>
                  <h3 class="font-primary text-xl font-semibold leading-7 tracking-[-0.00625rem] text-[#08284B] group-hover:underline">
                    <?php echo esc_html($post_title); ?>
                  </h3>
                  <?php if ($show_excerpt && !empty($mobile_excerpt)): ?>
                    <p class="mt-auto font-primary text-base font-medium leading-7 text-[#08284B]">
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
        class="w-full stories-slider-desktop cursor-grab touch-pan-y select-none"
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
            <div class="flex w-full max-w-full items-stretch justify-between gap-3">
              <?php foreach ($slide_posts as $card_index => $sp):
                setup_postdata($sp);
                $post_date  = matrix_format_blog_post_date($sp->ID);
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
                <article class="flex min-w-0 flex-[1_0_0] flex-col max-w-[280px]">
                  <a
                    href="<?php echo esc_url($post_url); ?>"
                    class="group flex h-full w-full flex-col rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2"
                    aria-label="<?php echo esc_attr(sprintf('Read more: %s', $post_title)); ?>"
                    title="<?php echo esc_attr($post_title); ?>"
                    style="background-color: <?php echo esc_attr($card_background_color); ?>;"
                  >
                    <div class="flex h-full w-full flex-col gap-3 p-4">
                      <?php if ($show_thumbnail_layout && !empty($post_thumb)): ?>
                        <img
                          src="<?php echo esc_url($post_thumb); ?>"
                          alt="<?php echo esc_attr($post_title); ?>"
                          class="h-[142px] w-full shrink-0 rounded object-cover"
                          loading="lazy"
                        />
                      <?php endif; ?>

                      <div class="flex flex-1 flex-col gap-3">
                        <?php if ($show_date): ?>
                          <time
                            datetime="<?php echo esc_attr(get_the_date('c', $sp->ID)); ?>"
                            class="w-full font-primary text-sm font-medium leading-6 text-[#08284B]"
                          ><?php echo esc_html($post_date); ?></time>
                        <?php endif; ?>

                        <div class="h-1 w-10 shrink-0 bg-[#F9F1D1]" aria-hidden="true"></div>

                        <h3 class="w-full font-primary text-xl font-semibold leading-7 tracking-[-0.00625rem] text-[#08284B] group-hover:underline">
                          <?php echo esc_html($post_title); ?>
                        </h3>

                        <?php if (!$show_thumbnail_layout && !empty($excerpt_text)): ?>
                          <p class="mt-auto w-full font-primary text-base font-medium leading-7 text-[#08284B]">
                            <?php echo esc_html($excerpt_text); ?>
                          </p>
                        <?php endif; ?>
                      </div>
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

    function equalizeSlideCards(slide){
      if (!slide) return;
      var articles = slide.querySelectorAll('article');
      if (!articles.length) return;
      articles.forEach(function(article){
        article.style.minHeight = '';
      });
      var maxHeight = 0;
      articles.forEach(function(article){
        maxHeight = Math.max(maxHeight, article.offsetHeight);
      });
      articles.forEach(function(article){
        article.style.minHeight = maxHeight + 'px';
      });
    }

    function show(i){
      slides.forEach(function(slide, idx){
        var on = idx === i;
        slide.style.display = on ? '' : 'none';
        slide.setAttribute('aria-hidden', on ? 'false' : 'true');
      });
      current = i;
      equalizeSlideCards(slides[i]);
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

    if (slides.length > 1) {
      var dragStartX = 0;
      var dragStartY = 0;
      var dragging = false;
      var dragThreshold = 48;

      wrap.addEventListener('pointerdown', function(e) {
        if (e.pointerType === 'mouse' && e.button !== 0) return;
        dragging = true;
        dragStartX = e.clientX;
        dragStartY = e.clientY;
        wrap.setPointerCapture(e.pointerId);
        wrap.classList.add('cursor-grabbing');
        wrap.classList.remove('cursor-grab');
      });

      function finishDesktopDrag(e) {
        if (!dragging) return;
        dragging = false;
        wrap.classList.remove('cursor-grabbing');
        wrap.classList.add('cursor-grab');

        try {
          wrap.releasePointerCapture(e.pointerId);
        } catch (err) {}

        var dx = e.clientX - dragStartX;
        var dy = e.clientY - dragStartY;
        if (Math.abs(dx) < dragThreshold || Math.abs(dx) < Math.abs(dy)) return;

        if (dx > 0 && current > 0) {
          show(current - 1);
        } else if (dx < 0 && current < slides.length - 1) {
          show(current + 1);
        }
      }

      wrap.addEventListener('pointerup', finishDesktopDrag);
      wrap.addEventListener('pointercancel', function() {
        dragging = false;
        wrap.classList.remove('cursor-grabbing');
        wrap.classList.add('cursor-grab');
      });
    }

    show(0);

    window.addEventListener('resize', function(){
      equalizeSlideCards(slides[current]);
    });
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

    // Mouse drag to scroll on touch/trackpad devices below lg
    var dragScrollActive = false;
    var dragScrollStartX = 0;
    var dragScrollStartLeft = 0;

    track.addEventListener('pointerdown', function(e) {
      if (e.pointerType !== 'mouse' || e.button !== 0) return;
      dragScrollActive = true;
      dragScrollStartX = e.clientX;
      dragScrollStartLeft = track.scrollLeft;
      track.setPointerCapture(e.pointerId);
      track.classList.add('cursor-grabbing');
      track.classList.remove('cursor-grab');
    });

    track.addEventListener('pointermove', function(e) {
      if (!dragScrollActive) return;
      track.scrollLeft = dragScrollStartLeft - (e.clientX - dragScrollStartX);
    });

    function endDragScroll(e) {
      if (!dragScrollActive) return;
      dragScrollActive = false;
      track.classList.remove('cursor-grabbing');
      track.classList.add('cursor-grab');

      try {
        track.releasePointerCapture(e.pointerId);
      } catch (err) {}

      currentIndex = indexFromScroll();
      updateUI();
    }

    track.addEventListener('pointerup', endDragScroll);
    track.addEventListener('pointercancel', function() {
      dragScrollActive = false;
      track.classList.remove('cursor-grabbing');
      track.classList.add('cursor-grab');
    });

    // Init
    updateUI();
  })();
});
</script>

<?php wp_reset_postdata(); ?>
