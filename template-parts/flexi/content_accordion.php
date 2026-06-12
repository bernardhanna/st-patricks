<?php

$section_id = 'content-accordion-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());
$layout_style = function_exists('matrix_resolve_content_accordion_layout_style')
    ? matrix_resolve_content_accordion_layout_style(get_sub_field('layout_style'))
    : 'default';
$layout_config = function_exists('matrix_get_content_accordion_layout_config')
    ? matrix_get_content_accordion_layout_config($layout_style)
    : matrix_get_content_accordion_layout_config('default');
$raw_items = get_sub_field('items');
$section_background = (string) get_sub_field('section_background');
$panel_background = (string) get_sub_field('panel_background');
$open_panel_background = (string) get_sub_field('open_panel_background');
$icon_tile_background_color = (string) get_sub_field('icon_tile_background_color');

$accordion_data = matrix_normalize_content_accordion_items($raw_items, $layout_style);
$items = $accordion_data['items'];
$initial_open_index = (int) $accordion_data['initial_open_index'];

if ($items === []) {
    return;
}

if ($section_background === '') {
    $section_background = $layout_config['section_background'];
}

if ($panel_background === '') {
    $panel_background = $layout_config['panel_background'];
}

if ($open_panel_background === '') {
    $open_panel_background = $layout_config['open_panel_background'];
}

if ($icon_tile_background_color === '') {
    $icon_tile_background_color = $layout_config['icon_tile_background_color'];
}

$panel_background_style = matrix_get_content_accordion_background_style($panel_background, $layout_config['panel_background']);
$open_panel_background_style = matrix_get_content_accordion_background_style(
    $open_panel_background,
    $layout_config['open_panel_background']
);
$section_background_style = matrix_get_content_accordion_background_style($section_background, '#FFFFFF');

$padding_classes = [];
if ($layout_style !== 'directions_page' && have_rows('padding_settings')) {
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

$wrapper_classes = trim(implode(' ', array_unique(array_merge(
    explode(' ', $layout_config['wrapper_classes']),
    $padding_classes
))));
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
    class="relative flex overflow-hidden"
    style="<?php echo esc_attr($section_background_style); ?>"
>
    <div
        x-data="{ activeIndex: <?php echo esc_attr((string) $initial_open_index); ?>, toggleItem(index) { this.activeIndex = this.activeIndex === index ? -1 : index; } }"
        class="<?php echo esc_attr($wrapper_classes); ?>"
    >
        <?php foreach ($items as $index => $item) { ?>
            <?php
            $button_id = $section_id . '-button-' . $index;
            $panel_id = $section_id . '-panel-' . $index;
            ?>
            <div
                class="<?php echo esc_attr($layout_config['item_classes']); ?>"
                :style="activeIndex === <?php echo esc_attr((string) $index); ?> ? '<?php echo esc_js($open_panel_background_style); ?>' : '<?php echo esc_js($panel_background_style); ?>'"
            >
                <button
                    type="button"
                    id="<?php echo esc_attr($button_id); ?>"
                    class="<?php echo esc_attr($layout_config['button_classes']); ?> focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#024B79]"
                    :aria-expanded="activeIndex === <?php echo esc_attr((string) $index); ?> ? 'true' : 'false'"
                    aria-controls="<?php echo esc_attr($panel_id); ?>"
                    @click="toggleItem(<?php echo esc_attr((string) $index); ?>)"
                >
                    <span
                        class="<?php echo esc_attr($layout_config['title_classes']); ?>"
                        :class="activeIndex === <?php echo esc_attr((string) $index); ?> ? 'text-[#08284B]' : ''"
                    >
                        <?php echo esc_html($item['title']); ?>
                    </span>

                    <span
                        class="shrink-0 transition-transform duration-200"
                        :class="activeIndex === <?php echo esc_attr((string) $index); ?> ? 'rotate-180' : ''"
                        aria-hidden="true"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M4 6L8 10L12 6" stroke="#1E244B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </button>

                <div
                    id="<?php echo esc_attr($panel_id); ?>"
                    x-show="activeIndex === <?php echo esc_attr((string) $index); ?>"
                    x-cloak
                    aria-labelledby="<?php echo esc_attr($button_id); ?>"
                    class="<?php echo esc_attr($layout_config['panel_body_classes']); ?>"
                >
                    <div class="<?php echo esc_attr($layout_config['rows_wrapper_classes']); ?>">
                        <?php foreach ($item['rows'] as $row) { ?>
                            <?php if ($layout_style === 'policies_page') { ?>
                                <?php
                                get_template_part('template-parts/flexi/partials/policies-accordion-row', null, [
                                    'row' => $row,
                                    'content_classes' => $layout_config['content_classes'],
                                ]);
                                ?>
                            <?php continue; } ?>
                            <?php
                            $row_icon_url = trim((string) ($row['icon']['url'] ?? ''));
                            $row_icon_key = trim((string) ($row['icon_key'] ?? ''));
                            $has_row_icon = $row_icon_url !== ''
                                || ($row_icon_key !== '' && function_exists('matrix_get_content_accordion_icon_svg'));
                            ?>
                            <div class="<?php echo esc_attr($layout_config['row_classes']); ?>">
                                <?php if ($has_row_icon && ($layout_config['icon_tile_classes'] ?? '') !== '') { ?>
                                    <div
                                        class="<?php echo esc_attr($layout_config['icon_tile_classes']); ?>"
                                        style="background-color: <?php echo esc_attr($icon_tile_background_color); ?>;"
                                    >
                                        <?php if ($row_icon_url !== '') { ?>
                                            <img
                                                src="<?php echo esc_url($row_icon_url); ?>"
                                                alt="<?php echo esc_attr($row['icon']['alt']); ?>"
                                                class="<?php echo esc_attr($layout_config['icon_image_classes'] ?? 'h-6 w-6 object-contain'); ?>"
                                            />
                                        <?php } else { ?>
                                            <span class="<?php echo esc_attr($layout_config['icon_image_classes'] ?? 'h-6 w-6 object-contain'); ?> flex items-center justify-center [&_svg]:h-full [&_svg]:w-full">
                                                <?php echo matrix_get_content_accordion_icon_svg($row_icon_key); ?>
                                            </span>
                                        <?php } ?>
                                    </div>
                                <?php } ?>

                                <div class="<?php echo esc_attr($layout_config['content_classes']); ?>">
                                    <?php echo matrix_kses_rich_text((string) ($row['content'] ?? '')); ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</section>
