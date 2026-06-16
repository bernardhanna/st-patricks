<?php

if (get_post_type() !== 'programmes_therapies') {
    return;
}

$defaults = matrix_get_programmes_therapies_single_defaults();
$back_label = (string) ($defaults['back_label'] ?? 'Back to programmes');
$archive_url = matrix_get_programmes_therapies_archive_url();
$intro = matrix_get_programmes_therapies_intro();
?>

<section class="bg-[#C6ECF4]">
    <div class="mx-auto flex w-full max-w-[1018px] flex-col px-5 py-12 lg:px-0 lg:py-[100px]">
        <a
            href="<?php echo esc_url($archive_url); ?>"
            class="inline-flex w-fit self-start items-center gap-2 font-primary text-[20px] font-semibold leading-[32px] tracking-[-0.12px] text-[#1E244B] transition-colors hover:text-[#024B79] hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]"
        >
            <span aria-hidden="true">&larr;</span>
            <span><?php echo esc_html($back_label); ?></span>
        </a>

        <div class="mt-4 flex max-w-[800px] flex-col gap-4">
            <h1 class="font-primary text-[36px] font-bold leading-[48px] tracking-[-0.576px] text-[#08284B] lg:text-[48px]">
                <?php the_title(); ?>
            </h1>

            <?php if ($intro !== '') { ?>
                <p class="max-w-[800px] font-primary text-[18px] font-normal leading-[28px] text-[#08284B]">
                    <?php echo esc_html($intro); ?>
                </p>
            <?php } ?>
        </div>
    </div>
</section>
