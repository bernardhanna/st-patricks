<?php

/**
 * Rebuild About Us > Occupational therapists from Drive Library 3 + layout.
 *
 * Sources:
 * - old/content/.../Occupational therapists/Occupational therapy page.docx
 * - old/content/.../Occupational therapists/OT page layout.docx
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/rebuild-occupational-therapists-from-drive.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

$post_id = (int) (get_page_by_path('about-us/our-team/occupational-therapists')?->ID
    ?? get_page_by_path('about-us/occupational-therapists')?->ID
    ?? 0);
if ($post_id === 0) {
    WP_CLI::error('Could not find about-us/occupational-therapists');
}

$home = untrailingslashit(home_url('/'));
$img_id = 4025;

$drive_img = get_template_directory() . '/old/content/SPMHS-Content-Gathering-Library 3/02-Page-content/About Us/Occupational therapists/Occupational therapists (1).png';
if ($img_id <= 0 && is_file($drive_img)) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $tmp = wp_tempnam('Occupational-therapists.png');
    copy($drive_img, $tmp);
    $sideloaded = media_handle_sideload([
        'name' => 'Occupational-therapists.png',
        'tmp_name' => $tmp,
    ], $post_id);
    if (! is_wp_error($sideloaded)) {
        $img_id = (int) $sideloaded;
    }
}

$p = static function (string $html): string {
    return '<p>' . $html . '</p>';
};

$ot_day = get_permalink(1185) ?: ($home . '/occupationaltherapyday/');
$routine = get_permalink(2303) ?: ($home . '/new-routine/');
$substance = get_permalink(2112) ?: ($home . '/substance-use-and-occupational-therapy/');

$hero_intro = $p('Our occupational therapists help service users to carry out the activities that they need or want to do to lead healthy and fulfilling lives.');

$about = $p('For our team of occupational therapists here in St Patrick’s Mental Health Services (SPMHS), the main goal of their work is to support service users to participate in the everyday activities that matter to them.')
    . $p('Our occupational therapists are members of our multidisciplinary teams; they work with people of all ages through both group programmes and one-to-one appointments.');

$what_is = $p('Occupational therapy (OT) is a profession concerned with what we do in our daily lives (our occupation) and how this both affects and is affected by our health.')
    . $p('Occupation includes:')
    . '<ul>'
    . '<li>looking after yourself (self-care)</li>'
    . '<li>enjoying your life and being with others (leisure and social life)</li>'
    . '<li>being productive (for example, work or college activities).</li>'
    . '</ul>'
    . $p('OT aims to contribute to a person’s sense of wellbeing, independence, and satisfaction in daily life.');

$what_do = $p('Occupational therapists can support people with all types of mental health difficulties. They know how these difficulties and challenging life events can impact a person’s ability to do the things that are important to them. They understand the links between activity and health, and work with people to maintain the roles and activities that support their health. They use evidence-based information and a person-centred approach to highlight the person’s strengths and preferences and to enable them to live their lives in a meaningful way.')
    . '<h3>Here in SPMHS, examples of work an occupational therapist may do with a service user include:</h3>'
    . '<ul>'
    . '<li>completing an assessment to identify the service user’s current needs and concerns</li>'
    . '<li>exploring lifestyle changes and set related goals to support their recovery</li>'
    . '<li>developing skills to help them live more independently</li>'
    . '<li>finding ways to make their daily activities easier or more enjoyable</li>'
    . '<li>developing a balanced and satisfying routine</li>'
    . '<li>getting ideas or information to help them take part in leisure or community activities</li>'
    . '<li>identifying social supports and outlets they might find helpful</li>'
    . '<li>preparing for their discharge from hospital and to stay well at home.</li>'
    . '</ul>'
    . $p('Because each service user is a unique person, their OT plans are specific to their needs and priorities.');

$hero = matrix_orlaith_hero_row('Occupational therapists', $hero_intro, $img_id);
$hero['layout_style'] = $img_id > 0 ? 'image_split' : 'title_accent';
$hero['text_max_width'] = 'default';
$hero['heading_max_width'] = 'default';
$hero['current_crumb_label'] = 'Occupational therapists';
$hero['background_color'] = '#C6ECF4';
$hero['accent_color'] = '#6FC9C0';

$related = [
    'acf_fc_layout' => 'related_cards',
    'heading_tag' => 'h2',
    'heading' => 'See more from our OT team',
    'intro_text' => '',
    'cards' => [
        [
            'image' => (int) get_post_thumbnail_id(1185) ?: '',
            'title' => 'Self-care and mental health',
            'description' => 'Our OT team explores how wellbeing activities can support mental health.',
            'link' => [
                'title' => 'Self-care and mental health',
                'url' => $ot_day,
                'target' => '',
            ],
        ],
        [
            'image' => (int) get_post_thumbnail_id(2303) ?: '',
            'title' => 'Routine and your mental health',
            'description' => 'OTs can help us shape a routine that reflects our priorities and values.',
            'link' => [
                'title' => 'Routine and your mental health',
                'url' => $routine,
                'target' => '',
            ],
        ],
        [
            'image' => (int) get_post_thumbnail_id(2112) ?: '',
            'title' => 'OT and substance abuse',
            'description' => 'Hear how OT can support people experiencing substance misuse.',
            'link' => [
                'title' => 'OT and substance abuse',
                'url' => $substance,
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
    matrix_orlaith_content_row('What is occupational therapy?', $what_is, 'cream'),
    matrix_orlaith_content_row('What do occupational therapists do?', $what_do, 'white'),
    $related,
];

wp_update_post([
    'ID' => $post_id,
    'post_title' => 'Occupational therapists',
]);

update_field('flexible_content_blocks', $flexi, $post_id);

WP_CLI::success('Rebuilt Occupational therapists ' . $post_id . ' → ' . get_permalink($post_id));
foreach (get_field('flexible_content_blocks', $post_id) as $i => $row) {
    WP_CLI::log(sprintf('[%d] %s %s', $i, $row['acf_fc_layout'] ?? '', $row['heading'] ?? ''));
}
