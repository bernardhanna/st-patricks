<?php

$section_id = 'contact-directory-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());
$heading = trim((string) (get_sub_field('heading') ?: 'Contact us'));
$heading_tag = (string) (get_sub_field('heading_tag') ?: 'h2');
$intro_text = get_sub_field('intro_text');
$auto_location_mode = (string) (get_sub_field('auto_location_mode') ?: 'none');
$auto_locations = get_sub_field('auto_locations');
$normalized_columns = matrix_normalize_contact_directory_columns(
    get_sub_field('columns'),
    $auto_locations,
    $auto_location_mode
);
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

$show_intro = matrix_contact_directory_has_visible_intro($intro_text);
$section_background_style = matrix_get_key_contact_info_background_style($section_background, '#FFFFFF');
$closed_panel_background_style = matrix_get_key_contact_info_background_style($closed_panel_background, '#FBFAF7');
$open_panel_background_style = matrix_get_key_contact_info_background_style($open_panel_background, 'linear-gradient(-79.46deg, #F8F6F3 3.24%, #F5F6ED 90.88%)');

$allowed_heading_tags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];

if (! in_array($heading_tag, $allowed_heading_tags, true)) {
    $heading_tag = 'h2';
}

$heading_id = $section_id . '-heading';
$accent_markup = '<div class="h-[4px] w-10 bg-[#6FC9C0]" aria-hidden="true"></div>';
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
    class="<?php echo esc_attr(matrix_get_contact_directory_section_wrapper_class_names()); ?>"
    style="<?php echo esc_attr($section_background_style); ?>"
    aria-labelledby="<?php echo esc_attr($heading_id); ?>"
>
    <div
        x-data="{ activeIndex: <?php echo esc_attr((string) $initial_open_index); ?>, toggleItem(index) { this.activeIndex = this.activeIndex === index ? -1 : index; } }"
        class="<?php echo esc_attr(matrix_get_contact_directory_wrapper_class_names()); ?>"
    >
        <div class="<?php echo esc_attr(matrix_get_contact_directory_grid_class_names()); ?>">
            <div class="<?php echo esc_attr(matrix_get_contact_directory_intro_column_class_names()); ?>">
                <<?php echo esc_attr($heading_tag); ?>
                    id="<?php echo esc_attr($heading_id); ?>"
                    class="<?php echo esc_attr(matrix_get_contact_directory_heading_class_names()); ?>"
                >
                    <?php echo esc_html($heading); ?>
                </<?php echo esc_attr($heading_tag); ?>>
                <?php echo $accent_markup; ?>

                <?php if ($show_intro) { ?>
                    <div class="<?php echo esc_attr(matrix_get_contact_directory_intro_body_class_names()); ?>">
                        <?php
                        if (function_exists('matrix_kses_rich_text')) {
                            echo matrix_kses_rich_text($intro_text);
                        } else {
                            echo wp_kses_post((string) $intro_text);
                        }
                        ?>
                    </div>
                <?php } ?>
            </div>

            <?php foreach ($columns as $column) { ?>
                <div class="<?php echo esc_attr(matrix_get_contact_directory_column_class_names()); ?>">
                    <?php foreach ($column['items'] as $item) { ?>
                        <?php
                        $flat_index = (int) ($item['flat_index'] ?? 0);
                        $button_id = $section_id . '-button-' . $flat_index;
                        $panel_id = $section_id . '-panel-' . $flat_index;
                        $has_panel_content = matrix_contact_directory_item_has_panel_content($item);
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
                                        'template-parts/flexi/partials/contact-directory-contact-details',
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
