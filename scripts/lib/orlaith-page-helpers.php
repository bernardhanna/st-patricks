<?php

/**
 * Shared builders for Orlaith August page layouts.
 */

if (! function_exists('matrix_orlaith_permalink')) {
    function matrix_orlaith_permalink(string $path): string
    {
        $path = trim($path, '/');

        // CPT rewrite prefixes are not WP parent slugs (posts are often parent=0).
        // Prefer looking up by leaf slug within the matching CPT before falling back
        // to pages that share the same basename (e.g. schizophrenia page vs MH CPT).
        $cpt_by_prefix = [
            'mental-health' => 'mental_health',
            'care-treatment' => 'care_treatment',
            'programmes-therapies' => 'programmes_therapies',
            'get-involved' => 'get_involved',
            'locations' => 'locations',
        ];
        $parts = explode('/', $path);
        if (count($parts) >= 2) {
            $prefix = $parts[0];
            $leaf = (string) end($parts);
            if (isset($cpt_by_prefix[$prefix]) && $leaf !== '') {
                $cpt_hits = get_posts([
                    'name' => $leaf,
                    'post_type' => $cpt_by_prefix[$prefix],
                    'post_status' => 'publish',
                    'posts_per_page' => 1,
                ]);
                if ($cpt_hits !== []) {
                    return (string) get_permalink($cpt_hits[0]);
                }
            }
        }

        $page = get_page_by_path($path, OBJECT, [
            'page',
            'post',
            'mental_health',
            'care_treatment',
            'programmes_therapies',
            'get_involved',
            'locations',
        ]);
        if ($page instanceof WP_Post && $page->post_status === 'publish') {
            return (string) get_permalink($page);
        }

        $found = get_posts([
            'name' => basename($path),
            'post_type' => [
                'page',
                'post',
                'mental_health',
                'care_treatment',
                'programmes_therapies',
                'get_involved',
                'locations',
            ],
            'post_status' => 'publish',
            'posts_per_page' => 1,
        ]);

        return $found !== [] ? (string) get_permalink($found[0]) : home_url('/' . $path . '/');
    }
}

if (! function_exists('matrix_orlaith_a')) {
    function matrix_orlaith_a(string $path, string $label): string
    {
        return '<a href="' . esc_url(matrix_orlaith_permalink($path)) . '">' . esc_html($label) . '</a>';
    }
}

if (! function_exists('matrix_orlaith_find_image')) {
    function matrix_orlaith_find_image(int $preferred_id, string $filename = ''): int
    {
        if ($preferred_id > 0 && get_post_type($preferred_id) === 'attachment') {
            return $preferred_id;
        }
        if ($filename === '') {
            return 0;
        }
        $found = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'title' => preg_replace('/\.[^.]+$/', '', $filename),
        ]);

        return $found !== [] ? (int) $found[0] : 0;
    }
}

if (! function_exists('matrix_orlaith_hero_row')) {
    function matrix_orlaith_hero_row(string $title, string $intro_html, int $image_id): array
    {
        return [
            'acf_fc_layout' => 'hero_with_breadcrumbs',
            'layout_style' => $image_id > 0 ? 'image_split' : 'title_accent',
            'show_breadcrumbs' => 1,
            'breadcrumb_source' => 'auto',
            'current_crumb_label' => $title,
            'heading_tag' => 'h1',
            'heading' => $title,
            'content' => $intro_html,
            'hero_image' => $image_id > 0 ? $image_id : '',
            'primary_button' => ['title' => '', 'url' => '', 'target' => ''],
            'text_max_width' => 'default',
            'heading_max_width' => 'default',
            'background_color' => '#C6ECF4',
            'breadcrumb_background_color' => '#F1F8F9',
            'heading_color' => '#08284B',
            'text_color' => '#08284B',
        ];
    }
}

if (! function_exists('matrix_orlaith_content_row')) {
    /**
     * @param array<string, mixed> $extra
     */
    function matrix_orlaith_content_row(
        string $heading,
        string $html,
        string $background = 'white',
        int $image_id = 0,
        string $layout_style = 'image_left',
        array $extra = []
    ): array {
        $has_image = $image_id > 0;
        $row = [
            'acf_fc_layout' => 'content',
            'heading' => $heading,
            'heading_tag' => 'h2',
            'accent_position' => 'below_heading',
            'intro_text' => '',
            'content' => $html,
            'background_type' => $background,
            'color_scheme' => 'default',
            'column_layout' => $has_image ? 'two_column' : 'one_column',
            'layout_style' => $layout_style,
            'image_height_mode' => 'match_text',
            'text_width' => 'full',
            'image' => $has_image ? $image_id : '',
        ];

        return array_merge($row, $extra);
    }
}

