<?php

/**
 * Psychosis mental_health page layout (slug schizophrenia-psychosis).
 *
 * Symptom and diagnosis-type headings sit on their accordion groups.
 * Useful resources uses the cream accordion heading. Related / Continue-to
 * cards are dropped.
 *
 * @param int $post_id mental_health post ID
 */
function matrix_orlaith_rebuild_psychosis_layout(int $post_id): void
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
            $row['image'] = '';
        }
        $row['heading_tag'] = 'h2';

        $heading = strtolower(trim(wp_strip_all_tags((string) ($row['heading'] ?? ''))));
        if (str_contains($heading, 'treatments are available for schizophrenia')) {
            $row['heading'] = 'What treatments are available for psychosis?';
        }

        return $row;
    };

    $rows = [];
    $pending_symptoms_heading = false;
    $pending_types_heading = false;
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

        if ($layout === 'hero_with_breadcrumbs' && (($row['heading'] ?? '') !== '')) {
            $row['heading'] = 'Psychosis';
            $row['current_crumb_label'] = 'Psychosis';
        }

        if ($layout === 'content' && str_contains($heading, 'what are the symptoms of psychosis')) {
            $pending_symptoms_heading = true;
            continue;
        }

        if ($layout === 'content' && str_contains($heading, 'what mental health difficulties have psychosis')) {
            $pending_types_heading = true;
            continue;
        }

        if ($layout === 'content' && str_contains($heading, 'useful resources')) {
            $pending_resources_heading = true;
            continue;
        }

        if ($layout === 'content_accordion') {
            $item_blob = $item_blob_of($row);

            if (
                $pending_symptoms_heading
                || str_contains($heading, 'what are the symptoms of psychosis')
                || str_contains($item_blob, 'hallucinations')
            ) {
                $rows[] = $apply_accordion_title($row, 'What are the symptoms of psychosis?', '#FBFAF7');
                $pending_symptoms_heading = false;
                continue;
            }

            if (
                $pending_types_heading
                || str_contains($heading, 'what mental health difficulties have psychosis')
                || str_contains($item_blob, 'schizophreniform')
                || str_contains($item_blob, 'delusional disorder')
            ) {
                $rows[] = $apply_accordion_title(
                    $row,
                    'What mental health difficulties have psychosis as a symptom?',
                    '#FBFAF7'
                );
                $pending_types_heading = false;
                continue;
            }

            if (
                $pending_resources_heading
                || str_contains($heading, 'useful resources')
                || str_contains($item_blob, 'websites')
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
