<?php

/**
 * Migrate SPIRE page content and move to /research/spire/.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/migrate-spire-page.php
 */

require_once get_template_directory() . '/inc/migrate-functions.php';
require_once get_template_directory() . '/inc/migrate-restyle-functions.php';
require_once get_template_directory() . '/inc/hero-functions.php';

if (! class_exists('WP_CLI')) {
    exit(1);
}

if (! function_exists('matrix_migrate_spire_ensure_image')) {
    function matrix_migrate_spire_ensure_image(string $legacy_path, string $title): int
    {
        $attachment_id = matrix_migrate_attachment_id_for_source_path($legacy_path);

        if ($attachment_id > 0) {
            return $attachment_id;
        }

        return matrix_migrate_import_attachment(matrix_migrate_live_url($legacy_path), $title);
    }
}

if (! function_exists('matrix_migrate_spire_ensure_youtube_poster')) {
    function matrix_migrate_spire_ensure_youtube_poster(string $video_id, string $title): int
    {
        $video_id = trim($video_id);

        if ($video_id === '') {
            return 0;
        }

        foreach ([
            'youtube-' . strtolower($video_id) . '-poster',
            'future-in-mind-research-and-training-video-poster',
            'research-and-training-video-poster',
        ] as $poster_slug) {
            $poster = get_page_by_path($poster_slug, OBJECT, 'attachment');

            if ($poster instanceof WP_Post) {
                return (int) $poster->ID;
            }
        }

        $urls = [
            'https://i.ytimg.com/vi/' . $video_id . '/maxresdefault.jpg',
            'https://i.ytimg.com/vi/' . $video_id . '/hqdefault.jpg',
        ];

        foreach ($urls as $url) {
            $normalized_url = matrix_migrate_normalize_asset_url($url);
            $cache_key = matrix_migrate_asset_cache_key($normalized_url);

            $existing = get_posts([
                'post_type' => 'attachment',
                'post_status' => 'inherit',
                'posts_per_page' => 1,
                'meta_query' => [
                    [
                        'key' => '_matrix_migrate_cache_key',
                        'value' => $cache_key,
                    ],
                ],
                'fields' => 'ids',
            ]);

            if ($existing !== []) {
                return (int) $existing[0];
            }

            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';

            $response = wp_remote_get($normalized_url, ['timeout' => 60]);

            if (is_wp_error($response) || (int) wp_remote_retrieve_response_code($response) !== 200) {
                continue;
            }

            $tmp = wp_tempnam($normalized_url);

            if ($tmp === '') {
                continue;
            }

            $bytes = file_put_contents($tmp, (string) wp_remote_retrieve_body($response));

            if ($bytes === false || $bytes < 1) {
                @unlink($tmp);

                continue;
            }

            $file_array = [
                'name' => sanitize_file_name('youtube-' . $video_id . '-poster.jpg'),
                'tmp_name' => $tmp,
            ];

            $attachment_id = media_handle_sideload($file_array, 0, sanitize_text_field($title));

            if (is_wp_error($attachment_id)) {
                @unlink($tmp);

                continue;
            }

            update_post_meta($attachment_id, '_matrix_migrate_cache_key', $cache_key);
            update_post_meta($attachment_id, '_matrix_migrate_source_url', $normalized_url);

            return (int) $attachment_id;
        }

        return 0;
    }
}

if (! function_exists('matrix_migrate_spire_image_field')) {
    function matrix_migrate_spire_image_field(int $attachment_id, string $alt = ''): array
    {
        if ($attachment_id < 1) {
            return [];
        }

        $alt = trim($alt);

        if ($alt === '') {
            $alt = trim((string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true));
        }

        return [
            'ID' => $attachment_id,
            'id' => $attachment_id,
            'url' => (string) wp_get_attachment_url($attachment_id),
            'alt' => $alt,
            'title' => (string) get_the_title($attachment_id),
        ];
    }
}

if (! function_exists('matrix_migrate_spire_clean_section_html')) {
    function matrix_migrate_spire_clean_section_html(string $html): string
    {
        $html = preg_replace('#<h[23][^>]*class="hide-for-main"[^>]*>\s*</h[23]>#', '', $html) ?? $html;
        $html = preg_replace('#<div class="section-head hide-for-side">\s*</div>#', '', $html) ?? $html;

        return trim($html);
    }
}

