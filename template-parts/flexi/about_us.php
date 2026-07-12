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

// Card colours from Figma node 966:5982 (St Patrick's design system tokens).
$cards = [
    [
        'bg_color'     => '#CEF2EE',
        'title'        => get_sub_field('card_1_title'),
        'text'         => get_sub_field('card_1_text'),
        'link'         => get_sub_field('card_1_link'),
    ],
    [
        'bg_color'     => '#E4F4D6',
        'title'        => get_sub_field('card_2_title'),
        'text'         => get_sub_field('card_2_text'),
        'link'         => get_sub_field('card_2_link'),
    ],
    [
        'bg_color'     => '#E9E2F7',
        'title'        => get_sub_field('card_3_title'),
        'text'         => get_sub_field('card_3_text'),
        'link'         => get_sub_field('card_3_link'),
    ],
    [
        'bg_color'     => '#F9E5F2',
        'title'        => get_sub_field('card_4_title'),
        'text'         => get_sub_field('card_4_text'),
        'link'         => get_sub_field('card_4_link'),
    ],
    [
        'bg_color'     => '#FADBD8',
        'title'        => get_sub_field('card_5_title'),
        'text'         => get_sub_field('card_5_text'),
        'link'         => get_sub_field('card_5_link'),
    ],
    [
        'bg_color'     => '#F9F1D1',
        'title'        => get_sub_field('card_6_title'),
        'text'         => get_sub_field('card_6_text'),
        'link'         => get_sub_field('card_6_link'),
    ],
];

// Padding settings


// Section background
$section_bg = get_sub_field('background_color') ?: '#FFFFFF';
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
    class="flex overflow-hidden relative"
    style="background-color: <?php echo esc_attr($section_bg); ?>;"
