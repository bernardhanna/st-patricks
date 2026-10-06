<?php

/**
 * Rebuild About Us > Nurses from Drive Library 3 content + layout notes.
 *
 * Sources:
 * - old/content/.../Nurses/Nursing.docx
 * - old/content/.../Nurses/nursing page layout.docx
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/rebuild-nurses-from-drive.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

$post_id = (int) (get_page_by_path('about-us/our-team/nurses')?->ID
    ?? get_page_by_path('about-us/nurses')?->ID
    ?? 0);
if ($post_id === 0) {
    WP_CLI::error('Could not find about-us/nurses');
}

$home = untrailingslashit(home_url('/'));

$p = static function (string $html): string {
    return '<p>' . $html . '</p>';
};

$sideload = static function (string $path_or_url, string $filename, int $parent_id): int {
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $existing = get_posts([
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => 1,
        'fields' => 'ids',
        's' => pathinfo($filename, PATHINFO_FILENAME),
    ]);
    if ($existing !== []) {
        return (int) $existing[0];
    }

    if (str_starts_with($path_or_url, 'http')) {
        $tmp = download_url($path_or_url, 60);
        if (is_wp_error($tmp)) {
            return 0;
        }
    } else {
        if (! is_file($path_or_url)) {
            return 0;
        }
        $tmp = wp_tempnam($filename);
        copy($path_or_url, $tmp);
    }

    $id = media_handle_sideload([
        'name' => $filename,
        'tmp_name' => $tmp,
    ], $parent_id);

    return is_wp_error($id) ? 0 : (int) $id;
};

$img_id = $sideload(
    'https://st-patricks.s1.matrix-test.com/wp-content/uploads/2026/08/AM8I8710-scaled.jpg',
    'AM8I8710-scaled.jpg',
    $post_id
);
if ($img_id <= 0 && is_file('/tmp/AM8I8710-scaled.jpg')) {
    $img_id = $sideload('/tmp/AM8I8710-scaled.jpg', 'AM8I8710-scaled.jpg', $post_id);
}

$podcast_url = get_permalink(1308) ?: ($home . '/mental-health-conversations-podcast/');
$nursing_story_url = get_permalink(1169) ?: ($home . '/nursing-in-st-patrick-s-mental-health-services/');
$careers_url = $home . '/recruitment-and-useful-information/';
$apply_url = $home . '/recruitment-and-useful-information/how-to-apply-for-a-role/';

$podcast_img = (int) get_post_thumbnail_id(1308);
$story_img = (int) get_post_thumbnail_id(1169);
$careers_img = (int) get_post_thumbnail_id(210);
if ($careers_img <= 0) {
    $careers_img = (int) get_post_thumbnail_id(250);
}

$hero_intro = $p('Our nursing team plays a central role across all of our mental health services.');

$about = $p('At St Patrick’s Mental Health Services (SPMHS), our nurses share the goal of delivering high quality care to people experiencing mental health difficulties with empathy, dignity, and professionalism.')
    . $p('We are proud of our strong nursing culture and we place a huge importance on continually developing the skills and supporting the growth of our nursing staff.')
    . $p('You can find out more about our nursing team below.');

$what_nurses_do = $p('Our nurses work in a diverse range of settings across our service.')
    . $p('Ward-based nurses provide care and treatment 24 hours a day, seven days a week. The type of care and level of intervention provided by ward nurses is individual to each service user’s needs. The care they deliver encourages independence and is underpinned by a recovery-orientated philosophy. At ward level, nurses provide many types of intervention, including:')
    . '<ul>'
    . '<li>assessment</li>'
    . '<li>psychoeducation, or sharing information about mental health</li>'
    . '<li>supportive counselling</li>'
    . '<li>supportive observation</li>'
    . '<li>assistance with self-care and independent living</li>'
    . '<li>delivery of our Recovery (WRAP®) Programme</li>'
    . '<li>working with service users on developing their initial care plan.</li>'
    . '</ul>'
    . '<h3>A number of our nurses work in specialist areas including:</h3>'
    . '<ul>'
    . '<li>our Referrals and Admissions Service</li>'
    . '<li>nurse education and practice development</li>'
    . '<li>Cognitive Behavioural Therapy</li>'
    . '<li>Electroconvulsive Therapy</li>'
    . '<li>day services</li>'
    . '<li>outpatient care in the Dean Clinics.</li>'
    . '</ul>'
    . $p('Nurses also fulfil specialist and advanced practice roles in all our programmes, such as assessment, psychotherapy and facilitating therapeutic groups. Nurses can also act in the key worker role of a service user’s multidisciplinary team, ensuring that the service user and their MDT are working well together and progressing their personalised care plan.');

$hero = matrix_orlaith_hero_row('Nurses', $hero_intro, $img_id);
$hero['layout_style'] = $img_id > 0 ? 'image_split' : 'title_accent';
$hero['text_max_width'] = 'default';
$hero['heading_max_width'] = 'default';
$hero['current_crumb_label'] = 'Nurses';
$hero['background_color'] = '#C6ECF4';
$hero['accent_color'] = '#6FC9C0';

$related = [
    'acf_fc_layout' => 'related_cards',
    'heading_tag' => 'h2',
    'heading' => 'More on nursing in SPMHS',
    'intro_text' => 'Explore more about what our nurses do and what a nursing career in SPMHS looks like.',
    'cards' => [
        [
            'image' => $podcast_img > 0 ? $podcast_img : '',
            'title' => 'Listen to our nursing podcast',
            'description' => 'Mental Health Conversations is made by nurses, for nurses.',
            'link' => [
                'title' => 'Listen to our nursing podcast',
                'url' => $podcast_url,
                'target' => '',
            ],
        ],
        [
            'image' => $story_img > 0 ? $story_img : '',
            'title' => 'Learn about our nursing roles',
            'description' => 'Hear from one of our nursing team on his work in mental healthcare.',
            'link' => [
                'title' => 'Learn about our nursing roles',
                'url' => $nursing_story_url,
                'target' => '',
            ],
        ],
        [
            'image' => $careers_img > 0 ? $careers_img : '',
            'title' => 'Join our nursing team',
            'description' => 'Learn more about recruitment and how to apply for a role.',
            'link' => [
                'title' => 'Join our nursing team',
                'url' => $careers_url,
                'target' => '',
            ],
        ],
    ],
    'background_color' => '#FBFAF7',
    'columns' => '3',
];

$flexi = [
    $hero,
    matrix_orlaith_content_row('', $about, 'white'),
    matrix_orlaith_content_row('What our nurses do', $what_nurses_do, 'cream'),
    $related,
];

wp_update_post([
    'ID' => $post_id,
    'post_title' => 'Nurses',
    'post_name' => 'nurses',
]);

update_field('flexible_content_blocks', $flexi, $post_id);

WP_CLI::success('Rebuilt Nurses page ' . $post_id . ' → ' . get_permalink($post_id));
WP_CLI::log('Hero image ID: ' . $img_id);
foreach (get_field('flexible_content_blocks', $post_id) as $i => $row) {
    $label = $row['heading'] ?? ($row['heading_text'] ?? '');
    WP_CLI::log(sprintf('[%d] %s %s', $i, $row['acf_fc_layout'] ?? '', $label));
}
