<?php
// Get all ACF fields
$section_id = 'stories-' . uniqid();
$heading = get_sub_field('heading');
$heading_tag = get_sub_field('heading_tag');
$description = get_sub_field('description');
$hero_image = get_sub_field('hero_image');
$hero_image_alt = get_post_meta($hero_image, '_wp_attachment_image_alt', true) ?: 'Stories and support image';
$button = get_sub_field('button');
$background_color = get_sub_field('background_color');

// Get padding settings
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

?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    class="relative flex overflow-hidden <?php echo esc_attr(implode(' ', $padding_classes)); ?>"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
    aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
>
    <div class="flex flex-col items-center pt-24 pb-24 mx-auto w-full max-w-container max-lg:px-5 max-sm:pt-16 max-sm:pb-16">
        <div class="flex flex-col gap-14 items-start max-w-full w-[1018px] max-md:px-5 max-md:py-0 max-md:w-[90%] max-sm:px-4 max-sm:py-0 max-sm:w-[95%]">

            <!-- Header Section -->
            <div class="flex gap-5 justify-between items-center w-full max-md:flex-col max-md:gap-10 max-md:items-start max-sm:gap-8">

                <!-- Left Content -->
                <div class="flex flex-col flex-1 gap-8 items-start max-md:w-full max-sm:gap-6">
                    <header class="flex flex-col gap-8 justify-center items-start max-sm:gap-6">
                        <?php if (!empty($heading)): ?>
                            <<?php echo esc_attr($heading_tag); ?>
                                id="<?php echo esc_attr($section_id); ?>-heading"
                                class="text-3xl font-semibold tracking-tight leading-9 text-indigo-950 max-md:text-2xl max-md:leading-8 max-sm:text-2xl max-sm:leading-8"
                            >
                                <?php echo esc_html($heading); ?>
                            </<?php echo esc_attr($heading_tag); ?>>
                        <?php endif; ?>
                        <div class="w-10 h-1 bg-red-400" role="presentation" aria-hidden="true"></div>
                    </header>

                    <?php if (!empty($description)): ?>
                        <div class="max-w-full text-base font-medium leading-7 text-teal-950 w-[467px] max-md:w-full max-md:text-base max-md:leading-7 max-sm:text-sm max-sm:leading-6 wp_editor">
                            <?php echo wp_kses_post($description); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($button && is_array($button) && isset($button['url'], $button['title'])): ?>
                        <div class="flex gap-4 items-center">
                            <a
                                href="<?php echo esc_url($button['url']); ?>"
                                class="flex gap-2.5 justify-center items-center px-4 py-2 whitespace-nowrap rounded border border-sky-900 border-solid transition-colors duration-300 cursor-pointer btn w-fit hover:bg-sky-900 hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-900"
                                target="<?php echo esc_attr($button['target'] ?? '_self'); ?>"
                                aria-label="<?php echo esc_attr($button['title']); ?>"
                            >
                                <span class="text-sm font-medium leading-6 text-teal-950">
                                    <?php echo esc_html($button['title']); ?>
                                </span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Hero Image -->
                <?php if ($hero_image): ?>
                    <div class="flex object-cover shrink-0 gap-2.5 justify-center items-center rounded-md h-[284px] w-[502px] max-md:w-full max-md:h-auto max-md:max-w-[502px] max-sm:w-full max-sm:h-[200px]">
                        <?php echo wp_get_attachment_image($hero_image, 'full', false, [
                            'alt' => esc_attr($hero_image_alt),
                            'class' => 'w-full h-full object-cover rounded-md',
                        ]); ?>
                    </div>
                <?php endif; ?>
            </div>
            </nav>
        </div>
    </div>
</section>

