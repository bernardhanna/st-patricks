<?php

while (have_posts()) {
    the_post();

    $post_id = get_the_ID();
    $area = function_exists('get_field') ? trim((string) get_field('career_area', $post_id)) : '';
    $location_terms = function_exists('get_the_terms') ? get_the_terms($post_id, 'career_location') : false;
    $location_label = '';

    if (is_array($location_terms)) {
        foreach ($location_terms as $term) {
            if ($term instanceof WP_Term) {
                $location_label = $term->name;
                break;
            }
        }
    }

    $job_description = matrix_get_career_job_description_html($post_id);
    $show_application_form = matrix_career_shows_application_form($post_id);
    $back_url = matrix_get_careers_single_back_url();
    ?>

    <section class="bg-[#C6ECF4]">
        <div class="mx-auto flex w-full max-w-[1018px] flex-col px-5 py-12 lg:px-0 lg:py-[100px]">
            <a
                href="<?php echo esc_url($back_url); ?>"
                class="inline-flex w-fit self-start items-center gap-2 font-primary text-[20px] font-semibold leading-[32px] tracking-[-0.12px] text-[#1E244B] transition-colors hover:text-[#024B79] hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
            >
                <span aria-hidden="true">&larr;</span>
                <span>Back to vacancies</span>
            </a>

            <div class="mt-4 flex max-w-[800px] flex-col gap-4">
                <h1 class="font-primary text-[36px] font-bold leading-[48px] tracking-[-0.576px] text-[#08284B] lg:text-[48px]">
                    <?php the_title(); ?>
                </h1>

                <?php if ($area !== '' || $location_label !== '') { ?>
                    <dl class="flex flex-col gap-2 font-primary text-[16px] leading-6 text-[#08284B] sm:flex-row sm:flex-wrap sm:gap-x-8">
                        <?php if ($area !== '') { ?>
                            <div class="flex gap-2">
                                <dt class="font-bold">Area:</dt>
                                <dd><?php echo esc_html($area); ?></dd>
                            </div>
                        <?php } ?>
                        <?php if ($location_label !== '') { ?>
                            <div class="flex gap-2">
                                <dt class="font-bold">Location:</dt>
                                <dd><?php echo esc_html($location_label); ?></dd>
                            </div>
                        <?php } ?>
                    </dl>
                <?php } ?>
            </div>
        </div>
    </section>

    <?php if (trim(wp_strip_all_tags($job_description)) !== '') { ?>
        <section class="bg-white">
            <div class="<?php echo esc_attr(matrix_get_editor_body_content_wrapper_class_names()); ?>">
                <article class="<?php echo esc_attr(matrix_get_editor_body_content_class_names()); ?> careers-job-description" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <?php echo wp_kses_post($job_description); ?>
                </article>
            </div>
        </section>
    <?php } ?>

    <?php if ($show_application_form) { ?>
        <?php
        get_template_part('template-parts/careers/application-form', null, [
            'form' => matrix_prepare_careers_application_form($post_id),
        ]);
        ?>
    <?php } ?>
    <?php
}
