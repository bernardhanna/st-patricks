<?php

/**
 * Rebuild New hospital / Modernising our facilities from Drive Library 3.
 *
 * Source: old/content/.../New hospital/New hospital page.docx
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/rebuild-new-hospital-from-drive.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

$post_id = (int) (get_page_by_path('new-hospital')?->ID ?? 0);
if ($post_id === 0) {
    WP_CLI::error('Could not find new-hospital page');
}

$home = untrailingslashit(home_url('/'));
$img_id = 4036;

$p = static function (string $html): string {
    return '<p>' . $html . '</p>';
};
$a = static function (string $url, string $label): string {
    return '<a href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
};

$lucan = get_permalink(712) ?: ($home . '/locations/st-patricks-hospital-lucan/');
$urls = [
    'about' => $home . '/about-us/',
    'offer' => $home . '/what-we-offer/',
    'strategy' => $home . '/about-us/our-present-and-future/',
    'national' => $home . '/national-centre/',
    'advocacy' => $home . '/advocacy-centre/',
    'training' => $home . '/healthcare-professionals/training-centre/',
    'lucan' => untrailingslashit((string) $lucan),
];

$hero_intro = $p('St Patrick’s Mental Health Services is committed to ensuring our facilities are of the highest quality possible for our service users.');

$body = $p('By continuing to modernise our inpatient facilities, we can create a purposefully designed and modern space where service users feel safe, respected and in control, and enjoy a sense of connection, community and dignity.')
    . $p('We will also explore the viability of building a world-class mental healthcare facility, leveraging our existing land at '
        . $a($urls['lucan'], 'St Patrick’s Hospital Lucan')
        . ', to provide:')
    . '<ul>'
    . '<li>adolescent and adult inpatient services</li>'
    . '<li>acute inpatient services</li>'
    . '<li>an addiction unit</li>'
    . '<li>an eating disorders unit</li>'
    . '<li>age-specific care units</li>'
    . '<li>day service and outpatient facilities</li>'
    . '<li>a full range of therapeutic facilities, including a gym, music therapy rooms, and an arts and crafts studio.</li>'
    . '</ul>';

$hero = matrix_orlaith_hero_row('Modernising our facilities', $hero_intro, $img_id);
$hero['layout_style'] = $img_id > 0 ? 'image_split' : 'title_accent';
$hero['text_max_width'] = 'default';
$hero['heading_max_width'] = 'default';
$hero['current_crumb_label'] = 'Modernising our facilities';
$hero['background_color'] = '#C6ECF4';
$hero['accent_color'] = '#6FC9C0';

$flexi = [
    $hero,
    matrix_orlaith_content_row('', $body, 'white'),
    matrix_orlaith_useful_links_row([
        'About us' => $urls['about'],
        'What we offer' => $urls['offer'],
        'Our strategy' => $urls['strategy'],
        'National Centre' => $urls['national'],
        'Advocacy Centre' => $urls['advocacy'],
        'Training Centre' => $urls['training'],
    ]),
];

wp_update_post([
    'ID' => $post_id,
    'post_title' => 'Modernising our facilities',
]);

update_field('flexible_content_blocks', $flexi, $post_id);

WP_CLI::success('Rebuilt New hospital page ' . $post_id . ' → ' . get_permalink($post_id));
foreach (get_field('flexible_content_blocks', $post_id) as $i => $row) {
    WP_CLI::log(sprintf('[%d] %s %s', $i, $row['acf_fc_layout'] ?? '', $row['heading'] ?? ''));
}
