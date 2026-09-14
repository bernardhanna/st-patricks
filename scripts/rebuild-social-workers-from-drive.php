<?php

/**
 * Rebuild About Us > Social workers from Drive Library 3.
 *
 * Sources:
 * - Social worker text.docx
 * - social work page layout.docx
 * - Social work.png
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/rebuild-social-workers-from-drive.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$post_id = (int) (get_page_by_path('about-us/social-workers')?->ID ?? 0);
if ($post_id === 0) {
    WP_CLI::error('Could not find about-us/social-workers');
}

$home = untrailingslashit(home_url('/'));
$p = static function (string $html): string {
    return '<p>' . $html . '</p>';
};
$ul = static function (array $items): string {
    $html = '<ul>';
    foreach ($items as $item) {
        $html .= '<li>' . $item . '</li>';
    }

    return $html . '</ul>';
};

$urls = [
    'team' => $home . '/about-us/our-team/',
    'psychiatrists' => $home . '/about-us/psychiatrists/',
    'psychologists' => $home . '/about-us/psychologists/',
    'nurses' => $home . '/about-us/nurses/',
    'social' => $home . '/about-us/social-workers/',
    'ot' => $home . '/about-us/occupational-therapists/',
    'pharmacists' => $home . '/about-us/pharmacists/',
];

$img_id = 0;
$existing = get_posts([
    'post_type' => 'attachment',
    'post_status' => 'inherit',
    'posts_per_page' => 1,
    'fields' => 'ids',
    'meta_query' => [[
        'key' => '_matrix_drive_source',
        'value' => 'Social work.png',
    ]],
]);
if ($existing !== []) {
    $img_id = (int) $existing[0];
} else {
    $src = get_template_directory()
        . '/old/content/SPMHS-Content-Gathering-Library 3/02-Page-content/About Us/Social workers/Social work.png';
    $out = '/tmp/social-work-hero.jpg';
    if (is_readable($src)) {
        $converted = false;
        if (function_exists('exec')) {
            exec('sips -s format jpeg -Z 2000 ' . escapeshellarg($src) . ' --out ' . escapeshellarg($out) . ' 2>/dev/null', $ignored, $code);
            $converted = ($code === 0 && is_readable($out));
        }
        $file_path = $converted ? $out : $src;
        $file_name = $converted ? 'social-work-hero.jpg' : 'Social-work.png';
        $tmp = wp_tempnam($file_name);
        if ($tmp && copy($file_path, $tmp)) {
            $sideloaded = media_handle_sideload([
                'name' => $file_name,
                'tmp_name' => $tmp,
            ], $post_id, 'Social work');
            if (! is_wp_error($sideloaded)) {
                $img_id = (int) $sideloaded;
                update_post_meta($img_id, '_matrix_drive_source', 'Social work.png');
            }
        }
    }
}
if ($img_id <= 0) {
    $img_id = (int) get_post_thumbnail_id($post_id);
}
if ($img_id <= 0) {
    $img_id = 4024;
}

$hero_intro = $p('Our team of social workers aim to empower service users, their families and supporters through the journey of mental health recovery.');

$about = $p('Social work is a profession that aims to help people to take charge of and improve the quality of their lives. It is rooted in the values of equality and social justice. Social work involves understanding people and the challenges they are navigating from many different perspectives so as to identify and guide the most meaningful supports.');

$what_they_do = $p("Here at St Patrick's Mental Health Services, we have a team of social workers who engage with our service users, their families and supporters as they move through mental health recovery.")
    . $p('Their work is often aimed at enabling service users to deal more effectively with matters of social and emotional concern which might affect them and their families. Social workers can also help service users to access helpful services and resources relevant to different aspects of life.')
    . $p('Our social workers are involved in a wide range of areas, including:')
    . $ul([
        'rehabilitation',
        'social care',
        'the protection of children and vulnerable adults',
        'income maintenance',
        'accommodation',
        'welfare rights.',
    ])
    . $p('A number of our social workers are also systemic family therapists, which means they help people to deal with their challenges within the context and framework of their relationships with others.');

$mdt_cards = [
    [
        'title' => 'Psychiatrists',
        'description' => $p('Our medical team of consultant psychiatrists and registrars provides tailored care and treatment to people experiencing a wide range of mental health difficulties.'),
        'link' => matrix_orlaith_button('Psychiatrists', $urls['psychiatrists']),
        'card_tone' => 'teal',
    ],
    [
        'title' => 'Psychologists',
        'description' => $p('Our Psychology Department is made up of a team of clinical and counselling psychologists who provide a range of treatment to adolescents, adults, and older adults.'),
        'link' => matrix_orlaith_button('Psychologists', $urls['psychologists']),
        'card_tone' => 'green',
    ],
    [
        'title' => 'Nurses',
        'description' => $p('Our nursing team plays a central role and works in a diverse range of settings across our mental health services.'),
        'link' => matrix_orlaith_button('Nurses', $urls['nurses']),
        'card_tone' => 'yellow',
    ],
    [
        'title' => 'Social workers',
        'description' => $p('Our team of social workers aim to empower service users, their families and supporters throughout their care.'),
        'link' => matrix_orlaith_button('Social workers', $urls['social']),
        'card_tone' => 'lavender',
    ],
    [
        'title' => 'Occupational therapists',
        'description' => $p('Our occupational therapists help service users to carry out the activities that matter to them in everyday life.'),
        'link' => matrix_orlaith_button('Occupational therapists', $urls['ot']),
        'card_tone' => 'pink',
    ],
    [
        'title' => 'Pharmacists',
        'description' => $p('Our team of pharmacists works with our medical and nursing colleagues to promote safe and effective use of medicines.'),
        'link' => matrix_orlaith_button('Pharmacists', $urls['pharmacists']),
        'card_tone' => 'coral',
    ],
];

$flexi = [
    matrix_orlaith_hero_row('Social workers', $hero_intro, $img_id),
    matrix_orlaith_content_row('', $about, 'white'),
    matrix_orlaith_content_row('What our social workers do', $what_they_do, 'cream'),
    matrix_orlaith_useful_links_row([
        'Our team' => 'about-us/our-team',
        'About us' => 'about-us',
        'Family supports' => 'service-users-and-visitors/carers-and-supporters',
        'Stories and support' => 'service-users-and-visitors/stories-and-support',
        'Safeguarding' => 'about-us/policies-and-publications/safeguarding',
        'Service user information' => 'service-users-and-visitors',
    ]),
    [
        'acf_fc_layout' => 'multidisciplinary_team_grid',
        'heading_tag' => 'h2',
        'heading' => 'Our multidisciplinary team',
        'intro' => $p('Our multidisciplinary team brings together specialist clinical expertise across psychiatry, psychology, nursing, social work, occupational therapy, and pharmacy.'),
        'cards' => $mdt_cards,
        'background_color' => '#FFFFFF',
    ],
];

wp_update_post([
    'ID' => $post_id,
    'post_title' => 'Social workers',
]);

update_field('flexible_content_blocks', $flexi, $post_id);
if ($img_id > 0) {
    set_post_thumbnail($post_id, $img_id);
}

WP_CLI::success(sprintf(
    'Rebuilt Social workers #%d → %s',
    $post_id,
    get_permalink($post_id)
));

foreach ((array) get_field('flexible_content_blocks', $post_id) as $i => $row) {
    $layout = (string) ($row['acf_fc_layout'] ?? '?');
    $heading = wp_strip_all_tags((string) ($row['heading'] ?? ''));
    $extra = '';
    if ($layout === 'useful_links' && ! empty($row['links'])) {
        $extra = ' | links=' . count($row['links']);
    }
    if ($layout === 'multidisciplinary_team_grid' && ! empty($row['cards'])) {
        $extra = ' | cards=' . count($row['cards']);
    }
    WP_CLI::log("[{$i}] {$layout} {$heading}{$extra}");
}
