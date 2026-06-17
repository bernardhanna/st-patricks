<?php

while (have_posts()) {
    the_post();

    $post_id = get_the_ID();
    $hero_meta = matrix_get_career_hero_meta($post_id);
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

                <?php
                $hero_meta_values = array_column($hero_meta, 'value', 'label');
                $hero_meta_columns = [
                    ['Area', 'Category'],
                    ['Location', 'Job Type'],
                ];
                $hero_meta_has_values = array_filter($hero_meta_values, static fn ($value): bool => $value !== '');
                ?>
                <?php if ($hero_meta_has_values !== []) { ?>
                    <dl class="grid grid-cols-1 gap-x-8 gap-y-4 font-primary text-[16px] leading-6 text-[#08284B] sm:grid-cols-2">
                        <?php foreach ($hero_meta_columns as $column_labels) { ?>
                            <div class="flex flex-col gap-2">
                                <?php foreach ($column_labels as $label) { ?>
                                    <?php if (($hero_meta_values[$label] ?? '') !== '') { ?>
                                        <div class="flex gap-2">
                                            <dt class="font-bold"><?php echo esc_html($label); ?>:</dt>
                                            <dd><?php echo esc_html((string) $hero_meta_values[$label]); ?></dd>
                                        </div>
                                    <?php } ?>
                                <?php } ?>
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

    <?php
    get_template_part('template-parts/careers/share', null, [
        'post_id' => $post_id,
    ]);
    ?>

    <?php if ($show_application_form) { ?>
        <?php
        get_template_part('template-parts/careers/application-form', null, [
            'form' => matrix_prepare_careers_application_form($post_id),
        ]);
        ?>
    <?php } ?>
    <?php
}
