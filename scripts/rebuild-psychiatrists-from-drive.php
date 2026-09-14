<?php

/**
 * Rebuild About Us > Psychiatrists from Drive Library 3.
 *
 * Sources:
 * - Psychiatrists - text.docx
 * - Psychiatry - layout.docx
 * - Psychiatry.png
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/rebuild-psychiatrists-from-drive.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$post_id = (int) (get_page_by_path('about-us/psychiatrists')?->ID ?? 0);
if ($post_id === 0) {
    WP_CLI::error('Could not find about-us/psychiatrists');
}

$home = untrailingslashit(home_url('/'));
$p = static function (string $html): string {
    return '<p>' . $html . '</p>';
};
$a = static function (string $url, string $label): string {
    return '<a href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
};

$urls = [
    'team' => $home . '/about-us/our-team/',
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
        'value' => 'Psychiatry.png',
    ]],
]);
if ($existing !== []) {
    $img_id = (int) $existing[0];
} else {
    $src = get_template_directory()
        . '/old/content/SPMHS-Content-Gathering-Library 3/02-Page-content/About Us/Psychiatrists/Psychiatry.png';
    $out = '/tmp/psychiatry-hero.jpg';
    if (is_readable($src)) {
        // Prefer a web-sized JPEG to avoid hang on large PNG sideloads.
        $converted = false;
        if (function_exists('exec')) {
            exec('sips -s format jpeg -Z 2000 ' . escapeshellarg($src) . ' --out ' . escapeshellarg($out) . ' 2>/dev/null', $ignored, $code);
            $converted = ($code === 0 && is_readable($out));
        }
        $file_path = $converted ? $out : $src;
        $file_name = $converted ? 'psychiatry-hero.jpg' : 'Psychiatry.png';
        $tmp = wp_tempnam($file_name);
        if ($tmp && copy($file_path, $tmp)) {
            $sideloaded = media_handle_sideload([
                'name' => $file_name,
                'tmp_name' => $tmp,
            ], $post_id, 'Psychiatry');
            if (! is_wp_error($sideloaded)) {
                $img_id = (int) $sideloaded;
                update_post_meta($img_id, '_matrix_drive_source', 'Psychiatry.png');
            }
        }
    }
}
if ($img_id <= 0) {
    $img_id = (int) get_post_thumbnail_id($post_id);
}

$hero_intro = $p('Our medical team provides tailored care and treatment to people experiencing a wide range of mental health difficulties.');

$body = $p('A consultant psychiatrist is a medical doctor, or physician, who specialises in preventing, diagnosing and treating mental, addictive and emotional disorders.')
    . $p("Here in St Patrick's Mental Health Services, our medical care team is made up of consultant psychiatrists and registrar psychiatrists, who are graduate doctors in training to become consultant psychiatrists.")
    . $p('Our consultant psychiatrists and registrars specialise in various mental health difficulties. They are trained in the medical, psychological and social elements or components of mental, emotional and behavioural disorders. They use a broad range of treatment types, or modalities, including diagnostic testing, medication, psychotherapy, and help service users and their families to cope with stress and crises.')
    . $p('For more on referrals to our services, please contact our Referral and Admission Service by phoning '
        . $a('tel:012493635', '01 249 3635')
        . ' or emailing '
        . $a('mailto:referrals@stpatricks.ie', 'referrals@stpatricks.ie')
        . '.');

$mdt_cards = [
    [
        'title' => 'Psychiatrists',
        'description' => $p('Our medical team of consultant psychiatrists and registrars provides tailored care and treatment to people experiencing a wide range of mental health difficulties.'),
        'link' => matrix_orlaith_button('Psychiatrists', $home . '/about-us/psychiatrists/'),
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
    matrix_orlaith_hero_row('Psychiatrists', $hero_intro, $img_id),
    matrix_orlaith_content_row('', $body, 'white'),
    matrix_orlaith_content_row('Meet our psychiatry team', $p('Find out more about the people who lead and support care across St Patrick\'s Mental Health Services.'), 'cream', 0, 'image_left', [
        'primary_button' => matrix_orlaith_button('Meet our psychiatry team', $urls['team']),
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
    'post_title' => 'Psychiatrists',
]);

update_field('flexible_content_blocks', $flexi, $post_id);
if ($img_id > 0) {
    set_post_thumbnail($post_id, $img_id);
}

WP_CLI::success(sprintf(
    'Rebuilt Psychiatrists #%d → %s',
    $post_id,
    get_permalink($post_id)
));

foreach ((array) get_field('flexible_content_blocks', $post_id) as $i => $row) {
    $layout = (string) ($row['acf_fc_layout'] ?? '?');
    $heading = wp_strip_all_tags((string) ($row['heading'] ?? ''));
    $extra = '';
    if ($layout === 'multidisciplinary_team_grid' && ! empty($row['cards'])) {
        $extra = ' | cards=' . count($row['cards']);
    }
    if (! empty($row['primary_button']['title'])) {
        $extra .= ' | btn=' . $row['primary_button']['title'];
    }
    WP_CLI::log("[{$i}] {$layout} {$heading}{$extra}");
}
