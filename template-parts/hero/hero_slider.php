<?php
/**
 * Hero Slider (Flexi Block) – full-bleed photo with a content card.
 */

if (!defined('ABSPATH')) {
    exit;
}

$section_id = 'hero-slider-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());

// Card colour. The photograph is full-bleed behind the card.
$background_color = get_sub_field('background_color');
if ($background_color === '' || $background_color === null) {
    $background_color = '#C6ECF4';
}

$hero_phone = function_exists('get_field') ? get_field('topbar_phone_link', 'option') : null;
if (!is_array($hero_phone) || empty($hero_phone['url']) || empty($hero_phone['title'])) {
    $hero_phone = array(
        'url' => 'tel:+35312493200',
        'title' => '01 249 3200',
        'target' => '_self',
    );
}

// Slides
$slides = array();
if (have_rows('slides')) {
    while (have_rows('slides')) {
        the_row();

        $tag     = get_sub_field('heading_tag');
        $text    = get_sub_field('heading_text');
        $desc    = get_sub_field('description');
        $pbtn    = matrix_canonical_cta_link(get_sub_field('primary_button'));
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
#<?php echo esc_attr($section_id); ?> .hero-slider-container:not(.slick-initialized) .hero-slide:not(:first-child) {
    display: none !important;
}

#<?php echo esc_attr($section_id); ?> .hero-slider-container:not(.slick-initialized) {
    overflow: hidden;
}

#<?php echo esc_attr($section_id); ?> .hero-slider-container .slick-list {
    overflow: hidden !important;
}

#<?php echo esc_attr($section_id); ?> .slick-slide {
    display: flex !important;
}

/* Inactive fade slides still sit in the stacking order; keep them from covering active text. */
#<?php echo esc_attr($section_id); ?> .slick-slide:not(.slick-active) {
    pointer-events: none;
}

#<?php echo esc_attr($section_id); ?> .hero-slider-container .slick-track {
    display: block;
    gap: 0;
}

@media (min-width: 1100px) {
    /* Older banner files include a pale wash on the left. Shift those
       photos so the wash sits off-canvas, matching the first slide. */
    #<?php echo esc_attr($section_id); ?> .hero-slide-image.is-washed img {
        object-position: right center;
        transform: scale(1.5);
        transform-origin: right center;
    }
}

#<?php echo esc_attr($section_id); ?> .slider-dot {
    width: 8px;
    height: 8px;
    padding: 0;
    border: 0;
    border-radius: 999px;
    background-color: #7ED0E0;
    flex: none;
}

#<?php echo esc_attr($section_id); ?> .slider-dot[aria-current="true"] {
    width: 22px;
    background-color: #0f172a;
}

#<?php echo esc_attr($section_id); ?> .slider-pause .icon-play {
    display: none;
}

#<?php echo esc_attr($section_id); ?> .slider-pause.is-paused .icon-pause {
    display: none;
}

#<?php echo esc_attr($section_id); ?> .slider-pause.is-paused .icon-play {
    display: block;
}
</style>