if (! function_exists('matrix_orlaith_button')) {
    /**
     * @return array{title:string,url:string,target:string}
     */
    function matrix_orlaith_button(string $title, string $url, string $target = ''): array
    {
        return [
            'title' => $title,
            'url' => $url,
            'target' => $target,
        ];
    }
}

if (! function_exists('matrix_orlaith_accordion_row')) {
    /**
     * @param array<string, string> $items title => html
     */
    function matrix_orlaith_accordion_row(array $items, string $layout_style = 'default', string $heading = ''): array
    {
        $out = [];
        foreach ($items as $title => $html) {
            $out[] = [
                'title' => $title,
                'starts_open' => 0,
                'content_rows' => [[
                    'row_type' => 'text',
                    'icon_key' => '',
                    'icon' => '',
                    'content' => $html,
                ]],
            ];
        }

        return [
            'acf_fc_layout' => 'content_accordion',
            'layout_style' => $layout_style,
            'heading' => $heading,
            'heading_tag' => 'h2',
            'vertical_padding' => 'default',
            'items' => $out,
        ];
    }
}

if (! function_exists('matrix_orlaith_video_row')) {
    /**
     * @param list<array{url:string,title?:string,caption?:string,poster?:int}> $slides
     */
    function matrix_orlaith_video_row(string $heading, string $intro_html, array $slides): array
    {
        $normalized = [];
        foreach ($slides as $slide) {
            $title = trim((string) ($slide['title'] ?? ''));
            $caption = trim((string) ($slide['caption'] ?? ''));
            $poster = (int) ($slide['poster'] ?? 0);
            $caption_html = '';
            if ($title !== '') {
                $caption_html .= '<p><strong>' . esc_html($title) . '</strong></p>';
            }
            if ($caption !== '') {
                $caption_html .= '<p>' . esc_html($caption) . '</p>';
            }
            $normalized[] = [
                'poster_image' => $poster > 0 ? $poster : '',
                'video_source_type' => 'embed_url',
                'video_embed_url' => (string) $slide['url'],
                'local_video_file' => '',
                'caption' => $caption_html,
                'cta_link' => '',
            ];
        }

        return [
            'acf_fc_layout' => 'video_showcase',
            'heading_tag' => 'h2',
            'heading' => $heading,
            'intro' => $intro_html,
            'layout_style' => count($normalized) > 1 ? 'feature_slider' : 'feature_single',
            'video_surface_size' => 'default',
            'text_max_width' => 'full',
            'slides' => $normalized,
        ];
    }
}

if (! function_exists('matrix_orlaith_useful_links_row')) {
    /**
     * @param array<string, string> $links label => path or url
     */
    function matrix_orlaith_useful_links_row(array $links, string $heading = 'Useful links'): array
    {
        $rows = [];
        foreach ($links as $label => $url) {
            $href = str_starts_with($url, 'http') ? $url : matrix_orlaith_permalink($url);
            $rows[] = [
                'link' => [
                    'title' => $label,
                    'url' => $href,
                    'target' => '',
                ],
            ];
        }

        return [
            'acf_fc_layout' => 'useful_links',
            'heading' => $heading,
            'heading_tag' => 'h2',
            'variant' => 'flexi',
            'background_color' => '#E9E2F7',
            'heading_color' => '#1E244B',
            'link_color' => '#1E244B',
            'links' => $rows,
        ];
    }
}

if (! function_exists('matrix_orlaith_newsletter_row')) {
    /**
     * @param array<string, mixed> $extra
     */
    function matrix_orlaith_newsletter_row(string $heading, string $subtext_html, array $extra = []): array
    {
        return array_merge([
            'acf_fc_layout' => 'newsletter',
            'heading' => $heading,
            'subtext' => $subtext_html,
            'newsletter_list_id' => '',
        ], $extra);
    }
}