>
    <div class="flex flex-col items-center w-full mx-auto max-w-container py-12 lg:py-24 max-lg:px-5 max-sm:px-6">

        <!-- Header Section -->
        <?php if (!empty($heading_text)) : ?>
        <div class="flex flex-col justify-center max-w-full text-3xl font-semibold tracking-tight leading-tight text-indigo-950 w-[1018px] max-sm:justify-center max-sm:items-start max-sm:px-4">
            <<?php echo esc_attr($heading_tag); ?> class="text-[#1E244B]">
                <?php echo esc_html($heading_text); ?>
            </<?php echo esc_attr($heading_tag); ?>>

        <div class="mt-6 h-[4px] w-10 bg-[#6FC9C0]"
                 role="presentation"
                 aria-hidden="true"></div>
        </div>
        <?php endif; ?>

        <!-- Main Content Container -->
        <div class="flex flex-col mt-16 max-w-full w-[1018px] max-md:mt-10">
            <!-- Cards + Image -->
            <div class="about-us-columns grid w-full gap-4 items-stretch max-[1084px]:grid-cols-2 max-sm:grid-cols-1 lg:grid-cols-[minmax(240px,315px)_minmax(0,1fr)_minmax(240px,315px)]" role="main">

                <!-- Left Column: first 3 cards -->
                <div class="about-us-cards-col flex h-full flex-col gap-4 lg:justify-between">
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

                        $card_classes = implode(' ', array_filter([
                            'mental-health-card',
                            'flex',
                            'flex-col',
                            'max-w-full',
                            'w-full',
                            'rounded-lg',
                            'border',
                            'transition-all',
                            'duration-300',
                            $has_link ? 'mental-health-card--interactive' : '',
                        ]));
                        $card_style = sprintf(
                            '--card-bg:%s;',
                            esc_attr($card['bg_color'])
                        );
                    ?>
                    <article class="<?php echo esc_attr($card_classes); ?>" style="<?php echo esc_attr($card_style); ?>">
                        <?php if ($has_link) : ?>
                            <a
                                href="<?php echo esc_url($link['url']); ?>"
                                target="<?php echo esc_attr($link['target'] ?? '_self'); ?>"
                                class="flex h-full flex-col rounded-lg p-6 max-md:px-5 max-sm:min-h-[164px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-primary-dark"
                                aria-label="<?php echo esc_attr($link['title'] ?: $card['title']); ?>"
                            >
                                <?php if (!empty($card['title'])) : ?>
                                    <h3 class="mental-health-card__title text-2xl font-semibold tracking-normal leading-8 text-[#1E244B] transition-colors duration-300">
                                        <?php echo esc_html($card['title']); ?>
                                    </h3>
                                <?php endif; ?>

                                <?php if (!empty($card['text'])) : ?>
                                    <p class="mt-4 text-base font-medium leading-7 text-[#1E244B]">
                                        <?php echo esc_html($card['text']); ?>
                                    </p>
                                <?php endif; ?>

                                <div class="about-us-card-arrow mt-auto flex shrink-0 justify-end pt-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="block" aria-hidden="true">
                                        <path d="M8 4L16 12L8 20" stroke="#001F33" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </a>
                        <?php else : ?>
                            <div class="flex h-full flex-col rounded-lg p-6 max-md:px-5 max-sm:min-h-[164px]">
                                <?php if (!empty($card['title'])) : ?>
                                    <h3 class="mental-health-card__title text-2xl font-semibold tracking-normal leading-8 text-[#1E244B] transition-colors duration-300">
                                        <?php echo esc_html($card['title']); ?>
                                    </h3>
                                <?php endif; ?>

                                <?php if (!empty($card['text'])) : ?>
                                    <p class="mt-4 text-base font-medium leading-7 text-[#1E244B]">
                                        <?php echo esc_html($card['text']); ?>
                                    </p>
                                <?php endif; ?>

                                <div class="about-us-card-arrow mt-auto flex shrink-0 justify-end pt-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="block" aria-hidden="true">
                                        <path d="M8 4L16 12L8 20" stroke="#001F33" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </div>
                        <?php endif; ?>
                    </article>
                    <?php endfor; ?>
                </div>

                <!-- Center Image -->
                <?php if (!empty($main_image_url)) : ?>
                <div class="about-us-media relative hidden h-full min-h-0 overflow-hidden rounded-lg lg:block">
                    <img
                        src="<?php echo esc_url($main_image_url); ?>"
                        alt="<?php echo esc_attr($main_image_alt); ?>"
                        class="h-full w-full object-cover"
                    />
                </div>
                <?php endif; ?>

                <!-- Right Column: last 3 cards -->
                <div class="about-us-cards-col flex h-full flex-col gap-4 lg:justify-between">
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

                        $card_classes = implode(' ', array_filter([
                            'mental-health-card',
                            'flex',
                            'flex-col',
                            'max-w-full',
                            'w-full',
                            'rounded-lg',
                            'border',
                            'transition-all',
                            'duration-300',
                            $has_link ? 'mental-health-card--interactive' : '',
                        ]));
                        $card_style = sprintf(
                            '--card-bg:%s;',
                            esc_attr($card['bg_color'])
                        );
                    ?>
                    <article class="<?php echo esc_attr($card_classes); ?>" style="<?php echo esc_attr($card_style); ?>">
                        <?php if ($has_link) : ?>
                            <a
                                href="<?php echo esc_url($link['url']); ?>"
                                target="<?php echo esc_attr($link['target'] ?? '_self'); ?>"
                                class="flex h-full flex-col rounded-lg p-6 max-md:px-5 max-sm:min-h-[164px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-primary-dark"
                                aria-label="<?php echo esc_attr($link['title'] ?: $card['title']); ?>"
                            >
                                <?php if (!empty($card['title'])) : ?>
                                    <h3 class="mental-health-card__title text-2xl font-semibold tracking-normal leading-8 text-[#1E244B] transition-colors duration-300">
                                        <?php echo esc_html($card['title']); ?>
                                    </h3>
                                <?php endif; ?>

                                <?php if (!empty($card['text'])) : ?>
                                    <p class="mt-4 text-base font-medium leading-7 text-[#1E244B]">
                                        <?php echo esc_html($card['text']); ?>
                                    </p>
                                <?php endif; ?>

                                <div class="about-us-card-arrow mt-auto flex shrink-0 justify-end pt-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="block" aria-hidden="true">
                                        <path d="M8 4L16 12L8 20" stroke="#001F33" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </a>
                        <?php else : ?>
                            <div class="flex h-full flex-col rounded-lg p-6 max-md:px-5 max-sm:min-h-[164px]">
                                <?php if (!empty($card['title'])) : ?>
                                    <h3 class="mental-health-card__title text-2xl font-semibold tracking-normal leading-8 text-[#1E244B] transition-colors duration-300">
                                        <?php echo esc_html($card['title']); ?>
                                    </h3>
                                <?php endif; ?>

                                <?php if (!empty($card['text'])) : ?>
                                    <p class="mt-4 text-base font-medium leading-7 text-[#1E244B]">
                                        <?php echo esc_html($card['text']); ?>
                                    </p>
                                <?php endif; ?>

                                <div class="about-us-card-arrow mt-auto flex shrink-0 justify-end pt-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="block" aria-hidden="true">
                                        <path d="M8 4L16 12L8 20" stroke="#001F33" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
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
                    class="flex gap-2.5 justify-center items-center self-start px-3 mt-9 text-sm font-medium leading-6 whitespace-nowrap rounded-lg border border-[#024B79] transition-colors duration-200 btn min-h-9 text-[#08284B] w-fit hover:bg-neutral-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-primary-dark"
                    aria-label="<?php echo esc_attr($view_more_link['title'] ?: 'View more mental health conditions'); ?>"
                >
                    <span class="self-stretch my-auto text-[#08284B]">
                        <?php echo esc_html($view_more_link['title'] ?: 'View more'); ?>
                    </span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
    #<?php echo esc_attr($section_id); ?> .about-us-columns {
        align-items: stretch;
    }

    #<?php echo esc_attr($section_id); ?> .mental-health-card {
        background-color: var(--card-bg);
        border-color: var(--card-bg);
    }

    #<?php echo esc_attr($section_id); ?> .mental-health-card > a,
    #<?php echo esc_attr($section_id); ?> .mental-health-card > div {
        background-color: inherit;
    }

    #<?php echo esc_attr($section_id); ?> .mental-health-card--interactive:hover,
    #<?php echo esc_attr($section_id); ?> .mental-health-card--interactive:focus-within {
        background-color: #ffffff;
        border-color: #e2e8f0;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }

    #<?php echo esc_attr($section_id); ?> .mental-health-card--interactive:hover .mental-health-card__title,
    #<?php echo esc_attr($section_id); ?> .mental-health-card--interactive:focus-within .mental-health-card__title {
        color: #024b79;
    }

    @media (min-width: 1085px) {
        #<?php echo esc_attr($section_id); ?> .about-us-columns {
            min-height: 0;
        }

        #<?php echo esc_attr($section_id); ?> .about-us-cards-col,
        #<?php echo esc_attr($section_id); ?> .about-us-media {
            height: 100%;
        }

        #<?php echo esc_attr($section_id); ?> .mental-health-card {
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.05);
        }

        #<?php echo esc_attr($section_id); ?> .about-us-card-arrow {
            padding-bottom: 2px;
        }

        #<?php echo esc_attr($section_id); ?> .about-us-card-arrow svg {
            overflow: visible;
        }
    }
</style>
