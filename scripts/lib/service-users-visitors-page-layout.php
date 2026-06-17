<?php

/**
 * Shared flexi design options for Service Users and Visitors subpages that mirror
 * About our St Patrick's at Home Service (Figma 2888:5419 layout pattern).
 */

if (! function_exists('matrix_get_service_users_visitors_flexi_design_by_index')) {
    /**
     * Design-only field values keyed by flexi row index (0-based).
     *
     * @return array<int, array<string, string>>
     */
    function matrix_get_service_users_visitors_flexi_design_by_index(): array
    {
        return [
            0 => [
                'text_max_width' => 'wide',
                'heading_max_width' => 'full',
            ],
            1 => [
                'heading_tag' => 'h2',
                'column_layout' => 'two_column',
                'text_width' => 'full',
                'vertical_padding' => 'default',
                'layout_style' => 'image_left',
                'accent_position' => 'below_heading',
                'image_height_mode' => 'match_text',
                'color_scheme' => 'default',
            ],
            2 => [
                'heading_tag' => 'h3',
                'column_layout' => 'one_column',
                'text_width' => 'full',
                'vertical_padding' => 'no_bottom',
                'layout_style' => 'image_left',
                'accent_position' => 'below_heading',
                'image_height_mode' => 'match_text',
                'color_scheme' => 'default',
            ],
            3 => [
                'vertical_padding' => 'small_top_large_bottom',
                'layout_style' => 'default',
            ],
            4 => [
                'heading_tag' => 'h4',
                'text_max_width' => 'full',
                'vertical_padding' => 'default',
                'layout_style' => 'feature_slider',
                'video_surface_size' => 'default',
            ],
            5 => [
                'heading_tag' => 'h5',
            ],
        ];
    }
}

if (! function_exists('matrix_merge_flexi_design_options')) {
    /**
     * Merge design fields into flexi rows without overwriting content.
     *
     * @param array<int, array<string, mixed>> $rows
     * @param array<int, array<string, mixed>> $design_by_index
     * @return array<int, array<string, mixed>>
     */
    function matrix_merge_flexi_design_options(array $rows, array $design_by_index): array
    {
        foreach ($rows as $index => &$row) {
            if (! is_array($row) || ! isset($design_by_index[$index])) {
                continue;
            }

            $row = array_merge($row, $design_by_index[$index]);
            unset($row['padding_settings']);
        }
        unset($row);

        return $rows;
    }
}

if (! function_exists('matrix_apply_service_users_visitors_flexi_layout')) {
    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    function matrix_apply_service_users_visitors_flexi_layout(array $rows): array
    {
        return matrix_merge_flexi_design_options(
            $rows,
            matrix_get_service_users_visitors_flexi_design_by_index()
        );
    }
}
