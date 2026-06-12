<?php

if (! function_exists('matrix_seed_import_remote_image')) {
    function matrix_seed_import_remote_image(string $url, string $title, string $cache_key): int
    {
        if ($url === '') {
            return 0;
        }

        $existing = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => '_matrix_seed_figma_key',
                    'value' => $cache_key,
                ],
            ],
        ]);

        if ($existing !== []) {
            return (int) $existing[0]->ID;
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $tmp = download_url($url, 30);

        if (is_wp_error($tmp)) {
            return 0;
        }

        $path = parse_url($url, PHP_URL_PATH);
        $filename = $path ? basename($path) : 'figma-asset.jpg';

        if (! preg_match('/\.(jpe?g|png|gif|webp|svg)$/i', $filename)) {
            $filename .= '.jpg';
        }

        $file_array = [
            'name' => sanitize_file_name($filename),
            'tmp_name' => $tmp,
        ];

        $attachment_id = media_handle_sideload($file_array, 0, $title);

        if (is_wp_error($attachment_id)) {
            @unlink($tmp);

            return 0;
        }

        update_post_meta($attachment_id, '_matrix_seed_figma_key', $cache_key);
        update_post_meta($attachment_id, '_matrix_seed_figma_url', $url);

        return (int) $attachment_id;
    }
}

if (! function_exists('matrix_seed_resolve_image')) {
    function matrix_seed_resolve_image(string $figma_url, string $cache_key, string $title): int
    {
        $id = matrix_seed_import_remote_image($figma_url, $title, $cache_key);

        if ($id > 0) {
            return $id;
        }

        $attachments = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'post_mime_type' => 'image',
            'orderby' => 'ID',
            'order' => 'DESC',
        ]);

        return $attachments !== [] ? (int) $attachments[0]->ID : 0;
    }
}

if (! function_exists('matrix_seed_build_manual_research_cards')) {
    function matrix_seed_build_manual_research_cards(array $image_ids, array $items): array
    {
        $cards = [];

        foreach ($items as $index => $item) {
            $title = trim((string) ($item['title'] ?? ''));
            $summary = trim((string) ($item['summary'] ?? ''));
            $url = trim((string) ($item['url'] ?? ''));

            if ($title === '' || $url === '') {
                continue;
            }

            $image_id = $image_ids[$index % count($image_ids)] ?? 0;
            $cards[] = [
                'title' => $title,
                'summary' => $summary !== '' ? $summary : '<p>' . esc_html($title) . '</p>',
                'image' => $image_id > 0 ? $image_id : '',
                'link' => [
                    'title' => $title,
                    'url' => $url,
                    'target' => '',
                ],
            ];
        }

        return $cards;
    }
}

if (! function_exists('matrix_get_research_page_seed_assets')) {
    function matrix_get_research_page_seed_assets(): array
    {
        $figma = [
            'hero' => 'https://www.figma.com/api/mcp/asset/7498d9fa-3124-4241-bbff-c5e72cb17aec',
            'card_a' => 'https://www.figma.com/api/mcp/asset/07e0c847-3ce6-4408-94b9-9a2a98347bb8',
            'card_b' => 'https://www.figma.com/api/mcp/asset/06212956-35f5-4e9c-a780-32b9a9586611',
            'card_c' => 'https://www.figma.com/api/mcp/asset/e16321aa-5447-4917-be04-e52b2c545bcd',
            'card_d' => 'https://www.figma.com/api/mcp/asset/f7cde890-22ea-4e3d-b3ce-7a6a646d2e52',
            'spire' => 'https://www.figma.com/api/mcp/asset/bfb3e585-a0c5-4f2a-9ff7-6df1eef9971b',
        ];

        return [
            'hero_image_id' => matrix_seed_resolve_image($figma['hero'], 'research-hero-2780-3856', 'Research hero'),
            'spire_image_id' => matrix_seed_resolve_image($figma['spire'], 'research-spire-2780-3960', 'SPIRE research hub'),
            'card_image_ids' => [
                matrix_seed_resolve_image($figma['card_a'], 'research-card-a-2780-3856', 'Research card image A'),
                matrix_seed_resolve_image($figma['card_b'], 'research-card-b-2780-3856', 'Research card image B'),
                matrix_seed_resolve_image($figma['card_c'], 'research-card-c-2780-3856', 'Research card image C'),
                matrix_seed_resolve_image($figma['card_d'], 'research-card-d-2780-3856', 'Research card image D'),
            ],
        ];
    }
}

if (! function_exists('matrix_get_research_page_seed_copy')) {
    function matrix_get_research_page_seed_copy(): array
    {
        return [
            'hero_content' => '<p>The Academic Institute at St Patrick&rsquo;s Mental Health Services (SPMHS) aims to promote research and to build a strong research culture.</p>',
            'intro_current' => '<p>It will play a crucial role in exploring how best to deliver and improve mental health treatment and evidence-based practice.</p>',
            'intro_past' => '<p>Browse completed research projects undertaken across St Patrick&rsquo;s Mental Health Services.</p>',
            'intro_ethics' => '<p>The Research Ethics Committee reviews applications to undertake research in St Patrick&rsquo;s Mental Health Services that requires ethical approval.</p>',
            'spire_body' => '<p>SPIRE (St Patrick&rsquo;s Institutional Repository) is our online research hub, storing and sharing mental health research from staff across our services. It aims to enhance the quality of mental healthcare and inform future developments in mental health treatment.</p>',
        ];
    }
}

