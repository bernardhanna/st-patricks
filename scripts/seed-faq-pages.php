<?php

/**
 * Seed FAQ pages to match Figma frame 2888:3376.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-faq-pages.php
 */

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

if (! function_exists('matrix_seed_ensure_faq_term')) {
    function matrix_seed_ensure_faq_term(string $slug, string $name): int
    {
        $existing = get_term_by('slug', $slug, 'faq_category');

        if ($existing instanceof WP_Term) {
            return (int) $existing->term_id;
        }

        $created = wp_insert_term($name, 'faq_category', ['slug' => $slug]);

        if (is_wp_error($created)) {
            return 0;
        }

        return (int) ($created['term_id'] ?? 0);
    }
}

if (! function_exists('matrix_seed_ensure_faq_post')) {
    function matrix_seed_ensure_faq_post(string $title, string $content, string $seed_key, int $term_id = 0): int
    {
        $existing = get_posts([
            'post_type' => 'faqs',
            'post_status' => 'any',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => '_matrix_seed_key',
                    'value' => $seed_key,
                ],
            ],
        ]);

        if ($existing !== []) {
            $faq_id = (int) $existing[0]->ID;
            wp_update_post([
                'ID' => $faq_id,
                'post_title' => $title,
                'post_content' => $content,
                'post_status' => 'publish',
            ]);
        } else {
            $faq_id = wp_insert_post([
                'post_type' => 'faqs',
                'post_status' => 'publish',
                'post_title' => $title,
                'post_content' => $content,
            ]);

            if (is_wp_error($faq_id) || ! $faq_id) {
                return 0;
            }

            update_post_meta((int) $faq_id, '_matrix_seed_key', $seed_key);
        }

        if ($term_id > 0) {
            wp_set_object_terms((int) $faq_id, [$term_id], 'faq_category', false);
        }

        return (int) $faq_id;
    }
}

if (! function_exists('matrix_seed_faq_page_rows')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function matrix_seed_faq_page_rows(
        string $home,
        string $section_url,
        string $section_label,
        string $current_crumb,
        string $hero_heading,
        string $hero_copy,
        array $primary_button,
        int $hero_image_id,
        int $faq_category_id
    ): array {
        return [
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
                            'title' => $section_label,
                            'url' => $section_url,
                            'target' => '',
                        ],
                    ],
                ],
                'current_crumb_label' => $current_crumb,
                'heading_tag' => 'h1',
                'heading' => $hero_heading,
                'content' => '<p>' . esc_html($hero_copy) . '</p>',
                'primary_button' => $primary_button,
                'hero_image' => $hero_image_id,
                'background_color' => '#C6ECF4',
                'breadcrumb_background_color' => '#F1F8F9',
                'heading_color' => '#08284B',
                'text_color' => '#08284B',
            ],
            [
                'acf_fc_layout' => 'faqs',
                'show_heading' => 0,
                'layout_style' => 'page',
                'heading' => 'FAQs',
                'heading_tag' => 'h2',
                'source_mode' => 'category',
                'selected_faq_categories' => [$faq_category_id],
                'section_background' => '#FBFAF7',
                'item_background' => '#FFFFFF',
                'open_item_background' => 'linear-gradient(-42.77deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
                'question_color' => '#1E244B',
                'answer_color' => '#08284B',
            ],
        ];
    }
}

$home = home_url('/');
$healthcare_section_url = home_url('/healthcare-professionals/');
$service_users_section_url = home_url('/service-users-and-visitors/');
$healthcare_faqs_url = home_url('/healthcare-professionals/frequently-asked-questions/');
$service_users_faqs_url = home_url('/service-users-and-visitors/frequently-asked-questions-faqs/');

$figma_hero = 'https://www.figma.com/api/mcp/asset/0a8f1acc-26f3-4c86-ba3d-9ea6effb99fa';
$hero_image_id = matrix_seed_resolve_image($figma_hero, 'faq-page-hero-2888-3376', 'FAQ page hero');

$faq_answer = '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat m dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>';

