<?php
/**
 * Related Posts Section — Grid ≥768px, Slider <768px (2 per view 575–767, 1 per view <575)
 * Arrows + indicators styled to match the features slider.
 */

$current_id = get_the_ID();
$categories = wp_get_post_terms($current_id, 'category', ['fields' => 'ids']);

// Prefer same-category posts
$args_related = [
  'post_type'      => 'post',
  'posts_per_page' => 6, // a few more so the slider feels natural
  'post__not_in'   => [$current_id],
  'orderby'        => 'date',
  'order'          => 'DESC',
];

if (!empty($categories)) {
  $args_related['category__in'] = $categories;
}

$related = new WP_Query($args_related);

// Fallback to latest posts
if (!$related->have_posts()) {
  $related = new WP_Query([
    'post_type'      => 'post',
    'posts_per_page' => 6,
    'post__not_in'   => [$current_id],
    'orderby'        => 'date',
    'order'          => 'DESC',
  ]);
}

$section_id = 'related-posts-' . esc_attr( wp_generate_uuid4() );

if ($related->have_posts()) :
?>
<section
  id="<?php echo esc_attr($section_id); ?>"
  class="flex overflow-hidden relative bg-white"
  role="region"
  aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
>
  <div class="flex flex-col items-center py-[5rem] mx-auto w-full max-w-container max-lg:px-5">
    <div class="px-4 bg-white w-full mx-auto max-w-[1170px]">
      <h6
        id="<?php echo esc_attr($section_id); ?>-heading"
        class="mb-20 text-2xl font-medium leading-8 text-center text-zinc-900"
      >
        You may also like
      </h6>

      <!-- GRID (≥768px) -->
      <div class="w-full rp-grid">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <?php while ($related->have_posts()) : $related->the_post(); ?>
            <?php
              $post_id   = get_the_ID();
              $post_url  = get_permalink();
              $title     = get_the_title();
              $date      = get_the_date('j F Y');
              $excerpt   = get_the_excerpt();

              // reading time
              $content     = get_post_field('post_content', $post_id);
              $word_count  = str_word_count(wp_strip_all_tags($content));
              $minutes     = max(1, (int)ceil($word_count / 200));
              $read_time   = $minutes . ' min' . ($minutes > 1 ? 's' : '');

              // image
              $thumb_id  = get_post_thumbnail_id($post_id);
              $img_url   = $thumb_id ? get_the_post_thumbnail_url($post_id, 'large') : 'https://via.placeholder.com/425x240?text=No+Image';
              $img_alt   = $thumb_id ? (get_post_meta($thumb_id, '_wp_attachment_image_alt', true) ?: ($title . ' featured image')) : ($title . ' featured image');
            ?>
            <a
              href="<?php echo esc_url($post_url); ?>"
              class="block overflow-hidden pb-4 bg-white rounded-br-3xl rounded-bl-3xl border-2 border-gray-300 border-solid group border-shape"
              aria-label="<?php echo esc_attr('Read full article: ' . $title); ?>"
            >
              <div class="overflow-hidden relative">
                <img
                  width="425" height="240"
                  alt="<?php echo esc_attr($img_alt); ?>"
                  src="<?php echo esc_url($img_url); ?>"
                  class="max-w-full w-full h-auto object-cover aspect-[425/240] duration-500 transition-transform group-hover:scale-105"
                  loading="lazy" decoding="async"
                />
              </div>
              <div class="py-4 bg-white">
                <div class="px-8 py-2.5 text-sm font-medium leading-5 text-gray-500 opacity-80">
                  <time datetime="<?php echo esc_attr(get_the_date('c')); ?>" class="text-sm text-gray-500">
                    <?php echo esc_html($date); ?> • Read time: <?php echo esc_html($read_time); ?>
                  </time>
                </div>
                <div class="px-8 mb-3.5">
                  <h6 class="text-2xl font-medium leading-8">
                    <span class="text-xl font-medium leading-7 transition-colors duration-200 cursor-pointer text-zinc-900 group-hover:text-primary">
                      <?php echo esc_html($title); ?>
                    </span>
                  </h6>
                </div>
                <div class="px-8 mb-2.5 text-lg leading-7 text-gray-500">
                  <p><?php echo esc_html($excerpt); ?></p>
                </div>
                <div class="px-8 mt-2.5 text-left">
                  <div
                    class="btn flex gap-2 justify-center items-center px-6 py-4 my-auto text-sm font-semibold leading-none bg-white border-primary border-2 border-solid min-h-[52px] max-md:px-5 transition-colors duration-200 group text-primary hover:text-white hover:bg-primary"
                    style="width:160px; height:48px;"
                  >
                    View More
                  </div>
                </div>
              </div>
            </a>
          <?php endwhile; ?>
          <?php wp_reset_postdata(); ?>
        </div>
      </div>

      <!-- SLIDER (<768px) -->
      <?php
      // re-query the same posts for the slider instance
      $related2 = new WP_Query($args_related);
      if (!$related2->have_posts()) {
        $related2 = new WP_Query([
          'post_type'      => 'post',
          'posts_per_page' => 6,
          'post__not_in'   => [$current_id],
          'orderby'        => 'date',
          'order'          => 'DESC',
        ]);
      }
      ?>
      <?php if ($related2->have_posts()) : ?>
      <div class="w-full rp-slider">
        <div class="relative w-full">
          <!-- Track -->
          <div id="<?php echo esc_attr($section_id); ?>__track"
               class="flex transition-transform duration-300 ease-out will-change-transform"
               style="transform: translateX(0);">
            <?php while ($related2->have_posts()) : $related2->the_post(); ?>
              <?php
                $post_id   = get_the_ID();
                $post_url  = get_permalink();
                $title     = get_the_title();
                $date      = get_the_date('j F Y');
                $excerpt   = get_the_excerpt();

                $content     = get_post_field('post_content', $post_id);
                $word_count  = str_word_count(wp_strip_all_tags($content));
                $minutes     = max(1, (int)ceil($word_count / 200));
                $read_time   = $minutes . ' min' . ($minutes > 1 ? 's' : '');

                $thumb_id  = get_post_thumbnail_id($post_id);
                $img_url   = $thumb_id ? get_the_post_thumbnail_url($post_id, 'large') : 'https://via.placeholder.com/425x240?text=No+Image';
                $img_alt   = $thumb_id ? (get_post_meta($thumb_id, '_wp_attachment_image_alt', true) ?: ($title . ' featured image')) : ($title . ' featured image');
              ?>
              <article class="flex-shrink-0 px-2">
                <a
                  href="<?php echo esc_url($post_url); ?>"
                  class="block overflow-hidden pb-4 bg-white rounded-br-3xl rounded-bl-3xl border-2 border-gray-300 border-solid group border-shape"
                  aria-label="<?php echo esc_attr('Read full article: ' . $title); ?>"
                >
                  <div class="overflow-hidden relative">
                    <img
                      width="425" height="240"
                      alt="<?php echo esc_attr($img_alt); ?>"
                      src="<?php echo esc_url($img_url); ?>"
                      class="max-w-full w-full h-auto object-cover aspect-[425/240] duration-500 transition-transform group-hover:scale-105"
                      loading="lazy" decoding="async"
                    />
                  </div>
                  <div class="py-4 bg-white">
                    <div class="px-8 py-2.5 text-sm font-medium leading-5 text-gray-500 opacity-80">
                      <time datetime="<?php echo esc_attr(get_the_date('c')); ?>" class="text-sm text-gray-500">
                        <?php echo esc_html($date); ?> • Read time: <?php echo esc_html($read_time); ?>
                      </time>
                    </div>
                    <div class="px-8 mb-3.5">
                      <h6 class="text-2xl font-medium leading-8">
                        <span class="text-xl font-medium leading-7 transition-colors duration-200 cursor-pointer text-zinc-900 group-hover:text-primary">
                          <?php echo esc_html($title); ?>
                        </span>
                      </h6>
                    </div>
                    <div class="px-8 mb-2.5 text-lg leading-7 text-gray-500">
                      <p><?php echo esc_html($excerpt); ?></p>
                    </div>
                    <div class="px-8 mt-2.5 text-left">
                      <div
                        class="btn flex gap-2 justify-center items-center px-6 py-4 my-auto text-sm font-semibold leading-none bg-white border-primary border-2 border-solid min-h-[52px] max-md:px-5 transition-colors duration-200 group text-primary hover:text-white hover:bg-primary"
                        style="width:160px; height:48px;"
                      >
                        View More
                      </div>
                    </div>
                  </div>
                </a>
              </article>
            <?php endwhile; wp_reset_postdata(); ?>
          </div>

          <!-- Arrows (same style as features) -->
          <div class="flex gap-4 justify-center items-center mt-6">
            <button type="button" id="<?php echo esc_attr($section_id); ?>__prev"
              class="inline-flex justify-center items-center w-10 h-10 text-white rounded-full border transition-colors border-primary bg-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary hover:bg-white hover:text-primary"
              aria-label="Previous">
              <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <path d="M15 19l-7-7 7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>

            <button type="button" id="<?php echo esc_attr($section_id); ?>__next"
              class="inline-flex justify-center items-center w-10 h-10 text-white rounded-full border transition-colors border-primary bg-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary hover:bg-white hover:text-primary"
              aria-label="Next">
              <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
          </div>

          <!-- Indicators (same style, below arrows) -->
          <div id="<?php echo esc_attr($section_id); ?>__dots" class="flex gap-2 justify-center mt-12"></div>
        </div>
      </div>
      <?php endif; ?>

      <!-- Grid/Slider visibility -->
      <style>
        #<?php echo esc_attr($section_id); ?> .rp-grid  { display: block; }
        #<?php echo esc_attr($section_id); ?> .rp-slider{ display: none; }
        @media (max-width: 768px){
          #<?php echo esc_attr($section_id); ?> .rp-grid  { display: none; }
          #<?php echo esc_attr($section_id); ?> .rp-slider{ display: block; }
        }
      </style>

      <!-- Slider JS: perView (2 between 575–767, 1 under 575), autoplay, arrows, dots -->
      <script>
        (function(){
          var root  = "<?php echo esc_js($section_id); ?>";
          var track = document.getElementById(root + "__track");
          if (!track) return;

          var slides = Array.prototype.slice.call(track.children);
          var wrap   = track.parentElement; // relative w-full
          var prev   = document.getElementById(root + "__prev");
          var next   = document.getElementById(root + "__next");
          var dotsEl = document.getElementById(root + "__dots");
          var index  = 0;

          function perView(){
            var isOne = window.matchMedia("(max-width: 575px)").matches;
            var isTwo = window.matchMedia("(max-width: 768px)").matches;
            if (isOne) return 1;   // <575
            if (isTwo) return 2;   // 575–768
            return 3;              // not used in slider mode, but safe
          }

          function sliderActive(){ return window.matchMedia("(max-width: 768px)").matches; }

          function slideWidth(){
            return wrap.clientWidth / Math.max(1, perView());
          }

          function update(){
            var w = slideWidth();
            track.style.transform = "translateX(" + (-index * w) + "px)";
            // dots
            if (dotsEl) {
              var pv = perView();
              var currentPage = Math.floor(index / pv);
              Array.prototype.forEach.call(dotsEl.children, function(dot, i){
                dot.classList.toggle('opacity-100', i === currentPage);
                dot.classList.toggle('opacity-40', i !== currentPage);
              });
            }
          }

          function to(i){
            var pv = perView();
            var maxIndex = Math.max(0, slides.length - pv);
            if (i < 0) i = maxIndex;
            if (i > maxIndex) i = 0;
            index = i;
            update();
          }

          function sizeSlides(){
            var w = slideWidth();
            slides.forEach(function(slide){ slide.style.width = w + "px"; });
            update();
          }

          // build dots as pages
          function buildDots(){
            if (!dotsEl) return;
            dotsEl.innerHTML = '';
            var pv = perView();
            var pages = Math.max(1, Math.ceil(slides.length / pv));
            for (var i = 0; i < pages; i++){
              var b = document.createElement('button');
              b.type = 'button';
              b.className = 'w-2.5 h-2.5 rounded-full bg-primary opacity-40 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary';
              b.setAttribute('aria-label', 'Go to slide ' + (i+1));
              (function(pageIndex){
                b.addEventListener('click', function(){ to(pageIndex * pv); });
              })(i);
              dotsEl.appendChild(b);
            }
            update();
          }

          // arrows
          if (prev) prev.addEventListener('click', function(){ to(index - 1); });
          if (next) next.addEventListener('click', function(){ to(index + 1); });

          // autoplay (only when slider visible)
          var AUTOPLAY_MS   = 3500;
          var timer         = null;
          var prefersReduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

          function startAutoplay(){
            if (timer || prefersReduce || slides.length < 2 || !sliderActive()) return;
            timer = setInterval(function(){ to(index + 1); }, AUTOPLAY_MS);
          }
          function stopAutoplay(){ if (timer){ clearInterval(timer); timer = null; } }

          wrap.addEventListener('mouseenter', stopAutoplay);
          wrap.addEventListener('mouseleave', startAutoplay);
          wrap.addEventListener('focusin',  stopAutoplay);
          wrap.addEventListener('focusout', startAutoplay);
          document.addEventListener('visibilitychange', function(){ document.hidden ? stopAutoplay() : startAutoplay(); });

          try {
            var io = new IntersectionObserver(function(entries){
              entries.forEach(function(e){ e.isIntersecting ? startAutoplay() : stopAutoplay(); });
            }, { threshold: 0.1 });
            io.observe(wrap);
          } catch(e){}

          window.addEventListener('resize', function(){
            sizeSlides();
            buildDots();
            stopAutoplay();
            startAutoplay();
          });

          // init
          sizeSlides();
          buildDots();
          startAutoplay();
        })();
      </script>
    </div>
  </div>
</section>
<?php endif; ?>
