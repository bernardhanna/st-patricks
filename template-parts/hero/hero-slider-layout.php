<style>
#<?php echo esc_attr($section_id); ?> {
    --spmhs-panel: #0B5D8F;
    --spmhs-navy: #08284B;
    --spmhs-aqua: #7ED0E0;
    --spmhs-soft: #DCEFF4;
    position: relative;
    overflow: hidden;
    margin: 0;
    container-type: inline-size;
    background: var(--spmhs-panel);
    font-family: Inter, system-ui, sans-serif;
}
#<?php echo esc_attr($section_id); ?> .spmhs-sprite {
    position: absolute;
    left: 0;
    top: 0;
    width: 0;
    height: 0;
    overflow: hidden;
    pointer-events: none;
}
#<?php echo esc_attr($section_id); ?> .hero-slider-container:not(.slick-initialized) .hero-slide:not(:first-child) {
    display: none !important;
}
#<?php echo esc_attr($section_id); ?> .hero-slider-container:not(.slick-initialized),
#<?php echo esc_attr($section_id); ?> .slick-list,
#<?php echo esc_attr($section_id); ?> .slick-track {
    height: 520px;
}
#<?php echo esc_attr($section_id); ?> .hero-slide,
#<?php echo esc_attr($section_id); ?> .slick-slide.slick-active {
    display: grid !important;
    grid-template-columns: 52fr 48fr;
    height: 520px;
    background: var(--spmhs-panel);
}
#<?php echo esc_attr($section_id); ?> .slick-slide {
    height: 520px;
    background: var(--spmhs-panel);
}
#<?php echo esc_attr($section_id); ?> .slick-slide:not(.slick-active) {
    pointer-events: none;
}
#<?php echo esc_attr($section_id); ?> .spmhs-hero__panel {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 56px 72px 72px 20px;
    padding-left: max(20px, calc((100cqw - 1280px) / 2 + 20px));
    color: #fff;
    overflow: hidden;
}
#<?php echo esc_attr($section_id); ?> .spmhs-hero__panel > :not(.spmhs-hero__watermark) {
    position: relative;
}
#<?php echo esc_attr($section_id); ?> .spmhs-hero__watermark {
    position: absolute;
    right: -150px;
    top: -170px;
    width: 500px;
    height: 483px;
    color: #fff;
    opacity: .08;
    pointer-events: none;
}
#<?php echo esc_attr($section_id); ?> .spmhs-hero__title {
    margin: 0 0 20px;
    max-width: 600px;
    font-size: 46px;
    line-height: 1.12;
    font-weight: 700;
    font-family: Inter, system-ui, sans-serif;
    letter-spacing: -.02em;
    color: #fff;
}
#<?php echo esc_attr($section_id); ?> .spmhs-hero__intro {
    margin: 0 0 32px;
    max-width: 520px;
    font-size: 18px;
    line-height: 1.55;
    font-family: Inter, system-ui, sans-serif;
    color: var(--spmhs-soft);
}
#<?php echo esc_attr($section_id); ?> .spmhs-hero__intro p { margin: 0; font-family: Inter, system-ui, sans-serif; }
#<?php echo esc_attr($section_id); ?> .spmhs-hero__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    margin-bottom: 20px;
}
#<?php echo esc_attr($section_id); ?> .spmhs-hero__call {
    margin: 0;
    font-size: 15px;
    font-family: Inter, system-ui, sans-serif;
    color: var(--spmhs-soft);
}
#<?php echo esc_attr($section_id); ?> .spmhs-hero__call a {
    color: #fff;
    font-weight: 600;
}
#<?php echo esc_attr($section_id); ?> .hero-slide-image {
    position: relative;
    min-height: 100%;
}
#<?php echo esc_attr($section_id); ?> .hero-slide-image img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    max-width: none;
    object-fit: cover;
    object-position: 60% 30%;
    display: block;
}
#<?php echo esc_attr($section_id); ?> .spmhs-hero__rosette {
    position: absolute;
    z-index: 3;
    left: 52%;
    top: 236px;
    width: 240px;
    height: 232px;
    transform: translateX(-50%);
    color: var(--spmhs-aqua);
    pointer-events: none;
}
#<?php echo esc_attr($section_id); ?> .spmhs-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 52px;
    padding: 0 26px;
    border-radius: 6px;
    font-size: 16px;
    line-height: 1.2;
    font-family: Inter, system-ui, sans-serif;
    text-decoration: none;
    transition: background-color .2s, border-color .2s, color .2s;
}
#<?php echo esc_attr($section_id); ?> .spmhs-btn--primary {
    background: var(--spmhs-aqua);
    color: var(--spmhs-navy);
    font-weight: 600;
}
#<?php echo esc_attr($section_id); ?> .spmhs-btn--primary:hover { background: #A9E1EC; color: var(--spmhs-navy); }
#<?php echo esc_attr($section_id); ?> .spmhs-btn--ghost {
    border: 1.5px solid rgba(255,255,255,.7);
    color: #fff;
    font-weight: 500;
}
#<?php echo esc_attr($section_id); ?> .spmhs-btn--ghost:hover {
    background: rgba(255,255,255,.12);
    border-color: #fff;
    color: #fff;
}
#<?php echo esc_attr($section_id); ?> .spmhs-btn:focus-visible,
#<?php echo esc_attr($section_id); ?> .spmhs-hero__call a:focus-visible {
    outline: 3px solid var(--spmhs-aqua);
    outline-offset: 3px;
}
#<?php echo esc_attr($section_id); ?> .hero-slide-controls {
    display: none;
}
#<?php echo esc_attr($section_id); ?> .slider-dot {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    border: 1px solid #7ED0E0;
    background: transparent;
}
#<?php echo esc_attr($section_id); ?> .slider-dot[aria-current="true"] {
    width: 22px;
    border-color: #0f172a;
    background-color: #0f172a;
}
#<?php echo esc_attr($section_id); ?> .slider-pause .icon-play { display: none; }
#<?php echo esc_attr($section_id); ?> .slider-pause.is-paused .icon-pause { display: none; }
#<?php echo esc_attr($section_id); ?> .slider-pause.is-paused .icon-play { display: block; }
@media (max-width: 1100px) {
    #<?php echo esc_attr($section_id); ?> .spmhs-hero__panel { padding-top: 48px; padding-right: 48px; padding-bottom: 64px; }
    #<?php echo esc_attr($section_id); ?> .spmhs-hero__title { font-size: 38px; }
}
@media (max-width: 860px) {
    #<?php echo esc_attr($section_id); ?> .hero-slider-container:not(.slick-initialized),
    #<?php echo esc_attr($section_id); ?> .slick-list,
    #<?php echo esc_attr($section_id); ?> .slick-track,
    #<?php echo esc_attr($section_id); ?> .hero-slide,
    #<?php echo esc_attr($section_id); ?> .slick-slide,
    #<?php echo esc_attr($section_id); ?> .slick-slide.slick-active {
        height: auto;
        grid-template-columns: 1fr;
    }
    #<?php echo esc_attr($section_id); ?> .hero-slide-image { order: -1; height: 250px; }
    #<?php echo esc_attr($section_id); ?> .hero-slide-image img { object-position: 62% 28%; }
    #<?php echo esc_attr($section_id); ?> .spmhs-hero__panel { padding-top: 36px; padding-right: 24px; padding-bottom: 64px; }
    #<?php echo esc_attr($section_id); ?> .spmhs-hero__watermark {
        right: auto;
        top: auto;
        left: -120px;
        bottom: -150px;
        width: 320px;
        height: 309px;
        opacity: .07;
    }
    #<?php echo esc_attr($section_id); ?> .spmhs-hero__title { font-size: 31px; line-height: 1.15; margin-bottom: 14px; }
    #<?php echo esc_attr($section_id); ?> .spmhs-hero__intro { font-size: 16px; margin-bottom: 24px; }
    #<?php echo esc_attr($section_id); ?> .spmhs-hero__actions { flex-direction: column; gap: 12px; margin-bottom: 16px; }
    #<?php echo esc_attr($section_id); ?> .spmhs-btn { width: 100%; }
    #<?php echo esc_attr($section_id); ?> .spmhs-hero__call { text-align: center; }
    #<?php echo esc_attr($section_id); ?> .spmhs-hero__rosette {
        left: auto;
        right: 20px;
        top: 186px;
        width: 128px;
        height: 124px;
        transform: none;
    }
}
@media (max-width: 1099px) {
    #<?php echo esc_attr($section_id); ?> .spmhs-hero__panel { padding-left: 24px; }
}
@media (max-width: 767px) {
    #<?php echo esc_attr($section_id); ?> .spmhs-hero__panel { padding-left: 20px; }
}
@media (max-width: 639px) {
    #<?php echo esc_attr($section_id); ?> .spmhs-hero__panel { padding-left: 16px; }
}
@media (prefers-reduced-motion: reduce) {
    #<?php echo esc_attr($section_id); ?> .spmhs-btn { transition: none; }
}
</style>

