<?php
/**
 * Flexi Block: Counters Statistics
 * - Tailwind-only styling
 * - Uses get_sub_field()
 * - Vanilla JS counter animation with Intersection Observer
 */

if (!defined('ABSPATH')) {
    exit;
}

$section_id = 'counters-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());

// Content fields
$background_color = get_sub_field('background_color') ?: '#024B79';
$counter_items = get_sub_field('counter_items') ?: [];
$total_counter_items = count($counter_items);

// Layout padding classes
$padding_classes = [];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size = get_sub_field('screen_size');
        $padding_top = get_sub_field('padding_top');
        $padding_bottom = get_sub_field('padding_bottom');

        if ($screen_size && $padding_top !== '' && $padding_top !== null) {
            $padding_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
        }
        if ($screen_size && $padding_bottom !== '' && $padding_bottom !== null) {
            $padding_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
        }
    }
}
?>

<section id="<?php echo esc_attr($section_id); ?>"
         data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
         class="relative flex overflow-hidden bg-[#024B79] <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
         role="region"
         aria-label="Statistics and Key Metrics">

    <div class="flex flex-col items-center pt-20 pb-20 mx-auto w-full max-w-[1018px] max-lg:px-5">

        <?php if (!empty($counter_items)): ?>
            <div class="grid grid-cols-3 gap-16 mx-auto max-w-[1200px] w-full max-md:grid-cols-1 max-md:gap-6"
                 role="list"
                 aria-label="Key statistics">

                <?php foreach ($counter_items as $index => $item):
                    $value = isset($item['value']) ? (float) $item['value'] : 0;
                    $suffix = isset($item['suffix']) ? (string) $item['suffix'] : '';
                    $title = isset($item['title']) ? (string) $item['title'] : '';
                    $description = isset($item['description']) ? (string) $item['description'] : '';
                    $display_value = $value . $suffix;
                    $article_classes = 'flex flex-col max-md:flex-row max-md:items-center max-md:gap-4';
                    if ($index > 0) {
                        $article_classes .= ' relative md:pl-8';
                    }
                ?>
                    <article class="<?php echo esc_attr($article_classes); ?>"
                             role="listitem"
                             data-counter
                             data-target="<?php echo esc_attr($value); ?>"
                             data-suffix="<?php echo esc_attr($suffix); ?>"
                             aria-label="Statistic: <?php echo esc_attr($display_value . ' ' . $title); ?>">

                        <?php if ($index > 0): ?>
                            <div class="hidden absolute left-0 top-1/2 w-px h-10 -translate-y-1/2 md:block bg-white/30"
                                 role="presentation"
                                 aria-hidden="true"></div>
                        <?php endif; ?>

                        <div class="max-md:min-w-[56px]">
                            <div class="mb-5 max-md:mb-0 text-[30px] font-semibold not-italic leading-[36px] tracking-[-0.225px] text-[#7ED0E0]"
                                 aria-live="polite"
                                 aria-atomic="true">
                                <span class="counter-value"
                                      aria-label="<?php echo esc_attr($display_value); ?>">
                                    <?php echo esc_html($display_value); ?>
                                </span>
                            </div>

                            <?php if ($index < $total_counter_items - 1): ?>
                                <div class="hidden mt-3 w-10 h-px max-md:block bg-white/30"
                                     role="presentation"
                                     aria-hidden="true"></div>
                            <?php endif; ?>
                        </div>

                        <div class="max-md:flex-1">

                        <?php if ($title): ?>
                            <h3 class="mb-3 text-[20px] font-semibold not-italic leading-[28px] tracking-[-0.1px] text-white">
                                <?php echo esc_html($title); ?>
                            </h3>
                        <?php endif; ?>

                        <?php if ($description): ?>
                            <p class="text-[14px] font-medium not-italic leading-[20px] text-[#F8FAFC]">
                                <?php echo esc_html($description); ?>
                            </p>
                        <?php endif; ?>

                        </div>

                    </article>
                <?php endforeach; ?>

            </div>
        <?php endif; ?>

    </div>
</section>

<?php if (!empty($counter_items)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var root = document.getElementById('<?php echo esc_js($section_id); ?>');
    if (!root) return;

    var counterElements = root.querySelectorAll('[data-counter]');
    if (!counterElements.length) return;

    // Fallback for browsers without IntersectionObserver
    if (!('IntersectionObserver' in window)) {
        counterElements.forEach(function(element) {
            var target = parseFloat(element.getAttribute('data-target') || '0');
            var suffix = element.getAttribute('data-suffix') || '';
            var valueSpan = element.querySelector('.counter-value');
            if (valueSpan) {
                valueSpan.textContent = target + suffix;
                valueSpan.setAttribute('aria-label', target + suffix);
            }
        });
        return;
    }

    // Intersection Observer for counter animation
    var observer = new IntersectionObserver(function(entries, obs) {
        entries.forEach(function(entry) {
            if (!entry.isIntersecting) return;

            var element = entry.target;
            var valueSpan = element.querySelector('.counter-value');
            if (!valueSpan) {
                obs.unobserve(element);
                return;
            }

            var target = parseFloat(element.getAttribute('data-target') || '0');
            var suffix = element.getAttribute('data-suffix') || '';
            var duration = 2000; // 2 seconds
            var startTime = performance.now();
            var startValue = 0;

            function animateCounter(currentTime) {
                var elapsed = currentTime - startTime;
                var progress = Math.min(elapsed / duration, 1);

                // Easing function for smooth animation
                var easedProgress = progress * (2 - progress);
                var currentValue = Math.round(startValue + (target - startValue) * easedProgress);

                var displayValue = currentValue + suffix;
                valueSpan.textContent = displayValue;
                valueSpan.setAttribute('aria-label', displayValue);

                if (progress < 1) {
                    requestAnimationFrame(animateCounter);
                } else {
                    // Ensure final value is exact
                    var finalValue = target + suffix;
                    valueSpan.textContent = finalValue;
                    valueSpan.setAttribute('aria-label', finalValue);
                }
            }

            requestAnimationFrame(animateCounter);
            obs.unobserve(element);
        });
    }, {
        threshold: 0.3,
        rootMargin: '0px 0px -50px 0px'
    });

    counterElements.forEach(function(element) {
        observer.observe(element);
    });
});
</script>
<?php endif; ?>