if (! function_exists('matrix_orlaith_gp_newsletter_row')) {
    /**
     * Healthcare GP e-newsletter flexi (same copy/list as subscribe-to-our-gp-enewsletter).
     *
     * @param array<string, mixed> $extra
     */
    function matrix_orlaith_gp_newsletter_row(string $subtext_html = '', array $extra = []): array
    {
        if ($subtext_html === '') {
            $subtext_html = '<p>We issue a quarterly digital newsletter especially tailored to GPs, covering mental health news, research findings, service updates and clinical insights. Sign up using the form below.</p>';
        }

        $list_id = function_exists('matrix_resolve_gp_newsletter_list_id')
            ? matrix_resolve_gp_newsletter_list_id()
            : '';

        return matrix_orlaith_newsletter_row('Sign-up to get the GP Newsletter', $subtext_html, array_merge([
            'newsletter_list_id' => $list_id,
        ], $extra));
    }
}

if (! function_exists('matrix_orlaith_contact_form_row')) {
    /**
     * @param array<string, mixed> $extra
     */
    function matrix_orlaith_contact_form_row(string $heading, string $intro_html, array $extra = []): array
    {
        return array_merge([
            'acf_fc_layout' => 'contact_form',
            'form_style' => 'mailing_list',
            'heading_tag' => 'h2',
            'heading' => $heading,
            'intro' => $intro_html,
            'background_type' => 'cream',
            'vertical_padding' => 'compact',
            'submit_label' => 'Submit',
            'success_message' => 'Thanks! Your registration request has been sent.',
            'form_name' => 'Mailing list registration',
            'email_subject' => 'New mailing list registration',
            'recipient_email' => 'sfitzharris@stpatricks.ie',
            'save_to_db' => 1,
            'privacy_policy_label' => 'Privacy Notice.',
        ], $extra);
    }
}

if (! function_exists('matrix_orlaith_attachment_url')) {
    function matrix_orlaith_attachment_url(int $attachment_id, string $size = 'large'): string
    {
        if ($attachment_id <= 0) {
            return '';
        }

        $url = wp_get_attachment_image_url($attachment_id, $size);

        return is_string($url) ? $url : '';
    }
}

if (! function_exists('matrix_orlaith_about_links_grid_row')) {
    /**
     * @param list<array<string, mixed>> $cards
     * @param array<string, mixed> $extra
     */
    function matrix_orlaith_about_links_grid_row(string $heading, array $cards, array $extra = []): array
    {
        return array_merge([
            'acf_fc_layout' => 'about_links_grid',
            'heading_tag' => 'h2',
            'heading_text' => $heading,
            'intro_text' => '',
            'links' => $cards,
            'bg_color' => '#F1F8F9',
            'heading_color' => '#1E244B',
            'intro_color' => '#08284B',
            'columns' => '3',
            'layout_style' => 'image_feature',
            'allow_title_wrap' => 1,
        ], $extra);
    }
}

if (! function_exists('matrix_orlaith_stories_row')) {
    function matrix_orlaith_stories_row(): array
    {
        return [
            'acf_fc_layout' => 'stories',
            'posts_per_slide' => 4,
            'max_posts' => 12,
            'show_date' => 1,
            'show_excerpt' => 0,
            'card_background_color' => '#fafaf9',
            'divider_color' => '#F9F1D1',
            'text_color' => '#08284B',
            'date_color' => '#08284B',
        ];
    }
}

if (! function_exists('matrix_orlaith_set_seo')) {
    function matrix_orlaith_set_seo(int $post_id, string $title, string $description): void
    {
        update_post_meta($post_id, 'rank_math_title', $title);
        update_post_meta($post_id, 'rank_math_description', $description);
        wp_update_post([
            'ID' => $post_id,
            'post_excerpt' => $description,
        ]);
    }
}

if (! function_exists('matrix_orlaith_save_page')) {
    /**
     * @param list<array<string, mixed>> $rows
     */
    function matrix_orlaith_save_page(int $post_id, array $rows, bool $builder = true, int $hero_image = 0): void
    {
        update_field('hero_content_blocks', [], $post_id);
        update_field('flexible_content_blocks', $rows, $post_id);
        if ($hero_image > 0) {
            set_post_thumbnail($post_id, $hero_image);
        }
        if (class_exists('Matrix_Flexible_Pages')) {
            Matrix_Flexible_Pages::set_flexible_page($post_id, $builder);
        }
    }
}
