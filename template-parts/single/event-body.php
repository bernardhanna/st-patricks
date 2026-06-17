<?php

if (! matrix_uses_event_style_single_layout()) {
    return;
}

$event_fields = matrix_get_event_post_fields();
$has_cta_summary = trim(wp_strip_all_tags($event_fields['cta_summary'])) !== '';
$has_external_url = $event_fields['external_url'] !== '';
$show_cta_box = $has_cta_summary || $has_external_url;
?>

<section class="bg-white">
    <div class="mx-auto flex w-full max-w-[1018px] flex-col gap-12 px-5 py-12 lg:flex-row lg:items-start lg:gap-[60px] lg:px-0 lg:py-[100px]">
        <div class="min-w-0 flex-1">
            <?php if ($show_cta_box) { ?>
                <div class="mb-8 flex flex-col gap-6 rounded-[8px] bg-[#F1F8F9] p-6 lg:flex-row lg:items-center lg:justify-between lg:gap-10 lg:p-8">
                    <?php if ($has_cta_summary) { ?>
                        <div class="blog-single-content font-primary text-[16px] leading-[28px] text-[#08284B] lg:max-w-[640px]">
                            <?php echo matrix_kses_rich_text($event_fields['cta_summary']); ?>
                        </div>
                    <?php } ?>

                    <?php if ($has_external_url) { ?>
                        <a
                            href="<?php echo esc_url($event_fields['external_url']); ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="btn inline-flex w-full shrink-0 items-center justify-center rounded-[6px] bg-[#08284B] px-6 py-4 text-center font-primary text-[16px] font-semibold leading-none text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79] lg:w-auto"
                        >
                            <?php echo esc_html($event_fields['external_button_label']); ?>
                        </a>
                    <?php } ?>
                </div>
            <?php } ?>

            <?php get_template_part('template-parts/single/partials/event-style-featured-image'); ?>

            <?php if (trim(get_the_content()) !== '') { ?>
                <article class="<?php echo esc_attr(matrix_get_editor_body_content_class_names()); ?>" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <?php the_content(); ?>
                </article>
            <?php } ?>

            <?php get_template_part('template-parts/single/author-share'); ?>
        </div>

        <?php get_template_part('template-parts/single/related-posts'); ?>
    </div>
</section>
