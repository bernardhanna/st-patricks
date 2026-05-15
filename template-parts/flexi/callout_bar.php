<?php

$section_id = 'callout-bar-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());
$message = matrix_resolve_callout_bar_message(
    get_sub_field('message'),
    'SPMHS is a registered charity (Registered Charity Number (RCN): 20000370).'
);

if ($message === '') {
    return;
}
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
    class="relative flex overflow-hidden"
    style="background-color: #F1F8F9;"
>
    <div class="flex min-h-[48px] w-full max-w-[1280px] mx-auto items-center justify-center px-4 py-[10px] text-center">
        <p class="font-primary text-[18px] font-semibold leading-[28px] text-black">
            <?php echo esc_html($message); ?>
        </p>
    </div>
</section>