if (! function_exists('matrix_migrate_spire_content_row')) {
    function matrix_migrate_spire_content_row(array $overrides): array
    {
        return array_merge([
            'acf_fc_layout' => 'content',
            'heading_tag' => 'h2',
            'accent_position' => 'below_heading',
            'intro_text' => '',
            'content' => '',
            'column_layout' => 'two_column',
            'layout_style' => 'image_left',
            'image_height_mode' => 'match_text',
            'text_width' => 'constrained',
            'reverse_layout' => 0,
            'background_type' => 'white',
            'background_color' => '#FFFFFF',
            'background_gradient' => '',
            'image' => 0,
            'primary_button' => '',
            'secondary_button' => '',
            'primary_button_variant' => 'filled',
            'secondary_button_variant' => 'outline',
            'document_link' => '',
            'padding_settings' => matrix_migrate_restyle_section_padding(),
        ], $overrides);
    }
}

if (! function_exists('matrix_migrate_spire_split_body_html')) {
    /**
     * @return array{overview: string, how_to: string}
     */
    function matrix_migrate_spire_split_body_html(string $body_html): array
    {
        $body_html = trim($body_html);

        if (preg_match('#<h2[^>]*>\s*How to use SPIRE\s*</h2>#i', $body_html, $match, PREG_OFFSET_CAPTURE)) {
            $offset = (int) $match[0][1];
            $how_to = trim(substr($body_html, $offset));
            $how_to = preg_replace('#<h2[^>]*>\s*How to use SPIRE\s*</h2>#i', '', $how_to, 1) ?? $how_to;

            return [
                'overview' => trim(substr($body_html, 0, $offset)),
                'how_to' => trim($how_to),
            ];
        }

        return [
            'overview' => $body_html,
            'how_to' => '',
        ];
    }
}

if (! function_exists('matrix_migrate_spire_normalise_body_html')) {
    function matrix_migrate_spire_normalise_body_html(
        string $body_html,
        string $home,
        string $about_research_url,
        string $spire_external_url
    ): string {
        $body_html = preg_replace(
            '#<div class="section-head hide-for-side">\s*<p class="intro">.*?</p>\s*</div>#s',
            '',
            $body_html,
            1
        ) ?? $body_html;

        $body_html = str_replace(
            [
                'href="' . esc_url($home) . '" target="_blank">SPIRE (St Patrick’s Institutional Repository)',
                'href="' . esc_url($home) . '" target="_blank">SPIRE (St Patrick\'s Institutional Repository)',
            ],
            'href="' . esc_url($spire_external_url) . '" target="_blank" rel="noopener noreferrer">SPIRE (St Patrick\'s Institutional Repository)',
            $body_html
        );

        $body_html = str_replace(
            'href="' . esc_url($home) . '">our Academic Institute',
            'href="' . esc_url($about_research_url) . '">our Academic Institute',
            $body_html
        );

        if (function_exists('matrix_process_external_links_in_html')) {
            $body_html = matrix_process_external_links_in_html($body_html);
        }

        return trim($body_html);
    }
}

$page_id = (int) (get_page_by_path('research/spire')?->ID ?? 0);

if ($page_id < 1) {
    $page_id = (int) (get_page_by_path('research-library-spire')?->ID ?? 0);
}

if ($page_id < 1) {
    WP_CLI::error('Could not find SPIRE page.');
}

$html_file = matrix_migrate_html_dir() . '/original_https_www.stpatricks.ie_research_spire.html';

if (! is_readable($html_file)) {
    WP_CLI::error('Missing cached HTML for research/spire.');
}

$html = (string) file_get_contents($html_file);
$parsed = matrix_migrate_extract_parsed_page($html, 'research/spire');

if ($parsed === null) {
    WP_CLI::error('Could not parse SPIRE HTML.');
}

$home = home_url('/');
$about_research_url = home_url('/about-us/research/');
$spire_url = home_url('/research/spire/');
$research_projects_url = home_url('/research-projects/');
$ethics_url = home_url('/research-ethics-committee/');
$spire_external_url = 'https://repository.stpatricks.ie/home';
$faqs_url = home_url('/service-users-and-visitors/frequently-asked-questions-faqs/');
$referrals_url = home_url('/healthcare-professionals/');

$spire_homepage_image_id = matrix_migrate_spire_ensure_image(
    '/media/4017/spire-research-repository-homepage.png',
    'SPIRE research repository homepage'
);
$research_department_image_id = matrix_migrate_spire_ensure_image(
    '/media/1541/st-patricks-mental-health-services-research-department.jpg',
    'St Patrick\'s research department'
);
$research_banner_image_id = matrix_migrate_spire_ensure_image(
    '/media/1675/st-patricks-mental-health-services-research-department-banner-min.jpg',
    'St Patrick\'s research department banner'
);
$nurses_research_wall_image_id = matrix_migrate_spire_ensure_image(
    '/media/3992/nurses-research-wall-launch.jpg',
    'Nurses research wall launch at St Patrick\'s'
);
$research_project_image_id = matrix_migrate_spire_ensure_image(
    '/media/2392/impact-eating-disorders-biological-ageing.jpg',
    'Research project at St Patrick\'s Mental Health Services'
);
$video_poster_image_id = matrix_migrate_spire_ensure_youtube_poster(
    'AjJQxOrmv1o',
    'Research and training video poster'
);

