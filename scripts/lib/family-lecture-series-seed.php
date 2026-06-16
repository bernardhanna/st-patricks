<?php

if (! function_exists('matrix_family_lecture_series_items')) {
    /**
     * @return array<int, array{old_path: string, slug: string, title: string, description: string, image: string}>
     */
    function matrix_family_lecture_series_items(): array
    {
        return [
            [
                'old_path' => 'media-centre/events/2022/february/family-lecture-series-adolescent-mental-health',
                'slug' => 'family-lecture-series-adolescent-mental-health',
                'title' => 'Family Information Series: Adolescent Mental Health',
                'description' => 'Watch our family information webinar on adolescent mental health.',
                'image' => '/media/3385/website-family-webinar-adolescent-mental-health-3.png',
            ],
            [
                'old_path' => 'media-centre/events/2022/march/family-lecture-series-bipolar-disorder',
                'slug' => 'family-lecture-series-bipolar-disorder',
                'title' => 'Family Information Series: Understanding Bipolar Affective Disorder',
                'description' => 'Watch our webinar on understanding bipolar affective disorder from a family perspective.',
                'image' => '/media/3397/website-family-webinar-6-bipolar-570-310-px.png',
            ],
            [
                'old_path' => 'media-centre/events/2022/april/family-lecture-series-supporting-someone-with-anxiety',
                'slug' => 'family-lecture-series-supporting-someone-with-anxiety',
                'title' => 'Family Information Series: Supporting a loved one with anxiety',
                'description' => 'Watch our webinar on supporting a loved one with anxiety.',
                'image' => '/media/3403/website-family-webinar-7-anxiety-570-310-px.png',
            ],
            [
                'old_path' => 'media-centre/events/2022/may/family-lecture-series-personality-disorders-and-recovery',
                'slug' => 'family-lecture-series-personality-disorders-and-recovery',
                'title' => 'Family Information Series: Personality disorders and recovery',
                'description' => 'Watch our webinar on personality disorders and recovery.',
                'image' => '/media/3452/_website-family-lecture-series-personality-disorder-2.png',
            ],
            [
                'old_path' => 'media-centre/events/2022/june/family-lecture-series-mental-health-in-the-caring-role',
                'slug' => 'family-lecture-series-mental-health-in-the-caring-role',
                'title' => 'Supporting the supporters: Minding your mental health in the caring role',
                'description' => 'Watch our webinar on minding your mental health while in a caring role.',
                'image' => '/media/3459/mental-health-in-the-caring-role.png',
            ],
        ];
    }
}

if (! function_exists('matrix_family_lecture_post_url')) {
    function matrix_family_lecture_post_url(string $old_path, string $fallback_slug = ''): string
    {
        if (! function_exists('matrix_migrate_find_by_old_path')) {
            return $fallback_slug !== '' ? home_url('/' . trim($fallback_slug, '/') . '/') : '';
        }

        $post_id = matrix_migrate_find_by_old_path($old_path, 'post');

        if ($post_id > 0) {
            $url = get_permalink($post_id);

            if (is_string($url) && $url !== '') {
                return $url;
            }
        }

        if ($fallback_slug !== '') {
            $post = get_page_by_path($fallback_slug, OBJECT, 'post');

            if ($post instanceof WP_Post) {
                $url = get_permalink($post);

                if (is_string($url) && $url !== '') {
                    return $url;
                }
            }

            return home_url('/' . trim($fallback_slug, '/') . '/');
        }

        return '';
    }
}

if (! function_exists('matrix_family_lecture_series_cards')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function matrix_family_lecture_series_cards(): array
    {
        $cards = [];

        foreach (matrix_family_lecture_series_items() as $item) {
            $image_id = function_exists('matrix_migrate_attachment_id_for_source_path')
                ? matrix_migrate_attachment_id_for_source_path($item['image'])
                : 0;

            $cards[] = [
                'image' => $image_id,
                'title' => $item['title'],
                'description' => $item['description'],
                'link' => [
                    'title' => $item['title'],
                    'url' => matrix_family_lecture_post_url($item['old_path'], $item['slug']),
                    'target' => '',
                ],
            ];
        }

        return $cards;
    }
}

if (! function_exists('matrix_family_lecture_patch_youth_advocacy_related_cards')) {
    function matrix_family_lecture_patch_youth_advocacy_related_cards(): void
    {
        if (! function_exists('get_field') || ! function_exists('update_field')) {
            return;
        }

        $youth_id = (int) (get_page_by_path('advocacy-services/youth-advocacy', OBJECT, 'page')?->ID ?? 0);

        if ($youth_id < 1) {
            return;
        }

        $rows = get_field('flexible_content_blocks', $youth_id);

        if (! is_array($rows) || $rows === []) {
            return;
        }

        $child_blog_url = home_url('/no-stigma-child-mental-health/');
        $child_image_id = function_exists('matrix_migrate_attachment_id_for_source_path')
            ? matrix_migrate_attachment_id_for_source_path('/media/1734/st-patricks-mental-health-services-suas.jpg')
            : 0;

        $cards = array_merge(matrix_family_lecture_series_cards(), [
            [
                'image' => $child_image_id,
                'title' => 'How can we support children and young people\'s mental health?',
                'description' => 'Read practical guidance on supporting young people\'s mental health.',
                'link' => [
                    'title' => 'How can we support children and young people\'s mental health?',
                    'url' => $child_blog_url,
                    'target' => '',
                ],
            ],
        ]);

        foreach ($rows as $index => $row) {
            if (($row['acf_fc_layout'] ?? '') !== 'related_cards') {
                continue;
            }

            $rows[$index]['cards'] = $cards;
            $rows[$index]['columns'] = '3';
            update_field('flexible_content_blocks', $rows, $youth_id);

            return;
        }
    }
}
