<?php
/**
 * Flexi Block: Counters (About Us)
 * - Tailwind-only, no Vue / Alpine
 * - Uses get_sub_field()
 * - Vanilla JS counter animation
 */

if (!defined('ABSPATH')) {
    exit;
}

$section_id = 'counters-' . ( function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid() );

// Content
$heading_tag   = get_sub_field('heading_tag') ?: 'h2';
$heading_text  = (string) (get_sub_field('heading_text') ?: 'About us');
$description   = (string) (get_sub_field('description') ?: '');
$image         = get_sub_field('image') ?: [];
$key_points    = get_sub_field('key_points') ?: [];
$primary_cta   = get_sub_field('primary_cta');
$secondary_cta = get_sub_field('secondary_cta');

// Design
$background_color = get_sub_field('background_color') ?: '#ffffff';
$heading_color    = get_sub_field('heading_color') ?: '#1e1b4b';
$desc_color       = get_sub_field('desc_color') ?: '#134e4a';
$divider_color    = get_sub_field('divider_color') ?: '#ef4444';
$value_color      = get_sub_field('value_color') ?: '#0c4a6e';
$title_color      = get_sub_field('title_color') ?: '#1e1b4b';
$text_color       = get_sub_field('text_color') ?: '#134e4a';

// Layout padding classes
$padding_classes = [];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen = get_sub_field('screen_size');
        $pt     = get_sub_field('padding_top');
        $pb     = get_sub_field('padding_bottom');

        if ($screen && $pt !== '' && $pt !== null) {
            $padding_classes[] = "{$screen}:pt-[{$pt}rem]";
        }
        if ($screen && $pb !== '' && $pb !== null) {
            $padding_classes[] = "{$screen}:pb-[{$pb}rem]";
        }
    }
}

// Helper for image
$img_parts = function($img, $alt_fallback = '') {
    if (!is_array($img)) {
        return ['', $alt_fallback, $alt_fallback];
    }
    $url = $img['url']   ?? '';
    $alt = $img['alt']   ?? ($img['title'] ?? $alt_fallback);
    $ttl = $img['title'] ?? $alt;
    return [$url, $alt ?: $alt_fallback, $ttl ?: $alt_fallback];
};

[$img_url, $img_alt, $img_title] = $img_parts($image, 'About us image');

// Sanitize heading tag
$allowed_tags = ['h1','h2','h3','h4','h5','h6','span','p'];
if (!in_array($heading_tag, $allowed_tags, true)) {
    $heading_tag = 'h2';
}
?>

