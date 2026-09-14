<?php

/**
 * Shared "Our present and future" content block for the Our History page.
 */

if (! function_exists('matrix_our_history_present_future_block')) {
    /**
     * Empty content block for editors to add copy under the timeline.
     *
     * @return array<string, mixed>
     */
    function matrix_our_history_present_future_block(): array
    {
        return [
            'acf_fc_layout' => 'content',
            'heading' => 'Our present and future',
            'heading_tag' => 'h2',
            'accent_position' => 'below_heading',
            'intro_text' => '',
            'content' => '<p>[Placeholder — add final copy about our present and future here.]</p>',
            'primary_button' => '',
            'document_link' => '',
            'secondary_button' => '',
            'image' => '',
            'background_type' => 'color',
            'background_color' => '#FBFAF7',
            'background_gradient' => '',
            'background_image' => '',
            'background_image_overlay_color' => '',
            'background_image_overlay_opacity' => '50',
            'color_scheme' => 'default',
            'column_layout' => 'one_column',
            'layout_style' => 'image_left',
            'image_height_mode' => 'match_text',
            'text_width' => 'full',
            'reverse_layout' => 0,
            'vertical_padding' => 'default',
        ];
    }
}

if (! function_exists('matrix_our_history_is_present_future_block')) {
    function matrix_our_history_is_present_future_block(array $row): bool
    {
        return ($row['acf_fc_layout'] ?? '') === 'content'
            && trim((string) ($row['heading'] ?? '')) === 'Our present and future';
    }
}

if (! function_exists('matrix_our_history_present_future_block_needs_sync')) {
    function matrix_our_history_present_future_block_needs_sync(array $row): bool
    {
        if (! matrix_our_history_is_present_future_block($row)) {
            return false;
        }

        if (($row['column_layout'] ?? '') !== 'one_column') {
            return true;
        }

        if (array_key_exists('padding_settings', $row)) {
            return true;
        }

        if (! array_key_exists('vertical_padding', $row)) {
            return true;
        }

        return false;
    }
}

if (! function_exists('matrix_our_history_ensure_present_future_block')) {
    /**
     * Insert or normalise the present/future block directly after the timeline.
     *
     * @return array{added: bool, synced: bool, block_count: int}
     */
    function matrix_our_history_ensure_present_future_block(int $post_id): array
    {
        if ($post_id <= 0 || ! function_exists('get_field') || ! function_exists('update_field')) {
            return ['added' => false, 'synced' => false, 'block_count' => 0];
        }

        $rows = get_field('flexible_content_blocks', $post_id);

        if (! is_array($rows)) {
            $rows = [];
        }

        $added = false;
        $synced = false;
        $present_future = matrix_our_history_present_future_block();
        $present_future_index = null;

        foreach ($rows as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            if (matrix_our_history_is_present_future_block($row)) {
                $present_future_index = $index;
                break;
            }
        }

        if ($present_future_index === null) {
            $insert_at = count($rows);

            foreach ($rows as $index => $row) {
                if (is_array($row) && ($row['acf_fc_layout'] ?? '') === 'timeline') {
                    $insert_at = $index + 1;
                    break;
                }
            }

            array_splice($rows, $insert_at, 0, [$present_future]);
            $added = true;
        } elseif (matrix_our_history_present_future_block_needs_sync($rows[$present_future_index])) {
            $existing = $rows[$present_future_index];
            $rows[$present_future_index] = array_merge(
                $present_future,
                [
                    'intro_text' => (string) ($existing['intro_text'] ?? ''),
                    'content' => (string) ($existing['content'] ?? ''),
                    'primary_button' => $existing['primary_button'] ?? '',
                    'document_link' => $existing['document_link'] ?? '',
                    'secondary_button' => $existing['secondary_button'] ?? '',
                    'image' => $existing['image'] ?? '',
                ]
            );
            $synced = true;
        }

        if ($added || $synced) {
            update_field('flexible_content_blocks', $rows, $post_id);
        }

        return [
            'added' => $added,
            'synced' => $synced,
            'block_count' => count($rows),
        ];
    }
}