<section id="<?php echo esc_attr($section_id); ?>"
         class="relative overflow-hidden"
         role="banner"
         aria-label="Hero section">

    <?php if ($is_slider) { ?>
        <div class="w-full hero-slider-container" data-slider-id="<?php echo esc_attr($section_id); ?>">
    <?php } ?>

    <?php foreach ($slides as $i => $sl) {
        $heading_id = $section_id . '-heading-' . $i;
        ?>
        <div class="hero-slide relative flex w-full flex-col">

            <?php if (!empty($sl['desktop_img']['url'])) { ?>
                <div class="hero-slide-image absolute inset-0 z-0 hidden lg:block<?php echo $i === 0 ? '' : ' is-washed'; ?>">
                    <img src="<?php echo esc_url($sl['desktop_img']['url']); ?>"
                         alt="<?php echo esc_attr($sl['desktop_img']['alt']); ?>"
                         title="<?php echo esc_attr($sl['desktop_img']['title']); ?>"
                         class="h-full w-full object-cover"
                         loading="<?php echo $i === 0 ? 'eager' : 'lazy'; ?>">
                </div>
            <?php } ?>

            <?php if (!empty($sl['mobile_img']['url'])) { ?>
                <div class="relative w-full lg:hidden">
                    <img src="<?php echo esc_url($sl['mobile_img']['url']); ?>"
                         alt="<?php echo esc_attr($sl['mobile_img']['alt']); ?>"
                         title="<?php echo esc_attr($sl['mobile_img']['title']); ?>"
                         class="h-auto w-full object-cover"
                         loading="<?php echo $i === 0 ? 'eager' : 'lazy'; ?>">
                </div>
            <?php } ?>

            <div class="relative z-10 mx-auto flex w-full max-w-container items-center px-4 py-8 lg:px-5 lg:py-10">
                <div class="hero-slide-text flex w-full flex-col rounded-2xl px-6 py-8 lg:max-w-[560px] lg:shrink-0 lg:px-10 lg:py-10"
                     style="background-color: <?php echo esc_attr($background_color); ?>;">

                    <?php if (empty($GLOBALS['matrix_hero_heading_css'])): $GLOBALS['matrix_hero_heading_css'] = true; ?>
                    <style>
                    .hero-slide-heading { line-height: 34px !important; }
.hero-slide-rule { display: block; width: 48px; height: 3px; margin-top: 16px; background: #08284B; }
                    @media (min-width: 1024px) {
                      .hero-slide-heading { font-size: 46px !important; line-height: 54px !important; letter-spacing: -0.55px !important; }
                    }
                    </style>
                    <?php endif; ?>

                    <?php if (!empty($sl['text'])) { ?>
                        <<?php echo esc_attr($sl['tag']); ?>
                            class="hero-slide-heading text-[#08284B] !text-[28px] !not-italic !font-bold !leading-[28px] !tracking-[-0.336px] lg:!text-[48px] lg:!leading-[48px] lg:!tracking-[-0.576px]"
                            style="color: #08284B;"
                            id="<?php echo esc_attr($heading_id); ?>">
                            <?php echo esc_html($sl['text']); ?>
                        </<?php echo esc_attr($sl['tag']); ?>>
                        <span class="hero-slide-rule mt-4 block" aria-hidden="true"></span>
                    <?php } ?>

                    <?php if (!empty($sl['desc'])) { ?>
                        <div class="mt-5 text-large text-[#08284B] md:text-lead wp_editor !text-[18px] !not-italic !font-normal !leading-[22.75px] !tracking-[-0.09px] !text-[#001F33] md:!text-[20px] md:!font-medium md:!leading-[28px] md:!tracking-[-0.1px]">
                            <?php echo matrix_kses_rich_text($sl['desc']); ?>
                        </div>
                    <?php } ?>

                    <div class="mt-6 flex flex-row flex-wrap gap-4">
                        <?php if (!empty($sl['pbtn']['url'])) { ?>
                            <a href="<?php echo esc_url($sl['pbtn']['url']); ?>"
                               target="<?php echo esc_attr($sl['pbtn']['target'] ? $sl['pbtn']['target'] : '_self'); ?>"
                               aria-label="<?php echo esc_attr($sl['pbtn']['title'] ? $sl['pbtn']['title'] : 'Primary action'); ?>"
                               class="btn flex h-[44px] items-center justify-center gap-2.5 rounded-md bg-primary-dark text-white border border-transparent px-8 font-primary !text-[14px] not-italic font-medium leading-6 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-primary-dark">
                                <?php echo esc_html($sl['pbtn']['title']); ?>
                            </a>
                        <?php } ?>

                        <?php if (!empty($sl['sbtn']['url'])) { ?>
                            <a href="<?php echo esc_url($sl['sbtn']['url']); ?>"
                               target="<?php echo esc_attr($sl['sbtn']['target'] ? $sl['sbtn']['target'] : '_self'); ?>"
                               aria-label="<?php echo esc_attr($sl['sbtn']['title'] ? $sl['sbtn']['title'] : 'Secondary action'); ?>"
                               class="btn flex h-[44px] items-center justify-center gap-2.5 rounded-md border border-primary-dark bg-transparent px-3 font-primary !text-[14px] not-italic font-medium leading-6 text-primary-dark transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-primary-dark lg:min-w-[180px]">
                                <?php echo esc_html($sl['sbtn']['title']); ?>
                            </a>
                        <?php } ?>
                    </div>

                    <p class="mt-5 flex items-center gap-2 text-sm font-medium leading-5 text-[#08284B]">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M6.5 3.5h2.2l1.2 3-1.6 1a12.5 12.5 0 0 0 6 6l1-1.6 3 1.2v2.2c0 .6-.4 1.1-1 1.2A15.5 15.5 0 0 1 5.3 4.5c.1-.6.6-1 1.2-1Z" stroke="#08284B" stroke-width="1.5" stroke-linejoin="round"/>
                        </svg>
                        <span>Or call us on
                            <a href="<?php echo esc_url($hero_phone['url']); ?>"
                               class="font-bold text-[#08284B] hover:underline"
                               aria-label="<?php echo esc_attr('Call ' . $hero_phone['title']); ?>">
                                <?php echo esc_html($hero_phone['title']); ?>
                            </a>
                        </span>
                    </p>

                    <?php if ($is_slider) { ?>
                        <!-- SLIDER CONTROLS -->
                        <div class="mt-8 flex w-full items-center justify-between">
                            <div class="flex items-center gap-2">
                                <?php for ($d = 0; $d < count($slides); $d++) {
                                    $is_active = $d === 0;
                                    ?>
                                    <button
                                        type="button"
                                        class="slider-dot"
                                        data-slide="<?php echo esc_attr($d); ?>"
                                        aria-label="<?php echo esc_attr('Go to slide ' . ($d + 1)); ?>"
                                        <?php echo $is_active ? 'aria-current="true"' : ''; ?>>
                                    </button>
                                <?php } ?>
                            </div>

                            <div class="flex items-center gap-3">
                                <button
                                    type="button"
                                    class="slider-prev group flex h-8 w-8 items-center justify-center rounded-full border border-[#7ED0E0] bg-white transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#7ED0E0] lg:hover:border-[#7ED0E0] lg:hover:bg-[#001F33] lg:active:bg-black lg:focus:bg-[#001F33]"
                                    aria-label="Previous slide">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none" aria-hidden="true">
                                        <path d="M6.08301 0.750081L0.749674 6.08342L6.08301 11.4167"
                                              class="stroke-[#020617] transition-colors lg:group-hover:stroke-white lg:group-active:stroke-white lg:group-focus:stroke-white"
                                              stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>
                                <button
                                    type="button"
                                    class="slider-next group flex h-8 w-8 items-center justify-center rounded-full border border-[#7ED0E0] bg-white transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#7ED0E0] lg:hover:border-[#7ED0E0] lg:hover:bg-[#001F33] lg:active:bg-black lg:focus:bg-[#001F33]"
                                    aria-label="Next slide">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none" aria-hidden="true">
                                        <path d="M0.916992 0.750081L6.25033 6.08342L0.916992 11.4167"
                                              class="stroke-[#020617] transition-colors lg:group-hover:stroke-white lg:group-active:stroke-white lg:group-focus:stroke-white"
                                              stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>
                                <button
                                    type="button"
                                    class="slider-pause group flex h-8 w-8 items-center justify-center rounded-full border border-[#7ED0E0] bg-white transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#7ED0E0] lg:hover:bg-[#001F33] lg:focus:bg-[#001F33]"
                                    aria-label="Pause slideshow"
                                    aria-pressed="false">
                                    <svg class="icon-pause" width="10" height="12" viewBox="0 0 10 12" fill="none" aria-hidden="true">
                                        <path d="M1 1v10M9 1v10" class="stroke-[#020617] lg:group-hover:stroke-white" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                    <svg class="icon-play" width="10" height="12" viewBox="0 0 10 12" fill="none" aria-hidden="true">
                                        <path d="M1 1.5v9l8-4.5-8-4.5Z" class="stroke-[#020617] lg:group-hover:stroke-white" stroke-width="1.5" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php } ?>

    <?php if ($is_slider) { ?>
        </div>
    <?php } ?>
</section>

<?php if ($is_slider) { ?>
<script>
(function() {
    var id        = '<?php echo esc_js($section_id); ?>';
    var selector  = '[data-slider-id="' + id + '"]';

    function initHeroSlider() {
        var container = document.querySelector(selector);

        if (!container || container.classList.contains('slick-initialized')) {
            return true;
        }

        if (typeof jQuery === 'undefined' || typeof jQuery.fn.slick === 'undefined') {
            return false;
        }

        var $ = jQuery;

        $(container).slick({
            arrows: false,
            dots: false,
            infinite: true,
            fade: true,
            speed: 600,
            autoplay: true,
            autoplaySpeed: 5500,
            adaptiveHeight: true
        });

        var dots = document.querySelectorAll('#' + id + ' .slider-dot');
        var pauseBtns = document.querySelectorAll('#' + id + ' .slider-pause');
        var paused = false;

        function setActiveDots(index) {
            dots.forEach(function(d) {
                var dotIndex = parseInt(d.getAttribute('data-slide') || '-1', 10);
                var active = dotIndex === index;
                if (active) {
                    d.setAttribute('aria-current', 'true');
                } else {
                    d.removeAttribute('aria-current');
                }
            });
        }

        function setPaused(next) {
            paused = next;
            if (paused) {
                $(container).slick('slickPause');
            } else {
                $(container).slick('slickPlay');
            }
            pauseBtns.forEach(function(btn) {
                btn.classList.toggle('is-paused', paused);
                btn.setAttribute('aria-pressed', paused ? 'true' : 'false');
                btn.setAttribute('aria-label', paused ? 'Play slideshow' : 'Pause slideshow');
            });
        }

        dots.forEach(function(dot) {
            dot.addEventListener('click', function() {
                var target = parseInt(dot.getAttribute('data-slide') || '0', 10) || 0;
                $(container).slick('slickGoTo', target);
            });
        });

        setActiveDots(0);

        $(container).on('beforeChange', function(e, slick, cur, next) {
            setActiveDots(next);
        });

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

        pauseBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                setPaused(!paused);
            });
        });

        return true;
    }

    function bootHeroSlider() {
        if (initHeroSlider()) {
            return;
        }

        var attempts = 0;
        var timer = setInterval(function() {
            if (initHeroSlider() || ++attempts >= 40) {
                clearInterval(timer);
            }
        }, 50);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootHeroSlider);
    } else {
        bootHeroSlider();
    }
})();
</script>
<?php } ?>