$body_html = matrix_migrate_spire_normalise_body_html(
    (string) $parsed['body_html'],
    $home,
    $about_research_url,
    $spire_external_url
);
$sections = matrix_migrate_spire_split_body_html($body_html);
$sections['overview'] = matrix_migrate_spire_clean_section_html($sections['overview']);
$sections['how_to'] = matrix_migrate_spire_clean_section_html($sections['how_to']);
$section_padding = matrix_migrate_restyle_section_padding();
$intro = trim((string) $parsed['intro']);
$video_poster = matrix_migrate_spire_image_field(
    $video_poster_image_id > 0 ? $video_poster_image_id : $research_banner_image_id,
    'Research and training at St Patrick\'s Mental Health Services'
);

$flexi_rows = [
    [
        'acf_fc_layout' => 'hero_with_breadcrumbs',
        'layout_style' => 'image_split',
        'show_breadcrumbs' => 1,
        'breadcrumb_source' => 'manual',
        'manual_breadcrumbs' => [
            [
                'breadcrumb_link' => [
                    'title' => 'Home',
                    'url' => $home,
                    'target' => '',
                ],
            ],
            [
                'breadcrumb_link' => [
                    'title' => 'Research',
                    'url' => $about_research_url,
                    'target' => '',
                ],
            ],
        ],
        'current_crumb_label' => 'Research Repository',
        'heading_tag' => 'h1',
        'heading' => 'SPIRE: Research repository',
        'content' => $intro !== '' ? '<p>' . esc_html($intro) . '</p>' : '',
        'primary_button' => [
            'title' => 'Go to SPIRE',
            'url' => $spire_external_url,
            'target' => '_blank',
        ],
        'hero_image' => $research_banner_image_id > 0 ? $research_banner_image_id : $spire_homepage_image_id,
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
    ],
    [
        'acf_fc_layout' => 'useful_links',
        'heading_tag' => 'h2',
        'heading' => 'In this section',
        'variant' => 'flexi',
        'links' => [
            ['link' => ['title' => 'Research Repository', 'url' => $spire_url, 'target' => '']],
            ['link' => ['title' => 'Research Projects', 'url' => $research_projects_url, 'target' => '']],
            ['link' => ['title' => 'Research Ethics Committee', 'url' => $ethics_url, 'target' => '']],
        ],
        'background_color' => '#F1F8F9',
    ],
    matrix_migrate_spire_content_row([
        'heading' => 'About SPIRE',
        'intro_text' => $intro !== '' ? '<p><strong>' . esc_html($intro) . '</strong></p>' : '',
        'content' => $sections['overview'],
        'layout_style' => 'image_left',
        'background_type' => 'white',
        'image' => $spire_homepage_image_id,
        'primary_button' => [
            'title' => 'Visit SPIRE',
            'url' => $spire_external_url,
            'target' => '_blank',
        ],
    ]),
    matrix_migrate_spire_content_row([
        'heading' => 'How to use SPIRE',
        'content' => $sections['how_to'] !== '' ? $sections['how_to'] : '<p>SPIRE includes journal articles, conference presentations, book chapters, technical reports, working papers, reviews, and other scholarly contributions from our research staff.</p>',
        'layout_style' => 'image_right',
        'background_type' => 'cream',
        'image' => $research_department_image_id,
        'secondary_button' => [
            'title' => 'About our research',
            'url' => $about_research_url,
            'target' => '',
        ],
    ]),
    matrix_migrate_spire_content_row([
        'heading' => 'See our research repository',
        'intro_text' => '<p>Discover collections by discipline, or search by issue date, author, title, and subject.</p>',
        'content' => '<p>SPIRE is a living resource that continues to grow with research by our staff. Visit the repository to explore publications and outputs from St Patrick\'s Mental Health Services.</p>',
        'layout_style' => 'image_left',
        'background_type' => 'gradient',
        'background_gradient' => 'linear-gradient(278deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
        'image' => $nurses_research_wall_image_id > 0 ? $nurses_research_wall_image_id : $spire_homepage_image_id,
        'primary_button' => [
            'title' => 'Visit SPIRE here',
            'url' => $spire_external_url,
            'target' => '_blank',
        ],
    ]),
    [
        'acf_fc_layout' => 'video_showcase',
        'heading_tag' => 'h2',
        'heading' => 'Research and training on video',
        'intro' => '<p>Learn how our Academic Institute advances mental health research and training as part of our Future in Mind strategy.</p>',
        'layout_style' => 'feature_single',
        'video_surface_size' => 'default',
        'slides' => [
            [
                'poster_image' => $video_poster,
                'video_source_type' => 'embed_url',
                'video_embed_url' => 'https://www.youtube.com/watch?v=AjJQxOrmv1o',
                'caption' => '',
                'cta_link' => [
                    'title' => 'Open SPIRE',
                    'url' => $spire_external_url,
                    'target' => '_blank',
                ],
            ],
        ],
        'section_background' => '#FFFFFF',
        'padding_settings' => $section_padding,
    ],
    matrix_migrate_spire_content_row([
        'heading' => 'Continue to research projects',
        'intro_text' => '<p>Explore current and past research projects from St Patrick\'s Mental Health Services.</p>',
        'content' => '<p>Browse studies across depression, ECT, eating disorders, pharmacy research, and more.</p>',
        'layout_style' => 'image_right',
        'background_type' => 'light_blue',
        'image' => $research_project_image_id > 0 ? $research_project_image_id : $research_department_image_id,
        'primary_button' => [
            'title' => 'View research projects',
            'url' => $research_projects_url,
            'target' => '',
        ],
        'secondary_button' => [
            'title' => 'Research Ethics Committee',
            'url' => $ethics_url,
            'target' => '',
        ],
    ]),
    [
        'acf_fc_layout' => 'content_cta',
        'heading_tag' => 'h3',
        'heading' => 'Queries',
        'body' => '<p>For general queries, please call us. For more on mental health and our services, see our <a href="' . esc_url($faqs_url) . '">frequently asked questions (FAQs)</a>.</p>',
        'button_link' => [
            'title' => '01 249 3200',
            'url' => 'tel:012493200',
            'target' => '',
        ],
        'background_type' => 'color',
        'background_color' => '#08284B',
        'color_scheme' => 'inverse',
    ],
    [
        'acf_fc_layout' => 'content_cta',
        'heading_tag' => 'h3',
        'heading' => 'Referrals',
        'body' => '<p>Contact our Referral and Assessment Service for queries regarding referrals to our services.</p>',
        'button_link' => [
            'title' => 'See more from our referrals team',
            'url' => $referrals_url,
            'target' => '',
        ],
        'background_type' => 'color',
        'background_color' => '#E9E2F7',
        'color_scheme' => 'default',
    ],
];

