<?php
/**
 * Generic archive fallback for CPTs without a dedicated archive template.
 * Ensures skip-link target #main-content exists (WCAG 2.4.1).
 */

get_header();

$archive_title = get_the_archive_title();
$archive_description = get_the_archive_description();
?>
<main id="main-content" class="w-full overflow-hidden site-main">
    <section class="mx-auto w-full max-w-[1018px] px-5 py-12 lg:py-[100px]">
        <header class="mb-10">
            <h1 class="font-primary text-[30px] font-semibold leading-[36px] tracking-[-0.225px] text-[#08284B]">
                <?php echo wp_kses_post($archive_title); ?>
            </h1>
            <?php if (is_string($archive_description) && trim(wp_strip_all_tags($archive_description)) !== '') { ?>
                <div class="wp_editor mt-4 text-[#08284B] [&_p:last-child]:mb-0">
                    <?php echo wp_kses_post($archive_description); ?>
                </div>
            <?php } ?>
        </header>

        <?php if (have_posts()) { ?>
            <ul class="m-0 list-none space-y-4 p-0">
                <?php while (have_posts()) { ?>
                    <?php the_post(); ?>
                    <li class="border-b border-[#E2E8F0] pb-4 last:border-b-0">
                        <a
                            href="<?php echo esc_url(get_permalink()); ?>"
                            class="font-primary text-[18px] font-medium leading-[28px] text-[#024B79] underline hover:no-underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
                        >
                            <?php echo esc_html(get_the_title()); ?>
                        </a>
                    </li>
                <?php } ?>
            </ul>

            <div class="mt-10">
                <?php the_posts_pagination([
                    'mid_size' => 2,
                    'prev_text' => __('Previous', 'matrix-starter'),
                    'next_text' => __('Next', 'matrix-starter'),
                ]); ?>
            </div>
        <?php } else { ?>
            <p class="font-primary text-[16px] leading-[28px] text-[#08284B]">
                <?php esc_html_e('No items found.', 'matrix-starter'); ?>
            </p>
        <?php } ?>
    </section>
</main>
<?php
get_footer();
