<?php
/**
 * Partners (Flexi Block)
 */

$section_id       = 'partners-' . ( function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid() );

$heading_tag      = get_sub_field('heading_tag') ?: 'h2';
$heading_text     = get_sub_field('heading_text') ?: '';
$partners         = get_sub_field('partners');
$background_color = get_sub_field('background_color') ?: '#FFFFFF';
$heading_color    = get_sub_field('heading_color') ?: '#1e293b';
$show_card_style  = (bool) get_sub_field('show_card_style');

$padding_classes = [];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
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

// Build heading tag safely
$allowed_tags = ['h1','h2','h3','h4','h5','h6','span','p'];
if (!in_array($heading_tag, $allowed_tags, true)) {
    $heading_tag = 'h2';
}

// Card style for logos
$card_base = $show_card_style
    ? 'flex items-center justify-center p-4 bg-white border border-neutral-200 rounded-lg shadow-sm'
    : 'flex items-center justify-center bg-transparent';

// Logo container classes
$logo_container = 'h-10 flex items-center justify-center';

?>

<section id="<?php echo esc_attr($section_id); ?>"
         data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
         class="flex overflow-hidden relative"
         style="background-color: <?php echo esc_attr($background_color); ?>;">
    <div class="flex flex-col items-center mx-auto w-full max-w-container_md p py-12 mob:py-[6rem] max-lg:px-5 <?php echo esc_attr(implode(' ', $padding_classes)); ?>">

        <div class="flex flex-col gap-8 w-full lg:flex-row lg:items-center lg:justify-between lg:gap-10">

            <!-- Heading Section -->
            <div class="w-full lg:max-w-[295px]">
                <?php if (!empty($heading_text)) : ?>
                    <<?php echo tag_escape($heading_tag); ?>
                        class="text-[18px] mob:text-xl font-medium tracking-normal leading-7 text-left"
                        style="color: <?php echo esc_attr($heading_color); ?>;">
                        <?php echo esc_html($heading_text); ?>
                    </<?php echo tag_escape($heading_tag); ?>>
                <?php endif; ?>
            </div>

            <!-- Partners Logos Section -->
            <div class="flex-1">
                <?php if (!empty($partners) && is_array($partners)) : ?>

                    <!-- Desktop / tablet layout (≥ sm) -->
                    <div class="hidden flex-wrap gap-4 justify-center items-center sm:flex md:gap-6 lg:justify-end lg:gap-8">
                        <?php foreach ($partners as $row) :
                            $logo = $row['logo'] ?? null;
                            if (!$logo || empty($logo['url'])) {
                                continue;
                            }

                            $logo_url   = $logo['url'];
                            $logo_alt   = $logo['alt']   ?: 'Partner logo';
                            $logo_title = $logo['title'] ?: $logo_alt;

                            $link       = $row['link'] ?? null;
                            $has_link   = is_array($link) && !empty($link['url']);
                            ?>
                            <div class="<?php echo esc_attr($card_base); ?>">
                                <div class="<?php echo esc_attr($logo_container); ?>">
                                    <?php if ($has_link) : ?>
                                        <a href="<?php echo esc_url($link['url']); ?>"
                                           target="<?php echo esc_attr($link['target'] ?: '_self'); ?>"
                                           class="btn focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-highlight-primary"
                                           aria-label="<?php echo esc_attr($logo_title); ?>">
                                            <img src="<?php echo esc_url($logo_url); ?>"
                                                 alt="<?php echo esc_attr($logo_alt); ?>"
                                                 title="<?php echo esc_attr($logo_title); ?>"
                                                 class="object-contain w-auto h-full" />
                                        </a>
                                    <?php else : ?>
                                        <img src="<?php echo esc_url($logo_url); ?>"
                                             alt="<?php echo esc_attr($logo_alt); ?>"
                                             title="<?php echo esc_attr($logo_title); ?>"
                                             class="object-contain w-auto h-full" />
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Mobile Slick slider (< sm) -->
                    <div class="w-full sm:hidden">
                        <div class="partners-slider" data-partners-slider="<?php echo esc_attr($section_id); ?>">
                            <?php foreach ($partners as $row) :
                                $logo = $row['logo'] ?? null;
                                if (!$logo || empty($logo['url'])) {
                                    continue;
                                }

                                $logo_url   = $logo['url'];
                                $logo_alt   = $logo['alt']   ?: 'Partner logo';
                                $logo_title = $logo['title'] ?: $logo_alt;

                                $link       = $row['link'] ?? null;
                                $has_link   = is_array($link) && !empty($link['url']);
                                ?>
                                <div class="px-2">
                                    <div class="<?php echo esc_attr($card_base); ?>">
                                        <div class="<?php echo esc_attr($logo_container); ?>">
                                            <?php if ($has_link) : ?>
                                                <a href="<?php echo esc_url($link['url']); ?>"
                                                   target="<?php echo esc_attr($link['target'] ?: '_self'); ?>"
                                                   class="btn focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-highlight-primary"
                                                   aria-label="<?php echo esc_attr($logo_title); ?>">
                                                    <img src="<?php echo esc_url($logo_url); ?>"
                                                         alt="<?php echo esc_attr($logo_alt); ?>"
                                                         title="<?php echo esc_attr($logo_title); ?>"
                                                         class="object-contain w-auto h-full" />
                                                </a>
                                            <?php else : ?>
                                                <img src="<?php echo esc_url($logo_url); ?>"
                                                     alt="<?php echo esc_attr($logo_alt); ?>"
                                                     title="<?php echo esc_attr($logo_title); ?>"
                                                     class="object-contain w-auto h-full" />
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <!-- No arrows, no indicators: swipe / drag only -->
                    </div>

                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<?php if (!empty($partners) && is_array($partners)) : ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery === 'undefined' || typeof jQuery.fn.slick === 'undefined') {
        return;
    }

    var sliderSelector = '[data-partners-slider="<?php echo esc_js($section_id); ?>"]';
    var $ = jQuery;
    var $slider = $(sliderSelector);

    if (!$slider.length) {
        return;
    }

    function initPartnersSlider() {
        var isMobile = window.innerWidth < 640; // Tailwind sm breakpoint

        if (isMobile) {
            if (!$slider.hasClass('slick-initialized')) {
                $slider.slick({
                    arrows: false,
                    dots: false,
                    infinite: false,
                    slidesToShow: 2,
                    slidesToScroll: 1,
                    swipeToSlide: true,
                    adaptiveHeight: false
                });
            }
        } else {
            if ($slider.hasClass('slick-initialized')) {
                $slider.slick('unslick');
            }
        }
    }

    initPartnersSlider();
    window.addEventListener('resize', initPartnersSlider);
});
</script>
<?php endif; ?>