<section id="<?php echo esc_attr($section_id); ?>"
         class="relative flex overflow-hidden <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
         style="background-color: <?php echo esc_attr($background_color); ?>;">

  <div class="flex flex-col items-center pt-24 pb-24 mx-auto w-full max-w-container_md max-lg:px-5 max-md:pt-16 max-md:pb-16 max-sm:pt-10 max-sm:pb-10">

    <!-- ===== MOBILE/TABLET (< lg): new style ===== -->
    <div class="w-full lg:hidden">
      <section class="px-4 bg-white md:px-6 py-section">
        <div class="mx-auto max-w-7xl">
          <div class="grid grid-cols-1 gap-8">

            <!-- Left Column -->
            <div>
              <!-- Header -->
              <div class="mb-8">
                <?php if ($heading_text): ?>
                  <<?php echo tag_escape($heading_tag); ?>
                    class="mb-8 font-semibold text-h2 md:text-h1 font-heading text-dark-bg"
                    style="color: <?php echo esc_attr($heading_color); ?>;">
                    <?php echo esc_html($heading_text); ?>
                  </<?php echo tag_escape($heading_tag); ?>>
                <?php endif; ?>

                <div class="mb-8 w-10 h-px"
                     style="background-color: <?php echo esc_attr($divider_color); ?>;"></div>

                <?php if ($description): ?>
                  <p class="text-base font-medium leading-7 text-secondary-coral"
                     style="color: <?php echo esc_attr($desc_color); ?>;">
                    <?php echo wp_kses_post($description); ?>
                  </p>
                <?php endif; ?>
              </div>

              <!-- Image -->
              <?php if ($img_url): ?>
                <img
                  src="<?php echo esc_url($img_url); ?>"
                  alt="<?php echo esc_attr($img_alt); ?>"
                  title="<?php echo esc_attr($img_title); ?>"
                  class="w-full h-[212px] md:h-[280px] object-cover rounded-card mb-8"
                />
              <?php endif; ?>

              <!-- Buttons -->
              <div class="flex gap-4 items-center">
                <?php if (!empty($primary_cta['url'])): ?>
                  <a href="<?php echo esc_url($primary_cta['url']); ?>"
                     target="<?php echo esc_attr($primary_cta['target'] ?: '_self'); ?>"
                     class="px-4 py-2 text-sm font-medium text-white transition-colors bg-primary-blue-dark rounded-card hover:bg-opacity-90">
                    <?php echo esc_html($primary_cta['title'] ?: 'Careers'); ?>
                  </a>
                <?php endif; ?>

                <?php if (!empty($secondary_cta['url'])): ?>
                  <a href="<?php echo esc_url($secondary_cta['url']); ?>"
                     target="<?php echo esc_attr($secondary_cta['target'] ?: '_self'); ?>"
                     class="px-4 py-2 text-sm font-medium border transition-colors border-primary-blue-dark text-primary-blue-dark2 rounded-card hover:bg-gray-50">
                    <?php echo esc_html($secondary_cta['title'] ?: 'About us'); ?>
                  </a>
                <?php endif; ?>
              </div>
            </div>

            <!-- Key Stats -->
            <?php if (!empty($key_points)): ?>
              <div class="flex flex-col gap-4 justify-center">
                <?php foreach ($key_points as $i => $kp):
                  $val    = isset($kp['value']) ? (float) $kp['value'] : 0;
                  $suffix = isset($kp['suffix']) ? (string) $kp['suffix'] : '';
                  $title  = isset($kp['title']) ? (string) $kp['title'] : '';
                  $text   = isset($kp['text'])  ? (string) $kp['text']  : '';
                ?>
                  <!-- Stat -->
                  <div class="flex gap-3 items-start" data-counter data-target="<?php echo esc_attr($val); ?>" data-suffix="<?php echo esc_attr($suffix); ?>">
                    <div class="flex-shrink-0 w-16 h-16">
                      <p class="font-semibold text-h2 font-heading text-primary-blue-dark"
                         style="color: <?php echo esc_attr($value_color); ?>;">
                        <span class="counter-value"><?php echo esc_html((string)$val . $suffix); ?></span>
                      </p>
                    </div>
                    <div class="flex-1">
                      <?php if ($title): ?>
                        <h3 class="mb-3 font-semibold text-h4 font-heading text-dark-bg"
                            style="color: <?php echo esc_attr($title_color); ?>;">
                          <?php echo esc_html($title); ?>
                        </h3>
                      <?php endif; ?>
                      <?php if ($text): ?>
                        <p class="text-sm font-medium leading-5 text-secondary-coral"
                           style="color: <?php echo esc_attr($text_color); ?>;">
                          <?php echo esc_html($text); ?>
                        </p>
                      <?php endif; ?>
                    </div>
                  </div>

                  <?php if ($i < count($key_points) - 1): ?>
                    <div class="w-10 h-px" style="background-color: <?php echo esc_attr($divider_color); ?>;"></div>
                  <?php endif; ?>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

          </div>
        </div>
      </section>
    </div>

    <!-- ===== DESKTOP (≥ lg): keep your current layout ===== -->
    <div class="hidden w-full lg:block">
      <!-- Main Content Container -->
      <div class="flex justify-between items-center w-full max-w-[1018px]">
        <!-- Left Content -->
        <div class="flex flex-col gap-8 items-start">

          <!-- Header -->
          <header class="flex flex-col gap-8 justify-center items-start">
            <?php if ($heading_text) : ?>
              <<?php echo tag_escape($heading_tag); ?>
                class="text-3xl font-semibold tracking-tight leading-9"
                style="color: <?php echo esc_attr($heading_color); ?>;">
                <?php echo esc_html($heading_text); ?>
              </<?php echo tag_escape($heading_tag); ?>>
            <?php endif; ?>

            <div class="w-10 h-1 rounded"
                 style="background-color: <?php echo esc_attr($divider_color); ?>;"
                 aria-hidden="true"></div>
          </header>

          <!-- Description -->
          <?php if ($description) : ?>
            <div class="text-base font-medium leading-7 w-full max-w-[467px] wp_editor"
                 style="color: <?php echo esc_attr($desc_color); ?>;">
              <?php echo wp_kses_post($description); ?>
            </div>
          <?php endif; ?>

          <!-- Buttons -->
          <div class="flex gap-4 items-center">
            <?php if (!empty($primary_cta['url'])) : ?>
              <a href="<?php echo esc_url($primary_cta['url']); ?>"
                 target="<?php echo esc_attr($primary_cta['target'] ?: '_self'); ?>"
                 class="flex gap-2.5 justify-center items-center px-4 py-2 bg-sky-900 rounded btn"
                 aria-label="<?php echo esc_attr($primary_cta['title'] ?: 'Careers'); ?>">
                <span class="text-sm font-medium leading-6 text-white">
                  <?php echo esc_html($primary_cta['title'] ?: 'Careers'); ?>
                </span>
              </a>
            <?php endif; ?>

            <?php if (!empty($secondary_cta['url'])) : ?>
              <a href="<?php echo esc_url($secondary_cta['url']); ?>"
                 target="<?php echo esc_attr($secondary_cta['target'] ?: '_self'); ?>"
                 class="flex gap-2.5 justify-center items-center px-4 py-2 rounded border border-sky-900 border-solid btn"
                 aria-label="<?php echo esc_attr($secondary_cta['title'] ?: 'About us'); ?>">
                <span class="text-sm font-medium leading-6 text-teal-950">
                  <?php echo esc_html($secondary_cta['title'] ?: 'About us'); ?>
                </span>
              </a>
            <?php endif; ?>
          </div>
        </div>

        <!-- Right Image -->
        <?php if ($img_url) : ?>
          <div class="flex shrink-0 justify-center items-center rounded-md h-[312px] w-[518px]">
            <img src="<?php echo esc_url($img_url); ?>"
                 alt="<?php echo esc_attr($img_alt); ?>"
                 title="<?php echo esc_attr($img_title); ?>"
                 class="object-cover w-full h-full rounded-md">
          </div>
        <?php endif; ?>
      </div>

      <!-- Key Points (Counters) -->
      <?php if (!empty($key_points)) : ?>
        <div class="flex justify-between items-center w-full max-w-[1018px] mt-10">
          <?php foreach ($key_points as $i => $kp) :
            $val    = isset($kp['value']) ? (float) $kp['value'] : 0;
            $suffix = isset($kp['suffix']) ? (string) $kp['suffix'] : '';
            $title  = isset($kp['title']) ? (string) $kp['title'] : '';
            $text   = isset($kp['text'])  ? (string) $kp['text']  : '';
          ?>
            <div class="flex flex-col gap-3 items-start"
                 data-counter data-target="<?php echo esc_attr($val); ?>" data-suffix="<?php echo esc_attr($suffix); ?>">
              <div class="flex gap-2.5 items-center">
                <span class="text-3xl font-semibold tracking-tight leading-9 counter-value"
                      style="color: <?php echo esc_attr($value_color); ?>;"
                      aria-live="polite">
                  <?php echo esc_html((string)$val . $suffix); ?>
                </span>
              </div>

              <?php if ($title): ?>
                <h3 class="text-xl font-semibold tracking-normal leading-7"
                    style="color: <?php echo esc_attr($title_color); ?>;">
                  <?php echo esc_html($title); ?>
                </h3>
              <?php endif; ?>

              <?php if ($text): ?>
                <p class="text-sm font-medium leading-5 w-[230px]"
                   style="color: <?php echo esc_attr($text_color); ?>;">
                  <?php echo esc_html($text); ?>
                </p>
              <?php endif; ?>
            </div>

            <?php if ($i < count($key_points) - 1) : ?>
              <div class="w-px h-10"
                   style="background-color: <?php echo esc_attr($divider_color); ?>;"
                   aria-hidden="true"></div>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

  </div>
