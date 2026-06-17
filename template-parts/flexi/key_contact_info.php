<?php

$section_id = 'key-contact-info-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());
$normalized_columns = matrix_normalize_key_contact_info_columns(get_sub_field('columns'));
$columns = $normalized_columns['columns'];
$initial_open_index = (int) $normalized_columns['initial_open_index'];
$section_background = (string) get_sub_field('section_background');
$closed_panel_background = (string) get_sub_field('closed_panel_background');
$open_panel_background = (string) get_sub_field('open_panel_background');

if ($columns === []) {
    return;
}

if ($section_background === '') {
    $section_background = '#FFFFFF';
}

if ($closed_panel_background === '') {
    $closed_panel_background = '#FBFAF7';
}

if ($open_panel_background === '') {
    $open_panel_background = 'linear-gradient(-79.46deg, #F8F6F3 3.24%, #F5F6ED 90.88%)';
}

$section_background_style = matrix_get_key_contact_info_background_style($section_background, '#FFFFFF');
$closed_panel_background_style = matrix_get_key_contact_info_background_style($closed_panel_background, '#FBFAF7');
$open_panel_background_style = matrix_get_key_contact_info_background_style($open_panel_background, 'linear-gradient(-79.46deg, #F8F6F3 3.24%, #F5F6ED 90.88%)');

?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
    class="flex overflow-hidden relative"
    style="<?php echo esc_attr($section_background_style); ?>"
>
    <div
        x-data="{ activeIndex: <?php echo esc_attr((string) $initial_open_index); ?>, toggleItem(index) { this.activeIndex = this.activeIndex === index ? -1 : index; } }"
        class="<?php echo esc_attr(matrix_get_key_contact_info_wrapper_class_names()); ?>"
    >
        <div class="<?php echo esc_attr(matrix_get_key_contact_info_grid_class_names()); ?>">
            <?php foreach ($columns as $column_index => $column) { ?>
                <div class="<?php echo esc_attr(matrix_get_key_contact_info_column_class_names()); ?>">
                    <?php foreach ($column['items'] as $item_index => $item) { ?>
                        <?php
                        $flat_index = (int) ($item['flat_index'] ?? 0);
                        $button_id = $section_id . '-button-' . $flat_index;
                        $panel_id = $section_id . '-panel-' . $flat_index;
                        $has_panel_content = matrix_key_contact_info_item_has_panel_content($item);
                        ?>
                        <div
                            class="<?php echo esc_attr(matrix_get_key_contact_info_item_class_names()); ?>"
                            :style="activeIndex === <?php echo esc_attr((string) $flat_index); ?> ? '<?php echo esc_js($open_panel_background_style); ?>' : '<?php echo esc_js($closed_panel_background_style); ?>'"
                        >
                            <button
                                type="button"
                                id="<?php echo esc_attr($button_id); ?>"
                                class="<?php echo esc_attr(matrix_get_key_contact_info_header_class_names()); ?>"
                                :aria-expanded="activeIndex === <?php echo esc_attr((string) $flat_index); ?> ? 'true' : 'false'"
                                <?php if ($has_panel_content) { ?>
                                    aria-controls="<?php echo esc_attr($panel_id); ?>"
                                <?php } ?>
                                @click="toggleItem(<?php echo esc_attr((string) $flat_index); ?>)"
                            >
                                <span class="<?php echo esc_attr(matrix_get_key_contact_info_title_class_names()); ?>">
                                    <?php echo esc_html($item['title']); ?>
                                </span>

                                <?php if ($has_panel_content) { ?>
                                    <span
                                        class="shrink-0 text-[#1E244B] transition-transform duration-200"
                                        :class="activeIndex === <?php echo esc_attr((string) $flat_index); ?> ? 'rotate-180' : ''"
                                        aria-hidden="true"
                                    >
                                        <?php echo matrix_get_key_contact_info_chevron_svg(); ?>
                                    </span>
                                <?php } ?>
                            </button>

                            <?php if ($has_panel_content) { ?>
                                <div
                                    id="<?php echo esc_attr($panel_id); ?>"
                                    x-show="activeIndex === <?php echo esc_attr((string) $flat_index); ?>"
                                    x-cloak
                                    aria-labelledby="<?php echo esc_attr($button_id); ?>"
                                >
                                    <?php
                                    get_template_part(
                                        'template-parts/flexi/partials/key-contact-info-contact-details',
                                        null,
                                        ['item' => $item]
                                    );
                                    ?>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>
</section>
