<?php

/**
 * Eating disorders mental_health page layout.
 *
 * Types and causes headings sit on their accordion groups (not separate
 * sections). Useful resources uses the cream accordion heading. Causes sit
 * below Useful resources. Related / Continue-to cards are dropped.
 *
 * @param int $post_id mental_health post ID
 */
function matrix_orlaith_rebuild_eating_disorders_layout(int $post_id): void
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

    $hero = null;
    $useful = null;
    $overview = null;
    $types_accordion = null;
    $signs = null;
    $help = null;
    $treatment = null;
    $resources_accordion = null;
    $causes_accordion = null;
    $leftovers = [];

    foreach ($existing as $row) {
        if (! is_array($row)) {
            continue;
        }

        $layout = (string) ($row['acf_fc_layout'] ?? '');
        $heading = $heading_of($row);

        if ($layout === 'related_cards') {
            continue;
        }

        if ($layout === 'hero_with_breadcrumbs' && $hero === null) {
            $hero = $row;
            continue;
        }

        if ($layout === 'useful_links' && $useful === null) {
            $useful = $row;
            continue;
        }

        if ($layout === 'content' && str_contains($heading, 'overview') && $overview === null) {
            $overview = $row;
            continue;
        }

        if ($layout === 'content' && str_contains($heading, 'what are the types of eating disorders')) {
            continue;
        }

        if ($layout === 'content' && str_contains($heading, 'further information')) {
            continue;
        }

        if ($layout === 'content' && str_contains($heading, 'what causes eating disorders')) {
            continue;
        }

        if ($layout === 'content' && str_contains($heading, 'useful resources')) {
            continue;
        }

        if ($layout === 'content' && str_contains($heading, 'what are the signs') && $signs === null) {
            $signs = $row;
            continue;
        }

        if ($layout === 'content' && str_contains($heading, 'where can i get help') && $help === null) {
            $help = $row;
            continue;
        }

        if ($layout === 'content' && str_contains($heading, 'how are eating disorders treated') && $treatment === null) {
            $treatment = $row;
            continue;
        }

        if ($layout === 'content_accordion') {
            $item_blob = $item_blob_of($row);

            if ($types_accordion === null && (str_contains($item_blob, 'anorexia') || str_contains($heading, 'types of eating'))) {
                $types_accordion = $apply_accordion_title($row, 'What are the types of eating disorders?', '#FBFAF7');
                continue;
            }

            if ($causes_accordion === null && (str_contains($item_blob, 'genetic') || str_contains($heading, 'what causes'))) {
                $causes_accordion = $apply_accordion_title($row, 'What causes eating disorders?', '#FBFAF7');
                continue;
            }

            if ($resources_accordion === null && (str_contains($item_blob, 'websites') || str_contains($heading, 'useful resources'))) {
                $resources_accordion = $apply_accordion_title($row, 'Useful resources', '#FBFAF7');
                continue;
            }
        }

        $leftovers[] = $row;
    }

    if (! is_array($hero)) {
        return;
    }

    $rows = [$hero];

    if (is_array($overview)) {
        $rows[] = $overview;
    }

    if (is_array($types_accordion)) {
        $rows[] = $types_accordion;
    }

    if (is_array($signs)) {
        $rows[] = $signs;
    }

    if (is_array($help)) {
        $rows[] = $help;
    }

    if (is_array($treatment)) {
        $rows[] = $treatment;
    }

    if (is_array($resources_accordion)) {
        $rows[] = $resources_accordion;
    }

    if (is_array($causes_accordion)) {
        $rows[] = $causes_accordion;
    }

    foreach ($leftovers as $row) {
        $rows[] = $row;
    }

    if (is_array($useful)) {
        $rows[] = $useful;
    }

    update_field('hero_content_blocks', [], $post_id);
    delete_field('flexible_content_blocks', $post_id);
    update_field('flexible_content_blocks', $rows, $post_id);
}
