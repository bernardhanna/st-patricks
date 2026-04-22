<?php
/**
 * About Us / About Mental Health (Flexi Block)
 * Uses get_sub_field() only.
 */

if (!defined('ABSPATH')) {
    exit;
}

$section_id = 'about-us-' . ( function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid() );

// Heading
$heading_text = get_sub_field('heading') ?: 'About Mental Health';
$heading_tag  = get_sub_field('heading_tag') ?: 'h2';

// Sanitize heading tag
$allowed_tags = ['h1','h2','h3','h4','h5','h6','span','p'];
if (!in_array($heading_tag, $allowed_tags, true)) {
    $heading_tag = 'h2';
}

// Main image
$main_image      = get_sub_field('main_image');
$main_image_url  = '';
$main_image_alt  = 'Mental health support illustration';

if (!empty($main_image) && is_array($main_image)) {
    $main_image_url = $main_image['url'] ?? '';
    $media_alt      = $main_image['alt'] ?? '';
    if ($media_alt !== '') {
        $main_image_alt = $media_alt;
    }
}

// "View more" button link
$view_more_link = get_sub_field('view_more_link');

// Card content (no repeaters – individual fields; NO icon fields)
// Now each card includes a bg class AND a matching border class
$cards = [
    [
        'bg_class'     => 'bg-emerald-100',
        'border_class' => 'border-emerald-100',
        'title'        => get_sub_field('card_1_title'),
        'text'         => get_sub_field('card_1_text'),
        'link'         => get_sub_field('card_1_link'),
    ],
    [
        'bg_class'     => 'bg-teal-100',
        'border_class' => 'border-teal-100',
        'title'        => get_sub_field('card_2_title'),
        'text'         => get_sub_field('card_2_text'),
        'link'         => get_sub_field('card_2_link'),
    ],
    [
        'bg_class'     => 'bg-sky-200',
        'border_class' => 'border-sky-200',
        'title'        => get_sub_field('card_3_title'),
        'text'         => get_sub_field('card_3_text'),
        'link'         => get_sub_field('card_3_link'),
    ],
    [
        'bg_class'     => 'bg-blue-200',
        'border_class' => 'border-blue-200',
        'title'        => get_sub_field('card_4_title'),
        'text'         => get_sub_field('card_4_text'),
        'link'         => get_sub_field('card_4_link'),
    ],
    [
        'bg_class'     => 'bg-indigo-200',
        'border_class' => 'border-indigo-200',
        'title'        => get_sub_field('card_5_title'),
        'text'         => get_sub_field('card_5_text'),
        'link'         => get_sub_field('card_5_link'),
    ],
    [
        'bg_class'     => 'bg-indigo-200',
        'border_class' => 'border-indigo-200',
        'title'        => get_sub_field('card_6_title'),
        'text'         => get_sub_field('card_6_text'),
        'link'         => get_sub_field('card_6_link'),
    ],
];

// Padding settings
$padding_classes = [];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();
        $screen_size    = get_sub_field('screen_size');
        $padding_top    = get_sub_field('padding_top');
        $padding_bottom = get_sub_field('padding_bottom');

        if ($screen_size !== '' && $padding_top !== '' && $padding_top !== null) {
            $padding_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
        }
        if ($screen_size !== '' && $padding_bottom !== '' && $padding_bottom !== null) {
            $padding_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
        }
    }
}

// Section background
$section_bg = get_sub_field('background_color') ?: '#FFFFFF';
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
    class="flex overflow-hidden relative"
    style="background-color: <?php echo esc_attr($section_bg); ?>;"
