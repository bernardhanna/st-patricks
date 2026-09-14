<?php

/**
 * Depression page layout from Orlaith's August draft.
 *
 * Hero uses the depression featured image. Body sections use distinct photos.
 * Useful links sit at the bottom. Continue-to cards are dropped.
 *
 * @param int $post_id mental_health post ID
 */
function matrix_orlaith_rebuild_depression_layout(int $post_id): void
{
    if ($post_id <= 0 || ! function_exists('update_field')) {
        return;
    }

    $rewrite = static function (string $html): string {
        $html = str_replace('https://st-patricks.s1.matrix-test.com', home_url(), $html);
        $html = str_replace('http://st-patricks.s1.matrix-test.com', home_url(), $html);

        return $html;
    };

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

    $hero_image = $find_image(758, 'depression-featured-image.jpg');
    $symptoms_image = $find_image(2242, '10-warning-signs-of-teenage-depression-1.jpg');
    $treatment_image = $find_image(1087, 'talking-to-people-with-depression.jpg');

    $programme = get_page_by_path('depression-recovery-programme', OBJECT, ['programmes_therapies', 'page']);
    $programme_url = $programme instanceof WP_Post
        ? (string) get_permalink($programme)
        : home_url('/programmes-therapies/depression-recovery-programme/');

    $existing = get_field('flexible_content_blocks', $post_id);
    $useful = null;
    $find_out_more = null;
    if (is_array($existing)) {
        foreach ($existing as $row) {
            $layout = (string) ($row['acf_fc_layout'] ?? '');
            if ($layout === 'useful_links' && $useful === null) {
                $useful = $row;
            }
            if ($layout === 'related_cards' && $find_out_more === null) {
                $heading = strtolower((string) ($row['heading'] ?? ''));
                if (str_contains($heading, 'continue')) {
                    continue;
                }
                $find_out_more = $row;
                $find_out_more['columns'] = '2';
                $find_out_more['heading_tag'] = 'h2';
            }
        }
    }

    if (is_array($useful)) {
        $useful['heading'] = 'Useful links';
        $useful['heading_tag'] = 'h2';
        if (isset($useful['links']) && is_array($useful['links'])) {
            foreach ($useful['links'] as &$link_row) {
                $title = (string) ($link_row['link']['title'] ?? '');
                if (strcasecmp($title, 'Depression') === 0 && function_exists('matrix_mental_health_condition_url')) {
                    $link_row['link']['url'] = matrix_mental_health_condition_url('depression');
                }
                if (strcasecmp($title, 'Schizophrenia & Psychosis') === 0 && function_exists('matrix_mental_health_condition_url')) {
                    $link_row['link']['title'] = 'Psychosis';
                    $link_row['link']['url'] = matrix_mental_health_condition_url('schizophrenia-psychosis');
                }
            }
            unset($link_row);
        }
    }

    $content = static function (
        string $heading,
        string $html,
        string $layout = 'image_left',
        int $image_id = 0,
        string $background = 'white',
        string $vertical_padding = 'default'
    ) use ($rewrite): array {
        $has_image = $image_id > 0;
        $row = [
            'acf_fc_layout' => 'content',
            'heading' => $heading,
            'heading_tag' => 'h2',
            'accent_position' => 'below_heading',
            'intro_text' => '',
            'content' => $rewrite($html),
            'background_type' => $background,
            'column_layout' => $has_image ? 'two_column' : 'one_column',
            'image_height_mode' => 'match_text',
            'text_width' => 'full',
            'image' => $has_image ? $image_id : '',
            'vertical_padding' => $vertical_padding,
        ];
        if ($has_image) {
            $row['layout_style'] = $layout;
        }

        return $row;
    };

    $hero = [
        'acf_fc_layout' => 'hero_with_breadcrumbs',
        'layout_style' => $hero_image > 0 ? 'image_split' : 'title_accent',
        'show_breadcrumbs' => 1,
        'breadcrumb_source' => 'auto',
        'current_crumb_label' => 'Depression',
        'heading_tag' => 'h1',
        'heading' => 'Depression',
        'content' => '<p>Depression is a common mood disorder which affects how you feel, think and act.</p>',
        'hero_image' => $hero_image > 0 ? $hero_image : '',
        'primary_button' => [
            'title' => 'Depression Recovery Programme',
            'url' => $programme_url,
            'target' => '',
        ],
        'text_max_width' => 'default',
        'heading_max_width' => 'default',
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
    ];

    $rows = [
        $hero,
        $content(
            'Overview of depression',
            '<p>Everyone feels sad or fed up from time to time. However, these feelings usually last only a few days. Depression is where these feelings are severe or long-lasting. It leaves you feeling down most of the time and finding it hard to cope from day to day.</p>'
            . '<p>Depression affects roughly one in 10 people in the population. It has a number of possible causes, including our genes, hormones and chemicals in our bodies, or our backgrounds. Periods of depression may also be triggered by significant life events, such as work or financial stress, exams, relationship changes, family conflict, or concerns around identity or sexual orientation.</p>'
            . '<p>Most people with depression make good recoveries and live full lives with the right care and support.</p>',
            'image_left',
            0,
            'white'
        ),
        $content(
            'Symptoms of depression',
            '<p>Depression can affect people in different ways and bring diverse symptoms which impact our psychological, physical and social wellbeing.</p>'
            . '<p>These symptoms, if we do not get support for them, can have negative effects in our lives, such as employment issues, strain on relationships, drug and alcohol use, and thoughts of suicide.</p>'
            . '<p>In general, a person may be diagnosed with depression if they experience five or more symptoms for more than two weeks.</p>',
            'image_left',
            0,
            'cream'
        ),
        $content(
            'Symptoms of depression include:',
            '<ul>'
            . '<li>feelings of overwhelming sadness and hopelessness</li>'
            . '<li>a loss of interest or pleasure in everyday activities or hobbies that are usually enjoyed</li>'
            . '<li>difficulties with sleep, such as being unable to fall asleep, waking early, feeling overly tired or having no energy to get out of bed</li>'
            . '<li>changes in appetite, such as reduced appetite and weight loss, or increased food cravings and weight gain</li>'
            . '<li>physical pains, such as headaches or muscle aches</li>'
            . '<li>recurrent thoughts of death, suicide or <a href="' . esc_url(home_url('/self-harm-causes-and-supports/')) . '">self-harm</a>.</li>'
            . '</ul>'
            . '<p>If you have thoughts of self-harm or suicide and are in immediate distress, please contact the emergency services by calling 999 in Ireland or 112 anywhere in Europe.</p>',
            'image_right',
            $symptoms_image,
            'white'
        ),
        $content(
            'Treatment for depression',
            '<p>Depression is very treatable. When you have received the right diagnosis and your individual needs have been properly assessed, recovery can begin within weeks of beginning a treatment plan.</p>'
            . '<p>If you or someone you love is experiencing symptoms of depression, start by talking to your GP. They can give you guidance and may recommend that you are <a href="' . esc_url(home_url('/what-we-offer/')) . '">referred for assessment</a> at a community mental health service or as an inpatient in hospital.</p>'
            . '<p>Treatment for depression can involve a number of approaches. Psychological supports can include therapies like <a href="' . esc_url(home_url('/programmes-therapies/cognitive-behavioural-therapy/')) . '">Cognitive Behavioural Therapy</a> (CBT) and <a href="' . esc_url(home_url('/programmes-therapies/compassion-focused-therapy/')) . '">Compassion-Focused Therapy</a> (CFT). Self-help and self-management practices, such as <a href="https://www.youtube.com/watch?v=wYAUm3XL7dM&amp;t=172s">mindfulness</a>, can also be very helpful. In addition, some people may take a course of <a href="' . esc_url(home_url('/service-users-and-visitors/medication/')) . '">medication</a> to manage their symptoms.</p>'
            . '<p><a href="' . esc_url($programme_url) . '">You can find out more about our Depression Recovery Programme here.</a></p>',
            'image_left',
            $treatment_image,
            'cream'
        ),
        $content(
            'Useful resources',
            '',
            'image_left',
            0,
            'white',
            'no_bottom'
        ),
        [
            'acf_fc_layout' => 'content_accordion',
            'layout_style' => 'default',
            'vertical_padding' => 'small_top_large_bottom',
            'items' => [
                [
                    'title' => 'Organisations and support groups',
                    'starts_open' => 1,
                    'content_rows' => [[
                        'row_type' => 'text',
                        'icon_key' => '',
                        'icon' => '',
                        'content' => $rewrite(
                            '<p>You can find out more about some organisations and support groups for people living with depression and their families below.</p>'
                            . '<ul>'
                            . '<li><a href="https://www.aware.ie/information/depression/">Aware</a></li>'
                            . '<li><a href="https://www2.hse.ie/mental-health/">Health Service Executive | Your Mental Health</a></li>'
                            . '<li><a href="https://www.mentalhealthireland.ie/a-to-z/d/#depression">Mental Health Ireland</a></li>'
                            . '<li><a href="https://www.pieta.ie/">Pieta</a></li>'
                            . '<li><a href="https://www.samaritans.org/ireland/how-we-can-help/if-youre-having-difficult-time/">Samaritans</a></li>'
                            . '</ul>'
                        ),
                    ]],
                ],
                [
                    'title' => 'Books',
                    'starts_open' => 0,
                    'content_rows' => [[
                        'row_type' => 'text',
                        'icon_key' => '',
                        'icon' => '',
                        'content' => $rewrite(
                            '<p>If you are interested in reading more about depression, you may find the books below helpful. You can also check our <a href="' . esc_url(home_url('/locations/st-patricks-university-hospital/')) . '">Information Centre in St Patrick’s University Hospital</a> for more information and a wider selection of books.</p>'
                            . '<ul>'
                            . '<li><em>Depression: The Common Sense Approach</em> | Tony Bates</li>'
                            . '<li><em>Mind Over Mood</em> | Dennis Greenberger and Christine A Padeskey</li>'
                            . '<li><em>Overcoming Depression</em> | Paul Gilbert</li>'
                            . '<li><em>Depression: Your Questions Answered</em> | Melvyn Lurie</li>'
                            . '</ul>'
                        ),
                    ]],
                ],
            ],
        ],
    ];

    if (is_array($find_out_more)) {
        $rows[] = $find_out_more;
    }
    if (is_array($useful)) {
        $rows[] = $useful;
    }

    update_field('hero_content_blocks', [], $post_id);
    delete_field('flexible_content_blocks', $post_id);
    update_field('flexible_content_blocks', $rows, $post_id);
}
