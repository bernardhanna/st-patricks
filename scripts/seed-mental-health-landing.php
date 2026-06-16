<?php

/**
 * Seed Mental Health hub page at /mental-health/ (live site IA).
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-mental-health-landing.php
 */

require_once __DIR__ . '/lib/page-seed-conventions.php';
require_once get_template_directory() . '/inc/migrate-functions.php';
require_once get_template_directory() . '/inc/mental-health-functions.php';

if (! function_exists('matrix_seed_mh_attachment_url')) {
    function matrix_seed_mh_attachment_url(string $source_path): string
    {
        $attachment_id = matrix_migrate_attachment_id_for_source_path($source_path);

        if ($attachment_id > 0) {
            $url = wp_get_attachment_url($attachment_id);

            return is_string($url) ? $url : '';
        }

        return matrix_migrate_live_url($source_path);
    }
}

$post = get_page_by_path('mental-health');

if ($post instanceof WP_Post) {
    $post_id = (int) $post->ID;
} else {
    $post_id = (int) wp_insert_post([
        'post_type' => 'page',
        'post_status' => 'publish',
        'post_title' => 'Mental health',
        'post_name' => 'mental-health',
        'post_content' => '',
    ], true);

    if (is_wp_error($post_id) || $post_id < 1) {
        if (class_exists('WP_CLI')) {
            WP_CLI::error('Could not create mental-health page.');
        }

        exit(1);
    }
}

$home = home_url('/');
$faqs_url = home_url('/service-users-and-visitors/frequently-asked-questions-faqs/');
$referrals_url = home_url('/make-a-referral/');
$information_centre_url = home_url('/information-centre/');

$hero_image_id = matrix_migrate_attachment_id_for_source_path(
    '/media/1435/st-patricks-mental-health-services-main-entrance.jpg'
);

$condition_cards = [
    [
        'title' => 'Addiction & Dual Diagnosis',
        'slug' => 'addiction-dual-diagnosis',
        'image' => '/media/1639/personality-disorder.jpeg',
        'tone' => 'bg1',
    ],
    [
        'title' => 'Anxiety',
        'slug' => 'anxiety',
        'image' => '/media/3279/anxiety-featured-image.png',
        'tone' => 'bg1',
    ],
    [
        'title' => 'Bipolar Disorder',
        'slug' => 'bipolar-disorder',
        'image' => '/media/3263/bipolar-disorder-featured-image.png',
        'tone' => 'bg1',
    ],
    [
        'title' => 'Depression',
        'slug' => 'depression',
        'image' => '/media/3264/depression-featured-image.jpg',
        'tone' => 'bg2',
    ],
    [
        'title' => 'Eating Disorders',
        'slug' => 'eating-disorders',
        'image' => '/media/1863/eating-disorders-causes-characteristics-common-misconceptions.jpg',
        'tone' => 'bg2',
    ],
    [
        'title' => 'Personality Disorders',
        'slug' => 'personality-disorders',
        'image' => '/media/2113/what-are-personality-disorders.jpg',
        'tone' => 'bg2',
    ],
    [
        'title' => 'Schizophrenia & Psychosis',
        'slug' => 'schizophrenia-psychosis',
        'image' => '/media/3292/still-just-me-psychosis-featured-image.jpg',
        'tone' => 'bg3',
    ],
    [
        'title' => 'Older Adults',
        'slug' => 'older-adults',
        'image' => '/media/2301/mental-health-older-adult.jpg',
        'tone' => 'bg3',
    ],
    [
        'title' => 'Young Adults',
        'slug' => 'young-adults',
        'image' => '/media/1662/adobestock_88353473.jpeg',
        'tone' => 'bg3',
    ],
];

$inner_nav_links = [];
$grid_links = [];

foreach ($condition_cards as $card) {
    $url = matrix_mental_health_condition_url($card['slug']);

    $inner_nav_links[] = [
        'link' => [
            'title' => $card['title'],
            'url' => $url,
            'target' => '',
        ],
    ];

    $grid_links[] = [
        'icon' => '',
        'image_url' => matrix_seed_mh_attachment_url($card['image']),
        'title' => $card['title'],
        'description' => '',
        'link' => [
            'title' => $card['title'],
            'url' => $url,
            'target' => '',
        ],
        'card_tone' => $card['tone'],
    ];
}

$hero_intro = "There is no health without mental health. Here at St Patrick's Mental Health Services, we have been looking after the mental wellbeing of Ireland for over 270 years.";

