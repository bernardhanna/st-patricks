<?php

/**
 * Addiction & dual diagnosis mental_health page layout.
 *
 * Signs of addiction become accordions. Dual diagnosis heading sits on the
 * mood-disorders accordion. Useful resources heading sits on the organisations
 * accordion with the cream background. Related / Continue-to cards are dropped.
 *
 * @param int $post_id mental_health post ID
 */
function matrix_orlaith_rebuild_addiction_layout(int $post_id): void
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

    $split_h3_sections = static function (string $html): array {
        $html = trim($html);
        if ($html === '') {
            return [];
        }

        $chunks = preg_split('/<h3\b[^>]*>/i', $html);
        if (! is_array($chunks) || count($chunks) < 2) {
            return [];
        }

        $items = [];

        foreach ($chunks as $index => $chunk) {
            if ($index === 0) {
                continue;
            }

            if (! preg_match('/^(.*?)<\/h3>(.*)$/is', $chunk, $match)) {
                continue;
            }

            $title = trim(wp_strip_all_tags(html_entity_decode((string) $match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8')));
            $content = trim((string) $match[2]);

            if ($title === '' || $content === '') {
                continue;
            }

            $items[] = [
                'title' => $title,
                'starts_open' => 0,
                'content_rows' => [[
                    'row_type' => 'text',
                    'icon_key' => '',
                    'icon' => '',
                    'content' => $content,
                ]],
            ];
        }

        return $items;
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

    $rows = [];
    $pending_dual_heading = false;
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

        if ($layout === 'content' && str_contains($heading, 'what are the signs of an addiction')) {
            $items = $split_h3_sections((string) ($row['content'] ?? ''));
            if ($items === []) {
                $rows[] = $row;
                continue;
            }

            $background = strtoupper(trim((string) ($row['background_color'] ?? '')));
            if ($background === '') {
                $background = '#FBFAF7';
            }

            $rows[] = [
                'acf_fc_layout' => 'content_accordion',
                'layout_style' => 'default',
                'heading' => 'What are the signs of an addiction?',
                'heading_tag' => 'h2',
                'vertical_padding' => 'default',
                'section_background' => $background,
                'panel_background' => '#FFFFFF',
                'open_panel_background' => 'linear-gradient(-42.77deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
                'icon_tile_background_color' => '#FFFFFF',
                'items' => $items,
            ];
            continue;
        }

        if ($layout === 'content_accordion' && str_contains($heading, 'what are the signs of an addiction')) {
            $rows[] = $apply_accordion_title($row, 'What are the signs of an addiction?', '#FBFAF7');
            continue;
        }

        if ($layout === 'content' && str_contains($heading, 'what is dual diagnosis')) {
            $pending_dual_heading = true;
            continue;
        }

        if ($layout === 'content' && str_contains($heading, 'useful resources')) {
            $pending_resources_heading = true;
            continue;
        }

        if ($layout === 'content_accordion') {
            $item_titles = [];
            foreach ((array) ($row['items'] ?? []) as $item) {
                $item_titles[] = strtolower(trim((string) ($item['title'] ?? '')));
            }
            $item_blob = implode(' ', $item_titles);

            if ($pending_dual_heading || str_contains($item_blob, 'mood disorders')) {
                $rows[] = $apply_accordion_title($row, 'What is dual diagnosis?', '#FBFAF7');
                $pending_dual_heading = false;
                continue;
            }

            if ($pending_resources_heading || str_contains($item_blob, 'organisations and support')) {
                $rows[] = $apply_accordion_title($row, 'Useful resources', '#FBFAF7');
                $pending_resources_heading = false;
                continue;
            }
        }

        $rows[] = $row;
    }

    update_field('hero_content_blocks', [], $post_id);
    delete_field('flexible_content_blocks', $post_id);
    update_field('flexible_content_blocks', $rows, $post_id);
}
