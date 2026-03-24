<?php
/**
 * Hero Slider (Flexi Block) – Desktop + Mobile Layouts
 */

if (!defined('ABSPATH')) {
    exit;
}

$section_id = 'hero-slider-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());

// Background color
$background_color = get_sub_field('background_color');
if ($background_color === '' || $background_color === null) {
    $background_color = '#C6ECF4';
}

// Slides
$slides = array();
if (have_rows('slides')) {
    while (have_rows('slides')) {
        the_row();

        $tag     = get_sub_field('heading_tag');
        $text    = get_sub_field('heading_text');
        $desc    = get_sub_field('description');
        $pbtn    = get_sub_field('primary_button');
        $sbtn    = get_sub_field('secondary_button');
        $img     = get_sub_field('hero_image');
        $img_mob = get_sub_field('mobile_optimised_image');

        if (!$tag) {
            $tag = 'h1';
        }
        $allowed_tags = array('h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p');
        if (!in_array($tag, $allowed_tags, true)) {
            $tag = 'h1';
        }

        if (!$text) {
            $text = '';
        }

        // Desktop image (media alt/title only)
        $hero_img_url   = isset($img['url']) && $img['url'] !== '' ? $img['url'] : '';
        $hero_img_alt   = isset($img['alt']) && $img['alt'] !== '' ? $img['alt'] : 'Hero image';
        $hero_img_title = isset($img['title']) && $img['title'] !== '' ? $img['title'] : $hero_img_alt;

        // Mobile image (fallback to hero image)
        $mobile_source  = !empty($img_mob) ? $img_mob : $img;
        $mob_img_url    = isset($mobile_source['url']) && $mobile_source['url'] !== '' ? $mobile_source['url'] : $hero_img_url;
        $mob_img_alt    = isset($mobile_source['alt']) && $mobile_source['alt'] !== '' ? $mobile_source['alt'] : $hero_img_alt;
        $mob_img_title  = isset($mobile_source['title']) && $mobile_source['title'] !== '' ? $mobile_source['title'] : $mob_img_alt;

        $slides[] = array(
            'tag'  => $tag,
            'text' => $text,
            'desc' => $desc,
            'pbtn' => $pbtn,
            'sbtn' => $sbtn,
            'desktop_img' => array(
                'url'   => $hero_img_url,
                'alt'   => $hero_img_alt,
                'title' => $hero_img_title,
            ),
            'mobile_img' => array(
                'url'   => $mob_img_url,
                'alt'   => $mob_img_alt,
                'title' => $mob_img_title,
            ),
        );
    }
}

$is_slider = count($slides) > 1;
?>

<style>
#<?php echo esc_attr($section_id); ?> .slick-slide {
    display: flex !important;
}
</style>

