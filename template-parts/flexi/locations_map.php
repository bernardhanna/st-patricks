<?php

if (! defined('ABSPATH')) {
    exit;
}

$section_id = 'locations-map-' . (function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid());
$heading = trim((string) (get_sub_field('heading') ?: 'Find us'));
$heading_tag = (string) (get_sub_field('heading_tag') ?: 'h2');
$intro_text = get_sub_field('intro_text');
$source_mode = (string) (get_sub_field('source_mode') ?: 'all');
$selected_locations = get_sub_field('selected_locations');
$directions_link = get_sub_field('directions_link');
$map_center_lat = get_sub_field('map_center_lat');
$map_center_lng = get_sub_field('map_center_lng');
$map_zoom = get_sub_field('map_zoom');
$tile_provider = matrix_resolve_locations_map_tile_provider(get_sub_field('tile_provider'));
$tile_api_key = matrix_get_locations_map_tile_api_key(get_sub_field('tile_api_key'));
$background_color = (string) (get_sub_field('background_color') ?: '#FFFFFF');

$map_center_lat = $map_center_lat !== null && $map_center_lat !== '' ? (float) $map_center_lat : 53.42;
$map_center_lng = $map_center_lng !== null && $map_center_lng !== '' ? (float) $map_center_lng : -7.69;
$map_zoom = $map_zoom !== null && $map_zoom !== '' ? (int) $map_zoom : 7;

$markers = matrix_resolve_locations_map_markers($source_mode, $selected_locations);

if ($markers === []) {
    return;
}

$initial_location_id = (int) ($markers[0]['id'] ?? 0);
$show_intro = matrix_locations_map_has_visible_intro($intro_text);
$has_directions_link = is_array($directions_link)
    && ! empty($directions_link['url'])
    && ! empty($directions_link['title']);
$accent_markup = '<div class="h-[4px] w-10 bg-[#6FC9C0]" aria-hidden="true"></div>';
$show_header = $heading !== '' || $show_intro || $has_directions_link;
?>

<section
    id="<?php echo esc_attr($section_id); ?>"
    data-matrix-block="<?php echo esc_attr(str_replace('_', '-', get_row_layout()) . '-' . get_row_index()); ?>"
    class="<?php echo esc_attr(matrix_get_locations_map_section_wrapper_class_names()); ?>"
    style="background-color: <?php echo esc_attr($background_color); ?>;"
    aria-labelledby="<?php echo esc_attr($section_id); ?>-heading"
    x-data="{ activeLocationId: <?php echo esc_attr((string) $initial_location_id); ?> }"
    @matrix-location-focus.window="if ($event.detail.sectionId === '<?php echo esc_js($section_id); ?>') activeLocationId = $event.detail.locationId"
>
    <?php if ($show_header) { ?>
        <div class="<?php echo esc_attr(matrix_get_locations_map_header_wrapper_class_names()); ?>">
            <header class="<?php echo esc_attr(matrix_get_locations_map_header_class_names()); ?>">
                <div class="<?php echo esc_attr(matrix_get_locations_map_header_title_row_class_names()); ?>">
                    <?php if ($heading !== '') { ?>
                        <div class="<?php echo esc_attr(matrix_get_locations_map_header_title_group_class_names()); ?>">
                            <<?php echo esc_attr($heading_tag); ?>
                                id="<?php echo esc_attr($section_id); ?>-heading"
                                class="<?php echo esc_attr(matrix_get_locations_map_heading_class_names()); ?>"
                            >
                                <?php echo esc_html($heading); ?>
                            </<?php echo esc_attr($heading_tag); ?>>
                            <?php echo $accent_markup; ?>
                        </div>
                    <?php } ?>

                    <?php if ($has_directions_link) { ?>
                        <?php $directions_target = (string) ($directions_link['target'] ?? '_self'); ?>
                        <div class="<?php echo esc_attr(matrix_get_locations_map_directions_button_wrapper_class_names()); ?>">
                            <a
                                href="<?php echo esc_url($directions_link['url']); ?>"
                                class="<?php echo esc_attr(matrix_get_locations_map_directions_link_class_names()); ?>"
                                target="<?php echo esc_attr($directions_target); ?>"
                                <?php if ($directions_target === '_blank') { ?>
                                    rel="noopener noreferrer"
                                <?php } ?>
                            >
                                <?php echo esc_html($directions_link['title']); ?>
                            </a>
                        </div>
                    <?php } ?>
                </div>

                <?php if ($show_intro) { ?>
                    <div class="<?php echo esc_attr(matrix_get_locations_map_intro_wrapper_class_names()); ?>">
                        <div class="<?php echo esc_attr(matrix_get_locations_map_intro_class_names()); ?>">
                        <?php
                        if (function_exists('matrix_kses_rich_text')) {
                            echo matrix_kses_rich_text($intro_text);
                        } else {
                            echo wp_kses_post((string) $intro_text);
                        }
                        ?>
                        </div>
                    </div>
                <?php } ?>
            </header>
        </div>
    <?php } ?>

    <div class="<?php echo esc_attr(matrix_get_locations_map_stage_class_names()); ?>">
        <div class="<?php echo esc_attr(matrix_get_locations_map_map_shell_class_names()); ?>">
            <div
                id="<?php echo esc_attr($section_id); ?>-map"
                class="<?php echo esc_attr(matrix_get_locations_map_map_class_names($tile_provider)); ?>"
                data-locations-leaflet
                data-provider="<?php echo esc_attr($tile_provider); ?>"
                data-token="<?php echo esc_attr($tile_api_key); ?>"
                data-lat="<?php echo esc_attr((string) $map_center_lat); ?>"
                data-lng="<?php echo esc_attr((string) $map_center_lng); ?>"
                data-zoom="<?php echo esc_attr((string) $map_zoom); ?>"
                data-section-id="<?php echo esc_attr($section_id); ?>"
                role="application"
                aria-label="<?php echo esc_attr__('Interactive map showing SPMHS locations', 'matrix-starter'); ?>"
            ></div>

            <script type="application/json" id="<?php echo esc_attr($section_id); ?>-markers">
