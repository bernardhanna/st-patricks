<?php

/**
 * Anxiety page layout, matching the Depression restyle.
 *
 * Hero keeps the anxiety featured image. Body sections use different photos.
 * Useful links sit at the bottom (renamed from "In this section").
 * Continue-to cards are dropped.
 *
 * @param int $post_id mental_health post ID
 */
function matrix_orlaith_rebuild_anxiety_layout(int $post_id): void
{
    if ($post_id <= 0 || ! function_exists('update_field')) {
        return;
    }

    $existing = get_field('flexible_content_blocks', $post_id);

    if (! is_array($existing) || $existing === []) {
        return;
    }

    $find_image = static function (int $preferred_id, string $filename): int {
        if ($preferred_id > 0 && get_post_type($preferred_id) === 'attachment') {
            return $preferred_id;
        }

        $found = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'title' => preg_replace('/\.[^.]+$/', '', $filename),
        ]);

        return $found !== [] ? (int) $found[0] : 0;
    };

    $hero_image = $find_image(767, 'anxiety-featured-image.png');
    $causes_image = $find_image(2222, 'anxiety-fight-flight-and-future-focused-fears-1.jpg');
    $treatment_image = $find_image(818, 'stress-anxiety-disorder-genes-recovery.jpg');
    $programme_card_image = $find_image(3450, 'anxiety-disorders-in-adolescents.jpg');
    $getting_help_card_image = $find_image(805, 'single-step-mental-health-info.jpg');

    $programme = get_page_by_path('anxiety-disorders-programme', OBJECT, ['care_treatment', 'programmes_therapies', 'page']);
    $programme_url = $programme instanceof WP_Post
        ? (string) get_permalink($programme)
        : home_url('/care-treatment/anxiety-disorders-programme/');

    $getting_help = get_page_by_path('getting-help');
    $getting_help_url = $getting_help instanceof WP_Post
        ? (string) get_permalink($getting_help)
        : home_url('/getting-help/');

    $rewrite = static function (string $html) use ($programme_url): string {
        $html = str_replace('https://st-patricks.s1.matrix-test.com', home_url(), $html);
        $html = str_replace('http://st-patricks.s1.matrix-test.com', home_url(), $html);
        $html = str_replace(home_url('/anxiety-disorders-programme/'), $programme_url, $html);
        $html = str_replace(home_url('/depression/'), matrix_mental_health_condition_url('depression'), $html);
        $html = str_replace(home_url('/addiction-dual-diagnosis/'), matrix_mental_health_condition_url('addiction-dual-diagnosis'), $html);
        $html = str_replace(home_url('/information-centre/'), home_url('/getting-help/information-centre/'), $html);

        return $html;
    };

    $apply_content = static function (array $row, array $overrides) use ($rewrite): array {
        $row['heading_tag'] = 'h2';
        $row['accent_position'] = $row['accent_position'] ?? 'below_heading';
        $row['content'] = $rewrite((string) ($row['content'] ?? ''));
        $row['intro_text'] = $rewrite((string) ($row['intro_text'] ?? ''));
        $row['text_width'] = 'full';
        $row['image_height_mode'] = 'match_text';

        foreach ($overrides as $key => $value) {
            $row[$key] = $value;
        }

        $has_image = (int) ($row['image'] ?? 0) > 0;
        $row['column_layout'] = $has_image ? 'two_column' : 'one_column';

        if (! $has_image) {
            unset($row['layout_style']);
            $row['image'] = '';
        }

        return $row;
    };

    $hero = null;
    $useful = null;
    $what = null;
    $types_heading = null;
    $types_accordion = null;
    $causes = null;
    $treatment = null;
    $resources_heading = null;
    $resources_accordion = null;

    foreach ($existing as $row) {
        $layout = (string) ($row['acf_fc_layout'] ?? '');
        $heading = strtolower(trim((string) ($row['heading'] ?? '')));

        if ($layout === 'hero_with_breadcrumbs' && $hero === null) {
            $hero = $row;
            continue;
        }

        if ($layout === 'useful_links' && $useful === null) {
            $useful = $row;
            continue;
        }

        if ($layout === 'related_cards' && str_contains($heading, 'continue')) {
            continue;
        }

        if ($layout === 'content' && str_contains($heading, 'what is anxiety') && $what === null) {
            $what = $row;
            continue;
        }

        if ($layout === 'content' && str_contains($heading, 'different types') && $types_heading === null) {
            $types_heading = $row;
            continue;
        }

        if ($layout === 'content_accordion' && $types_accordion === null) {
            $types_accordion = $row;
            continue;
        }

        if ($layout === 'content' && str_contains($heading, 'what causes') && $causes === null) {
            $causes = $row;
            continue;
        }

        if ($layout === 'content' && str_contains($heading, 'treatment') && $treatment === null) {
            $treatment = $row;
            continue;
        }

        if ($layout === 'content' && str_contains($heading, 'useful resources') && $resources_heading === null) {
            $resources_heading = $row;
            continue;
        }

        if ($layout === 'content_accordion' && $resources_accordion === null) {
            $resources_accordion = $row;
        }
    }

    if (! is_array($hero)) {
        return;
    }

    $hero['layout_style'] = $hero_image > 0 ? 'image_split' : 'title_accent';
    $hero['hero_image'] = $hero_image > 0 ? $hero_image : '';
    $hero['heading_tag'] = 'h1';
    $hero['current_crumb_label'] = 'Anxiety';
    $hero['primary_button'] = [
        'title' => 'Anxiety Disorders Programme',
        'url' => $programme_url,
        'target' => '',
    ];
    if (! empty($hero['content'])) {
        $hero['content'] = $rewrite((string) $hero['content']);
    }

    if (is_array($useful)) {
        $useful['heading'] = 'Useful links';
        $useful['heading_tag'] = 'h2';

        if (isset($useful['links']) && is_array($useful['links'])) {
            foreach ($useful['links'] as &$link_row) {
                $title = (string) ($link_row['link']['title'] ?? '');
                $slug_map = [
                    'Addiction & Dual Diagnosis' => 'addiction-dual-diagnosis',
                    'Anxiety' => 'anxiety',
                    'Bipolar Disorder' => 'bipolar-disorder',
                    'Depression' => 'depression',
                    'Eating Disorders' => 'eating-disorders',
                    'Personality Disorders' => 'personality-disorders',
                    'Schizophrenia & Psychosis' => 'schizophrenia-psychosis',
                    'Psychosis' => 'schizophrenia-psychosis',
                    'Young Adults' => 'young-adults',
                    'Older Adults' => 'older-adults',
                ];

                if (strcasecmp($title, 'Schizophrenia & Psychosis') === 0) {
                    $link_row['link']['title'] = 'Psychosis';
                }

                if (isset($slug_map[$title]) && function_exists('matrix_mental_health_condition_url')) {
                    $link_row['link']['url'] = matrix_mental_health_condition_url($slug_map[$title]);
                }
            }
            unset($link_row);
        }
    }

    $rewrite_accordion = static function (array $accordion) use ($rewrite): array {
        $accordion['layout_style'] = $accordion['layout_style'] ?? 'default';
        $items = is_array($accordion['items'] ?? null) ? $accordion['items'] : [];

        foreach ($items as &$item) {
            if (! empty($item['content'])) {
                $item['content'] = $rewrite((string) $item['content']);
            }

            if (! empty($item['content_rows']) && is_array($item['content_rows'])) {
                foreach ($item['content_rows'] as &$row) {
                    if (! empty($row['content'])) {
                        $row['content'] = $rewrite((string) $row['content']);
                    }
                }
                unset($row);
            }
        }
        unset($item);

        $accordion['items'] = $items;

        return $accordion;
    };

    $rows = [$hero];

    if (is_array($what)) {
        $rows[] = $apply_content($what, [
            'background_type' => 'white',
            'image' => '',
            'vertical_padding' => 'default',
        ]);
    }

    if (is_array($types_heading)) {
        $rows[] = $apply_content($types_heading, [
            'background_type' => 'cream',
            'image' => '',
            'vertical_padding' => 'no_bottom',
        ]);
    }

    if (is_array($types_accordion)) {
        $types_accordion = $rewrite_accordion($types_accordion);
        $types_accordion['vertical_padding'] = 'small_top_large_bottom';
        $rows[] = $types_accordion;
    }

    if (is_array($causes)) {
        $rows[] = $apply_content($causes, [
            'background_type' => 'white',
            'image' => $causes_image > 0 ? $causes_image : '',
            'layout_style' => 'image_right',
            'vertical_padding' => 'default',
        ]);
    }

    if (is_array($treatment)) {
        $rows[] = $apply_content($treatment, [
            'background_type' => 'cream',
            'image' => $treatment_image > 0 ? $treatment_image : '',
            'layout_style' => 'image_left',
            'vertical_padding' => 'default',
        ]);
    }

    if (is_array($resources_heading)) {
        $rows[] = $apply_content($resources_heading, [
            'background_type' => 'white',
            'image' => '',
            'vertical_padding' => 'no_bottom',
        ]);
    }

    if (is_array($resources_accordion)) {
        $resources_accordion = $rewrite_accordion($resources_accordion);
        $resources_accordion['vertical_padding'] = 'small_top_large_bottom';
        $rows[] = $resources_accordion;
    }

    $find_out_cards = [];

    if ($programme_url !== '') {
        $find_out_cards[] = [
            'image' => $programme_card_image > 0 ? $programme_card_image : '',
            'title' => 'Anxiety Disorders Programme',
            'description' => '',
            'link' => [
                'title' => 'Anxiety Disorders Programme',
                'url' => $programme_url,
                'target' => '',
            ],
        ];
    }

    if ($getting_help_url !== '') {
        $find_out_cards[] = [
            'image' => $getting_help_card_image > 0 ? $getting_help_card_image : '',
            'title' => 'Getting help',
            'description' => '',
            'link' => [
                'title' => 'Getting help',
                'url' => $getting_help_url,
                'target' => '',
            ],
        ];
    }

    if ($find_out_cards !== []) {
        $rows[] = [
            'acf_fc_layout' => 'related_cards',
            'heading' => 'Find out more about treatment options',
            'heading_tag' => 'h2',
            'intro_text' => '',
            'background_color' => '#FBFAF7',
            'columns' => '2',
            'cards' => $find_out_cards,
        ];
    }

    if (is_array($useful)) {
        $rows[] = $useful;
    }

    update_field('hero_content_blocks', [], $post_id);
    delete_field('flexible_content_blocks', $post_id);
    update_field('flexible_content_blocks', $rows, $post_id);
}
