<?php

/**
 * Bipolar disorder mental_health page layout.
 *
 * Signs heading sits on the symptoms accordion. Useful resources heading sits
 * on the organisations accordion with the cream background. Related /
 * Continue-to cards are dropped.
 *
 * @param int $post_id mental_health post ID
 */
function matrix_orlaith_rebuild_bipolar_layout(int $post_id): void
{
    if ($post_id <= 0 || ! function_exists('update_field')) {
        return;
    }

    $existing = get_field('flexible_content_blocks', $post_id);

    if (! is_array($existing) || $existing === []) {
        return;
    }

    $heading_of = static function (array $row): string {
        return strtolower(trim(wp_strip_all_tags((string) ($row['heading'] ?? ''))));
    };

    $item_blob_of = static function (array $row): string {
        $titles = [];
        foreach ((array) ($row['items'] ?? []) as $item) {
            $titles[] = strtolower(trim((string) ($item['title'] ?? '')));
        }

        return implode(' ', $titles);
    };

    $apply_accordion_title = static function (array $accordion, string $heading, string $section_background): array {
        $accordion['heading'] = $heading;
        $accordion['heading_tag'] = 'h2';
        $accordion['section_background'] = $section_background;

        if (($accordion['layout_style'] ?? '') === '') {
            $accordion['layout_style'] = 'default';
        }

        return $accordion;
    };

    $normalise_content = static function (array $row): array {
        $has_image = (int) ($row['image'] ?? 0) > 0;
        if (! $has_image) {
            $row['column_layout'] = 'one_column';
        }
        $row['heading_tag'] = 'h2';

        return $row;
    };

    $rows = [];
    $pending_signs_heading = false;
    $pending_resources_heading = false;

    foreach ($existing as $row) {
        if (! is_array($row)) {
            continue;
        }

        $layout = (string) ($row['acf_fc_layout'] ?? '');
        $heading = $heading_of($row);

        if ($layout === 'related_cards') {
            continue;
        }

        if ($layout === 'content' && (str_contains($heading, 'signs of bipolar') || str_contains($heading, 'what are the signs'))) {
            $pending_signs_heading = true;
            continue;
        }

        if ($layout === 'content' && str_contains($heading, 'useful resources')) {
            $pending_resources_heading = true;
            continue;
        }

        if ($layout === 'content_accordion') {
            $item_blob = $item_blob_of($row);

            if (
                $pending_signs_heading
                || str_contains($heading, 'signs of bipolar')
                || str_contains($item_blob, 'symptoms of elation')
            ) {
                $rows[] = $apply_accordion_title($row, 'Signs of bipolar disorder', '#FBFAF7');
                $pending_signs_heading = false;
                continue;
            }

            if (
                $pending_resources_heading
                || str_contains($heading, 'useful resources')
                || str_contains($item_blob, 'information and support')
                || str_contains($item_blob, 'organisations and support')
            ) {
                $rows[] = $apply_accordion_title($row, 'Useful resources', '#FBFAF7');
                $pending_resources_heading = false;
                continue;
            }
        }

        if ($layout === 'content') {
            $row = $normalise_content($row);
        }

        if ($layout === 'useful_links') {
            $row['heading'] = 'Useful links';
            $row['heading_tag'] = 'h2';
        }

        $rows[] = $row;
    }

    update_field('hero_content_blocks', [], $post_id);
    delete_field('flexible_content_blocks', $post_id);
    update_field('flexible_content_blocks', $rows, $post_id);
}
