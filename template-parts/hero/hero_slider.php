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

$hero_focals = array(
    '01-nurse-portrait' => '50% 25%',
    '02-garden-stone-building' => '50% 40%',
    '03-grounds-period-house' => '50% 55%',
    '04-two-nurses-sitting' => '50% 15%',
    '05-staff-pair-entrance' => '50% 20%',
);

foreach ($slides as $slide_index => $slide) {
    $slides[$slide_index]['focal'] = '50% 25%';
    $slides[$slide_index]['srcset'] = '';
    $image_url = (string) ($slide['desktop_img']['url'] ?? '');
    if (!preg_match('#^(.*?/)(\d{2}-[a-z0-9-]+)-(768|1440|1920|2560)\.(jpe?g|png|webp)$#i', $image_url, $image_match)) {
        continue;
    }
    $stem = $image_match[2];
    if (isset($hero_focals[$stem])) {
        $slides[$slide_index]['focal'] = $hero_focals[$stem];
    }
    $srcset = array();
    foreach (array(768, 1440, 1920, 2560) as $width) {
        $srcset[] = $image_match[1] . $stem . '-' . $width . '.' . $image_match[4] . ' ' . $width . 'w';
    }
    $slides[$slide_index]['srcset'] = implode(', ', $srcset);
    $slides[$slide_index]['desktop_img']['url'] = $image_match[1] . $stem . '-1920.' . $image_match[4];
}
?>

<?php require __DIR__ . '/hero-slider-layout.php'; ?>


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
            autoplaySpeed: 8000,
            adaptiveHeight: true,
            pauseOnHover: false,
            pauseOnFocus: false
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

        function keepPlayingUnlessPaused() {
            if (paused) {
                $(container).slick('slickPause');
            } else {
                $(container).slick('slickPlay');
            }
        }

        dots.forEach(function(dot) {
            dot.addEventListener('click', function() {
                var target = parseInt(dot.getAttribute('data-slide') || '0', 10) || 0;
                $(container).slick('slickGoTo', target);
                keepPlayingUnlessPaused();
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
                keepPlayingUnlessPaused();
            });
        });

        nextBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                $(container).slick('slickNext');
                keepPlayingUnlessPaused();
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
