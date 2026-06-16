<?php
/**
 * About Links Grid (Flexi Block)
 * Figma: 2780:3450 (About Us landing grid)
 */

$section_id = 'about-links-grid-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());

$heading_tag  = get_sub_field('heading_tag') ?: 'h2';
$heading_text = (string) (get_sub_field('heading_text') ?: '');
$intro_text   = (string) (get_sub_field('intro_text') ?: '');
$links        = get_sub_field('links');
$links        = is_array($links) ? $links : [];

$bg_color         = get_sub_field('bg_color') ?: '#FFFFFF';
$heading_color    = get_sub_field('heading_color') ?: '#1E244B';
$intro_color      = get_sub_field('intro_color') ?: '#08284B';
$card_bg_color    = (string) (get_sub_field('card_bg_color') ?: '#F1F8F9');
$card_title_color = get_sub_field('card_title_color') ?: '#1E244B';
$card_desc_color  = get_sub_field('card_desc_color') ?: '#08284B';
$columns_raw      = (string) (get_sub_field('columns') ?: '3');
$columns          = preg_match('/[234]/', $columns_raw, $matches) ? $matches[0] : '3';

$allowed_tags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p'];
if (! in_array($heading_tag, $allowed_tags, true)) {
    $heading_tag = 'h2';
}

$column_classes = [
    '2' => 'lg:grid-cols-2',
    '3' => 'lg:grid-cols-3',
    '4' => 'lg:grid-cols-4',
];
$grid_columns = $column_classes[$columns] ?? 'lg:grid-cols-3';

?>

<section id="<?php echo esc_attr($section_id); ?>"
    data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
    class="flex overflow-hidden relative"
    style="background-color: <?php echo esc_attr($bg_color); ?>;">

    <div class="mx-auto flex w-full max-w-[1018px] flex-col gap-8 px-4 py-12 lg:gap-8 lg:py-[100px] xl:px-0">
        <?php if ($heading_text || $intro_text) : ?>
            <div class="flex w-full flex-col gap-8">
                <?php if ($heading_text) : ?>
                    <<?php echo tag_escape($heading_tag); ?>
                        class="font-primary text-[24px] font-semibold leading-[28px] tracking-[-0.18px] lg:text-[30px] lg:leading-[36px] lg:tracking-[-0.225px]"
                        style="color: <?php echo esc_attr($heading_color); ?>;">
                        <?php echo esc_html($heading_text); ?>
                    </<?php echo tag_escape($heading_tag); ?>>
                    <div class="h-[4px] w-10 bg-[#6FC9C0]" aria-hidden="true"></div>
                <?php endif; ?>

                <?php if ($intro_text) : ?>
                    <div class="max-w-2xl font-primary text-base font-medium leading-7 wp_editor"
                        style="color: <?php echo esc_attr($intro_color); ?>;">
                        <?php echo matrix_kses_rich_text($intro_text); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (! empty($links)) : ?>
            <div class="grid grid-cols-1 gap-4 <?php echo esc_attr($grid_columns); ?> lg:gap-x-8 lg:gap-y-4">
                <?php foreach ($links as $item) :
                    $icon = $item['icon'] ?? null;
                    $image_url = trim((string) ($item['image_url'] ?? ''));
                    $title = trim((string) ($item['title'] ?? ''));
                    $description = trim((string) ($item['description'] ?? ''));
                    $link = $item['link'] ?? null;
                    $card_tone = trim((string) ($item['card_tone'] ?? ''));
                    $footer_background = matrix_get_about_links_grid_card_footer_background($card_tone, $card_bg_color);

                    $has_link = is_array($link) && ! empty($link['url']);
                    $link_url = $has_link ? $link['url'] : '';
                    $link_target = $has_link ? ($link['target'] ?: '_self') : '_self';
                    $link_title = $has_link ? (string) ($link['title'] ?: $title) : $title;

                    $icon_url = is_array($icon) ? ($icon['url'] ?? '') : '';
                    $icon_alt = is_array($icon) ? ($icon['alt'] ?? ($icon['title'] ?? $title)) : $title;

                    if ($image_url === '' && $icon_url === '' && $title === '' && $description === '' && ! $has_link) {
                        continue;
                    }

                    $card_tag = $has_link ? 'a' : 'div';
                    $card_attrs = $has_link
                        ? sprintf(
                            ' href="%s" target="%s"%s',
                            esc_url($link_url),
                            esc_attr($link_target),
                            $link_target === '_blank' ? ' rel="noopener noreferrer"' : ''
                        )
                        : '';
                    ?>
                    <article class="h-full overflow-hidden rounded-lg bg-white shadow-[0_1px_1px_rgba(0,0,0,0.05)]">
                        <<?php echo $card_tag; ?>
                            <?php echo $card_attrs; ?>
                            class="group flex h-full flex-col focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#024B79]<?php echo $has_link ? '' : ' pointer-events-none'; ?>"
                            <?php if ($has_link) { ?>
                                aria-label="<?php echo esc_attr($link_title); ?>"
                            <?php } ?>
                        >
                            <?php if ($image_url || $icon_url) : ?>
                                <div class="relative h-[220px] w-full overflow-hidden lg:h-[273px]">
                                    <?php if ($image_url) : ?>
                                        <img src="<?php echo esc_url($image_url); ?>"
                                            alt="<?php echo esc_attr($title); ?>"
                                            class="absolute inset-0 h-full w-full object-cover transition-transform duration-300 ease-out group-hover:scale-[1.03]"
                                            loading="lazy"
                                            decoding="async">
                                    <?php else : ?>
                                        <div class="flex h-full w-full items-center justify-center bg-[#F1F8F9]">
                                            <img src="<?php echo esc_url($icon_url); ?>"
                                                alt="<?php echo esc_attr($icon_alt); ?>"
                                                class="h-12 w-12 object-contain"
                                                loading="lazy"
                                                decoding="async">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div
                                class="flex flex-1 items-center p-6"
                                style="background-color: <?php echo esc_attr($footer_background); ?>;"
                            >
                                <div class="flex min-w-0 w-full flex-col gap-2">
                                    <?php if ($title) : ?>
                                        <div class="flex w-full items-center justify-between gap-4">
                                            <h3 class="min-w-0 font-primary text-[20px] font-semibold leading-6 tracking-[-0.12px] transition-colors group-hover:text-[#024B79]"
                                                style="color: <?php echo esc_attr($card_title_color); ?>;">
                                                <?php echo esc_html($title); ?>
                                            </h3>
                                            <?php if ($has_link) : ?>
                                                <span class="shrink-0 text-[#1E244B] transition-colors group-hover:text-[#024B79]" aria-hidden="true">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                                        <path d="M6 3L12 9L6 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($description) : ?>
                                        <p class="font-primary text-base font-medium leading-7"
                                            style="color: <?php echo esc_attr($card_desc_color); ?>;">
                                            <?php echo esc_html($description); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </<?php echo $card_tag; ?>>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