>
    <div class="flex flex-col items-center w-full mx-auto max-w-container py-12 lg:py-24 max-lg:px-5 max-sm:px-6 <?php echo esc_attr(implode(' ', $padding_classes)); ?>">

        <!-- Header Section -->
        <?php if (!empty($heading_text)) : ?>
        <div class="flex flex-col justify-center max-w-full text-3xl font-semibold tracking-tight leading-tight text-indigo-950 w-[1018px] max-sm:justify-center max-sm:items-start max-sm:px-4">
            <<?php echo esc_attr($heading_tag); ?> class="text-indigo-950">
                <?php echo esc_html($heading_text); ?>
            </<?php echo esc_attr($heading_tag); ?>>

        <div class="mt-4 w-10  bg-[#F86] h-[4px]"
                 role="presentation"
                 aria-hidden="true"></div>
        </header>
        </div>
        <?php endif; ?>

        <!-- Main Content Container -->
        <div class="flex flex-col mt-16 max-w-full w-[1018px] max-md:mt-10">
            <!-- Cards + Image -->
            <div class="flex flex-wrap gap-4 items-start w-full max-md:max-w-full max-sm:justify-center max-sm:items-center max-sm:w-full" role="main">

                <!-- Left Column: first 3 cards -->
                <div class="flex flex-col justify-center min-w-60 w-[315px] max-[1084px]:w-[calc(50%-0.5rem)] max-sm:w-full">
                    <?php for ($i = 0; $i < 3; $i++) :
                        if (!isset($cards[$i])) {
                            continue;
                        }
                        $card = $cards[$i];
                        if (empty($card['title']) && empty($card['text'])) {
                            continue;
                        }

                        $link     = $card['link'];
                        $has_link = is_array($link) && !empty($link['url']);

                        // First card no margin-top, others get mt-4 (to match right column)
                        $margin_class = ($i === 0) ? '' : 'mt-4';

                        $card_classes = implode(' ', [
                            'mental-health-card',
                            'max-w-full',
                            'w-full',
                            'shadow-none',
                            'rounded-lg',
                            'w-full',
                            $card['bg_class'],
                            'border',
                            $card['border_class'],          // border matches background
                            'transition-all',
                            'duration-300',
                            'hover:border',
                            'hover:border-[var(--border)]',
                            'hover:bg-white',
                            'hover:shadow-sm',
                            $margin_class,
                        ]);
                    ?>
                    <article class="<?php echo esc_attr($card_classes); ?>">
                        <?php if ($has_link) : ?>
                            <a
                                href="<?php echo esc_url($link['url']); ?>"
                                target="<?php echo esc_attr($link['target'] ?? '_self'); ?>"
                                class="block w-full h-full rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-primary-dark"
                                aria-label="<?php echo esc_attr($link['title'] ?: $card['title']); ?>"
                            >
                                <div class="w-full rounded-lg">
                                    <div class="p-6 w-full max-sm:min-h-[164px] min-h-[15rem] max-md:px-5">
                                        <?php if (!empty($card['title'])) : ?>
                                            <h3 class="text-2xl font-semibold tracking-normal leading-8 text-indigo-950">
                                                <?php echo esc_html($card['title']); ?>
                                            </h3>
                                        <?php endif; ?>

                                        <?php if (!empty($card['text'])) : ?>
                                            <p class="mt-4 text-base font-medium leading-7 text-indigo-950">
                                                <?php echo esc_html($card['text']); ?>
                                            </p>
                                        <?php endif; ?>

                                        <!-- Static arrow icon -->
                                        <div class="flex flex-1 gap-2.5 justify-end items-end mt-4">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M8 4L16 12L8 20" stroke="#001F33" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        <?php else : ?>
                            <div class="w-full rounded-lg">
                                <div class="p-6 w-full min-h-[15rem] max-md:px-5">
                                    <?php if (!empty($card['title'])) : ?>
                                        <h3 class="text-2xl font-semibold tracking-normal leading-8 text-indigo-950">
                                            <?php echo esc_html($card['title']); ?>
                                        </h3>
                                    <?php endif; ?>

                                    <?php if (!empty($card['text'])) : ?>
                                        <p class="mt-4 text-base font-medium leading-7 text-indigo-950">
                                            <?php echo esc_html($card['text']); ?>
                                        </p>
                                    <?php endif; ?>

                                    <div class="flex flex-1 gap-2.5 justify-end items-end mt-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M8 4L16 12L8 20" stroke="#001F33" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </article>
                    <?php endfor; ?>
                </div>

                <!-- Center Image -->
                <?php if (!empty($main_image_url)) : ?>
                <img
                    src="<?php echo esc_url($main_image_url); ?>"
                    alt="<?php echo esc_attr($main_image_alt); ?>"
                    class="object-contain flex-1 self-stretch w-full shrink basis-0 min-w-60 max-[1084px]:hidden"
                />
                <?php endif; ?>

                <!-- Right Column: last 3 cards -->
                <div class="flex flex-col justify-center min-w-60 w-[315px] max-[1084px]:w-[calc(50%-0.5rem)] max-sm:w-full">
                    <?php for ($i = 3; $i < 6; $i++) :
                        if (!isset($cards[$i])) {
                            continue;
                        }
                        $card = $cards[$i];
                        if (empty($card['title']) && empty($card['text'])) {
                            continue;
                        }

                        $link     = $card['link'];
                        $has_link = is_array($link) && !empty($link['url']);

                        // Within the right column, first card (i=3) has no mt-4; others do
                        $margin_class = ($i === 3) ? '' : 'mt-4';

                        $card_classes = implode(' ', [
                            'mental-health-card',
                            'max-w-full',
                            'shadow-none',
                            'rounded-lg',
                            'w-full',
                            $card['bg_class'],
                            'border',
                            $card['border_class'],          // border matches background
                            'transition-all',
                            'duration-300',
                            'hover:border',
                            'hover:border-[var(--border)]',
                            'hover:bg-white',
                            'hover:shadow-sm',
                            $margin_class,
                        ]);
                    ?>
                    <article class="<?php echo esc_attr($card_classes); ?>">
                        <?php if ($has_link) : ?>
                            <a
                                href="<?php echo esc_url($link['url']); ?>"
                                target="<?php echo esc_attr($link['target'] ?? '_self'); ?>"
                                class="block w-full h-full rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-primary-dark"
                                aria-label="<?php echo esc_attr($link['title'] ?: $card['title']); ?>"
                            >
                                <div class="w-full rounded-lg">
                                    <div class="p-6 w-full min-h-[15rem] max-md:px-5">
                                        <?php if (!empty($card['title'])) : ?>
                                            <h3 class="text-2xl font-semibold tracking-normal leading-8 text-indigo-950">
                                                <?php echo esc_html($card['title']); ?>
                                            </h3>
                                        <?php endif; ?>

                                        <?php if (!empty($card['text'])) : ?>
                                            <p class="mt-4 text-base font-medium leading-7 text-indigo-950">
                                                <?php echo esc_html($card['text']); ?>
                                            </p>
                                        <?php endif; ?>

                                        <div class="flex flex-1 gap-2.5 justify-end items-end mt-4">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M8 4L16 12L8 20" stroke="#001F33" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        <?php else : ?>
                            <div class="w-full rounded-lg">
                                <div class="p-6 w-full min-h-[15rem] max-md:px-5">
                                    <?php if (!empty($card['title'])) : ?>
                                        <h3 class="text-2xl font-semibold tracking-normal leading-8 text-indigo-950">
                                            <?php echo esc_html($card['title']); ?>
                                        </h3>
                                    <?php endif; ?>

                                    <?php if (!empty($card['text'])) : ?>
                                        <p class="mt-4 text-base font-medium leading-7 text-indigo-950">
                                            <?php echo esc_html($card['text']); ?>
                                        </p>
                                    <?php endif; ?>

                                    <div class="flex flex-1 gap-2.5 justify-end items-end mt-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M8 4L16 12L8 20" stroke="#001F33" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </article>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- View More Button -->
            <?php if ($view_more_link && is_array($view_more_link) && !empty($view_more_link['url'])) : ?>
                <a
                    href="<?php echo esc_url($view_more_link['url']); ?>"
                    target="<?php echo esc_attr($view_more_link['target'] ?? '_self'); ?>"
                    class="flex gap-2.5 justify-center items-center self-start px-3 mt-9 text-sm font-medium leading-6 whitespace-nowrap rounded-lg border transition-colors duration-200 btn border-primary-dark min-h-9 text-teal-950 w-fit hover:bg-neutral-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-primary-dark"
                    aria-label="<?php echo esc_attr($view_more_link['title'] ?: 'View more mental health conditions'); ?>"
                >
                    <span class="self-stretch my-auto text-teal-950">
                        <?php echo esc_html($view_more_link['title'] ?: 'View more'); ?>
                    </span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>
