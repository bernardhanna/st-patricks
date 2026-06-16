<?php

/**
 * Shared helpers for seeding Get Involved CPT posts.
 *
 * @see scripts/seed-get-involved-cpt.php
 */

if (! function_exists('matrix_seed_get_involved_url')) {
    function matrix_seed_get_involved_url(string $slug): string
    {
        return home_url('/get-involved/' . trim($slug, '/') . '/');
    }
}

if (! function_exists('matrix_seed_get_involved_main_link_defs')) {
    /**
     * @return array<int, array{title: string, slug: string}>
     */
    function matrix_seed_get_involved_main_link_defs(): array
    {
        return [
            ['title' => 'Service User Participation', 'slug' => 'service-user-participation'],
            ['title' => 'Peer Support', 'slug' => 'peer-support'],
            ['title' => 'Fundraising', 'slug' => 'fundraising'],
            ['title' => 'Donations', 'slug' => 'donations'],
        ];
    }
}

if (! function_exists('matrix_seed_get_involved_participation_link_defs')) {
    /**
     * @return array<int, array{title: string, slug: string}>
     */
    function matrix_seed_get_involved_participation_link_defs(): array
    {
        return [
            ['title' => 'Service User Participation', 'slug' => 'service-user-participation'],
            ['title' => 'Service User and Supporters Council - SUAS', 'slug' => 'service-user-and-supporters-council-suas'],
            ['title' => 'Service User Advisory Network - SUAN', 'slug' => 'service-user-advisory-network-suan'],
            ['title' => 'News for Service Users', 'slug' => 'news-for-service-users'],
            ['title' => 'Service User Experience Survey', 'slug' => 'service-user-experience-survey'],
        ];
    }
}

if (! function_exists('matrix_seed_get_involved_links_from_defs')) {
    /**
     * @param array<int, array{title: string, slug: string}> $defs
     * @return array<int, array{link: array{title: string, url: string, target: string}}>
     */
    function matrix_seed_get_involved_links_from_defs(array $defs): array
    {
        $rows = [];

        foreach ($defs as $link) {
            $rows[] = [
                'link' => [
                    'title' => $link['title'],
                    'url' => matrix_seed_get_involved_url($link['slug']),
                    'target' => '',
                ],
            ];
        }

        return $rows;
    }
}

if (! function_exists('matrix_seed_get_involved_useful_links_block')) {
    /**
     * @return array<string, mixed>
     */
    function matrix_seed_get_involved_useful_links_block(string $nav = 'main'): array
    {
        $defs = $nav === 'participation'
            ? matrix_seed_get_involved_participation_link_defs()
            : matrix_seed_get_involved_main_link_defs();

        return [
            'acf_fc_layout' => 'useful_links',
            'heading_tag' => matrix_page_seed_heading(2),
            'heading' => 'In this section',
            'variant' => 'flexi',
            'links' => matrix_seed_get_involved_links_from_defs($defs),
            'background_color' => '#F1F8F9',
        ];
    }
}

if (! function_exists('matrix_seed_get_involved_accordion_item')) {
    function matrix_seed_get_involved_accordion_item(string $title, string $content, bool $starts_open = false): array
    {
        return [
            'title' => $title,
            'starts_open' => $starts_open ? 1 : 0,
            'content_rows' => [
                [
                    'row_type' => 'text',
                    'icon_key' => '',
                    'icon' => '',
                    'content' => $content,
                ],
            ],
        ];
    }
}

if (! function_exists('matrix_seed_get_involved_content_block')) {
    /**
     * @return array<string, mixed>
     */
    function matrix_seed_get_involved_content_block(string $heading, string $content, string $background = 'white'): array
    {
        return [
            'acf_fc_layout' => 'content',
            'heading' => $heading,
            'heading_tag' => matrix_page_seed_heading(2),
            'accent_position' => 'below_heading',
            'content' => $content,
            'column_layout' => 'one_column',
            'background_type' => $background,
            'text_width' => 'constrained',
        ];
    }
}