<section id="<?php echo esc_attr($section_id); ?>" class="spmhs-hero" aria-label="Welcome">
    <div class="spmhs-sprite"><?php get_template_part('template-parts/hero/rosette-symbol', null, array('id' => $section_id . '-rosette')); ?></div>

    <?php if ($is_slider) { ?>
        <div class="w-full hero-slider-container" data-slider-id="<?php echo esc_attr($section_id); ?>">
    <?php } ?>

    <?php foreach ($slides as $i => $sl) {
        $heading_id = $section_id . '-heading-' . $i;
        $rosette_href = '#' . $section_id . '-rosette';
        ?>
        <div class="hero-slide">
            <div class="spmhs-hero__panel">
                <svg class="spmhs-hero__watermark" aria-hidden="true" focusable="false"><use href="<?php echo esc_attr($rosette_href); ?>"/></svg>
                <div class="hero-slide-text flex w-full flex-col lg:max-w-[560px] lg:shrink-0">
                    <?php if (!empty($sl['text'])) { ?>
                        <<?php echo esc_attr($sl['tag']); ?> class="spmhs-hero__title" id="<?php echo esc_attr($heading_id); ?>">
                            <?php echo nl2br(esc_html($sl['text']), false); ?>
                        </<?php echo esc_attr($sl['tag']); ?>>
                    <?php } ?>
                    <?php if (!empty($sl['desc'])) { ?>
                        <div class="spmhs-hero__intro">
                            <?php echo matrix_kses_rich_text($sl['desc']); ?>
                        </div>
                    <?php } ?>
                    <div class="spmhs-hero__actions">
                        <?php if (!empty($sl['pbtn']['url'])) { ?>
                            <a class="spmhs-btn spmhs-btn--primary" href="<?php echo esc_url($sl['pbtn']['url']); ?>" target="<?php echo esc_attr($sl['pbtn']['target'] ? $sl['pbtn']['target'] : '_self'); ?>">
                                <?php echo esc_html($sl['pbtn']['title']); ?>
                            </a>
                        <?php } ?>
                        <?php if (!empty($sl['sbtn']['url'])) { ?>
                            <a class="spmhs-btn spmhs-btn--ghost" href="<?php echo esc_url($sl['sbtn']['url']); ?>" target="<?php echo esc_attr($sl['sbtn']['target'] ? $sl['sbtn']['target'] : '_self'); ?>">
                                <?php echo esc_html($sl['sbtn']['title']); ?>
                            </a>
                        <?php } ?>
                    </div>
                    <p class="spmhs-hero__call">Or call us on <a href="<?php echo esc_url($hero_phone['url']); ?>"><?php echo esc_html($hero_phone['title']); ?></a></p>
                </div>
            </div>
            <div class="hero-slide-image spmhs-hero__media">
                <?php if (!empty($sl['desktop_img']['url'])) { ?>
                    <img src="<?php echo esc_url($sl['desktop_img']['url']); ?>"
                         <?php if (!empty($sl['srcset'])) { ?>
                         srcset="<?php echo esc_attr($sl['srcset']); ?>"
                         sizes="48vw"
                         <?php } ?>
                         alt="<?php echo esc_attr($sl['desktop_img']['alt']); ?>"
                         <?php echo $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"'; ?>>
                <?php } ?>
                <?php if ($is_slider) { ?>
                    <!-- SLIDER CONTROLS -->
                    <div class="hero-slide-controls flex w-full items-center justify-between">
                        <div class="flex items-center gap-2">
                            <?php for ($d = 0; $d < count($slides); $d++) {
                                $is_active = $d === 0;
                                ?>
                                <button type="button" class="slider-dot" data-slide="<?php echo esc_attr($d); ?>" aria-label="<?php echo esc_attr('Go to slide ' . ($d + 1)); ?>" <?php echo $is_active ? 'aria-current="true"' : ''; ?>></button>
                            <?php } ?>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="button" class="slider-prev group flex h-8 w-8 items-center justify-center rounded-full border border-[#7ED0E0] bg-white transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#7ED0E0] lg:hover:border-[#7ED0E0] lg:hover:bg-[#001F33] lg:active:bg-black lg:focus:bg-[#001F33]" aria-label="Previous slide">
                                <svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none" aria-hidden="true"><path d="M6.08301 0.750081L0.749674 6.08342L6.08301 11.4167" class="stroke-[#020617] transition-colors lg:group-hover:stroke-white lg:group-active:stroke-white lg:group-focus:stroke-white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            </button>
                            <button type="button" class="slider-next group flex h-8 w-8 items-center justify-center rounded-full border border-[#7ED0E0] bg-white transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#7ED0E0] lg:hover:border-[#7ED0E0] lg:hover:bg-[#001F33] lg:active:bg-black lg:focus:bg-[#001F33]" aria-label="Next slide">
                                <svg xmlns="http://www.w3.org/2000/svg" width="7" height="13" viewBox="0 0 7 13" fill="none" aria-hidden="true"><path d="M0.916992 0.750081L6.25033 6.08342L0.916992 11.4167" class="stroke-[#020617] transition-colors lg:group-hover:stroke-white lg:group-active:stroke-white lg:group-focus:stroke-white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            </button>
                            <button type="button" class="slider-pause group flex h-8 w-8 items-center justify-center rounded-full border border-[#001F33] bg-[#001F33] transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#7ED0E0] lg:hover:bg-[#001F33] lg:focus:bg-[#001F33]" aria-label="Pause slideshow" aria-pressed="false">
                                <svg class="icon-pause" width="10" height="12" viewBox="0 0 10 12" fill="none" aria-hidden="true"><path d="M1 1v10M9 1v10" stroke="#FFFFFF" class="lg:group-hover:stroke-white" stroke-width="1.5" stroke-linecap="round"/></svg>
                                <svg class="icon-play" width="10" height="12" viewBox="0 0 10 12" fill="none" aria-hidden="true"><path d="M1 1.5v9l8-4.5-8-4.5Z" stroke="#FFFFFF" class="lg:group-hover:stroke-white" stroke-width="1.5" stroke-linejoin="round"/></svg>
                            </button>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?>

    <?php if ($is_slider) { ?>
        </div>
    <?php } ?>

    <svg class="spmhs-hero__rosette" aria-hidden="true" focusable="false"><use href="#<?php echo esc_attr($section_id); ?>-rosette"/></svg>
</section>