<section id="<?php echo esc_attr($section_id); ?>"
         class="flex overflow-hidden relative"
         style="background-color: <?php echo esc_attr($background_color); ?>;"
         role="banner"
         aria-label="Hero section">

    <div class="flex flex-col items-center mx-auto w-full max-w-container">

        <?php if ($is_slider) { ?>
            <div class="w-full hero-slider-container" data-slider-id="<?php echo esc_attr($section_id); ?>">
        <?php } ?>

        <?php foreach ($slides as $i => $sl) {
            $heading_id = $section_id . '-heading-' . $i;
            ?>
            <!-- SLIDE -->
            <div class="hero-slide flex flex-col items-stretch w-full min-h-[520px] lg:min-h-[600px] lg:flex-row">

                <!-- MOBILE IMAGE (TOP, < lg) -->
                <?php if (!empty($sl['mobile_img']['url'])) { ?>
                    <div class="mb-0 w-full lg:hidden">
                        <img src="<?php echo esc_url($sl['mobile_img']['url']); ?>"
                             alt="<?php echo esc_attr($sl['mobile_img']['alt']); ?>"
                             title="<?php echo esc_attr($sl['mobile_img']['title']); ?>"
                             class="object-cover w-full h-auto rounded-b-xl"
                             loading="<?php echo $i === 0 ? 'eager' : 'lazy'; ?>">
                    </div>
                <?php } ?>

                <!-- TEXT PANEL (BOTTOM ON MOBILE, LEFT ON DESKTOP) -->
                <div class="flex flex-col flex-1 order-2 justify-center px-4 pt-6 pb-8 lg:pt-0 lg:pb-0 lg:order-1 lg:max-w-[500px] relative lg:left-[5rem]">

                    <?php if ($is_slider) { ?>
                        <!-- MOBILE-ONLY CONTROLS: below image, above text -->
                        <div class="flex gap-6 justify-center items-center mb-4 lg:hidden">

                            <!-- Mobile Prev -->
                            <button
                                class="flex justify-center items-center w-8 h-8 rounded-full border transition-colors slider-prev hover:bg-neutral-100"
                                style="background-color:#ffffff;border:1px solid #7ED0E0;"
                                aria-label="Previous slide">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 16 16">
                                    <path d="M12.7 8H3.3M8 3.3L3.3 8l4.7 4.7"
                                          stroke="currentColor" stroke-width="1.5"
                                          stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>

                            <!-- Dots (mobile) -->
                            <div class="flex gap-2">
                                <?php for ($d = 0; $d < count($slides); $d++) {
                                    $is_active = $d === 0;
                                    $dot_color = $is_active ? '#0f172a' : '#7ED0E0'; // active vs primary
                                    ?>
                                    <button
                                        class="w-3 h-3 rounded-full transition-colors slider-dot"
                                        data-slide="<?php echo esc_attr($d); ?>"
                                        aria-label="<?php echo esc_attr('Go to slide ' . ($d + 1)); ?>"
                                        aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
                                        style="background-color: <?php echo esc_attr($dot_color); ?>;">
                                    </button>
                                <?php } ?>
                            </div>

                            <!-- Mobile Next -->
                            <button
                                class="flex justify-center items-center w-8 h-8 rounded-full border transition-colors slider-next hover:bg-neutral-100"
                                style="background-color:#ffffff;border:1px solid #7ED0E0;"
                                aria-label="Next slide">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 16 16">
                                    <path d="M3.3 8h9.4M8 12.7L12.7 8 8 3.3"
                                          stroke="currentColor" stroke-width="1.5"
                                          stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>
                    <?php } ?>

                    <?php if (!empty($sl['text'])) { ?>
                        <<?php echo esc_attr($sl['tag']); ?>
                            class="mb-4 font-bold leading-tight text-secondary text-h1-mobile lg:text-h1 lg:text-slate-900"
                            id="<?php echo esc_attr($heading_id); ?>">
                            <?php echo esc_html($sl['text']); ?>
                        </<?php echo esc_attr($sl['tag']); ?>>
                    <?php } ?>

                    <?php if (!empty($sl['desc'])) { ?>
                        <div class="mb-6 text-large text-slate-900 md:text-lead wp_editor">
                            <?php echo wp_kses_post($sl['desc']); ?>
                        </div>
                    <?php } ?>

                    <!-- BUTTONS -->
                    <div class="flex flex-row flex-wrap gap-4">
                        <?php
                        if (!empty($sl['pbtn']['url'])) {
                            $primary_class        = 'hero-slider-primary-btn-' . $section_id . '-' . $i;
                            $primary_hover_bg     = '#024B79'; // dark primary
                            $primary_hover_text   = '#FFFFFF';
                            $primary_hover_border = '#024B79';
                            ?>
                            <a href="<?php echo esc_url($sl['pbtn']['url']); ?>"
                               target="<?php echo esc_attr($sl['pbtn']['target'] ? $sl['pbtn']['target'] : '_self'); ?>"
                               aria-label="<?php echo esc_attr($sl['pbtn']['title'] ? $sl['pbtn']['title'] : 'Primary action'); ?>"
                               class="btn <?php echo esc_attr($primary_class); ?> flex h-9 items-center justify-center gap-2.5 px-3 rounded bg-primary-dark text-white text-sm font-medium leading-6 border border-transparent transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-primary-dark">
                                <?php echo esc_html($sl['pbtn']['title']); ?>
                            </a>
                            <style>
                                .<?php echo esc_attr($primary_class); ?>:hover,
                                .<?php echo esc_attr($primary_class); ?>:focus {
                                    background-color: <?php echo esc_attr($primary_hover_bg); ?> !important;
                                    color: <?php echo esc_attr($primary_hover_text); ?> !important;
                                    border-color: <?php echo esc_attr($primary_hover_border); ?> !important;
                                    outline: 2px solid <?php echo esc_attr($primary_hover_border); ?>;
                                    outline-offset: 2px;
                                }
                                .<?php echo esc_attr($primary_class); ?>:hover svg path,
                                .<?php echo esc_attr($primary_class); ?>:focus svg path {
                                    stroke: <?php echo esc_attr($primary_hover_text); ?>;
                                }
                            </style>
                        <?php
                        }

                        if (!empty($sl['sbtn']['url'])) {
                            $secondary_class        = 'hero-slider-secondary-btn-' . $section_id . '-' . $i;
                            $secondary_hover_bg     = '#024B79';
                            $secondary_hover_text   = '#FFFFFF';
                            $secondary_hover_border = '#024B79';
                            ?>
                            <a href="<?php echo esc_url($sl['sbtn']['url']); ?>"
                               target="<?php echo esc_attr($sl['sbtn']['target'] ? $sl['sbtn']['target'] : '_self'); ?>"
                               aria-label="<?php echo esc_attr($sl['sbtn']['title'] ? $sl['sbtn']['title'] : 'Secondary action'); ?>"
                               class="btn <?php echo esc_attr($secondary_class); ?> flex h-9 items-center justify-center gap-2.5 px-3 rounded border border-primary-dark text-primary-dark bg-transparent text-sm font-medium leading-6 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-primary-dark lg:min-w-[180px]">
                                <?php echo esc_html($sl['sbtn']['title']); ?>
                            </a>
                            <style>
                                .<?php echo esc_attr($secondary_class); ?>:hover,
                                .<?php echo esc_attr($secondary_class); ?>:focus {
                                    background-color: <?php echo esc_attr($secondary_hover_bg); ?> !important;
                                    color: <?php echo esc_attr($secondary_hover_text); ?> !important;
                                    border-color: <?php echo esc_attr($secondary_hover_border); ?> !important;
                                    outline: 2px solid <?php echo esc_attr($secondary_hover_border); ?>;
                                    outline-offset: 2px;
                                }
                                .<?php echo esc_attr($secondary_class); ?>:hover svg path,
                                .<?php echo esc_attr($secondary_class); ?>:focus svg path {
                                    stroke: <?php echo esc_attr($secondary_hover_text); ?>;
                                }
                            </style>
                        <?php
                        }
                        ?>
                    </div>
                </div>

                <!-- DESKTOP IMAGE (RIGHT, >= lg) + GRADIENT OVERLAY -->
                <?php if (!empty($sl['desktop_img']['url'])) { ?>
                    <div class="hidden relative flex-1 order-1 justify-end h-full lg:flex lg:order-2">
                        <img src="<?php echo esc_url($sl['desktop_img']['url']); ?>"
                             alt="<?php echo esc_attr($sl['desktop_img']['alt']); ?>"
                             title="<?php echo esc_attr($sl['desktop_img']['title']); ?>"
                             class="object-contain h-full max-h-[600px]"
                             loading="<?php echo $i === 0 ? 'eager' : 'lazy'; ?>">

                        <!-- Right-side gradient fade on wide screens -->
                        <div class="hidden absolute inset-y-0 right-0 w-1/3 pointer-events-none xl:block"
                             style="background: linear-gradient(to right, transparent, <?php echo esc_attr($background_color); ?>);">
                        </div>
                    </div>
                <?php } ?>

            </div>
        <?php } ?>

        <?php if ($is_slider) { ?>
            </div>

            <!-- DESKTOP CONTROLS (absolute, centered, lg+) -->
            <div class="hidden absolute bottom-8 left-1/2 z-20 flex-col gap-4 items-center -translate-x-1/2 lg:flex">

                <div class="flex gap-6 items-center">
                    <!-- Desktop Prev -->
                    <button
                        class="flex justify-center items-center w-8 h-8 rounded-full border transition-colors slider-prev hover:bg-neutral-100"
                        style="background-color:#ffffff;border:1px solid #7ED0E0;"
                        aria-label="Previous slide">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 16 16">
                            <path d="M12.7 8H3.3M8 3.3L3.3 8l4.7 4.7"
                                  stroke="currentColor" stroke-width="1.5"
                                  stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>

                    <!-- Dots (desktop) -->
                    <div class="flex gap-3">
                        <?php
                        for ($d = 0; $d < count($slides); $d++) {
                            $is_active = $d === 0;
                            $dot_color = $is_active ? '#0f172a' : '#7ED0E0';
                            ?>
                            <button
                                class="w-3 h-3 rounded-full transition-colors slider-dot"
                                data-slide="<?php echo esc_attr($d); ?>"
                                aria-label="<?php echo esc_attr('Go to slide ' . ($d + 1)); ?>"
                                aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
                                style="background-color: <?php echo esc_attr($dot_color); ?>;">
                            </button>
                        <?php } ?>
                    </div>

                    <!-- Desktop Next -->
                    <button
                        class="flex justify-center items-center w-8 h-8 rounded-full border transition-colors slider-next hover:bg-neutral-100"
                        style="background-color:#ffffff;border:1px solid #7ED0E0;"
                        aria-label="Next slide">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 16 16">
                            <path d="M3.3 8h9.4M8 12.7L12.7 8 8 3.3"
                                  stroke="currentColor" stroke-width="1.5"
                                  stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                </div>

            </div>
        <?php } ?>

    </div>