if (! function_exists('matrix_seed_get_involved_hero_block')) {
    /**
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    function matrix_seed_get_involved_hero_block(array $config): array
    {
        $home = home_url('/');

        $breadcrumbs = [
            ['breadcrumb_link' => ['title' => 'Home', 'url' => $home, 'target' => '']],
            ['breadcrumb_link' => ['title' => 'Get Involved', 'url' => matrix_seed_get_involved_url('service-user-participation'), 'target' => '']],
        ];

        if (($config['parent'] ?? '') === 'participation') {
            $breadcrumbs[] = [
                'breadcrumb_link' => [
                    'title' => 'Service User Participation',
                    'url' => matrix_seed_get_involved_url('service-user-participation'),
                    'target' => '',
                ],
            ];
        }

        return [
            'acf_fc_layout' => 'hero_with_breadcrumbs',
            'layout_style' => 'image_split',
            'show_breadcrumbs' => 1,
            'breadcrumb_source' => 'manual',
            'manual_breadcrumbs' => $breadcrumbs,
            'current_crumb_label' => (string) ($config['crumb_label'] ?? $config['heading'] ?? ''),
            'heading_tag' => matrix_page_seed_heading(1),
            'heading' => (string) ($config['heading'] ?? ''),
            'content' => (string) ($config['intro'] ?? ''),
            'hero_image' => (int) ($config['hero_image_id'] ?? 0),
            'background_color' => '#C6ECF4',
            'breadcrumb_background_color' => '#F1F8F9',
            'heading_color' => '#08284B',
            'text_color' => '#08284B',
        ];
    }
}

if (! function_exists('matrix_seed_get_involved_footer_cta_rows')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function matrix_seed_get_involved_footer_cta_rows(): array
    {
        $faqs_url = home_url('/service-users-and-visitors/frequently-asked-questions-faqs/');
        $referrals_url = home_url('/make-a-referral/');

        return [
            [
                'acf_fc_layout' => 'content_cta',
                'heading_tag' => matrix_page_seed_heading(2),
                'heading' => 'Queries',
                'body' => '<p>For general queries, please call us. For more on mental health and our services, see our <a href="' . esc_url($faqs_url) . '">frequently asked questions (FAQs)</a>.</p>',
                'button_link' => [
                    'title' => '01 249 3200',
                    'url' => 'tel:012493200',
                    'target' => '',
                ],
                'background_type' => 'color',
                'background_color' => '#CEF2EE',
            ],
            [
                'acf_fc_layout' => 'content_cta',
                'heading_tag' => matrix_page_seed_heading(2),
                'heading' => 'Referrals',
                'body' => '<p>Contact our Referral and Assessment Service for queries regarding referrals to our services. <a href="' . esc_url($referrals_url) . '">See more from our referrals team</a>.</p>',
                'button_link' => [
                    'title' => '01 249 3635',
                    'url' => 'tel:012493635',
                    'target' => '',
                ],
                'background_type' => 'color',
                'background_color' => '#E9E2F7',
            ],
        ];
    }
}

if (! function_exists('matrix_seed_get_involved_page_rows')) {
    /**
     * @param array<int, array<string, mixed>> $content_rows
     * @return array<int, array<string, mixed>>
     */
    function matrix_seed_get_involved_page_rows(
        string $heading,
        string $crumb_label,
        string $intro,
        string $nav,
        array $content_rows,
        int $hero_image_id,
        string $parent = ''
    ): array {
        return array_merge(
            [
                matrix_seed_get_involved_hero_block([
                    'heading' => $heading,
                    'crumb_label' => $crumb_label,
                    'intro' => $intro !== '' ? '<p>' . esc_html($intro) . '</p>' : '',
                    'hero_image_id' => $hero_image_id,
                    'parent' => $parent,
                ]),
                matrix_seed_get_involved_useful_links_block($nav),
            ],
            $content_rows,
            matrix_seed_get_involved_footer_cta_rows()
        );
    }
}

if (! function_exists('matrix_seed_ensure_get_involved')) {
    /**
     * @param array<string, mixed> $args
     */
    function matrix_seed_ensure_get_involved(array $args): int
    {
        $slug = (string) ($args['slug'] ?? '');
        $title = (string) ($args['title'] ?? '');
        $seed_key = (string) ($args['seed_key'] ?? $slug);
        $section_term_id = (int) ($args['section_term_id'] ?? 0);

        $existing = get_posts([
            'post_type' => 'get_involved',
            'post_status' => 'any',
            'posts_per_page' => 1,
            'name' => $slug,
            'meta_query' => [
                [
                    'key' => '_matrix_seed_key',
                    'value' => $seed_key,
                ],
            ],
        ]);

        if ($existing !== []) {
            $post_id = (int) $existing[0]->ID;
            wp_update_post([
                'ID' => $post_id,
                'post_title' => $title,
                'post_excerpt' => (string) ($args['excerpt'] ?? ''),
                'post_status' => 'publish',
            ]);
        } else {
            $post_id = (int) wp_insert_post([
                'post_type' => 'get_involved',
                'post_status' => 'publish',
                'post_title' => $title,
                'post_name' => $slug,
                'post_excerpt' => (string) ($args['excerpt'] ?? ''),
            ]);

            if ($post_id < 1) {
                return 0;
            }

            update_post_meta($post_id, '_matrix_seed_key', $seed_key);
        }

        if ($section_term_id > 0) {
            wp_set_object_terms($post_id, [$section_term_id], 'get_involved_section');
        }

        if (! empty($args['featured_image_id'])) {
            set_post_thumbnail($post_id, (int) $args['featured_image_id']);
        }

        if (! empty($args['flexi_rows']) && function_exists('update_field')) {
            update_field('hero_content_blocks', [], $post_id);
            update_field('flexible_content_blocks', $args['flexi_rows'], $post_id);
        }

        return $post_id;
    }
}

if (! function_exists('matrix_seed_get_involved_ensure_term')) {
    function matrix_seed_get_involved_ensure_term(string $name, string $slug): int
    {
        $existing = get_term_by('slug', $slug, 'get_involved_section');

        if ($existing instanceof WP_Term) {
            return (int) $existing->term_id;
        }

        $created = wp_insert_term($name, 'get_involved_section', ['slug' => $slug]);

        if (is_wp_error($created)) {
            return 0;
        }

        return (int) ($created['term_id'] ?? 0);
    }
}
