<?php
// Get ACF field values
$heading = get_sub_field('heading');
$heading_tag = get_sub_field('heading_tag');
$content = get_sub_field('content');
$image = get_sub_field('image');
$image_alt = get_post_meta($image, '_wp_attachment_image_alt', true) ?: 'Child safeguarding illustration';
$background_type = get_sub_field('background_type');
$background_color = get_sub_field('background_color');
$reverse_layout = get_sub_field('reverse_layout');

// Generate unique section ID
$section_id = 'child-safeguarding-' . wp_rand(1000, 9999);

// Build padding classes
$padding_classes = [];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size = get_sub_field('screen_size');
        $padding_top = get_sub_field('padding_top');
        $padding_bottom = get_sub_field('padding_bottom');
        $padding_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
        $padding_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
    }
}

// Determine background style
$background_style = '';
if ($background_type === 'gradient') {
    $background_style = 'background: linear-gradient(278deg, #F6EDE0 3.24%, #F4F5DE 90.88%);';
} elseif ($background_type === 'color' && $background_color) {
    $background_style = 'background-color: ' . esc_attr($background_color) . ';';
}
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="flex overflow-hidden relative"
    style="<?php echo $background_style; ?>"
    role="region"
    aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
>
    <div class="flex flex-col items-center w-full mx-auto max-w-container pt-5 pb-5 lg:py-[6rem] lg max-lg:px-5 <?php echo esc_attr(implode(' ', $padding_classes)); ?>">
        <div class="grid grid-cols-1 lg:grid-cols-2 max-lg:gap-12 items-center w-full max-w-[1018px] <?php echo $reverse_layout ? 'lg:grid-flow-col-dense' : ''; ?>">

            <!-- Image Section -->
            <div class="<?php echo $reverse_layout ? 'lg:col-start-2' : 'lg:col-start-1'; ?> flex justify-center lg:justify-start">
                <?php if ($image): ?>
                    <figure class="relative">
                        <?php echo wp_get_attachment_image($image, 'full', false, [
                            'alt' => esc_attr($image_alt),
                            'class' => 'rounded object-cover w-full h-auto max-w-[450px] max-h-[346px] max-md:max-w-[400px] max-sm:max-w-[300px]',
                            'loading' => 'lazy'
                        ]); ?>
                    </figure>
                <?php endif; ?>
            </div>

            <!-- Content Section -->
            <article class="<?php echo $reverse_layout ? 'lg:col-start-1' : 'lg:col-start-2'; ?> flex flex-col gap-8 justify-center items-start w-full">

                <!-- Header Section -->
                <header class="flex flex-col gap-8 justify-center items-start w-full">
                    <?php if (!empty($heading)): ?>
                        <div class="flex flex-col gap-8">
                            <<?php echo esc_attr($heading_tag); ?>
                                id="<?php echo esc_attr($section_id); ?>-heading"
                                class="text-3xl font-semibold tracking-tight leading-9 text-indigo-950 max-md:text-2xl max-md:leading-8 max-sm:text-2xl max-sm:leading-8"
                            >
                                <?php echo esc_html($heading); ?>
                            </<?php echo esc_attr($heading_tag); ?>>

                            <div
                                class="w-10 h-1 bg-red-400"
                                role="presentation"
                                aria-hidden="true"
                            ></div>
                        </div>
                    <?php endif; ?>
                </header>

                <!-- Content Section -->
                <?php if (!empty($content)): ?>
                    <div class="w-full">
                        <div class="text-base font-medium leading-7 text-teal-950 wp_editor max-md:text-base max-md:leading-7 max-sm:text-sm max-sm:leading-6">
                            <?php echo wp_kses_post($content); ?>
                        </div>
                    </div>
                <?php endif; ?>

            </article>
        </div>
    </div>
</section>