</section>

<?php if ($is_slider) { ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var id        = '<?php echo esc_js($section_id); ?>';
    var selector  = '[data-slider-id="' + id + '"]';
    var container = document.querySelector(selector);

    if (!container) return;
    if (typeof jQuery === 'undefined' || typeof jQuery.fn.slick === 'undefined') return;

    var $ = jQuery;

    $(container).slick({
        arrows: false,
        dots: false,
        infinite: true,
        fade: true,
        speed: 600,
        autoplay: true,
        autoplaySpeed: 5500,
        adaptiveHeight: false
    });

    var dots = document.querySelectorAll('#' + id + ' .slider-dot');

    // Click to go to slide
    dots.forEach(function(dot, index) {
        dot.addEventListener('click', function() {
            $(container).slick('slickGoTo', index);
        });
    });

    // Update indicator colors (active vs inactive)
    $(container).on('beforeChange', function(e, slick, cur, next) {
        dots.forEach(function(d, i) {
            if (i === next) {
                d.style.backgroundColor = '#0f172a';   // active (dark)
                d.setAttribute('aria-selected', 'true');
            } else {
                d.style.backgroundColor = '#7ED0E0';   // inactive (primary)
                d.setAttribute('aria-selected', 'false');
            }
        });
    });

    // Multiple prev/next buttons (mobile + desktop)
    var prevBtns = document.querySelectorAll('#' + id + ' .slider-prev');
    var nextBtns = document.querySelectorAll('#' + id + ' .slider-next');

    prevBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            $(container).slick('slickPrev');
        });
    });

    nextBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            $(container).slick('slickNext');
        });
    });
});
</script>
<?php } ?>