$faq_titles = [
    'Lorem ipsum dolor sit amet lorem consectetur.',
    'Lorem ipsum dolor sit amet consectetur.',
    'Lorem ipsum sit amet consectetur.',
    'Lorem ipsum dolor sit amet lorem consectetur.',
    'Sit amet lorem consectetur.',
    'Lorem ipsum dolor sit amet consectetur.',
    'Lorem ipsum dolor sit amet lorem consectetur.',
    'Lorem ipsum dolor sit amet consectetur.',
    'Lorem ipsum dolor sit amet lorem consectetur.',
    'Sit amet lorem consectetur.',
    'Lorem ipsum dolor sit amet consectetur.',
    'Lorem ipsum dolor sit amet lorem consectetur.',
    'Lorem ipsum dolor sit amet consectetur.',
    'Lorem ipsum dolor sit amet lorem consectetur.',
    'Sit amet lorem consectetur.',
];

$healthcare_term_id = matrix_seed_ensure_faq_term('healthcare-professionals', 'Healthcare Professionals');
$service_users_term_id = matrix_seed_ensure_faq_term('service-users-and-visitors', 'Service Users and Visitors');

foreach ($faq_titles as $index => $faq_title) {
    matrix_seed_ensure_faq_post(
        $faq_title,
        $faq_answer,
        'faq-healthcare-' . ($index + 1),
        $healthcare_term_id
    );
    matrix_seed_ensure_faq_post(
        $faq_title,
        $faq_answer,
        'faq-service-users-' . ($index + 1),
        $service_users_term_id
    );
}

$hero_copy = 'Page context is optional - Service Users FAQs will be the same layout as this Healthcare Professionals page but different content (text can be added updated during content gathering stage).';

$pages = [
    [
        'id' => (int) (get_page_by_path('healthcare-professionals/frequently-asked-questions')?->ID ?? 0),
        'label' => 'Healthcare Professionals FAQ page',
        'rows' => matrix_seed_faq_page_rows(
            $home,
            $healthcare_section_url,
            'Healthcare Professionals',
            'FAQs',
            'FAQs for Healthcare Professionals',
            $hero_copy,
            [
                'title' => 'See Service Users FAQs',
                'url' => $service_users_faqs_url,
                'target' => '',
            ],
            $hero_image_id,
            $healthcare_term_id
        ),
    ],
    [
        'id' => (int) (get_page_by_path('service-users-and-visitors/frequently-asked-questions-faqs')?->ID ?? 0),
        'label' => 'Service Users FAQ page',
        'rows' => matrix_seed_faq_page_rows(
            $home,
            $service_users_section_url,
            'Service Users and Visitors',
            'FAQs',
            'FAQs for Service Users and Visitors',
            $hero_copy,
            [
                'title' => 'See Healthcare Professionals FAQs',
                'url' => $healthcare_faqs_url,
                'target' => '',
            ],
            $hero_image_id,
            $service_users_term_id
        ),
    ],
];

foreach ($pages as $page_config) {
    $post_id = $page_config['id'];

    if ($post_id === 0) {
        if (class_exists('WP_CLI')) {
            WP_CLI::warning('Could not find ' . $page_config['label'] . '.');
        }

        continue;
    }

    update_field('hero_content_blocks', [], $post_id);
    update_field('flexible_content_blocks', $page_config['rows'], $post_id);

    $saved_rows = get_field('flexible_content_blocks', $post_id);
    $saved_count = is_array($saved_rows) ? count($saved_rows) : 0;

    if (class_exists('WP_CLI')) {
        if ($saved_count === count($page_config['rows'])) {
            WP_CLI::success(sprintf(
                'Seeded %s (%d) with %d flexi blocks.',
                $page_config['label'],
                $post_id,
                $saved_count
            ));
        } else {
            WP_CLI::warning(sprintf(
                'Updated %s (%d) but expected %d blocks, found %d.',
                $page_config['label'],
                $post_id,
                count($page_config['rows']),
                $saved_count
            ));
        }
    }
}
