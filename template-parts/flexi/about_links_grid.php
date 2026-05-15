<?php
/**
 * About Links Grid (Flexi Block)
 */

$section_id = 'about-links-grid-' . ( function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid() );

$heading_tag  = get_sub_field('heading_tag') ?: 'h2';
$heading_text = (string) (get_sub_field('heading_text') ?: '');
$intro_text   = (string) (get_sub_field('intro_text') ?: '');
$links        = get_sub_field('links');
$links        = is_array($links) ? $links : [];

$bg_color         = get_sub_field('bg_color') ?: '#FFFFFF';
$heading_color    = get_sub_field('heading_color') ?: '#0B0B08';
$intro_color      = get_sub_field('intro_color') ?: '#4A4B37';
$card_bg_color    = get_sub_field('card_bg_color') ?: '#F9FAFB';
$card_title_color = get_sub_field('card_title_color') ?: '#0B0B08';
$card_desc_color  = get_sub_field('card_desc_color') ?: '#4A4B37';
$columns_raw      = (string) (get_sub_field('columns') ?: '3');
$columns          = preg_match('/[234]/', $columns_raw, $matches) ? $matches[0] : '3';

$allowed_tags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p'];
if (!in_array($heading_tag, $allowed_tags, true)) {
    $heading_tag = 'h2';
}

$column_classes = [
    '2' => 'sm:grid-cols-2',
    '3' => 'sm:grid-cols-2 lg:grid-cols-3',
    '4' => 'sm:grid-cols-2 lg:grid-cols-4',
];
$grid_columns = $column_classes[$columns] ?? 'sm:grid-cols-2 lg:grid-cols-3';
$card_tones = [
    'bg1' => '#CEF2EE',
    'bg2' => '#E4F4D6',
    'bg3' => '#E9E2F7',
    'bg4' => '#F9E5F2',
];

$padding_classes = [];
if (have_rows('padding_settings')) {
    while (have_rows('padding_settings')) {
        the_row();

        $screen_size = get_sub_field('screen_size');
        $padding_top = get_sub_field('padding_top');
        $padding_bottom = get_sub_field('padding_bottom');

        if ($screen_size !== '' && $padding_top !== '' && $padding_top !== null) {
            $padding_classes[] = "{$screen_size}:pt-[{$padding_top}rem]";
        }

        if ($screen_size !== '' && $padding_bottom !== '' && $padding_bottom !== null) {
            $padding_classes[] = "{$screen_size}:pb-[{$padding_bottom}rem]";
        }
    }
}
?>

<section id="<?php echo esc_attr($section_id); ?>"
    data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
    class="flex overflow-hidden relative"
    style="background-color: <?php echo esc_attr($bg_color); ?>;">

    <div class="w-full mx-auto max-w-[1018px] py-[100px] max-xl:px-5 <?php echo esc_attr(implode(' ', $padding_classes)); ?>">
        <?php if ($heading_text || $intro_text) : ?>
            <div class="flex flex-col gap-8 items-start justify-center mb-16 w-full">
                <?php if ($heading_text) : ?>
                    <<?php echo tag_escape($heading_tag); ?>
                        class="text-[30px] font-semibold leading-9 tracking-[-0.225px]"
                        style="color: <?php echo esc_attr($heading_color); ?>;">
                        <?php echo esc_html($heading_text); ?>
                    </<?php echo tag_escape($heading_tag); ?>>
                    <div class="w-10 h-px" style="background-color: #6FC9C0;"></div>
                <?php endif; ?>

                <?php if ($intro_text) : ?>
                    <div class="max-w-2xl text-base font-medium leading-7 wp_editor"
                        style="color: <?php echo esc_attr($intro_color); ?>;">
                        <?php echo wp_kses_post($intro_text); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($links)) : ?>
            <div class="grid grid-cols-1 gap-4 <?php echo esc_attr($grid_columns); ?>">
                <?php foreach ($links as $item) :
                    $icon = $item['icon'] ?? null;
                    $image_url = trim((string) ($item['image_url'] ?? ''));
                    $title = trim((string) ($item['title'] ?? ''));
                    $description = trim((string) ($item['description'] ?? ''));
                    $link = $item['link'] ?? null;
                    $card_tone = (string) ($item['card_tone'] ?? 'bg1');

                    $has_link = is_array($link) && !empty($link['url']);
                    $link_url = $has_link ? $link['url'] : '';
                    $link_target = $has_link ? ($link['target'] ?: '_self') : '_self';
                    $link_title = $has_link ? (string) ($link['title'] ?: 'Learn more') : '';

                    $icon_url = is_array($icon) ? ($icon['url'] ?? '') : '';
                    $icon_alt = is_array($icon) ? ($icon['alt'] ?? ($icon['title'] ?? $title)) : $title;
                    $card_bg = $card_tones[$card_tone] ?? $card_tones['bg1'];

                    if ($image_url === '' && $icon_url === '' && $title === '' && $description === '' && !$has_link) {
                        continue;
                    }

                    $card_inner_classes = 'flex h-full flex-col gap-4 items-start p-6 rounded-lg w-full';
                    $card_wrapper_classes = 'group h-full rounded-lg shadow-sm transition-transform duration-200 hover:-translate-y-0.5';
                    ?>
                    <article class="<?php echo esc_attr($card_wrapper_classes); ?>"
                        style="background-color: <?php echo esc_attr($card_bg); ?>; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);">
                        <?php if ($has_link) : ?>
                            <a href="<?php echo esc_url($link_url); ?>"
                                target="<?php echo esc_attr($link_target); ?>"
                                class="block h-full rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-primary-dark"
                                aria-label="<?php echo esc_attr($link_title ?: $title); ?>">
                        <?php endif; ?>

                        <div class="<?php echo esc_attr($card_inner_classes); ?>">
                            <?php if ($image_url || $icon_url) : ?>
                                <div class="relative h-[129px] rounded-[4px] overflow-hidden w-full">
                                    <?php if ($image_url) : ?>
                                        <img src="<?php echo esc_url($image_url); ?>"
                                            alt="<?php echo esc_attr($title); ?>"
                                            class="absolute inset-0 object-cover w-full h-full">
                                    <?php else : ?>
                                        <div class="flex items-center justify-center w-full h-full bg-white/80">
                                            <img src="<?php echo esc_url($icon_url); ?>"
                                                alt="<?php echo esc_attr($icon_alt); ?>"
                                                class="object-contain w-12 h-12">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="flex items-center justify-between w-full gap-4">
                                <div class="flex-1 min-w-px">
                                    <?php if ($title) : ?>
                                        <h3 class="text-[20px] font-semibold leading-8 tracking-[-0.12px]"
                                            style="color: <?php echo esc_attr($card_title_color); ?>;">
                                            <?php echo esc_html($title); ?>
                                        </h3>
                                    <?php endif; ?>
                                </div>

                                <?php if ($has_link) : ?>
                                    <div class="flex items-center justify-center shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M8 4L16 12L8 20" stroke="#08284B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if ($description) : ?>
                                <p class="text-base font-medium leading-7"
                                    style="color: <?php echo esc_attr($card_desc_color); ?>;">
                                    <?php echo esc_html($description); ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <?php if ($has_link) : ?>
                            </a>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