</section>


<?php if (!empty($key_points)) : ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var root = document.getElementById('<?php echo esc_js($section_id); ?>');
    if (!root) return;

    var items = root.querySelectorAll('[data-counter]');
    if (!items.length) return;

    // Fallback: no IntersectionObserver -> just show final values
    if (!('IntersectionObserver' in window)) {
        items.forEach(function (el) {
            var target = parseFloat(el.getAttribute('data-target') || '0');
            var suffix = el.getAttribute('data-suffix') || '';
            var span   = el.querySelector('.counter-value');
            if (span) {
                span.textContent = target + suffix;
            }
        });
        return;
    }

    var observer = new IntersectionObserver(function (entries, obs) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) {
                return;
            }

            var el    = entry.target;
            var span  = el.querySelector('.counter-value');
            if (!span) {
                obs.unobserve(el);
                return;
            }

            var target = parseFloat(el.getAttribute('data-target') || '0');
            var suffix = el.getAttribute('data-suffix') || '';
            var duration = 2000;
            var startTime = performance.now();
            var startVal = 0;

            function step(now) {
                var progress = Math.min(1, (now - startTime) / duration);
                var value = Math.round(startVal + (target - startVal) * progress);
                span.textContent = value + suffix;
                if (progress < 1) {
                    requestAnimationFrame(step);
                }
            }

            requestAnimationFrame(step);
            obs.unobserve(el);
        });
    }, { threshold: 0.3 });

    items.forEach(function (el) {
        observer.observe(el);
    });
});
</script>
<?php endif; ?>