if (! function_exists('matrix_build_research_page_flexi_rows')) {
    function matrix_build_research_page_flexi_rows(array $manual_breadcrumbs): array
    {
        $home = home_url('/');
        $current_projects_url = home_url('/current-research-projects/');
        $past_projects_url = home_url('/past-research-projects/');
        $ethics_committee_url = home_url('/research-ethics-committee/');
        $spire_url = home_url('/research/spire/');

        $assets = matrix_get_research_page_seed_assets();
        $copy = matrix_get_research_page_seed_copy();

        $current_term = get_term_by('slug', 'current', 'research_project_category');
        $past_term = get_term_by('slug', 'past', 'research_project_category');

        $ethics_cards = matrix_seed_build_manual_research_cards($assets['card_image_ids'], [
            [
                'title' => 'Role of the REC',
                'summary' => '<p>Learn how the committee protects the dignity, rights, and welfare of research participants.</p>',
                'url' => $ethics_committee_url,
            ],
            [
                'title' => 'Applications to the REC',
                'summary' => '<p>Find information on submitting research applications that require ethical approval.</p>',
                'url' => $ethics_committee_url,
            ],
            [
                'title' => 'Research governance',
                'summary' => '<p>Understand how research activity is overseen and approved across our services.</p>',
                'url' => $ethics_committee_url,
            ],
            [
                'title' => 'REC membership',
                'summary' => '<p>See how service users, clinicians, and lay members contribute to ethical review.</p>',
                'url' => $ethics_committee_url,
            ],
        ]);

        $section_padding = [
            [
                'screen_size' => 'mob',
                'padding_top' => '3',
                'padding_bottom' => '3',
            ],
            [
                'screen_size' => 'lg',
                'padding_top' => '6.25',
                'padding_bottom' => '6.25',
            ],
        ];

        return [
            [
                'acf_fc_layout' => 'hero_with_breadcrumbs',
                'layout_style' => 'image_split',
                'show_breadcrumbs' => 1,
                'breadcrumb_source' => 'manual',
                'manual_breadcrumbs' => $manual_breadcrumbs,
                'current_crumb_label' => 'Research',
                'heading_tag' => 'h1',
                'heading' => 'Research',
                'content' => $copy['hero_content'],
                'primary_button' => [
                    'title' => 'Mental Health Research Hub',
                    'url' => $spire_url,
                    'target' => '',
                ],
                'hero_image' => $assets['hero_image_id'],
                'background_color' => '#C6ECF4',
                'breadcrumb_background_color' => '#F1F8F9',
                'heading_color' => '#08284B',
                'text_color' => '#08284B',
            ],
            [
                'acf_fc_layout' => 'research_cards_grid',
                'heading' => 'Current Research Projects',
                'heading_tag' => 'h2',
                'intro' => $copy['intro_current'],
                'cards_source' => 'category',
                'posts_per_page' => 4,
                'selected_research_categories' => $current_term instanceof WP_Term ? [(int) $current_term->term_id] : [],
                'footer_button_link' => [
                    'title' => 'All Current Research Projects',
                    'url' => $current_projects_url,
                    'target' => '',
                ],
                'background_color' => '#FFFFFF',
                'padding_settings' => $section_padding,
            ],
            [
                'acf_fc_layout' => 'research_cards_grid',
                'heading' => 'Past Research Projects',
                'heading_tag' => 'h2',
                'intro' => $copy['intro_past'],
                'cards_source' => 'category',
                'posts_per_page' => 4,
                'selected_research_categories' => $past_term instanceof WP_Term ? [(int) $past_term->term_id] : [],
                'footer_button_link' => [
                    'title' => 'All Past Research Projects',
                    'url' => $past_projects_url,
                    'target' => '',
                ],
                'background_color' => '#FBFAF7',
                'padding_settings' => $section_padding,
            ],
            [
                'acf_fc_layout' => 'research_cards_grid',
                'heading' => 'Our Research Ethics Committee',
                'heading_tag' => 'h2',
                'intro' => $copy['intro_ethics'],
                'cards_source' => 'manual',
                'cards' => $ethics_cards,
                'footer_button_link' => [
                    'title' => 'View Ethics Committee',
                    'url' => $ethics_committee_url,
                    'target' => '',
                ],
                'background_color' => '#FFFFFF',
                'padding_settings' => $section_padding,
            ],
            [
                'acf_fc_layout' => 'content',
                'heading' => 'Access our Central Hub for mental health research',
                'heading_tag' => 'h2',
                'accent_position' => 'below_heading',
                'content' => $copy['spire_body'],
                'primary_button' => [
                    'title' => 'Visit SPIRE',
                    'url' => $spire_url,
                    'target' => '',
                ],
                'primary_button_variant' => 'filled',
                'layout_style' => 'image_right',
                'background_type' => 'gradient',
                'background_gradient' => 'linear-gradient(-70.72deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
                'image' => $assets['spire_image_id'],
                'padding_settings' => $section_padding,
            ],
        ];
    }
}
