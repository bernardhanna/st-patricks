<?php

/**
 * Rebuild About Us > Partnering with service users from Drive Library 3.
 *
 * Source: Partnering with service users.docx
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/rebuild-partnering-with-service-users-from-drive.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

$post_id = (int) (get_page_by_path('about-us/partnering-with-service-users')?->ID ?? 0);
if ($post_id === 0) {
    WP_CLI::error('Could not find about-us/partnering-with-service-users');
}

$p = static function (string $html): string {
    return '<p>' . $html . '</p>';
};

$hero_image = matrix_orlaith_find_image(4092, 'Service user engagement.png');
if ($hero_image <= 0) {
    $hero_image = matrix_orlaith_find_image(2632, 'service-user-engagement-featured-image');
}

$hero_intro = $p("Partnership and consultation with service users form a critical part of how we operate here in St Patrick's Mental Health Services.");

$body = $p('The feedback we receive from service users and their supporters has a tangible and valuable effect on how we develop as an organisation.')
    . $p('We believe in a more inclusive system of service user representation. We will continue to develop and enhance our service user engagement structures so that our service users remain at the core of everything we do.')
    . $p('We will deepen the involvement that service users, and their supporters, have in shaping our services and in contributing to achieving our strategic objectives such as advancing research, strengthening advocacy, and educating the general public about mental health.')
    . $p('The establishment and introduction of a peer support service will also be explored, and we will continue to champion diversity and inclusion to ensure our service users are wholly and equally represented.');

$flexi = [
    matrix_orlaith_hero_row('Partnering with service users', $hero_intro, $hero_image),
    matrix_orlaith_content_row('', $body, 'white'),
    matrix_orlaith_video_row(
        'Video resources',
        '',
        [
            [
                'url' => 'https://www.youtube.com/watch?v=MZHEqYPaRJk',
                'title' => 'Service user engagement strategy',
                'caption' => 'Find out more about how we are continuing to involve our service users in shaping how we evolve, while also developing new ways to partner with service users.',
                'poster' => $hero_image,
            ],
            [
                'url' => 'https://www.youtube.com/watch?v=QQDB6ZyvjBk',
                'title' => 'SUAS and SUAN',
                'caption' => 'Learn more about our Service User and Supporters Council (SUAS) and Service User Advisory Network (SUAN).',
            ],
        ]
    ),
    matrix_orlaith_useful_links_row([
        'Service User and Supporters Council' => 'service-users-and-visitors/service-user-participation/service-user-and-supporters-council',
        'Service User Advisory Network' => 'service-users-and-visitors/service-user-participation/service-user-advisory-network',
        'Family, Carers and Supporters Advisory Network' => 'service-users-and-visitors/service-user-participation/family-carers-and-supporters-advisory-network',
        'Service user experience surveys' => 'service-users-and-visitors/feedback-and-comments/service-user-experience-survey',
        'Feedback and comments' => 'service-users-and-visitors/feedback-and-comments',
        'Frequently asked questions' => 'service-users-and-visitors/frequently-asked-questions-faqs',
    ]),
];

update_field('flexible_content_blocks', $flexi, $post_id);
if ($hero_image > 0) {
    set_post_thumbnail($post_id, $hero_image);
}

WP_CLI::success(sprintf(
    'Rebuilt Partnering with service users #%d → %s',
    $post_id,
    get_permalink($post_id)
));

foreach ((array) get_field('flexible_content_blocks', $post_id) as $i => $row) {
    $layout = (string) ($row['acf_fc_layout'] ?? '?');
    $heading = wp_strip_all_tags((string) ($row['heading'] ?? ''));
    $extra = '';
    if ($layout === 'video_showcase' && ! empty($row['slides'])) {
        $extra = ' | slides=' . count($row['slides']);
    }
    if ($layout === 'useful_links' && ! empty($row['links'])) {
        $extra = ' | links=' . count($row['links']);
    }
    WP_CLI::log("[{$i}] {$layout} {$heading}{$extra}");
}