$intro_body = '<p>Having access to the facts that will help you understand your mental health can play a key part in your road to recovery.</p>'
    . '<p>We have up-to-date information available below on a range of mental health issues, including addiction and dual diagnosis, anxiety, bipolar disorder, depression, eating disorders, personality disorder, schizophrenia and psychosis, and mental health difficulties in young adults.</p>'
    . '<p>If you are receiving care in St Patrick\'s University Hospital, you can also drop into our <a href="' . esc_url($information_centre_url) . '">Information Centre</a>, where we have a range of books and booklets relating to all aspects of mental health.</p>'
    . '<p>For more information on mental health and our services, please <a href="' . esc_url($faqs_url) . '">visit our Frequently Asked Questions page</a>.</p>';

$flexi_rows = [
    [
        'acf_fc_layout' => 'hero_with_breadcrumbs',
        'layout_style' => 'image_split',
        'show_breadcrumbs' => 1,
        'breadcrumb_source' => 'manual',
        'manual_breadcrumbs' => [
            ['breadcrumb_link' => ['title' => 'Home', 'url' => $home, 'target' => '']],
        ],
        'current_crumb_label' => 'Mental health',
        'heading_tag' => matrix_page_seed_heading(1),
        'heading' => 'Mental health',
        'content' => '<p>' . esc_html($hero_intro) . '</p>',
        'primary_button' => '',
        'hero_image' => $hero_image_id,
        'background_color' => '#C6ECF4',
        'breadcrumb_background_color' => '#F1F8F9',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
    ],
    [
        'acf_fc_layout' => 'useful_links',
        'heading_tag' => matrix_page_seed_heading(2),
        'heading' => 'In this section',
        'variant' => 'flexi',
        'links' => $inner_nav_links,
        'background_color' => '#F1F8F9',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => '',
        'heading_tag' => matrix_page_seed_heading(2),
        'accent_position' => 'below_heading',
        'intro_text' => '',
        'content' => $intro_body,
        'image' => '',
        'column_layout' => 'one_column',
        'layout_style' => 'image_left',
        'text_width' => 'wide',
        'background_type' => 'color',
        'background_color' => '#FFFFFF',
    ],
    [
        'acf_fc_layout' => 'about_links_grid',
        'heading_tag' => matrix_page_seed_heading(2),
        'heading_text' => 'Mental health conditions',
        'intro_text' => '',
        'links' => $grid_links,
        'bg_color' => '#F1F8F9',
        'heading_color' => '#0B0B08',
        'intro_color' => '#4A4B37',
        'columns' => '3',
    ],
    [
        'acf_fc_layout' => 'content_cta',
        'heading_tag' => matrix_page_seed_heading(2),
        'heading' => 'Queries',
        'body' => '<p>For general queries, please call us. For more on mental health and our services, see our frequently asked questions (FAQs).</p><p><strong>01 249 3200</strong></p>',
        'button_link' => [
            'title' => 'See our FAQs',
            'url' => $faqs_url,
            'target' => '',
        ],
        'background_type' => 'color',
        'background_color' => '#C6ECF4',
    ],
    [
        'acf_fc_layout' => 'content_cta',
        'heading_tag' => matrix_page_seed_heading(2),
        'heading' => 'Referrals',
        'body' => '<p>Contact our Referral and Assessment Service for queries regarding referrals to our services.</p><p><strong>01 249 3635</strong></p>',
        'button_link' => [
            'title' => 'See more from our referrals team',
            'url' => $referrals_url,
            'target' => '',
        ],
        'background_type' => 'color',
        'background_color' => '#CEF2EE',
    ],
];

update_post_meta($post_id, '_matrix_migrate_old_path', 'mental-health');
update_field('hero_content_blocks', [], $post_id);
update_field('flexible_content_blocks', $flexi_rows, $post_id);

flush_rewrite_rules(false);

$saved_rows = get_field('flexible_content_blocks', $post_id);
$saved_count = is_array($saved_rows) ? count($saved_rows) : 0;

if (class_exists('WP_CLI')) {
    if ($saved_count === count($flexi_rows)) {
        WP_CLI::success(sprintf(
            'Seeded Mental Health hub page (%d) at %s with %d flexi blocks and %d condition cards.',
            $post_id,
            get_permalink($post_id),
            $saved_count,
            count($grid_links)
        ));
    } else {
        WP_CLI::warning(sprintf(
            'Updated page %d but expected %d blocks, found %d.',
            $post_id,
            count($flexi_rows),
            $saved_count
        ));
    }
}
