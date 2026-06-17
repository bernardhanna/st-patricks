<?php

/**
 * Seed Healthcare Professionals landing page (Figma 2888:2478).
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-healthcare-professionals.php
 */

require_once __DIR__ . '/lib/page-seed-conventions.php';
require_once __DIR__ . '/lib/healthcare-faqs-seed.php';
require_once __DIR__ . '/lib/healthcare-professionals-landing-seed-data.php';
require_once get_template_directory() . '/inc/migrate-functions.php';

$post_id = (int) (get_page_by_path('healthcare-professionals')?->ID ?? 0);

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at healthcare-professionals.');
    }

    exit(1);
}

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
        $filename = $path ? basename((string) $path) : 'figma-asset.jpg';

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

if (! function_exists('matrix_seed_attachment_url')) {
    function matrix_seed_attachment_url(int $attachment_id): string
    {
        if ($attachment_id <= 0) {
            return '';
        }

        $url = wp_get_attachment_url($attachment_id);

        return is_string($url) ? $url : '';
    }
}

$home = home_url('/');
$referrals_url = home_url('/make-a-referral/');

$hero_image_id = matrix_seed_resolve_image(
    'https://www.figma.com/api/mcp/asset/9f265cc3-6a87-409a-910a-33366708aa0c',
    'healthcare-professionals-hero-2780-4288',
    'Healthcare Professionals hero'
);

$parent_term_id = matrix_seed_hp_faq_ensure_term('healthcare-professionals', 'Healthcare Professionals');
$referrals_term_id = matrix_seed_hp_faq_ensure_term('hp-referrals', 'Referrals and admissions', $parent_term_id);
$services_term_id = matrix_seed_hp_faq_ensure_term('hp-services', 'Services and assessments', $parent_term_id);
$insurance_term_id = matrix_seed_hp_faq_ensure_term('hp-insurance', 'Insurance and funding', $parent_term_id);
$clinical_term_id = matrix_seed_hp_faq_ensure_term('hp-clinical', 'Clinical information and professional development', $parent_term_id);

$term_map = [
    'referrals' => $referrals_term_id,
    'services' => $services_term_id,
    'insurance' => $insurance_term_id,
    'clinical' => $clinical_term_id,
];

foreach (matrix_seed_hp_faq_sections() as $section_key => $section) {
    $term_id = (int) ($term_map[$section_key] ?? 0);

    foreach ($section['items'] as $index => $item) {
        matrix_seed_hp_faq_ensure_post(
            $item['title'],
            $item['content'],
            'hp-faq-' . $section_key . '-' . ($index + 1),
            [$term_id, $parent_term_id],
            $index + 1
        );
    }
}

$faq_ids = matrix_seed_hp_faq_landing_ids();

$hp_links = [];

foreach (matrix_seed_hp_landing_card_definitions($home) as $card) {
    $image_url = '';

    if (! empty($card['source_path'])) {
        $migrated_id = (int) matrix_migrate_attachment_id_for_source_path($card['source_path']);

        if ($migrated_id > 0) {
            $image_url = matrix_seed_attachment_url($migrated_id);
        }
    }

    if ($image_url === '' && ! empty($card['figma'])) {
        $image_id = matrix_seed_resolve_image(
            $card['figma'],
            $card['cache'],
            'Healthcare Professionals grid – ' . $card['title']
        );
        $image_url = matrix_seed_attachment_url($image_id);
    }

    $hp_links[] = [
        'icon' => '',
        'image_url' => $image_url,
        'title' => $card['title'],
        'description' => '',
        'link' => [
            'title' => $card['title'],
            'url' => $card['url'],
            'target' => '',
        ],
        'card_tone' => $card['tone'],
    ];
}

$grid_padding = [
    ['screen_size' => 'mob', 'padding_top' => '3', 'padding_bottom' => '6.25'],
    ['screen_size' => 'lg', 'padding_top' => '6.25', 'padding_bottom' => '6.25'],
];

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
        ],
        'current_crumb_label' => 'Healthcare Professionals',
        'heading_tag' => 'h1',
        'heading' => 'Healthcare Professionals',
        'content' => '<p>' . esc_html(matrix_seed_hp_landing_hero_intro()) . '</p>',
        'primary_button' => [
            'title' => 'Make a referral',
            'url' => $referrals_url,
            'target' => '',
        ],
        'hero_image' => $hero_image_id,
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
    ],
    [
        'acf_fc_layout' => 'about_links_grid',
        'heading_tag' => 'h2',
        'heading_text' => 'Healthcare professionals',
        'intro_text' => '',
        'links' => $hp_links,
        'bg_color' => '#F1F8F9',
        'heading_color' => '#1E244B',
        'intro_color' => '#4A4B37',
        'columns' => '3',
        'layout_style' => 'image_feature',
        'padding_settings' => $grid_padding,
    ],
];

update_field('hero_content_blocks', [], $post_id);
update_field('flexible_content_blocks', $flexi_rows, $post_id);

$saved_rows = get_field('flexible_content_blocks', $post_id);
$saved_count = is_array($saved_rows) ? count($saved_rows) : 0;

if (class_exists('WP_CLI')) {
    if ($saved_count === count($flexi_rows)) {
        WP_CLI::success(sprintf(
            'Seeded Healthcare Professionals landing page (%d) with %d flexi blocks, %d link cards, and %d FAQs.',
            $post_id,
            $saved_count,
            count($hp_links),
            count($faq_ids)
        ));
    } else {
        WP_CLI::warning(sprintf(
            'Updated page %d but expected %d blocks, found %d.',
            $post_id,
            count($flexi_rows),
            $saved_count
        ));
    }

    WP_CLI::log('Page: ' . get_permalink($post_id));
}