$research_parent = get_page_by_path('research');

if (! $research_parent instanceof WP_Post) {
    $research_parent_id = (int) wp_insert_post([
        'post_type' => 'page',
        'post_status' => 'publish',
        'post_title' => 'Research',
        'post_name' => 'research',
        'post_parent' => 0,
    ]);

    if ($research_parent_id < 1) {
        WP_CLI::error('Could not create top-level research parent page.');
    }

    update_field('hero_content_blocks', [], $research_parent_id);
    update_field('flexible_content_blocks', [
        [
            'acf_fc_layout' => 'useful_links',
            'heading_tag' => 'h2',
            'heading' => 'Research',
            'variant' => 'flexi',
            'links' => [
                ['link' => ['title' => 'About our research', 'url' => $about_research_url, 'target' => '']],
                ['link' => ['title' => 'SPIRE research repository', 'url' => $spire_url, 'target' => '']],
                ['link' => ['title' => 'Research projects', 'url' => $research_projects_url, 'target' => '']],
                ['link' => ['title' => 'Research Ethics Committee', 'url' => $ethics_url, 'target' => '']],
            ],
            'background_color' => '#FFFFFF',
        ],
    ], $research_parent_id);
} else {
    $research_parent_id = (int) $research_parent->ID;
}

wp_update_post([
    'ID' => $page_id,
    'post_title' => 'SPIRE: Research repository',
    'post_name' => 'spire',
    'post_parent' => $research_parent_id,
]);

update_post_meta($page_id, '_matrix_migrate_old_path', 'research/spire');
update_field('hero_content_blocks', [], $page_id);
update_field('flexible_content_blocks', $flexi_rows, $page_id);

flush_rewrite_rules(false);

WP_CLI::success(sprintf(
    'SPIRE page migrated to %s (%d blocks).',
    get_permalink($page_id),
    count($flexi_rows)
));