<?php echo wp_json_encode($markers); ?>
            </script>
        </div>

        <div class="<?php echo esc_attr(matrix_get_locations_map_overlay_row_class_names()); ?>">
            <div class="<?php echo esc_attr(matrix_get_locations_map_panel_column_class_names()); ?>">
                <div
                    class="<?php echo esc_attr(matrix_get_locations_map_panel_card_class_names()); ?>"
                    style="<?php echo esc_attr(matrix_get_locations_map_panel_background_style()); ?>"
                >
                    <div class="<?php echo esc_attr(matrix_get_locations_map_panel_scrollbar_class_names()); ?>" aria-hidden="true">
                        <div class="<?php echo esc_attr(matrix_get_locations_map_panel_scrollbar_track_class_names()); ?>">
                            <div class="<?php echo esc_attr(matrix_get_locations_map_panel_scrollbar_thumb_class_names()); ?>"></div>
                        </div>
                    </div>

                    <div
                        id="<?php echo esc_attr($section_id); ?>-panel"
                        class="<?php echo esc_attr(matrix_get_locations_map_panel_scroll_class_names()); ?>"
                        tabindex="0"
                        role="region"
                        aria-label="<?php echo esc_attr__('Location details', 'matrix-starter'); ?>"
                    >
                        <?php foreach ($markers as $index => $marker) { ?>
                            <?php
                            $location_id = (int) ($marker['id'] ?? 0);
                            $item_classes = matrix_get_locations_map_panel_item_class_names();

                            if ($index > 0) {
                                $item_classes .= ' ' . matrix_get_locations_map_location_divider_class_names();
                            }
                            ?>
                            <article
                                id="<?php echo esc_attr($section_id . '-location-' . $location_id); ?>"
                                class="<?php echo esc_attr($item_classes); ?>"
                                data-location-id="<?php echo esc_attr((string) $location_id); ?>"
                                :class="activeLocationId === <?php echo esc_attr((string) $location_id); ?> ? 'locations-map-panel__item--active' : ''"
                                @click="activeLocationId = <?php echo esc_attr((string) $location_id); ?>; window.matrixLocationsMapFocus && window.matrixLocationsMapFocus('<?php echo esc_js($section_id); ?>', <?php echo esc_js((string) $location_id); ?>);"
                            >
                                <h4 class="locations-map-panel__location-title <?php echo esc_attr(matrix_get_locations_map_location_title_class_names()); ?>">
                                    <?php echo esc_html((string) ($marker['title'] ?? '')); ?>
                                </h4>

                                <?php
                                get_template_part(
                                    'template-parts/flexi/partials/locations-map-location-details',
                                    null,
                                    ['marker' => $marker]
                                );
                                ?>
                            </article>
                        <?php } ?>
                    </div>
                </div>

                <?php if ($has_directions_link) { ?>
                    <?php $mobile_directions_target = (string) ($directions_link['target'] ?? '_self'); ?>
                    <div class="<?php echo esc_attr(matrix_get_locations_map_mobile_directions_wrapper_class_names()); ?>">
                        <a
                            href="<?php echo esc_url($directions_link['url']); ?>"
                            class="<?php echo esc_attr(matrix_get_locations_map_directions_link_class_names()); ?>"
                            target="<?php echo esc_attr($mobile_directions_target); ?>"
                            <?php if ($mobile_directions_target === '_blank') { ?>
                                rel="noopener noreferrer"
                            <?php } ?>
                        >
                            <?php echo esc_html($directions_link['title']); ?>
                        </a>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</section>

<?php get_template_part('template-parts/partials/locations-map-script', null, ['section_id' => $section_id]); ?>
