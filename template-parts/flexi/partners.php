<?php
/**
 * Partners (Flexi Block)
 */

$section_id       = 'partners-' . ( function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid() );

$heading_tag      = get_sub_field('heading_tag') ?: 'h2';
$heading_text     = get_sub_field('heading_text') ?: '';
$heading_color    = get_sub_field('heading_color') ?: '#1e293b';
$partners         = get_sub_field('partners');
$background_color = get_sub_field('background_color') ?: '#FFFFFF';
$show_card_style  = (bool) get_sub_field('show_card_style');

// Build heading tag safely
$allowed_tags = ['h1','h2','h3','h4','h5','h6','span','p'];
if (!in_array($heading_tag, $allowed_tags, true)) {
    $heading_tag = 'h2';
}

// Card style for logos
$card_base = $show_card_style
    ? 'flex items-center justify-center p-4 bg-white border border-neutral-200 rounded-lg shadow-sm'
    : 'flex items-center justify-center bg-transparent';

// Match the previous academic logo strip: fixed height, auto width,
// object-contain, no overflow clipping.
$logo_img_class = 'block h-12 w-auto max-w-[14rem] object-contain';
$logo_link_class = 'inline-flex items-center justify-center focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-highlight-primary';

?>

<section id="<?php echo esc_attr($section_id); ?>"
         data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
         class="flex relative"
         style="background-color: <?php echo esc_attr($background_color); ?>;">
    <div class="flex flex-col items-center mx-auto w-full max-w-container_md p py-12 mob:py-[6rem] max-lg:px-5">

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
                    <div class="hidden flex-nowrap gap-6 justify-center items-center sm:flex md:gap-8 lg:justify-end lg:gap-10">
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
                                <?php if ($has_link) : ?>
                                    <a href="<?php echo esc_url($link['url']); ?>"
                                       target="<?php echo esc_attr($link['target'] ?: '_self'); ?>"
                                       rel="<?php echo ($link['target'] ?? '') === '_blank' ? 'noopener noreferrer' : ''; ?>"
                                       class="<?php echo esc_attr($logo_link_class); ?>"
                                       aria-label="<?php echo esc_attr($logo_title); ?>">
                                        <img src="<?php echo esc_url($logo_url); ?>"
                                             alt="<?php echo esc_attr($logo_alt); ?>"
                                             class="<?php echo esc_attr($logo_img_class); ?>" />
                                    </a>
                                <?php else : ?>
                                    <img src="<?php echo esc_url($logo_url); ?>"
                                         alt="<?php echo esc_attr($logo_alt); ?>"
                                         class="<?php echo esc_attr($logo_img_class); ?>" />
                                <?php endif; ?>
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
                                    <div class="<?php echo esc_attr($card_base); ?> min-h-12">
                                        <?php if ($has_link) : ?>
                                            <a href="<?php echo esc_url($link['url']); ?>"
                                               target="<?php echo esc_attr($link['target'] ?: '_self'); ?>"
                                               rel="<?php echo ($link['target'] ?? '') === '_blank' ? 'noopener noreferrer' : ''; ?>"
                                               class="<?php echo esc_attr($logo_link_class); ?>"
                                               aria-label="<?php echo esc_attr($logo_title); ?>">
                                                <img src="<?php echo esc_url($logo_url); ?>"
                                                     alt="<?php echo esc_attr($logo_alt); ?>"
                                                     class="<?php echo esc_attr($logo_img_class); ?>" />
                                            </a>
                                        <?php else : ?>
                                            <img src="<?php echo esc_url($logo_url); ?>"
                                                 alt="<?php echo esc_attr($logo_alt); ?>"
                                                 class="<?php echo esc_attr($logo_img_class); ?>" />
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
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
