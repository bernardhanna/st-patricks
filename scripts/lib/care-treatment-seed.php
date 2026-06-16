<?php

/**
 * Shared helpers for seeding Care & Treatment CPT posts.
 *
 * @see scripts/seed-care-treatment.php
 */

if (! function_exists('matrix_seed_care_treatment_url')) {
    function matrix_seed_care_treatment_url(string $slug): string
    {
        return home_url('/care-treatment/' . trim($slug, '/') . '/');
    }
}

if (! function_exists('matrix_seed_care_treatment_service_link_defs')) {
    /**
     * @return array<int, array{title: string, slug: string}>
     */
    function matrix_seed_care_treatment_service_link_defs(): array
    {
        return [
            ['title' => 'Homecare service', 'slug' => 'homecare-service'],
            ['title' => 'Remote Services', 'slug' => 'remote-services'],
            ['title' => 'Addiction and Dual Diagnosis', 'slug' => 'addiction-and-dual-diagnosis'],
            ['title' => 'Anxiety Disorders Programme', 'slug' => 'anxiety-disorders-programme'],
            ['title' => 'Bipolar Education Programme', 'slug' => 'bipolar-education-programme'],
            ['title' => 'Depression Recovery Programme', 'slug' => 'depression-recovery-programme'],
            ['title' => 'Eating Disorders Programme', 'slug' => 'eating-disorders-programme'],
            ['title' => 'Psychosis Recovery Programme', 'slug' => 'psychosis-recovery-programme'],
            ['title' => 'Young Adult Service', 'slug' => 'young-adult-service'],
            ['title' => 'Older Adult Service', 'slug' => 'older-adult-service'],
        ];
    }
}

if (! function_exists('matrix_seed_care_treatment_section_links')) {
    /**
     * @return array<int, array{link: array{title: string, url: string, target: string}}>
     */
    function matrix_seed_care_treatment_section_links(string $section): array
    {
        if ($section === 'medication') {
            return [
                [
                    'link' => [
                        'title' => 'Medication',
                        'url' => matrix_seed_care_treatment_url('medication'),
                        'target' => '',
                    ],
                ],
            ];
        }

        $rows = [];

        foreach (matrix_seed_care_treatment_service_link_defs() as $link) {
            $rows[] = [
                'link' => [
                    'title' => $link['title'],
                    'url' => matrix_seed_care_treatment_url($link['slug']),
                    'target' => '',
                ],
            ];
        }

        return $rows;
    }
}

if (! function_exists('matrix_seed_care_treatment_useful_links_block')) {
    /**
     * @return array<string, mixed>
     */
    function matrix_seed_care_treatment_useful_links_block(string $section): array
    {
        return [
            'acf_fc_layout' => 'useful_links',
            'heading_tag' => matrix_page_seed_heading(2),
            'heading' => 'In this section',
            'variant' => 'flexi',
            'links' => matrix_seed_care_treatment_section_links($section),
            'background_color' => '#F1F8F9',
        ];
    }
}

if (! function_exists('matrix_seed_care_treatment_accordion_item')) {
    function matrix_seed_care_treatment_accordion_item(string $title, string $content, bool $starts_open = false): array
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

if (! function_exists('matrix_seed_care_treatment_content_block')) {
    /**
     * @return array<string, mixed>
     */
    function matrix_seed_care_treatment_content_block(string $heading, string $content, string $background = 'white'): array
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

if (! function_exists('matrix_seed_care_treatment_hero_block')) {
    /**
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    function matrix_seed_care_treatment_hero_block(array $config): array
    {
        $home = home_url('/');
        $what_we_offer_url = home_url('/what-we-offer/');

        $breadcrumbs = [
            ['breadcrumb_link' => ['title' => 'Home', 'url' => $home, 'target' => '']],
            ['breadcrumb_link' => ['title' => 'What we offer', 'url' => $what_we_offer_url, 'target' => '']],
        ];

        if (($config['section'] ?? '') === 'our-services') {
            $breadcrumbs[] = [
                'breadcrumb_link' => [
                    'title' => 'Our Services',
                    'url' => matrix_seed_care_treatment_url('homecare-service'),
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

if (! function_exists('matrix_seed_care_treatment_footer_cta_rows')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function matrix_seed_care_treatment_footer_cta_rows(): array
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

if (! function_exists('matrix_seed_ensure_care_treatment')) {
    /**
     * @param array<string, mixed> $args
     */
    function matrix_seed_ensure_care_treatment(array $args): int
    {
        $slug = (string) ($args['slug'] ?? '');
        $title = (string) ($args['title'] ?? '');
        $seed_key = (string) ($args['seed_key'] ?? $slug);
        $section_term_id = (int) ($args['section_term_id'] ?? 0);

        $existing = get_posts([
            'post_type' => 'care_treatment',
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
                'post_type' => 'care_treatment',
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
            wp_set_object_terms($post_id, [$section_term_id], 'care_treatment_section');
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

if (! function_exists('matrix_seed_care_treatment_ensure_term')) {
    function matrix_seed_care_treatment_ensure_term(string $name, string $slug): int
    {
        $existing = get_term_by('slug', $slug, 'care_treatment_section');

        if ($existing instanceof WP_Term) {
            return (int) $existing->term_id;
        }

        $created = wp_insert_term($name, 'care_treatment_section', ['slug' => $slug]);

        if (is_wp_error($created)) {
            return 0;
        }

        return (int) ($created['term_id'] ?? 0);
    }
}
