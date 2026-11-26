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
$logo_img_class = 'block h-10 w-auto max-w-[14rem] object-contain';
$logo_link_class = 'inline-flex items-center justify-center focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-highlight-primary';

if (function_exists('is_front_page') && is_front_page()) {
    ?>
    <style>
    .spmhs-partners-band {
        background: var(--StPatricks_Aux_DarkBG4, linear-gradient(278deg, #F8F6F3 3.24%, #F5F6ED 90.88%));
        width: 100vw;
        margin-left: calc(50% - 50vw);
    }
    .spmhs-partners {
        --spmhs-navy: #08284B;
        --spmhs-aqua: #7ED0E0;
        position: relative;
        z-index: 4;
        display: flex;
        align-items: center;
        gap: 32px;
        width: min(1018px, 100%);
        max-width: 1018px;
        margin: -48px auto 0;
        padding: 24px 36px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 12px 32px rgba(8,40,75,.12);
        font-family: Inter, system-ui, sans-serif;
        box-sizing: border-box;
    }
    .spmhs-partners * { box-sizing: border-box; }
    .spmhs-partners__title {
        flex-shrink: 0;
        max-width: 300px;
        margin: 0;
        padding-right: 32px;
        border-right: 1px solid #E5E7EB;
        color: var(--spmhs-navy);
    }
    .spmhs-partners__logos {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 40px;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    .spmhs-partners__logos a { display: flex; }
    .spmhs-partners__logos img { display: block; height: 44px; width: auto; }
    .spmhs-partners a:focus-visible { outline: 3px solid var(--spmhs-aqua); outline-offset: 3px; }
    @media (max-width: 1100px) {
        .spmhs-partners { margin-top: -40px; }
    }
    @media (max-width: 860px) {
        .spmhs-partners { flex-direction: column; align-items: flex-start; gap: 16px; margin-top: -36px; padding: 20px; }
        .spmhs-partners__title { max-width: none; padding-right: 0; border-right: 0; }
        .spmhs-partners__logos { width: 100%; justify-content: space-between; gap: 12px; }
        .spmhs-partners__logos img { height: 32px; }
    }
    </style>
    <div class="spmhs-partners-band">
    <div class="spmhs-partners">
        <?php if ($heading_text !== '') { ?>
            <<?php echo tag_escape($heading_tag); ?> class="spmhs-partners__title font-primary text-[18px] font-semibold leading-[1.4] max-lg:font-medium max-lg:leading-[22.8px] max-lg:tracking-[-0.005em]"><?php echo esc_html($heading_text); ?></<?php echo tag_escape($heading_tag); ?>>
        <?php } ?>
        <?php if (!empty($partners) && is_array($partners)) { ?>
            <ul class="spmhs-partners__logos">
                <?php foreach ($partners as $row) {
                    $logo = $row['logo'] ?? null;
                    if (!$logo || empty($logo['url'])) {
                        continue;
                    }
                    $logo_url = $logo['url'];
                    $logo_alt = $logo['alt'] ?: 'Partner logo';
                    $link = $row['link'] ?? null;
                    $has_link = is_array($link) && !empty($link['url']);
                    ?>
                    <li>
                        <?php if ($has_link) { ?>
                            <a href="<?php echo esc_url($link['url']); ?>" target="<?php echo esc_attr($link['target'] ?: '_self'); ?>" <?php echo ($link['target'] ?? '') === '_blank' ? 'rel="noopener noreferrer"' : ''; ?>>
                                <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($logo_alt); ?>" height="44">
                            </a>
                        <?php } else { ?>
                            <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($logo_alt); ?>" height="44">
                        <?php } ?>
                    </li>
                <?php } ?>
            </ul>
        <?php } ?>
    </div>
    </div>
    <?php
    return;
}

$padding_settings = get_sub_field('padding_settings');
$padding_classes = function_exists('matrix_flexi_resolve_vertical_padding_classes')
    ? matrix_flexi_resolve_vertical_padding_classes(
        get_sub_field('vertical_padding'),
        is_array($padding_settings) ? $padding_settings : [],
        'py-12 lg:py-[6rem]'
    )
    : 'py-12 lg:py-[6rem]';

?>

<section id="<?php echo esc_attr($section_id); ?>"
         data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
         class="flex relative"
         style="background-color: <?php echo esc_attr($background_color); ?>;">
    <div class="flex flex-col items-center mx-auto w-full max-w-container_md p max-lg:px-5 <?php echo esc_attr($padding_classes); ?>">

        <div class="flex flex-col gap-8 w-full lg:flex-row lg:items-center lg:justify-between lg:gap-10">

            <!-- Heading Section -->
            <div class="w-full lg:max-w-[295px] lg:shrink-0">
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
                    <div class="hidden flex-nowrap gap-6 justify-center items-center sm:flex md:gap-8 lg:justify-end lg:gap-6">
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
