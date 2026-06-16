<?php

/**
 * Shared helpers for seeding Outpatient Clinics CPT posts.
 *
 * @see scripts/seed-outpatient-clinics.php
 */

if (! function_exists('matrix_seed_outpatient_clinic_slugs')) {
    /**
     * @return array<string, string> seed_key => slug
     */
    function matrix_seed_outpatient_clinic_slugs(): array
    {
        return [
            'about' => 'about-the-dean-clinics',
            'adolescent' => 'adolescent-dean-clinic',
            'cork' => 'dean-clinic-cork',
            'galway' => 'dean-clinic-galway',
            'lucan' => 'dean-clinic-lucan',
            'st-patricks' => 'dean-clinic-st-patricks',
            'associate' => 'associate-dean-clinics',
            'how-to-access' => 'how-to-access',
        ];
    }
}

if (! function_exists('matrix_seed_outpatient_clinic_url')) {
    function matrix_seed_outpatient_clinic_url(string $slug): string
    {
        return home_url('/outpatient-clinics/' . trim($slug, '/') . '/');
    }
}

if (! function_exists('matrix_seed_outpatient_section_links')) {
    /**
     * @return array<int, array{link: array{title: string, url: string, target: string}}>
     */
    function matrix_seed_outpatient_section_links(): array
    {
        $links = [
            ['title' => 'About the Dean Clinics', 'slug' => 'about-the-dean-clinics'],
            ['title' => 'Adolescent Dean Clinic', 'slug' => 'adolescent-dean-clinic'],
            ['title' => 'Dean Clinic Cork', 'slug' => 'dean-clinic-cork'],
            ['title' => 'Dean Clinic Galway', 'slug' => 'dean-clinic-galway'],
            ['title' => 'Dean Clinic Lucan', 'slug' => 'dean-clinic-lucan'],
            ['title' => 'Dean Clinic St Patrick\'s', 'slug' => 'dean-clinic-st-patricks'],
            ['title' => 'Associate Dean Clinics', 'slug' => 'associate-dean-clinics'],
            ['title' => 'How to Access', 'slug' => 'how-to-access'],
        ];

        $rows = [];

        foreach ($links as $link) {
            $rows[] = [
                'link' => [
                    'title' => $link['title'],
                    'url' => matrix_seed_outpatient_clinic_url($link['slug']),
                    'target' => '',
                ],
            ];
        }

        return $rows;
    }
}

if (! function_exists('matrix_seed_outpatient_useful_links_block')) {
    /**
     * @return array<string, mixed>
     */
    function matrix_seed_outpatient_useful_links_block(): array
    {
        return [
            'acf_fc_layout' => 'useful_links',
            'heading_tag' => matrix_page_seed_heading(2),
            'heading' => 'In this section',
            'variant' => 'flexi',
            'links' => matrix_seed_outpatient_section_links(),
            'background_color' => '#F1F8F9',
        ];
    }
}

if (! function_exists('matrix_seed_outpatient_accordion_item')) {
    function matrix_seed_outpatient_accordion_item(string $title, string $content, bool $starts_open = false): array
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

if (! function_exists('matrix_seed_outpatient_content_block')) {
    /**
     * @return array<string, mixed>
     */
    function matrix_seed_outpatient_content_block(string $heading, string $content, string $background = 'white'): array
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

if (! function_exists('matrix_seed_outpatient_hero_block')) {
    /**
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    function matrix_seed_outpatient_hero_block(array $config): array
    {
        $home = home_url('/');
        $what_we_offer_url = home_url('/what-we-offer/');
        $landing_url = home_url('/what-we-offer/outpatient-care-dean-clinics/');

        return [
            'acf_fc_layout' => 'hero_with_breadcrumbs',
            'layout_style' => 'image_split',
            'show_breadcrumbs' => 1,
            'breadcrumb_source' => 'manual',
            'manual_breadcrumbs' => [
                ['breadcrumb_link' => ['title' => 'Home', 'url' => $home, 'target' => '']],
                ['breadcrumb_link' => ['title' => 'What we offer', 'url' => $what_we_offer_url, 'target' => '']],
                ['breadcrumb_link' => ['title' => 'Outpatient Care - Dean Clinics', 'url' => $landing_url, 'target' => '']],
            ],
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

if (! function_exists('matrix_seed_outpatient_footer_cta_rows')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function matrix_seed_outpatient_footer_cta_rows(): array
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

if (! function_exists('matrix_seed_outpatient_contact_cta')) {
    /**
     * @return array<string, mixed>
     */
    function matrix_seed_outpatient_contact_cta(string $heading, string $phone): array
    {
        $tel = preg_replace('/\s+/', '', $phone);

        return [
            'acf_fc_layout' => 'content_cta',
            'heading_tag' => matrix_page_seed_heading(2),
            'heading' => $heading,
            'body' => '<p><strong>Call ' . esc_html($heading) . '</strong></p>',
            'button_link' => [
                'title' => $phone,
                'url' => 'tel:' . esc_attr($tel),
                'target' => '',
            ],
            'background_type' => 'color',
            'background_color' => '#CEF2EE',
        ];
    }
}

if (! function_exists('matrix_seed_ensure_outpatient_clinic')) {
    /**
     * @param array<string, mixed> $args
     */
    function matrix_seed_ensure_outpatient_clinic(array $args): int
    {
        $slug = (string) ($args['slug'] ?? '');
        $title = (string) ($args['title'] ?? '');
        $seed_key = (string) ($args['seed_key'] ?? $slug);

        $existing = get_posts([
            'post_type' => 'outpatient_clinics',
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
                'post_type' => 'outpatient_clinics',
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
