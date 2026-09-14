<?php
/**
 * About Links Grid (Flexi Block)
 * Figma: 2780:3450 grid, 2780:3457 card (desktop), 2780:6686 (mobile).
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
$allow_title_wrap = (bool) get_sub_field('allow_title_wrap');
$layout_style     = function_exists('matrix_resolve_about_links_grid_layout_style')
    ? matrix_resolve_about_links_grid_layout_style(get_sub_field('layout_style'))
    : 'image_feature';
$columns          = function_exists('matrix_resolve_about_links_grid_columns')
    ? matrix_resolve_about_links_grid_columns($columns_raw)
    : (preg_match('/[234]/', $columns_raw, $matches) ? $matches[0] : '3');

$allowed_tags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'p'];
if (! in_array($heading_tag, $allowed_tags, true)) {
    $heading_tag = 'h2';
}

$grid_classes = function_exists('matrix_get_about_links_grid_grid_class_names')
    ? matrix_get_about_links_grid_grid_class_names($layout_style, $columns)
    : 'grid grid-cols-1 gap-4 lg:grid-cols-3 lg:gap-x-8 lg:gap-y-4';

$card_partial = function_exists('matrix_get_about_links_grid_card_partial')
    ? matrix_get_about_links_grid_card_partial($layout_style)
    : ($layout_style === 'compact_row'
        ? 'template-parts/flexi/partials/about-links-grid-card-compact-row'
        : 'template-parts/flexi/partials/about-links-grid-card-image-feature');

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
    data-about-links-grid-layout="<?php echo esc_attr($layout_style); ?>"
    class="flex overflow-hidden relative"
    style="background-color: <?php echo esc_attr($bg_color); ?>;">

    <div class="mx-auto flex w-full max-w-[1018px] flex-col gap-8 px-4 py-12 lg:gap-8 lg:py-[100px] xl:px-0 <?php echo esc_attr(implode(' ', $padding_classes)); ?>">
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
            <div class="<?php echo esc_attr($grid_classes); ?>">
                <?php foreach ($links as $item) :
                    $icon = $item['icon'] ?? null;
                    $image_url = trim((string) ($item['image_url'] ?? ''));
                    $title = trim((string) ($item['title'] ?? ''));
                    $description = trim((string) ($item['description'] ?? ''));
                    $link = $item['link'] ?? null;
                    $card_tone = trim((string) ($item['card_tone'] ?? ''));
                    $footer_background = function_exists('matrix_get_about_links_grid_card_background')
                        ? matrix_get_about_links_grid_card_background($layout_style, $card_tone, $card_bg_color)
                        : matrix_get_about_links_grid_card_footer_background($card_tone, $card_bg_color);

                    $has_link = is_array($link) && ! empty($link['url']);
                    $link_url = $has_link ? $link['url'] : '';
                    $link_target = $has_link ? ($link['target'] ?: '_self') : '_self';
                    $link_title = $has_link ? (string) ($link['title'] ?: $title) : $title;

                    $icon_url = is_array($icon) ? ($icon['url'] ?? '') : '';
                    $icon_alt = is_array($icon) ? ($icon['alt'] ?? ($icon['title'] ?? $title)) : $title;

                    if ($image_url === '' && $icon_url === '' && $title === '' && $description === '' && ! $has_link) {
                        continue;
                    }

                    get_template_part($card_partial, null, [
                        'card' => [
                            'title' => $title,
                            'description' => $description,
                            'image_url' => $image_url,
                            'icon_url' => $icon_url,
                            'icon_alt' => $icon_alt,
                            'has_link' => $has_link,
                            'link_url' => $link_url,
                            'link_target' => $link_target,
                            'link_title' => $link_title,
                            'footer_background' => $footer_background,
                            'card_title_color' => $card_title_color,
                            'card_desc_color' => $card_desc_color,
                            'allow_title_wrap' => $allow_title_wrap,
                        ],
                    ]);
                endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
