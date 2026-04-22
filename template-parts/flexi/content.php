<?php
// Get ACF field values
$heading = get_sub_field('heading');
$heading_tag = get_sub_field('heading_tag');
$content = get_sub_field('content');
$primary_button = get_sub_field('primary_button');
$secondary_button = get_sub_field('secondary_button');
$image = get_sub_field('image');
$image_alt = get_post_meta($image, '_wp_attachment_image_alt', true) ?: 'Child safeguarding illustration';
$background_type = get_sub_field('background_type');
$background_color = get_sub_field('background_color');
$reverse_layout = get_sub_field('reverse_layout');

// Generate unique section ID
$section_id = 'child-safeguarding-' . wp_rand(1000, 9999);

$resolve_link = static function ($link) {
    if (!is_array($link) || empty($link['url'])) {
        return null;
    }

    return [
        'url' => esc_url($link['url']),
        'title' => esc_html($link['title'] ?: $link['url']),
        'target' => esc_attr($link['target'] ?: '_self'),
    ];
};

$primary_cta = $resolve_link($primary_button);
$secondary_cta = $resolve_link($secondary_button);

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
    data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
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

                <?php if ($primary_cta || $secondary_cta): ?>
                    <div class="flex flex-wrap gap-2.5 items-center">
                        <?php if ($primary_cta): ?>
                            <a
                                href="<?php echo $primary_cta['url']; ?>"
                                class="btn flex justify-center items-center gap-2.5 px-4 py-2 bg-[#024B79] text-white text-sm font-medium leading-6 transition-colors duration-300 hover:bg-[#FF9E66] hover:text-[#1E244B] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#024B79] rounded"
                                target="<?php echo $primary_cta['target']; ?>"
                                aria-label="<?php echo esc_attr($primary_cta['title']); ?>"
                            >
                                <?php echo $primary_cta['title']; ?>
                            </a>
                        <?php endif; ?>

                        <?php if ($secondary_cta): ?>
                            <a
                                href="<?php echo $secondary_cta['url']; ?>"
                                class="btn flex justify-center items-center gap-2.5 px-4 py-2 border border-[#024B79] bg-white text-[#024B79] text-sm font-medium leading-6 transition-colors duration-300 hover:bg-[#FF9E66] hover:text-[#1E244B] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#024B79] rounded"
                                target="<?php echo $secondary_cta['target']; ?>"
                                aria-label="<?php echo esc_attr($secondary_cta['title']); ?>"
                            >
                                <?php echo $secondary_cta['title']; ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            </article>
        </div>
    </div>
</section>
